<?php

namespace App\Http\Middleware;

use App\Models\Poll;
use App\Models\RegistrationField;
use App\Models\Webinar;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = $request->user();
        abort_unless($user?->isAdmin(), 403);
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        $permission ??= $this->permissionFor($request);
        $hasPermission = $user->hasPermission($permission) || ($permission === 'users.view' && $user->hasPermission('registrations.view'));
        abort_unless($permission && $hasPermission, 403, 'You do not have permission to access this module.');

        $webinar = $request->route('webinar');
        $poll = $request->route('poll');
        if ($poll instanceof Poll) {
            $webinar = $poll->webinar;
        }
        $field = $request->route('field');
        if ($field instanceof RegistrationField) {
            $webinar = $field->form?->webinar;
        }
        if ($webinar instanceof Webinar) {
            abort_unless($user->hasAssignedWebinar($webinar), 403, 'This event is not assigned to you.');
            abort_unless($user->canForWebinar($permission, $webinar), 403, 'You do not have this permission for the selected event.');
        }

        return $next($request);
    }

    private function permissionFor(Request $request): ?string
    {
        $name = (string) $request->route()?->getName();
        if ($name === 'admin.dashboard' || str_starts_with($name, 'admin.profile') || str_starts_with($name, 'admin.password')) {
            return 'dashboard.view';
        }

        $module = match (true) {
            str_contains($name, '.dynamic-fields.') || (str_contains($name, '.registration.') && $name !== 'admin.registration-settings') => 'dynamic-fields',
            str_contains($name, '.webinars.') => 'webinars',
            str_contains($name, '.polls.') => 'polls',
            str_contains($name, '.users') => 'users',
            str_contains($name, '.registrations') || $name === 'admin.registration-settings' => 'registrations',
            str_contains($name, '.speakers.') => 'speakers',
            str_contains($name, '.reports.') => 'reports',
            str_contains($name, '.certificates.') => 'certificates',
            str_contains($name, '.attendance') => 'attendance',
            str_contains($name, '.chats.') => 'chat',
            str_contains($name, '.questions.') => 'q-and-a',
            str_contains($name, '.comments.') => 'q-and-a',
            str_contains($name, '.notifications.') => 'notifications',
            default => null,
        };
        if (! $module) {
            return null;
        }

        $action = match (true) {
            str_ends_with($name, '.create') || str_ends_with($name, '.store') => 'create',
            str_ends_with($name, '.edit') || str_ends_with($name, '.update') || str_ends_with($name, '.status') || str_ends_with($name, '.controls') => 'edit',
            str_ends_with($name, '.destroy') || str_contains($name, 'bulk-destroy') => 'delete',
            str_ends_with($name, '.export') => 'export',
            default => 'view',
        };

        return "$module.$action";
    }
}
