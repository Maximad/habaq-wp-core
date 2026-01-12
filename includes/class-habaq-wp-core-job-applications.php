<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Job_Applications {
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
            'label' => 'Job Applications',
            'public' => false,
            'show_ui' => true,
            'supports' => array('title'),
            'show_in_rest' => true,
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
     * Render the application form.
     *
     * @return string
     */
    public static function render_form() {
        $job = self::get_job_context();
        if (!$job) {
            return '';
        }

        if (Habaq_WP_Core_Helpers::job_is_closed($job->ID)) {
            $deadline = Habaq_WP_Core_Helpers::get_job_deadline($job->ID);
            $deadline_text = Habaq_WP_Core_Helpers::job_format_date($deadline);
            $message = 'انتهى التقديم لهذه الفرصة.';
            if ($deadline_text) {
                $message .= ' ' . $deadline_text;
            }
            return '<div class="habaq-job-application__closed">' . esc_html($message) . '</div>';
        }

        $message = self::get_notice_message();
        $output = '';
        if ($message) {
            $output .= '<div class="habaq-job-application__message">' . esc_html($message) . '</div>';
        }

        $output .= '<form class="habaq-job-application" method="post" enctype="multipart/form-data">';
        $output .= wp_nonce_field('habaq_job_application', 'habaq_job_application_nonce', true, false);
        $output .= '<input type="hidden" name="habaq_job_application" value="1" />';
        $output .= '<input type="hidden" name="habaq_job_slug" value="' . esc_attr($job->post_name) . '" />';
        $output .= '<div style="display:none;"><label>Website<input type="text" name="habaq_hp" value="" /></label></div>';
        $output .= '<div class="habaq-job-application__field">';
        $output .= '<label for="habaq-apply-full-name">' . esc_html__('الاسم الكامل', 'habaq-wp-core') . '</label>';
        $output .= '<input type="text" id="habaq-apply-full-name" name="full_name" required />';
        $output .= '</div>';
        $output .= '<div class="habaq-job-application__field">';
        $output .= '<label for="habaq-apply-email">' . esc_html__('البريد الإلكتروني', 'habaq-wp-core') . '</label>';
        $output .= '<input type="email" id="habaq-apply-email" name="email" required />';
        $output .= '</div>';
        $output .= '<div class="habaq-job-application__field">';
        $output .= '<label for="habaq-apply-motivation">' . esc_html__('الدافع', 'habaq-wp-core') . '</label>';
        $output .= '<textarea id="habaq-apply-motivation" name="motivation" rows="4" required></textarea>';
        $output .= '</div>';
        $output .= '<div class="habaq-job-application__field">';
        $output .= '<label for="habaq-apply-cv">' . esc_html__('السيرة الذاتية (PDF/DOC/DOCX)', 'habaq-wp-core') . '</label>';
        $output .= '<input type="file" id="habaq-apply-cv" name="cv_file" accept=".pdf,.doc,.docx" required />';
        $output .= '</div>';
        $output .= '<div class="habaq-job-application__field habaq-job-application__consent">';
        $output .= '<label><input type="checkbox" name="consent" value="1" required /> ' . esc_html__('أوافق على مشاركة بياناتي للتوظيف.', 'habaq-wp-core') . '</label>';
        $output .= '</div>';
        $output .= '<div class="habaq-job-application__actions">';
        $output .= '<button type="submit">' . esc_html__('إرسال الطلب', 'habaq-wp-core') . '</button>';
        $output .= '</div>';
        $output .= '</form>';

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

        if (!isset($_POST['habaq_job_application_nonce']) || !wp_verify_nonce(wp_unslash($_POST['habaq_job_application_nonce']), 'habaq_job_application')) {
            self::redirect_with_notice(get_permalink($job), 'invalid_nonce');
        }

        if (!empty($_POST['habaq_hp'])) {
            self::redirect_with_notice(get_permalink($job), 'invalid_form');
        }

        $rate_key = self::get_rate_limit_key($job->ID);
        if (get_transient($rate_key)) {
            self::redirect_with_notice(get_permalink($job), 'rate_limited');
        }

        if (Habaq_WP_Core_Helpers::job_is_closed($job->ID)) {
            self::redirect_with_notice(get_permalink($job), 'closed');
        }

        $full_name = isset($_POST['full_name']) ? sanitize_text_field(wp_unslash($_POST['full_name'])) : '';
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $motivation = isset($_POST['motivation']) ? sanitize_textarea_field(wp_unslash($_POST['motivation'])) : '';
        $consent = !empty($_POST['consent']) ? '1' : '';

        if ($full_name === '' || $email === '' || $motivation === '' || $consent === '') {
            self::redirect_with_notice(get_permalink($job), 'missing_fields');
        }

        if (empty($_FILES['cv_file']['name'])) {
            self::redirect_with_notice(get_permalink($job), 'missing_cv');
        }

        $file = $_FILES['cv_file'];
        $max_mb = (int) apply_filters('habaq_cv_max_mb', HABAQ_WP_CORE_DEFAULT_CV_MAX_MB);
        $max_bytes = $max_mb * 1024 * 1024;
        if (!empty($file['size']) && $file['size'] > $max_bytes) {
            self::redirect_with_notice(get_permalink($job), 'cv_too_large');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, array('pdf', 'doc', 'docx'), true)) {
            self::redirect_with_notice(get_permalink($job), 'invalid_cv');
        }

        if (!function_exists('wp_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $upload = wp_handle_upload($file, array('test_form' => false));
        if (!empty($upload['error'])) {
            self::redirect_with_notice(get_permalink($job), 'upload_failed');
        }

        $application_id = wp_insert_post(array(
            'post_title' => sprintf('Application: %s - %s', $job->post_title, $full_name),
            'post_status' => 'private',
            'post_type' => 'job_application',
        ));

        if (is_wp_error($application_id) || !$application_id) {
            self::redirect_with_notice(get_permalink($job), 'save_failed');
        }

        $attachment_id = self::attach_cv($upload, $application_id);
        if (!$attachment_id) {
            self::redirect_with_notice(get_permalink($job), 'upload_failed');
        }

        update_post_meta($application_id, 'full_name', $full_name);
        update_post_meta($application_id, 'email', $email);
        update_post_meta($application_id, 'motivation', $motivation);
        update_post_meta($application_id, 'consent', $consent);
        update_post_meta($application_id, 'job_id', $job->ID);
        update_post_meta($application_id, 'job_title', $job->post_title);
        update_post_meta($application_id, 'job_slug', $job->post_name);
        update_post_meta($application_id, 'job_deadline', Habaq_WP_Core_Helpers::get_job_deadline($job->ID));
        update_post_meta($application_id, 'cv_attachment_id', $attachment_id);

        self::send_notifications($application_id, $email, $job->post_title, $attachment_id);
        set_transient($rate_key, time(), 60);

        self::redirect_with_notice(home_url('/'), 'success');
    }

    /**
     * Render the transient notice in the footer.
     *
     * @return void
     */
    public static function render_notice() {
        $message = self::get_notice_message();
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
     * @return void
     */
    private static function send_notifications($application_id, $email, $job_title, $attachment_id) {
        $to = apply_filters('habaq_apply_to_email', HABAQ_WP_CORE_DEFAULT_APPLY_TO);
        $cv_url = wp_get_attachment_url($attachment_id);

        $admin_subject = sprintf('New Job Application #%d', $application_id);
        $admin_message = "A new job application has been submitted.\n";
        $admin_message .= "Application ID: {$application_id}\n";
        $admin_message .= "Job Title: {$job_title}\n";
        if ($cv_url) {
            $admin_message .= "CV: {$cv_url}\n";
        }

        wp_mail($to, $admin_subject, $admin_message);

        if ($email) {
            $user_subject = sprintf(__('Application received for %s', 'habaq-wp-core'), $job_title);
            $user_message = sprintf(__('تم استلام طلبك للفرصة %s.', 'habaq-wp-core'), $job_title);
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
     * @return void
     */
    private static function redirect_with_notice($url, $message_key) {
        $token = wp_generate_password(12, false);
        set_transient('habaq_notice_' . $token, $message_key, 300);
        $redirect = add_query_arg('habaq_notice', $token, $url);
        wp_safe_redirect($redirect);
        exit;
    }

    /**
     * Resolve notice message from transient token.
     *
     * @return string
     */
    private static function get_notice_message() {
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

        delete_transient('habaq_notice_' . $token);

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
}
