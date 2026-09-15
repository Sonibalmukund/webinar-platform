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
        return collect(timezone_identifiers_list())
            ->mapWithKeys(fn (string $timezone) => [$timezone => str_replace('_', ' ', $timezone)])
            ->all();
    }

    public static function languageName(?string $code): string
    {
        return self::languages()[$code ?? ''] ?? strtoupper((string) $code);
    }
}
