<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Compress and store an uploaded image.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int $width
     * @param int $quality
     * @return array [original_path, compressed_path]
     */
    public function compressAndStore(UploadedFile $file, string $directory, int $width = 800, int $quality = 70): array
    {
        Log::info("ImageService: Starting compression for " . $file->getClientOriginalName());
        
        // Store original image
        $originalPath = $file->store($directory, 'public');
        Log::info("ImageService: Original stored at " . $originalPath);

        // Create compressed version
        $filename = pathinfo($originalPath, PATHINFO_FILENAME) . '_compressed.webp';
        $compressedPath = $directory . '/' . $filename;

        try {
            // Read image from storage
            $imageData = Storage::disk('public')->get($originalPath);
            Log::info("ImageService: Read " . strlen($imageData) . " bytes from original");
            
            $image = $this->manager->read($imageData);

            // Resize if needed
            if ($image->width() > $width) {
                $image->scale(width: $width);
                Log::info("ImageService: Resized image to " . $width . "px width");
            }

            // Encode to WebP for better compression
            $encoded = $image->toWebp($quality);

            // Save compressed image
            Storage::disk('public')->put($compressedPath, (string) $encoded);
            Log::info("ImageService: Compressed image stored at " . $compressedPath);
        } catch (\Exception $e) {
            Log::error("ImageService Error: " . $e->getMessage());
            // We still have the original, so we can return it at least
            return [
                'original' => $originalPath,
                'compressed' => null,
            ];
        }

        return [
            'original' => $originalPath,
            'compressed' => $compressedPath,
        ];
    }

    /**
     * Delete both original and compressed images.
     *
     * @param string|null $originalPath
     * @param string|null $compressedPath
     * @return void
     */
    public function deleteImages(?string $originalPath, ?string $compressedPath): void
    {
        if ($originalPath) {
            Storage::disk('public')->delete($originalPath);
        }
        if ($compressedPath) {
            Storage::disk('public')->delete($compressedPath);
        }
    }
}
