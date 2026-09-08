<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserNotificationCreated;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View { return view('pages.admin.notifications.index',['campaigns'=>DB::table('notification_campaigns')->leftJoin('webinars','webinars.id','=','notification_campaigns.webinar_id')->select('notification_campaigns.*','webinars.title as webinar_title')->latest('notification_campaigns.id')->paginate(20)]); }
    public function create(): View { return view('pages.admin.notifications.create',['webinars'=>Webinar::orderBy('title')->get(['id','title'])]); }
    public function store(Request $request): RedirectResponse
    {
        $data=$request->validate(['subject'=>['required','string','max:255'],'message'=>['required','string','max:3000'],'audience'=>['required','in:all_learners,webinar'],'webinar_id'=>['nullable','required_if:audience,webinar','exists:webinars,id']]);
        $users=User::query()->whereHas('roles',fn($q)=>$q->where('slug','learner'))
            ->when($data['audience']==='webinar',fn($q)=>$q->whereHas('registrations',fn($registration)=>$registration->where('webinar_id',$data['webinar_id'])))->get();
        $campaign=DB::table('notification_campaigns')->insertGetId(['created_by'=>$request->user()->id,'webinar_id'=>$data['webinar_id']??null,'subject'=>$data['subject'],'message'=>$data['message'],'channels'=>json_encode(['in_app']),'audience'=>$data['audience'],'status'=>'sent','sent_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
        foreach($users as $user){$id=(string)Str::uuid();$payload=['id'=>$id,'campaign_id'=>$campaign,'subject'=>$data['subject'],'message'=>$data['message'],'webinar_id'=>$data['webinar_id']??null,'created_at'=>now()->toIso8601String()];DB::table('user_notifications')->insert(['id'=>$id,'user_id'=>$user->id,'type'=>'campaign','data'=>json_encode($payload),'created_at'=>now(),'updated_at'=>now()]);try{broadcast(new UserNotificationCreated($user->id,$payload));}catch(\Throwable $e){report($e);}}
        return redirect()->route('admin.notifications.index')->with('status',$users->count().' users notified.');
    }
}
