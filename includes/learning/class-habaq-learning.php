<?php
/** Member learning paths. Content is read server-side; answer keys stay off the frontend. */
if (!defined('ABSPATH')) { exit; }

class Habaq_Learning {
    public static function register() {
        add_shortcode('habaq_learning', array(__CLASS__, 'render'));
        add_action('admin_menu', array(__CLASS__, 'menu'));
        add_action('admin_post_habaq_learning', array(__CLASS__, 'submit'));
        add_action('template_redirect', array(__CLASS__, 'prevent_cache'));
        add_filter('wp_privacy_personal_data_exporters', array(__CLASS__, 'exporters'));
        add_filter('wp_privacy_personal_data_erasers', array(__CLASS__, 'erasers'));
    }

    public static function catalog() {
        static $data;
        if ($data === null) {
            $data = require __DIR__ . '/catalog.php';
            $extension = require __DIR__ . '/curriculum/index.php';
            $data['tracks'] = array_merge($data['tracks'], $extension['tracks']);
            $data['sources'] = array_merge($data['sources'], $extension['sources']);
            $data['categories'] = $extension['categories'];
            $data['roles'] = $extension['roles'];
            foreach (array_merge(array('onboarding'), array_keys($extension['categories'])) as $group) {
                $data['modules'] = array_merge($data['modules'], require __DIR__ . '/curriculum/' . $group . '.php');
            }
        }
        return is_array($data) ? $data : array();
    }

    public static function eligible() {
        return is_user_logged_in() && (current_user_can('habaq_insider_access') || current_user_can('manage_options'));
    }

    public static function prevent_cache() {
        global $post;
        if ($post && has_shortcode($post->post_content, 'habaq_learning')) {
            if (!defined('DONOTCACHEPAGE')) { define('DONOTCACHEPAGE', true); }
            nocache_headers();
        }
    }

    public static function module($id) {
        foreach (self::catalog()['modules'] as $module) {
            if ($module['id'] === $id) { return $module; }
        }
        return null;
    }

    /** Keep previous lesson versions as an internal audit history. */
    private static function save_record($user_id, $id, $record) {
        $key = 'habaq_learning_' . $id;
        $previous = get_user_meta($user_id, $key, true);
        $record['revision'] = (is_array($previous) ? ($previous['revision'] ?? 0) : 0) + 1;
        if (is_array($previous) && isset($previous['version']) && $previous['version'] !== $record['version']) {
            $history = get_user_meta($user_id, $key . '_history', true);
            if (!is_array($history)) { $history = array(); }
            $history[$previous['version']] = $previous;
            update_user_meta($user_id, $key . '_history', $history);
        }
        update_user_meta($user_id, $key, $record);
    }

    public static function exporters($items) {
        $items['habaq-learning'] = array('exporter_friendly_name' => 'تعلّم حبق', 'callback' => array(__CLASS__, 'export_data'));
        return $items;
    }

    public static function erasers($items) {
        $items['habaq-learning'] = array('eraser_friendly_name' => 'تعلّم حبق', 'callback' => array(__CLASS__, 'erase_data'));
        return $items;
    }

    public static function export_data($email, $page = 1) {
        $user = get_user_by('email', $email);
        $data = array();
        if ($user) {
            $keys = array_merge(array('habaq_learning_track'), Habaq_Learning_Journey::META_KEYS);
            foreach (self::catalog()['modules'] as $module) {
                $keys[] = 'habaq_learning_' . $module['id'];
                $keys[] = 'habaq_learning_' . $module['id'] . '_history';
            }
            foreach ($keys as $key) {
                $value = get_user_meta($user->ID, $key, true);
                if ($value !== '') { $data[] = array('name' => $key, 'value' => is_array($value) ? wp_json_encode($value, JSON_UNESCAPED_UNICODE) : $value); }
            }
        }
        return array('data' => empty($data) ? array() : array(array('group_id' => 'habaq-learning', 'group_label' => 'تعلّم حبق', 'item_id' => 'habaq-learning-' . $user->ID, 'data' => $data)), 'done' => true);
    }

    public static function erase_data($email, $page = 1) {
        $user = get_user_by('email', $email);
        $removed = false;
        if ($user) {
            $removed = delete_user_meta($user->ID, 'habaq_learning_track') || $removed;
            foreach (Habaq_Learning_Journey::META_KEYS as $key) { $removed = delete_user_meta($user->ID, $key) || $removed; }
            foreach (self::catalog()['modules'] as $module) {
                $removed = delete_user_meta($user->ID, 'habaq_learning_' . $module['id']) || $removed;
                $removed = delete_user_meta($user->ID, 'habaq_learning_' . $module['id'] . '_history') || $removed;
            }
        }
        return array('items_removed' => $removed, 'items_retained' => false, 'messages' => array(), 'done' => true);
    }

