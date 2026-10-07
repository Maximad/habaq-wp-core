<?php
if (!defined('ABSPATH')) { exit; }
return json_decode(<<<'HABAQ_CURRICULUM'
{
  "categories": {
    "editorial": "الصحافة والتحرير والمنصات",
    "creative": "الصورة والصوت والتصميم والإنتاج",
    "community": "البرامج والمشاركة والحماية",
    "operations": "العمليات والأشخاص والتعلم",
    "finance": "المال والمنح",
    "research": "البحث والقياس والذاكرة",
    "technology": "الموقع والتقنية والأمن",
    "leadership": "القيادة والشراكات والموارد"
  },
  "roles": {
    "reporter": {
      "title": "صحافة وكتابة تقارير",
      "track": "media",
      "priority": [
        "reporting",
        "verification",
        "interview"
      ],
      "development": [
        "field-safety",
        "editing"
      ],
      "output": "تكليف وتقرير قصير بسجل مصادر وخطة مراجعة."
    },
    "fact-checker": {
      "title": "تدقيق الوقائع",
      "track": "media",
      "priority": [
        "verification",
        "editing"
      ],
      "development": [
        "responsible-ai",
        "knowledge"
      ],
      "output": "سجل تحقق يوضح الدليل والقيود والقرار المقترح."
    },
    "editor": {
      "title": "تحرير وقيادة التحرير",
      "track": "media",
      "priority": [
        "editing",
        "verification",
        "interview"
      ],
      "development": [
        "feedback",
        "mentoring"
      ],
      "output": "مراجعة مادة وفق الدليل والإنصاف والحقوق مع ملاحظات محددة."
    },
    "platforms": {
      "title": "منصات وسوشال ميديا",
      "track": "media",
      "priority": [
        "social",
        "editing",
        "digital-security"
      ],
      "development": [
        "measurement",
        "design"
      ],
      "output": "حزمة نشر معتمدة وردود مناسبة ومسار تصحيح."
    },
    "photographer": {
      "title": "تصوير فوتوغرافي",
      "track": "production",
      "priority": [
        "photo",
        "equipment",
        "safeguarding"
      ],
      "development": [
        "field-safety",
        "knowledge"
      ],
      "output": "صور افتراضية أو معتمدة بوصف وحقوق وتسليم آمن."
    },
    "video-editor": {
      "title": "تصوير فيديو ومونتاج",
      "track": "production",
      "priority": [
        "video",
        "equipment",
        "photo"
      ],
      "development": [
        "design",
        "client-production"
      ],
      "output": "نسخة مراجعة وقائمة حقوق ومشروع قابل للاستكمال."
    },
    "audio-producer": {
      "title": "صوت وبودكاست",
      "track": "radio",
      "priority": [
        "audio",
        "radio-rights",
        "equipment"
      ],
      "development": [
        "interview",
        "knowledge"
      ],
      "output": "مخطط حلقة وعينة معتمدة وسجل حقوق."
    },
    "radio-programmer": {
      "title": "برمجة وتشغيل الراديو",
      "track": "radio",
      "priority": [
        "radio-rights",
        "audio",
        "digital-security"
      ],
      "development": [
        "knowledge",
        "measurement"
      ],
      "output": "قائمة بث افتراضية مطابقة لسجل الحقوق وخطة بديلة."
    },
    "designer": {
      "title": "تصميم وهوية بصرية",
      "track": "production",
      "priority": [
        "design",
        "photo"
      ],
      "development": [
        "accessibility",
        "client-production"
      ],
      "output": "بطاقة واضحة بروح حبق ونسخة قابلة للتعديل ووصف بديل."
    },
    "translator": {
      "title": "ترجمة وتحرير لغوي",
      "track": "media",
      "priority": [
        "translation",
        "editing"
      ],
      "development": [
        "verification",
        "responsible-ai"
      ],
      "output": "نص مراجع مع الأصل وقاموس مصطلحات وأسئلة مفتوحة."
    },
    "producer": {
      "title": "إدارة الإنتاج وخدمات العملاء",
      "track": "production",
      "priority": [
        "client-production",
        "project-management",
        "budgeting"
      ],
      "development": [
        "procurement",
        "partnership-development"
      ],
      "output": "نطاق وميزانية ومعيار قبول ومسار تغيير للمخول."
    },
    "event-producer": {
      "title": "تنظيم فعاليات وعلاقة بالفنانين",
      "track": "people",
      "priority": [
        "events",
        "equipment",
        "safeguarding"
      ],
      "development": [
        "budgeting",
        "partnership-development"
      ],
      "output": "جدول تشغيل ورايدر وأدوار وخطة سلامة وإغلاق."
    },
    "facilitator": {
      "title": "تيسير حوار وورش",
      "track": "people",
      "priority": [
        "facilitation",
        "accessibility",
        "safeguarding"
      ],
      "development": [
        "mentoring",
        "measurement"
      ],
      "output": "خطة جلسة وخيارات مشاركة وقواعد وملخص دون بيانات حساسة."
    },
    "community-organizer": {
      "title": "مشاركة مجتمعية وتنسيق متطوعين",
      "track": "people",
      "priority": [
        "volunteer-coordination",
        "accessibility",
        "facilitation"
      ],
      "development": [
        "events",
        "feedback"
      ],
      "output": "اتفاق مساهمة وخطة مشاركة تراعي الوقت وحدود الدور."
    },
    "operations-coordinator": {
      "title": "تنسيق العمليات واللوجستيات",
      "track": "operations",
      "priority": [
        "project-management",
        "equipment",
        "budgeting"
      ],
      "development": [
        "volunteer-coordination",
        "knowledge"
      ],
      "output": "ملخص أسبوعي بالمواعيد والعوائق والقرارات ومالكيها."
    },
    "people-coordinator": {
      "title": "إدارة أشخاص وموارد بشرية",
      "track": "people-ops",
      "priority": [
        "volunteer-coordination",
        "feedback",
        "safeguarding"
      ],
      "development": [
        "mentoring",
        "governance"
      ],
      "output": "خطة انضمام واتفاق دور ودعم ومراجعة وتسليم صلاحيات."
    },
    "trainer": {
      "title": "تدريب ومرافقة تعلم",
      "track": "people-ops",
      "priority": [
        "mentoring",
        "feedback",
        "accessibility"
      ],
      "development": [
        "facilitation",
        "measurement"
      ],
      "output": "جلسة قصيرة وتطبيق بمعايير معلنة وخطة متابعة."
    },
    "finance-officer": {
      "title": "مالية ومحاسبة وصندوق",
      "track": "finance",
      "priority": [
        "budgeting",
        "procurement",
        "reconciliation"
      ],
      "development": [
        "grant-management",
        "equipment"
      ],
      "output": "ملف عملية ومطابقة افتراضية وقائمة معلقات."
    },
    "grant-coordinator": {
      "title": "منح وتقارير للممولين",
      "track": "finance",
      "priority": [
        "grant-management",
        "measurement",
        "budgeting"
      ],
      "development": [
        "project-management",
        "research"
      ],
      "output": "بطاقة منحة وجدول تقارير ومؤشر بدليل وأهلية مصروف."
    },
    "resource-development": {
      "title": "تمويل ورعاية وتنمية موارد",
      "track": "leadership",
      "priority": [
        "fundraising",
        "partnership-development",
        "budgeting"
      ],
      "development": [
        "grant-management",
        "measurement"
      ],
      "output": "مفهوم دعم واقعي يشرح الغرض والالتزامات والحدود."
    },
    "researcher": {
      "title": "بحث وتحليل وثينك",
      "track": "research",
      "priority": [
        "research",
        "verification",
        "measurement"
      ],
      "development": [
        "translation",
        "knowledge"
      ],
      "output": "سؤال ومنهج ومصادر ونتائج وقيود وتوصية محدودة."
    },
    "learning-evaluation": {
      "title": "متابعة وتقييم وتعلم",
      "track": "research",
      "priority": [
        "measurement",
        "research"
      ],
      "development": [
        "grant-management",
        "mentoring"
      ],
      "output": "بطاقة مؤشر وتعريف ومصدر ووقت وحدود وقرار تحسين."
    },
    "archivist": {
      "title": "أرشيف وذاكرة وإدارة معرفة",
      "track": "research",
      "priority": [
        "knowledge",
        "verification",
        "interview"
      ],
      "development": [
        "photo",
        "digital-security"
      ],
      "output": "فهرس مادة بأصل وحقوق وقيود ووصول وتسليم."
    },
    "developer": {
      "title": "تطوير وتقنية وحبق غيكس",
      "track": "technology",
      "priority": [
        "software-delivery",
        "digital-security",
        "wordpress"
      ],
      "development": [
        "responsible-ai",
        "knowledge"
      ],
      "output": "تغيير محدود مع قبول واختبارات وخطة إصدار ورجوع."
    },
    "site-admin": {
      "title": "إدارة الموقع والأدوات",
      "track": "technology",
      "priority": [
        "wordpress",
        "digital-security",
        "software-delivery"
      ],
      "development": [
        "knowledge",
        "mentoring"
      ],
      "output": "خريطة وصول وبلاغ عطل وخطة اختبار دون أسرار أو تغيير تفويض."
    },
    "project-lead": {
      "title": "قيادة وحدة أو مشروع",
      "track": "leadership",
      "priority": [
        "governance",
        "project-management",
        "feedback"
      ],
      "development": [
        "budgeting",
        "measurement"
      ],
      "output": "تفويض محدود وخطة موارد ومراجعة أول مساهمة."
    },
    "governance-member": {
      "title": "حوكمة وتنسيق عام",
      "track": "leadership",
      "priority": [
        "governance",
        "partnership-development",
        "budgeting"
      ],
      "development": [
        "fundraising",
        "grant-management"
      ],
      "output": "مذكرة قرار بقدرة وموارد ومصالح ومالك ومراجعة."
    },
    "safety-contact": {
      "title": "مهمة حماية أو سلامة مكلف بها",
      "track": "operations",
      "priority": [
        "safeguarding",
        "field-safety",
        "digital-security"
      ],
      "development": [
        "facilitation",
        "governance"
      ],
      "output": "خريطة إحالة ومراجعة خطر مجردة؛ التدريب لا ينشئ تكليف حماية."
    }
  },
  "tracks": {
    "radio": {
      "title": "راديو حبق والصوت",
      "modules": [
        "radio-basics",
        "radio-task"
      ]
    },
    "operations": {
      "title": "العمليات والتنسيق",
      "modules": [
        "operations-basics",
        "operations-task"
      ]
    },
    "finance": {
      "title": "المالية والمنح",
      "modules": [
        "finance-basics",
        "finance-task"
      ]
    },
    "people-ops": {
      "title": "الأشخاص والتدريب",
      "modules": [
        "people-ops-basics",
        "people-ops-task"
      ]
    },
    "research": {
      "title": "ثينك والبحث والذاكرة",
      "modules": [
        "research-basics",
        "research-task"
      ]
    },
    "technology": {
      "title": "غيكس والموقع والأدوات",
      "modules": [
        "technology-basics",
        "technology-task"
      ]
    },
    "leadership": {
      "title": "القيادة والحوكمة والشراكات",
      "modules": [
        "leadership-basics",
        "leadership-task"
      ]
    }
  },
  "sources": [
    {
      "id": "ops",
      "title": "منسق العمليات - التوصيف الوظيفي (وثيقة عمل)",
      "url": "https://docs.google.com/document/d/1Q5awvL1VN14zW1hn4kM67EWlUz2H58nIrrtRG6yaIjI/edit",
      "status": "working_document",
      "updated": "2026-01-06"
    },
    {
      "id": "hr",
      "title": "9- مسودة لائحة الموارد البشرية في تجمع حبق (مرجع عمل؛ تحقق من الاعتماد)",
      "url": "https://docs.google.com/document/d/14KjHfLnFyixF8o0ScIkEi1nGleUHWgGH3WNnwE6vFFk/edit",
      "status": "draft_or_proposal",
      "updated": "2025-12-13"
    },
    {
      "id": "editorial",
      "title": "17- مسودة سياسة التحرير والنشر والتصحيح وحق الرد في حبق (مرجع عمل؛ تحقق من الاعتماد)",
      "url": "https://docs.google.com/document/d/1-ARuixeA9JjMuPL1ChkP0ZuUZqXaHOCkR2-Mdj01ZC0/edit",
      "status": "draft_or_proposal",
      "updated": "2025-12-13"
    },
    {
      "id": "style",
      "title": "دليل اللغة والأسلوب لحبق ميديا (مرجع عمل؛ تحقق من الاعتماد)",
      "url": "https://docs.google.com/document/d/1ZDCSvoM0sgBJDMlUzHJbkAjp_k2nKjP0CVvkiwE8AEo/edit",
      "status": "working_document",
      "updated": "2025-10-28"
    },
    {
      "id": "finance",
      "title": "10- مسودة دليل الإجراءات المالية لحبق (مرجع عمل؛ تحقق من الاعتماد)",
      "url": "https://docs.google.com/document/d/1f8XBnxdRyid20e0LraqM6PvJDBVOb4xuOghkkiDvVmM/edit",
      "status": "draft_or_proposal",
      "updated": "2025-12-13"
    },
    {
      "id": "grants",
      "title": "8- مسودة سياسة المنح والأموال المقيدة والتقارير للممولين لتجمع حبق (مرجع عمل؛ تحقق من الاعتماد)",
      "url": "https://docs.google.com/document/d/1vzrO3jQFs6tmMUGX3g6rJMXkw6iI_vT30B_JcnioC-Q/edit",
      "status": "draft_or_proposal",
      "updated": "2025-12-26"
    },
    {
      "id": "security",
      "title": "18- مسودة سياسة الأمن الرقمي والسلامة الميدانية في حبق (مرجع عمل؛ تحقق من الاعتماد)",
      "url": "https://docs.google.com/document/d/17Xg1sP7MF_7mnV5tGkkHApxhelgLa0f_GDKGStRVzQY/edit",
      "status": "draft_or_proposal",
      "updated": "2025-12-13"
    },
    {
      "id": "safeguard",
      "title": "16- مسودة سياسة حماية الأطفال والفئات الأضعف في حبق (مرجع عمل؛ تحقق من الاعتماد)",
      "url": "https://docs.google.com/document/d/1XB4OnxMEJ3N9hUf0Slm1lhu80csjXGY8Z9CydVCnnbY/edit",
      "status": "draft_or_proposal",
      "updated": "2025-12-13"
    },
    {
      "id": "partnerships",
      "title": "14- مسودة ملحق F سياسة الشراكات (مرجع عمل؛ تحقق من الاعتماد)",
      "url": "https://docs.google.com/document/d/1liFarsI0JaSRqcCA6yrslcyFZ9t-Nltd4qqSNTpWj7w/edit",
      "status": "draft_or_proposal",
      "updated": "2025-12-28"
    },
    {
      "id": "assets",
      "title": "13- مسودة ملحق E: سياسة الأصول والمعدات في حبق (مرجع عمل؛ تحقق من الاعتماد)",
      "url": "https://docs.google.com/document/d/1S3ythF_Faun4llO_C_Tx4Md3whPVCMWMW80Fgg37fVA/edit",
      "status": "draft_or_proposal",
      "updated": "2025-12-13"
    },
    {
      "id": "program",
      "title": "برنامج حبق المتكامل — منحة حياة 2026–2027 (مرجع عمل؛ تحقق من الاعتماد)",
      "url": "https://docs.google.com/document/d/1sNXPEI3lYtj34hsHOCjviObr2UaN7gyljPPGWFmhS9s/edit",
      "status": "draft_or_proposal",
      "updated": "2026-09-18"
    },
    {
      "id": "bylaws",
      "title": "مسودة النظام الداخلي لتجمع حبق (مرجع عمل؛ تحقق من الاعتماد)",
      "url": "https://docs.google.com/document/d/1MSOFNZp_h1ShBIWvmc9GnQrBbgWXMDg-bAaqUPG4XpM/edit",
      "status": "draft_or_proposal",
      "updated": "2025-12-07"
    },
    {
      "id": "radio",
      "title": "إذن محدود لبث أعمال موسيقية عبر راديو حبق (مرجع عمل؛ تحقق من الاعتماد)",
      "url": "https://docs.google.com/document/d/1lh1uswEYbMkBgT7NXCEBGbzDihqKHZWcuBtVCs9yUCc/edit",
      "status": "draft_or_proposal",
      "updated": "2026-09-08"
    },
    {
      "id": "events",
      "title": "سياسة خدمات حبق ناس للفعاليات والشراكات (مرجع عمل؛ تحقق من الاعتماد)",
      "url": "https://docs.google.com/document/d/1VY4V-bv2oa2ntqQCMBnMi7K-jTZ_t6hWrH2ri_D3Qt0/edit",
      "status": "draft_or_proposal",
      "updated": "2026-01-02"
    },
    {
      "id": "safevoices",
      "title": "أصوات آمنة: نواة إعلامية نسوية لمواجهة العنف ضد النساء والعنف الرقمي في السويداء (مرجع عمل؛ تحقق من الاعتماد)",
      "url": "https://docs.google.com/document/d/1gPNNm152KrUMDF9Gioeo_oogonQDw2DLwPOUSWuQ3sE/edit",
      "status": "draft_or_proposal",
      "updated": "2026-09-19"
    },
    {
      "id": "folders",
      "title": "📂 هيكلية المجلدات – Drive Structure (وثيقة عمل)",
      "url": "https://docs.google.com/document/d/1tspAvte5Euoy64xo48sLM8Rh3cCUMnusDD4gyhs8FnE/edit",
      "status": "working_document",
      "updated": "2025-11-04"
    },
    {
      "id": "brand-jobs",
      "title": "مواد حبق البصرية: habaq.online/jobs",
      "url": "https://www.canva.com/design/DAG-mB_C2-c",
      "status": "visual_reference",
      "updated": "تمت المراجعة 2026-10-07"
    }
  ]
}
HABAQ_CURRICULUM
, true);
