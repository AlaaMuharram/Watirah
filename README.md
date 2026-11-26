# WATIRA

WATIRA تطبيق ويب عربي/إنجليزي لإدارة طلبات الإنتاج ومتابعة ساعات العمل والطاقة المتاحة. بُني باستخدام Laravel 12 وPHP 8.2 وSQLite وواجهة Blade مع Bootstrap 5 وVite.

## الوظائف المنجزة

- لوحة متابعة تعرض عدد الطلبات، ساعات العمل المطلوبة، الطاقة المتاحة خلال 30 يوماً، ونسبة استغلال الطاقة، مع أحدث الطلبات ومؤشر المخاطر.
- إدارة الطلبات: عرض وبحث، إنشاء، تعديل، حذف، وتتبع حالات التنفيذ.
- إدارة أنواع المنتجات وزمن التحضير القياسي.
- إعداد أيام وساعات العمل والطاقة الإنتاجية اليومية واسم المشروع واللغة.
- جدول تنفيذ للطلبات غير المكتملة مرتباً حسب موعد التسليم.
- محاكاة أثر طلب جديد على عبء العمل ونسبة استغلال الطاقة، مع خيار قبول الطلب.
- عرض الطلبات المتوسطة والعالية المخاطر.
- استخراج محلي تجريبي لاسم العميل والمنتج والكمية والتاريخ من نص عربي أو إنجليزي. هذه ميزة تعتمد على أنماط نصية بسيطة ولا تتصل بخدمة ذكاء اصطناعي خارجية.
- واجهة متجاوبة مع العربية والإنجليزية واتجاه RTL/LTR.

## المتطلبات

- PHP 8.2 أو أحدث ضمن نطاق Laravel 12، مع امتدادات `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo` و`dom`.
- Composer 2.
- Node.js 20.19+ أو 22.12+ مع npm لبناء أصول الواجهة.
- Git لاستنساخ المستودع.

لا حاجة لخادم MySQL أو Redis للتشغيل الافتراضي؛ الإعداد يستخدم SQLite محلياً. يلزم اتصال بالإنترنت أول مرة لتنزيل حزم Composer وnpm. ملفات الخطوط وBootstrap وBootstrap Icons يجري تحميلها من CDN أثناء عرض الصفحات.

## تشغيل أول مرة على Windows

افتح PowerShell في المجلد الذي تريد وضع المشروع فيه، ثم نفّذ:

```powershell
git clone --branch proto --single-branch https://github.com/AlaaMuharram/-.git watira
cd watira
composer setup
php artisan serve --host=127.0.0.1 --port=8000
```

افتح `http://127.0.0.1:8000` في المتصفح. اترك نافذة PowerShell التي تشغّل `artisan serve` مفتوحة أثناء الاستخدام.

ينفذ `composer setup` تثبيت حزم PHP، وإنشاء `.env` من `.env.example` إن لم يكن موجوداً، وتوليد مفتاح التطبيق، وإنشاء ملف SQLite، وتشغيل migrations، ثم تثبيت حزم JavaScript وبناء ملفات الواجهة.

## تشغيل أول مرة على macOS أو Linux

تأكد من تثبيت PHP والامتدادات المذكورة وComposer وNode.js/npm وGit، ثم نفّذ:

```bash
git clone --branch proto --single-branch https://github.com/AlaaMuharram/-.git watira
cd watira
composer setup
php artisan serve --host=127.0.0.1 --port=8000
```

افتح `http://127.0.0.1:8000` واترك الطرفية قيد التشغيل.

## الإعداد اليدوي

إذا تعذر تشغيل سكربت التهيئة الشامل، نفذ الخطوات التالية من جذر المشروع:

```bash
composer install
```

أنشئ ملف إعداد محلي من `.env.example`، ولا تستبدل `.env` موجوداً يحتوي إعدادات خاصة بك.

PowerShell:

```powershell
Copy-Item .env.example .env
if (-not (Test-Path database/database.sqlite)) { New-Item -ItemType File database/database.sqlite | Out-Null }
```

macOS/Linux:

```bash
cp .env.example .env
touch database/database.sqlite
```

ثم أكمل على جميع الأنظمة:

```bash
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

تأكد أن `.env` يحتوي `DB_CONNECTION=sqlite`. التطبيق ينشئ مستخدم مساحة العمل وإعداداتها تلقائياً عند أول زيارة. لا توجد شاشة تسجيل دخول في النسخة الحالية.

### بيانات تجريبية اختيارية

لإضافة الطلبات وأنواع المنتجات التجريبية، نفذ الأمر التالي **فقط على قاعدة بيانات جديدة أو بعد التأكد من أنك تريد حذف كل بيانات قاعدة التطوير الحالية**:

```bash
php artisan migrate:fresh --seed
```

هذا الأمر يحذف كل الجداول والبيانات ثم يعيد إنشاءها وإضافة بيانات المثال. لا تستخدمه على بيانات مهمة.

## التشغيل أثناء التطوير

بعد إكمال التهيئة وبناء الأصول مرة واحدة، شغّل الخادم وVite في نافذتين منفصلتين:

```bash
php artisan serve
```

```bash
npm run dev
```

عند الحاجة إلى إعادة بناء أصول الإنتاج محلياً:

```bash
npm run build
```

## الاختبارات

```bash
php artisan test
```

تستخدم اختبارات Laravel قاعدة SQLite مؤقتة في الذاكرة؛ لا تمس قاعدة بيانات التطوير.

## قاعدة البيانات والإعدادات

- ملف التطوير المحلي هو `database/database.sqlite`، وهو مستثنى من Git. خذ نسخة احتياطية منه إذا احتجت الاحتفاظ ببيانات هذا الجهاز.
- لإضافة migration جديدة بعد تحديث المشروع: `php artisan migrate`.
- بعد تغيير إعدادات `.env` أو ظهور إعدادات مخزنة قديمة: `php artisan config:clear`.
- لا ترفع `.env` أو مفاتيح التطبيقات أو قواعد بيانات تحتوي معلومات حقيقية إلى GitHub.

## الفروع

فرع التطوير الجاري مشاركته هو `proto`. استنسخه باستخدام أوامر التشغيل أعلاه، أو انتقل إليه في نسخة موجودة عبر `git switch proto`.
