<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::with('user')->when($request->filled('action'), fn ($q) => $q->where('action', 'like', '%'.$request->string('action').'%'))->latest()->paginate(25)->withQueryString();

        return view('pages.admin.activity-logs', compact('logs'));
    }
}
