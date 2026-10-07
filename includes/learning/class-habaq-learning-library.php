<?php
/** Readable specialist library with optional server-graded checks; no permissions or role assignment. */
if (!defined('ABSPATH')) { exit; }

class Habaq_Learning_Library {
    public static function number($value) {
        return strtr((string) $value, array('0'=>'٠','1'=>'١','2'=>'٢','3'=>'٣','4'=>'٤','5'=>'٥','6'=>'٦','7'=>'٧','8'=>'٨','9'=>'٩'));
    }

    private static function duration($value) {
        return self::number($value) . ((int) $value >= 3 && (int) $value <= 10 ? ' دقائق' : ' دقيقة');
    }

    public static function tabs($base, $library) {
        echo '<nav class="habaq-learning__tabs" aria-label="أقسام التعلم"><a href="' . esc_url($base) . '"' . (!$library ? ' aria-current="page"' : '') . '>رحلة الانضمام</a><a href="' . esc_url(add_query_arg('view', 'library', $base)) . '"' . ($library ? ' aria-current="page"' : '') . '>مكتبة الدورات حسب الدور</a></nav>';
    }

    public static function query($key) {
        return isset($_GET[$key]) && is_string($_GET[$key]) ? sanitize_text_field(wp_unslash($_GET[$key])) : '';
    }

    public static function courses($role = '', $query = '') {
        $catalog = Habaq_Learning::catalog();
        $ids = isset($catalog['roles'][$role]) ? array_merge($catalog['roles'][$role]['priority'], $catalog['roles'][$role]['development']) : null;
        return array_values(array_filter($catalog['modules'], function ($module) use ($ids, $query) {
            if (empty($module['optional']) || ($ids !== null && !in_array($module['id'], $ids, true))) { return false; }
            return $query === '' || stripos($module['title'] . ' ' . $module['outcome'], $query) !== false;
        }));
    }

    public static function render($notice = '') {
        if (!Habaq_Learning::eligible()) { return ''; }
        $catalog = Habaq_Learning::catalog();
        $base = get_permalink();
        $library = add_query_arg('view', 'library', $base);
        $role = sanitize_key(self::query('role'));
        if (!isset($catalog['roles'][$role])) { $role = ''; }
        $query = self::query('q');
        if (strlen($query) > 480) { $query = ''; }
        $return = $role ? add_query_arg('role', $role, $library) : $library;
        $id = sanitize_key(self::query('lesson'));
        $module = Habaq_Learning::module($id);
        $member = get_current_user_id();
        ob_start();
        echo '<section class="habaq-learning alignwide" dir="rtl"><header class="habaq-learning__hero"><p class="habaq-learning__eyebrow">معرفة تتحول إلى عمل</p><h2>مكتبة تعلّم حبق</h2><p>' . esc_html(self::number(count(self::courses()))) . ' دورة تخصصية · ' . esc_html(self::number(count($catalog['roles']))) . ' خريطة دور مقترحة. اختر ما تحتاجه لمهمتك، وناقش التطبيق مع فريقك.</p></header>';
        self::tabs($base, true);
        if ($notice) { echo '<p class="habaq-learning__feedback" role="status">' . esc_html($notice) . '</p>'; }
        echo '<p class="habaq-learning__library-note">ابدأ برحلة الانضمام. بعدها اختر دورة أو اثنتين مع مرافقك قبل المهمة، ودورة تطوير عند الحاجة. خرائط الأدوار تعليمية ولا تثبت وجود منصب أو تعيين. التحقق هنا يسجل الفهم؛ إتقان العمل يحتاج تطبيقاً ومراجعة بشرية.</p>';
        if ($module && !empty($module['optional'])) {
            echo '<p><a href="' . esc_url($return) . '">العودة إلى الدورات</a></p><article class="habaq-learning__course">';
            self::lesson($module, $member, $return);
            echo '</article>';
        } else {
            echo '<form class="habaq-learning__filters" method="get" action="' . esc_url($base) . '"><input type="hidden" name="view" value="library"><label>اعرض ما يناسب دوري<select name="role"><option value="">كل الأدوار والوظائف</option>';
            foreach ($catalog['roles'] as $key => $item) {
                echo '<option value="' . esc_attr($key) . '" ' . selected($role, $key, false) . '>' . esc_html($item['title']) . '</option>';
            }
            echo '</select></label><label>ابحث في الدورات<input type="search" name="q" value="' . esc_attr($query) . '" maxlength="120" placeholder="مثال: صوت، ميزانية، موافقة"></label><button type="submit">عرض الدورات</button></form>';
            if ($role) {
                $profile = $catalog['roles'][$role];
                echo '<div class="habaq-learning__outcome"><h3>' . esc_html($profile['title']) . '</h3><p><strong>ناتج عملي مقترح:</strong> ' . esc_html($profile['output']) . '</p><p>مسار بداية مناسب: ' . esc_html($catalog['tracks'][$profile['track']]['title']) . '. اختره مع المسؤول في رحلة الانضمام؛ الفلتر لا يغير مسارك.</p><p>ابدأ بدورة أو اثنتين موسومتين «أولوية للمهمة»، ثم انتقل للتطوير حسب الحاجة.</p></div>';
            }
            $courses = self::courses($role, $query);
            echo '<p role="status">عدد الدورات المطابقة: ' . esc_html(self::number(count($courses))) . '.</p>';
            if (!$courses) { echo '<p>لا توجد دورة تطابق البحث. عدل الكلمة أو اعرض كل الأدوار.</p>'; }
            foreach ($catalog['categories'] as $category => $label) {
                $items = array_values(array_filter($courses, function ($item) use ($category) { return $item['category'] === $category; }));
                if (!$items) { continue; }
                // Priorities lead within a category; grouping stays predictable across roles.
                if ($role) { usort($items, function ($a, $b) use ($profile) { return (int) !in_array($a['id'], $profile['priority'], true) <=> (int) !in_array($b['id'], $profile['priority'], true); }); }
                echo '<details class="habaq-learning__category"' . ($role || $query ? ' open' : '') . '><summary>' . esc_html($label) . ' <span>عدد الدورات: ' . esc_html(self::number(count($items))) . '</span></summary><div class="habaq-learning__cards">';
                foreach ($items as $item) {
                    $state = Habaq_Learning::record($member, $item);
                    $priority = $role && in_array($item['id'], $profile['priority'], true);
                    echo '<article class="habaq-learning__card"><p class="habaq-learning__tag">' . esc_html($role ? ($priority ? 'أولوية للمهمة' : 'تطوير لاحق') : 'دورة تخصصية اختيارية') . '</p><h3><a href="' . esc_url(add_query_arg('lesson', $item['id'], $return)) . '">' . esc_html($item['title']) . '</a></h3><p>' . esc_html($item['outcome']) . '</p><small>' . esc_html(self::duration($item['minutes'])) . ' للقراءة والتحقق · ' . esc_html(self::duration($item['practice_minutes'])) . ' تقريباً للتطبيق</small><p class="habaq-learning__tag">' . esc_html($state['status'] === 'complete' ? 'تم التحقق من الفهم' : 'لم يسجل التحقق بعد') . '</p></article>';
                }
                echo '</div></details>';
            }
            echo '<details><summary>كيف تتطور بعد البداية؟</summary><p>اختر هدفاً واحداً للشهر القادم ومخرجاً يبين التطبيق. يمكن الاتفاق على مرافقة زميل، مراجعة عمل، مشاركة درس تعلمه الفريق، أو تجربة دور قريب بمهمة صغيرة مفوضة. راجع الخطة عند تغيير الدور أو حدوث خطأ متكرر، ولا تفرض كل المكتبة على كل عضو.</p><p>المدة تقدير مرن، ويمكن القراءة والطباعة عند ضعف الاتصال. الأمثلة افتراضية، والمواد تعليمية جديدة مستندة إلى وثائق عمل ومسودات تحتاج تأكيد اعتمادها.</p></details>';
        }
        echo '</section>';
        return ob_get_clean();
    }

