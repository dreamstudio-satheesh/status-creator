<?php

namespace App\Services;

use App\Models\AIGenerationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterService
{
    private string $apiKey;
    private string $baseUrl;
    private string $defaultModel;
    private array $modelPricing;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key');
        $this->baseUrl = config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $this->defaultModel = config('services.openrouter.model', 'meta-llama/llama-3.2-3b-instruct:free');
        
        // Pricing per 1M tokens (approximate)
        $this->modelPricing = [
            'meta-llama/llama-3.2-3b-instruct:free' => ['input' => 0, 'output' => 0],
            'meta-llama/llama-3.2-8b-instruct:free' => ['input' => 0, 'output' => 0],
            'meta-llama/llama-3.1-8b-instruct:free' => ['input' => 0, 'output' => 0],
            'anthropic/claude-3-haiku' => ['input' => 0.25, 'output' => 1.25],
            'openai/gpt-3.5-turbo' => ['input' => 0.5, 'output' => 1.5],
            'openai/gpt-4o-mini' => ['input' => 0.15, 'output' => 0.6],
        ];
    }

    public function generateTamilQuote(array $params): array
    {
        $theme = $params['theme'] ?? 'general';
        $style = $params['style'] ?? 'inspirational';
        $length = $params['length'] ?? 'medium';
        $context = $params['context'] ?? '';
        $userId = $params['user_id'] ?? null;
        $templateId = $params['template_id'] ?? null;
        $model = $params['model'] ?? $this->defaultModel;

        try {
            $startTime = microtime(true);
            
            $prompt = $this->buildTamilQuotePrompt($theme, $style, $length, $context);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => 'Tamil Status Creator',
            ])
            ->timeout(60)
            ->post($this->baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a Tamil language expert and poet. Generate meaningful, culturally appropriate Tamil quotes and sayings. Always respond in proper Tamil script (தமிழ்).'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $this->getMaxTokensForLength($length),
                'temperature' => 0.8,
                'top_p' => 0.9,
                'frequency_penalty' => 0.3,
                'presence_penalty' => 0.2,
                'stream' => false,
            ]);

            $endTime = microtime(true);
            $processingTime = round(($endTime - $startTime) * 1000, 2);

            if ($response->successful()) {
                $result = $response->json();
                
                $quote = $result['choices'][0]['message']['content'] ?? '';
                $usage = $result['usage'] ?? [];
                $inputTokens = $usage['prompt_tokens'] ?? 0;
                $outputTokens = $usage['completion_tokens'] ?? 0;
                $totalTokens = $usage['total_tokens'] ?? 0;

                // Calculate cost
                $cost = $this->calculateCost($model, $inputTokens, $outputTokens);

                // Clean and validate Tamil quote
                $cleanedQuote = $this->cleanTamilQuote($quote);

                $this->logGeneration([
                    'user_id' => $userId,
                    'template_id' => $templateId,
                    'model_used' => $model,
                    'prompt' => $prompt,
                    'response' => $cleanedQuote,
                    'tokens_used' => $totalTokens,
                    'cost' => $cost,
                    'status' => 'success',
                    'metadata' => [
                        'theme' => $theme,
                        'style' => $style,
                        'length' => $length,
                        'processing_time' => $processingTime,
                        'input_tokens' => $inputTokens,
                        'output_tokens' => $outputTokens,
                    ],
                ]);

                return [
                    'success' => true,
                    'quote' => $cleanedQuote,
                    'metadata' => [
                        'theme' => $theme,
                        'style' => $style,
                        'length' => $length,
                        'model_used' => $model,
                        'tokens_used' => $totalTokens,
                        'cost' => $cost,
                        'processing_time_ms' => $processingTime,
                    ],
                ];
            } else {
                $error = $response->json();
                $errorMessage = $error['error']['message'] ?? 'Unknown error from OpenRouter API';

                $this->logGeneration([
                    'user_id' => $userId,
                    'template_id' => $templateId,
                    'model_used' => $model,
                    'prompt' => $prompt,
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                    'metadata' => [
                        'theme' => $theme,
                        'style' => $style,
                        'length' => $length,
                        'processing_time' => $processingTime,
                        'status_code' => $response->status(),
                    ],
                ]);

                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'processing_time_ms' => $processingTime,
                ];
            }

        } catch (\Exception $e) {
            Log::error('OpenRouter Tamil quote generation failed', [
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            $this->logGeneration([
                'user_id' => $userId,
                'template_id' => $templateId,
                'model_used' => $model,
                'prompt' => $prompt ?? 'Failed to build prompt',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'metadata' => [
                    'theme' => $theme,
                    'style' => $style,
                    'length' => $length,
                ],
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate Tamil quote: ' . $e->getMessage(),
            ];
        }
    }

    public function generateBulkQuotes(array $themes, int $countPerTheme = 10, string $model = null): array
    {
        $model = $model ?? $this->defaultModel;
        $results = [];
        $totalGenerated = 0;
        $totalCost = 0;

        foreach ($themes as $theme) {
            $themeResults = [];
            
            for ($i = 0; $i < $countPerTheme; $i++) {
                $styles = ['inspirational', 'emotional', 'philosophical', 'traditional'];
                $lengths = ['short', 'medium'];
                
                $style = $styles[array_rand($styles)];
                $length = $lengths[array_rand($lengths)];

                $result = $this->generateTamilQuote([
                    'theme' => $theme,
                    'style' => $style,
                    'length' => $length,
                    'model' => $model,
                ]);

                if ($result['success']) {
                    $themeResults[] = [
                        'quote' => $result['quote'],
                        'style' => $style,
                        'length' => $length,
                        'metadata' => $result['metadata'],
                    ];
                    $totalGenerated++;
                    $totalCost += $result['metadata']['cost'];
                }

                // Add delay to avoid rate limiting
                usleep(500000); // 0.5 seconds
            }

            $results[$theme] = $themeResults;
        }

        return [
            'success' => true,
            'themes' => $results,
            'summary' => [
                'total_requested' => count($themes) * $countPerTheme,
                'total_generated' => $totalGenerated,
                'total_cost' => round($totalCost, 6),
                'themes_processed' => count($themes),
                'model_used' => $model,
            ],
        ];
    }

    private function buildTamilQuotePrompt(string $theme, string $style, string $length, string $context): string
    {
        $themeInstructions = [
            'love' => 'காதல் மற்றும் அன்பு பற்றிய',
            'motivation' => 'ஊக்கம் மற்றும் உத்வேகம் தரும்',
            'life' => 'வாழ்க்கை மற்றும் அனுபவம் பற்றிய',
            'success' => 'வெற்றி மற்றும் சாதனை பற்றிய',
            'friendship' => 'நட்பு மற்றும் தோழமை பற்றிய',
            'family' => 'குடும்பம் மற்றும் உறவுகள் பற்றிய',
            'spiritual' => 'ஆன்மீகம் மற்றும் தத்துவம் பற்றிய',
            'nature' => 'இயற்கை மற்றும் சுற்றுச்சூழல் பற்றிய',
            'wisdom' => 'ஞானம் மற்றும் அறிவு பற்றிய',
            'hope' => 'நம்பிக்கை மற்றும் எதிர்காலம் பற்றிய',
        ];

        $styleInstructions = [
            'inspirational' => 'உத்வேகம் அளிக்கும் விதத்தில்',
            'emotional' => 'உணர்வுபூர்வமான முறையில்',
            'philosophical' => 'தத்துவ சிந்தனையுடன்',
            'traditional' => 'பாரம்பரிய தமிழ் கலாச்சாரத்தின் அடிப்படையில்',
            'modern' => 'நவீன சிந்தனையுடன்',
            'poetic' => 'கவிதை நயத்துடன்',
        ];

        $lengthInstructions = [
            'short' => '10-15 வார்த்தைகளில் குறுகிய',
            'medium' => '20-30 வார்த்தைகளில் நடுத்தர',
            'long' => '40-50 வார்த்தைகளில் விரிவான',
        ];

        $themeDesc = $themeInstructions[$theme] ?? 'பொதுவான';
        $styleDesc = $styleInstructions[$style] ?? 'எளிமையான';
        $lengthDesc = $lengthInstructions[$length] ?? 'நடுத்தர';

        $prompt = "{$themeDesc} {$styleDesc} {$lengthDesc} தமிழ் வாக்கியம் ஒன்றை உருவாக்கவும்.";

        if (!empty($context)) {
            $prompt .= " சூழல்: {$context}";
        }

        $prompt .= "\n\nவழிமுறைகள்:
1. தூய தமிழில் மட்டும் எழுதவும்
2. அர்த்தமுள்ள மற்றும் ஆழமான கருத்து இருக்க வேண்டும்
3. சமூக வலைதளங்களில் பகிர ஏற்ற வகையில் இருக்க வேண்டும்
4. எதிர்மறை அல்லது வன்முறை கருத்துகள் இல்லாமல் இருக்க வேண்டும்
5. தமிழ் கலாச்சாரத்திற்கு ஏற்ற வகையில் இருக்க வேண்டும்

மட்டும் தமிழ் வாக்கியத்தை மட்டும் வழங்கவும், வேறு எந்த விளக்கமும் வேண்டாம்.";

        return $prompt;
    }

    private function getMaxTokensForLength(string $length): int
    {
        return match ($length) {
            'short' => 50,
            'medium' => 100,
            'long' => 150,
            default => 100,
        };
    }

    private function cleanTamilQuote(string $quote): string
    {
        // Remove common AI response prefixes/suffixes
        $quote = preg_replace('/^(Here is|Here\'s|Tamil quote:|Quote:)/i', '', $quote);
        $quote = preg_replace('/\n\n.*$/s', '', $quote); // Remove explanations after double newline
        
        // Clean up whitespace
        $quote = trim($quote);
        $quote = preg_replace('/\s+/', ' ', $quote);
        
        // Remove quotes if wrapped
        $quote = trim($quote, '"');
        $quote = trim($quote, "'");
        $quote = trim($quote, "\u{201C}"); // Left double quotation mark
        $quote = trim($quote, "\u{201D}"); // Right double quotation mark
        $quote = trim($quote, "\u{2018}"); // Left single quotation mark
        $quote = trim($quote, "\u{2019}"); // Right single quotation mark
        
        return $quote;
    }

    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        if (!isset($this->modelPricing[$model])) {
            return 0; // Free or unknown model
        }

        $pricing = $this->modelPricing[$model];
        $inputCost = ($inputTokens / 1000000) * $pricing['input'];
        $outputCost = ($outputTokens / 1000000) * $pricing['output'];

        return round($inputCost + $outputCost, 6);
    }

    private function logGeneration(array $data): void
    {
        try {
            AIGenerationLog::create([
                'user_id' => $data['user_id'],
                'template_id' => $data['template_id'],
                'prompt' => $data['prompt'],
                'response' => $data['response'],
                'model_used' => $data['model_used'],
                'tokens_used' => $data['tokens_used'],
                'cost' => $data['cost'],
                'status' => $data['status'],
                'error_message' => $data['error_message'] ?? null,
                'metadata' => json_encode($data['metadata'] ?? []),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log AI generation', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }

    public function getAvailableModels(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->timeout(10)->get($this->baseUrl . '/models');

            if ($response->successful()) {
                $models = $response->json()['data'] ?? [];
                
                return array_map(function ($model) {
                    return [
                        'id' => $model['id'],
                        'name' => $model['name'] ?? $model['id'],
                        'pricing' => $this->modelPricing[$model['id']] ?? null,
                        'context_length' => $model['context_length'] ?? null,
                    ];
                }, $models);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch OpenRouter models', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->timeout(10)->get($this->baseUrl . '/models');

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'message' => $response->successful() ? 'Connection successful' : 'Connection failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }
}