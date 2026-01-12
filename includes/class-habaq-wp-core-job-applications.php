<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Job_Applications {
    /**
     * Track if notice was already rendered.
     *
     * @var bool
     */
    private static $notice_rendered = false;
    /**
     * Register the job application CPT.
     *
     * @return void
     */
    public static function register_cpt() {
        if (post_type_exists('job_application')) {
            return;
        }

        register_post_type('job_application', array(
            'labels' => array(
                'name' => __('طلبات التقديم', 'habaq-wp-core'),
                'singular_name' => __('طلب تقديم', 'habaq-wp-core'),
            ),
            'public' => false,
            'show_ui' => true,
            'supports' => array('title'),
            'menu_icon' => 'dashicons-forms',
        ));
    }

    /**
     * Register application shortcode.
     *
     * @return void
     */
    public static function register_shortcodes() {
        if (!shortcode_exists('habaq_job_application')) {
            add_shortcode('habaq_job_application', array(__CLASS__, 'render_form'));
        }
    }

    /**
     * Append the application form to job content if missing.
     *
     * @param string $content Post content.
     * @return string
     */
    public static function append_form($content) {
        if (!is_singular('job')) {
            return $content;
        }

        if (has_shortcode($content, 'habaq_job_application')) {
            return $content;
        }

        return $content . self::render_form();
    }

    /**
     * Render the application form.
     *
     * @return string
     */
    public static function render_form() {
        $job = self::get_job_context();
        if (!$job) {
            return '';
        }

        self::enqueue_styles();

        if (Habaq_WP_Core_Helpers::job_is_closed($job->ID)) {
            $deadline = Habaq_WP_Core_Helpers::get_job_deadline($job->ID);
            $deadline_text = Habaq_WP_Core_Helpers::job_format_date($deadline);
            $message = 'انتهى التقديم لهذه الفرصة.';
            if ($deadline_text) {
                $message .= ' ' . $deadline_text;
            }
            return '<div class="habaq-job-application__closed">' . esc_html($message) . '</div>';
        }

        $message = self::get_notice_message(false);
        $prefill = self::get_prefill_data();
        $output = '';
        if ($message) {
            $output .= '<div class="habaq-job-application__message">' . esc_html($message) . '</div>';
            self::$notice_rendered = true;
        }

        if (!empty($prefill['has_file_error'])) {
            $output .= '<div class="habaq-job-application__message habaq-job-application__message--warning">' . esc_html__('يرجى إعادة رفع السيرة الذاتية.', 'habaq-wp-core') . '</div>';
        }

        $output .= '<form class="habaq-job-application" method="post" enctype="multipart/form-data" action="' . esc_url(get_permalink($job)) . '">';
        $output .= wp_nonce_field('habaq_job_application', 'habaq_job_application_nonce', true, false);
        $output .= '<input type="hidden" name="habaq_job_application" value="1" />';
        $output .= '<input type="hidden" name="habaq_job_slug" value="' . esc_attr($job->post_name) . '" />';
        $output .= '<div class="habaq-job-application__hp"><label>Website<input type="text" name="habaq_hp" value="" /></label></div>';

        $output .= self::render_field('full_name', __('الاسم الكامل', 'habaq-wp-core'), 'text', true, $prefill);
        $output .= self::render_field('email', __('البريد الإلكتروني', 'habaq-wp-core'), 'email', true, $prefill);
        $output .= self::render_field('phone', __('رقم الهاتف', 'habaq-wp-core'), 'text', true, $prefill);
        $output .= self::render_field('city', __('المدينة', 'habaq-wp-core'), 'text', true, $prefill);
        $output .= self::render_field('availability', __('التفرغ المتوقع', 'habaq-wp-core'), 'text', true, $prefill);
        $output .= self::render_field('portfolio', __('الرابط/الملف الشخصي', 'habaq-wp-core'), 'url', false, $prefill);

        $output .= '<div class="habaq-job-application__field">';
        $output .= '<label for="habaq-apply-motivation">' . esc_html__('الدافع', 'habaq-wp-core') . '</label>';
        $output .= '<textarea id="habaq-apply-motivation" name="motivation" rows="4" required>' . esc_textarea(isset($prefill['motivation']) ? $prefill['motivation'] : '') . '</textarea>';
        $output .= '</div>';

        $output .= '<div class="habaq-job-application__field">';
        $output .= '<label for="habaq-apply-cv">' . esc_html__('السيرة الذاتية (PDF/DOC/DOCX)', 'habaq-wp-core') . '</label>';
        $output .= '<input type="file" id="habaq-apply-cv" name="cv_file" accept=".pdf,.doc,.docx" required />';
        $output .= '</div>';

        $consent_checked = !empty($prefill['consent']) ? ' checked' : '';
        $output .= '<div class="habaq-job-application__field habaq-job-application__consent">';
        $output .= '<label><input type="checkbox" name="consent" value="1" required' . $consent_checked . ' /> ' . esc_html__('أوافق على مشاركة بياناتي للتوظيف.', 'habaq-wp-core') . '</label>';
        $output .= '</div>';
        $output .= '<div class="habaq-job-application__actions">';
        $output .= '<button type="submit">' . esc_html__('إرسال الطلب', 'habaq-wp-core') . '</button>';
        $output .= '</div>';
        $output .= '</form>';

        return $output;
    }

    /**
     * Render a text input field.
     *
     * @param string $name Field name.
     * @param string $label Label.
     * @param string $type Input type.
     * @param bool   $required Required.
     * @param array  $prefill Prefill data.
     * @return string
     */
    private static function render_field($name, $label, $type, $required, $prefill) {
        $value = isset($prefill[$name]) ? $prefill[$name] : '';
        $output = '<div class="habaq-job-application__field">';
        $output .= '<label for="habaq-apply-' . esc_attr($name) . '">' . esc_html($label) . '</label>';
        $output .= '<input type="' . esc_attr($type) . '" id="habaq-apply-' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '"' . ($required ? ' required' : '') . ' />';
        $output .= '</div>';

        return $output;
    }

    /**
     * Handle form submissions.
     *
     * @return void
     */
    public static function handle_submission() {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        if (empty($_POST['habaq_job_application'])) {
            return;
        }

        $job = self::get_job_context();
        if (!$job) {
            return;
        }

        $redirect_url = get_permalink($job);

        if (!isset($_POST['habaq_job_application_nonce']) || !wp_verify_nonce(wp_unslash($_POST['habaq_job_application_nonce']), 'habaq_job_application')) {
            self::redirect_with_notice($redirect_url, 'invalid_nonce');
        }

        if (!empty($_POST['habaq_hp'])) {
            self::redirect_with_notice($redirect_url, 'invalid_form');
        }

        $rate_key = self::get_rate_limit_key($job->ID);
        if (get_transient($rate_key)) {
            self::redirect_with_notice($redirect_url, 'rate_limited');
        }

        if (Habaq_WP_Core_Helpers::job_is_closed($job->ID)) {
            self::redirect_with_notice($redirect_url, 'closed');
        }

        $fields = self::sanitize_fields();
        $required = array('full_name', 'email', 'phone', 'city', 'availability', 'motivation', 'consent');
        foreach ($required as $key) {
            if (empty($fields[$key])) {
                self::redirect_with_notice($redirect_url, 'missing_fields', $fields);
            }
        }

        if (empty($_FILES['cv_file']['name'])) {
            self::redirect_with_notice($redirect_url, 'missing_cv', $fields, true);
        }

        $file = $_FILES['cv_file'];
        $max_mb = (int) apply_filters('habaq_cv_max_mb', HABAQ_WP_CORE_DEFAULT_CV_MAX_MB);
        $max_bytes = $max_mb * 1024 * 1024;
        if (!empty($file['size']) && $file['size'] > $max_bytes) {
            self::redirect_with_notice($redirect_url, 'cv_too_large', $fields, true);
        }

        $allowed_mimes = array(
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        );
        $file_check = wp_check_filetype_and_ext($file['tmp_name'], $file['name'], $allowed_mimes);
        if (empty($file_check['ext']) || empty($file_check['type'])) {
            self::redirect_with_notice($redirect_url, 'invalid_cv', $fields, true);
        }

        if (!function_exists('wp_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $upload = wp_handle_upload($file, array(
            'test_form' => false,
            'mimes' => $allowed_mimes,
        ));
        if (!empty($upload['error'])) {
            self::redirect_with_notice($redirect_url, 'upload_failed', $fields, true);
        }

        $application_id = wp_insert_post(array(
            'post_title' => sprintf('Application: %s - %s', $job->post_title, $fields['full_name']),
            'post_status' => 'private',
            'post_type' => 'job_application',
        ));

        if (is_wp_error($application_id) || !$application_id) {
            self::redirect_with_notice($redirect_url, 'save_failed', $fields, true);
        }

        $attachment_id = self::attach_cv($upload, $application_id);
        if (!$attachment_id) {
            self::redirect_with_notice($redirect_url, 'upload_failed', $fields, true);
        }

        $deadline = Habaq_WP_Core_Helpers::get_job_deadline($job->ID);
        $taxonomy_data = self::get_job_taxonomy_data($job->ID);

        update_post_meta($application_id, 'full_name', $fields['full_name']);
        update_post_meta($application_id, 'email', $fields['email']);
        update_post_meta($application_id, 'phone', $fields['phone']);
        update_post_meta($application_id, 'city', $fields['city']);
        update_post_meta($application_id, 'availability', $fields['availability']);
        update_post_meta($application_id, 'portfolio', $fields['portfolio']);
        update_post_meta($application_id, 'motivation', $fields['motivation']);
        update_post_meta($application_id, 'consent', $fields['consent']);
        update_post_meta($application_id, 'job_id', $job->ID);
        update_post_meta($application_id, 'job_title', $job->post_title);
        update_post_meta($application_id, 'job_url', get_permalink($job));
        update_post_meta($application_id, 'job_unit', $taxonomy_data['job_unit']);
        update_post_meta($application_id, 'job_type', $taxonomy_data['job_type']);
        update_post_meta($application_id, 'job_location', $taxonomy_data['job_location']);
        update_post_meta($application_id, 'job_level', $taxonomy_data['job_level']);
        update_post_meta($application_id, 'habaq_deadline', $deadline);
        update_post_meta($application_id, 'cv_attachment_id', $attachment_id);
        update_post_meta($application_id, 'cv_url', wp_get_attachment_url($attachment_id));

        self::send_notifications($application_id, $fields['email'], $job->post_title, $attachment_id, $fields['full_name']);
        set_transient($rate_key, time(), 60);

        self::redirect_with_notice(home_url('/'), 'success');
    }

    /**
     * Render the transient notice in the footer/body.
     *
     * @return void
     */
    public static function render_notice() {
        if (self::$notice_rendered) {
            return;
        }

        $message = self::get_notice_message(true);
        if ($message) {
            echo '<div class="habaq-job-application__notice">' . esc_html($message) . '</div>';
        }
    }

    /**
     * Resolve the current job context.
     *
     * @return WP_Post|null
     */
    private static function get_job_context() {
        if (is_singular('job')) {
            return get_post();
        }

        if (!empty($_POST['habaq_job_slug'])) {
            $slug = sanitize_title(wp_unslash($_POST['habaq_job_slug']));
            if ($slug) {
                $job = get_page_by_path($slug, OBJECT, 'job');
                if ($job) {
                    return $job;
                }
            }
        }

        if (!empty($_GET['job'])) {
            $slug = sanitize_title(wp_unslash($_GET['job']));
            if ($slug) {
                $job = get_page_by_path($slug, OBJECT, 'job');
                if ($job) {
                    return $job;
                }
            }
        }

        return null;
    }

    /**
     * Attach the uploaded CV.
     *
     * @param array $upload Upload data.
     * @param int   $application_id Application ID.
     * @return int
     */
    private static function attach_cv($upload, $application_id) {
        $attachment = array(
            'post_mime_type' => $upload['type'],
            'post_title' => basename($upload['file']),
            'post_content' => '',
            'post_status' => 'private',
            'post_parent' => $application_id,
        );

        $attachment_id = wp_insert_attachment($attachment, $upload['file'], $application_id);
        if (is_wp_error($attachment_id)) {
            return 0;
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        if ($attach_data) {
            wp_update_attachment_metadata($attachment_id, $attach_data);
        }

        return (int) $attachment_id;
    }

    /**
     * Send admin and applicant emails.
     *
     * @param int    $application_id Application ID.
     * @param string $email Applicant email.
     * @param string $job_title Job title.
     * @param int    $attachment_id CV attachment ID.
     * @param string $full_name Applicant name.
     * @return void
     */
    private static function send_notifications($application_id, $email, $job_title, $attachment_id, $full_name) {
        $to = apply_filters('habaq_apply_to_email', HABAQ_APPLY_TO);
        $cv_url = wp_get_attachment_url($attachment_id);

        $admin_subject = sprintf(__('طلب تقديم جديد رقم %d', 'habaq-wp-core'), $application_id);
        $admin_message = __('تم استلام طلب تقديم جديد.', 'habaq-wp-core') . "\n";
        $admin_message .= sprintf("%s %d\n", __('رقم الطلب:', 'habaq-wp-core'), $application_id);
        $admin_message .= sprintf("%s %s\n", __('عنوان الفرصة:', 'habaq-wp-core'), $job_title);
        $admin_message .= sprintf("%s %s\n", __('اسم المتقدم:', 'habaq-wp-core'), $full_name);
        if ($cv_url) {
            $admin_message .= sprintf("%s %s\n", __('السيرة الذاتية:', 'habaq-wp-core'), $cv_url);
        }

        wp_mail($to, $admin_subject, $admin_message);

        if ($email) {
            $user_subject = sprintf(__('تم استلام طلبك لفرصة %s', 'habaq-wp-core'), $job_title);
            $user_message = sprintf(__('نشكر لك اهتمامك. تم استلام طلبك للفرصة %s وسنراجع الطلب قريبًا.', 'habaq-wp-core'), $job_title);
            wp_mail($email, $user_subject, $user_message);
        }
    }

    /**
     * Build a rate limit key per IP and job.
     *
     * @param int $job_id Job ID.
     * @return string
     */
    private static function get_rate_limit_key($job_id) {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
        return 'habaq_apply_rate_' . md5($job_id . '|' . $ip);
    }

    /**
     * Redirect with a transient notice token.
     *
     * @param string $url Target URL.
     * @param string $message_key Message key.
     * @param array  $form_data Form data.
     * @param bool   $file_error Flag for file errors.
     * @return void
     */
    private static function redirect_with_notice($url, $message_key, $form_data = array(), $file_error = false) {
        $token = wp_generate_password(12, false);
        set_transient('habaq_notice_' . $token, $message_key, 300);
        if (!empty($form_data)) {
            $form_data['has_file_error'] = $file_error;
            set_transient('habaq_form_' . $token, $form_data, 300);
        }
        $redirect = add_query_arg('habaq_notice', $token, $url);
        wp_safe_redirect($redirect);
        exit;
    }

    /**
     * Resolve notice message from transient token.
     *
     * @return string
     */
    private static function get_notice_message($delete) {
        if (empty($_GET['habaq_notice'])) {
            return '';
        }

        $token = sanitize_text_field(wp_unslash($_GET['habaq_notice']));
        if ($token === '') {
            return '';
        }

        $message_key = get_transient('habaq_notice_' . $token);
        if (!$message_key) {
            return '';
        }

        if ($delete) {
            delete_transient('habaq_notice_' . $token);
        }

        $messages = array(
            'success' => 'تم إرسال الطلب بنجاح.',
            'missing_fields' => 'يرجى تعبئة جميع الحقول المطلوبة.',
            'missing_cv' => 'يرجى إرفاق السيرة الذاتية.',
            'invalid_cv' => 'صيغة السيرة الذاتية غير مدعومة.',
            'cv_too_large' => 'حجم السيرة الذاتية كبير جدًا.',
            'upload_failed' => 'تعذر رفع الملف. حاول مرة أخرى.',
            'save_failed' => 'تعذر حفظ الطلب.',
            'invalid_nonce' => 'تعذر التحقق من الطلب.',
            'invalid_form' => 'تعذر إرسال الطلب.',
            'rate_limited' => 'يرجى الانتظار قبل إعادة الإرسال.',
            'closed' => 'انتهى التقديم لهذه الفرصة.',
        );

        return isset($messages[$message_key]) ? $messages[$message_key] : '';
    }

    /**
     * Get prefill data from transient token.
     *
     * @return array
     */
    private static function get_prefill_data() {
        if (empty($_GET['habaq_notice'])) {
            return array();
        }

        $token = sanitize_text_field(wp_unslash($_GET['habaq_notice']));
        if ($token === '') {
            return array();
        }

        $data = get_transient('habaq_form_' . $token);
        if (!$data || !is_array($data)) {
            return array();
        }

        delete_transient('habaq_form_' . $token);

        return $data;
    }

    /**
     * Sanitize form fields.
     *
     * @return array
     */
    private static function sanitize_fields() {
        return array(
            'full_name' => isset($_POST['full_name']) ? sanitize_text_field(wp_unslash($_POST['full_name'])) : '',
            'email' => isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '',
            'phone' => isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '',
            'city' => isset($_POST['city']) ? sanitize_text_field(wp_unslash($_POST['city'])) : '',
            'availability' => isset($_POST['availability']) ? sanitize_text_field(wp_unslash($_POST['availability'])) : '',
            'portfolio' => isset($_POST['portfolio']) ? esc_url_raw(wp_unslash($_POST['portfolio'])) : '',
            'motivation' => isset($_POST['motivation']) ? sanitize_textarea_field(wp_unslash($_POST['motivation'])) : '',
            'consent' => !empty($_POST['consent']) ? '1' : '',
        );
    }

    /**
     * Get taxonomy data for job.
     *
     * @param int $job_id Job ID.
     * @return array
     */
    private static function get_job_taxonomy_data($job_id) {
        $taxonomies = array('job_unit', 'job_type', 'job_location', 'job_level');
        $data = array();
        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($job_id, $taxonomy);
            if (is_wp_error($terms) || empty($terms)) {
                $data[$taxonomy] = '';
                continue;
            }
            $names = wp_list_pluck($terms, 'name');
            $data[$taxonomy] = implode(', ', $names);
        }

        return $data;
    }

    /**
     * Enqueue frontend styles.
     *
     * @return void
     */
    private static function enqueue_styles() {
        $css = '.habaq-job-application{display:grid;gap:16px;border:1px solid rgba(0,0,0,.08);border-radius:18px;padding:18px;background:#fff}
.habaq-job-application__field label{display:block;margin-bottom:6px;font-size:.9rem;color:#444}
.habaq-job-application__field input[type="text"],
.habaq-job-application__field input[type="email"],
.habaq-job-application__field input[type="url"],
.habaq-job-application__field input[type="file"],
.habaq-job-application__field textarea{width:100%;padding:10px 12px;border:1px solid #d5d5d5;border-radius:10px}
.habaq-job-application__consent label{display:flex;gap:8px;align-items:flex-start;font-size:.9rem}
.habaq-job-application__actions button{background:#111;color:#fff;border:0;border-radius:10px;padding:10px 16px;cursor:pointer}
.habaq-job-application__message{padding:12px;border-radius:10px;background:#f0f7ff;border:1px solid #cfe2ff}
.habaq-job-application__message--warning{background:#fff8e1;border-color:#ffe4a3}
.habaq-job-application__notice{position:fixed;bottom:20px;left:20px;right:20px;max-width:520px;margin:auto;background:#111;color:#fff;padding:14px;border-radius:12px;z-index:9999}
.habaq-job-application__closed{padding:14px;border:1px solid rgba(0,0,0,.12);border-radius:12px;background:#fafafa}
.habaq-job-application__hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
@media (max-width:720px){.habaq-job-application{padding:14px}}';

        Habaq_WP_Core_Helpers::enqueue_inline_style($css);
    }
}
