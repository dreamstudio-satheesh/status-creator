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

    protected array $themes;
    protected int $countPerTheme;
    protected string $jobId;
    protected array $options;

    public function __construct(array $themes, int $countPerTheme = 10, array $options = [])
    {
        $this->themes = $themes;
        $this->countPerTheme = $countPerTheme;
        $this->jobId = uniqid('bulk_ai_');
        $this->options = array_merge([
            'create_templates' => true,
            'model' => 'meta-llama/llama-3.2-3b-instruct:free',
            'delay_between_requests' => 1000, // ms
        ], $options);
        
        $this->onQueue('bulk_generation');
    }

    public function handle(OpenRouterService $openRouterService): void
    {
        try {
            Log::info('Starting bulk AI generation', [
                'job_id' => $this->jobId,
                'themes' => $this->themes,
                'count_per_theme' => $this->countPerTheme,
                'options' => $this->options,
            ]);

            $results = [];
            $totalGenerated = 0;
            $totalCost = 0;

            foreach ($this->themes as $themeName) {
                $theme = Theme::where('slug', $themeName)->orWhere('name', $themeName)->first();
                
                if (!$theme) {
                    Log::warning("Theme not found: {$themeName}");
                    continue;
                }

                $themeResults = [];

                for ($i = 0; $i < $this->countPerTheme; $i++) {
                    try {
                        $styles = ['inspirational', 'emotional', 'philosophical', 'traditional'];
                        $lengths = ['short', 'medium'];
                        
                        $style = $styles[array_rand($styles)];
                        $length = $lengths[array_rand($lengths)];

                        $result = $openRouterService->generateTamilQuote([
                            'theme' => $themeName,
                            'style' => $style,
                            'length' => $length,
                            'model' => $this->options['model'],
                        ]);

                        if ($result['success']) {
                            $quoteData = [
                                'quote' => $result['quote'],
                                'style' => $style,
                                'length' => $length,
                                'metadata' => $result['metadata'],
                            ];

                            $themeResults[] = $quoteData;
                            $totalGenerated++;
                            $totalCost += $result['metadata']['cost'];

                            // Create template if option is enabled
                            if ($this->options['create_templates']) {
                                $this->createTemplate($theme, $quoteData);
                            }

                            Log::info("Generated quote " . ($i+1) . "/{$this->countPerTheme} for theme {$themeName}");
                        } else {
                            Log::warning("Failed to generate quote for theme {$themeName}: " . $result['error']);
                        }

                        // Add delay between requests
                        if ($this->options['delay_between_requests'] > 0) {
                            usleep($this->options['delay_between_requests'] * 1000);
                        }

                    } catch (\Exception $e) {
                        Log::error("Error generating quote for theme {$themeName}", [
                            'error' => $e->getMessage(),
                            'iteration' => $i + 1,
                        ]);
                    }
                }

                $results[$themeName] = $themeResults;

                Log::info("Completed theme {$themeName}: {$this->countPerTheme} requested, " . count($themeResults) . " generated");
            }

            $summary = [
                'job_id' => $this->jobId,
                'total_requested' => count($this->themes) * $this->countPerTheme,
                'total_generated' => $totalGenerated,
                'total_cost' => round($totalCost, 6),
                'themes_processed' => count($this->themes),
                'model_used' => $this->options['model'],
                'templates_created' => $this->options['create_templates'] ? $totalGenerated : 0,
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

    private function createTemplate(Theme $theme, array $quoteData): void
    {
        try {
            Template::create([
                'theme_id' => $theme->id,
                'title' => 'AI Generated - ' . ucfirst($quoteData['style']) . ' ' . ucfirst($quoteData['length']),
                'quote_text' => $quoteData['quote'],
                'quote_text_ta' => $quoteData['quote'], // Same as Tamil
                'font_family' => 'Tamil',
                'font_size' => $quoteData['length'] === 'short' ? 28 : 24,
                'text_color' => $theme->color ?? '#FFFFFF',
                'text_alignment' => 'center',
                'padding' => 20,
                'is_premium' => false,
                'is_featured' => false,
                'is_active' => true,
                'ai_generated' => true,
                'image_caption' => "AI generated {$quoteData['style']} quote about {$theme->name}",
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create template from AI generation', [
                'theme_id' => $theme->id,
                'quote' => $quoteData['quote'],
                'error' => $e->getMessage(),
            ]);
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