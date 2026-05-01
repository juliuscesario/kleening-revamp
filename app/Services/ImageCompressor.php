<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Image;

class ImageCompressor
{
    /**
     * Compress an uploaded image and store it.
     *
     * @param UploadedFile $file      The uploaded file
     * @param string       $directory Storage directory (relative to storage/app/public)
     * @param string|null  $filename  Optional filename (UUID generated if null)
     * @return string                 Relative path stored in storage/app/public
     */
    public function compress(UploadedFile $file, string $directory, ?string $filename = null): string
    {
        // Validate mime type — skip compression for non-images
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'];
        $mimeType = $file->getMimeType() ?: '';

        // Normalize HEIC/HEIF detection (some systems report as application/octet-stream)
        $extension = strtolower($file->getClientOriginalExtension());
        $isHeic = in_array($extension, ['heic', 'heif']);

        if (!in_array($mimeType, $allowedMimes) && !$isHeic) {
            // Not a recognized image — store as-is
            $generatedName = $filename ?? Str::uuid()->toString();
            $path = $file->storeAs($directory, $generatedName, 'public');
            Log::warning("ImageCompressor: skipped compression for non-image mime {$mimeType}, stored original.");
            return $path;
        }

        $generatedName = $filename ?? Str::uuid()->toString();
        // Always output as .jpg for consistent compression
        $outputFilename = pathinfo($generatedName, PATHINFO_FILENAME) . '.jpg';
        $storagePath = rtrim($directory, '/') . '/' . $outputFilename;
        $fullPath = Storage::disk('public')->path($storagePath);

        try {
            // Save uploaded file to a temp location so spatie/image can read it
            $tempPath = $file->getRealPath();

            // Ensure output directory exists
            $outputDir = dirname($fullPath);
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // Use spatie/image v3 to process
            Image::load($tempPath)
                ->width(1200)               // Max 1200px, height scales automatically (don't upscale if smaller)
                ->format('jpg')             // Convert to JPEG
                ->quality(75)               // JPEG quality 1-100
                ->save($fullPath);

            Log::info("ImageCompressor: compressed {$file->getSize()} bytes → " . filesize($fullPath) . " bytes → {$storagePath}");

            return $storagePath;
        } catch (\Throwable $e) {
            // Fallback: store original file
            Log::warning("ImageCompressor: compression failed ({$e->getMessage()}), storing original.", [
                'original_size' => $file->getSize(),
                'mime' => $mimeType,
            ]);

            $generatedName = $filename ?? Str::uuid()->toString();
            return $file->storeAs($directory, $generatedName, 'public');
        }
    }
}
