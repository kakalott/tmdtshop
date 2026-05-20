<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    public function __construct(
        private ShopContextService $shopContext,
        private ChatResponseFormatter $responseFormatter
    ) {}

    public function reply(string $message, ?User $user, string $guestSessionId): array
    {
        $conversation = $this->resolveConversation($user, $guestSessionId);

        $conversation->messages()->create([
            'role' => 'user',
            'content' => $message,
        ]);

        $apiKey = config('services.gemini.key');

        if (empty($apiKey)) {
            $fallback = 'Chatbot tạm thời chưa được cấu hình. Vui lòng liên hệ quản trị viên hoặc thử lại sau.';
            $this->storeAssistantMessage($conversation, $fallback, ['error' => 'missing_api_key']);

            return [
                'reply' => $fallback,
                'conversation_id' => $conversation->id,
            ];
        }

        try {
            $reply = $this->responseFormatter->format($this->callGemini($conversation, $user));
            $this->storeAssistantMessage($conversation, $reply);

            return [
                'reply' => $reply,
                'conversation_id' => $conversation->id,
            ];
        } catch (\Throwable $e) {
            Log::error('Chatbot Gemini error', [
                'message' => $e->getMessage(),
                'conversation_id' => $conversation->id,
            ]);

            $fallback = $this->responseFormatter->format($this->userFacingError($e));
            $this->storeAssistantMessage($conversation, $fallback, ['error' => 'api_failure']);

            return [
                'reply' => $fallback,
                'conversation_id' => $conversation->id,
            ];
        }
    }

    public function history(?User $user, string $guestSessionId): array
    {
        $conversation = $this->findConversation($user, $guestSessionId);

        if (! $conversation) {
            return ['messages' => []];
        }

        $messages = $conversation->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('id')
            ->get(['role', 'content', 'created_at']);

        return [
            'messages' => $messages->map(fn ($m) => [
                'role' => $m->role,
                'content' => $m->content,
                'at' => $m->created_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }

    private function resolveConversation(?User $user, string $guestSessionId): ChatConversation
    {
        $conversation = $this->findConversation($user, $guestSessionId);

        if ($conversation) {
            return $conversation;
        }

        return ChatConversation::create([
            'user_id' => $user?->id,
            'guest_session_id' => $user ? null : $guestSessionId,
            'status' => 'open',
        ]);
    }

    private function findConversation(?User $user, string $guestSessionId): ?ChatConversation
    {
        if ($user) {
            return ChatConversation::query()
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->latest('id')
                ->first();
        }

        return ChatConversation::query()
            ->whereNull('user_id')
            ->where('guest_session_id', $guestSessionId)
            ->where('status', 'open')
            ->latest('id')
            ->first();
    }

    private function callGemini(ChatConversation $conversation, ?User $user): string
    {
        $apiKey = config('services.gemini.key');
        $models = array_values(array_unique(array_filter(array_merge(
            [config('chatbot.model', 'gemini-2.5-flash')],
            config('chatbot.fallback_models', [])
        ))));

        $contents = collect($this->buildMessageHistory($conversation))
            ->map(fn (array $msg) => [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]],
            ])
            ->values()
            ->all();

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $this->buildSystemPrompt($user)]],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 800,
            ],
        ];

        $lastError = 'Không gọi được Gemini API';
        $retryableStatuses = [404, 429, 500, 502, 503];

        foreach ($models as $index => $model) {
            if ($index > 0) {
                usleep(500000);
            }

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

            $response = Http::timeout(45)
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->post($url, $payload);

            if (! $response->successful()) {
                $lastError = 'Gemini HTTP ' . $response->status() . ' (' . $model . '): ' . $response->body();
                if (in_array($response->status(), $retryableStatuses, true)) {
                    continue;
                }
                throw new \RuntimeException($lastError);
            }

            $content = $response->json('candidates.0.content.parts.0.text');

            if (is_string($content) && trim($content) !== '') {
                return trim($content);
            }

            $blockReason = $response->json('promptFeedback.blockReason')
                ?? $response->json('candidates.0.finishReason');
            $lastError = 'Gemini empty content (' . $model . ')' . ($blockReason ? " ({$blockReason})" : '');
        }

        throw new \RuntimeException($lastError);
    }

    private function userFacingError(\Throwable $e): string
    {
        $msg = $e->getMessage();

        if (str_contains($msg, 'HTTP 429') || str_contains($msg, 'RESOURCE_EXHAUSTED')) {
            return 'API Gemini đã hết hạn mức (quota). Một key dùng cho nhiều dự án sẽ chia sẻ cùng quota — hãy đợi vài phút, tạo key/project riêng, hoặc bật billing tại Google AI Studio rồi thử lại.';
        }

        if (str_contains($msg, 'HTTP 503') || str_contains($msg, 'UNAVAILABLE')) {
            return 'Gemini đang quá tải tạm thời (503). Vui lòng thử lại sau 1–2 phút.';
        }

        if (str_contains($msg, 'HTTP 400') || str_contains($msg, 'API key not valid')) {
            return 'API key Gemini không hợp lệ. Kiểm tra GEMINI_API_KEY trong file .env.';
        }

        if (config('app.debug')) {
            return 'Lỗi chatbot: ' . mb_substr($msg, 0, 200);
        }

        return 'Xin lỗi, hệ thống đang gặp sự cố. Bạn có thể thử lại sau hoặc xem đơn hàng [tại đây](/profile/orders).';
    }

    private function buildSystemPrompt(?User $user): string
    {
        $context = $this->shopContext->build($user);
        $shopName = $this->shopName();

        return <<<PROMPT
Bạn là trợ lý chăm sóc khách hàng của {$shopName}. Trả lời bằng tiếng Việt, thân thiện, ngắn gọn (2–6 câu trừ khi khách cần chi tiết).

QUY TẮC:
- Chỉ dùng thông tin trong DỮ LIỆU CỬA HÀNG bên dưới. Không bịa giá, tồn kho, trạng thái đơn.
- Nếu không chắc, nói rõ và gợi ý đăng nhập bằng link [tại đây](url đăng nhập trong dữ liệu).
- Không yêu cầu mật khẩu, OTP, thông tin thẻ ngân hàng.
- Không tư vấn ngoài phạm vi mua sắm tại shop.

ĐỊNH DẠNG TRẢ LỜI (bắt buộc):
- Dùng đúng [ID số] trong dữ liệu (VD: ID 180001), không tự đổi mã.
- Link trang sản phẩm: [tại đây](/product/ID) — copy đúng trường "trang" (đường dẫn /product/...).
- Link trang khác (giỏ, đơn hàng): [tại đây](/cart), [tại đây](/profile/orders) theo mục liên kết.
- Không ghi URL dạng http://localhost hay http://127.0.0.1.
- Liệt kê sản phẩm: mỗi loại một dòng bullet (* ), ghi [ID số], tên, giá, tồn kho, "xem chi tiết tại đây". Hệ thống tự chèn ảnh + chuẩn hóa ngay trên từng dòng.
- Không tự thêm khối ảnh riêng ở cuối tin nhắn.

DỮ LIỆU CỬA HÀNG (cập nhật theo thời điểm chat):
{$context}
PROMPT;
    }

    private function buildMessageHistory(ChatConversation $conversation): array
    {
        $max = config('chatbot.max_history', 12);

        return $conversation->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderByDesc('id')
            ->limit($max)
            ->get()
            ->reverse()
            ->map(fn (ChatMessage $m) => [
                'role' => $m->role === 'assistant' ? 'assistant' : 'user',
                'content' => $m->content,
            ])
            ->values()
            ->all();
    }

    private function storeAssistantMessage(
        ChatConversation $conversation,
        string $content,
        ?array $metadata = null
    ): void {
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $content,
            'metadata' => $metadata,
        ]);
    }

    private function shopName(): string
    {
        return (string) config('chatbot.shop_name', 'Cửa Hàng');
    }
}
