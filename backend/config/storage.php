<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for file storage, CDN, and backup strategies
    |
    */

    // Storage disk configuration based on environment
    'production_disk' => env('STORAGE_PRODUCTION_DISK', 'spaces'),
    'staging_disk' => env('STORAGE_STAGING_DISK', 's3'),
    'development_disk' => env('STORAGE_DEVELOPMENT_DISK', 'minio'),

    // CDN configuration
    'cdn_enabled' => env('CDN_ENABLED', false),
    'cdn_url' => env('CDN_URL'),
    'cdn_zones' => [
        'images' => env('CDN_IMAGES_ZONE'),
        'static' => env('CDN_STATIC_ZONE'),
    ],

    // Backup storage configuration
    'backup_enabled' => env('BACKUP_STORAGE_ENABLED', false),
    'backup_strategy' => env('BACKUP_STRATEGY', 'async'), // sync, async, or disabled
    'backup_retention_days' => env('BACKUP_RETENTION_DAYS', 30),

    // File optimization settings
    'image_optimization' => [
        'jpeg_quality' => env('IMAGE_JPEG_QUALITY', 85),
        'png_compression' => env('IMAGE_PNG_COMPRESSION', 6),
        'webp_quality' => env('IMAGE_WEBP_QUALITY', 80),
        'strip_exif' => env('IMAGE_STRIP_EXIF', true),
        'progressive_jpeg' => env('IMAGE_PROGRESSIVE_JPEG', true),
    ],

    // File size limits (in bytes)
    'max_file_sizes' => [
        'avatar' => [
            'free' => 2 * 1024 * 1024,      // 2MB
            'premium' => 5 * 1024 * 1024,   // 5MB
        ],
        'template' => [
            'free' => 5 * 1024 * 1024,      // 5MB
            'premium' => 10 * 1024 * 1024,  // 10MB
        ],
        'user_upload' => [
            'free' => 5 * 1024 * 1024,      // 5MB
            'premium' => 10 * 1024 * 1024,  // 10MB
        ],
    ],

    // Upload limits
    'daily_upload_limits' => [
        'free' => 10,
        'premium' => 'unlimited',
    ],

    // Allowed file types
    'allowed_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ],

    'allowed_extensions' => [
        'jpg', 'jpeg', 'png', 'gif', 'webp'
    ],

    // File cleanup settings
    'cleanup' => [
        'enabled' => env('FILE_CLEANUP_ENABLED', true),
        'schedule' => env('FILE_CLEANUP_SCHEDULE', 'daily'),
        'rules' => [
            'orphaned_files_older_than' => env('CLEANUP_ORPHANED_FILES_DAYS', 7),
            'temp_files_older_than' => env('CLEANUP_TEMP_FILES_DAYS', 1),
            'unused_avatars_older_than' => env('CLEANUP_UNUSED_AVATARS_DAYS', 30),
            'failed_uploads_older_than' => env('CLEANUP_FAILED_UPLOADS_DAYS', 3),
        ],
    ],

    // Security settings
    'security' => [
        'scan_uploads' => env('SCAN_UPLOADS', true),
        'max_image_dimensions' => [
            'width' => env('MAX_IMAGE_WIDTH', 5000),
            'height' => env('MAX_IMAGE_HEIGHT', 5000),
        ],
        'blocked_file_signatures' => [
            // Common malicious file signatures
            "\x4d\x5a", // MZ (Windows executable)
            "\x50\x4b\x03\x04", // ZIP files (when not expected)
            "<?php", // PHP code
            "<script", // JavaScript
        ],
    ],

    // Thumbnail generation settings
    'thumbnails' => [
        'enabled' => env('THUMBNAILS_ENABLED', true),
        'sizes' => [
            'thumbnail' => ['width' => 300, 'height' => 300],
            'medium' => ['width' => 800, 'height' => 600],
            'large' => ['width' => 1200, 'height' => 900],
        ],
        'quality' => env('THUMBNAIL_QUALITY', 80),
        'format' => env('THUMBNAIL_FORMAT', 'jpg'),
    ],

    // Storage providers configuration
    'providers' => [
        'aws_s3' => [
            'name' => 'Amazon S3',
            'features' => ['backup', 'cdn', 'global'],
            'cost_tier' => 'medium',
        ],
        'digitalocean_spaces' => [
            'name' => 'DigitalOcean Spaces',
            'features' => ['backup', 'cdn', 'cost_effective'],
            'cost_tier' => 'low',
        ],
        'minio' => [
            'name' => 'MinIO (Development)',
            'features' => ['local', 'development'],
            'cost_tier' => 'free',
        ],
    ],

    // Monitoring and logging
    'monitoring' => [
        'log_uploads' => env('LOG_UPLOADS', true),
        'log_deletions' => env('LOG_DELETIONS', true),
        'track_storage_usage' => env('TRACK_STORAGE_USAGE', true),
        'alert_on_quota_exceeded' => env('ALERT_QUOTA_EXCEEDED', true),
    ],
];