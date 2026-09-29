<?php

namespace App\Helpers;

use App\Models\Webinar;
use Illuminate\Support\Str;

final class AdminSidebarHelper
{
    public static function menu(): array
    {
        $user = auth()->user();
        $eventScope = Webinar::query()->when($user?->hasRole('sub-admin'), fn ($query) => $query->whereIn('id', $user->assignedWebinars()->pluck('webinars.id')));
        $chatVisible = (clone $eventScope)->where('chat_enabled', true)->exists();
        $commentsVisible = (clone $eventScope)->where('comments_enabled', true)->exists();
        $items = [
            self::item('Dashboard', 'grid-1x2', '/admin/dashboard', 'dashboard.view', false, 'Workspace'),
            self::item('Webinars', 'camera-video', '/admin/webinars', 'webinars.view', true, 'Workspace'),
            self::group('Webinar Setup', 'sliders', 'webinarSetupSubmenu', array_filter([
                self::childItem('Dynamic Fields', '/admin/dynamic-fields', 'dynamic-fields.view'),
                self::childItem('Speakers', '/admin/speakers', 'speakers.view'),
            ]), '', false, 'Workspace'),
            self::group('Audience', 'people', 'audienceSubmenu', array_filter([
                self::childItem('Users', '/admin/users', 'users.view'),
                self::childItem('Attendance', '/admin/attendance', 'attendance.view'),
            ]), '', false, 'Workspace'),
            self::group('Engagement', 'chat-square-heart', 'engagementSubmenu', array_filter([
                $chatVisible ? self::childItem('Live Chat', '/admin/chats', 'chat.view') : null,
                $commentsVisible ? self::childItem('Comments', '/admin/comments', 'q-and-a.view') : null,
                self::childItem('Polls', '/admin/polls', 'polls.view'),
                self::childItem('Poll Logs', '/admin/poll-logs', 'poll-logs.view'),
                self::childItem('Feedback', '/admin/feedback', 'feedback.view'),
            ]), '', false, 'Manage'),
            self::group('Certificates', 'award', 'certificateSubmenu', array_filter([
                self::childItem('Certificates', '/admin/certificates', 'certificates.view'),
                self::childItem('Certificate Logs', '/admin/certificates/logs', 'certificate-logs.view'),
            ]), '', false, 'Manage'),
            self::group('Communication & Reports', 'megaphone', 'communicationSubmenu', array_filter([
                self::childItem('Notifications', '/admin/notifications', 'notifications.view'),
                self::childItem('Email Logs', '/admin/notifications/email-logs', 'notifications.view'),
                self::childItem('Reports', '/admin/reports', 'reports.view'),
            ]), '', false, 'Manage'),
            self::group('Administration', 'gear', 'administrationSubmenu', array_filter([
                self::childItem('Sub Admins', '/admin/sub-admins', 'subadmins.view', true),
                self::childItem('Roles / Permissions', '/admin/permissions', 'permissions.view', true),
                self::childItem('Site Settings', '/admin/general-settings/site', 'settings.view', true),
                self::childItem('Banners', '/admin/general-settings/banners', 'settings.view', true),
                self::childItem('Brands', '/admin/general-settings/brands', 'settings.view', true),
            ]), '', false, 'Manage'),
        ];

        return array_values(array_filter($items, function (array $item) {
            return ($item['type'] ?? '') === 'group'
                ? count($item['children']) > 0
                : self::allowed($item['permission'], $item['super_admin_only']);
        }));
    }

    private static function item(string $title, string $icon, string $route, string $permission, bool $superAdminOnly = false, string $section = 'Management'): array
    {
        $path = '/'.ltrim(request()->path(), '/');
        $active = $path === $route || Str::startsWith($path, rtrim($route, '/').'/');
        if ($route === '/admin/certificates' && Str::startsWith($path, '/admin/certificates/logs')) {
            $active = false;
        }
        if ($route === '/admin/registrations') {
            $active = Str::startsWith($path, '/admin/registrations');
        }
        if ($route === '/admin/users') {
            $active = $path === '/admin/users';
        }
        if ($route === '/admin/dynamic-fields') {
            $active = $active || Str::startsWith($path, '/admin/registration-settings/') || Str::startsWith($path, '/admin/webinar-registration-fields/');
        }
        if ($route === '/admin/profile' && $path === '/admin/change-password') {
            $active = true;
        }

        return compact('title', 'icon', 'route', 'permission') + [
            'type' => 'item',
            'label' => $title,
            'active' => $active,
            'active_routes' => [$route],
            'children' => [],
            'super_admin_only' => $superAdminOnly,
            'section' => $section,
        ];
    }

    private static function group(string $label, string $icon, string $id, array $children, string $permission, bool $superAdminOnly = false, string $section = 'Administration'): array
    {
        $active = collect($children)->contains('active', true);

        return [
            'type' => 'group',
            'title' => $label,
            'label' => $label,
            'icon' => $icon,
            'id' => $id,
            'children' => $children,
            'active' => $active,
            'permission' => $permission,
            'super_admin_only' => $superAdminOnly,
            'section' => $section,
        ];
    }

    private static function childItem(string $label, string $url, string $permission = '', bool $superAdminOnly = false): ?array
    {
        if (! self::allowed($permission, $superAdminOnly)) {
            return null;
        }
        $path = '/'.ltrim(request()->path(), '/');
        $active = $path === $url || Str::startsWith($path, rtrim($url, '/').'/');
        if ($url === '/admin/certificates' && Str::startsWith($path, '/admin/certificates/logs')) {
            $active = false;
        }
        if ($url === '/admin/dynamic-fields') {
            $active = $active || Str::startsWith($path, '/admin/registration-settings/') || Str::startsWith($path, '/admin/webinar-registration-fields/');
        }
        if ($url === '/admin/notifications') {
            $active = $path === $url || ($active && ! Str::startsWith($path, '/admin/notifications/email-logs'));
        }

        return compact('label', 'url', 'active');
    }

    private static function allowed(string $permission, bool $superAdminOnly = false): bool
    {
        $user = auth()->user();
        if ($user?->hasRole('super-admin')) {
            return true;
        }
        if ($superAdminOnly) {
            return false;
        }
        if ($permission === '' || $user?->hasPermission($permission)) {
            return true;
        }

        return $permission === 'users.view' && $user?->hasPermission('registrations.view');
    }
}
