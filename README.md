# وَتِيرة

منصة لإدارة الطلبات ومهام التنفيذ ومواعيد التسليم للمتاجر الحرفية. مبنية باستخدام Laravel 12 وPHP 8.2 وMySQL وVite.

## المتطلبات

- PHP 8.2 أو أحدث، مع إضافات Laravel المعتادة مثل `pdo_mysql` و`mbstring` و`openssl` و`fileinfo`.
- Composer 2.
- MySQL.
- Node.js وnpm.
- Git.

## التشغيل المحلي على Windows

### 1. تنزيل المشروع وتثبيت الاعتماديات

```powershell
git clone https://github.com/AlaaMuharram/Watirah.git
cd Watirah
composer install
npm ci
```

### 2. إعداد البيئة وقاعدة البيانات

أنشئ قاعدة بيانات MySQL باسم `watira`، مثلاً:

```sql
CREATE DATABASE watira CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

انسخ ملف إعداد البيئة وأنشئ مفتاح التطبيق:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

عدّل إعدادات قاعدة البيانات في `.env` لتطابق إعدادات MySQL لديك:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=watira
DB_USERNAME=root
DB_PASSWORD=
```

ثم أنشئ الجداول والبيانات التجريبية:

```powershell
php artisan migrate --seed
```

### 3. بناء ملفات الواجهة

```powershell
npm run build
```

### 4. تشغيل التطبيق

```powershell
php artisan serve
```

افتح [http://127.0.0.1:8000](http://127.0.0.1:8000) في المتصفح.

للتطوير مع إعادة تحميل ملفات الواجهة تلقائياً، شغّل `npm run dev` في نافذة طرفية ثانية بدلاً من بناء الملفات مسبقاً.

## تشغيل الاختبارات

```powershell
php artisan test
```

## ملاحظات

- ملف `.env` يحتوي إعدادات جهازك المحلية، ولا ينبغي رفعه إلى Git.
- يستخدم ملف البيئة الافتراضي MySQL؛ تأكد من تشغيل MySQL وإنشاء قاعدة البيانات قبل تنفيذ الترحيلات.
- ينشئ الأمر `php artisan migrate --seed` بيانات تجريبية لتسهيل تجربة التطبيق محلياً.
