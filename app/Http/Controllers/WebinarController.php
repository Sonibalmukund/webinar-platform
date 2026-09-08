<?php

namespace App\Http\Controllers;

use App\Models\Webinar;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\SignupField;
use App\Support\WebinarExperience;
use App\Models\Poll;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use App\Events\WebinarChatMessageSent;
use App\Events\WebinarPollUpdated;

class WebinarController extends Controller
{
    public function index(): View
    {
        return view('pages.user.webinars', ['webinars'=>Webinar::with('creator')->whereNot('status','draft')->whereNotNull('published_at')->orderBy('starts_at')->paginate(12)]);
    }

    public function show(Webinar $webinar): View
    {
        $canPreview=auth()->check() && (auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('sub-admin'));
        abort_if($webinar->status==='draft' && !$canPreview,404);
        if ($webinar->status !== 'draft') request()->session()->put('frontend_event_slug', $webinar->slug);
        $webinar=$webinar->load(['registrationForm.fields.options','speakers','creator'])->loadCount('registrations');
        $loginField=$webinar->registrationForm?->fields->first(fn($field)=>$field->is_enabled && $field->login_enabled);
        $banners=Banner::where('webinar_id',$webinar->id)->where('is_active',true)->where(fn($q)=>$q->whereNull('starts_at')->orWhere('starts_at','<=',now()))->where(fn($q)=>$q->whereNull('ends_at')->orWhere('ends_at','>=',now()))->orderBy('display_order')->get();
        $brands=Brand::where('webinar_id',$webinar->id)->where('is_active',true)->get();
        $agenda=DB::table('webinar_agenda_items')->where('webinar_id',$webinar->id)->orderBy('display_order')->get();
        $resources=DB::table('webinar_resources')->where('webinar_id',$webinar->id)->where('is_public',true)->orderBy('display_order')->get();
        $agenda->each(function($item) use($webinar){$item->starts_at_iso=$item->starts_at && $webinar->starts_at ? $webinar->starts_at->copy()->timezone($webinar->timezone)->setTimeFromTimeString($item->starts_at)->utc()->toIso8601String() : null;});
        $authSettings=DB::table('settings')->where('group','registration')->pluck('value','key');
        $defaultCountryId=(int)($authSettings['registration_default_country_id']??0);
        $defaultStateId=(int)($authSettings['registration_default_state_id']??0);
        return view('pages.user.webinar-show', [
            'webinar'=>$webinar,
            'banners'=>$banners,
            'brands'=>$brands,
            'agenda'=>$agenda,
            'resources'=>$resources,
            'isRegistered'=>$webinar->registrations()->where('user_id',auth()->id())->where('status','approved')->exists(),
            'loginField'=>$loginField,
            'authSettings'=>$authSettings,
            'countries'=>Country::where('is_active',true)->orderBy('name')->get(),
            'states'=>State::where('country_id',$defaultCountryId)->where('is_active',true)->orderBy('name')->get(),
            'cities'=>City::where('state_id',$defaultStateId)->where('is_active',true)->orderBy('name')->get(),
            'signupFields'=>SignupField::with(['options'=>fn($q)=>$q->where('is_enabled',true)])->where('is_enabled',true)->orderBy('display_order')->get(),
        ]);
    }

