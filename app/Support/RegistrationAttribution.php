<?php

namespace App\Support;

use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegistrationAttribution
{
    private const QUERY_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

    public static function capture(Request $request, Webinar $webinar): void
    {
        $values = [];
        foreach (self::QUERY_KEYS as $key) {
            if ($request->filled($key)) {
                $values[$key] = Str::limit(trim((string) $request->query($key)), 255, '');
            }
        }
        if ($request->filled('ref')) {
            $values['referral_code'] = Str::limit(trim((string) $request->query('ref')), 255, '');
        }

        $existing = (array) $request->session()->get(self::sessionKey($webinar), []);
        $referrer = (string) $request->headers->get('referer', '');
        if ($referrer !== '' && ! Str::startsWith($referrer, url('/'))) {
            $values['referrer_url'] = Str::limit($referrer, 2000, '');
        }
        if ($values !== []) {
            $values['landing_url'] = Str::limit($request->fullUrl(), 2000, '');
            $request->session()->put(self::sessionKey($webinar), array_merge($existing, $values));
        }
    }

    public static function values(Request $request, Webinar $webinar, string $fallbackSource): array
    {
        $values = (array) $request->session()->get(self::sessionKey($webinar), []);
        $source = $values['utm_source'] ?? $values['referral_code'] ?? $fallbackSource;

        return [
            'source' => Str::limit((string) $source, 255, ''),
            'utm_source' => $values['utm_source'] ?? null,
            'utm_medium' => $values['utm_medium'] ?? null,
            'utm_campaign' => $values['utm_campaign'] ?? null,
            'utm_content' => $values['utm_content'] ?? null,
            'utm_term' => $values['utm_term'] ?? null,
            'referral_code' => $values['referral_code'] ?? null,
            'referrer_url' => $values['referrer_url'] ?? null,
            'landing_url' => $values['landing_url'] ?? null,
        ];
    }

    private static function sessionKey(Webinar $webinar): string
    {
        return 'webinar_attribution.'.$webinar->id;
    }
}
