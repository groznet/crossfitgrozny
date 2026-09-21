<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoUploadService
{
    private const MAX_DIMENSION = 1000;

    private const JPEG_QUALITY = 78;

    /**
     * Resize/compress an uploaded photo and store it on the public disk.
     * Returns the stored relative path (for Member::photo_url).
     */
    public function store(UploadedFile $file): string
    {
        $image = $this->loadImage($file);
        $image = $this->correctOrientation($image, $file);
        $image = $this->resize($image);

        ob_start();
        imagejpeg($image, null, self::JPEG_QUALITY);
        $binary = ob_get_clean();
        imagedestroy($image);

        $path = 'photos/'.Str::uuid()->toString().'.jpg';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    /**
     * Delete a previously stored photo, if any.
     */
    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * @return \GdImage
     */
    private function loadImage(UploadedFile $file)
    {
        return match ($file->getMimeType()) {
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            default => imagecreatefromjpeg($file->getRealPath()),
        };
    }

    /**
     * Phone photos are frequently rotated per EXIF orientation; correct it.
     *
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private function correctOrientation($image, UploadedFile $file)
    {
        if ($file->getMimeType() !== 'image/jpeg') {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = $exif['Orientation'] ?? 1;

        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }

    /**
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private function resize($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longestSide = max($width, $height);

        if ($longestSide <= self::MAX_DIMENSION) {
            return $image;
        }

        $ratio = self::MAX_DIMENSION / $longestSide;
        $newWidth = (int) round($width * $ratio);
        $newHeight = (int) round($height * $ratio);

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }
}
