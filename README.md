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
- `[habaq_training slug="code-of-conduct" folder="audio" rtl="1" autoadvance="0"]` مشغّل تدريب HTML5 (يحمّل الأصول فقط عند وجود الشورتكود).

Blocks:
- أضف “Habaq Job Dates” داخل Post Template في Query Loop لعرض تواريخ الفرصة.

Filters behavior:
- النماذج تحفظ القيم المختارة بعد الإرسال وتبقى داخل مسار `/jobs`.
- عدادات المصطلحات تستبعد الفرص المنتهية/المغلقة.

Troubleshooting:
- إذا ظهرت 404 في `/jobs` أو ظهر تنبيه الروابط الدائمة، احفظ إعدادات الروابط الدائمة مرة واحدة.
- إذا ظهرت روابط تصفية بأسماء عربية في الرابط، يتم تحويلها تلقائيًا إلى slugs صحيحة وإعادة توجيه الطلب.
- لتفعيل سجلات التصفية: فعّل `WP_DEBUG` وأضف `define('HABAQ_DEBUG_FILTERS', true);` في `wp-config.php`.


## Training Player (HTML5)

- الشورتكود الجديد: `[habaq_training slug="default" folder="audio" rtl="auto" autoadvance="0" access="public" roles="" cap="" preview_slides="0" version="1" require_ack=""]`.
- أولوية البيانات:
  1. ملف إعدادات JSON من المسار: `wp-content/uploads/habaq-training/<slug>/training.json`.
  2. عند غياب JSON يتم توليد الشرائح تلقائيًا من الملفات الصوتية داخل `wp-content/uploads/<folder>/`.
- يدعم اكتشاف الملفات الصوتية ذات الامتدادات: `mp3`, `m4a` (وأيضًا `wav`, `ogg`) مع ترقيم Western أو Arabic-Indic.
- التحكم بالوصول: `access` يدعم `public | logged_in | roles | cap`، مع `roles` و`cap` عند الحاجة.
- تتبّع التقدم: للزوار في `localStorage` (المفتاح `habaq_training_progress:<slug>`)، وللمستخدمين المسجلين في `user_meta` تحت المفتاح `habaq_training_progress`.

- هيكل الملفات المعتمد لكل تدريب:
  - `wp-content/uploads/habaq-training/<slug>/training.json`
  - `wp-content/uploads/habaq-training/<slug>/audio/`
  - `wp-content/uploads/habaq-training/<slug>/images/`
  - `wp-content/uploads/habaq-training/<slug>/captions/` (اختياري)
- لوحة الإدارة: من wp-admin > **Trainings** يمكنك إنشاء/تعديل/حذف تدريب، رفع `training.json`، رفع ملفات صوت/صور متعددة، أو استيراد ZIP.
- استيراد ZIP يدعم: `training.json` + `audio/*` + `images/*` + `captions/*` مع منع zip-slip ورفض الأنواع غير الآمنة.
- أنواع الملفات المدعومة للصوت والصور تعتمد ديناميكيًا على أنواع MIME المسموح بها في ووردبريس بالموقع (`get_allowed_mime_types` + `wp_check_filetype_and_ext`).

مثال لهيكل `training.json`:

```json
{
  "meta": {
    "title": "مدوّنة السلوك والسياسات الأساسية في حَبَقْ",
    "rtl": true,
    "autoadvance": false,
    "access": "logged_in",
    "roles": ["editor", "administrator"],
    "cap": "edit_posts",
    "preview_slides": 0,
    "version": "2026-02-27",
    "require_ack": true
  },
  "slides": [
    { "id": "intro", "title": "مقدّمة", "body_html": "...", "audio_index": 1, "image_url": "" },
    { "id": "s1", "title": "الشريحة ١", "body_html": "...", "audio_index": 2, "image_url": "" }
  ]
}
```

## Manual test checklist

- `/jobs/?job_unit[]=SOME_SLUG` يجب أن يعمل بدون إعادة توجيه ويعرض الفرص المطابقة لوحدة واحدة فقط.
- `/jobs/?job_unit[]=SOME_SLUG&job_unit[]=OTHER_SLUG` يجب أن يعمل بدون إعادة توجيه ويعرض النتائج المطابقة للوحدات المحددة.
- `/jobs/?job_q=test` يجب أن يعمل بدون إعادة توجيه ويفلتر النتائج بالكلمة المفتاحية داخل أرشيف `/jobs/`.
- `/jobs/?job_unit[]=SOME_SLUG&job_type[]=SOME_TYPE` يجب أن يدمج الفلاتر ويعرض النتائج المشتركة فقط.

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
