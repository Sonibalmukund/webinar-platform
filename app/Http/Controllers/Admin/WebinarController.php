<?php

namespace App\Http\Controllers\Admin;

use App\Events\WebinarRoomUpdated;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\CertificateTemplate;
use App\Models\Webinar;
use App\Support\AuditTrail;
use App\Support\VideoEmbed;
use App\Support\WebinarHealth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WebinarController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        $query = Webinar::with('registrationForm')
            ->withCount('registrations')
            ->when($user->hasRole('sub-admin'), fn ($query) => $query->whereIn('id', $user->assignedWebinars()->pluck('webinars.id')));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $webinars = $query->latest()->paginate(15)->withQueryString();
        $webinars->getCollection()->each(fn ($webinar) => $webinar->health = WebinarHealth::score($webinar));

        return view('pages.admin.webinars.index', compact('webinars'));
    }

    public function create(): View
    {
        return view('pages.admin.webinars.form', ['webinar' => new Webinar, 'sessionResourcesText' => '', 'agendaItems' => collect()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $webinar = DB::transaction(function () use ($request) {
            $webinar = Webinar::create($this->webinarData($request) + ['created_by' => $request->user()->id]);
            if ($request->hasAny(['registration_enabled', 'fields'])) {
                $this->saveRegistrationForm($request, $webinar);
            }
            $this->saveLegacyPollsAndCertificate($request, $webinar);
            $this->saveSessionResources($request, $webinar);
            $this->saveAgenda($request, $webinar);

            return $webinar;
        });
        AuditTrail::record('webinar.created', $webinar, 'Webinar created.');

        return redirect()->route('admin.webinars.index')->with('status', 'Webinar created successfully.');
    }

    public function edit(Webinar $webinar): View
    {
        $sessionResourcesText = DB::table('webinar_resources')->where('webinar_id', $webinar->id)->orderBy('display_order')->get()->map(fn ($item) => $item->title.' | '.$item->path_or_url)->join("\n");
        $agendaItems = DB::table('webinar_agenda_items')->where('webinar_id', $webinar->id)->orderBy('display_order')->get();

        return view('pages.admin.webinars.form', ['webinar' => $webinar->load('registrationForm.fields.options'), 'sessionResourcesText' => $sessionResourcesText, 'agendaItems' => $agendaItems]);
    }

    public function show(Webinar $webinar): View
    {
        $webinar = $webinar->load(['registrationForm.fields', 'speakers', 'polls'])->loadCount('registrations');
        $readiness = ['Schedule' => (bool) ($webinar->starts_at && $webinar->ends_at), 'Video player' => (bool) $webinar->live_url, 'Registration form' => (bool) $webinar->registrationForm?->is_active, 'Speaker assigned' => $webinar->speakers->isNotEmpty(), 'Poll configured' => $webinar->polls->isNotEmpty(), 'Certificate' => (bool) data_get($webinar->settings, 'certificate_template_id')];
        $events = DB::table('webinar_attendance_events')->where('webinar_id', $webinar->id)->where('event_type', 'heartbeat')->pluck('occurred_at')->groupBy(fn ($at) => Carbon::parse($at)->format('H'))->map(fn ($items, $hour) => (object) ['hour' => $hour, 'total' => $items->count()])->values();

        return view('pages.admin.webinars.show', compact('webinar', 'readiness', 'events'));
    }

    public function live(Webinar $webinar): View
    {
        $liveViewers = DB::table('webinar_attendees')->where('webinar_id', $webinar->id)->whereNull('left_at')->where('last_seen_at', '>=', now()->subSeconds(75))->count();

        return view('pages.admin.webinars.live', ['webinar' => $webinar->loadCount('registrations'), 'liveViewers' => $liveViewers]);
    }

    public function controls(Request $request, Webinar $webinar): RedirectResponse|JsonResponse
    {
        $webinar->update([
            'status' => $request->input('status', $webinar->status),
            'chat_enabled' => $request->boolean('chat_enabled'),
            'qa_enabled' => $request->boolean('qa_enabled'),
            'comments_enabled' => $request->boolean('comments_enabled'),
            'polls_enabled' => $request->boolean('polls_enabled'),
            'feedback_enabled' => $request->boolean('feedback_enabled'),
        ]);
        $this->broadcastRoom($webinar, 'controls');
        AuditTrail::record('webinar.controls', $webinar, 'Live controls updated.', ['status' => $webinar->status]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Live controls updated.',
                'status' => $webinar->status,
                'chat_enabled' => $webinar->chat_enabled,
                'qa_enabled' => $webinar->qa_enabled,
                'polls_enabled' => $webinar->polls_enabled,
                'comments_enabled' => $webinar->comments_enabled,
                'feedback_enabled' => $webinar->feedback_enabled,
                'certificate_enabled' => $webinar->certificate_enabled === 'yes',
            ]);
        }

        return redirect()->route('admin.webinars.index')->with('status', 'Live controls updated successfully.');
    }

    public function announcement(Request $request, Webinar $webinar): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:500'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'button_url' => ['nullable', 'url', 'max:500'],
            'enabled' => ['nullable'],
        ]);

        $settings = $webinar->settings ?? [];
        $settings['pinned_announcement'] = [
            'enabled' => $request->boolean('enabled'),
            'message' => trim((string) ($data['message'] ?? '')),
            'button_text' => trim((string) ($data['button_text'] ?? '')),
            'button_url' => trim((string) ($data['button_url'] ?? '')),
            'updated_at' => now()->toIso8601String(),
        ];

        $webinar->update(['settings' => $settings]);
        $this->broadcastRoom($webinar, 'announcement');
        AuditTrail::record('webinar.announcement', $webinar, 'Pinned announcement updated.');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Pinned announcement updated.',
                'pinned_announcement' => $settings['pinned_announcement'],
            ]);
        }

        return back()->with('status', 'Pinned announcement updated.');
    }

    public function status(Request $request, Webinar $webinar): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:draft,scheduled,live,completed,cancelled']]);
        if (in_array($data['status'], ['scheduled', 'live']) && (! $webinar->starts_at || ! $webinar->ends_at)) {
            return redirect()->route('admin.webinars.index')->withErrors(['status' => 'Set the start and end time before publishing this webinar.']);
        }
        $webinar->update(['status' => $data['status'], 'published_at' => $data['status'] === 'draft' ? null : ($webinar->published_at ?? now())]);
        $this->broadcastRoom($webinar, 'status');
        AuditTrail::record('webinar.status', $webinar, 'Webinar status changed to '.$data['status'].'.');

        return redirect()->route('admin.webinars.index')->with('status', 'Webinar status updated successfully.');
    }

    public function update(Request $request, Webinar $webinar): RedirectResponse
    {
        DB::transaction(function () use ($request, $webinar) {
            $webinar->update($this->webinarData($request, $webinar));
            if ($request->hasAny(['registration_enabled', 'fields'])) {
                $this->saveRegistrationForm($request, $webinar);
            }
            $this->saveLegacyPollsAndCertificate($request, $webinar);
            $this->saveSessionResources($request, $webinar);
            $this->saveAgenda($request, $webinar);
        });
        AuditTrail::record('webinar.updated', $webinar, 'Webinar settings updated.', ['changes' => $webinar->getChanges()]);
        $this->broadcastRoom($webinar, 'controls');

        return redirect()->route('admin.webinars.index')->with('status', 'Webinar updated successfully.');
    }

    public function destroy(Webinar $webinar): RedirectResponse
    {
        AuditTrail::record('webinar.deleted', $webinar, 'Webinar deleted.', ['title' => $webinar->title]);
        $webinar->delete();

        return redirect()->route('admin.webinars.index')->with('status', 'Webinar removed.');
    }

    public function clone(Webinar $webinar): RedirectResponse
    {
        $copy = DB::transaction(function () use ($webinar) {
            $webinar->load(['registrationForm.fields.options', 'polls.options', 'speakers']);
            $copy = $webinar->replicate(['slug', 'published_at']);
            $copy->title = $webinar->title.' Copy';
            $copy->slug = Str::slug($copy->title).'-'.Str::lower(Str::random(6));
            $copy->status = 'draft';
            $copy->published_at = null;
            $copy->starts_at = null;
            $copy->ends_at = null;
            $copy->save();
            if ($webinar->registrationForm) {
                $form = $webinar->registrationForm->replicate();
                $form->webinar_id = $copy->id;
                $form->save();
                $fieldMap = [];
                foreach ($webinar->registrationForm->fields as $field) {
                    $new = $field->replicate(['condition_field_id']);
                    $new->registration_form_id = $form->id;
                    $new->save();
                    $fieldMap[$field->id] = $new->id;
                    foreach ($field->options as $option) {
                        $newOption = $option->replicate();
                        $newOption->registration_field_id = $new->id;
                        $newOption->save();
                    }
                }foreach ($webinar->registrationForm->fields as $field) {
                    if ($field->condition_field_id && ! empty($fieldMap[$field->condition_field_id])) {
                        $form->fields()->whereKey($fieldMap[$field->id])->update(['condition_field_id' => $fieldMap[$field->condition_field_id]]);
                    }
                }
            }foreach ($webinar->polls as $poll) {
                $newPoll = $poll->replicate(['started_at', 'ended_at']);
                $newPoll->webinar_id = $copy->id;
                $newPoll->status = 'draft';
                $newPoll->save();
                foreach ($poll->options as $option) {
                    $newOption = $option->replicate();
                    $newOption->poll_id = $newPoll->id;
                    $newOption->save();
                }
            }$copy->speakers()->sync($webinar->speakers->mapWithKeys(fn ($speaker) => [$speaker->id => ['role' => $speaker->pivot->role, 'display_order' => $speaker->pivot->display_order]])->all());
            foreach (Banner::where('webinar_id', $webinar->id)->get() as $item) {
                $new = $item->replicate(['starts_at', 'ends_at']);
                $new->webinar_id = $copy->id;
                $new->is_active = false;
                $new->save();
            }foreach (Brand::where('webinar_id', $webinar->id)->get() as $item) {
                $new = $item->replicate();
                $new->webinar_id = $copy->id;
                $new->save();
            }

            return $copy;
        });
        AuditTrail::record('webinar.cloned', $copy, 'Webinar cloned from '.$webinar->title, ['source_id' => $webinar->id]);

        return redirect()->route('admin.webinars.index')->with('status', 'Webinar cloned as a draft. Set its schedule before publishing.');
    }

    private function webinarData(Request $request, ?Webinar $webinar = null): array
    {
        $request->merge([
            'early_entry_minutes' => $request->input('early_entry_minutes', 30),
            'registration_type' => $request->input('registration_type', 'free'),
            'price' => $request->input('price', 0),
        ]);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('webinars', 'slug')->ignore($webinar?->id)],
            'icon' => ['nullable', 'string', 'max:60'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,scheduled,live,completed,cancelled'],
            'language' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'timezone'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'early_entry_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'registration_type' => ['required', 'in:free,paid'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ], [
            'ends_at.after' => 'The end date and time must be after the start date and time.',
        ]);
        $player = $request->validate(['live_provider' => ['nullable', 'in:youtube,vimeo,custom'], 'live_source' => ['nullable', 'string', 'max:5000']]);
        $provider = $player['live_provider'] ?? null;
        $data['live_provider'] = $provider;
        $data['live_url'] = $provider ? VideoEmbed::url($provider, $player['live_source'] ?? null) : null;
        if ($provider && filled($player['live_source'] ?? null) && ! $data['live_url']) {
            throw ValidationException::withMessages(['live_source' => 'Enter a valid '.$provider.' video URL, code, or iframe.']);
        }
        foreach (['starts_at', 'ends_at'] as $field) {
            if (filled($data[$field] ?? null)) {
                $data[$field] = Carbon::parse($data[$field], $data['timezone'])->utc();
            }
        }
        $data['certificate_enabled'] = $request->has('certificate_enabled') ? ($request->boolean('certificate_enabled') ? 'yes' : 'no') : ($webinar?->certificate_enabled ?? 'no');
        $data['chat_enabled'] = $request->boolean('chat_enabled');
        $data['qa_enabled'] = $request->has('qa_enabled') ? $request->boolean('qa_enabled') : ($webinar?->qa_enabled ?? true);
        $data['comments_enabled'] = $request->boolean('comments_enabled');
        $data['feedback_enabled'] = $request->boolean('feedback_enabled');
        $data['polls_enabled'] = $request->has('polls_enabled') ? $request->boolean('polls_enabled') : ($webinar?->polls_enabled ?? false);
        if ($data['registration_type'] === 'free') {
            $data['price'] = null;
        }
        $data['slug'] = filled($data['slug'] ?? null) ? $data['slug'] : $this->uniqueSlug($data['title'], $webinar?->id);
        $data['published_at'] = $data['status'] === 'draft' ? null : ($webinar?->published_at ?? now());
        $experience = $request->validate([
            'room_layout' => ['nullable', 'in:theater,presentation,interview,panel'],
            'brand_primary' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'], 'brand_secondary' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'], 'brand_logo_url' => ['nullable', 'url'],
            'brand_logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'waiting_message' => ['nullable', 'string', 'max:500'], 'waiting_media_url' => ['nullable', 'url'], 'post_message' => ['nullable', 'string', 'max:500'], 'registration_success_title' => ['nullable', 'string', 'max:120'], 'registration_success_message' => ['nullable', 'string', 'max:500'],
            'video_chapters' => ['nullable', 'string', 'max:5000'], 'certificate_min_attendance' => ['nullable', 'integer', 'min:0', 'max:100'], 'certificate_require_poll' => ['nullable', 'boolean'],
            'registration_preset' => ['nullable', 'in:custom,business,education,healthcare,marketing'],
        ]);
        $contact = $request->validate(['contact_mobile' => ['nullable', 'string', 'max:25', 'regex:/^[+0-9() .-]+$/']]);
        $settings = $webinar?->settings ?? [];
        if ($request->has('contact_mobile')) {
            $settings['contact_mobile'] = $contact['contact_mobile'] ?? null;
        }
        $existingLogo = data_get($webinar?->settings, 'experience.logo_url');
        $logoUrl = $request->filled('brand_logo_url') ? $request->input('brand_logo_url') : $existingLogo;
        if ($request->hasFile('brand_logo_file')) {
            $logoFile = $request->file('brand_logo_file');
            $directory = public_path('uploads/webinars');
            \Illuminate\Support\Facades\File::ensureDirectoryExists($directory);
            $logoName = \Illuminate\Support\Str::uuid().'.'.$logoFile->getClientOriginalExtension();
            $logoFile->move($directory, $logoName);
            $logoUrl = '/uploads/webinars/'.$logoName;
        }
        $settings['experience'] = [
            'layout' => $experience['room_layout'] ?? 'theater', 'primary' => $experience['brand_primary'] ?? '#6d28d9', 'secondary' => $experience['brand_secondary'] ?? '#2563eb', 'logo_url' => $logoUrl,
            'waiting_message' => $experience['waiting_message'] ?? 'The webinar will begin shortly.', 'waiting_media_url' => $experience['waiting_media_url'] ?? null, 'post_message' => $experience['post_message'] ?? 'Thank you for attending.',
            'registration_success_title' => $experience['registration_success_title'] ?? 'You are registered!', 'registration_success_message' => $experience['registration_success_message'] ?? 'Your seat is confirmed. Add the webinar to your calendar and return when the room opens.',
            'chapters' => collect(preg_split('/\r\n|\r|\n/', $experience['video_chapters'] ?? ''))->filter()->map(function ($line) {
                [$time,$title] = array_pad(explode('|', $line, 2), 2, '');

                return ['time' => trim($time), 'title' => trim($title)];
            })->values()->all(),
            'certificate_min_attendance' => (int) ($experience['certificate_min_attendance'] ?? 80), 'certificate_require_poll' => $request->boolean('certificate_require_poll'), 'registration_preset' => $experience['registration_preset'] ?? 'custom',
        ];
        $data['settings'] = $settings;

        return $data;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'webinar';
        $slug = $base;
        $suffix = 2;
        while (Webinar::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function saveRegistrationForm(Request $request, Webinar $webinar): void
    {
        $form = $webinar->registrationForm()->updateOrCreate([], ['title' => $request->input('form_title', 'Registration Form'), 'description' => $request->input('form_description'), 'is_active' => $request->boolean('registration_enabled'), 'require_login' => true, 'success_message' => $request->input('success_message')]);
        $kept = [];
        foreach ($request->input('fields', []) as $order => $row) {
            if (blank($row['label'] ?? null)) {
                continue;
            }
            $field = $form->fields()->updateOrCreate(['id' => $row['id'] ?? null], ['label' => $row['label'], 'field_key' => $row['field_key'] ?? Str::slug($row['label'], '_'), 'field_type' => $row['field_type'] ?? 'text', 'placeholder' => $row['placeholder'] ?? null, 'is_required' => isset($row['is_required']), 'is_enabled' => isset($row['is_enabled']), 'display_order' => $order]);
            $kept[] = $field->id;
            $field->options()->delete();
            if (in_array($field->field_type, ['dropdown', 'radio', 'checkbox'])) {
                foreach (preg_split('/\r\n|\r|\n/', trim($row['options'] ?? '')) as $i => $label) {
                    if (filled($label)) {
                        $field->options()->create(['label' => trim($label), 'value' => Str::slug(trim($label), '_'), 'display_order' => $i, 'is_enabled' => true]);
                    }
                }
            }
        }
        $form->fields()->when($kept, fn ($q) => $q->whereNotIn('id', $kept))->when(! $kept, fn ($q) => $q)->delete();
    }

    private function saveLegacyPollsAndCertificate(Request $request, Webinar $webinar): void
    {
        if ($request->has('polls')) {
            $kept = [];
            foreach ($request->input('polls', []) as $row) {
                if (blank($row['question'] ?? null)) {
                    continue;
                }
                $poll = $webinar->polls()->updateOrCreate(['id' => $row['id'] ?? null], [
                    'created_by' => $request->user()->id, 'question' => $row['question'],
                    'allow_multiple' => isset($row['allow_multiple']), 'status' => $row['status'] ?? 'draft',
                ]);
                $kept[] = $poll->id;
                $poll->options()->delete();
                foreach (preg_split('/\r\n|\r|\n/', trim($row['options'] ?? '')) as $order => $label) {
                    if (filled($label)) {
                        $poll->options()->create(['label' => trim($label), 'display_order' => $order]);
                    }
                }
            }
            $webinar->polls()->when($kept, fn ($query) => $query->whereNotIn('id', $kept))->delete();
        }

        if (filled($request->input('certificate_name'))) {
            $settings = $webinar->settings ?? [];
            $template = isset($settings['certificate_template_id']) ? CertificateTemplate::find($settings['certificate_template_id']) : null;
            $values = [
                'name' => $request->input('certificate_name'),
                'orientation' => $request->input('certificate_orientation', 'landscape'),
                'design' => ['headline' => $request->input('certificate_headline', 'Certificate of Completion'), 'signatory' => $request->input('certificate_signatory')],
                'created_by' => $request->user()->id,
            ];
            $template ? $template->update($values) : $template = CertificateTemplate::create($values);
            $settings['certificate_template_id'] = $template->id;
            $webinar->update(['settings' => $settings]);
        }
    }

    private function broadcastRoom(Webinar $webinar, string $change): void
    {
        try {
            broadcast(new WebinarRoomUpdated($webinar->id, $change, [
                'status' => $webinar->status,
                'chat_enabled' => (bool) $webinar->chat_enabled,
                'qa_enabled' => (bool) $webinar->qa_enabled,
                'polls_enabled' => (bool) $webinar->polls_enabled,
                'comments_enabled' => (bool) $webinar->comments_enabled,
                'feedback_enabled' => (bool) $webinar->feedback_enabled,
                'certificate_enabled' => $webinar->certificate_enabled === 'yes',
                'pinned_announcement' => data_get($webinar->settings, 'pinned_announcement'),
            ]));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function saveSessionResources(Request $request, Webinar $webinar): void
    {
        if (! $request->has('session_resources') && ! $request->hasFile('resource_pdfs')) {
            return;
        }
        $request->validate(['resource_pdfs' => ['nullable', 'array'], 'resource_pdfs.*' => ['file', 'mimes:pdf', 'max:20480']]);
        $lines = collect(preg_split('/\r\n|\r|\n/', trim((string) $request->input('session_resources'))))->filter();
        DB::table('webinar_resources')->where('webinar_id', $webinar->id)->delete();
        foreach ($lines->values() as $order => $line) {
            [$title,$url] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
            $safeUrl = Str::startsWith($url, '/') || filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
            if ($title === '' || ! $safeUrl) {
                continue;
            }DB::table('webinar_resources')->insert(['webinar_id' => $webinar->id, 'uploaded_by' => $request->user()->id, 'title' => $title, 'type' => 'link', 'path_or_url' => $url, 'is_public' => true, 'display_order' => $order, 'created_at' => now(), 'updated_at' => now()]);
        }
        foreach ($request->file('resource_pdfs', []) as $file) {
            $directory = public_path('uploads/resources');
            File::ensureDirectoryExists($directory);
            $name = Str::uuid().'.pdf';
            $file->move($directory, $name);
            DB::table('webinar_resources')->insert(['webinar_id' => $webinar->id, 'uploaded_by' => $request->user()->id, 'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 'type' => 'file', 'path_or_url' => '/uploads/resources/'.$name, 'is_public' => true, 'display_order' => DB::table('webinar_resources')->where('webinar_id', $webinar->id)->count(), 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    private function saveAgenda(Request $request, Webinar $webinar): void
    {
        if (! $request->has('agenda_present')) {
            return;
        }
        $rows = collect($request->input('agenda', []));
        DB::table('webinar_agenda_items')->where('webinar_id', $webinar->id)->delete();
        foreach ($rows->values() as $order => $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }$time = (string) ($row['starts_at'] ?? '');
            $duration = $row['duration_minutes'] ?? null;
            DB::table('webinar_agenda_items')->insert(['webinar_id' => $webinar->id, 'title' => $title, 'description' => null, 'starts_at' => preg_match('/^\d{2}:\d{2}$/', $time) ? $time : null, 'duration_minutes' => is_numeric($duration) ? max(1, (int) $duration) : null, 'display_order' => $order, 'created_at' => now(), 'updated_at' => now()]);
        }
    }
}