    public function dashboard(Request $request, Webinar $webinar): View
    {
        abort_unless($webinar->registrations()->where('user_id',$request->user()->id)->exists(),403,'Register for this webinar before opening its dashboard.');
        $request->session()->put('frontend_event_slug', $webinar->slug);
        $webinar->load(['speakers'])->loadCount('registrations');
        $banners=Banner::where('webinar_id',$webinar->id)->where('is_active',true)->orderBy('display_order')->get();
        $brands=Brand::where('webinar_id',$webinar->id)->where('is_active',true)->get();
        $agenda=DB::table('webinar_agenda_items')->where('webinar_id',$webinar->id)->orderBy('display_order')->get();
        $resources=DB::table('webinar_resources')->where('webinar_id',$webinar->id)->where('is_public',true)->orderBy('display_order')->get();
        $agenda->each(function($item) use($webinar){$item->starts_at_iso=$item->starts_at && $webinar->starts_at ? $webinar->starts_at->copy()->timezone($webinar->timezone)->setTimeFromTimeString($item->starts_at)->utc()->toIso8601String() : null;});
        $activePoll=$webinar->polls()->with(['options'=>fn($query)=>$query->withCount('responses')])->where('status','active')->latest()->first();
        $chatMessages=$webinar->chat_enabled ? DB::table('chat_messages')->leftJoin('users','users.id','=','chat_messages.user_id')->where('chat_messages.webinar_id',$webinar->id)->whereNull('chat_messages.deleted_at')->latest('chat_messages.sent_at')->limit(20)->get(['chat_messages.*','users.name as user_name'])->reverse() : collect();
        $feedback=$webinar->feedback_enabled ? DB::table('feedback')->where(['webinar_id'=>$webinar->id,'user_id'=>$request->user()->id])->latest()->first() : null;
        $pollResponse=$activePoll ? DB::table('poll_responses')->where('poll_id',$activePoll->id)->where('user_id',$request->user()->id)->pluck('poll_option_id') : collect();
        $metrics=WebinarExperience::metrics($webinar,$request->user()->id);
        $templateId=data_get($webinar->settings,'certificate_template_id');
        if($webinar->certificate_enabled==='yes' && $templateId && $metrics['eligible']) {
            DB::table('certificates')->insertOrIgnore(['webinar_id'=>$webinar->id,'user_id'=>$request->user()->id,'template_id'=>$templateId,'credential_id'=>(string)Str::uuid(),'status'=>'pending','created_at'=>now(),'updated_at'=>now()]);
        }
        $certificate=DB::table('certificates')->where(['webinar_id'=>$webinar->id,'user_id'=>$request->user()->id])->first();
        return view('pages.user.webinar-dashboard',compact('webinar','banners','brands','agenda','resources','activePoll','chatMessages','feedback','pollResponse','metrics','certificate'));
    }

    public function chatMessages(Request $request, Webinar $webinar): JsonResponse
    {
        $this->authorizeRegistration($request,$webinar);
        abort_unless($webinar->chat_enabled,404);
        $messages = DB::table('chat_messages')->leftJoin('users','users.id','=','chat_messages.user_id')
            ->where('chat_messages.webinar_id',$webinar->id)->whereNull('chat_messages.deleted_at')
            ->orderByDesc('chat_messages.id')->limit(50)
            ->get(['chat_messages.id','chat_messages.user_id','users.name as user_name','chat_messages.message','chat_messages.sent_at','chat_messages.attachment_path','chat_messages.attachment_name','chat_messages.attachment_mime']);
        return response()->json(['messages'=>$messages->reverse()->values()]);
    }

    public function sendChat(Request $request, Webinar $webinar): RedirectResponse|JsonResponse
    {
        $this->authorizeRegistration($request,$webinar);
        abort_unless($webinar->chat_enabled,404);
        $data=$request->validate(['message'=>['required','string','max:2000']]);
        $sentAt=now();
        $id=DB::table('chat_messages')->insertGetId(['webinar_id'=>$webinar->id,'user_id'=>$request->user()->id,'message'=>$data['message'],'sent_at'=>$sentAt,'created_at'=>$sentAt,'updated_at'=>$sentAt]);
        $message=['id'=>$id,'user_id'=>$request->user()->id,'user_name'=>$request->user()->name,'message'=>$data['message'],'sent_at'=>$sentAt->toIso8601String()];
        try { broadcast(new WebinarChatMessageSent($webinar->id,$message)); } catch (\Throwable $exception) { report($exception); }
        if($request->expectsJson()) return response()->json(['message'=>$message],201);
        return back()->with('dashboard_status','Message sent.');
    }

    public function storeComment(Request $request, Webinar $webinar): RedirectResponse|JsonResponse
    {
        $this->authorizeRegistration($request,$webinar);
        abort_unless($webinar->comments_enabled,404);
        $data=$request->validate(['comment'=>['required','string','max:2000']]);
        DB::table('comments')->insert(['webinar_id'=>$webinar->id,'user_id'=>$request->user()->id,'comment'=>$data['comment'],'status'=>'visible','created_at'=>now(),'updated_at'=>now()]);
        if($request->expectsJson()) return response()->json(['message'=>'Your private comment was sent to the host.'],201);
        return back()->with('dashboard_status','Your private comment was sent to the host.');
    }

