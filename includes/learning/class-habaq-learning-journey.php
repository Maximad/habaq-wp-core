<?php
/** Small-team onboarding operations. No role changes, mail queue or additional database tables. */
if (!defined('ABSPATH')) { exit; }

class Habaq_Learning_Journey {
    const META_KEYS = array('habaq_learning_plan', 'habaq_learning_support', 'habaq_learning_reflection', 'habaq_learning_reflection_history');

    public static function labels() {
        return array('new' => 'لم يبدأ', 'complete' => 'مكتمل', 'pending' => 'بانتظار المراجعة', 'revise' => 'يحتاج تعديلاً');
    }

    public static function criteria() {
        return array('purpose' => 'هدف واضح وناتج مناسب', 'safety' => 'السلامة والموافقة والحقوق', 'quality' => 'جودة مناسبة للمهمة والمصادر', 'handover' => 'موارد وموعد وتسليم ومراجع محدد');
    }

    public static function blockers() {
        return array('role' => 'الدور أو التوقعات غير واضحة', 'tools' => 'صلاحية أو أداة غير متاحة', 'time' => 'الوقت أو الاتصال أو الكهرباء', 'lesson' => 'أحتاج شرحاً أو بديلاً للتعلم', 'task' => 'أحتاج مساعدة في المهمة');
    }

    public static function meta($member, $key) {
        $value = get_user_meta($member, 'habaq_learning_' . $key, true);
        return is_array($value) ? $value : array();
    }

    public static function settings() {
        $value = get_option('habaq_learning_settings', array());
        return is_array($value) ? $value : array();
    }

