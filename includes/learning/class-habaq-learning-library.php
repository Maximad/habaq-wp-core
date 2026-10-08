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

    /** Presentation only: no cookie, user record, permission or content version change. */
    public static function text_only() {
        return self::query('reading') === 'text';
    }

    public static function reading_url($url) {
        return self::text_only() ? add_query_arg('reading', 'text', $url) : $url;
    }

    public static function reading_controls() {
        $url = get_permalink();
        if (self::query('view') === 'library') { $url = add_query_arg('view', 'library', $url); }
        $role = sanitize_key(self::query('role'));
        if (isset(Habaq_Learning::catalog()['roles'][$role])) { $url = add_query_arg('role', $role, $url); }
        $id = sanitize_key(self::query('lesson'));
        if (Habaq_Learning::module($id)) { $url = add_query_arg('lesson', $id, $url); }
        $query = self::query('q');
        if ($query !== '' && strlen($query) <= 480) { $url = add_query_arg('q', $query, $url); }
        if (!self::text_only()) { $url = add_query_arg('reading', 'text', $url); }
        echo '<div class="habaq-learning__reading"><a href="' . esc_url($url) . '">' . esc_html(self::text_only() ? 'عرض الصور' : 'قراءة دون صور') . '</a><span>' . esc_html(self::text_only() ? 'صورة الافتتاحية متوقفة لتخفيف التحميل. حفظ النتائج يحتاج اتصالاً.' : 'اتصالك ضعيف؟ يمكنك إيقاف صورة الافتتاحية، أو حفظ الدرس PDF من المتصفح.') . '</span></div>';
    }

    public static function tabs($base, $library) {
        echo '<nav class="habaq-learning__tabs" aria-label="أقسام التعلم"><a href="' . esc_url($base) . '"' . (!$library ? ' aria-current="page"' : '') . '>رحلة الانضمام</a><a href="' . esc_url(add_query_arg('view', 'library', $base)) . '"' . ($library ? ' aria-current="page"' : '') . '>مكتبة الدورات حسب الدور</a></nav>';
    }

    /** One photographic opening for both views. Lessons use a shorter version. */
    public static function hero($base, $library, $compact = false) {
        echo '<header class="habaq-learning__hero' . ($compact ? ' habaq-learning__hero--compact' : '') . (self::text_only() ? ' habaq-learning__hero--text' : '') . '">';
        if (!self::text_only() && function_exists('wp_get_attachment_image')) {
            echo wp_get_attachment_image(Habaq_Learning::photo_id(), 'full', false, array('class' => 'habaq-learning__photo', 'alt' => 'جرار يحمل محصولاً أمام بيوت وجدران حجرية، من مكتبة صور حبق', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw'));
        }
        echo '<div class="habaq-learning__hero-inner"><p class="habaq-learning__eyebrow">حبق · نتعلّم معاً</p><h2>' . esc_html($library ? 'تعلّم لما تحتاجه اليوم' : 'أهلاً بك في حبق') . '</h2><p>' . esc_html($library ? 'اختر درساً يساعدك في مهمتك، جرّب ما تعلمته، وناقش النتيجة مع فريقك.' : 'تعرّف إلى الفريق وطريقة العمل، ثم جرّب مهمة صغيرة مع زميل يرافقك. خطوة واحدة في كل مرة.') . '</p><div class="habaq-learning__actions"><a class="habaq-learning__action" href="' . esc_url($library ? '#habaq-courses' : '#habaq-lesson') . '">' . esc_html($library ? 'اختر دورتك' : 'تابع درسك') . '</a><a class="habaq-learning__action habaq-learning__action--secondary" href="' . esc_url($library ? $base : add_query_arg('view', 'library', $base)) . '">' . esc_html($library ? 'رحلة الانضمام' : 'استكشف الدورات') . '</a></div>';
        if ($library) { echo '<p class="habaq-learning__hero-meta">' . esc_html(self::number(count(self::courses()))) . ' دورة اختيارية · تعلّم حسب حاجتك ووقتك</p>'; }
        echo '</div>';
        if (!self::text_only()) { echo '<p class="habaq-learning__photo-note">من مكتبة صور حبق</p>'; }
        echo '</header>';
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
        $base = self::reading_url(get_permalink());
        $library = add_query_arg('view', 'library', $base);
        $role = sanitize_key(self::query('role'));
        if (!isset($catalog['roles'][$role])) { $role = ''; }
        $query = self::query('q');
        if (strlen($query) > 480) { $query = ''; }
        $return = $role ? add_query_arg('role', $role, $library) : $library;
        if ($query !== '') { $return = add_query_arg('q', $query, $return); }
        $id = sanitize_key(self::query('lesson'));
        $module = Habaq_Learning::module($id);
        $member = get_current_user_id();
        ob_start();
        echo '<section class="habaq-learning alignfull" dir="rtl">';
        self::hero($base, true, $module && !empty($module['optional']));
        self::reading_controls();
        self::tabs($base, true);
        if ($notice) { echo '<p class="habaq-learning__feedback" role="status">' . esc_html($notice) . '</p>'; }
        echo '<p class="habaq-learning__library-note">ابدأ برحلة الانضمام، ثم اختر مع مرافقك دورة أو اثنتين تناسبان أول مهمة. اقتراحات الأدوار تساعدك على الاختيار، ولا تعني تعييناً أو تغييراً في الصلاحيات. سؤال التحقق يساعدك على فهم الدرس؛ التطبيق وملاحظات الفريق يساعدانك على إتقان العمل.</p>';
        if ($module && !empty($module['optional'])) {
            echo '<p><a href="' . esc_url($return) . '">العودة إلى الدورات</a></p><article id="habaq-courses" tabindex="-1" aria-labelledby="habaq-course-title" class="habaq-learning__course">';
            self::lesson($module, $member, $return);
            echo '</article>';
        } else {
            echo '<form id="habaq-courses" tabindex="-1" aria-label="اختيار الدورات" class="habaq-learning__filters" method="get" action="' . esc_url($base) . '"><input type="hidden" name="view" value="library">';
            if (self::text_only()) { echo '<input type="hidden" name="reading" value="text">'; }
            echo '<label>اعرض ما يناسب دوري<select name="role"><option value="">كل الأدوار والوظائف</option>';
            foreach ($catalog['roles'] as $key => $item) {
                echo '<option value="' . esc_attr($key) . '" ' . selected($role, $key, false) . '>' . esc_html($item['title']) . '</option>';
            }
            echo '</select></label><label>ابحث في الدورات<input type="search" name="q" value="' . esc_attr($query) . '" maxlength="120" placeholder="مثال: صوت، ميزانية، موافقة"></label><button type="submit">عرض الدورات</button></form>';
            if ($role) {
                $profile = $catalog['roles'][$role];
                echo '<div class="habaq-learning__outcome"><h3>' . esc_html($profile['title']) . '</h3><p><strong>ناتج عملي مقترح:</strong> ' . esc_html($profile['output']) . '</p><p>مسار يمكنك البدء به: ' . esc_html($catalog['tracks'][$profile['track']]['title']) . '. اختره مع مرافقك في رحلة الانضمام. عرض الدورات هنا لا يغيّر مسارك المحفوظ.</p><p>ابدأ بدورة أو اثنتين بعلامة «أولوية للمهمة»، ثم انتقل للتطوير حسب الحاجة.</p></div>';
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
                    echo '<article class="habaq-learning__card"><p class="habaq-learning__tag">' . esc_html($role ? ($priority ? 'أولوية للمهمة' : 'تطوير لاحق') : 'دورة تخصصية اختيارية') . '</p><h3><a href="' . esc_url(add_query_arg('lesson', $item['id'], $return) . '#habaq-courses') . '">' . esc_html($item['title']) . '</a></h3><p>' . esc_html($item['outcome']) . '</p><small>' . esc_html(self::duration($item['minutes'])) . ' للقراءة والتحقق · ' . esc_html(self::duration($item['practice_minutes'])) . ' تقريباً للتطبيق</small><p class="habaq-learning__tag">' . esc_html($state['status'] === 'complete' ? 'تم التحقق من الفهم' : 'جاهزة للبدء') . '</p></article>';
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
        echo '<p class="habaq-learning__tag">دورة تخصصية اختيارية · ' . esc_html(self::duration($module['minutes'])) . ' قراءة وتحقق + ' . esc_html(self::duration($module['practice_minutes'])) . ' تطبيق تقريباً</p><h2 id="habaq-course-title">' . esc_html($module['title']) . '</h2><p class="habaq-learning__outcome"><strong>بعد هذه الدورة:</strong> ' . esc_html($module['outcome']) . '</p>';
        echo wp_kses_post($module['body_html']);
        $notice_key = self::query('learning_notice');
        if ($notice_key === 'wrong' && !empty($module['feedback']['retry'])) {
            echo '<p class="habaq-learning__feedback" role="status"><strong>فكرة تساعدك:</strong> ' . esc_html($module['feedback']['retry']) . '</p>';
        }
        echo '<details><summary>بطاقة عمل سريعة قابلة للطباعة</summary><div class="habaq-learning__job-aid">' . wp_kses_post($module['job_aid']) . '</div><p>يمكن طباعة الصفحة أو حفظها PDF من المتصفح.</p></details><details><summary>المراجع وحالة المحتوى</summary><p>بنينا هذا الدرس على الوثائق التالية، وبعضها ما زال مسودة. التعلم منها لا يعني اعتمادها أو منح صلاحيات جديدة. قد تحتاج إذناً لفتح المصدر.</p><ul>';
        foreach (Habaq_Learning::catalog()['sources'] as $source) {
            if (in_array($source['id'], $module['sources'], true)) { echo '<li><a href="' . esc_url($source['url']) . '" target="_blank" rel="noopener">' . esc_html($source['title']) . '</a> · ' . esc_html($source['updated']) . '</li>'; }
        }
        echo '</ul></details><h3>تحقق من الفهم</h3><p>فكّر في المثال، ثم اختر الإجابة الأقرب. يمكنك العودة إلى الدرس والمحاولة مرة أخرى. لا نحفظ ملاحظات تمرينك هنا؛ ناقشها مع مرافقك إذا أردت ملاحظات على التطبيق.</p>';
        if ($state['status'] === 'complete') {
            echo '<p class="habaq-learning__feedback">تم حفظ التحقق من الفهم لهذا الإصدار. لا يمثل شهادة أو اعتماداً للمهارة.';
            if (!empty($module['feedback']['complete'])) { echo ' <strong>تذكّر:</strong> ' . esc_html($module['feedback']['complete']); }
            echo '</p>';
            return;
        }
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('habaq_learning');
        echo '<input type="hidden" name="action" value="habaq_learning"><input type="hidden" name="op" value="lesson"><input type="hidden" name="return" value="' . esc_attr($return) . '"><input type="hidden" name="lesson" value="' . esc_attr($module['id']) . '"><input type="hidden" name="version" value="' . esc_attr($module['version']) . '"><fieldset><legend>' . esc_html($module['quiz']['prompt']) . '</legend>';
        foreach ($module['quiz']['options'] as $key => $option) { echo '<label class="habaq-learning__choice"><input type="radio" name="answer" value="' . esc_attr($key) . '" required> ' . esc_html($option) . '</label>'; }
        echo '</fieldset><label class="habaq-learning__choice"><input type="checkbox" name="ack" value="1" required> قرأت الدرس وفهمت حدود دوري. سأناقش التطبيق مع المسؤول عند الحاجة.</label><button type="submit">تحقق وحفظ الفهم</button></form>';
    }
}
