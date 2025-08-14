<?php

namespace App\Http\Controllers;

use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class UploadController extends Controller
{
    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
    /**
     * @OA\Post(
     *     path="/api/v1/uploads/avatar",
     *     tags={"File Upload"},
     *     summary="Upload user avatar",
     *     description="Upload and optimize user avatar image. Automatically resizes to 200x200px.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="avatar", type="string", format="binary", description="Avatar image file (JPEG, PNG, GIF - max 2MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avatar uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Avatar uploaded successfully"),
     *             @OA\Property(property="avatar_url", type="string", example="https://cdn.example.com/avatars/123_1692014400.jpg"),
     *             @OA\Property(property="avatar_path", type="string", example="avatars/123_1692014400.jpg"),
     *             @OA\Property(property="file_info", type="object",
     *                 @OA\Property(property="width", type="integer", example=200),
     *                 @OA\Property(property="height", type="integer", example=200),
     *                 @OA\Property(property="size", type="integer", example=15432),
     *                 @OA\Property(property="format", type="string", example="jpg")
     *             )
     *         )
     *     )
     * )
     */
    public function avatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $file = $request->file('avatar');

        // Delete old avatar if exists
        if ($user->avatar) {
            $this->fileUploadService->deleteFile($user->avatar, $user);
        }

        // Upload new avatar using the service
        $result = $this->fileUploadService->uploadFile($file, 'avatar', $user);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar',
                'error' => $result['error'],
            ], 500);
        }

        // Update user avatar path
        $user->update(['avatar' => $result['file_path']]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully',
            'avatar_url' => $result['url'],
            'avatar_path' => $result['file_path'],
            'file_info' => $result['file_info'],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/uploads/image",
     *     tags={"File Upload"},
     *     summary="Upload and optimize image",
     *     description="Upload images with automatic optimization and resizing. Supports templates and user uploads.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="image", type="string", format="binary", description="Image file"),
     *                 @OA\Property(property="type", type="string", enum={"template_background", "user_upload"}, description="Upload type"),
     *                 @OA\Property(property="quality", type="integer", minimum=60, maximum=100, description="JPEG quality (60-100)"),
     *                 @OA\Property(property="width", type="integer", minimum=100, maximum=2000, description="Target width"),
     *                 @OA\Property(property="height", type="integer", minimum=100, maximum=2000, description="Target height")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="image_url", type="string", example="https://cdn.example.com/uploads/123/1692014400_abc123.jpg"),
     *             @OA\Property(property="thumbnails", type="object", description="Generated thumbnail URLs")
     *         )
     *     )
     * )
     */
    public function image(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'type' => 'required|string|in:template_background,user_upload',
            'quality' => 'nullable|integer|between:60,100',
            'width' => 'nullable|integer|between:100,2000',
            'height' => 'nullable|integer|between:100,2000',
            'generate_thumbnails' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Check upload limits using the service
        $limits = $this->fileUploadService->getUploadLimits($user);
        if (!$user->isPremium() && $limits['daily_uploads_used'] >= 10) {
            return response()->json([
                'success' => false,
                'message' => 'Daily upload limit reached. Upgrade to premium for unlimited uploads.',
                'is_premium_required' => true,
                'daily_uploads_used' => $limits['daily_uploads_used'],
                'daily_upload_limit' => $limits['daily_upload_limit'],
            ], 429);
        }

        $file = $request->file('image');
        $type = $request->type;

        // Prepare upload options
        $options = [
            'quality' => $request->get('quality', 85),
            'width' => $request->get('width'),
            'height' => $request->get('height'),
            'format' => 'jpg',
        ];

        // Upload file using the service
        $result = $this->fileUploadService->uploadFile($file, $type, $user, $options);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => $result['error'],
            ], 500);
        }

        $response = [
            'success' => true,
            'message' => 'Image uploaded successfully',
            'image_url' => $result['url'],
            'image_path' => $result['file_path'],
            'image_info' => $result['file_info'],
            'storage_info' => [
                'disk' => $result['storage_disk'],
                'backup_enabled' => config('filesystems.backup_enabled', false),
            ],
        ];

        // Generate thumbnails if requested
        if ($request->get('generate_thumbnails', false)) {
            $thumbnails = $this->fileUploadService->createThumbnails($result['file_path']);
            $response['thumbnails'] = $thumbnails;
        }

        return response()->json($response);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/uploads/file",
     *     tags={"File Upload"},
     *     summary="Delete uploaded file",
     *     description="Delete a file from storage. Users can only delete their own files.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="file_path", type="string", example="uploads/123/1692014400_abc123.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File deleted successfully")
     *         )
     *     )
     * )
     */
    public function deleteFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $filePath = $request->file_path;

        $deleted = $this->fileUploadService->deleteFile($filePath, $user);

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file or file not found',
            ], 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/uploads/limits",
     *     tags={"File Upload"},
     *     summary="Get upload limits and usage",
     *     description="Get current upload limits, usage statistics, and file size restrictions for the authenticated user.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Upload limits retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="upload_limits", type="object",
     *                 @OA\Property(property="daily_upload_limit", type="string", example="unlimited"),
     *                 @OA\Property(property="daily_uploads_used", type="integer", example=5),
     *                 @OA\Property(property="max_file_size", type="object"),
     *                 @OA\Property(property="allowed_formats", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="is_premium", type="boolean", example=true)
     *             )
     *         )
     *     )
     * )
     */
    public function getUploadLimits(Request $request)
    {
        $user = $request->user();
        $limits = $this->fileUploadService->getUploadLimits($user);

        return response()->json([
            'success' => true,
            'upload_limits' => $limits,
        ]);
    }
}