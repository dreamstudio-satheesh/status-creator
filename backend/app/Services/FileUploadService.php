<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use App\Models\User;

class FileUploadService
{
    private array $allowedMimeTypes = [
        'image/jpeg',
        'image/png', 
        'image/gif',
        'image/webp'
    ];
    
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    private array $maxFileSizes = [
        'avatar' => 2 * 1024 * 1024,     // 2MB
        'template' => 10 * 1024 * 1024,  // 10MB
        'user_upload' => 5 * 1024 * 1024, // 5MB (free), 10MB (premium)
    ];

    private array $imageDimensions = [
        'avatar' => ['width' => 200, 'height' => 200],
        'thumbnail' => ['width' => 300, 'height' => 300],
        'medium' => ['width' => 800, 'height' => 600],
        'large' => ['width' => 1200, 'height' => 900],
    ];

    public function uploadFile(
        UploadedFile $file, 
        string $type, 
        User $user, 
        array $options = []
    ): array {
        try {
            // Validate file
            $validation = $this->validateFile($file, $type, $user);
            if (!$validation['valid']) {
                return ['success' => false, 'error' => $validation['message']];
            }

            // Get storage disk based on environment
            $disk = $this->getStorageDisk();
            
            // Generate file path and name
            $filePath = $this->generateFilePath($file, $type, $user);
            
            // Process and optimize image
            $processedImage = $this->processImage($file, $type, $options);
            
            // Upload to primary storage
            $uploaded = Storage::disk($disk)->put($filePath, $processedImage['content']);
            
            if (!$uploaded) {
                throw new \Exception('Failed to upload file to storage');
            }

            // Upload to backup storage if configured
            $this->uploadToBackup($filePath, $processedImage['content']);

            // Generate CDN URL
            $url = $this->generateFileUrl($filePath, $disk);

            // Log upload activity
            $this->logUploadActivity($user, $filePath, $type, $processedImage['info']);

            return [
                'success' => true,
                'file_path' => $filePath,
                'url' => $url,
                'file_info' => $processedImage['info'],
                'storage_disk' => $disk,
            ];

        } catch (\Exception $e) {
            Log::error('File upload failed', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName(),
            ]);

            return [
                'success' => false,
                'error' => 'File upload failed: ' . $e->getMessage()
            ];
        }
    }

    public function deleteFile(string $filePath, ?User $user = null): bool
    {
        try {
            $disk = $this->getStorageDisk();
            
            // Security check: ensure user can only delete their own files
            if ($user && !$this->canUserDeleteFile($filePath, $user)) {
                return false;
            }

            // Delete from primary storage
            if (Storage::disk($disk)->exists($filePath)) {
                Storage::disk($disk)->delete($filePath);
            }

            // Delete from backup storage
            $this->deleteFromBackup($filePath);

            return true;
        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'file_path' => $filePath,
                'user_id' => $user?->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function validateFile(UploadedFile $file, string $type, User $user): array
    {
        // Check if file is valid
        if (!$file->isValid()) {
            return ['valid' => false, 'message' => 'Invalid file upload'];
        }

        // Check file size
        $maxSize = $this->getMaxFileSize($type, $user);
        if ($file->getSize() > $maxSize) {
            $maxSizeMB = round($maxSize / 1024 / 1024, 1);
            return ['valid' => false, 'message' => "File too large. Maximum size: {$maxSizeMB}MB"];
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            return ['valid' => false, 'message' => 'Invalid file type. Only images are allowed.'];
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            return ['valid' => false, 'message' => 'Invalid file extension'];
        }

        // Check image dimensions for security
        try {
            $imageSize = getimagesize($file->getRealPath());
            if (!$imageSize) {
                return ['valid' => false, 'message' => 'Invalid image file'];
            }

            // Prevent extremely large images (memory exhaustion attack)
            if ($imageSize[0] > 5000 || $imageSize[1] > 5000) {
                return ['valid' => false, 'message' => 'Image dimensions too large (max 5000x5000)'];
            }
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => 'Unable to validate image'];
        }

        return ['valid' => true, 'message' => 'File is valid'];
    }

    private function processImage(UploadedFile $file, string $type, array $options): array
    {
        $image = Image::make($file);
        
        // Get processing options
        $quality = $options['quality'] ?? 85;
        $width = $options['width'] ?? null;
        $height = $options['height'] ?? null;
        $format = $options['format'] ?? 'jpg';

        // Apply image transformations based on type
        switch ($type) {
            case 'avatar':
                $image->fit(200, 200);
                break;
            case 'template_background':
                // Keep original dimensions but optimize
                break;
            case 'user_upload':
                // Resize if too large
                if ($image->width() > 1200 || $image->height() > 1200) {
                    $image->resize(1200, 1200, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
                break;
        }

        // Apply custom dimensions if provided
        if ($width && $height) {
            $image->fit($width, $height);
        } elseif ($width) {
            $image->widen($width, function ($constraint) {
                $constraint->upsize();
            });
        } elseif ($height) {
            $image->heighten($height, function ($constraint) {
                $constraint->upsize();
            });
        }

        // Optimize image
        $image->encode($format, $quality);

        // Strip EXIF data for privacy
        $image->orientate();

        return [
            'content' => $image->__toString(),
            'info' => [
                'width' => $image->width(),
                'height' => $image->height(),
                'size' => strlen($image->__toString()),
                'format' => $format,
                'quality' => $quality,
            ]
        ];
    }

    private function generateFilePath(UploadedFile $file, string $type, User $user): string
    {
        $extension = 'jpg'; // We always convert to JPG for consistency
        $timestamp = time();
        $uniqueId = uniqid();
        
        return match($type) {
            'avatar' => "avatars/{$user->id}_{$timestamp}.{$extension}",
            'template_background' => "templates/{$user->id}_{$timestamp}_{$uniqueId}.{$extension}",
            'user_upload' => "uploads/{$user->id}/{$timestamp}_{$uniqueId}.{$extension}",
            default => "misc/{$user->id}_{$timestamp}_{$uniqueId}.{$extension}"
        };
    }

    private function getMaxFileSize(string $type, User $user): int
    {
        $baseSize = $this->maxFileSizes[$type] ?? $this->maxFileSizes['user_upload'];
        
        // Premium users get double the upload limit
        if ($user->isPremium() && $type === 'user_upload') {
            return $baseSize * 2;
        }
        
        return $baseSize;
    }

    private function getStorageDisk(): string
    {
        // Use environment-specific storage
        return match(app()->environment()) {
            'production' => config('filesystems.production_disk', 'spaces'),
            'staging' => config('filesystems.staging_disk', 's3'),
            default => config('filesystems.development_disk', 'minio'),
        };
    }

    private function generateFileUrl(string $filePath, string $disk): string
    {
        // For production, use CDN URLs
        if (app()->environment('production') && config('filesystems.cdn_url')) {
            return config('filesystems.cdn_url') . '/' . $filePath;
        }
        
        return Storage::disk($disk)->url($filePath);
    }

    private function uploadToBackup(string $filePath, string $content): void
    {
        if (!config('filesystems.backup_enabled', false)) {
            return;
        }

        try {
            Storage::disk('backup')->put($filePath, $content);
        } catch (\Exception $e) {
            Log::warning('Backup upload failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function deleteFromBackup(string $filePath): void
    {
        if (!config('filesystems.backup_enabled', false)) {
            return;
        }

        try {
            if (Storage::disk('backup')->exists($filePath)) {
                Storage::disk('backup')->delete($filePath);
            }
        } catch (\Exception $e) {
            Log::warning('Backup deletion failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function canUserDeleteFile(string $filePath, User $user): bool
    {
        // Users can only delete files that contain their user ID
        return str_contains($filePath, (string)$user->id);
    }

    private function logUploadActivity(User $user, string $filePath, string $type, array $fileInfo): void
    {
        Log::info('File uploaded successfully', [
            'user_id' => $user->id,
            'file_path' => $filePath,
            'type' => $type,
            'file_info' => $fileInfo,
            'timestamp' => now(),
        ]);
    }

    public function getUploadLimits(User $user): array
    {
        $todayUploads = \DB::table('user_creations')
                         ->where('user_id', $user->id)
                         ->whereDate('created_at', today())
                         ->count();

        return [
            'daily_upload_limit' => $user->isPremium() ? 'unlimited' : 10,
            'daily_uploads_used' => $todayUploads,
            'max_file_size' => [
                'avatar' => '2MB',
                'template' => '10MB',
                'user_upload' => $user->isPremium() ? '10MB' : '5MB',
            ],
            'allowed_formats' => $this->allowedExtensions,
            'is_premium' => $user->isPremium(),
        ];
    }

    public function createThumbnails(string $filePath, array $sizes = ['thumbnail', 'medium']): array
    {
        $disk = $this->getStorageDisk();
        $thumbnails = [];

        try {
            $fileContent = Storage::disk($disk)->get($filePath);
            $image = Image::make($fileContent);

            foreach ($sizes as $size) {
                if (!isset($this->imageDimensions[$size])) {
                    continue;
                }

                $dimensions = $this->imageDimensions[$size];
                $thumbnailImage = clone $image;
                
                $thumbnailImage->fit($dimensions['width'], $dimensions['height']);
                $thumbnailImage->encode('jpg', 80);

                $thumbnailPath = $this->generateThumbnailPath($filePath, $size);
                
                if (Storage::disk($disk)->put($thumbnailPath, $thumbnailImage->__toString())) {
                    $thumbnails[$size] = [
                        'path' => $thumbnailPath,
                        'url' => $this->generateFileUrl($thumbnailPath, $disk),
                        'width' => $dimensions['width'],
                        'height' => $dimensions['height'],
                    ];
                }
            }

            return $thumbnails;
        } catch (\Exception $e) {
            Log::error('Thumbnail generation failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    private function generateThumbnailPath(string $originalPath, string $size): string
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/thumbs/' . $pathInfo['filename'] . '_' . $size . '.' . $pathInfo['extension'];
    }
}