<?php

namespace App\Services;

use App\Models\ProductType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Throwable;

class AiOrderExtractorService
{
    /**
     * استخراج بيانات الطلب من نص محادثة باللغة العربية
     */
    public function extract(string $text, ?string $today = null): array
    {
        $today = $today ? Carbon::parse($today) : Carbon::today();
        $text = trim($text);

        // إذا كان مفتاح Gemini API متوفراً في .env، نحاول تحسين الاستخراج عبر LLM أولاً
        $geminiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
        if (!empty($geminiKey)) {
            $llmResult = $this->extractWithGemini($text, $today, $geminiKey);
            if ($llmResult !== null) {
                return $llmResult;
            }
        }

        // استخدام المحرك القواعدي العربي المتقن
        return $this->extractWithRuleEngine($text, $today);
    }

    /**
     * محرك القواعد المخصص للنصوص العربية
     */
    protected function extractWithRuleEngine(string $text, Carbon $today): array
    {
        $products = ProductType::all();
        $customerName = $this->findCustomerName($text);
        $productMatch = $this->findProduct($text, $products);
        $quantity = $this->findQuantity($text);
        $dueDate = $this->findDueDate($text, $today);

        $fields = [
            'customer_name' => $customerName,
            'product_name' => $productMatch['product_name'] ?? null,
            'unit_duration' => $productMatch['unit_duration'] ?? null,
            'quantity' => $quantity ?: 1,
            'due_date' => $dueDate['date'] ?? null,
            'due_date_phrase' => $dueDate['phrase'] ?? null,
            'description' => $this->cleanDescription($text),
        ];

        $missing = [];
        if (empty($fields['customer_name'])) {
            $missing[] = 'اسم العميل';
        }
        if (empty($fields['product_name'])) {
            $missing[] = 'نوع المنتج';
        }
        if (empty($fields['due_date'])) {
            $missing[] = 'تاريخ موعد التسليم';
        }

        return [
            'success' => true,
            'engine' => 'rules',
            'fields' => $fields,
            'missing' => $missing,
            'confidence' => empty($missing) ? 0.95 : (count($missing) === 1 ? 0.75 : 0.5),
        ];
    }

    /**
     * استخراج اسم العميل من أسطر المحادثة
     */
    protected function findCustomerName(string $text): ?string
    {
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            // نمط محادثات واتساب: [12:30] سارة القحطاني: السلام عليكم
            if (preg_match('/^(?:\[[^\]]+\]|\d{1,2}[\/\.]\d{1,2}[\/\.]\d{2,4}[^:\n]{0,20})?\s*([^:\n\[\]]{2,30}):\s*\S/u', $line, $m)) {
                $name = trim($m[1]);
                if (!in_array($name, ['أنا', 'المتجر', 'خدمة العملاء', 'الدعم', 'Admin'])) {
                    return $name;
                }
            }

            // نمط "اسمي فلان" أو "معك فلان"
            if (preg_match('/(?:اسمي|معك|أنا|العميلة|العميل)\s+([^\s\.\,،]{2,25}(?:\s+[^\s\.\,،]{2,25})?)/u', $line, $m)) {
                return trim($m[1]);
            }
        }

