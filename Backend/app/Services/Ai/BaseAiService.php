<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BaseAiService
{
    protected string $aiEndpoint;

    protected string $apiKey;

    public function __construct()
    {
        // إعدادات الاتصال بخدمة الـ AI (توضع في ملف .env)
        $this->aiEndpoint = config('services.ai.url', 'https://api.ai-service.local/');
        $this->apiKey = config('services.ai.key', 'default-key');
    }

    /**
     * إرسال طلب موحد للـ AI مع تتبع الـ Metadata
     */
    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    protected function sendRequest(string $endpoint, array $payload): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post("{$this->aiEndpoint}/{$endpoint}", [
                    'metadata' => [
                        'timestamp' => now()->toIso8601String(),
                        'environment' => config('app.env'),
                    ],
                    'payload' => $payload,
                ]);

            if ($response->failed()) {
                Log::error("AI Service Error [{$endpoint}]: ".$response->body());
                throw ValidationException::withMessages([
                    'ai' => __('ai.service_error') ?: 'حدث خطأ أثناء معالجة الذكاء الاصطناعي، يجيب المحاولة لاحقاً.',
                ]);
            }

            $result = $response->json();

            // التحقق من توفر الـ Success والـ Confidence في الاستجابة الموحدة
            return [
                'success' => $result['success'] ?? true,
                'output' => $result['output'] ?? [],
                'confidence' => $result['confidence'] ?? 1.0,
                'error' => $result['error'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('AI Connection Exception: '.$e->getMessage());
            throw ValidationException::withMessages([
                'ai' => __('ai.connection_failed') ?: 'تعذر الاتصال بخدمة الذكاء الاصطناعي.',
            ]);
        }
    }
}