    public function storeFeedback(Request $request, Webinar $webinar): RedirectResponse|JsonResponse
    {
        $this->authorizeRegistration($request,$webinar);
        abort_unless($webinar->feedback_enabled,404);
        $data=$request->validate(['rating'=>['required','integer','between:1,5'],'message'=>['required','string','max:3000']]);
        DB::table('feedback')->updateOrInsert(['webinar_id'=>$webinar->id,'user_id'=>$request->user()->id],$data+['status'=>'new','created_at'=>now(),'updated_at'=>now()]);
        if($request->expectsJson()) return response()->json(['message'=>'Thank you. Your feedback was saved.']);
        return back()->with('dashboard_status','Thank you for your feedback.');
    }

    public function vote(Request $request, Webinar $webinar, Poll $poll): RedirectResponse
    {
        $this->authorizeRegistration($request,$webinar);
        abort_unless($webinar->polls_enabled && $poll->webinar_id===$webinar->id && $poll->status==='active',404);
        $data=$request->validate(['option_id'=>['required','integer']]);
        abort_unless($poll->options()->whereKey($data['option_id'])->exists(),422);
        if(DB::table('poll_responses')->where(['poll_id'=>$poll->id,'user_id'=>$request->user()->id])->exists()) {
            return back()->with('dashboard_status','You have already answered this poll.')->with('dashboard_toast_tone','warning');
        }
        DB::table('poll_responses')->insertOrIgnore(['poll_id'=>$poll->id,'poll_option_id'=>$data['option_id'],'user_id'=>$request->user()->id,'created_at'=>now(),'updated_at'=>now()]);
        $options=$poll->options()->withCount('responses')->get()->map(fn($option)=>['id'=>$option->id,'count'=>$option->responses_count])->all();
        try { broadcast(new WebinarPollUpdated($webinar->id,$poll->id,$options)); } catch (\Throwable $exception) { report($exception); }
        return back()->with('dashboard_status','Your vote was recorded.');
    }

    public function downloadCertificate(Request $request, Webinar $webinar): Response
    {
        $this->authorizeRegistration($request,$webinar);
        abort_unless($webinar->certificate_enabled==='yes',404);
        $certificate=DB::table('certificates')->where(['webinar_id'=>$webinar->id,'user_id'=>$request->user()->id])->where('status','approved')->first();
        abort_unless($certificate,403,'Your certificate is not approved yet.');
        $pdf=$this->certificatePdf($request->user()->name,$webinar->title,$certificate->credential_id,$certificate->issued_at ?: now());
        return response($pdf,200,['Content-Type'=>'application/pdf','Content-Disposition'=>'attachment; filename="'.Str::slug($webinar->title).'-certificate.pdf"']);
    }

    private function authorizeRegistration(Request $request, Webinar $webinar): void
    {
        abort_unless($webinar->registrations()->where('user_id',$request->user()->id)->exists(),403);
    }

    private function certificatePdf(string $name,string $title,string $credential,mixed $issuedAt): string
    {
        $escape=fn(string $text)=>str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$text);
        $lines=[['/F2',18,230,520,'CERTIFICATE OF COMPLETION'],['/F1',11,290,475,'THIS CERTIFIES THAT'],['/F2',27,220,420,$name],['/F1',12,150,370,'has successfully completed the webinar'],['/F2',18,120,325,$title],['/F1',10,190,255,'Issued: '.\Illuminate\Support\Carbon::parse($issuedAt)->format('F j, Y')],['/F1',9,150,220,'Credential ID: '.$credential]];
        $stream="0.25 0.12 0.55 rg 0 0 842 595 re f\n0.98 0.97 1 rg 25 25 792 545 re f\n0.45 0.25 0.78 RG 3 w 42 42 758 511 re S\n0.12 0.08 0.22 rg\n";
        foreach($lines as [$font,$size,$x,$y,$text])$stream.="BT {$font} {$size} Tf {$x} {$y} Td (".$escape($text).") Tj ET\n";
        $objects=["<< /Type /Catalog /Pages 2 0 R >>","<< /Type /Pages /Kids [3 0 R] /Count 1 >>","<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>","<< /Length ".strlen($stream)." >>\nstream\n{$stream}endstream","<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>","<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>"];
        $pdf="%PDF-1.4\n";$offsets=[0];foreach($objects as $index=>$object){$offsets[]=strlen($pdf);$pdf.=($index+1)." 0 obj\n{$object}\nendobj\n";}$xref=strlen($pdf);$pdf.="xref\n0 7\n0000000000 65535 f \n";for($i=1;$i<=6;$i++)$pdf.=sprintf('%010d 00000 n ',$offsets[$i])."\n";$pdf.="trailer << /Size 7 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";return $pdf;
    }

}
