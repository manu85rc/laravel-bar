<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    public function optimizeAndStore(
        UploadedFile $file,
        string $path,
        int $maxWidth,
        int $maxHeight,
        bool $convertToWebp = true,
        int $quality = 85
    ): string {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['svg', 'ico'])) {
            $filename = uniqid().'.'.$extension;
            $fullPath = $path.'/'.$filename;
            Storage::disk('public')->put($fullPath, file_get_contents($file->getRealPath()));

            return $fullPath;
        }

        $image = $this->manager->decode($file->getRealPath());
        $image->resizeDown(width: $maxWidth, height: $maxHeight);

        if ($convertToWebp) {
            $filename = uniqid().'.webp';
            $encoded = $image->encodeUsingFileExtension('webp', quality: $quality);
        } else {
            $filename = uniqid().'.'.$extension;
            $encoded = $image->encodeUsingFileExtension($extension, quality: $quality);
        }

        $fullPath = $path.'/'.$filename;
        Storage::disk('public')->put($fullPath, (string) $encoded);

        return $fullPath;
    }

    public function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
