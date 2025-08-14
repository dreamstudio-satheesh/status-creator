<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\OpenRouterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAIGeneration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // 2 minutes
    public $tries = 3;
    public $maxExceptions = 2;

    protected array $params;
    protected ?int $userId;
    protected string $jobId;

    public function __construct(array $params, ?int $userId = null, string $jobId = null)
    {
        $this->params = $params;
        $this->userId = $userId;
        $this->jobId = $jobId ?? uniqid('ai_gen_');
        
        // Queue configuration
        $this->onQueue('ai_generation');
    }

    public function handle(OpenRouterService $openRouterService): void
    {
        try {
            Log::info('Processing AI generation job', [
                'job_id' => $this->jobId,
                'user_id' => $this->userId,
                'params' => $this->params,
            ]);

            // Check user quota if user is specified
            if ($this->userId) {
                $user = User::find($this->userId);
                
                if (!$user) {
                    throw new \Exception('User not found');
                }

                // Reset quota if needed
                if ($user->last_quota_reset !== today()) {
                    $user->update([
                        'daily_ai_used' => 0,
                        'last_quota_reset' => today(),
                    ]);
                    $user->refresh();
                }

                // Check quota
                if ($user->daily_ai_used >= $user->daily_ai_quota) {
                    throw new \Exception('User quota exceeded');
                }
            }

            // Generate the quote
            $result = $openRouterService->generateTamilQuote(array_merge($this->params, [
                'user_id' => $this->userId,
            ]));

            if ($result['success']) {
                // Increment user quota if applicable
                if ($this->userId && isset($user)) {
                    $user->increment('daily_ai_used');
                }

                // Store result in cache for retrieval
                cache()->put(
                    "ai_generation_result:{$this->jobId}",
                    $result,
                    now()->addHours(24)
                );

                Log::info('AI generation job completed successfully', [
                    'job_id' => $this->jobId,
                    'user_id' => $this->userId,
                    'quote_length' => strlen($result['quote']),
                ]);
            } else {
                throw new \Exception('AI generation failed: ' . ($result['error'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            Log::error('AI generation job failed', [
                'job_id' => $this->jobId,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'params' => $this->params,
            ]);

            // Store error result in cache
            cache()->put(
                "ai_generation_result:{$this->jobId}",
                [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'job_id' => $this->jobId,
                ],
                now()->addHours(24)
            );

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('AI generation job failed permanently', [
            'job_id' => $this->jobId,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'params' => $this->params,
        ]);

        // Store final failure result in cache
        cache()->put(
            "ai_generation_result:{$this->jobId}",
            [
                'success' => false,
                'error' => 'AI generation failed after multiple attempts: ' . $exception->getMessage(),
                'job_id' => $this->jobId,
                'final_failure' => true,
            ],
            now()->addHours(24)
        );
    }

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10);
    }
}