    public static function path($user_id) {
        $catalog = self::catalog();
        $track = get_user_meta($user_id, 'habaq_learning_track', true);
        $ids = $catalog['shared'];
        if (isset($catalog['tracks'][$track])) {
            $ids = array_merge($ids, $catalog['tracks'][$track]['modules']);
        }
        return $ids;
    }

    public static function record($user_id, $module) {
        $item = get_user_meta($user_id, 'habaq_learning_' . $module['id'], true);
        if (!is_array($item) || !isset($item['version']) || $item['version'] !== $module['version']) {
            return array('status' => 'new');
        }
        return $item;
    }

    public static function unlocked($user_id, $id) {
        $module = self::module($id);
        // Specialist checks are optional and never change the required onboarding path.
        if ($module && !empty($module['optional'])) { return true; }
        foreach (self::path($user_id) as $step) {
            if ($step === $id) { return true; }
            if (self::record($user_id, self::module($step))['status'] !== 'complete') { return false; }
        }
        return false;
    }

    public static function grade($module, $answer) {
        return is_scalar($answer) && preg_match('/^\d+$/', (string) $answer)
            && (int) $answer === (int) $module['quiz']['correct'];
    }

    private static function form_start($op, $return) {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('habaq_learning');
        echo '<input type="hidden" name="action" value="habaq_learning"><input type="hidden" name="op" value="' . esc_attr($op) . '"><input type="hidden" name="return" value="' . esc_attr($return) . '">';
    }

