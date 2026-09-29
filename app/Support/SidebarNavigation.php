<?php

namespace App\Support;

use App\Helpers\AdminSidebarHelper;
use Illuminate\Support\Str;

final class SidebarNavigation
{
    public static function forPortal(bool $isAdmin): array
    {
        if ($isAdmin) {
            return AdminSidebarHelper::menu();
        }

        return self::attendee();
    }

    public static function sectionLabel(bool $isAdmin): string
    {
        return $isAdmin ? '' : 'LEARNING SPACE';
    }

    private static function attendee(): array
    {
        return [
            self::item('Dashboard', 'grid-1x2', '/dashboard', true),
        ];
    }

    private static function item(string $label, ?string $icon, string $url, bool $exact = false): array
    {
        $path = '/'.ltrim(request()->path(), '/');
        $active = $exact ? $path === $url : ($path === $url || Str::startsWith($path, rtrim($url, '/').'/'));

        return compact('label', 'icon', 'url', 'active') + ['type' => 'item'];
    }
}
