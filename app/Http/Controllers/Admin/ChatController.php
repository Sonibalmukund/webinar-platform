<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webinar;
use App\Events\WebinarChatMessageSent;
use App\Events\WebinarChatMessageDeleted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $search=trim((string)$request->input('search'));
        $webinars=Webinar::query()->when($request->user()->hasRole('sub-admin'),fn($query)=>$query->whereIn('id',$request->user()->assignedWebinars()->pluck('webinars.id')))->when($search,fn($query)=>$query->where('title','like','%'.$search.'%'))->latest('starts_at')->get();
        $stats=DB::table('chat_messages')->whereIn('webinar_id',$webinars->pluck('id'))->whereNull('deleted_at')->selectRaw('webinar_id, COUNT(*) messages_count, COUNT(DISTINCT user_id) participants_count, MAX(sent_at) last_message_at')->groupBy('webinar_id')->get()->keyBy('webinar_id');

        $routePrefix=$request->user()->hasRole('sub-admin')?'subadmin':'admin';
        return view('pages.admin.chats.index',compact('webinars','stats','search','routePrefix'));
    }

    public function show(Webinar $webinar): View
    {
        $this->authorizeWebinar(request(),$webinar);
        $messages=DB::table('chat_messages')->leftJoin('users','users.id','=','chat_messages.user_id')->where('chat_messages.webinar_id',$webinar->id)->whereNull('chat_messages.deleted_at')->orderBy('chat_messages.sent_at')->select('chat_messages.*','users.name as user_name','users.email as user_email')->get();
        $participants=$messages->whereNotNull('user_id')->groupBy('user_id')->map(function($items){$last=$items->last();return (object)['id'=>$last->user_id,'name'=>$last->user_name ?: 'Deleted user','email'=>$last->user_email,'messages_count'=>$items->count(),'last_message_at'=>$last->sent_at];})->sortByDesc('messages_count')->values();

        $routePrefix=request()->user()->hasRole('sub-admin')?'subadmin':'admin';
        return view('pages.admin.chats.show',compact('webinar','messages','participants','routePrefix'));
    }

    public function store(Request $request,Webinar $webinar): RedirectResponse|JsonResponse
    {
        $this->authorizeWebinar($request,$webinar);
        $data=$request->validate(['message'=>['nullable','string','max:2000','required_without:attachment'],'attachment'=>['nullable','file','mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,mp4,webm,mov,mp3,wav','max:20480']]);
        $attachment=[];
        if($request->hasFile('attachment')){$file=$request->file('attachment');$directory=public_path('uploads/chat');File::ensureDirectoryExists($directory);$name=Str::uuid().'.'.$file->getClientOriginalExtension();$attachment=['attachment_path'=>'/uploads/chat/'.$name,'attachment_name'=>$file->getClientOriginalName(),'attachment_mime'=>$file->getMimeType()];$file->move($directory,$name);}
        $sentAt = now();
        $id = DB::table('chat_messages')->insertGetId(array_merge(['webinar_id'=>$webinar->id,'user_id'=>$request->user()->id,'message'=>$data['message']??'','sent_at'=>$sentAt,'created_at'=>$sentAt,'updated_at'=>$sentAt],$attachment));
        $message = array_merge(['id'=>$id,'user_id'=>$request->user()->id,'user_name'=>$request->user()->name,'message'=>$data['message']??'','sent_at'=>$sentAt->toIso8601String()],$attachment);
        try { broadcast(new WebinarChatMessageSent($webinar->id, $message)); } catch (\Throwable $exception) { report($exception); }
        if ($request->expectsJson()) return response()->json(['message'=>$message],201);
        return redirect()->route(($request->user()->hasRole('sub-admin')?'subadmin':'admin').'.chats.show',$webinar)->with('success','Message sent successfully.');
    }

    public function destroy(Request $request,Webinar $webinar,int $message): RedirectResponse|JsonResponse
    {
        $this->authorizeWebinar($request,$webinar);
        $deleted = DB::table('chat_messages')->where('webinar_id',$webinar->id)->where('id',$message)->whereNull('deleted_at')->update(['is_moderated'=>true,'deleted_at'=>now(),'updated_at'=>now()]);
        abort_unless($deleted,404);
        try { broadcast(new WebinarChatMessageDeleted($webinar->id,$message)); } catch (\Throwable $exception) { report($exception); }
        if ($request->expectsJson()) return response()->json(['id'=>$message]);
        return redirect()->route(($request->user()->hasRole('sub-admin')?'subadmin':'admin').'.chats.show',$webinar)->with('success','Message removed from the chat.');
    }

    private function authorizeWebinar(Request $request,Webinar $webinar): void
    {
        abort_unless($request->user()->hasRole('super-admin') || ($request->user()->hasRole('sub-admin') && $request->user()->assignedWebinars()->whereKey($webinar->id)->exists()),403);
    }
}
