<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\RegistrationAnswer;
use App\Models\User;
use App\Models\Webinar;
use App\Support\DynamicFieldsHelper;
use App\Support\SpreadsheetReader;
use App\Support\XlsxExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $request->user();
        $accessibleWebinarIds = $admin->accessibleWebinarIds();
        $webinars = Webinar::when($admin->hasRole('sub-admin'), fn ($query) => $query->whereIn('id', $accessibleWebinarIds))
            ->orderBy('title')
            ->get();
        $search = trim((string) $request->input('search'));
        $webinarId = $request->integer('webinar_id');
        $status = $request->input('status');

        $query = Registration::with(['user', 'webinar'])
            ->when($admin->hasRole('sub-admin'), fn ($query) => $query->whereIn('webinar_id', $accessibleWebinarIds));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    })
                    ->orWhereHas('webinar', function ($wq) use ($search) {
                        $wq->where('title', 'like', "%{$search}%");
                    });
            });
        }

        if ($webinarId) {
            $query->where('webinar_id', $webinarId);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $databaseRows = $query->latest('registered_at')->latest('id')->paginate(15)->withQueryString();
        $dynamicColumns = DynamicFieldsHelper::attach($databaseRows, $webinarId ? [$webinarId] : null);

        return view('pages.admin.audience_users_registrations', [
            'title' => 'Registrations',
            'type' => 'registrations',
            'databaseRows' => $databaseRows,
            'webinars' => $webinars,
            'dynamicColumns' => $dynamicColumns,
        ]);
    }

    public function users(Request $request): View
    {
        $admin = $request->user();
        $accessibleWebinarIds = $admin->accessibleWebinarIds();
        $webinars = Webinar::when($admin->hasRole('sub-admin'), fn ($query) => $query->whereIn('id', $accessibleWebinarIds))
            ->orderBy('title')
            ->get();
        $webinarId = $request->integer('webinar_id');
        $databaseRows = $this->usersQuery($request)->paginate(15)->withQueryString();
        $dynamicColumns = DynamicFieldsHelper::attach($databaseRows, $webinarId ? [$webinarId] : null);

        return view('pages.admin.audience_users_registrations', [
            'title' => 'Users',
            'type' => 'users',
            'databaseRows' => $databaseRows,
            'webinars' => $webinars,
            'dynamicColumns' => $dynamicColumns,
        ]);
    }

    public function exportUsers(Request $request)
    {
        $webinarId = $request->integer('webinar_id');
        $users = $this->usersQuery($request)->get();
        $dynamicColumns = DynamicFieldsHelper::attach($users, $webinarId ? [$webinarId] : null);
        $rows = [array_merge(['User', 'Email', 'Mobile', 'Webinars'], $dynamicColumns, ['Registered', 'Status'])];

        foreach ($users as $user) {
            $primaryRegistration = $user->registrations->first();
            $dynamicValues = collect($dynamicColumns)->map(fn ($column) => $user->dynamic_fields[$column] ?? '')->all();
            $rows[] = array_merge([
                $user->name,
                $user->email,
                $user->mobile,
                $user->registrations->pluck('webinar.title')->filter()->unique()->implode(', '),
            ], $dynamicValues, [
                $primaryRegistration?->registered_at ?? $user->created_at,
                ucfirst($user->status ?? 'active'),
            ]);
        }

        $path = XlsxExport::create($rows, [4 + count($dynamicColumns)], [], 'Users');

        return response()->download($path, 'users-'.now()->format('Y-m-d').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function importTemplate(Request $request): StreamedResponse
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);

        return response()->streamDownload(function () {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, ['Name', 'Email', 'Mobile', 'Emp ID', 'Company', 'Designation']);
            fputcsv($stream, ['Aarav Sharma', 'aarav@example.com', '9876543210', 'EMP-001', 'Example Ltd', 'Manager']);
            fclose($stream);
        }, 'attendee-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importUsers(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);
        $data = $request->validate([
            'webinar_id' => ['required', 'integer', 'exists:webinars,id'],
            'sheet' => ['required', 'file', 'max:10240'],
        ]);
        $file = $request->file('sheet');
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['csv', 'txt', 'xlsx'], true)) {
            return back()->withErrors(['sheet' => 'Please upload a CSV or XLSX file.'])->withInput();
        }

        try {
            $rows = SpreadsheetReader::read($file->getRealPath(), $extension);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['sheet' => $exception->getMessage()])->withInput();
        }

        if (count($rows) < 2) {
            return back()->withErrors(['sheet' => 'The sheet must contain a header row and at least one attendee.'])->withInput();
        }

        $headers = array_map(fn ($header) => trim((string) $header), array_shift($rows));
        $normalizedHeaders = array_map(fn ($header) => $this->normalizeImportHeader($header), $headers);
        if (count(array_filter($normalizedHeaders, fn ($header) => $header === 'email')) !== 1) {
            return back()->withErrors(['sheet' => 'The sheet must contain exactly one Email column.'])->withInput();
        }

        $webinar = Webinar::findOrFail($data['webinar_id']);
        $result = DB::transaction(fn () => $this->importAttendeeRows($webinar, $headers, $normalizedHeaders, $rows));
        $message = "Import complete: {$result['registrations']} registered, {$result['users']} new users, {$result['updated']} updated";
        if ($result['skipped'] > 0) {
            $message .= ", {$result['skipped']} skipped";
        }

        return redirect()->route('admin.users')
            ->with('success', $message.'.')
            ->with('import_warnings', $result['warnings']);
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string>  $normalizedHeaders
     * @param  array<int, array<int, string>>  $rows
     * @return array{registrations:int,users:int,updated:int,skipped:int,warnings:array<int,string>}
     */
    private function importAttendeeRows(Webinar $webinar, array $headers, array $normalizedHeaders, array $rows): array
    {
        $form = $webinar->registrationForm()->firstOrCreate([], [
            'title' => 'Registration Form', 'is_active' => true, 'require_login' => true,
        ]);
        // Only identity/contact columns are fixed. Every business-specific column
        // (Company, Designation, Emp ID, Department, etc.) must also remain attached
        // to this webinar as a dynamic registration field and answer.
        $standard = ['name', 'first_name', 'last_name', 'email', 'mobile'];
        $extraFields = [];
        $lastOrder = (int) $form->fields()->max('display_order');
        $existingFields = $form->fields()->get()->keyBy(fn ($field) => $this->normalizeImportHeader($field->field_key ?: $field->label));

        foreach ($normalizedHeaders as $index => $normalized) {
            if ($normalized === '' || in_array($normalized, $standard, true)) {
                continue;
            }
            $field = $existingFields->get($normalized);
            if (! $field) {
                $baseKey = Str::slug($normalized, '_') ?: 'imported_field';
                $fieldKey = $baseKey;
                $suffix = 2;
                while ($form->fields()->where('field_key', $fieldKey)->exists()) {
                    $fieldKey = $baseKey.'_'.$suffix++;
                }
                $field = $form->fields()->create([
                    'label' => $headers[$index], 'field_key' => $fieldKey, 'field_type' => 'text',
                    'placeholder' => 'Enter '.$headers[$index],
                    'is_required' => false, 'is_enabled' => true, 'display_order' => ++$lastOrder,
                    'help_text' => 'Imported attendee data.',
                ]);
                $existingFields->put($normalized, $field);
            } else {
                $field->update([
                    'is_enabled' => true,
                    'placeholder' => $field->placeholder ?: 'Enter '.$headers[$index],
                ]);
            }
            $extraFields[$index] = $field;
        }

        $counts = ['registrations' => 0, 'users' => 0, 'updated' => 0, 'skipped' => 0, 'warnings' => []];
        $seen = [];
        foreach ($rows as $offset => $row) {
            $line = $offset + 2;
            $values = [];
            foreach ($normalizedHeaders as $index => $key) {
                if ($key !== '') {
                    $values[$key] = trim((string) ($row[$index] ?? ''));
                }
            }
            $email = strtolower($values['email'] ?? '');
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $counts['skipped']++;
                if (count($counts['warnings']) < 20) {
                    $counts['warnings'][] = "Row {$line}: invalid or missing email.";
                }

                continue;
            }
            if (isset($seen[$email])) {
                $counts['skipped']++;
                if (count($counts['warnings']) < 20) {
                    $counts['warnings'][] = "Row {$line}: duplicate email in this sheet ({$email}).";
                }

                continue;
            }
            $seen[$email] = true;

            $name = trim($values['name'] ?? trim(($values['first_name'] ?? '').' '.($values['last_name'] ?? '')));
            if ($name === '') {
                $name = Str::headline(Str::before($email, '@'));
            }
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
            if (! $user) {
                $user = User::create([
                    'name' => $name, 'email' => $email, 'mobile' => ($values['mobile'] ?? '') ?: null,
                    'company' => ($values['company'] ?? '') ?: null, 'job_title' => ($values['job_title'] ?? '') ?: null,
                    'password' => Str::random(40), 'status' => 'active',
                ]);
                $counts['users']++;
            } else {
                $updates = [];
                foreach (['mobile', 'company', 'job_title'] as $attribute) {
                    if (! $user->{$attribute} && ! empty($values[$attribute])) {
                        $updates[$attribute] = $values[$attribute];
                    }
                }
                if (! $user->name && $name !== '') {
                    $updates['name'] = $name;
                }
                if ($updates !== []) {
                    $user->update($updates);
                }
            }

            $registration = Registration::where(['webinar_id' => $webinar->id, 'email' => $email])->first();
            if ($registration) {
                $registration->update(['user_id' => $user->id]);
                $counts['updated']++;
            } else {
                $registration = Registration::create([
                    'webinar_id' => $webinar->id, 'user_id' => $user->id, 'email' => $email,
                    'status' => 'approved', 'source' => 'spreadsheet_import', 'registered_at' => now(), 'approved_at' => now(),
                ]);
                $counts['registrations']++;
            }

            foreach ($extraFields as $index => $field) {
                $value = trim((string) ($row[$index] ?? ''));
                if ($value !== '') {
                    RegistrationAnswer::updateOrCreate(
                        ['registration_id' => $registration->id, 'registration_field_id' => $field->id],
                        ['value' => $value]
                    );
                }
            }
        }

        return $counts;
    }

    private function normalizeImportHeader(string $header): string
    {
        $key = Str::of($header)->lower()->trim()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->value();

        return match ($key) {
            'e_mail', 'email_address', 'mail', 'email_id' => 'email',
            'full_name', 'employee_name', 'attendee_name', 'user_name' => 'name',
            'firstname', 'given_name' => 'first_name',
            'lastname', 'surname', 'family_name' => 'last_name',
            'phone', 'phone_number', 'mobile_number', 'contact', 'contact_number' => 'mobile',
            'organisation', 'organization', 'employer' => 'company',
            'designation', 'role', 'title' => 'job_title',
            'employee_id', 'employee_code', 'empid', 'emp_code' => 'emp_id',
            default => $key,
        };
    }

    private function usersQuery(Request $request): Builder
    {
        $admin = $request->user();
        $accessibleWebinarIds = $admin->accessibleWebinarIds();
        $search = trim((string) $request->input('search'));
        $webinarId = $request->integer('webinar_id');
        $status = $request->input('status');

        return User::with([
            'roles',
            'registrations' => function ($query) use ($admin, $accessibleWebinarIds, $webinarId) {
                if ($admin->hasRole('sub-admin')) {
                    $query->whereIn('webinar_id', $accessibleWebinarIds);
                }
                if ($webinarId) {
                    $query->where('webinar_id', $webinarId);
                }
            },
            'registrations.webinar',
        ])
            ->whereHas('registrations')
            ->when($admin->hasRole('sub-admin'), fn ($query) => $query->whereHas('registrations', fn ($registrations) => $registrations->whereIn('webinar_id', $accessibleWebinarIds)))
            ->when($search !== '', fn ($query) => $query->where(function ($nested) use ($search) {
                $nested->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            }))
            ->when($webinarId, fn ($query) => $query->whereHas('registrations', fn ($registrations) => $registrations->where('webinar_id', $webinarId)))
            ->when($status && $status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest('updated_at')
            ->latest('id');
    }

    public function show(Registration $registration): View
    {
        $registration->load(['user', 'webinar']);
        $events = DB::table('webinar_attendance_events')->where(['webinar_id' => $registration->webinar_id, 'user_id' => $registration->user_id])->orderBy('occurred_at')->get();
        $pollAnswers = DB::table('poll_responses')->join('polls', 'polls.id', '=', 'poll_responses.poll_id')->where('polls.webinar_id', $registration->webinar_id)->where('poll_responses.user_id', $registration->user_id)->count();
        $certificate = DB::table('certificates')->where(['webinar_id' => $registration->webinar_id, 'user_id' => $registration->user_id])->first();

        return view('pages.admin.registration-journey', compact('registration', 'events', 'pollAnswers', 'certificate'));
    }

    public function destroy(Request $request, Registration $registration): RedirectResponse
    {
        $registration->delete();

        $route = $request->input('return_to') === 'users' ? 'admin.users' : 'admin.registrations';

        return redirect()->route($route)
            ->with('status', 'Registration deleted successfully.');
    }
}
