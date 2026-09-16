<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PermissionController extends Controller
{
    private const ASSIGNABLE_MODULES = [
        'dashboard', 'webinars', 'dynamic-fields', 'speakers', 'users', 'registrations',
        'attendance', 'chat', 'q-and-a', 'polls', 'poll-logs', 'feedback', 'certificates',
        'certificate-logs', 'notifications', 'reports', 'live-control',
    ];

    public function index(): View
    {
        $subAdmins = User::whereHas('roles', fn ($query) => $query->where('slug', 'sub-admin'))
            ->withCount('assignedWebinars')->latest()->get();
        $permissionCounts = DB::table('user_webinar_permissions')
            ->select('user_id', DB::raw('COUNT(DISTINCT permission_id) as permissions_count'))
            ->groupBy('user_id')->pluck('permissions_count', 'user_id');

        return view('pages.admin.permissions.index', compact('subAdmins', 'permissionCounts'));
    }

    public function create(Request $request): View
    {
        $selectedUser = $request->integer('user_id') ? User::find($request->integer('user_id')) : null;
        if ($selectedUser && ! $selectedUser->hasRole('sub-admin')) {
            $selectedUser = null;
        }
        $selectedUser ??= User::whereHas('roles', fn ($query) => $query->where('slug', 'sub-admin'))
            ->orderBy('name')
            ->first();

        return $this->form($selectedUser);
    }

    public function edit(User $user): View
    {
        abort_unless($user->hasRole('sub-admin'), 404);

        return $this->form($user);
    }

    private function form(?User $selectedUser = null): View
    {
        $assigned = $selectedUser
            ? DB::table('user_webinar_permissions')->where('user_id', $selectedUser->id)->distinct()->pluck('permission_id')
            : collect();
        $permissions = Permission::whereIn('module', self::ASSIGNABLE_MODULES)
            ->orderByRaw("CASE WHEN module = 'dashboard' THEN 0 ELSE 1 END")
            ->orderBy('module')->orderBy('name')->get()->groupBy('module');
        $subAdmins = User::whereHas('roles', fn ($query) => $query->where('slug', 'sub-admin'))
            ->withCount('assignedWebinars')->orderBy('name')->get();

        return view('pages.admin.permissions.form', compact('subAdmins', 'permissions', 'assigned') + [
            'selectedUserId' => $selectedUser?->id,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')->where(fn ($query) => $query->whereIn('module', self::ASSIGNABLE_MODULES))],
        ]);
        $user = User::findOrFail($data['user_id']);
        abort_unless($user->hasRole('sub-admin'), 422, 'Permissions can only be assigned to a sub admin.');
        $webinarIds = $user->assignedWebinars()->pluck('webinars.id');
        if ($webinarIds->isEmpty()) {
            throw ValidationException::withMessages(['user_id' => 'Assign at least one webinar to this sub admin before saving permissions.']);
        }

        DB::transaction(function () use ($data, $request, $webinarIds) {
            DB::table('user_webinar_permissions')->where('user_id', $data['user_id'])->delete();
            $now = now();
            $rows = [];
            foreach ($webinarIds as $webinarId) {
                foreach ($data['permissions'] ?? [] as $permissionId) {
                    $rows[] = ['user_id' => $data['user_id'], 'webinar_id' => $webinarId, 'permission_id' => $permissionId, 'assigned_by' => $request->user()->id, 'created_at' => $now, 'updated_at' => $now];
                }
            }
            if ($rows !== []) {
                DB::table('user_webinar_permissions')->insert($rows);
            }
        });

        return redirect()->route('admin.permissions.index')->with('status', 'Role permissions updated for all assigned webinars.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->hasRole('sub-admin'), 404);
        DB::table('user_webinar_permissions')->where('user_id', $user->id)->delete();

        return back()->with('status', 'Role permissions removed. Webinar assignments were kept.');
    }
}
