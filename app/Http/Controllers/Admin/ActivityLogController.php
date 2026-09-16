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
        $logs = ActivityLog::with('user')
            ->where(function ($q) {
                $q->where('action', 'like', 'certificate%')
                  ->orWhere('action', 'certificate.downloaded');
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('pages.admin.activity-logs', compact('logs'));
    }
}