    public static function render() {
        if (!self::eligible()) {
            return '<p dir="rtl">مساحة التعلم مخصصة لأعضاء حبق. <a href="' . esc_url(wp_login_url(get_permalink())) . '">تسجيل الدخول</a>. إذا كنت مسجلاً، اطلب تفعيل عضويتك من الإدارة.</p>';
        }
        wp_enqueue_style('habaq-learning', HABAQ_WP_CORE_URL . 'assets/learning.css', array(), HABAQ_WP_CORE_VERSION);
        if (isset($_GET['view']) && is_string($_GET['view']) && $_GET['view'] === 'library') {
            return Habaq_Learning_Library::render(self::notice());
        }
        $catalog = self::catalog();
        $user_id = get_current_user_id();
        $track = get_user_meta($user_id, 'habaq_learning_track', true);
        $return = get_permalink();
        $path = self::path($user_id);
        $done = 0;
        foreach ($path as $id) {
            if (self::record($user_id, self::module($id))['status'] === 'complete') { $done++; }
        }
        $selected = isset($_GET['lesson']) && is_string($_GET['lesson']) ? sanitize_key(wp_unslash($_GET['lesson'])) : Habaq_Learning_Journey::next_lesson($user_id);
        $module = self::module($selected);
        ob_start();
        echo '<section class="habaq-learning alignwide" dir="rtl"><header class="habaq-learning__hero"><p class="habaq-learning__eyebrow">تعلّم · جرّب · ناقش</p><h2>' . esc_html($catalog['title']) . '</h2><p>' . esc_html($catalog['notice']) . '</p></header>';
        Habaq_Learning_Library::tabs($return, false);
        echo '<p role="status">' . esc_html(self::notice()) . '</p>';
        echo '<p>أكملت ' . esc_html($done) . ' من ' . esc_html(count($path)) . ' دروس في مسارك الحالي.</p><progress max="' . esc_attr(count($path)) . '" value="' . esc_attr($done) . '"></progress>';
        Habaq_Learning_Journey::dashboard($user_id, $return);
        echo '<details><summary>كيف تبدأ؟</summary><p>ابدأ بالدروس المشتركة، ثم أكمل مسار الوحدة. الوقت المقترح موزع على أول أسبوعين ويمكن تعديله مع مسؤولك. اعمل على مهمة أولى صغيرة بمراجعة بشرية، ثم ناقش التطبيق بعد أربعة إلى ستة أسابيع.</p><p>نحفظ مسارك وإجابات التحقق وملخص التكليف وملاحظات المراجع في حسابك. تطلع عليها الإدارة فقط. اطلب تصحيحها أو تصديرها أو حذفها من إدارة الموقع. لا تدرج معلومات حساسة في الإجابات.</p></details>';
        echo '<details' . (!$track ? ' open' : '') . '><summary>اختيار مسار عملي</summary><p>اختر مع مرافق التعلم الوحدة أو الوظيفة الأقرب لأول مهمة. لا يلزم إكمال بقية المسارات. اختيار المسار لا ينشئ منصباً أو يغير صلاحياتك. الدروس المشتركة محفوظة عند تغييره؛ المراجعة التطبيقية تبدأ من جديد إذا غيرت المسار بعد إكماله.</p>';
        self::form_start('track', $return);
        echo '<label>مسار العمل <select name="track" required>';
        echo '<option value="">اختر المسار</option>';
        foreach ($catalog['tracks'] as $key => $value) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($track, $key, false) . '>' . esc_html($value['title']) . '</option>';
        }
        echo '</select></label><button type="submit">حفظ المسار</button></form></details><div class="habaq-learning__layout"><nav aria-label="دروس المسار"><h3>دروس قصيرة، تطبيق واحد</h3><p>اقرأ أي درس تحتاجه. سجل الإكمال بالترتيب كي تبني المهمة الأولى على أساس واضح.</p><ol>';
        $labels = array('new' => 'لم يبدأ', 'complete' => 'مكتمل', 'pending' => 'بانتظار المراجعة', 'revise' => 'يحتاج تعديلاً');
        foreach ($path as $id) {
            $item = self::module($id);
            $state = self::record($user_id, $item);
            echo '<li>';
            echo '<a href="' . esc_url(add_query_arg('lesson', $id, $return)) . '"' . ($selected === $id ? ' aria-current="step"' : '') . '>' . esc_html($item['title']) . '</a>';
            if (!self::unlocked($user_id, $id)) { echo ' · الإكمال بعد الدرس السابق'; }
            echo '<small>' . esc_html($item['minutes']) . ' دقيقة تقريباً · ' . esc_html(isset($labels[$state['status']]) ? $labels[$state['status']] : '') . '</small></li>';
        }
        echo '</ol></nav><article>';
        if ($module && in_array($selected, $path, true)) {
            $state = self::record($user_id, $module);
            echo '<h2>' . esc_html($module['title']) . '</h2>';
            if (!empty($module['outcome'])) { echo '<p class="habaq-learning__outcome"><strong>بعد هذا الدرس:</strong> ' . esc_html($module['outcome']) . '</p>'; }
            echo wp_kses_post($module['body_html']);
            if (!empty($module['job_aid'])) { echo '<details><summary>بطاقة عمل سريعة قابلة للطباعة</summary><p>قالب مقترح للتطبيق. عدله مع الفريق حسب المهمة.</p><div class="habaq-learning__job-aid">' . wp_kses_post($module['job_aid']) . '</div><p>يمكن طباعة الصفحة أو حفظها PDF من المتصفح للقراءة عند ضعف الاتصال.</p></details>'; }
            echo '<details><summary>المراجع وحالة الوثائق</summary><p>المراجع مسودات أو وثائق عمل. قد تحتاج صلاحية منفصلة على Drive. طلب الوصول لا يفتح الملف تلقائياً.</p><ul>';
            foreach ($catalog['sources'] as $source) {
                if (in_array($source['id'], $module['sources'], true)) {
                    echo '<li><a target="_blank" rel="noopener" href="' . esc_url($source['url']) . '">' . esc_html($source['title']) . '</a> · ' . esc_html($source['updated']) . '</li>';
                }
            }
            echo '</ul></details>';
            if (!empty($state['feedback'])) { echo '<p class="habaq-learning__feedback">ملاحظات المراجع: ' . esc_html($state['feedback']) . '</p>'; }
            $can_submit = self::unlocked($user_id, $selected) && !in_array($state['status'], array('complete', 'pending'), true);
            if ($state['status'] === 'complete') { echo '<p class="habaq-learning__feedback">سجل إكمال هذا الدرس محفوظ.</p>'; }
            elseif ($state['status'] === 'pending') { echo '<p class="habaq-learning__feedback">المهمة بانتظار المراجعة. يناقش المراجع معك المعايير ويطلب تعديلاً عند الحاجة.</p>'; }
            elseif (!$can_submit) { echo '<p class="habaq-learning__feedback">يمكنك قراءة الدرس الآن. سجل إكمال الدرس السابق قبل حفظ نتيجة هذا الدرس.</p>'; }
            if ($can_submit) {
            if ($module['requires_review']) { echo '<h3>معايير المهمة قبل إرسالها</h3><ul>'; foreach (Habaq_Learning_Journey::criteria() as $criterion) { echo '<li>' . esc_html($criterion) . '</li>'; } echo '</ul><p>التقدير المقترح للمهمة 45 إلى 90 دقيقة وفق النطاق المتفق عليه. إذا زاد الوقت، صغر المهمة مع مرافقك.</p>'; }
            self::form_start('lesson', $return);
            echo '<input type="hidden" name="lesson" value="' . esc_attr($selected) . '"><input type="hidden" name="version" value="' . esc_attr($module['version']) . '"><fieldset><legend>' . esc_html($module['quiz']['prompt']) . '</legend>';
            foreach ($module['quiz']['options'] as $key => $option) {
                echo '<label><input type="radio" name="answer" value="' . esc_attr($key) . '" required> ' . esc_html($option) . '</label>';
            }
            echo '</fieldset>';
            if ($module['requires_review']) {
                echo '<label>' . esc_html($module['assignment']) . '<textarea name="evidence" rows="6" maxlength="3000" required>' . esc_textarea(isset($state['evidence']) ? $state['evidence'] : '') . '</textarea></label>';
            }
            echo '<label><input type="checkbox" name="ack" value="1" required> قرأت الدرس وفهمت حدود دوري. هذا تأكيد تعلّم ولا يمثل اعتماداً للمسودات.</label><button type="submit">' . esc_html($module['requires_review'] ? 'إرسال للمراجعة' : 'تحقق وحفظ الإكمال') . '</button></form>';
            }
        } else {
            echo '<h2>خطوتك التالية</h2><p>' . esc_html(!$track && $done === count($path) ? 'اختر مسار وحدتك مع مرافق التعلم كي تبدأ المهمة الأولى.' : (Habaq_Learning_Journey::task_done($user_id) ? 'انتقل إلى مراجعة التطبيق أدناه واتفق على هدف التطوير التالي.' : 'اختر درساً من القائمة. سجل الإكمال بالترتيب ثم سلم مهمتك الأولى للمراجعة.')) . '</p>';
        }
        echo '</article></div>';
        Habaq_Learning_Journey::member_forms($user_id, $return);
        echo '</section>';
        return ob_get_clean();
    }

    private static function notice() {
        $key = isset($_GET['learning_notice']) && is_string($_GET['learning_notice']) ? sanitize_key(wp_unslash($_GET['learning_notice'])) : '';
        $messages = array('saved' => 'تم الحفظ.', 'wrong' => 'راجع المثال في الدرس وحاول مرة أخرى. هذه فرصة للتعلم ولا يوجد ترتيب أو عقوبة.', 'review' => 'تم الإرسال للمراجعة.', 'stale' => 'تغير السجل منذ فتح الصفحة. أعد فتحه قبل الحفظ.', 'invalid' => 'تعذر الحفظ. تحقق من البيانات والصلاحيات وإصدار الدرس.', 'created' => 'تم إنشاء صفحة التعلم. أضف رابطها إلى قائمة الموقع واستثنها من التخزين المؤقت.');
        return isset($messages[$key]) ? $messages[$key] : '';
    }

    public static function submit() {
        if (!self::eligible()) { wp_die('غير مصرح.', '', array('response' => 403)); }
        check_admin_referer('habaq_learning');
        $op = isset($_POST['op']) ? sanitize_key(wp_unslash($_POST['op'])) : '';
        $return = isset($_POST['return']) && is_string($_POST['return']) ? wp_validate_redirect(esc_url_raw(wp_unslash($_POST['return'])), home_url('/')) : home_url('/');
        $notice = 'invalid';
        $user_id = get_current_user_id();
        if ($op === 'track') {
            $track = isset($_POST['track']) ? sanitize_key(wp_unslash($_POST['track'])) : '';
            if (isset(self::catalog()['tracks'][$track])) {
                $previous_track = get_user_meta($user_id, 'habaq_learning_track', true);
                if ($previous_track && $previous_track !== $track) { Habaq_Learning_Journey::archive_reflection($user_id); }
                update_user_meta($user_id, 'habaq_learning_track', $track);
                $notice = 'saved';
            }
        } elseif ($op === 'lesson') {
            $id = isset($_POST['lesson']) ? sanitize_key(wp_unslash($_POST['lesson'])) : '';
            $module = self::module($id);
            $version = isset($_POST['version']) ? sanitize_text_field(wp_unslash($_POST['version'])) : '';
            if ($module && $version === $module['version'] && self::unlocked($user_id, $id) && isset($_POST['ack']) && $_POST['ack'] === '1') {
                $return = add_query_arg('lesson', $id, $return);
                $answer = isset($_POST['answer']) ? wp_unslash($_POST['answer']) : '';
                if (!self::grade($module, $answer)) { $notice = 'wrong'; }
                else {
                    $evidence = isset($_POST['evidence']) && is_string($_POST['evidence']) ? sanitize_textarea_field(wp_unslash($_POST['evidence'])) : '';
                    if (!$module['requires_review'] || (strlen(trim($evidence)) >= 30 && strlen($evidence) <= 12000)) {
                        $previous = self::record($user_id, $module);
                        if (!in_array($previous['status'], array('complete', 'pending'), true)) {
                            self::save_record($user_id, $id, array('version' => $module['version'], 'status' => $module['requires_review'] ? 'pending' : 'complete', 'answer' => (int) $answer, 'ack_at' => time(), 'evidence' => $evidence, 'updated_at' => time()));
                        }
                        $notice = $module['requires_review'] && $previous['status'] !== 'complete' ? 'review' : 'saved';
                    }
                }
            }
        } elseif (current_user_can('manage_options') && $op === 'review') {
            $member = isset($_POST['member']) ? absint($_POST['member']) : 0;
            $id = isset($_POST['lesson']) ? sanitize_key(wp_unslash($_POST['lesson'])) : '';
            $module = self::module($id);
            $decision = isset($_POST['decision']) ? sanitize_key(wp_unslash($_POST['decision'])) : '';
            $feedback = isset($_POST['feedback']) && is_string($_POST['feedback']) ? sanitize_textarea_field(wp_unslash($_POST['feedback'])) : '';
            $criteria = isset($_POST['criteria']) && is_array($_POST['criteria']) ? array_values(array_intersect(array_filter($_POST['criteria'], 'is_string'), array_keys(Habaq_Learning_Journey::criteria()))) : array();
            $review_version = isset($_POST['version']) && is_string($_POST['version']) ? wp_unslash($_POST['version']) : '';
            $revision = isset($_POST['revision']) && is_scalar($_POST['revision']) && preg_match('/^\d+$/D', (string) $_POST['revision']) ? (int) $_POST['revision'] : -1;
            if ($member && $member !== $user_id && get_userdata($member) && (user_can($member, 'habaq_insider_access') || user_can($member, 'manage_options')) && $module && $module['requires_review'] && $review_version === $module['version'] && in_array($decision, array('complete', 'revise'), true) && strlen(trim($feedback)) >= 3 && strlen($feedback) <= 2400 && ($decision !== 'complete' || count(array_unique($criteria)) === 4)) {
                $item = self::record($member, $module);
                if ($item['status'] === 'pending' && $revision === (int) ($item['revision'] ?? 0) && self::unlocked($member, $id)) {
                    $item['status'] = $decision;
                    $item['feedback'] = $feedback;
                    $item['reviewer'] = $user_id;
                    $item['reviewed_at'] = time();
                    $item['criteria'] = array_values(array_unique($criteria));
                    self::save_record($member, $id, $item);
                    $notice = 'saved';
                }
            }
        } elseif (current_user_can('manage_options') && $op === 'setup') {
            $page = get_page_by_path('learning');
            if (!$page) {
                $result = wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'مساحة تعلّم حبق', 'post_name' => 'learning', 'post_content' => '[habaq_learning]'), true);
                if (!is_wp_error($result) && $result) { $notice = 'created'; }
            } else { $notice = has_shortcode($page->post_content, 'habaq_learning') ? 'saved' : 'invalid'; }
        } else { $notice = Habaq_Learning_Journey::handle($op, $user_id); }
        wp_safe_redirect(add_query_arg('learning_notice', $notice, $return));
        exit;
    }

    public static function menu() {
        add_submenu_page('habaq-trainings', 'مسارات تعلّم حبق', 'مسارات التعلم', 'manage_options', 'habaq-learning', array(__CLASS__, 'admin'));
    }

    public static function admin() {
        if (!current_user_can('manage_options')) { return; }
        $return = admin_url('admin.php?page=habaq-learning');
        echo '<div class="wrap" dir="rtl"><h1>مسارات تعلّم حبق</h1><p>' . esc_html(self::notice()) . '</p><p>نسخة تجريبية. قبل التشغيل: حدد جهة الإبلاغ ومسؤول كل مسار، راجع حالة السياسات، واستثن صفحة التعلم من الكاش. جميع المراجعات هنا إدارية، ولا تمنح صلاحية نشر.</p>';
        self::form_start('setup', $return);
        echo '<button class="button" type="submit">إنشاء صفحة /learning دون تعديل صفحة موجودة</button></form>';
        Habaq_Learning_Journey::admin($return);
        echo '</div>';
    }
}
