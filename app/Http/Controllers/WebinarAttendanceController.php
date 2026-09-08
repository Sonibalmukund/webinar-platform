<?php

namespace App\Http\Controllers;

use App\Events\WebinarAttendanceUpdated;
use App\Models\Webinar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebinarAttendanceController extends Controller
{
    public function join(Request $request, Webinar $webinar): JsonResponse { return $this->touch($request,$webinar,'join'); }
    public function heartbeat(Request $request, Webinar $webinar): JsonResponse { return $this->touch($request,$webinar,'heartbeat'); }
    public function leave(Request $request, Webinar $webinar): JsonResponse { return $this->touch($request,$webinar,'leave'); }

    private function touch(Request $request, Webinar $webinar, string $state): JsonResponse
    {
        abort_unless($webinar->registrations()->where('user_id',$request->user()->id)->exists(),403);
        abort_unless($webinar->canEnter(),409,'The webinar room is not open.');
        $now=now();
        $row=DB::transaction(function() use($request,$webinar,$state,$now){
            $current=DB::table('webinar_attendees')->where(['webinar_id'=>$webinar->id,'user_id'=>$request->user()->id])->lockForUpdate()->first();
            $increment=$current?->last_seen_at && !$current?->left_at ? min(60,max(0,$now->diffInSeconds(\Illuminate\Support\Carbon::parse($current->last_seen_at),true))) : 0;
            $values=['last_seen_at'=>$now,'left_at'=>$state==='leave'?$now:null,'updated_at'=>$now];
            if(!$current){$values += ['webinar_id'=>$webinar->id,'user_id'=>$request->user()->id,'joined_at'=>$now,'watch_seconds'=>0,'raised_hand'=>false,'created_at'=>$now];DB::table('webinar_attendees')->insert($values);}
            else { $values['watch_seconds']=min(PHP_INT_MAX,(int)$current->watch_seconds+$increment); DB::table('webinar_attendees')->where('id',$current->id)->update($values); }
            DB::table('webinar_attendance_events')->insert(['webinar_id'=>$webinar->id,'user_id'=>$request->user()->id,'event_type'=>$state,'occurred_at'=>$now,'metadata'=>json_encode(['visibility'=>$request->input('visibility','visible')]),'created_at'=>$now,'updated_at'=>$now]);
            return DB::table('webinar_attendees')->where(['webinar_id'=>$webinar->id,'user_id'=>$request->user()->id])->first();
        });
        $live=DB::table('webinar_attendees')->where('webinar_id',$webinar->id)->whereNull('left_at')->where('last_seen_at','>=',$now->copy()->subSeconds(75))->count();
        try { broadcast(new WebinarAttendanceUpdated($webinar->id,$request->user()->id,$live,(int)$row->watch_seconds,$state)); } catch(\Throwable $e){ report($e); }
        return response()->json(['live_viewers'=>$live,'watch_seconds'=>(int)$row->watch_seconds,'state'=>$state]);
    }
}
