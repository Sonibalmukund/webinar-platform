<?php

namespace App\Http\Controllers\Admin;

use App\Events\WebinarRoomUpdated;
use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use App\Models\Webinar;
use App\Support\AuditTrail;
use App\Support\WebinarExperience;
use App\Support\WebinarCertificateTemplate;
use App\Support\DynamicFieldsHelper;
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

        $accessibleIds = $user->hasRole('sub-admin') ? $user->accessibleWebinarIds() : null;

        $allWebinars = Webinar::when($accessibleIds, fn ($query) => $query->whereIn('id', $accessibleIds))->orderBy('title')->get();

        $webinars = Webinar::when($accessibleIds, fn ($query) => $query->whereIn('id', $accessibleIds))
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

        return view('pages.admin.certificates.create', ['webinars' => Webinar::when($request->user()->hasRole('sub-admin'), fn ($query) => $query->whereIn('id', $request->user()->accessibleWebinarIds()))->orderBy('title')->get()]);
    }

    public function edit(Webinar $webinar): View
    {
        $templateId = data_get($webinar->settings, 'certificate_template_id');

        return view('pages.admin.certificates.form', [
            'webinar' => $webinar,
            'template' => $templateId ? CertificateTemplate::find($templateId) : null,
        ]);
    }

    public function preview(Webinar $webinar): View
    {
        $templateId = data_get($webinar->settings, 'certificate_template_id');

        return view('pages.admin.certificates.preview', [
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
            'visible_elements' => ['nullable', 'array'],
            'visible_elements.*' => ['nullable', 'boolean'],
        ]);
        $templateId = data_get($webinar->settings, 'certificate_template_id');
        $template = $templateId ? CertificateTemplate::find($templateId) : null;
        $template = WebinarCertificateTemplate::editableCopy($template, $webinar, $request->user()->id);
        $existingDesign = $template?->design ?? [];
        $imagePath = $existingDesign['template_image'] ?? null;
        $fontPath = $existingDesign['font_file'] ?? null;
        $signaturePath = $existingDesign['signature_image'] ?? null;
        if ($request->hasFile('template_image')) {
            $file = $request->file('template_image');
            $existingDesign = array_replace($existingDesign, WebinarCertificateTemplate::imageDimensions($file));
            $extension = $file->getClientOriginalExtension();
            $name = Str::uuid().($extension ? '.'.$extension : '');
            $path = $file->storeAs('certificates', $name, 'public');
            $imagePath = '/storage/'.$path;
        }
        if ($request->hasFile('font_file')) {
            $file = $request->file('font_file');
            $extension = $file->getClientOriginalExtension();
            $name = Str::uuid().($extension ? '.'.$extension : '');
            $path = $file->storeAs('certificates', $name, 'public');
            $fontPath = '/storage/'.$path;
        }
        if ($request->hasFile('signature_image')) {
            $file = $request->file('signature_image');
            $extension = $file->getClientOriginalExtension();
            $name = Str::uuid().($extension ? '.'.$extension : '');
            $path = $file->storeAs('certificates', $name, 'public');
            $signaturePath = '/storage/'.$path;
        }
        $values = [
            'name' => $data['name'], 'orientation' => $data['orientation'],
            'design' => [
                'headline' => $data['headline'], 'signatory' => $data['signatory'] ?? null,
                'template_image' => $imagePath,
                'font_file' => $fontPath, 'font_family' => $fontPath ? 'CustomCertificateFont' : 'Manrope',
                'signature_image' => $signaturePath,
                'positions' => $data['positions'],
                'visible_elements' => $request->has('visible_elements')
                    ? collect(WebinarCertificateTemplate::ELEMENT_VISIBILITY_DEFAULTS)
                        ->mapWithKeys(fn ($default, $key) => [$key => $request->boolean('visible_elements.'.$key)])
                        ->all()
                    : WebinarCertificateTemplate::visibleElements($existingDesign),
                'image_width' => $existingDesign['image_width'] ?? null,
                'image_height' => $existingDesign['image_height'] ?? null,
                'canvas_aspect_ratio' => $existingDesign['canvas_aspect_ratio'] ?? null,
            ],
            'created_by' => $template?->created_by ?? $request->user()->id,
        ];
        $template ? $template->update($values) : $template = CertificateTemplate::create($values);
        $settings = $webinar->settings ?? [];
        $settings['certificate_template_id'] = $template->id;
        $webinar->update(['settings' => $settings, 'certificate_enabled' => 'yes']);
        DB::table('certificates')->where('webinar_id', $webinar->id)->update([
            'template_id' => $template->id,
            'updated_at' => now(),
        ]);

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
                'users.mobile as user_mobile',
                'webinars.id as webinar_id',
                'webinars.title as webinar_title',
                'webinars.slug as webinar_slug'
            );

        if ($user->hasRole('sub-admin')) {
            $assignedIds = $user->accessibleWebinarIds()->all();
            $query->whereIn('certificate_downloads.webinar_id', $assignedIds);
        }

        if ($webinarId) {
            $query->where('certificate_downloads.webinar_id', $webinarId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('users.mobile', 'like', "%{$search}%")
                    ->orWhere('webinars.title', 'like', "%{$search}%")
                    ->orWhere('certificate_downloads.ip_address', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest('certificate_downloads.downloaded_at')->paginate(20)->withQueryString();

        $dynamicColumns = DynamicFieldsHelper::attach($logs, $webinarId ? [$webinarId] : null);

        $webinars = Webinar::when($user->hasRole('sub-admin'), fn ($q) => $q->whereIn('id', $user->accessibleWebinarIds()))->orderBy('title')->get();

        return view('pages.admin.certificates.logs', compact('logs', 'webinars', 'search', 'webinarId', 'dynamicColumns'));
    }
}
