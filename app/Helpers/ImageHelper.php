<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class ImageHelper
{
    public static function toCompressedBase64(UploadedFile $file, int $maxWidth = 800, int $quality = 75): string
    {
        $path = $file->getRealPath();
        $info = getimagesize($path);
        $mime = $info['mime'] ?? 'image/jpeg';

        $source = match ($mime) {
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default => imagecreatefromjpeg($path),
        };

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

        ob_start();
        imagejpeg($canvas, null, $quality);
        $binary = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        return 'data:image/jpeg;base64,' . base64_encode($binary);
    }

    public static function resolveUrl(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (str_starts_with($value, 'data:image') || str_starts_with($value, 'http')) {
            return $value;
        }

        return asset('storage/' . $value);
    }
}
