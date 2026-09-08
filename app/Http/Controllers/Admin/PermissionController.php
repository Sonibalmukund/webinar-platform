<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(): View
    {
        $assignments = DB::table('user_webinar_permissions')->select('user_id','webinar_id',DB::raw('COUNT(*) as permissions_count'))->groupBy('user_id','webinar_id')->get();
        return view('pages.admin.permissions.index', ['assignments'=>$assignments, 'users'=>User::whereIn('id',$assignments->pluck('user_id'))->get()->keyBy('id'), 'webinars'=>Webinar::whereIn('id',$assignments->pluck('webinar_id'))->get()->keyBy('id')]);
    }

    public function create(): View { return $this->form(); }
    public function edit(User $user, Webinar $webinar): View { return $this->form($user, $webinar); }

    private function form(?User $selectedUser=null, ?Webinar $selectedWebinar=null): View
    {
        $assigned = ($selectedUser && $selectedWebinar) ? DB::table('user_webinar_permissions')->where('user_id',$selectedUser->id)->where('webinar_id',$selectedWebinar->id)->pluck('permission_id') : collect();
        return view('pages.admin.permissions.form', ['subAdmins'=>User::whereHas('roles',fn($q)=>$q->where('slug','sub-admin'))->orderBy('name')->get(), 'webinars'=>Webinar::orderBy('title')->get(), 'permissions'=>Permission::orderBy('module')->orderBy('name')->get()->groupBy('module'), 'selectedUserId'=>$selectedUser?->id, 'selectedWebinarId'=>$selectedWebinar?->id, 'assigned'=>$assigned]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'webinar_id' => ['required', 'exists:webinars,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);
        abort_unless(User::findOrFail($data['user_id'])->hasRole('sub-admin'), 422, 'Permissions can only be assigned to a sub admin.');
        DB::transaction(function () use ($data, $request) {
            DB::table('user_webinar_permissions')->where('user_id', $data['user_id'])->where('webinar_id', $data['webinar_id'])->delete();
            DB::table('user_webinar_assignments')->updateOrInsert(
                ['user_id' => $data['user_id'], 'webinar_id' => $data['webinar_id']],
                ['assigned_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]
            );
            foreach ($data['permissions'] ?? [] as $permissionId) DB::table('user_webinar_permissions')->insert([
                'user_id' => $data['user_id'], 'webinar_id' => $data['webinar_id'], 'permission_id' => $permissionId,
                'assigned_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now(),
            ]);
        });
        return redirect()->route('admin.permissions.index')->with('status', 'Event permissions updated.');
    }

    public function destroy(User $user, Webinar $webinar): RedirectResponse
    {
        DB::table('user_webinar_permissions')->where('user_id',$user->id)->where('webinar_id',$webinar->id)->delete();
        DB::table('user_webinar_assignments')->where('user_id',$user->id)->where('webinar_id',$webinar->id)->delete();
        return back()->with('status','Event permission assignment deleted.');
    }
}