    private static function lesson($module, $member, $return) {
        $state = Habaq_Learning::record($member, $module);
        echo '<p class="habaq-learning__tag">دورة تخصصية اختيارية · ' . esc_html(self::duration($module['minutes'])) . ' قراءة وتحقق + ' . esc_html(self::duration($module['practice_minutes'])) . ' تطبيق تقريباً</p><h2>' . esc_html($module['title']) . '</h2><p class="habaq-learning__outcome"><strong>بعد هذه الدورة:</strong> ' . esc_html($module['outcome']) . '</p>';
        echo wp_kses_post($module['body_html']);
        echo '<details><summary>بطاقة عمل سريعة قابلة للطباعة</summary><div class="habaq-learning__job-aid">' . wp_kses_post($module['job_aid']) . '</div><p>يمكن طباعة الصفحة أو حفظها PDF من المتصفح.</p></details><details><summary>المراجع وحالة المحتوى</summary><p>هذه دورة مقترحة مستندة إلى الوثائق التالية. لا تعتمد السياسات أو تنشئ تفويضاً. قد تحتاج صلاحية منفصلة على المصدر.</p><ul>';
        foreach (Habaq_Learning::catalog()['sources'] as $source) {
            if (in_array($source['id'], $module['sources'], true)) { echo '<li><a href="' . esc_url($source['url']) . '" target="_blank" rel="noopener">' . esc_html($source['title']) . '</a> · ' . esc_html($source['updated']) . '</li>'; }
        }
        echo '</ul></details><h3>تحقق من الفهم</h3><p>فكر في الحالة وراجع التطبيق. لا نحفظ ملاحظاتك العملية في هذه الدورة. إذا احتجت مراجعة كفاءة، اتفق عليها مع مسؤولك في أول مهمة أو هدف التطوير.</p>';
        if ($state['status'] === 'complete') { echo '<p class="habaq-learning__feedback">تم حفظ التحقق من الفهم لهذا الإصدار. لا يمثل شهادة أو اعتماداً للمهارة.</p>'; return; }
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('habaq_learning');
        echo '<input type="hidden" name="action" value="habaq_learning"><input type="hidden" name="op" value="lesson"><input type="hidden" name="return" value="' . esc_attr($return) . '"><input type="hidden" name="lesson" value="' . esc_attr($module['id']) . '"><input type="hidden" name="version" value="' . esc_attr($module['version']) . '"><fieldset><legend>' . esc_html($module['quiz']['prompt']) . '</legend>';
        foreach ($module['quiz']['options'] as $key => $option) { echo '<label><input type="radio" name="answer" value="' . esc_attr($key) . '" required> ' . esc_html($option) . '</label>'; }
        echo '</fieldset><label><input type="checkbox" name="ack" value="1" required> قرأت الدرس وفهمت حدود دوري. سأناقش التطبيق مع المسؤول عند الحاجة.</label><button type="submit">تحقق وحفظ الفهم</button></form>';
    }
}
