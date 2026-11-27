<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class ImageHelper
{
    public static function toCompressedBase64(UploadedFile $file, int $maxWidth = 1080, int $quality = 75): string
    {
        $path = $file->getRealPath() ?: $file->getPathname();
        $raw = file_get_contents($path);
        $info = @getimagesizefromstring($raw);
        $mime = $info['mime'] ?? $file->getMimeType() ?? 'image/jpeg';

        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecreatetruecolor')) {
            return 'data:' . $mime . ';base64,' . base64_encode($raw);
        }

        $source = @imagecreatefromstring($raw);
        if (! $source) {
            return 'data:' . $mime . ';base64,' . base64_encode($raw);
        }

        $origWidth = imagesx($source);
        $origHeight = imagesy($source);

        if ($origWidth > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) round(($origHeight / $origWidth) * $maxWidth);
        } else {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        }

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        $currentQuality = $quality;
        $binary = '';
        $maxBytes = 240 * 1024;

        do {
            ob_start();
            imagejpeg($canvas, null, $currentQuality);
            $binary = ob_get_clean();
            $currentQuality -= 10;
        } while ($binary && strlen($binary) > $maxBytes && $currentQuality >= 40);

        imagedestroy($source);
        imagedestroy($canvas);

        if (! $binary) {
            return 'data:' . $mime . ';base64,' . base64_encode($raw);
        }

        return 'data:image/jpeg;base64,' . base64_encode($binary);
    }

    public static function resolveUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (str_starts_with($value, 'data:image') || str_starts_with($value, 'http')) {
            return $value;
        }

        return asset('storage/' . $value);
    }
}
