<?php

namespace App\Services;

use App\Models\AIGenerationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HuggingFaceService
{
    private string $apiKey;
    private string $baseUrl;
    private array $models;

    public function __construct()
    {
        $this->apiKey = config('services.huggingface.api_key');
        $this->baseUrl = 'https://api-inference.huggingface.co/models/';
        $this->models = [
            'image_captioning' => config('services.huggingface.caption_model', 'Salesforce/blip-image-captioning-base'),
            'image_classification' => 'google/vit-base-patch16-224',
            'object_detection' => 'facebook/detr-resnet-50',
        ];
    }

    public function captionImage(string $imageUrl, ?int $userId = null): array
    {
        try {
            $startTime = microtime(true);
            
            // Download image content
            $imageResponse = Http::timeout(30)->get($imageUrl);
            
            if (!$imageResponse->successful()) {
                throw new \Exception('Failed to download image from URL');
            }

            $imageData = $imageResponse->body();
            
            // Send to Hugging Face API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/octet-stream',
            ])
            ->timeout(60)
            ->withBody($imageData, 'application/octet-stream')
            ->post($this->baseUrl . $this->models['image_captioning']);

            $endTime = microtime(true);
            $processingTime = round(($endTime - $startTime) * 1000, 2); // ms

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result[0]['generated_text'])) {
                    $caption = $result[0]['generated_text'];
                    $confidence = $result[0]['score'] ?? null;

                    // Log successful generation
                    $this->logGeneration([
                        'user_id' => $userId,
                        'model_used' => $this->models['image_captioning'],
                        'prompt' => 'Image captioning for: ' . $imageUrl,
                        'response' => $caption,
                        'status' => 'success',
                        'processing_time' => $processingTime,
                        'confidence' => $confidence,
                    ]);

                    return [
                        'success' => true,
                        'caption' => $caption,
                        'confidence' => $confidence,
                        'processing_time_ms' => $processingTime,
                        'model_used' => $this->models['image_captioning'],
                    ];
                }
            }

            $errorMessage = $response->json()['error'] ?? 'Unknown error from Hugging Face API';
            
            $this->logGeneration([
                'user_id' => $userId,
                'model_used' => $this->models['image_captioning'],
                'prompt' => 'Image captioning for: ' . $imageUrl,
                'status' => 'failed',
                'error_message' => $errorMessage,
                'processing_time' => $processingTime,
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'processing_time_ms' => $processingTime,
            ];

        } catch (\Exception $e) {
            Log::error('Hugging Face image captioning failed', [
                'image_url' => $imageUrl,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            $this->logGeneration([
                'user_id' => $userId,
                'model_used' => $this->models['image_captioning'],
                'prompt' => 'Image captioning for: ' . $imageUrl,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to process image: ' . $e->getMessage(),
            ];
        }
    }

    public function classifyImage(string $imageUrl, ?int $userId = null): array
    {
        try {
            $startTime = microtime(true);
            
            $imageResponse = Http::timeout(30)->get($imageUrl);
            
            if (!$imageResponse->successful()) {
                throw new \Exception('Failed to download image from URL');
            }

            $imageData = $imageResponse->body();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/octet-stream',
            ])
            ->timeout(60)
            ->withBody($imageData, 'application/octet-stream')
            ->post($this->baseUrl . $this->models['image_classification']);

            $endTime = microtime(true);
            $processingTime = round(($endTime - $startTime) * 1000, 2);

            if ($response->successful()) {
                $result = $response->json();
                
                $classifications = array_map(function ($item) {
                    return [
                        'label' => $item['label'],
                        'confidence' => round($item['score'], 4),
                    ];
                }, array_slice($result, 0, 5)); // Top 5 classifications

                $this->logGeneration([
                    'user_id' => $userId,
                    'model_used' => $this->models['image_classification'],
                    'prompt' => 'Image classification for: ' . $imageUrl,
                    'response' => json_encode($classifications),
                    'status' => 'success',
                    'processing_time' => $processingTime,
                ]);

                return [
                    'success' => true,
                    'classifications' => $classifications,
                    'processing_time_ms' => $processingTime,
                    'model_used' => $this->models['image_classification'],
                ];
            }

            $errorMessage = $response->json()['error'] ?? 'Unknown error from Hugging Face API';
            
            $this->logGeneration([
                'user_id' => $userId,
                'model_used' => $this->models['image_classification'],
                'prompt' => 'Image classification for: ' . $imageUrl,
                'status' => 'failed',
                'error_message' => $errorMessage,
                'processing_time' => $processingTime,
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'processing_time_ms' => $processingTime,
            ];

        } catch (\Exception $e) {
            Log::error('Hugging Face image classification failed', [
                'image_url' => $imageUrl,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to classify image: ' . $e->getMessage(),
            ];
        }
    }

    public function analyzeImageForTemplate(string $imageUrl, ?int $userId = null): array
    {
        $caption = $this->captionImage($imageUrl, $userId);
        $classification = $this->classifyImage($imageUrl, $userId);

        $analysis = [
            'image_url' => $imageUrl,
            'timestamp' => now()->toISOString(),
        ];

        if ($caption['success']) {
            $analysis['caption'] = $caption['caption'];
            $analysis['caption_confidence'] = $caption['confidence'];
        }

        if ($classification['success']) {
            $analysis['classifications'] = $classification['classifications'];
            $analysis['primary_category'] = $classification['classifications'][0]['label'] ?? null;
            $analysis['category_confidence'] = $classification['classifications'][0]['confidence'] ?? null;
        }

        // Generate theme suggestions based on analysis
        $analysis['suggested_themes'] = $this->suggestThemesFromAnalysis($analysis);

        return [
            'success' => true,
            'analysis' => $analysis,
            'processing_summary' => [
                'caption_success' => $caption['success'],
                'classification_success' => $classification['success'],
                'total_processing_time_ms' => ($caption['processing_time_ms'] ?? 0) + ($classification['processing_time_ms'] ?? 0),
            ],
        ];
    }

    private function suggestThemesFromAnalysis(array $analysis): array
    {
        $suggestions = [];
        
        // Theme mapping based on image content
        $themeKeywords = [
            'love' => ['couple', 'romantic', 'wedding', 'heart', 'kiss', 'bride', 'groom', 'flower', 'rose'],
            'nature' => ['tree', 'forest', 'mountain', 'sky', 'landscape', 'sunset', 'sunrise', 'beach', 'ocean'],
            'motivation' => ['success', 'achievement', 'trophy', 'winner', 'goal', 'business', 'work'],
            'family' => ['family', 'child', 'baby', 'parent', 'home', 'house', 'children'],
            'friendship' => ['friends', 'group', 'team', 'together', 'party', 'celebration'],
            'spiritual' => ['temple', 'prayer', 'religious', 'god', 'devotion', 'meditation'],
        ];

        $content = strtolower($analysis['caption'] ?? '');
        
        foreach ($themeKeywords as $theme => $keywords) {
            $matches = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($content, $keyword)) {
                    $matches++;
                }
            }
            
            if ($matches > 0) {
                $suggestions[] = [
                    'theme' => $theme,
                    'confidence' => min(1.0, $matches / count($keywords)),
                    'matched_keywords' => array_filter($keywords, fn($k) => str_contains($content, $k)),
                ];
            }
        }

        // Sort by confidence
        usort($suggestions, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        return array_slice($suggestions, 0, 3);
    }

    private function logGeneration(array $data): void
    {
        try {
            AIGenerationLog::create([
                'user_id' => $data['user_id'],
                'template_id' => $data['template_id'] ?? null,
                'prompt' => $data['prompt'],
                'response' => $data['response'] ?? null,
                'model_used' => $data['model_used'],
                'tokens_used' => $data['tokens_used'] ?? null,
                'cost' => $data['cost'] ?? 0,
                'status' => $data['status'],
                'error_message' => $data['error_message'] ?? null,
                'metadata' => json_encode([
                    'processing_time' => $data['processing_time'] ?? null,
                    'confidence' => $data['confidence'] ?? null,
                ]),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log AI generation', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }

    public function getModelStatus(): array
    {
        $status = [];
        
        foreach ($this->models as $type => $model) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])->timeout(10)->get($this->baseUrl . $model);

                $status[$type] = [
                    'model' => $model,
                    'available' => $response->successful(),
                    'status_code' => $response->status(),
                ];
            } catch (\Exception $e) {
                $status[$type] = [
                    'model' => $model,
                    'available' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $status;
    }
}