<?php

namespace App\Support;

use App\Models\Webinar;
use Illuminate\Http\Request;

class FrontendAuth
{
    public static function webinar(Request $request): ?Webinar
    {
        $routeWebinar = $request->route('webinar');
        if ($routeWebinar instanceof Webinar) {
            return $routeWebinar;
        }
        if (is_string($routeWebinar)) {
            $webinar = Webinar::where('slug', $routeWebinar)->whereNot('status', 'draft')->first();
            if ($webinar) {
                return $webinar;
            }
        }

        $returnTo = $request->input('return_to');
        if (is_string($returnTo)) {
            $webinar = self::webinarFromPath($request, $returnTo);
            if ($webinar) {
                return $webinar;
            }
        }

        // A submitted webinar ID is explicit request context and must win over
        // an intended URL or last-viewed event left behind by another tab.
        if ($request->filled('webinar_id')) {
            $webinar = Webinar::whereKey($request->integer('webinar_id'))->whereNot('status', 'draft')->first();
            if ($webinar) {
                return $webinar;
            }
        }

        $intended = $request->session()->get('url.intended');
        if (is_string($intended)) {
            $webinar = self::webinarFromPath($request, $intended);
            if ($webinar) {
                return $webinar;
            }
        }

        return Webinar::where('slug', $request->session()->get('frontend_event_slug'))->whereNot('status', 'draft')->first();
    }

    private static function webinarFromPath(Request $request, string $path): ?Webinar
    {
        $host = parse_url($path, PHP_URL_HOST);
        if ($host && $host !== $request->getHost()) {
            return null;
        }
        $path = parse_url($path, PHP_URL_PATH);
        if (is_string($path) && preg_match('#^/(?:webinars/)?([A-Za-z0-9-]+)(?:/dashboard)?$#', $path, $matches)) {
            $webinar = Webinar::where('slug', $matches[1])->whereNot('status', 'draft')->first();
            if ($webinar) {
                return $webinar;
            }
        }

        return null;
    }

    public static function landing(Request $request, ?string $modal = null): string
    {
        $webinar = self::webinar($request);
        $path = $webinar ? route('webinars.show', $webinar, false) : route('webinars.index', [], false);

        return $path.($modal ? '?auth='.$modal : '');
    }

    public static function guestRedirect(Request $request): string
    {
        if ($request->is('admin/*')) {
            return route('admin.login');
        }
        if ($request->is('sub-admin/*')) {
            return route('admin.login');
        }

        return self::landing($request, 'login');
    }
}
