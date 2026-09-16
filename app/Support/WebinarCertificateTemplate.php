<?php

namespace App\Support;

use App\Models\CertificateTemplate;
use App\Models\Webinar;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WebinarCertificateTemplate
{
    public const ELEMENT_VISIBILITY_DEFAULTS = [
        'headline' => true,
        'recipient' => true,
        'webinar' => true,
        'date' => true,
        'signature' => true,
        'signatory' => true,
    ];

    /**
     * Return a template that can safely be edited for only this webinar.
     * Shared templates are copied first so another webinar's design never changes.
     */
    public static function editableCopy(?CertificateTemplate $template, Webinar $webinar, int $createdBy): ?CertificateTemplate
    {
        if (! $template) {
            return null;
        }

        $usedByAnotherWebinar = Webinar::query()
            ->whereKeyNot($webinar->getKey())
            ->get(['settings'])
            ->contains(fn (Webinar $candidate) => (int) data_get($candidate->settings, 'certificate_template_id') === (int) $template->getKey());
        if (! $usedByAnotherWebinar) {
            return $template;
        }

        $copy = $template->replicate();
        $copy->created_by = $createdBy;
        $copy->save();

        return $copy;
    }

    public static function imageDimensions(UploadedFile $file): array
    {
        $dimensions = @getimagesize($file->getRealPath());
        if (! $dimensions || empty($dimensions[0]) || empty($dimensions[1])) {
            return [];
        }

        return [
            'image_width' => (int) $dimensions[0],
            'image_height' => (int) $dimensions[1],
            'canvas_aspect_ratio' => round($dimensions[0] / $dimensions[1], 6),
        ];
    }

    public static function aspectRatio(?CertificateTemplate $template): float
    {
        $design = $template?->design ?? [];
        $stored = (float) data_get($design, 'canvas_aspect_ratio', 0);
        if ($stored > 0) {
            return $stored;
        }

        $path = self::localPublicPath(data_get($design, 'template_image'));
        if ($path && ($dimensions = @getimagesize($path)) && ! empty($dimensions[1])) {
            return round($dimensions[0] / $dimensions[1], 6);
        }

        return $template?->orientation === 'portrait' ? 0.707107 : 1.414214;
    }

    public static function visibleElements(array $design): array
    {
        return array_replace(self::ELEMENT_VISIBILITY_DEFAULTS, data_get($design, 'visible_elements', []));
    }

    public static function localPublicPath(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        if (str_starts_with($path, '/storage/')) {
            $relative = ltrim(substr($path, 9), '/');
            $candidate = Storage::disk('public')->path($relative);

            return is_file($candidate) ? $candidate : null;
        }

        $candidate = public_path(ltrim($path, '/'));

        return is_file($candidate) ? $candidate : null;
    }
}
