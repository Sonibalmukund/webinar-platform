<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications=DB::table('user_notifications')->where('user_id',$request->user()->id)->latest()->paginate(20);
        $notifications->getCollection()->transform(function($row){$row->data=json_decode($row->data,true)?:[];return $row;});
        return view('pages.user.notifications',compact('notifications'));
    }
    public function read(Request $request,string $notification): RedirectResponse
    {
        DB::table('user_notifications')->where(['id'=>$notification,'user_id'=>$request->user()->id])->update(['read_at'=>now(),'updated_at'=>now()]);
        return back();
    }
}
