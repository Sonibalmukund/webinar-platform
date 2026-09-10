<?php

namespace App\Http\Controllers\Admin;

use App\Events\WebinarRoomUpdated;
use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use App\Models\Webinar;
use App\Support\AuditTrail;
use App\Support\WebinarExperience;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $selectedWebinarId = $request->filled('webinar_id') ? $request->integer('webinar_id') : null;
        $search = trim($request->input('search', ''));

        $allWebinars = Webinar::when($user->hasRole('sub-admin'), fn ($query) => $query->whereIn('id', $user->assignedWebinars()->pluck('webinars.id')))->orderBy('title')->get();

        $webinars = Webinar::when($user->hasRole('sub-admin'), fn ($query) => $query->whereIn('id', $user->assignedWebinars()->pluck('webinars.id')))
            ->when($selectedWebinarId, fn ($query) => $query->where('id', $selectedWebinarId))
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.certificates.index', [
            'webinars' => $webinars,
            'allWebinars' => $allWebinars,
            'selectedWebinarId' => $selectedWebinarId,
            'search' => $search,
            'templates' => CertificateTemplate::orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->filled('webinar_id')) {
            return redirect()->route('admin.certificates.edit', Webinar::findOrFail($request->integer('webinar_id')));
        }

        return view('pages.admin.certificates.create', ['webinars' => Webinar::when($request->user()->hasRole('sub-admin'), fn ($query) => $query->whereIn('id', $request->user()->assignedWebinars()->pluck('webinars.id')))->orderBy('title')->get()]);
    }

    public function edit(Webinar $webinar): View
    {
        $templateId = data_get($webinar->settings, 'certificate_template_id');

        return view('pages.admin.certificates.form', [
            'webinar' => $webinar,
            'template' => $templateId ? CertificateTemplate::find($templateId) : null,
        ]);
    }

    public function update(Request $request, Webinar $webinar): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'orientation' => ['required', 'in:landscape,portrait'],
            'headline' => ['required', 'string', 'max:255'],
            'signatory' => ['nullable', 'string', 'max:255'],
            'template_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:10240'],
            'font_file' => ['nullable', 'file', 'mimes:ttf,otf,woff,woff2', 'max:5120'],
            'signature_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
            'positions' => ['required', 'array'],
            'positions.*.x' => ['required', 'numeric', 'between:0,100'],
            'positions.*.y' => ['required', 'numeric', 'between:0,100'],
            'positions.*.width' => ['required', 'numeric', 'between:5,90'],
            'positions.*.scale' => ['required', 'numeric', 'between:50,200'],
        ]);
        $templateId = data_get($webinar->settings, 'certificate_template_id');
        $template = $templateId ? CertificateTemplate::find($templateId) : null;
        $existingDesign = $template?->design ?? [];
        $uploadDirectory = public_path('uploads/certificates');
        File::ensureDirectoryExists($uploadDirectory);
        $imagePath = $existingDesign['template_image'] ?? null;
        $fontPath = $existingDesign['font_file'] ?? null;
        $signaturePath = $existingDesign['signature_image'] ?? null;
        if ($request->hasFile('template_image')) {
            $file = $request->file('template_image');
            $name = Str::uuid().'.'.$file->getClientOriginalExtension();
            $file->move($uploadDirectory, $name);
            $imagePath = '/uploads/certificates/'.$name;
        }
        if ($request->hasFile('font_file')) {
            $file = $request->file('font_file');
            $name = Str::uuid().'.'.$file->getClientOriginalExtension();
            $file->move($uploadDirectory, $name);
            $fontPath = '/uploads/certificates/'.$name;
        }
        if ($request->hasFile('signature_image')) {
            $file = $request->file('signature_image');
            $name = Str::uuid().'.'.$file->getClientOriginalExtension();
            $file->move($uploadDirectory, $name);
            $signaturePath = '/uploads/certificates/'.$name;
        }
        $values = [
            'name' => $data['name'], 'orientation' => $data['orientation'],
            'design' => [
                'headline' => $data['headline'], 'signatory' => $data['signatory'],
                'template_image' => $imagePath,
                'font_file' => $fontPath, 'font_family' => $fontPath ? 'CustomCertificateFont' : 'Manrope',
                'signature_image' => $signaturePath,
                'positions' => $data['positions'],
            ],
            'created_by' => $template?->created_by ?? $request->user()->id,
        ];
        $template ? $template->update($values) : $template = CertificateTemplate::create($values);
        $settings = $webinar->settings ?? [];
        $settings['certificate_template_id'] = $template->id;
        $webinar->update(['settings' => $settings, 'certificate_enabled' => 'yes']);

        return redirect()->route('admin.certificates.index')->with('status', 'Certificate template saved and enabled.');
    }

    public function visibility(Request $request, Webinar $webinar): RedirectResponse
    {
        $data = $request->validate(['enabled' => ['required', 'boolean']]);
        $webinar->update(['certificate_enabled' => $data['enabled'] ? 'yes' : 'no']);
        try {
            broadcast(new WebinarRoomUpdated($webinar->id, 'controls', [
                'status' => $webinar->status,
                'chat_enabled' => (bool) $webinar->chat_enabled,
                'polls_enabled' => (bool) $webinar->polls_enabled,
                'comments_enabled' => (bool) $webinar->comments_enabled,
                'certificate_enabled' => $webinar->certificate_enabled === 'yes',
            ]));
        } catch (\Throwable $e) {
            report($e);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'enabled' => (bool) $data['enabled'],
                'status_text' => $data['enabled'] ? 'VISIBLE' : 'HIDDEN',
                'message' => $data['enabled'] ? 'Certificate enabled.' : 'Certificate hidden.',
            ]);
        }

        return back()->with('status', $data['enabled'] ? 'Certificate enabled.' : 'Certificate hidden.');
    }

    public function queue(): View
    {
        $webinars = Webinar::where('certificate_enabled', 'yes')->whereIn('status', ['live', 'completed'])->get();
        foreach ($webinars as $webinar) {
            $templateId = data_get($webinar->settings, 'certificate_template_id');
            if (! $templateId) {
                continue;
            }foreach ($webinar->registrations()->where('status', 'approved')->whereNotNull('user_id')->get() as $registration) {
                $metrics = WebinarExperience::metrics($webinar, $registration->user_id);
                if ($metrics['eligible']) {
                    DB::table('certificates')->insertOrIgnore(['webinar_id' => $webinar->id, 'user_id' => $registration->user_id, 'template_id' => $templateId, 'credential_id' => (string) Str::uuid(), 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        }
        $rows = DB::table('certificates')->join('users', 'users.id', '=', 'certificates.user_id')->join('webinars', 'webinars.id', '=', 'certificates.webinar_id')->select('certificates.*', 'users.name as user_name', 'users.email', 'webinars.title as webinar_title')->latest('certificates.created_at')->paginate(25);

        return view('pages.admin.certificates.queue', compact('rows'));
    }

    public function decision(Request $request, int $certificate): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:approved,rejected'], 'review_notes' => ['nullable', 'string', 'max:1000']]);
        DB::table('certificates')->where('id', $certificate)->update(['status' => $data['status'], 'reviewed_by' => $request->user()->id, 'reviewed_at' => now(), 'review_notes' => $data['review_notes'] ?? null, 'issued_at' => $data['status'] === 'approved' ? now() : null, 'updated_at' => now()]);
        AuditTrail::record('certificate.'.$data['status'], null, 'Certificate eligibility reviewed.', ['certificate_id' => $certificate]);

        return back()->with('status', 'Certificate '.$data['status'].'.');
    }

    public function logs(Request $request): View
    {
        $user = $request->user();
        $search = trim($request->input('search', ''));
        $webinarId = $request->input('webinar_id');

        $query = DB::table('certificate_downloads')
            ->join('users', 'users.id', '=', 'certificate_downloads.user_id')
            ->join('webinars', 'webinars.id', '=', 'certificate_downloads.webinar_id')
            ->select(
                'certificate_downloads.*',
                'users.name as user_name',
                'users.email as user_email',
                'webinars.title as webinar_title',
                'webinars.slug as webinar_slug'
            );

        if ($user->hasRole('sub-admin')) {
            $assignedIds = $user->assignedWebinars()->pluck('webinars.id')->all();
            $query->whereIn('certificate_downloads.webinar_id', $assignedIds);
        }

        if ($webinarId) {
            $query->where('certificate_downloads.webinar_id', $webinarId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('webinars.title', 'like', "%{$search}%")
                    ->orWhere('certificate_downloads.credential_id', 'like', "%{$search}%")
                    ->orWhere('certificate_downloads.ip_address', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest('certificate_downloads.downloaded_at')->paginate(20)->withQueryString();
        $webinars = Webinar::when($user->hasRole('sub-admin'), fn ($q) => $q->whereIn('id', $user->assignedWebinars()->pluck('webinars.id')))->orderBy('title')->get();

        return view('pages.admin.certificates.logs', compact('logs', 'webinars', 'search', 'webinarId'));
    }
}
