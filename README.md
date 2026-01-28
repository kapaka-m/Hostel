# Hostel Management System

نظام لإدارة السكن الجامعي يشمل لوحة تحكم ويب للجامعة والسكنات، مع واجهات موبايل مبنية بــ Flutter، وواجهة API آمنة مبنية بــ Laravel.

## المحتويات
- [نظرة عامة](#نظرة-عامة)
- [المكونات](#المكونات)
- [الأدوار والصلاحيات](#الأدوار-والصلاحيات)
- [أبرز المزايا](#أبرز-المزايا)
- [التقنيات المستخدمة](#التقنيات-المستخدمة)
- [هيكل المشروع](#هيكل-المشروع)
- [تشغيل المشروع محلياً (Backend)](#تشغيل-المشروع-محلياً-backend)
- [حسابات تجريبية (Seed)](#حسابات-تجريبية-seed)
- [تشغيل تطبيق الموبايل](#تشغيل-تطبيق-الموبايل)
- [واجهات الـ API والتوثيق](#واجهات-ال-api-والتوثيق)
- [استيراد الطلاب CSV](#استيراد-الطلاب-csv)
- [Feature Flags](#feature-flags)
- [الاختبارات](#الاختبارات)
- [ملاحظات للإنتاج](#ملاحظات-للإنتاج)
- [الترخيص](#الترخيص)
- [المساهمة](#المساهمة)

## نظرة عامة
المشروع يقدم منصة متكاملة لإدارة السكن الجامعي، تشمل إدارة السكنات والغرف والطوابق والطلاب والتسكين، مع نظام تذاكر للصيانة وإعلانات وتقارير وسجل تدقيق. النظام يعتمد على صلاحيات وأدوار متعددة ويقدم API لاستخدام تطبيق الموبايل.

## المكونات
- **Backend (Laravel 11)**: REST API + لوحة تحكم ويب (Blade) للمدراء.
- **Mobile (Flutter)**: تطبيق موبايل بأدوار متعددة.
- **لقطات شاشة**: داخل `image/`.
- **قوالب استيراد**: داخل `templit import/`.

## الأدوار والصلاحيات
- **SUPER_ADMIN**: لوحة إشراف عامة.
- **UNIVERSITY_ADMIN**: إدارة السكنات، مدراء السكن، الطلاب، التذاكر، الإعلانات، التقارير، الإعدادات، وسجلات التدقيق/النشاط (حسب الـ Feature Flags).
- **DORM_ADMIN**: إدارة الطوابق والغرف والطلاب والتسكين والتذاكر والإعلانات، مع عمليات جماعية (Bulk) وتجميد/إلغاء تجميد الطلاب (حسب الـ Feature Flags).
- **STUDENT**: عرض بيانات الغرفة المخصصة له عبر API وتطبيق الموبايل.

## أبرز المزايا
- إدارة السكنات (Dorms) والطوابق والغرف وربطها بالجامعة.
- إدارة الطلاب والتسكين مع نقل الطلاب بين الغرف.
- استيراد/تصدير الطلاب والغرف والتذاكر.
- نظام تذاكر صيانة مع تعليقات.
- إدارة الإعلانات ونشرها حسب الجمهور.
- تقارير وإحصاءات ملخصة.
- سجل تدقيق (Audit Logs) وموجز نشاط (Activity Feed) قابلة للتفعيل.
- إشعارات للمدراء وتفضيلات واجهة (Theme).

## التقنيات المستخدمة
- **Backend**: PHP 8.2, Laravel 11, Sanctum, Spatie Permissions.
- **Frontend (Web)**: Blade, Vite, TailwindCSS, Bootstrap.
- **Mobile**: Flutter 3.10, Dio, Provider, GoRouter.
- **Database**: MySQL أو SQLite.

## هيكل المشروع
```
Hostel/
├─ backend/        # Laravel API + لوحة الويب
├─ mobile/         # Flutter app
├─ image/          # لقطات شاشة
├─ templit import/ # قوالب CSV
└─ Cours Work/     # مستندات المشروع
```

## تشغيل المشروع محلياً (Backend)
**المتطلبات:** PHP 8.2+, Composer, Node.js/NPM, قاعدة بيانات (MySQL أو SQLite).

1) تثبيت الاعتمادات:
```bash
cd backend
composer install
npm install
```

2) إعداد ملف البيئة:
```bash
copy .env.example .env
php artisan key:generate
```

3) ضبط قاعدة البيانات في `.env` (مثل MySQL):
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hostel
DB_USERNAME=root
DB_PASSWORD=
```

4) تشغيل الـ migrations مع البيانات التجريبية:
```bash
php artisan migrate --seed
```

5) تشغيل السيرفر والأصول:
- خيار سريع (يشغّل السيرفر + الـ queue + Vite):
```bash
composer run dev
```
- أو يدوياً:
```bash
php artisan serve
npm run dev
php artisan queue:work
```

**ملاحظة:** إذا كنت تستخدم `SESSION_DRIVER=database`، أنشئ جدول الجلسات أولاً:
```bash
php artisan session:table
php artisan migrate
```

## حسابات تجريبية (Seed)
بعد تشغيل `php artisan migrate --seed` سيتم إنشاء حسابات افتراضية للتجربة:
- **Super Admin**: `superadmin@test.com` / `Super@12345`
- **University Admin**: `uniadmin@test.com` / `Uni@12345`
- **Dorm Admin**: `dormadmin@test.com` / `Dorm@12345`
- **Student**: `student1@test.com` / `Stud@12345`

> غيّر كلمات المرور فوراً في بيئة الإنتاج.

## تشغيل تطبيق الموبايل
**المتطلبات:** Flutter SDK.

1) تثبيت الاعتمادات:
```bash
cd mobile
flutter pub get
```

2) تشغيل التطبيق مع تحديد عنوان الـ API:
```bash
flutter run --dart-define=API_BASE_URL=http://127.0.0.1:8000
```

ملاحظات العناوين الافتراضية داخل التطبيق:
- **Windows**: `http://127.0.0.1:8000`
- **Android Emulator**: `http://10.0.2.2:8000`
- **Web**: `http://localhost:8000`

## واجهات الـ API والتوثيق
- المسار الأساسي: `/api`
- المصادقة عبر **Sanctum** (Bearer Token).
- ملف OpenAPI جاهز في: `backend/docs/api-v1.json`

## استيراد الطلاب CSV
يوجد ملف نموذج في:
`templit import/students_im.csv`

الأعمدة المطلوبة:
```
full_name,student_no,email,phone,dorm_code
```

## Feature Flags
الخصائص قابلة للتفعيل/الإيقاف عبر جدول `system_settings` باستخدام مفاتيح من الشكل `feature.<flag>`.
الخيارات المتاحة:
- `audit_logs`
- `activity_feed`
- `permissions`
- `correlation_ids`
- `response_time_logging`
- `user_freeze`
- `strong_passwords`
- `admin_ip_allowlist`
- `suspicious_login_alerts`

## الاختبارات
- Backend:
```bash
cd backend
php artisan test
```

- Mobile:
```bash
cd mobile
flutter test
```

## ملاحظات للإنتاج
- اضبط `APP_ENV=production` و `APP_DEBUG=false`.
- شغّل Cache/Config/Route:
```bash
php artisan config:cache
php artisan route:cache
```
- تأكد من إعداد الـ queue والـ scheduler إذا لزم.
- فعّل HTTPS وحدث `APP_URL` و `FRONTEND_URL`.

## الترخيص
لم يتم تحديد ترخيص بعد. يُنصح بإضافة ملف `LICENSE` قبل النشر العام.

## المساهمة
المساهمات مرحّب بها. افتح Issue لشرح المطلوب أو قدّم Pull Request مع وصف واضح للتغييرات.
