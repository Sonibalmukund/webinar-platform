<?php

namespace App\Support;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

final class SidebarNavigation
{
    public static function forPortal(bool $isAdmin): array
    {
        if ($isAdmin && auth()->check() && auth()->user()->hasRole('sub-admin')) return self::subAdmin();
        return $isAdmin ? self::admin() : self::learner();
    }

    public static function sectionLabel(bool $isAdmin): string
    {
        return $isAdmin ? 'MANAGEMENT' : 'LEARNING SPACE';
    }

    private static function admin(): array
    {
        return [
            self::item('Dashboard', 'grid-1x2', '/admin/dashboard'),
            self::item('Webinars', 'camera-video', '/admin/webinars'),
            self::item('Chat', 'chat-dots', '/admin/chats'),
            self::item('Comments', 'chat-square-text', '/admin/comments'),
            self::item('Feedback', 'star', '/admin/feedback'),
            self::item('Polls', 'bar-chart', '/admin/polls'),
            self::item('Certificates', 'award', '/admin/certificates'),
            self::item('Certificate Queue', 'patch-check', '/admin/certificate-queue'),
            self::item('Users', 'people', '/admin/users'),
            self::item('User Attendance', 'person-video3', '/admin/attendance'),
            self::item('Registrations', 'person-check', '/admin/registrations'),
            self::item('Registration Form', 'ui-checks-grid', '/admin/registration-settings'),
            self::item('Sub Admins', 'shield-check', '/admin/sub-admins'),
            self::item('Permissions', 'key', '/admin/permissions'),
            self::item('Reports', 'graph-up', '/admin/reports'),
            self::item('Notifications', 'bell', '/admin/notifications'),
            self::item('Activity Logs', 'clock-history', '/admin/activity-logs'),
            self::group('General Settings', 'gear', 'generalSettingsMenu', [
                self::item('Site Settings', null, '/admin/general-settings/site'),
                self::item('Banners', null, '/admin/general-settings/banners'),
                self::item('Speakers', null, '/admin/speakers'),
                self::item('Brands', null, '/admin/general-settings/brands'),
                self::item('Content', null, '/admin/cms'),
            ]),
        ];
    }

    private static function subAdmin(): array
    {
        return [
            self::item('Dashboard','grid-1x2','/sub-admin/dashboard'),
            self::item('Assigned chats','chat-dots','/sub-admin/chats'),
        ];
    }

    private static function learner(): array
    {
        $userId=auth()->id();
        $hasRecordings=$userId && DB::table('webinar_recordings')->join('webinars','webinars.id','=','webinar_recordings.webinar_id')->join('registrations','registrations.webinar_id','=','webinars.id')->where('registrations.user_id',$userId)->where('webinars.status','completed')->where('webinar_recordings.status','published')->exists();
        $hasCertificates=$userId && DB::table('certificates')->where('user_id',$userId)->where('status','approved')->whereNull('revoked_at')->exists();
        $hasBookmarks=$userId && DB::table('webinar_bookmarks')->where('user_id',$userId)->exists();
        return array_values(array_filter([
            self::item('Dashboard', 'grid-1x2', '/dashboard', true),
            self::item('Discover', 'compass', '/webinars'),
            self::item('My Webinars', 'camera-video', '/my-webinars'),
            $hasRecordings ? self::item('Recordings', 'play-btn', '/recordings') : null,
            $hasCertificates ? self::item('Certificates', 'award', '/certificates') : null,
            $hasBookmarks ? self::item('Bookmarks', 'bookmark', '/bookmarks') : null,
            self::item('Profile', 'person', '/profile'),
        ]));
    }

    private static function item(string $label, ?string $icon, string $url, bool $exact = false): array
    {
        $path = '/'.ltrim(request()->path(), '/');
        $active = $exact ? $path === $url : ($path === $url || Str::startsWith($path, rtrim($url, '/').'/'));
        return compact('label', 'icon', 'url', 'active') + ['type' => 'item'];
    }

    private static function group(string $label, string $icon, string $id, array $children): array
    {
        return compact('label', 'icon', 'id', 'children') + ['type' => 'group', 'active' => collect($children)->contains('active', true)];
    }
}
