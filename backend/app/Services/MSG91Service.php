<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MSG91Service
{
    private string $authKey;
    private string $senderId;
    private string $route;
    private string $templateId;

    public function __construct()
    {
        $this->authKey = config('services.msg91.auth_key');
        $this->senderId = config('services.msg91.sender_id');
        $this->route = config('services.msg91.route');
        $this->templateId = config('services.msg91.template_id');
    }

    public function sendOTP(string $mobile, string $otp): array
    {
        try {
            $response = Http::post('https://api.msg91.com/api/v5/otp', [
                'authkey' => $this->authKey,
                'template_id' => $this->templateId,
                'mobile' => $this->formatMobile($mobile),
                'otp' => $otp,
                'sender' => $this->senderId,
                'DLT_TE_ID' => $this->templateId,
            ]);

            $responseData = $response->json();

            Log::info('MSG91 OTP Response', [
                'mobile' => $mobile,
                'response' => $responseData,
                'status_code' => $response->status()
            ]);

            if ($response->successful() && $responseData['type'] === 'success') {
                return [
                    'success' => true,
                    'message' => 'OTP sent successfully',
                    'request_id' => $responseData['request_id'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'Failed to send OTP',
                'error_code' => $responseData['code'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('MSG91 OTP Send Error', [
                'mobile' => $mobile,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send OTP due to service error',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function verifyOTP(string $mobile, string $otp): array
    {
        try {
            $response = Http::post('https://api.msg91.com/api/v5/otp/verify', [
                'authkey' => $this->authKey,
                'mobile' => $this->formatMobile($mobile),
                'otp' => $otp,
            ]);

            $responseData = $response->json();

            Log::info('MSG91 OTP Verify Response', [
                'mobile' => $mobile,
                'response' => $responseData,
                'status_code' => $response->status()
            ]);

            if ($response->successful() && $responseData['type'] === 'success') {
                return [
                    'success' => true,
                    'message' => 'OTP verified successfully',
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'Invalid OTP',
                'error_code' => $responseData['code'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('MSG91 OTP Verify Error', [
                'mobile' => $mobile,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to verify OTP due to service error',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function resendOTP(string $mobile): array
    {
        try {
            $response = Http::post('https://api.msg91.com/api/v5/otp/retry', [
                'authkey' => $this->authKey,
                'mobile' => $this->formatMobile($mobile),
                'retrytype' => 'text',
            ]);

            $responseData = $response->json();

            Log::info('MSG91 OTP Resend Response', [
                'mobile' => $mobile,
                'response' => $responseData,
                'status_code' => $response->status()
            ]);

            if ($response->successful() && $responseData['type'] === 'success') {
                return [
                    'success' => true,
                    'message' => 'OTP resent successfully',
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'Failed to resend OTP',
                'error_code' => $responseData['code'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('MSG91 OTP Resend Error', [
                'mobile' => $mobile,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to resend OTP due to service error',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function formatMobile(string $mobile): string
    {
        $mobile = preg_replace('/[^\d]/', '', $mobile);
        
        if (strlen($mobile) === 10) {
            return '91' . $mobile;
        }
        
        if (strlen($mobile) === 12 && str_starts_with($mobile, '91')) {
            return $mobile;
        }
        
        if (strlen($mobile) === 13 && str_starts_with($mobile, '+91')) {
            return substr($mobile, 1);
        }
        
        return $mobile;
    }
}