<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        // DigitalOcean Spaces (compatible with S3)
        'spaces' => [
            'driver' => 's3',
            'key' => env('DO_ACCESS_KEY_ID'),
            'secret' => env('DO_SECRET_ACCESS_KEY'),
            'region' => env('DO_DEFAULT_REGION', 'nyc3'),
            'bucket' => env('DO_BUCKET'),
            'url' => env('DO_URL'),
            'endpoint' => env('DO_ENDPOINT'),
            'use_path_style_endpoint' => false,
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // MinIO for development
        'minio' => [
            'driver' => 's3',
            'key' => env('MINIO_ACCESS_KEY_ID', 'minio'),
            'secret' => env('MINIO_SECRET_ACCESS_KEY', 'minio123'),
            'region' => env('MINIO_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('MINIO_BUCKET', 'tamil-status'),
            'url' => env('MINIO_URL'),
            'endpoint' => env('MINIO_ENDPOINT', 'http://localhost:9000'),
            'use_path_style_endpoint' => true,
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // Backup storage (for redundancy)
        'backup' => [
            'driver' => 's3',
            'key' => env('BACKUP_ACCESS_KEY_ID'),
            'secret' => env('BACKUP_SECRET_ACCESS_KEY'),
            'region' => env('BACKUP_DEFAULT_REGION'),
            'bucket' => env('BACKUP_BUCKET'),
            'url' => env('BACKUP_URL'),
            'endpoint' => env('BACKUP_ENDPOINT'),
            'use_path_style_endpoint' => env('BACKUP_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
