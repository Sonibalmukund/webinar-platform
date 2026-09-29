<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Webinar;
use App\Support\NotificationCampaignService;
use App\Support\RichText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.notifications.index', ['campaigns' => DB::table('notification_campaigns')->leftJoin('webinars', 'webinars.id', '=', 'notification_campaigns.webinar_id')->select('notification_campaigns.*', 'webinars.title as webinar_title')->latest('notification_campaigns.id')->paginate(20)]);
    }

    public function create(): View
    {
        return view('pages.admin.notifications.create', [
            'webinars' => Webinar::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function emailLogs(Request $request): View
    {
        $query = $this->filteredEmailLogs($request);

        return view('pages.admin.notifications.email-logs', [
            'logs' => $query->latest('logs.id')->paginate(25)->withQueryString(),
            'webinars' => Webinar::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function exportEmailLogs(Request $request)
    {
        $logs = $this->filteredEmailLogs($request)->orderByDesc('logs.id')->get();

        return response()->streamDownload(function () use ($logs) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['Recipient name', 'Recipient', 'Subject', 'Webinar', 'Status', 'Sent at', 'Details']);
            foreach ($logs as $log) {
                fputcsv($stream, [
                    $log->user_name ?: 'Registered attendee',
                    $log->recipient,
                    $log->subject,
                    $log->webinar_title ?: 'All webinars',
                    strtoupper($log->status),
                    $log->sent_at ?: '',
                    $log->error_message ?: 'Sent successfully',
                ]);
            }
            fclose($stream);
        }, 'email-delivery-logs-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filteredEmailLogs(Request $request)
    {
        $query = DB::table('notification_delivery_logs as logs')
            ->join('notification_campaigns as campaigns', 'campaigns.id', '=', 'logs.campaign_id')
            ->leftJoin('webinars', 'webinars.id', '=', 'campaigns.webinar_id')
            ->leftJoin('users', 'users.id', '=', 'logs.user_id')
            ->select('logs.*', 'campaigns.subject', 'webinars.title as webinar_title', 'users.name as user_name');

        if ($request->filled('status')) {
            $query->where('logs.status', $request->string('status'));
        }
        if ($request->filled('webinar_id')) {
            $query->where('campaigns.webinar_id', $request->integer('webinar_id'));
        }
        if ($request->filled('search')) {
            $search = '%'.$request->string('search').'%';
            $query->where(function ($inner) use ($search) {
                $inner->where('logs.recipient', 'like', $search)
                    ->orWhere('campaigns.subject', 'like', $search)
                    ->orWhere('webinars.title', 'like', $search)
                    ->orWhere('users.name', 'like', $search);
            });
        }

        return $query;
    }

    public function store(Request $request, NotificationCampaignService $campaigns): RedirectResponse
    {
        $request->merge(['delivery_mode' => $request->input('delivery_mode', 'reminder_now'), 'channel' => $request->input('channel', 'email')]);
        if ($request->input('delivery_mode') === 'after_registration_email') {
            $request->merge(['audience' => 'webinar', 'channel' => 'email']);
        }
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:20000'],
            'delivery_mode' => ['required', Rule::in(['reminder_now', 'after_registration_email', 'save_template'])],
            'channel' => ['required', Rule::in(['email', 'whatsapp'])],
            'audience' => ['required', 'in:all_registrations,webinar'],
            'webinar_id' => ['nullable', Rule::requiredIf(fn () => $request->input('audience') === 'webinar' || $request->input('delivery_mode') === 'after_registration_email'), 'exists:webinars,id'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt', 'max:10240'],
        ]);
        $data['message'] = RichText::sanitize($data['message']);
        if (RichText::plain($data['message']) === '') {
            return back()->withInput()->withErrors(['message' => 'Enter a message.']);
        }
        if ($data['delivery_mode'] === 'after_registration_email') {
            $data['audience'] = 'webinar';
            $data['channel'] = 'email';
        }
        $webinar = ! empty($data['webinar_id']) ? Webinar::findOrFail($data['webinar_id']) : null;
        $attachment = ['attachment_path' => null, 'attachment_name' => null, 'attachment_mime' => null];
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachment = [
                'attachment_path' => $file->store('communication-attachments', 'public'),
                'attachment_name' => $file->getClientOriginalName(),
                'attachment_mime' => $file->getMimeType(),
            ];
        }

        $values = array_merge([
            'created_by' => $request->user()->id,
            'webinar_id' => $webinar?->id,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'channels' => json_encode([$data['channel']]),
            'audience' => $data['audience'],
            'delivery_mode' => $data['delivery_mode'],
            'status' => $data['delivery_mode'] === 'save_template' ? 'template' : ($data['delivery_mode'] === 'after_registration_email' ? 'active' : ($data['channel'] === 'whatsapp' ? 'ready' : 'draft')),
            'scheduled_at' => null,
            'sent_at' => null,
            'updated_at' => now(),
        ], $attachment);

        if ($data['delivery_mode'] === 'after_registration_email') {
            $existingId = DB::table('notification_campaigns')->where('webinar_id', $webinar->id)->where('delivery_mode', 'after_registration_email')->where('status', 'active')->value('id');
            if ($existingId) {
                DB::table('notification_campaigns')->where('id', $existingId)->update($values);

                return redirect()->route('admin.notifications.index')->with('status', 'After-registration email template updated.');
            }
        }

        $campaignId = DB::table('notification_campaigns')->insertGetId($values + ['sent_count' => 0, 'created_at' => now()]);
        if ($data['delivery_mode'] === 'save_template') {
            return redirect()->route('admin.notifications.index')->with('status', 'Communication template saved. Nothing was sent.');
        }
        if ($data['delivery_mode'] === 'reminder_now' && $data['channel'] === 'email') {
            $count = $campaigns->sendEmail($campaignId);

            return redirect()->route('admin.notifications.index')->with('status', $count.' reminder emails sent now.');
        }
        if ($data['delivery_mode'] === 'reminder_now' && $data['channel'] === 'whatsapp') {
            return redirect()->route('admin.notifications.whatsapp', $campaignId)->with('status', 'WhatsApp queue is ready. Open each recipient to send the prefilled message.');
        }

        return redirect()->route('admin.notifications.index')->with('status', 'After-registration email template activated.');
    }

    public function whatsapp(int $campaignId): View
    {
        $campaign = DB::table('notification_campaigns')->where('id', $campaignId)->first();
        abort_unless($campaign && in_array('whatsapp', json_decode($campaign->channels, true) ?: [], true), 404);
        $webinar = $campaign->webinar_id ? Webinar::find($campaign->webinar_id) : null;
        $users = User::query()->whereHas('registrations')
            ->when($campaign->audience === 'webinar', fn ($query) => $query->whereHas('registrations', fn ($registration) => $registration->where('webinar_id', $campaign->webinar_id)))
            ->whereNotNull('mobile')->get()->map(function ($user) use ($campaign, $webinar) {
                $message = strtr(RichText::plain($campaign->message), ['{name}' => $user->name, '{webinar}' => $webinar?->title ?? 'the webinar', '{date}' => $webinar?->starts_at?->timezone($webinar->timezone)->format('M d, Y · g:i A') ?? 'To be announced']);
                if ($campaign->attachment_path) {
                    $message .= "\n\nAttachment: ".url(Storage::disk('public')->url($campaign->attachment_path));
                }

                return ['user' => $user, 'url' => 'https://wa.me/'.preg_replace('/\D/', '', $user->mobile).'?text='.rawurlencode($message)];
            });

        return view('pages.admin.notifications.whatsapp', compact('campaign', 'webinar', 'users'));
    }
}
