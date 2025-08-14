<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class UploadController extends Controller
{
    public function avatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        try {
            $file = $request->file('avatar');
            $filename = 'avatars/' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Resize and optimize image
            $image = Image::make($file)
                         ->fit(200, 200) // Square crop to 200x200
                         ->encode('jpg', 85); // Convert to JPEG with 85% quality

            // Store the image
            Storage::put($filename, $image->__toString());

            // Delete old avatar if exists
            if ($user->avatar && Storage::exists($user->avatar)) {
                Storage::delete($user->avatar);
            }

            // Update user avatar
            $user->update(['avatar' => $filename]);

            $avatarUrl = Storage::url($filename);

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'avatar_url' => $avatarUrl,
                'avatar_path' => $filename,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function image(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'type' => 'required|string|in:template_background,user_upload',
            'quality' => 'nullable|integer|between:60,100',
            'width' => 'nullable|integer|between:100,2000',
            'height' => 'nullable|integer|between:100,2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Check upload limits for free users
        if (!$user->isPremium()) {
            $todayUploads = \DB::table('user_creations')
                             ->where('user_id', $user->id)
                             ->whereDate('created_at', today())
                             ->count();

            if ($todayUploads >= 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Daily upload limit reached. Upgrade to premium for unlimited uploads.',
                    'is_premium_required' => true,
                ], 429);
            }
        }

        try {
            $file = $request->file('image');
            $type = $request->type;
            $quality = $request->get('quality', 85);
            $width = $request->get('width');
            $height = $request->get('height');

            $filename = $type . '/' . $user->id . '_' . time() . '_' . uniqid() . '.jpg';

            $image = Image::make($file);

            // Resize if dimensions provided
            if ($width && $height) {
                $image->fit($width, $height);
            } elseif ($width) {
                $image->widen($width);
            } elseif ($height) {
                $image->heighten($height);
            }

            // Optimize for web
            $image->encode('jpg', $quality);

            // Store the image
            Storage::put($filename, $image->__toString());

            $imageUrl = Storage::url($filename);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'image_url' => $imageUrl,
                'image_path' => $filename,
                'image_info' => [
                    'width' => $image->width(),
                    'height' => $image->height(),
                    'size' => Storage::size($filename),
                    'mime_type' => 'image/jpeg',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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

        // Security check: ensure user can only delete their own files
        if (!str_contains($filePath, $user->id . '_')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this file',
            ], 403);
        }

        try {
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);

                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getUploadLimits(Request $request)
    {
        $user = $request->user();

        $todayUploads = \DB::table('user_creations')
                         ->where('user_id', $user->id)
                         ->whereDate('created_at', today())
                         ->count();

        $limits = [
            'daily_upload_limit' => $user->isPremium() ? 'unlimited' : 10,
            'daily_uploads_used' => $todayUploads,
            'max_file_size_mb' => $user->isPremium() ? 10 : 5,
            'allowed_formats' => ['jpeg', 'jpg', 'png', 'gif'],
            'is_premium' => $user->isPremium(),
        ];

        return response()->json([
            'success' => true,
            'upload_limits' => $limits,
        ]);
    }
}