    public static function valid_date($value) {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) { return false; }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value && (int) $date->format('Y') >= 2020 && (int) $date->format('Y') <= 2100;
    }

    public static function due($start, $days) {
        if (!self::valid_date($start)) { return ''; }
        return (new DateTimeImmutable($start))->modify('+' . (int) $days . ' days')->format('Y-m-d');
    }

    public static function foundation_done($member) {
        foreach (Habaq_Learning::catalog()['shared'] as $id) {
            if (Habaq_Learning::record($member, Habaq_Learning::module($id))['status'] !== 'complete') { return false; }
        }
        return true;
    }

    public static function task_done($member) {
        $track = get_user_meta($member, 'habaq_learning_track', true);
        $catalog = Habaq_Learning::catalog();
        if (!isset($catalog['tracks'][$track])) { return false; }
        foreach ($catalog['tracks'][$track]['modules'] as $id) {
            if (Habaq_Learning::record($member, Habaq_Learning::module($id))['status'] !== 'complete') { return false; }
        }
        return self::foundation_done($member);
    }

    public static function completed($member) {
        $plan = self::meta($member, 'plan');
        $reflection = self::meta($member, 'reflection');
        return !empty($plan['kickoff']) && self::task_done($member) && ($reflection['status'] ?? '') === 'complete' && ($reflection['track'] ?? '') === get_user_meta($member, 'habaq_learning_track', true);
    }

    public static function next_lesson($member) {
        foreach (Habaq_Learning::path($member) as $id) {
            if (Habaq_Learning::record($member, Habaq_Learning::module($id))['status'] !== 'complete') { return $id; }
        }
        return '';
    }

    private static function form($op, $return, $member = 0) {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('habaq_learning');
        echo '<input type="hidden" name="action" value="habaq_learning"><input type="hidden" name="op" value="' . esc_attr($op) . '"><input type="hidden" name="return" value="' . esc_attr($return) . '">';
        if ($member) { echo '<input type="hidden" name="member" value="' . esc_attr($member) . '">'; }
    }

    private static function field($name, $label, $value = '', $type = 'text', $max = 300, $required = false) {
        echo '<label>' . esc_html($label);
        if ($type === 'textarea') {
            echo '<textarea name="' . esc_attr($name) . '" rows="3" maxlength="' . esc_attr($max) . '"' . ($required ? ' required' : '') . '>' . esc_textarea($value) . '</textarea>';
        } else {
            echo '<input type="' . esc_attr($type) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" maxlength="' . esc_attr($max) . '"' . ($required ? ' required' : '') . '>';
        }
        echo '</label>';
    }

    public static function dashboard($member, $return) {
        $plan = self::meta($member, 'plan');
        $reflection = self::meta($member, 'reflection');
        $settings = self::settings();
        $start = $plan['start_date'] ?? '';
        $milestones = array(
            array('التعارف واتفاق الدور', 3, !empty($plan['kickoff']), 'لقاء قصير، تعريف بالفريق، صلاحيات وأدوات واتفاق على الوقت.'),
            array('الأساس المشترك', 7, self::foundation_done($member), 'خمسة دروس قصيرة. تعرف دورك وحدود القرار والسلامة.'),
            array('مساهمة أولى بمراجعة', 14, self::task_done($member), 'مسار وحدتك ومهمة صغيرة بمعايير معلنة وملاحظات بشرية.'),
            array('مراجعة التطبيق', 42, ($reflection['status'] ?? '') === 'complete', 'ناقش ما طبقته وما تحتاجه. اتفق على هدف التعلم التالي.')
        );
        echo '<section class="habaq-learning__journey" aria-label="خطة الانضمام"><h2>بداية واضحة، خطوة واحدة كل مرة</h2><p>هذه رحلة مقترحة لستة أسابيع. نكيف الوقت وحجم المهمة مع ظروفك ونوع تعاونك. لا تقييم للحضور على الشاشة.</p><ol class="habaq-learning__milestones">';
        foreach ($milestones as $stage) {
            echo '<li' . ($stage[2] ? ' class="is-complete"' : '') . '><strong>' . esc_html($stage[0]) . '</strong><span>' . esc_html($stage[2] ? 'مكتمل' : 'قادم') . '</span><small>' . esc_html($stage[3]) . '</small>';
            if ($start) { echo '<small>موعد مقترح: ' . esc_html(self::due($start, $stage[1])) . '</small>'; }
            echo '</li>';
        }
        echo '</ol>';
        if (!$plan) {
            echo '<p class="habaq-learning__feedback">يمكنك بدء القراءة الآن. تطلب الإدارة خطة قصيرة تحدد دورك ومرافق التعلم ومهمتك الأولى. هذا النموذج لا ينشئ اتفاق عمل.</p>';
        } else {
            echo '<details><summary>اتفاق البداية الخاص بي</summary><dl>';
            foreach (array('role' => 'الدور', 'mentor' => 'مرافق التعلم', 'start_date' => 'تاريخ البداية', 'weekly_minutes' => 'دقائق التعلم المتاحة أسبوعياً', 'first_task' => 'المساهمة الأولى المتفق عليها') as $key => $label) {
                echo '<dt>' . esc_html($label) . '</dt><dd>' . nl2br(esc_html($plan[$key] ?? '')) . '</dd>';
            }
            echo '</dl><p>هذا ملخص للتعلم. تفاصيل التعاون والتعويض والتفويض تثبت في اتفاق مستقل.</p></details>';
        }
        if (self::completed($member)) { echo '<p class="habaq-learning__feedback">اكتملت رحلة البداية ومراجعة التطبيق. احتفظ بهدف التطوير التالي وراجعه في لقائك المعتاد مع الفريق.</p>'; }
        echo '<details><summary>المساعدة وقنوات الإبلاغ</summary><p>للتعلم والأدوات: ' . esc_html(!empty($settings['support_contact']) ? $settings['support_contact'] : 'اطلب من الإدارة تحديد جهة دعم التعلم.') . '</p><p>للإبلاغ الآمن: ' . esc_html(!empty($settings['report_contact']) ? $settings['report_contact'] : 'لم تحدد الإدارة جهة الإبلاغ بعد.') . '</p><p>جهة بديلة عند تضارب المصالح: ' . esc_html(!empty($settings['alternate_contact']) ? $settings['alternate_contact'] : 'لم تحدد الإدارة جهة بديلة بعد.') . '</p><p>لا تستخدم طلب دعم التعلم لإرسال بلاغات أو بيانات أشخاص. لا تعد هذه الصفحة نظام شكاوى.</p></details></section>';
    }

    public static function member_forms($member, $return) {
        $support = self::meta($member, 'support');
        echo '<details class="habaq-learning__panel"><summary>أحتاج مساعدة كي أكمل</summary><p>سجل عائق التعلم وخطوة الدعم المطلوبة فقط. تطلع الإدارة على الطلب عند فتح لوحة التعلم. لا يرسل النظام تنبيهاً آلياً. للأمور العاجلة، استخدم جهة الدعم المعلنة.</p>';
        if ($support) {
            echo '<p>آخر طلب: ' . esc_html(self::blockers()[$support['category']] ?? '') . ' · ' . esc_html(($support['status'] ?? '') === 'resolved' ? 'تم الرد' : 'بانتظار الرد') . '</p><p>' . nl2br(esc_html($support['note'] ?? '')) . '</p>';
            if (!empty($support['feedback'])) { echo '<p class="habaq-learning__feedback">' . nl2br(esc_html($support['feedback'])) . '</p>'; }
        }
        self::form('support', $return);
        echo '<label>نوع المساعدة<select name="category" required><option value="">اختر السبب</option>';
        foreach (self::blockers() as $key => $label) { echo '<option value="' . esc_attr($key) . '">' . esc_html($label) . '</option>'; }
        echo '</select></label>';
        self::field('note', 'ما العائق وما المساعدة المطلوبة؟ دون أسماء أو تفاصيل حساسة. يحل الطلب الجديد محل آخر طلب.', '', 'textarea', 600, true);
        echo '<button type="submit">حفظ طلب الدعم</button></form></details>';

        if (!self::task_done($member)) { return; }
        $reflection = self::meta($member, 'reflection');
        echo '<details class="habaq-learning__panel"' . (($reflection['status'] ?? '') !== 'complete' ? ' open' : '') . '><summary>مراجعة التطبيق بعد أربعة إلى ستة أسابيع</summary><p>هذا حوار للتطوير. نتأكد مما أمكن تطبيقه ونعدل الدعم والتوقعات. لا يمثل تقييم توظيف أو صلاحية نشر.</p>';
        if (!empty($reflection['feedback'])) { echo '<p class="habaq-learning__feedback">ملاحظات المراجع: ' . nl2br(esc_html($reflection['feedback'])) . '</p>'; }
        if (($reflection['status'] ?? '') === 'complete') {
            echo '<p>تمت المراجعة. هدف التطوير التالي: ' . esc_html($reflection['next_goal'] ?? '') . '</p>';
        } elseif (($reflection['status'] ?? '') === 'pending') {
            echo '<p>أرسلت ملاحظات التطبيق وهي بانتظار حوار ومراجعة الإدارة.</p>';
        } else {
            self::form('reflection', $return);
            self::field('applied', 'ما الذي طبقته؟ اذكر مثالاً منزوع الهوية.', $reflection['applied'] ?? '', 'textarea', 600, true);
            self::field('obstacle', 'ما العائق أو الدعم الذي تحتاجه؟ يمكن كتابة: لا يوجد حالياً.', $reflection['obstacle'] ?? '', 'textarea', 600, true);
            self::field('next_goal', 'هدف صغير للتعلم خلال الشهر القادم', $reflection['next_goal'] ?? '', 'textarea', 600, true);
            echo '<button type="submit">إرسال ملاحظات التطبيق</button></form>';
        }
        echo '</details>';
    }

    private static function input($key, $max = 600) {
        $raw = $_POST[$key] ?? '';
        if (!is_string($raw)) { return null; }
        $value = sanitize_textarea_field(wp_unslash($raw));
        $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : preg_match_all('/./us', $value);
        return $length !== false && $length <= $max ? trim($value) : null;
    }

    private static function revision_matches($previous) {
        $value = self::input('revision', 12);
        return $value !== null && preg_match('/^\d+$/D', $value) && (int) $value === (int) ($previous['revision'] ?? 0);
    }

    public static function archive_reflection($member) {
        $record = self::meta($member, 'reflection');
        if ($record) {
            $history = self::meta($member, 'reflection_history');
            $history[] = $record;
            update_user_meta($member, 'habaq_learning_reflection_history', $history);
            delete_user_meta($member, 'habaq_learning_reflection');
        }
    }

    /** Called only after the learning endpoint checks membership and CSRF. */
    public static function handle($op, $member) {
        if ($op === 'support') {
            $category = self::input('category', 30);
            $note = self::input('note');
            if (isset(self::blockers()[$category ?? '']) && $note) {
                $previous = self::meta($member, 'support');
                update_user_meta($member, 'habaq_learning_support', array('category' => $category, 'note' => $note, 'status' => 'open', 'updated_at' => time(), 'revision' => ($previous['revision'] ?? 0) + 1));
                return 'saved';
            }
        } elseif ($op === 'reflection') {
            $values = array();
            foreach (array('applied', 'obstacle', 'next_goal') as $key) { $values[$key] = self::input($key); }
            $previous = self::meta($member, 'reflection');
            if (self::task_done($member) && !in_array($previous['status'] ?? '', array('pending', 'complete'), true) && !in_array(null, $values, true) && !in_array('', $values, true)) {
                update_user_meta($member, 'habaq_learning_reflection', array_merge($values, array('track' => get_user_meta($member, 'habaq_learning_track', true), 'status' => 'pending', 'updated_at' => time(), 'revision' => ($previous['revision'] ?? 0) + 1)));
                return 'review';
            }
        } elseif (current_user_can('manage_options')) {
            if ($op === 'learning_settings') {
                $settings = array();
                foreach (array('support_contact', 'report_contact', 'alternate_contact') as $key) {
                    $settings[$key] = self::input($key, 300);
                    if ($settings[$key] === null) { return 'invalid'; }
                }
                update_option('habaq_learning_settings', $settings, false);
                return 'saved';
            }
            $target = isset($_POST['member']) && is_scalar($_POST['member']) ? absint($_POST['member']) : 0;
            if (!$target || !get_userdata($target) || !user_can($target, 'habaq_insider_access') && !user_can($target, 'manage_options')) { return 'invalid'; }
            if ($op === 'plan') {
                $previous = self::meta($target, 'plan');
                if (!self::revision_matches($previous)) { return 'stale'; }
                $values = array();
                foreach (array('role', 'mentor', 'first_task', 'start_date') as $key) { $values[$key] = self::input($key, $key === 'first_task' ? 600 : 160); }
                $minutes = self::input('weekly_minutes', 4);
                if (in_array(null, $values, true) || in_array('', $values, true) || !self::valid_date($values['start_date']) || !preg_match('/^\d+$/D', $minutes ?? '') || (int) $minutes < 15 || (int) $minutes > 600) { return 'invalid'; }
                $values['weekly_minutes'] = (int) $minutes;
                $values['kickoff'] = ($_POST['kickoff'] ?? '') === '1';
                $values['updated_at'] = time(); $values['updated_by'] = $member; $values['revision'] = ($previous['revision'] ?? 0) + 1;
                update_user_meta($target, 'habaq_learning_plan', $values);
                return 'saved';
            }
            if ($op === 'support_reply' || $op === 'reflection_review') {
                $key = $op === 'support_reply' ? 'support' : 'reflection';
                $previous = self::meta($target, $key);
                if (!self::revision_matches($previous)) { return 'stale'; }
                $feedback = self::input('feedback');
                if (!$feedback || ($previous['status'] ?? '') !== ($key === 'support' ? 'open' : 'pending')) { return 'invalid'; }
                if ($key === 'reflection') {
                    $decision = self::input('decision', 20);
                    if ($target === $member || !self::task_done($target) || !in_array($decision, array('complete', 'revise'), true)) { return 'invalid'; }
                    $previous['status'] = $decision;
                } else { $previous['status'] = 'resolved'; }
                $previous['feedback'] = $feedback; $previous['reviewer'] = $member; $previous['reviewed_at'] = time(); $previous['revision']++;
                update_user_meta($target, 'habaq_learning_' . $key, $previous);
                return 'saved';
            }
        }
        return 'invalid';
    }

    public static function admin($return) {
        wp_enqueue_style('habaq-learning', HABAQ_WP_CORE_URL . 'assets/learning.css', array(), HABAQ_WP_CORE_VERSION);
        echo '<div class="habaq-learning habaq-learning--admin"><p>إدارة البداية: خطة واحدة ومهمة واحدة ومراجعة تطبيق لكل عضو. المواعيد مقترحة، وغياب التقدم يستدعي سؤالاً عن الدعم.</p>';
        $settings = self::settings();
        echo '<details><summary>جهات الدعم والإبلاغ</summary><p>تظهر للأعضاء فقط. استخدم جهة فريق معتمدة، وحدد بديلاً خارج تضارب المصالح. لا تسجل بيانات البلاغات هنا.</p>';
        self::form('learning_settings', $return);
        foreach (array('support_contact' => 'دعم التعلم والأدوات', 'report_contact' => 'جهة الإبلاغ الآمن', 'alternate_contact' => 'جهة الإبلاغ البديلة') as $key => $label) { self::field($key, $label, $settings[$key] ?? ''); }
        echo '<button type="submit">حفظ جهات الدعم</button></form></details>';

        $member_id = isset($_GET['member']) && is_scalar($_GET['member']) ? absint($_GET['member']) : 0;
        if ($member_id && get_userdata($member_id)) {
            self::admin_member($member_id, add_query_arg('member', $member_id, $return));
            echo '<p><a href="' . esc_url($return) . '">العودة لقائمة الأعضاء</a></p></div>';
            return;
        }
        $page = isset($_GET['member_page']) ? max(1, absint($_GET['member_page'])) : 1;
        $query = new WP_User_Query(array('number' => 25, 'offset' => ($page - 1) * 25, 'orderby' => 'ID', 'order' => 'ASC', 'meta_query' => array('relation' => 'OR', array('key' => 'habaq_learning_track', 'compare' => 'EXISTS'), array('key' => 'habaq_learning_plan', 'compare' => 'EXISTS'))));
        echo '<h2>الأعضاء والمتابعة</h2><p>أضف خطة لمستخدم عضو موجود بإدخال رقمه من صفحة المستخدمين. لا يمنح هذا الإجراء عضوية أو صلاحيات.</p><form method="get" action="' . esc_url(admin_url('admin.php')) . '"><input type="hidden" name="page" value="habaq-learning">';
        self::field('member', 'رقم المستخدم', '', 'number', 10, true);
        echo '<button type="submit">فتح خطة العضو</button></form><div class="habaq-learning__table"><table><thead><tr><th>العضو</th><th>الخطوة التالية</th><th>المراجعة والدعم</th><th>الخطة</th></tr></thead><tbody>';
        foreach ($query->get_results() as $member) {
            $pending = 0;
            foreach (Habaq_Learning::path($member->ID) as $id) { if (Habaq_Learning::record($member->ID, Habaq_Learning::module($id))['status'] === 'pending') { $pending++; } }
            $support = self::meta($member->ID, 'support'); $reflection = self::meta($member->ID, 'reflection');
            $next = self::next_lesson($member->ID);
            echo '<tr><td>' . esc_html($member->display_name) . '</td><td>' . esc_html($next ? Habaq_Learning::module($next)['title'] : (self::completed($member->ID) ? 'اكتملت البداية' : 'مراجعة التطبيق واتفاق البداية')) . '</td><td>' . esc_html($pending . ' مهمة للمراجعة') . (($support['status'] ?? '') === 'open' ? '<br>طلب دعم مفتوح' : '') . (($reflection['status'] ?? '') === 'pending' ? '<br>مراجعة تطبيق' : '') . '</td><td><a href="' . esc_url(add_query_arg('member', $member->ID, $return)) . '">فتح الخطة والمراجعات</a></td></tr>';
        }
        echo '</tbody></table></div><p>القائمة تعرض 25 عضواً في الصفحة. تجنب تفسير نسب الإكمال كأثر اجتماعي أو ترتيب للأشخاص.</p>';
        $pages = max(1, (int) ceil($query->get_total() / 25));
        for ($i = 1; $i <= $pages; $i++) { echo '<a href="' . esc_url(add_query_arg('member_page', $i, $return)) . '">' . esc_html($i) . '</a> '; }
        echo '</div>';
    }

    private static function admin_member($member, $return) {
        $user = get_userdata($member);
        if (!user_can($member, 'habaq_insider_access') && !user_can($member, 'manage_options')) { echo '<p>هذا المستخدم لا يملك وصول عضو حبق. تحقق من العضوية عبر إدارة المستخدمين قبل إنشاء خطة.</p>'; return; }
        echo '<h2>' . esc_html($user->display_name) . '</h2>';
        $plan = self::meta($member, 'plan');
        echo '<details open><summary>اتفاق البداية</summary>';
        self::form('plan', $return, $member);
        echo '<input type="hidden" name="revision" value="' . esc_attr($plan['revision'] ?? 0) . '">';
        foreach (array('role' => 'الدور المتفق عليه', 'mentor' => 'مرافق التعلم وجهة التواصل', 'start_date' => 'تاريخ البداية', 'weekly_minutes' => 'دقائق التعلم المتاحة أسبوعياً', 'first_task' => 'مهمة صغيرة، موعدها ومراجعها والموارد المتاحة') as $key => $label) {
            self::field($key, $label, $plan[$key] ?? '', $key === 'first_task' ? 'textarea' : ($key === 'start_date' ? 'date' : ($key === 'weekly_minutes' ? 'number' : 'text')), $key === 'first_task' ? 600 : 160, true);
        }
        echo '<label><input type="checkbox" name="kickoff" value="1"' . (!empty($plan['kickoff']) ? ' checked' : '') . '> تم لقاء البداية: وضحنا الدور وحدود القرار، عرفنا الفريق، واتفقنا على الوقت والأدوات والصلاحيات المطلوبة.</label><button type="submit">حفظ اتفاق البداية</button></form></details>';
        $support = self::meta($member, 'support');
        if ($support) {
            echo '<h3>دعم التعلم</h3><p>' . esc_html(self::blockers()[$support['category']] ?? '') . '</p><p>' . nl2br(esc_html($support['note'])) . '</p>';
            if (($support['status'] ?? '') === 'open') {
                self::form('support_reply', $return, $member);
                echo '<input type="hidden" name="revision" value="' . esc_attr($support['revision']) . '">';
                self::field('feedback', 'خطوة الدعم والمتابعة المتفق عليها', '', 'textarea', 600, true);
                echo '<button type="submit">حفظ الرد وإغلاق الطلب</button></form>';
            } else { echo '<p>' . nl2br(esc_html($support['feedback'] ?? '')) . '</p>'; }
        }
        echo '<h3>التعلم والمهمة الأولى</h3><ul>';
        foreach (Habaq_Learning::path($member) as $id) {
            $module = Habaq_Learning::module($id); $item = Habaq_Learning::record($member, $module);
            echo '<li>' . esc_html($module['title']) . ' · ' . esc_html(self::labels()[$item['status']] ?? '') . '</li>';
            if ($item['status'] === 'pending') {
                echo '<li><p>' . nl2br(esc_html($item['evidence'])) . '</p>';
                if ($member === get_current_user_id()) { echo '<p>يلزم مراجع آخر. لا تعتمد مهمتك بنفسك.</p></li>'; continue; }
                self::form('review', $return, $member);
                echo '<input type="hidden" name="lesson" value="' . esc_attr($id) . '"><input type="hidden" name="version" value="' . esc_attr($module['version']) . '"><input type="hidden" name="revision" value="' . esc_attr($item['revision'] ?? 0) . '">';
                foreach (self::criteria() as $key => $label) { echo '<label><input type="checkbox" name="criteria[]" value="' . esc_attr($key) . '"> ' . esc_html($label) . '</label>'; }
                echo '<p>لإكمال المهمة يلزم تحقق المعايير الأربعة. عند طلب تعديل، حدد التغيير والدعم المطلوب.</p>';
                self::field('feedback', 'ملاحظات محددة للعضو', '', 'textarea', 600, true);
                echo '<button name="decision" value="revise" type="submit">طلب تعديل</button> <button name="decision" value="complete" type="submit">اعتماد المهمة التعليمية</button></form></li>';
            }
        }
        echo '</ul>';
        $reflection = self::meta($member, 'reflection');
        if ($reflection) {
            echo '<h3>مراجعة التطبيق</h3>';
            foreach (array('applied' => 'التطبيق', 'obstacle' => 'العائق والدعم', 'next_goal' => 'هدف التطوير') as $key => $label) { echo '<p><strong>' . esc_html($label) . '</strong>: ' . nl2br(esc_html($reflection[$key] ?? '')) . '</p>'; }
            if (($reflection['status'] ?? '') === 'pending' && $member !== get_current_user_id()) {
                self::form('reflection_review', $return, $member);
                echo '<input type="hidden" name="revision" value="' . esc_attr($reflection['revision']) . '">';
                self::field('feedback', 'خلاصة الحوار وخطوة الدعم التالية', '', 'textarea', 600, true);
                echo '<button name="decision" value="revise" type="submit">طلب توضيح</button> <button name="decision" value="complete" type="submit">تسجيل مراجعة التطبيق</button></form>';
            } elseif (!empty($reflection['feedback'])) { echo '<p>' . nl2br(esc_html($reflection['feedback'])) . '</p>'; }
        }
    }
}
