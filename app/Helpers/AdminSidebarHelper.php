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
            self::item('Dashboard', 'grid-1x2', '/admin/dashboard', 'dashboard.view', false, 'Overview'),
            self::item('Webinars', 'camera-video', '/admin/webinars', 'webinars.view', false, 'Webinar management'),
            self::item('Dynamic Fields', 'ui-checks-grid', '/admin/dynamic-fields', 'dynamic-fields.view', false, 'Webinar management'),
            self::item('Speakers', 'mic', '/admin/speakers', 'speakers.view', false, 'Webinar management'),
            self::item('Users', 'people', '/admin/users', 'users.view', false, 'Audience'),
            self::item('Attendance', 'person-video3', '/admin/attendance', 'attendance.view', false, 'Audience'),
            ...($chatVisible ? [self::item('Live Chat', 'chat-dots', '/admin/chats', 'chat.view', false, 'Engagement')] : []),
            ...($commentsVisible ? [self::item('Comments', 'chat-square-text', '/admin/comments', 'q-and-a.view', false, 'Engagement')] : []),
            self::item('Polls', 'bar-chart', '/admin/polls', 'polls.view', false, 'Engagement'),
            self::item('Feedback', 'star', '/admin/feedback', 'feedback.view', false, 'Engagement'),
            self::item('Certificates', 'award', '/admin/certificates', 'certificates.view', false, 'Operations'),
            self::item('Certificate Logs', 'journal-text', '/admin/certificates/logs', 'certificates.view', false, 'Operations'),
            self::item('Notifications', 'bell', '/admin/notifications', 'notifications.view', false, 'Operations'),
            self::item('Reports', 'graph-up', '/admin/reports', 'reports.view', false, 'Operations'),
            self::item('Sub Admins', 'shield-check', '/admin/sub-admins', 'subadmins.view', true, 'Administration'),
            self::item('Roles / Permissions', 'key', '/admin/permissions', 'permissions.view', true, 'Administration'),
            self::group('General Settings', 'gear', 'generalSettingsSubmenu', [
                self::childItem('Site Settings', '/admin/general-settings/site'),
                self::childItem('Banners', '/admin/general-settings/banners'),
                self::childItem('Brands', '/admin/general-settings/brands'),
            ], 'settings.view', true, 'Administration'),
        ];

        return array_values(array_filter($items, fn (array $item) => $user?->hasRole('super-admin') || (! $item['super_admin_only'] && ($user?->hasPermission($item['permission']) || ($item['permission'] === 'users.view' && $user?->hasPermission('registrations.view'))))));
    }

    private static function item(string $title, string $icon, string $route, string $permission, bool $superAdminOnly = false, string $section = 'Management'): array
    {
        $path = '/'.ltrim(request()->path(), '/');
        $active = $path === $route || Str::startsWith($path, rtrim($route, '/').'/');
        if ($route === '/admin/certificates' && Str::startsWith($path, '/admin/certificates/logs')) {
            $active = false;
        }
        if ($route === '/admin/users') {
            $active = $active || Str::startsWith($path, '/admin/registrations');
        }
        if ($route === '/admin/dynamic-fields') {
            $active = $active || Str::startsWith($path, '/admin/registration-settings/') || Str::startsWith($path, '/admin/webinar-registration-fields/');
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

    private static function childItem(string $label, string $url): array
    {
        $path = '/'.ltrim(request()->path(), '/');
        $active = $path === $url || Str::startsWith($path, rtrim($url, '/').'/');

        return compact('label', 'url', 'active');
    }
}
