<?php

namespace App\Jobs;

use App\Models\Template;
use App\Models\Theme;
use App\Services\OpenRouterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkAIGeneration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour
    public $tries = 1;

    protected $theme;
    protected string $type;
    protected array $prompts;
    protected int $countPerPrompt;
    protected string $style;
    protected string $length;
    protected int $adminId;
    protected string $jobId;

    public function __construct(
        Theme $theme,
        string $type,
        array $prompts,
        int $countPerPrompt,
        string $style,
        string $length,
        int $adminId
    )
    {
        $this->theme = $theme;
        $this->type = $type;
        $this->prompts = $prompts;
        $this->countPerPrompt = $countPerPrompt;
        $this->style = $style;
        $this->length = $length;
        $this->adminId = $adminId;
        $this->jobId = uniqid('bulk_ai_');
        
        $this->onQueue('bulk_generation');
    }

    public function handle(OpenRouterService $openRouterService): void
    {
        try {
            Log::info('Starting bulk AI generation', [
                'job_id' => $this->jobId,
                'theme' => $this->theme->name,
                'type' => $this->type,
                'prompts_count' => count($this->prompts),
                'count_per_prompt' => $this->countPerPrompt,
                'style' => $this->style,
                'length' => $this->length,
                'admin_id' => $this->adminId,
            ]);

            $results = [];
            $totalGenerated = 0;
            $totalCost = 0;

            foreach ($this->prompts as $index => $prompt) {
                $promptResults = [];

                for ($i = 0; $i < $this->countPerPrompt; $i++) {
                    try {
                        $quote = $openRouterService->generateTamilQuote(
                            $prompt,
                            $this->type,
                            $this->style,
                            $this->length
                        );

                        if ($quote) {
                            $templateData = [
                                'name' => "AI Generated - " . ucfirst($this->type) . " " . ($index + 1) . "-" . ($i + 1),
                                'content' => $quote,
                                'theme_id' => $this->theme->id,
                                'type' => $this->type,
                                'tags' => "ai-generated,{$this->type},{$this->style}",
                                'is_premium' => false,
                                'is_active' => true,
                                'is_featured' => false,
                                'sort_order' => 0,
                            ];

                            $template = $this->createTemplate($templateData);
                            
                            $promptResults[] = [
                                'template_id' => $template->id,
                                'content' => $quote,
                                'prompt' => $prompt,
                            ];
                            
                            $totalGenerated++;
                            $totalCost += 0.02; // Estimate

                            Log::info("Generated content " . ($i + 1) . "/{$this->countPerPrompt} for prompt " . ($index + 1));
                        } else {
                            Log::warning("Failed to generate content for prompt " . ($index + 1));
                        }

                        // Add delay between requests
                        usleep(1000000); // 1 second delay

                    } catch (\Exception $e) {
                        Log::error("Error generating content for prompt " . ($index + 1), [
                            'error' => $e->getMessage(),
                            'iteration' => $i + 1,
                        ]);
                    }
                }

                $results["prompt_" . ($index + 1)] = $promptResults;

                Log::info("Completed prompt " . ($index + 1) . ": {$this->countPerPrompt} requested, " . count($promptResults) . " generated");
            }

            $summary = [
                'job_id' => $this->jobId,
                'total_requested' => count($this->prompts) * $this->countPerPrompt,
                'total_generated' => $totalGenerated,
                'total_cost' => round($totalCost, 6),
                'prompts_processed' => count($this->prompts),
                'theme' => $this->theme->name,
                'type' => $this->type,
                'style' => $this->style,
                'length' => $this->length,
                'templates_created' => $totalGenerated,
                'completed_at' => now()->toISOString(),
            ];

            // Store results in cache
            cache()->put(
                "bulk_generation_result:{$this->jobId}",
                [
                    'success' => true,
                    'results' => $results,
                    'summary' => $summary,
                ],
                now()->addDays(7)
            );

            Log::info('Bulk AI generation completed successfully', $summary);

        } catch (\Exception $e) {
            Log::error('Bulk AI generation failed', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
            ]);

            cache()->put(
                "bulk_generation_result:{$this->jobId}",
                [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'job_id' => $this->jobId,
                ],
                now()->addDays(7)
            );

            throw $e;
        }
    }

    private function createTemplate(array $templateData): Template
    {
        try {
            return Template::create($templateData);
        } catch (\Exception $e) {
            Log::error('Failed to create template from AI generation', [
                'template_data' => $templateData,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk AI generation job failed permanently', [
            'job_id' => $this->jobId,
            'error' => $exception->getMessage(),
        ]);

        cache()->put(
            "bulk_generation_result:{$this->jobId}",
            [
                'success' => false,
                'error' => 'Bulk generation failed: ' . $exception->getMessage(),
                'job_id' => $this->jobId,
                'final_failure' => true,
            ],
            now()->addDays(7)
        );
    }
}