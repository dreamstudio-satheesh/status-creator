<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FileCleanupJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 300; // 5 minutes
    public int $tries = 3;

    private array $cleanupRules;

    public function __construct(array $cleanupRules = [])
    {
        $this->cleanupRules = array_merge([
            'orphaned_files_older_than' => 7,  // days
            'temp_files_older_than' => 1,      // days
            'unused_avatars_older_than' => 30, // days
            'failed_uploads_older_than' => 3,  // days
        ], $cleanupRules);
    }

    public function handle(): void
    {
        Log::info('Starting file cleanup job', $this->cleanupRules);

        $stats = [
            'orphaned_files_deleted' => 0,
            'temp_files_deleted' => 0,
            'unused_avatars_deleted' => 0,
            'failed_uploads_deleted' => 0,
            'total_space_freed' => 0,
        ];

        try {
            // Clean up orphaned files (files not referenced in database)
            $stats['orphaned_files_deleted'] = $this->cleanupOrphanedFiles();
            
            // Clean up temporary files
            $stats['temp_files_deleted'] = $this->cleanupTempFiles();
            
            // Clean up unused avatars
            $stats['unused_avatars_deleted'] = $this->cleanupUnusedAvatars();
            
            // Clean up failed uploads
            $stats['failed_uploads_deleted'] = $this->cleanupFailedUploads();

            // Clean up empty directories
            $this->cleanupEmptyDirectories();

            // Sync cleanup with backup storage
            $this->syncBackupCleanup();

            Log::info('File cleanup job completed successfully', $stats);

        } catch (\Exception $e) {
            Log::error('File cleanup job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stats' => $stats,
            ]);
            throw $e;
        }
    }

    private function cleanupOrphanedFiles(): int
    {
        $disk = $this->getStorageDisk();
        $deletedCount = 0;
        $cutoffDate = Carbon::now()->subDays($this->cleanupRules['orphaned_files_older_than']);

        try {
            // Get all files from storage
            $allFiles = collect(Storage::disk($disk)->allFiles());
            
            // Get referenced files from database
            $referencedFiles = collect();
            
            // Check user avatars
            $avatars = DB::table('users')
                        ->whereNotNull('avatar')
                        ->pluck('avatar');
            $referencedFiles = $referencedFiles->merge($avatars);
            
            // Check user creations
            $userCreationFiles = DB::table('user_creations')
                                  ->whereNotNull('image_path')
                                  ->pluck('image_path');
            $referencedFiles = $referencedFiles->merge($userCreationFiles);
            
            // Check templates
            $templateFiles = DB::table('templates')
                           ->whereNotNull('image_path')
                           ->pluck('image_path');
            $referencedFiles = $referencedFiles->merge($templateFiles);

            // Find orphaned files
            $orphanedFiles = $allFiles->diff($referencedFiles->unique());

            foreach ($orphanedFiles as $file) {
                try {
                    $lastModified = Storage::disk($disk)->lastModified($file);
                    $fileDate = Carbon::createFromTimestamp($lastModified);
                    
                    if ($fileDate->lt($cutoffDate)) {
                        Storage::disk($disk)->delete($file);
                        $deletedCount++;
                        
                        Log::debug('Deleted orphaned file', [
                            'file' => $file,
                            'last_modified' => $fileDate,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to delete orphaned file', [
                        'file' => $file,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Orphaned files cleanup failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $deletedCount;
    }

    private function cleanupTempFiles(): int
    {
        $disk = $this->getStorageDisk();
        $deletedCount = 0;
        $cutoffDate = Carbon::now()->subDays($this->cleanupRules['temp_files_older_than']);

        try {
            $tempDirectories = ['temp', 'tmp', 'uploads/temp'];
            
            foreach ($tempDirectories as $directory) {
                if (!Storage::disk($disk)->exists($directory)) {
                    continue;
                }
                
                $tempFiles = Storage::disk($disk)->allFiles($directory);
                
                foreach ($tempFiles as $file) {
                    try {
                        $lastModified = Storage::disk($disk)->lastModified($file);
                        $fileDate = Carbon::createFromTimestamp($lastModified);
                        
                        if ($fileDate->lt($cutoffDate)) {
                            Storage::disk($disk)->delete($file);
                            $deletedCount++;
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to delete temp file', [
                            'file' => $file,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Temp files cleanup failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $deletedCount;
    }

    private function cleanupUnusedAvatars(): int
    {
        $disk = $this->getStorageDisk();
        $deletedCount = 0;
        $cutoffDate = Carbon::now()->subDays($this->cleanupRules['unused_avatars_older_than']);

        try {
            // Find users who haven't logged in recently and have old avatars
            $inactiveUsers = DB::table('users')
                           ->whereNotNull('avatar')
                           ->where('last_login_at', '<', $cutoffDate)
                           ->orWhere(function($query) use ($cutoffDate) {
                               $query->whereNull('last_login_at')
                                     ->where('created_at', '<', $cutoffDate);
                           })
                           ->pluck('avatar', 'id');

            foreach ($inactiveUsers as $userId => $avatarPath) {
                try {
                    if (Storage::disk($disk)->exists($avatarPath)) {
                        Storage::disk($disk)->delete($avatarPath);
                        
                        // Clear avatar reference from user record
                        DB::table('users')
                          ->where('id', $userId)
                          ->update(['avatar' => null]);
                        
                        $deletedCount++;
                        
                        Log::debug('Deleted unused avatar', [
                            'user_id' => $userId,
                            'avatar_path' => $avatarPath,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to delete unused avatar', [
                        'user_id' => $userId,
                        'avatar_path' => $avatarPath,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Unused avatars cleanup failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $deletedCount;
    }

    private function cleanupFailedUploads(): int
    {
        $disk = $this->getStorageDisk();
        $deletedCount = 0;
        $cutoffDate = Carbon::now()->subDays($this->cleanupRules['failed_uploads_older_than']);

        try {
            // Clean up failed upload records and their associated files
            $failedUploads = DB::table('upload_logs')
                           ->where('status', 'failed')
                           ->where('created_at', '<', $cutoffDate)
                           ->get();

            foreach ($failedUploads as $upload) {
                try {
                    if ($upload->file_path && Storage::disk($disk)->exists($upload->file_path)) {
                        Storage::disk($disk)->delete($upload->file_path);
                        $deletedCount++;
                    }
                    
                    // Remove the failed upload record
                    DB::table('upload_logs')->where('id', $upload->id)->delete();
                    
                } catch (\Exception $e) {
                    Log::warning('Failed to cleanup failed upload', [
                        'upload_id' => $upload->id,
                        'file_path' => $upload->file_path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed uploads cleanup failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $deletedCount;
    }

    private function cleanupEmptyDirectories(): void
    {
        $disk = $this->getStorageDisk();
        
        try {
            $directories = Storage::disk($disk)->allDirectories();
            
            foreach ($directories as $directory) {
                try {
                    $files = Storage::disk($disk)->allFiles($directory);
                    $subdirectories = Storage::disk($disk)->directories($directory);
                    
                    // If directory is empty (no files and no subdirectories)
                    if (empty($files) && empty($subdirectories)) {
                        Storage::disk($disk)->deleteDirectory($directory);
                        
                        Log::debug('Deleted empty directory', [
                            'directory' => $directory,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to delete empty directory', [
                        'directory' => $directory,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Empty directories cleanup failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function syncBackupCleanup(): void
    {
        if (!config('filesystems.backup_enabled', false)) {
            return;
        }

        try {
            $primaryDisk = $this->getStorageDisk();
            $backupDisk = 'backup';
            
            $primaryFiles = collect(Storage::disk($primaryDisk)->allFiles());
            $backupFiles = collect(Storage::disk($backupDisk)->allFiles());
            
            // Delete backup files that no longer exist in primary storage
            $filesToDeleteFromBackup = $backupFiles->diff($primaryFiles);
            
            foreach ($filesToDeleteFromBackup as $file) {
                try {
                    Storage::disk($backupDisk)->delete($file);
                    
                    Log::debug('Deleted file from backup storage', [
                        'file' => $file,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete file from backup storage', [
                        'file' => $file,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Backup storage sync failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getStorageDisk(): string
    {
        return match(app()->environment()) {
            'production' => config('filesystems.production_disk', 'spaces'),
            'staging' => config('filesystems.staging_disk', 's3'),
            default => config('filesystems.development_disk', 'minio'),
        };
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('File cleanup job failed permanently', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}