        return null;
    }

    /**
     * مطابقة نوع المنتج
     */
    protected function findProduct(string $text, $products): ?array
    {
        foreach ($products as $p) {
            if (mb_stripos($text, $p->product_name) !== false) {
                return [
                    'product_name' => $p->product_name,
                    'unit_duration' => $p->estimated_unit_duration,
                ];
            }
        }

        // كلمات دلالية عامة
        if (preg_match('/(باقة|باقات|بوكيه)/u', $text)) {
            return ['product_name' => 'باقة ورد دائمة', 'unit_duration' => 90];
        }
        if (preg_match('/(بوكس|صندوق|علبة|علب)/u', $text)) {
            return ['product_name' => 'بوكس هدايا', 'unit_duration' => 120];
        }
        if (preg_match('/(توزيعات|توزيعة|هدايا تخرج|تذكار)/u', $text)) {
            return ['product_name' => 'توزيعات مناسبات', 'unit_duration' => 20];
        }
        if (preg_match('/(لوحة|برواز|جدارية)/u', $text)) {
            return ['product_name' => 'لوحة ديكور يدوية', 'unit_duration' => 180];
        }
        if (preg_match('/(وردة|وردات)/u', $text)) {
            return ['product_name' => 'وردة منفردة', 'unit_duration' => 15];
        }

        return null;
    }

    /**
     * استخراج الكمية
     */
    protected function findQuantity(string $text): int
    {
        // أرقام صريحة قبل أو بعد الكلمات
        if (preg_match('/(\d+)\s*(?:حبات|حبة|قطع|قطعة|باقات|باقة|بوكسات|بوكس|وردات|وردة)/u', $text, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/(?:العدد|الكمية|ابغى|أحتاج)\s*(\d+)/u', $text, $m)) {
            return (int) $m[1];
        }

        // المثنى
        if (preg_match('/(باقتين|بوكسين|لوحتين|وردتين|قطعتين|علبتين)/u', $text)) {
            return 2;
        }

        // كلمات الأعداد العربية
        $wordMap = [
            'واحد' => 1, 'واحدة' => 1,
            'اثنين' => 2, 'اثنان' => 2, 'اثنتين' => 2,
            'ثلاث' => 3, 'ثلاثة' => 3,
            'أربع' => 4, 'أربعة' => 4,
            'خمس' => 5, 'خمسة' => 5,
            'ست' => 6, 'ستة' => 6,
            'سبع' => 7, 'سبعة' => 7,
            'ثمان' => 8, 'ثمانية' => 8,
            'تسع' => 9, 'تسعة' => 9,
            'عشر' => 10, 'عشرة' => 10,
            'دستة' => 12, 'دزينة' => 12,
        ];

        foreach ($wordMap as $word => $qty) {
            if (preg_match('/(?:^|\s)' . preg_quote($word, '/') . '(?:\s|$)/u', $text)) {
                return $qty;
            }
        }

        return 1;
    }

    /**
     * استخراج تاريخ التسليم
     */
    protected function findDueDate(string $text, Carbon $today): ?array
    {
        // 1. تاريخ ISO مباشر: 2026-11-30
        if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $text, $m)) {
            return [
                'date' => sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]),
                'phrase' => $m[0],
            ];
        }

        // 2. تعابير نسبية شائعة
        if (preg_match('/(بعد غد|بعد بكره)/u', $text, $m)) {
            return [
                'date' => $today->copy()->addDays(2)->toDateString(),
                'phrase' => $m[0],
            ];
        }
        if (preg_match('/(غداً|غدا|بكره|باكر)/u', $text, $m)) {
            return [
                'date' => $today->copy()->addDays(1)->toDateString(),
                'phrase' => $m[0],
            ];
        }
        if (preg_match('/بعد\s*(\d+)\s*(أيام|ايام|يوم)/u', $text, $m)) {
            return [
                'date' => $today->copy()->addDays((int) $m[1])->toDateString(),
                'phrase' => $m[0],
            ];
        }
        if (preg_match('/بعد\s*(أسبوع|اسبوع)/u', $text, $m)) {
            return [
                'date' => $today->copy()->addDays(7)->toDateString(),
                'phrase' => $m[0],
            ];
        }

        // 3. أيام الأسبوع
        $weekdays = [
            'الأحد' => 0, 'الاحد' => 0,
            'الإثنين' => 1, 'الاثنين' => 1,
            'الثلاثاء' => 2,
            'الأربعاء' => 3, 'الاربعاء' => 3,
            'الخميس' => 4,
            'الجمعة' => 5, 'الجمعه' => 5,
            'السبت' => 6,
        ];

        foreach ($weekdays as $dayName => $dow) {
            if (preg_match('/(?:يوم\s*)?' . preg_quote($dayName, '/') . '(?:\s*(?:القادم|الجاي))?/u', $text, $m)) {
                $target = $today->copy();
                $diff = ($dow - $target->dayOfWeek + 7) % 7;
                if ($diff === 0) {
                    $diff = 7; // الأسبوع القادم
                }
                $target->addDays($diff);
                return [
                    'date' => $target->toDateString(),
                    'phrase' => $m[0],
                ];
            }
        }

        return null;
    }

    protected function cleanDescription(string $text): string
    {
        return mb_substr(strip_tags($text), 0, 300);
    }

    /**
     * استخراج متقدم باستخدام Gemini API إذا توفر المفتاح
     */
    protected function extractWithGemini(string $text, Carbon $today, string $apiKey): ?array
    {
        try {
            $prompt = "استخرج بيانات طلب الحرفة اليدوية من المحادثة التالية كـ JSON فقط:\n"
                . "النص: \"{$text}\"\n"
                . "تاريخ اليوم: {$today->toDateString()}\n"
                . "أعد الحقول: customer_name, product_name, quantity, due_date (YYYY-MM-DD), description.";

            $response = Http::timeout(5)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $content = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($content) {
                    $data = json_decode($content, true);
                    if ($data && is_array($data)) {
                        return [
                            'success' => true,
                            'engine' => 'gemini-llm',
                            'fields' => $data,
                            'missing' => [],
                            'confidence' => 0.98,
                        ];
                    }
                }
            }
        } catch (Throwable $e) {
            // العودة التلقائية للمحرك القواعدي
        }

        return null;
    }
}
