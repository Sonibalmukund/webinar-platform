<?php

namespace App\Support;

final class WebinarPreferences
{
    public static function languages(): array
    {
        return [
            'en' => 'English',
            'hi' => 'Hindi (हिन्दी)',
            'bn' => 'Bengali (বাংলা)',
            'as' => 'Assamese (অসমীয়া)',
            'gu' => 'Gujarati (ગુજરાતી)',
            'mr' => 'Marathi (मराठी)',
            'pa' => 'Punjabi (ਪੰਜਾਬੀ)',
            'ta' => 'Tamil (தமிழ்)',
            'te' => 'Telugu (తెలుగు)',
            'kn' => 'Kannada (ಕನ್ನಡ)',
            'ml' => 'Malayalam (മലയാളം)',
            'or' => 'Odia (ଓଡ଼ିଆ)',
            'ur' => 'Urdu (اردو)',
            'ne' => 'Nepali (नेपाली)',
            'sa' => 'Sanskrit (संस्कृतम्)',
            'sd' => 'Sindhi (سنڌي)',
            'ks' => 'Kashmiri (कॉशुर)',
            'kok' => 'Konkani (कोंकणी)',
            'mai' => 'Maithili (मैथिली)',
            'mni' => 'Manipuri (মৈতৈলোন্)',
            'doi' => 'Dogri (डोगरी)',
            'brx' => 'Bodo (बड़ो)',
            'sat' => 'Santali (ᱥᱟᱱᱛᱟᱲᱤ)',
            'ar' => 'Arabic (العربية)',
            'es' => 'Spanish (Español)',
            'fr' => 'French (Français)',
            'de' => 'German (Deutsch)',
            'it' => 'Italian (Italiano)',
            'pt' => 'Portuguese (Português)',
            'ru' => 'Russian (Русский)',
            'ja' => 'Japanese (日本語)',
            'ko' => 'Korean (한국어)',
            'zh' => 'Chinese (中文)',
        ];
    }

    public static function timezones(): array
    {
        $aliases = [
            'Asia/Manila' => 'Asia/Manila (Philippines)',
            'Asia/Kolkata' => 'Asia/Kolkata (India / IST)',
            'Asia/Dubai' => 'Asia/Dubai (UAE)',
            'Asia/Singapore' => 'Asia/Singapore (Singapore)',
            'Asia/Kathmandu' => 'Asia/Kathmandu (Nepal)',
            'Asia/Dhaka' => 'Asia/Dhaka (Bangladesh)',
            'Asia/Colombo' => 'Asia/Colombo (Sri Lanka)',
            'Asia/Karachi' => 'Asia/Karachi (Pakistan)',
            'Asia/Bangkok' => 'Asia/Bangkok (Thailand / Vietnam)',
            'Asia/Jakarta' => 'Asia/Jakarta (Indonesia)',
            'Asia/Tokyo' => 'Asia/Tokyo (Japan)',
            'Europe/London' => 'Europe/London (UK / GMT)',
            'America/New_York' => 'America/New_York (US Eastern)',
            'America/Chicago' => 'America/Chicago (US Central)',
            'America/Los_Angeles' => 'America/Los_Angeles (US Pacific)',
            'Australia/Sydney' => 'Australia/Sydney (Australia)',
        ];

        return collect(timezone_identifiers_list())
            ->mapWithKeys(fn (string $timezone) => [$timezone => $aliases[$timezone] ?? str_replace('_', ' ', $timezone)])
            ->all();
    }

    public static function languageName(?string $code): string
    {
        return self::languages()[$code ?? ''] ?? strtoupper((string) $code);
    }
}
