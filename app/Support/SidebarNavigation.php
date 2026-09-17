<?php

namespace App\Support;

use App\Helpers\AdminSidebarHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SidebarNavigation
{
    public static function forPortal(bool $isAdmin): array
    {
        if ($isAdmin) {
            return AdminSidebarHelper::menu();
        }

        return self::learner();
    }

    public static function sectionLabel(bool $isAdmin): string
    {
        return $isAdmin ? 'ADMIN WORKSPACE' : 'LEARNING SPACE';
    }

    private static function learner(): array
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
