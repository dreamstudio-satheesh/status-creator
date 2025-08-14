<?php

namespace App\Console\Commands;

use App\Jobs\FileCleanupJob;
use App\Services\FileUploadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StorageManagementCommand extends Command
{
    protected $signature = 'storage:manage 
                            {action : Action to perform (cleanup|stats|sync|migrate|test)}
                            {--disk= : Storage disk to operate on}
                            {--dry-run : Show what would be done without making changes}
                            {--force : Force the action without confirmation}';

    protected $description = 'Manage file storage operations including cleanup, statistics, and synchronization';

    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        parent::__construct();
        $this->fileUploadService = $fileUploadService;
    }

    public function handle(): int
    {
        $action = $this->argument('action');
        
        return match ($action) {
            'cleanup' => $this->handleCleanup(),
            'stats' => $this->handleStats(),
            'sync' => $this->handleSync(),
            'migrate' => $this->handleMigrate(),
            'test' => $this->handleTest(),
            default => $this->error("Unknown action: {$action}"),
        };
    }

    private function handleCleanup(): int
    {
        $this->info('Starting file cleanup...');
        
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No files will actually be deleted');
        }

        $rules = [
            'orphaned_files_older_than' => 7,
            'temp_files_older_than' => 1,
            'unused_avatars_older_than' => 30,
            'failed_uploads_older_than' => 3,
        ];

        if (!$this->option('dry-run')) {
            FileCleanupJob::dispatch($rules);
            $this->info('File cleanup job dispatched to queue');
        } else {
            $this->simulateCleanup($rules);
        }

        return 0;
    }

    private function handleStats(): int
    {
        $this->info('Generating storage statistics...');
        
        $disk = $this->option('disk') ?? $this->getDefaultDisk();
        
        $stats = $this->calculateStorageStats($disk);
        
        $this->displayStorageStats($stats);
        
        return 0;
    }

    private function handleSync(): int
    {
        $this->info('Synchronizing storage systems...');
        
        $primaryDisk = $this->getDefaultDisk();
        $backupDisk = 'backup';
        
        if (!config('filesystems.backup_enabled')) {
            $this->error('Backup storage is not enabled');
            return 1;
        }

        $this->syncStorageSystems($primaryDisk, $backupDisk);
        
        return 0;
    }

    private function handleMigrate(): int
    {
        $this->info('Migrating files between storage systems...');
        
        $fromDisk = $this->option('disk');
        if (!$fromDisk) {
            $fromDisk = $this->choice('Select source storage disk:', [
                'local', 'public', 's3', 'spaces', 'minio'
            ]);
        }
        
        $toDisk = $this->choice('Select destination storage disk:', [
            'local', 'public', 's3', 'spaces', 'minio'
        ]);
        
        if ($fromDisk === $toDisk) {
            $this->error('Source and destination cannot be the same');
            return 1;
        }

        if (!$this->option('force')) {
            if (!$this->confirm("Migrate all files from {$fromDisk} to {$toDisk}?")) {
                $this->info('Migration cancelled');
                return 0;
            }
        }

        $this->migrateFiles($fromDisk, $toDisk);
        
        return 0;
    }

    private function handleTest(): int
    {
        $this->info('Testing storage connections...');
        
        $disks = ['local', 'public', 's3', 'spaces', 'minio', 'backup'];
        
        foreach ($disks as $disk) {
            $this->testStorageDisk($disk);
        }
        
        return 0;
    }

    private function simulateCleanup(array $rules): void
    {
        $disk = $this->getDefaultDisk();
        
        $this->info("Simulating cleanup on disk: {$disk}");
        
        // Simulate orphaned files check
        $allFiles = collect(Storage::disk($disk)->allFiles())->take(10);
        $this->line("Would check {$allFiles->count()} files for orphaned status...");
        
        // Simulate temp files check
        $tempDirectories = ['temp', 'tmp', 'uploads/temp'];
        foreach ($tempDirectories as $dir) {
            if (Storage::disk($disk)->exists($dir)) {
                $tempFiles = Storage::disk($disk)->allFiles($dir);
                $this->line("Would check {count($tempFiles)} temp files in {$dir}");
            }
        }
        
        $this->info('Cleanup simulation completed');
    }

    private function calculateStorageStats(string $disk): array
    {
        $stats = [
            'disk' => $disk,
            'total_files' => 0,
            'total_size' => 0,
            'file_types' => [],
            'directories' => [],
            'largest_files' => [],
            'oldest_files' => [],
        ];

        try {
            $files = Storage::disk($disk)->allFiles();
            $stats['total_files'] = count($files);
            
            foreach ($files as $file) {
                $size = Storage::disk($disk)->size($file);
                $stats['total_size'] += $size;
                
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $stats['file_types'][$extension] = ($stats['file_types'][$extension] ?? 0) + 1;
                
                $directory = dirname($file);
                $stats['directories'][$directory] = ($stats['directories'][$directory] ?? 0) + 1;
                
                // Track largest files
                $stats['largest_files'][] = ['file' => $file, 'size' => $size];
            }
            
            // Sort and limit largest files
            usort($stats['largest_files'], fn($a, $b) => $b['size'] <=> $a['size']);
            $stats['largest_files'] = array_slice($stats['largest_files'], 0, 10);
            
            // Sort file types by count
            arsort($stats['file_types']);
            arsort($stats['directories']);
            
        } catch (\Exception $e) {
            $this->error("Failed to calculate stats for disk {$disk}: " . $e->getMessage());
        }

        return $stats;
    }

    private function displayStorageStats(array $stats): void
    {
        $this->table(['Metric', 'Value'], [
            ['Disk', $stats['disk']],
            ['Total Files', number_format($stats['total_files'])],
            ['Total Size', $this->formatBytes($stats['total_size'])],
        ]);

        if (!empty($stats['file_types'])) {
            $this->info('File Types:');
            foreach (array_slice($stats['file_types'], 0, 10) as $type => $count) {
                $this->line("  {$type}: " . number_format($count) . " files");
            }
        }

        if (!empty($stats['directories'])) {
            $this->info('Top Directories:');
            foreach (array_slice($stats['directories'], 0, 10) as $dir => $count) {
                $this->line("  {$dir}: " . number_format($count) . " files");
            }
        }

        if (!empty($stats['largest_files'])) {
            $this->info('Largest Files:');
            foreach ($stats['largest_files'] as $file) {
                $this->line("  {$file['file']}: " . $this->formatBytes($file['size']));
            }
        }
    }

    private function syncStorageSystems(string $primaryDisk, string $backupDisk): void
    {
        try {
            $primaryFiles = collect(Storage::disk($primaryDisk)->allFiles());
            $backupFiles = collect(Storage::disk($backupDisk)->allFiles());
            
            $missingInBackup = $primaryFiles->diff($backupFiles);
            $extraInBackup = $backupFiles->diff($primaryFiles);
            
            $this->info("Files missing in backup: " . $missingInBackup->count());
            $this->info("Extra files in backup: " . $extraInBackup->count());
            
            if ($missingInBackup->count() > 0) {
                $this->info('Copying missing files to backup...');
                $this->withProgressBar($missingInBackup, function ($file) use ($primaryDisk, $backupDisk) {
                    try {
                        $content = Storage::disk($primaryDisk)->get($file);
                        Storage::disk($backupDisk)->put($file, $content);
                    } catch (\Exception $e) {
                        Log::error("Failed to copy {$file} to backup: " . $e->getMessage());
                    }
                });
                $this->newLine();
            }
            
            if ($extraInBackup->count() > 0 && $this->confirm('Remove extra files from backup?')) {
                $this->info('Removing extra files from backup...');
                $this->withProgressBar($extraInBackup, function ($file) use ($backupDisk) {
                    try {
                        Storage::disk($backupDisk)->delete($file);
                    } catch (\Exception $e) {
                        Log::error("Failed to delete {$file} from backup: " . $e->getMessage());
                    }
                });
                $this->newLine();
            }
            
            $this->info('Storage synchronization completed');
            
        } catch (\Exception $e) {
            $this->error('Synchronization failed: ' . $e->getMessage());
        }
    }

    private function migrateFiles(string $fromDisk, string $toDisk): void
    {
        try {
            $files = Storage::disk($fromDisk)->allFiles();
            $this->info("Migrating " . count($files) . " files from {$fromDisk} to {$toDisk}");
            
            $this->withProgressBar($files, function ($file) use ($fromDisk, $toDisk) {
                try {
                    $content = Storage::disk($fromDisk)->get($file);
                    Storage::disk($toDisk)->put($file, $content);
                    
                    // Verify the file was copied successfully
                    if (Storage::disk($toDisk)->exists($file)) {
                        Storage::disk($fromDisk)->delete($file);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to migrate {$file}: " . $e->getMessage());
                }
            });
            
            $this->newLine();
            $this->info('File migration completed');
            
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
        }
    }

    private function testStorageDisk(string $disk): void
    {
        try {
            $testFile = 'test_' . time() . '.txt';
            $testContent = 'Storage test file';
            
            // Test write
            Storage::disk($disk)->put($testFile, $testContent);
            
            // Test read
            $readContent = Storage::disk($disk)->get($testFile);
            
            // Test delete
            Storage::disk($disk)->delete($testFile);
            
            if ($readContent === $testContent) {
                $this->info("✅ {$disk}: OK");
            } else {
                $this->error("❌ {$disk}: Content mismatch");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ {$disk}: " . $e->getMessage());
        }
    }

    private function getDefaultDisk(): string
    {
        return match(app()->environment()) {
            'production' => config('storage.production_disk', 'spaces'),
            'staging' => config('storage.staging_disk', 's3'),
            default => config('storage.development_disk', 'minio'),
        };
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}