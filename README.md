# Habaq Engine

WordPress plugin scaffold for the Habaq Engine core.

## Usage

Shortcodes:
- `[habaq_job_fields]` عرض تفاصيل الفرصة في صفحة الفرصة الواحدة.
- `[habaq_job_filters]` نموذج تصفية الفرص في صفحة أرشيف `/jobs`.
- `[job_apply]` أزرار التقديم عبر البريد أو النموذج.
- `[habaq_job_application]` نموذج التقديم (يتم إضافته تلقائيًا في صفحة الفرصة إذا لم يوجد).
- `[habaq_deadline]` عرض آخر موعد للفرصة (يستخدم `habaq_deadline` فقط).
- `[habaq_job_meta key="..."]` عرض قيمة من بيانات الفرصة (الحقول المسموحة: `habaq_deadline`, `habaq_start_date`, `habaq_time_commitment`, `habaq_compensation`, `habaq_job_status`).
- `[habaq_job_terms taxonomy="job_unit"]` عرض مصطلحات التصنيف المحدد في صفحة الفرصة.

Filters behavior:
- النماذج تحفظ القيم المختارة بعد الإرسال وتبقى داخل مسار `/jobs`.
- عدادات المصطلحات تستبعد الفرص المنتهية/المغلقة.

Troubleshooting:
- إذا ظهرت 404 في `/jobs` أو ظهر تنبيه الروابط الدائمة، احفظ إعدادات الروابط الدائمة مرة واحدة.
- إذا ظهرت روابط تصفية بأسماء عربية في الرابط، يتم تحويلها تلقائيًا إلى slugs صحيحة وإعادة توجيه الطلب.

## Changelog

- 0.2.1: تحسين الفلاتر، العدادات، ونظام الإشعارات، وإضافة صفحة الإعدادات والإحصاءات.

## Manual QA

- تأكد من أن `/jobs` يعمل بدون 404 وأن التنبيه لا يظهر بشكل دائم.
- تحقق من أن روابط الفلاتر تستخدم slugs وأن زر إعادة الضبط يعيدك للأرشيف بدون معاملات.
- تأكد من أن عدادات المصطلحات تستبعد الفرص المنتهية/المغلقة.
- جرّب إرسال طلب ناقص وتأكد من إعادة تعبئة الحقول بعد إعادة التوجيه مع إعادة رفع السيرة الذاتية.

## Development

- Code lives in `/includes`.
- Run `php -l` on all PHP files before committing.
