<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Support\WebinarExperience;
class AttendanceController extends Controller {
    private function query(int $webinarId=0){return DB::table('webinar_attendees')->join('users','users.id','=','webinar_attendees.user_id')->join('webinars','webinars.id','=','webinar_attendees.webinar_id')->when($webinarId,fn($q)=>$q->where('webinar_attendees.webinar_id',$webinarId))->select('webinar_attendees.*','users.name as user_name','users.email','users.mobile','webinars.title as webinar_title')->orderByDesc('joined_at');}
    public function index(Request $request):View{$webinarId=$request->integer('webinar_id');$rows=$this->query($webinarId)->paginate(20)->withQueryString();$webinarMap=Webinar::whereIn('id',$rows->pluck('webinar_id'))->get()->keyBy('id');$rows->getCollection()->transform(function($row)use($webinarMap){$row->metrics=WebinarExperience::metrics($webinarMap[$row->webinar_id],$row->user_id);return $row;});return view('pages.admin.attendance',['rows'=>$rows,'webinars'=>Webinar::orderBy('title')->get(),'webinarId'=>$webinarId]);}
    public function export(Request $request){$rows=$this->query($request->integer('webinar_id'))->get();return response()->streamDownload(function()use($rows){$out=fopen('php://output','w');fputcsv($out,['User','Email','Mobile','Webinar','Joined','Left','Watch seconds','Last seen']);foreach($rows as $row)fputcsv($out,[$row->user_name,$row->email,$row->mobile,$row->webinar_title,$row->joined_at,$row->left_at,$row->watch_seconds,$row->last_seen_at]);fclose($out);},'attendance-'.now()->format('Y-m-d').'.csv',['Content-Type'=>'text/csv']);}
}
