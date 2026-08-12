<?php
/**
 * Plugin Name: AN Table Reservation Manager
 * Description: Lưu đơn đặt bàn từ Contact Form 7, gửi email cho khách/chủ, quản lý xác nhận/từ chối trên mobile và tự gửi kết quả song ngữ Đức/Anh.
 * Version: 1.9.0
 * Author: Diem
 * Text Domain: an-reservation-manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class AN_Table_Reservation_Manager {
    const VERSION = '1.9.0';
    const TABLE = 'an_reservations';
    const RESTAURANT_NAME = 'An Vegan Restaurant';
    const OPTION_OWNER_EMAILS = 'an_res_owner_emails';
    const OPTION_MANAGER_KEY = 'an_res_manager_key';
    const OPTION_MANAGER_PAGE_ID = 'an_res_manager_page_id';
    const OPTION_CF7_FORM_ID = 'an_res_cf7_form_id';
    const OPTION_RESTAURANT_NAME = 'an_res_restaurant_name';
    const OPTION_RESTAURANT_PHONE = 'an_res_restaurant_phone';
    const OPTION_RESTAURANT_ADDRESS = 'an_res_restaurant_address';
    const OPTION_EMAIL_TEMPLATES = 'an_res_email_templates';
    const OPTION_DB_VERSION = 'an_res_db_version';

    private $handled_submission_hashes = [];

    public function __construct() {
        register_activation_hook(__FILE__, [$this, 'activate']);

        add_action('plugins_loaded', [$this, 'maybe_upgrade_db']);
        add_action('wpcf7_before_send_mail', [$this, 'capture_cf7_submission'], 10, 3);
        add_action('wpcf7_mail_sent', [$this, 'capture_cf7_submission_fallback'], 10, 1);
        add_action('wpcf7_mail_failed', [$this, 'capture_cf7_submission_fallback'], 10, 1);
        add_action('wp_ajax_an_reservation_save_ajax', [$this, 'save_ajax_reservation']);
        add_action('wp_ajax_nopriv_an_reservation_save_ajax', [$this, 'save_ajax_reservation']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_shortcode('an_reservation_manager', [$this, 'reservation_manager_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('init', [$this, 'handle_manager_action']);
        add_action('admin_post_an_res_action', [$this, 'handle_admin_action']);
        add_action('admin_post_an_res_edit', [$this, 'handle_admin_edit']);
        add_action('admin_post_an_res_export', [$this, 'handle_export']);
    }

    public function activate() {
        global $wpdb;
        $table = $this->table_name();
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            token VARCHAR(64) NOT NULL,
            cf7_form_id BIGINT(20) UNSIGNED NULL,
            customer_name VARCHAR(190) NOT NULL,
            customer_phone VARCHAR(80) NOT NULL,
            customer_email VARCHAR(190) NOT NULL,
            res_date VARCHAR(80) NOT NULL,
            res_time VARCHAR(80) NOT NULL,
            guests VARCHAR(80) NOT NULL,
            notes TEXT NULL,
            language VARCHAR(10) NOT NULL DEFAULT 'en',
            status VARCHAR(30) NOT NULL DEFAULT 'pending',
            owner_notified TINYINT(1) NOT NULL DEFAULT 0,
            customer_notified TINYINT(1) NOT NULL DEFAULT 0,
            decision_email_sent TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            action_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY token (token),
            KEY res_date (res_date),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset_collate};";

        dbDelta($sql);
        update_option(self::OPTION_DB_VERSION, self::VERSION);

        if (!get_option(self::OPTION_MANAGER_KEY)) {
            update_option(self::OPTION_MANAGER_KEY, wp_generate_password(32, false, false));
        }

        if (!get_option(self::OPTION_OWNER_EMAILS)) {
            update_option(self::OPTION_OWNER_EMAILS, get_option('admin_email'));
        }

        if (!get_option(self::OPTION_RESTAURANT_NAME)) {
            update_option(self::OPTION_RESTAURANT_NAME, self::RESTAURANT_NAME);
        }

        if (!get_option(self::OPTION_EMAIL_TEMPLATES)) {
            update_option(self::OPTION_EMAIL_TEMPLATES, $this->default_email_templates());
        }

        $this->maybe_create_manager_page();
    }

    public function maybe_upgrade_db() {
        if (get_option(self::OPTION_DB_VERSION) !== self::VERSION) {
            $this->activate();
        }
    }

    private function table_name() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE;
    }

    private function maybe_create_manager_page() {
        $page_id = absint(get_option(self::OPTION_MANAGER_PAGE_ID));
        if ($page_id && get_post($page_id)) {
            return $page_id;
        }

        $existing = get_page_by_path('reservation-manager');
        if ($existing && strpos($existing->post_content, '[an_reservation_manager]') !== false) {
            update_option(self::OPTION_MANAGER_PAGE_ID, $existing->ID);
            return $existing->ID;
        }

        $page_id = wp_insert_post([
            'post_title'   => 'Reservation Manager',
            'post_name'    => 'reservation-manager',
            'post_content' => '[an_reservation_manager]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);

        if (!is_wp_error($page_id) && $page_id) {
            update_option(self::OPTION_MANAGER_PAGE_ID, $page_id);
            return $page_id;
        }

        return 0;
    }

    public function register_settings() {
        register_setting('an_res_settings_group', self::OPTION_OWNER_EMAILS, [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_email_list'],
            'default' => get_option('admin_email'),
        ]);

        register_setting('an_res_settings_group', self::OPTION_CF7_FORM_ID, [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 0,
        ]);

        register_setting('an_res_settings_group', self::OPTION_RESTAURANT_NAME, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => self::RESTAURANT_NAME,
        ]);

        register_setting('an_res_settings_group', self::OPTION_RESTAURANT_PHONE, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting('an_res_settings_group', self::OPTION_RESTAURANT_ADDRESS, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting('an_res_settings_group', self::OPTION_EMAIL_TEMPLATES, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_email_templates'],
            'default' => [],
        ]);
    }

    public function sanitize_email_list($value) {
        $emails = preg_split('/[,\n\r]+/', (string) $value);
        $valid = [];
        foreach ($emails as $email) {
            $email = sanitize_email(trim($email));
            if (is_email($email)) {
                $valid[] = $email;
            }
        }
        return implode(',', array_unique($valid));
    }

    public function sanitize_email_templates($value) {
        $defaults = $this->default_email_templates();
        $clean = [];
        $value = is_array($value) ? $value : [];

        foreach ($defaults as $key => $default) {
            $raw = isset($value[$key]) ? wp_unslash($value[$key]) : $default;

            if (strpos($key, 'subject') !== false) {
                $clean[$key] = sanitize_text_field($raw);
            } else {
                $clean[$key] = wp_kses_post($raw);
            }
        }

        return $clean;
    }

	public function admin_menu() {
        add_menu_page(
            'Reservierungen',
            'Reservierungen',
            'manage_options',
            'an-reservations',
            [$this, 'admin_reservations_page'],
            'dashicons-food',
            26
        );

        add_submenu_page(
            'an-reservations',
            'Reservierungsliste',
            'Liste',
            'manage_options',
            'an-reservations',
            [$this, 'admin_reservations_page']
        );

        add_submenu_page(
            'an-reservations',
            'Reservierungseinstellungen',
            'Einstellungen',
            'manage_options',
            'an-reservation-settings',
            [$this, 'settings_page']
        );
    }

    public function frontend_assets() {
        $css = $this->shared_css();
        wp_register_style('an-reservation-manager', false, [], self::VERSION);
        wp_enqueue_style('an-reservation-manager');
        wp_add_inline_style('an-reservation-manager', $css);

        $config = [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('an_reservation_ajax'),
        ];

        $js = "window.anReservationManager=" . wp_json_encode($config) . ";\n";
        $js .= <<<'JS'
(function(){
    function getField(form, name){
        var field = form.querySelector('[name="' + name + '"]');
        return field ? field.value : '';
    }

    function saveReservation(event){
        var form = event.target;
        if (!form || !form.querySelector('[name="res-date"]')) return;

        var status = event.detail && event.detail.status ? event.detail.status : '';
        if (['validation_failed','spam','aborted'].indexOf(status) !== -1) return;
        if (form.dataset.anReservationSaved === '1') return;

        var required = ['your-name','your-phone','your-email','res-date','res-time','res-guests'];
        for (var i = 0; i < required.length; i++) {
            if (!getField(form, required[i])) return;
        }

        form.dataset.anReservationSaved = '1';

        var body = new URLSearchParams();
        body.append('action', 'an_reservation_save_ajax');
        body.append('nonce', window.anReservationManager ? window.anReservationManager.nonce : '');
        ['your-name','your-phone','your-email','res-date','res-time','res-guests','res-notes','an-res-lang','an_res_lang'].forEach(function(name){
            body.append(name, getField(form, name));
        });

        fetch(window.anReservationManager.ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
            body: body.toString()
        }).catch(function(){
            form.dataset.anReservationSaved = '';
        });
    }

    document.addEventListener('DOMContentLoaded',function(){
        var d = document.querySelector('#res-date');
        if (d) {
            try { d.setAttribute('type','date'); } catch(e) {}

            var now = new Date();
            var yyyy = now.getFullYear();
            var mm = String(now.getMonth() + 1).padStart(2, '0');
            var dd = String(now.getDate()).padStart(2, '0');
            d.setAttribute('min', yyyy + '-' + mm + '-' + dd);

            var openPicker = function(){
                try {
                    if (typeof d.showPicker === 'function') d.showPicker();
                    else d.focus();
                } catch(e) { d.focus(); }
            };

            d.addEventListener('click', openPicker);
            d.addEventListener('focus', function(){ d.setAttribute('min', yyyy + '-' + mm + '-' + dd); });

            var wrap = d.closest('.an-input-wrapper');
            if (wrap) {
                wrap.addEventListener('click', function(){ openPicker(); });
            }
        }
    });

    document.addEventListener('wpcf7submit', saveReservation, false);
})();
JS;

        wp_register_script('an-reservation-manager', false, [], self::VERSION, true);
        wp_enqueue_script('an-reservation-manager');
        wp_add_inline_script('an-reservation-manager', $js);
    }

    public function admin_assets($hook) {
        if (strpos($hook, 'an-reservation') === false && strpos($hook, 'an-reservations') === false) {
            return;
        }
        wp_register_style('an-reservation-admin', false, [], self::VERSION);
        wp_enqueue_style('an-reservation-admin');
        wp_add_inline_style('an-reservation-admin', $this->shared_css());
    }

    private function shared_css() {
        return <<<CSS
.an-res-wrap{max-width:1180px;margin:20px auto;padding:0 14px;font-family:Arial,sans-serif;color:#2b241d}.an-res-top{display:flex;gap:12px;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px}.an-res-title{font-size:24px;font-weight:700;margin:0}.an-res-filter{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.an-res-filter input,.an-res-filter select{height:42px;border:1px solid #d9c9b4;border-radius:10px;padding:0 12px;background:#fff;color:#2b241d}.an-res-filter button,.an-res-btn{border:0;border-radius:12px;padding:12px 16px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;line-height:1;background:#8B6D47;color:#fff}.an-res-btn:hover{color:#fff;opacity:.9}.an-res-btn.confirm{background:#18864b}.an-res-btn.decline{background:#c0392b}.an-res-btn.secondary{background:#efe5d6;color:#5c4429}.an-res-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px}.an-res-card{background:#fff;border:1px solid #eadfce;border-radius:18px;padding:16px;box-shadow:0 8px 24px rgba(50,35,20,.06)}.an-res-card.pending{border-color:#d8b577}.an-res-card.confirmed{border-color:#78c394}.an-res-card.declined{border-color:#e49b91}.an-res-card h3{margin:0 0 8px;font-size:18px;color:#2b241d}.an-res-meta{display:grid;gap:7px;margin:10px 0;font-size:14px}.an-res-meta div{display:flex;gap:8px;justify-content:space-between;border-bottom:1px dashed #eee3d3;padding-bottom:7px}.an-res-meta strong{color:#6a5133;min-width:86px}.an-res-note{background:#fbf7f0;border-radius:12px;padding:10px;margin:10px 0;color:#4b4035}.an-res-status{display:inline-flex;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:700;text-transform:uppercase}.an-res-status.pending{background:#fff1d6;color:#9a6500}.an-res-status.confirmed{background:#dff6e8;color:#08723a}.an-res-status.declined{background:#ffe3df;color:#a42b1d}.an-res-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}.an-res-empty{background:#fff;border:1px solid #eadfce;border-radius:16px;padding:20px;text-align:center}.an-res-table{width:100%;border-collapse:collapse;background:#fff;border-radius:16px;overflow:hidden}.an-res-table th,.an-res-table td{padding:12px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}.an-res-table th{background:#fbf7f0;color:#6a5133}.an-res-alert{padding:12px 14px;border-radius:12px;margin:12px 0;background:#fbf7f0;border:1px solid #eadfce}.an-res-alert.success{background:#e5f8ed;border-color:#a9ddb9}.an-res-alert.error{background:#ffe9e5;border-color:#efb2aa}@media(max-width:720px){.an-res-wrap{margin:10px auto;padding:0 10px}.an-res-title{font-size:20px}.an-res-filter{width:100%}.an-res-filter input,.an-res-filter select,.an-res-filter button{width:100%}.an-res-grid{grid-template-columns:1fr}.an-res-card{border-radius:16px;padding:14px}.an-res-actions .an-res-btn{flex:1;min-height:46px}.an-res-table,.an-res-table thead,.an-res-table tbody,.an-res-table tr,.an-res-table th,.an-res-table td{display:block}.an-res-table thead{display:none}.an-res-table tr{border:1px solid #eadfce;border-radius:16px;margin-bottom:12px;padding:10px;background:#fff}.an-res-table td{border:0;padding:7px 4px}.an-res-table td:before{content:attr(data-label);display:block;font-size:12px;font-weight:700;color:#8B6D47;margin-bottom:3px}}
.an-reservation-form .an-input-wrapper,.an-reservation-form .an-select-wrapper{position:relative}.an-reservation-form .an-input-icon{pointer-events:none;z-index:2}.an-reservation-form #res-date{cursor:pointer}.an-reservation-form #res-date::-webkit-calendar-picker-indicator{opacity:0;position:absolute;inset:0;width:100%;height:100%;cursor:pointer}.an-reservation-form select{-webkit-appearance:none;appearance:none}.an-reservation-form .an-select-wrapper:after{content:'⌄';position:absolute;right:22px;top:50%;transform:translateY(-56%);font-size:22px;line-height:1;color:#8B6D47;pointer-events:none}
.an-reservation-form input[type='date']::-webkit-calendar-picker-indicator{display:none!important;-webkit-appearance:none!important}.an-reservation-form input[type='date']::-webkit-inner-spin-button,.an-reservation-form input[type='date']::-webkit-clear-button{display:none!important}.an-reservation-form input[type='date']{-webkit-appearance:none!important;appearance:none!important}.an-reservation-form select{-webkit-appearance:none!important;-moz-appearance:none!important;appearance:none!important;background-image:none!important}.an-reservation-form select::-ms-expand{display:none!important}.an-reservation-form .an-select-wrapper:after{display:none!important}
.an-res-btn.edit{background:#4caf50;color:#fff}.an-res-edit-form{display:none;margin-top:12px;background:#fbf7f0;border-radius:14px;padding:12px}.an-res-card.editing .an-res-edit-form{display:block}.an-res-edit-form label{display:block;font-weight:700;color:#6a5133;margin:8px 0 4px}.an-res-edit-form input,.an-res-edit-form select,.an-res-edit-form textarea{width:100%;border:1px solid #d9c9b4;border-radius:10px;padding:10px;background:#fff;color:#2b241d}.an-res-edit-form textarea{min-height:80px}.an-res-edit-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}@media(max-width:720px){.an-res-edit-row{grid-template-columns:1fr}}
.an-res-btn.mail{background:#3273dc;color:#fff}
CSS;
    }

    public function capture_cf7_submission_fallback($contact_form) {
        $submission = class_exists('WPCF7_Submission') ? WPCF7_Submission::get_instance() : null;
        $abort = false;
        $this->capture_cf7_submission($contact_form, $abort, $submission);
    }

    public function save_ajax_reservation() {
        check_ajax_referer('an_reservation_ajax', 'nonce');

        $posted = wp_unslash($_POST);
        if (!$this->is_reservation_submission(null, $posted)) {
            wp_send_json_error(['message' => 'Missing reservation fields']);
        }

        $data = $this->build_reservation_data($posted, null);
        $reservation_id = $this->save_reservation_and_notify($data);

        if ($reservation_id) {
            wp_send_json_success(['id' => $reservation_id]);
        }

        wp_send_json_error(['message' => 'Reservation could not be saved']);
    }

    public function capture_cf7_submission($contact_form, &$abort, $submission) {
        if (!$submission) {
            $submission = class_exists('WPCF7_Submission') ? WPCF7_Submission::get_instance() : null;
        }
        if (!$submission) {
            return;
        }

        $posted = $submission->get_posted_data();
        if (!$this->is_reservation_submission($contact_form, $posted)) {
            return;
        }

        $submission_hash = md5(wp_json_encode([
            $posted['your-name'] ?? '',
            $posted['your-phone'] ?? '',
            $posted['your-email'] ?? '',
            $posted['res-date'] ?? '',
            $posted['res-time'] ?? '',
            $posted['res-guests'] ?? '',
            $posted['res-notes'] ?? '',
        ]));

        if (isset($this->handled_submission_hashes[$submission_hash])) {
            return;
        }
        $this->handled_submission_hashes[$submission_hash] = true;

        $data = $this->build_reservation_data($posted, $contact_form);
        $this->save_reservation_and_notify($data);
    }

    private function build_reservation_data($posted, $contact_form = null) {
        $language = $this->posted_language($posted);
        if (!$language && $contact_form) {
            $language = $this->detect_form_language($contact_form);
        }
        if (!$language) {
            $language = 'en';
        }

        return [
            'cf7_form_id'    => is_object($contact_form) && method_exists($contact_form, 'id') ? absint($contact_form->id()) : 0,
            'customer_name'  => sanitize_text_field($posted['your-name'] ?? ''),
            'customer_phone' => sanitize_text_field($posted['your-phone'] ?? ''),
            'customer_email' => sanitize_email($posted['your-email'] ?? ''),
            'res_date'       => sanitize_text_field($posted['res-date'] ?? ''),
            'res_time'       => sanitize_text_field($posted['res-time'] ?? ''),
            'guests'         => sanitize_text_field($posted['res-guests'] ?? ''),
            'notes'          => sanitize_textarea_field($posted['res-notes'] ?? ''),
            'language'       => $language,
        ];
    }

    private function save_reservation_and_notify($data) {
        if (empty($data['customer_name']) || empty($data['customer_phone']) || empty($data['customer_email']) || empty($data['res_date']) || empty($data['res_time']) || empty($data['guests'])) {
            return 0;
        }

        $duplicate_id = $this->find_recent_duplicate($data);
        if ($duplicate_id) {
            return $duplicate_id;
        }

        $reservation_id = $this->insert_reservation($data);
        if (!$reservation_id) {
            return 0;
        }

        $reservation = $this->get_reservation($reservation_id);
        if (!$reservation) {
            return 0;
        }

        $owner_sent = $this->send_owner_new_reservation_email($reservation);
        $customer_sent = $this->send_customer_received_email($reservation);

        global $wpdb;
        $wpdb->update(
            $this->table_name(),
            [
                'owner_notified' => $owner_sent ? 1 : 0,
                'customer_notified' => $customer_sent ? 1 : 0,
                'updated_at' => current_time('mysql'),
            ],
            ['id' => $reservation_id],
            ['%d','%d','%s'],
            ['%d']
        );

        return $reservation_id;
    }

    private function posted_language($posted) {
        $lang = sanitize_key($posted['an-res-lang'] ?? ($posted['an_res_lang'] ?? ''));
        return in_array($lang, ['en', 'de'], true) ? $lang : '';
    }

    private function find_recent_duplicate($data) {
        global $wpdb;
        $since = date('Y-m-d H:i:s', current_time('timestamp') - 15 * MINUTE_IN_SECONDS);
        return absint($wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name()} WHERE customer_email = %s AND res_date = %s AND res_time = %s AND created_at >= %s ORDER BY id DESC LIMIT 1",
            $data['customer_email'],
            $data['res_date'],
            $data['res_time'],
            $since
        )));
    }

    private function detect_form_language($contact_form) {
        $title = '';
        $hash = '';

        if (is_object($contact_form) && method_exists($contact_form, 'title')) {
            $title = (string) $contact_form->title();
        }

        if (is_object($contact_form) && method_exists($contact_form, 'hash')) {
            $hash = (string) $contact_form->hash();
        }

        $text = strtolower(trim($title . ' ' . $hash));

        if (preg_match('/(^|[^a-z])(de|deutsch|german)([^a-z]|$)/i', $text)) {
            return 'de';
        }

        if (preg_match('/(^|[^a-z])(en|english)([^a-z]|$)/i', $text)) {
            return 'en';
        }

        return 'en';
    }

    private function reservation_language($r) {
        $lang = isset($r->language) ? strtolower((string) $r->language) : 'en';
        return $lang === 'de' ? 'de' : 'en';
    }

    private function is_reservation_submission($contact_form, $posted) {
        // Ưu tiên nhận theo field name để dùng được cả form EN và DE.
        // CF7 bản mới có thể hiển thị ID dạng chữ/hash nên không khóa cứng theo ID nữa.
        return isset(
            $posted['your-name'],
            $posted['your-phone'],
            $posted['your-email'],
            $posted['res-date'],
            $posted['res-time'],
            $posted['res-guests']
        );
    }

    private function insert_reservation($data) {
        global $wpdb;
        $inserted = $wpdb->insert(
            $this->table_name(),
            [
                'token' => wp_generate_password(32, false, false),
                'cf7_form_id' => $data['cf7_form_id'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'],
                'res_date' => $data['res_date'],
                'res_time' => $data['res_time'],
                'guests' => $data['guests'],
                'notes' => $data['notes'],
                'language' => $data['language'],
                'status' => 'pending',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ],
            ['%s','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s']
        );

        return $inserted ? absint($wpdb->insert_id) : 0;
    }

    private function get_reservation($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name()} WHERE id = %d", absint($id)));
    }

    private function get_reservation_by_token($id, $token) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name()} WHERE id = %d AND token = %s",
            absint($id),
            sanitize_text_field($token)
        ));
    }

    private function get_owner_emails() {
        $raw = get_option(self::OPTION_OWNER_EMAILS, get_option('admin_email'));
        $emails = array_filter(array_map('trim', explode(',', $raw)));
        return $emails ?: [get_option('admin_email')];
    }

    private function manager_page_url($reservation = null) {
        $page_id = absint(get_option(self::OPTION_MANAGER_PAGE_ID));
        $base = $page_id ? get_permalink($page_id) : home_url('/reservation-manager/');
        $args = ['an_key' => get_option(self::OPTION_MANAGER_KEY)];
        if ($reservation) {
            $args['an_res_id'] = absint($reservation->id);
            $args['an_token'] = $reservation->token;
        }
        return add_query_arg($args, $base);
    }

    private function email_headers() {
        return ['Content-Type: text/html; charset=UTF-8'];
    }

    private function restaurant_name() {
        $name = trim((string) get_option(self::OPTION_RESTAURANT_NAME, self::RESTAURANT_NAME));
        return $name ?: self::RESTAURANT_NAME;
    }

    private function default_email_templates() {
        return [
            'owner_subject' => '[{restaurant_name}] New table reservation #{reservation_id} ({language})',
            'owner_body' => '<h2>{restaurant_name} - New table reservation</h2><p>A customer has submitted a new table reservation from the <strong>{language}</strong> form.</p><p>Please open the restaurant management page below and tap <strong>Confirm</strong> or <strong>Decline</strong>. The customer will automatically receive the result email in the same language as the submitted form.</p>{details_table}{manager_button}<p><strong>Management link:</strong><br>{manager_link}</p>',

            'customer_received_subject_en' => '{restaurant_name} - Your reservation request has been received - #{reservation_id}',
            'customer_received_body_en' => '<h2>{restaurant_name} - Your reservation request has been received.</h2><p>Thank you, {name}. We have received your table reservation request and our team will review it shortly.</p><p>You will receive another email once your reservation has been confirmed or declined.</p><h3>Reservation details:</h3>{details_table}<p>Best regards,<br>{restaurant_name}</p>',

            'customer_received_subject_de' => '{restaurant_name} - Wir haben Ihre Reservierung erhalten - #{reservation_id}',
            'customer_received_body_de' => '<h2>{restaurant_name} - Wir haben Ihre Reservierungsanfrage erhalten.</h2><p>Vielen Dank, {name}. Wir haben Ihre Anfrage für eine Tischreservierung erhalten. Unser Team wird sie in Kürze prüfen.</p><p>Sie erhalten eine weitere E-Mail, sobald Ihre Reservierung bestätigt oder abgelehnt wurde.</p><h3>Reservierungsdetails:</h3>{details_table}<p>Mit freundlichen Grüßen,<br>{restaurant_name}</p>',

            'confirm_subject_en' => '{restaurant_name} - Your reservation is confirmed - #{reservation_id}',
            'confirm_body_en' => '<h2>{restaurant_name} - Your reservation is confirmed.</h2><p>Good news! Your table reservation has been confirmed. We look forward to welcoming you.</p><h3>Reservation details:</h3>{details_table}<p>Best regards,<br>{restaurant_name}</p>',

            'decline_subject_en' => '{restaurant_name} - Your reservation request was declined - #{reservation_id}',
            'decline_body_en' => '<h2>{restaurant_name} - Your reservation request was declined.</h2><p>We are sorry, but we are unable to accept your reservation request for the selected time. Please contact us or choose another time.</p><h3>Reservation details:</h3>{details_table}<p>Best regards,<br>{restaurant_name}</p>',

            'confirm_subject_de' => '{restaurant_name} - Ihre Reservierung ist bestätigt - #{reservation_id}',
            'confirm_body_de' => '<h2>{restaurant_name} - Ihre Reservierung ist bestätigt.</h2><p>Gute Nachrichten! Ihre Tischreservierung wurde bestätigt. Wir freuen uns darauf, Sie begrüßen zu dürfen.</p><h3>Reservierungsdetails:</h3>{details_table}<p>Mit freundlichen Grüßen,<br>{restaurant_name}</p>',

            'decline_subject_de' => '{restaurant_name} - Ihre Reservierungsanfrage wurde abgelehnt - #{reservation_id}',
            'decline_body_de' => '<h2>{restaurant_name} - Ihre Reservierungsanfrage wurde abgelehnt.</h2><p>Es tut uns leid, aber wir können Ihre Reservierungsanfrage für die gewählte Zeit nicht annehmen. Bitte kontaktieren Sie uns oder wählen Sie eine andere Uhrzeit.</p><h3>Reservierungsdetails:</h3>{details_table}<p>Mit freundlichen Grüßen,<br>{restaurant_name}</p>',
        ];
    }

    private function get_email_templates() {
        $saved = get_option(self::OPTION_EMAIL_TEMPLATES, []);
        return wp_parse_args(is_array($saved) ? $saved : [], $this->default_email_templates());
    }

    private function manager_button_html($r) {
        $url = esc_url($this->manager_page_url($r));
        return '<p style="margin:22px 0"><a href="' . $url . '" style="background:#8B6D47;color:#fff;text-decoration:none;padding:13px 18px;border-radius:10px;font-weight:700;display:inline-block">Open Reservation Manager</a></p>';
    }

    private function replace_email_placeholders($template, $r, $lang = 'en', $for_subject = false) {
        $manager_link = $this->manager_page_url($r);
        $tokens = [
            '{restaurant_name}' => $this->restaurant_name(),
            '{restaurant_phone}' => get_option(self::OPTION_RESTAURANT_PHONE, ''),
            '{restaurant_address}' => get_option(self::OPTION_RESTAURANT_ADDRESS, ''),
            '{reservation_id}' => absint($r->id),
            '{language}' => strtoupper($this->reservation_language($r)),
            '{name}' => $r->customer_name,
            '{phone}' => $r->customer_phone,
            '{email}' => $r->customer_email,
            '{date}' => $r->res_date,
            '{time}' => $r->res_time,
            '{guests}' => $r->guests,
            '{notes}' => $r->notes,
            '{manager_link}' => $for_subject ? $manager_link : '<a href="' . esc_url($manager_link) . '">' . esc_html($manager_link) . '</a>',
            '{manager_button}' => $for_subject ? '' : $this->manager_button_html($r),
            '{details_table}' => $for_subject ? '' : $this->reservation_details_html($r, $lang),
        ];

        $output = strtr((string) $template, $tokens);
        return $for_subject ? wp_strip_all_tags($output) : $output;
    }

    private function email_body_from_template($template, $r, $lang = 'en') {
        $content = $this->replace_email_placeholders($template, $r, $lang, false);
        return '<div style="font-family:Arial,sans-serif;color:#2b241d;line-height:1.6">' . wp_kses_post($content) . '</div>';
    }

    private function reservation_details_html($r, $lang = 'de') {
        $notes = $r->notes ? nl2br(esc_html($r->notes)) : '—';
        $labels = [
            'name'   => 'Name',
            'phone'  => 'Telefon',
            'email'  => 'E-Mail',
            'date'   => 'Datum',
            'time'   => 'Uhrzeit',
            'guests' => 'Gäste',
            'notes'  => 'Notizen'
        ];

        return '
            <table cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:620px;border:1px solid #eee">
                <tr><td style="background:#fbf7f0;width:160px"><strong>' . esc_html($labels['name']) . '</strong></td><td>' . esc_html($r->customer_name) . '</td></tr>
                <tr><td style="background:#fbf7f0"><strong>' . esc_html($labels['phone']) . '</strong></td><td>' . esc_html($r->customer_phone) . '</td></tr>
                <tr><td style="background:#fbf7f0"><strong>' . esc_html($labels['email']) . '</strong></td><td>' . esc_html($r->customer_email) . '</td></tr>
                <tr><td style="background:#fbf7f0"><strong>' . esc_html($labels['date']) . '</strong></td><td>' . esc_html($r->res_date) . '</td></tr>
                <tr><td style="background:#fbf7f0"><strong>' . esc_html($labels['time']) . '</strong></td><td>' . esc_html($r->res_time) . '</td></tr>
                <tr><td style="background:#fbf7f0"><strong>' . esc_html($labels['guests']) . '</strong></td><td>' . esc_html($r->guests) . '</td></tr>
                <tr><td style="background:#fbf7f0"><strong>' . esc_html($labels['notes']) . '</strong></td><td>' . $notes . '</td></tr>
            </table>';
    }

    private function send_owner_new_reservation_email($r) {
        $templates = $this->get_email_templates();
        $subject = $this->replace_email_placeholders($templates['owner_subject'], $r, 'en', true);
        $body = $this->email_body_from_template($templates['owner_body'], $r, 'en');
        return wp_mail($this->get_owner_emails(), $subject, $body, $this->email_headers());
    }

    private function send_customer_received_email($r) {
        $lang = $this->reservation_language($r);
        $templates = $this->get_email_templates();
        $subject_key = $lang === 'de' ? 'customer_received_subject_de' : 'customer_received_subject_en';
        $body_key = $lang === 'de' ? 'customer_received_body_de' : 'customer_received_body_en';

        $subject = $this->replace_email_placeholders($templates[$subject_key], $r, $lang, true);
        $body = $this->email_body_from_template($templates[$body_key], $r, $lang);

        return wp_mail($r->customer_email, $subject, $body, $this->email_headers());
    }

    private function send_customer_decision_email($r) {
        $confirmed = $r->status === 'confirmed';
        $lang = $this->reservation_language($r);
        $templates = $this->get_email_templates();

        if ($confirmed) {
            $subject_key = $lang === 'de' ? 'confirm_subject_de' : 'confirm_subject_en';
            $body_key = $lang === 'de' ? 'confirm_body_de' : 'confirm_body_en';
        } else {
            $subject_key = $lang === 'de' ? 'decline_subject_de' : 'decline_subject_en';
            $body_key = $lang === 'de' ? 'decline_body_de' : 'decline_body_en';
        }

        $subject = $this->replace_email_placeholders($templates[$subject_key], $r, $lang, true);
        $body = $this->email_body_from_template($templates[$body_key], $r, $lang);

        return wp_mail($r->customer_email, $subject, $body, $this->email_headers());
    }

    public function handle_manager_action() {
        if (empty($_POST['an_res_front_action'])) {
            return;
        }

        $key = sanitize_text_field($_POST['an_key'] ?? '');
        if (!$this->valid_manager_key($key)) {
            wp_die('Invalid manager key.');
        }

        $id = absint($_POST['an_res_id'] ?? 0);
        $token = sanitize_text_field($_POST['an_token'] ?? '');
        $action = sanitize_key($_POST['an_res_front_action']);

        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'an_res_front_action_' . $id)) {
            wp_die('Security check failed.');
        }

        $reservation = $this->get_reservation_by_token($id, $token);
        if (!$reservation) {
            wp_die('Reservation not found.');
        }

        if ($action === 'confirm') {
            $this->change_status($reservation->id, 'confirmed');
        } elseif ($action === 'decline') {
            $this->change_status($reservation->id, 'declined');
        } elseif ($action === 'save_edit') {
            $this->update_reservation_details($reservation->id, $_POST);
        }

        $redirect = add_query_arg([
            'an_key' => $key,
            'an_res_id' => $id,
            'an_token' => $token,
            'updated' => $action,
        ], $this->manager_page_url());

        wp_safe_redirect($redirect);
        exit;
    }

    public function handle_admin_action() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission.');
        }
        $id = absint($_GET['reservation_id'] ?? 0);
        $action = sanitize_key($_GET['reservation_action'] ?? '');
        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'an_res_admin_action_' . $id)) {
            wp_die('Security check failed.');
        }

        if ($action === 'confirm') {
            $this->change_status($id, 'confirmed');
        } elseif ($action === 'decline') {
            $this->change_status($id, 'declined');
        }

        wp_safe_redirect(admin_url('admin.php?page=an-reservations&updated=1'));
        exit;
    }

    public function handle_admin_edit() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission.');
        }

        $id = absint($_POST['reservation_id'] ?? 0);
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'an_res_admin_edit_' . $id)) {
            wp_die('Security check failed.');
        }

        $this->update_reservation_details($id, $_POST);
        wp_safe_redirect(admin_url('admin.php?page=an-reservations&updated=edit'));
        exit;
    }

    public function handle_export() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission.');
        }

        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'an_res_export')) {
            wp_die('Security check failed.');
        }

        $date = sanitize_text_field($_GET['res_date'] ?? '');
        $status = sanitize_text_field($_GET['status'] ?? '');
        $reservations = $this->get_reservations($date, $status, 5000);

        $filename = 'an-vegan-reservations-' . date('Ymd-His', current_time('timestamp')) . '.csv';

        nocache_headers();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        echo "\xEF\xBB\xBF";
        fputcsv($out, [
            'ID',
            'Status',
            'Form',
            'Name',
            'Phone',
            'Email',
            'Date',
            'Time',
            'Guests',
            'Notes',
            'Created At',
            'Updated At',
            'Action At',
        ]);

        foreach ($reservations as $r) {
            fputcsv($out, [
                $r->id,
                $this->status_label($r->status),
                strtoupper($this->reservation_language($r)),
                $r->customer_name,
                $r->customer_phone,
                $r->customer_email,
                $r->res_date,
                $r->res_time,
                $r->guests,
                $r->notes,
                $r->created_at,
                $r->updated_at,
                $r->action_at,
            ]);
        }

        fclose($out);
        exit;
    }

    private function valid_manager_key($key) {
        $stored = (string) get_option(self::OPTION_MANAGER_KEY);
        return $stored && hash_equals($stored, (string) $key);
    }

    private function change_status($id, $status) {
        if (!in_array($status, ['confirmed', 'declined'], true)) {
            return false;
        }

        global $wpdb;
        $updated = $wpdb->update(
            $this->table_name(),
            [
                'status' => $status,
                'updated_at' => current_time('mysql'),
                'action_at' => current_time('mysql'),
            ],
            ['id' => absint($id)],
            ['%s','%s','%s'],
            ['%d']
        );

        if ($updated !== false) {
            $reservation = $this->get_reservation($id);
            if ($reservation) {
                $sent = $this->send_customer_decision_email($reservation);
                $wpdb->update(
                    $this->table_name(),
                    [
                        'decision_email_sent' => $sent ? 1 : 0,
                        'updated_at' => current_time('mysql'),
                    ],
                    ['id' => absint($id)],
                    ['%d','%s'],
                    ['%d']
                );
            }
        }

        return $updated !== false;
    }

    private function status_label($status) {
        $labels = [
            'pending' => 'Ausstehend',
            'confirmed' => 'Bestätigt',
            'declined' => 'Abgelehnt',
        ];
        return $labels[$status] ?? $status;
    }

    private function get_reservations($date = '', $status = '', $limit = 200) {
        global $wpdb;
        $where = ['1=1'];
        $params = [];

        if ($date !== '') {
            $where[] = 'res_date = %s';
            $params[] = $date;
        }

        if ($status !== '' && in_array($status, ['pending','confirmed','declined'], true)) {
            $where[] = 'status = %s';
            $params[] = $status;
        }

        $sql = "SELECT * FROM {$this->table_name()} WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC LIMIT %d";
        $params[] = absint($limit);

        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }

    public function reservation_manager_shortcode() {
        $key = sanitize_text_field($_GET['an_key'] ?? '');
        if (!$this->valid_manager_key($key)) {
            return '<div class="an-res-wrap"><div class="an-res-alert error">Ungültiger Management-Link oder fehlender Sicherheitsschlüssel.</div></div>';
        }

        $single_id = absint($_GET['an_res_id'] ?? 0);
        $single_token = sanitize_text_field($_GET['an_token'] ?? '');
        $message = '';

        if (!empty($_GET['updated'])) {
            if (sanitize_key($_GET['updated']) === 'save_edit') {
                $message = '<div class="an-res-alert success">Reservierungsänderungen wurden erfolgreich gespeichert.</div>';
            } else {
                $message = '<div class="an-res-alert success">Status aktualisiert und Ergebnis-E-Mail an den Gast gesendet.</div>';
            }
        }

        ob_start();
        echo '<div class="an-res-wrap">';
        echo '<div class="an-res-top"><h1 class="an-res-title">Reservierungsverwaltung</h1><a class="an-res-btn secondary" href="' . esc_url($this->manager_page_url()) . '">Alle anzeigen</a></div>';
        echo $message;

        if ($single_id && $single_token) {
            $reservation = $this->get_reservation_by_token($single_id, $single_token);
            if ($reservation) {
                echo '<div class="an-res-grid">';
                echo $this->render_reservation_card($reservation, true, $key);
                echo '</div>';
            } else {
                echo '<div class="an-res-alert error">Reservierung nicht gefunden.</div>';
            }
            echo '</div>';
            return ob_get_clean();
        }

        $date = sanitize_text_field($_GET['res_date'] ?? '');
        $status = sanitize_text_field($_GET['status'] ?? '');
        $reservations = $this->get_reservations($date, $status, 300);

        echo '<form class="an-res-filter" method="get">';
        echo '<input type="hidden" name="an_key" value="' . esc_attr($key) . '">';
        echo '<input type="date" name="res_date" value="' . esc_attr($date) . '">';
        echo '<select name="status"><option value="">Alle Status</option>';
        foreach (['pending' => 'Ausstehend', 'confirmed' => 'Bestätigt', 'declined' => 'Abgelehnt'] as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($status, $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select><button type="submit">Filtern</button></form>';

        if (!$reservations) {
            echo '<div class="an-res-empty">Keine passenden Reservierungen gefunden.</div>';
        } else {
            echo '<div class="an-res-grid">';
            foreach ($reservations as $reservation) {
                echo $this->render_reservation_card($reservation, true, $key);
            }
            echo '</div>';
        }

        echo '</div>';
        return ob_get_clean();
    }

    private function time_options_for_lang($lang = 'en') {
        if ($lang === 'de') {
            return ['11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30','19:00','19:30','20:00','20:30','21:00','21:30','22:00'];
        }

        return ['11:00 AM','11:30 AM','12:00 PM','12:30 PM','1:00 PM','1:30 PM','2:00 PM','2:30 PM','3:00 PM','3:30 PM','4:00 PM','4:30 PM','5:00 PM','5:30 PM','6:00 PM','6:30 PM','7:00 PM','7:30 PM','8:00 PM','8:30 PM','9:00 PM','9:30 PM','10:00 PM'];
    }

    private function guest_options_for_lang($lang = 'en') {
        if ($lang === 'de') {
            return ['1 Person','2 Personen','3 Personen','4 Personen','5 Personen','6 Personen','7+ Personen'];
        }

        return ['1 Guest','2 Guests','3 Guests','4 Guests','5 Guests','6 Guests','7+ Guests'];
    }

    private function render_plain_options($options, $selected) {
        $html = '';
        foreach ($options as $option) {
            $html .= '<option value="' . esc_attr($option) . '" ' . selected($selected, $option, false) . '>' . esc_html($option) . '</option>';
        }
        return $html;
    }

    private function min_booking_date() {
        return date('Y-m-d', current_time('timestamp'));
    }

    private function update_reservation_details($id, $data) {
        global $wpdb;

        $res_date = sanitize_text_field($data['edit_res_date'] ?? '');
        $res_time = sanitize_text_field($data['edit_res_time'] ?? '');
        $guests = sanitize_text_field($data['edit_res_guests'] ?? '');
        $notes = sanitize_textarea_field($data['edit_res_notes'] ?? '');

        if (empty($res_date) || empty($res_time) || empty($guests)) {
            return false;
        }

        return $wpdb->update(
            $this->table_name(),
            [
                'res_date' => $res_date,
                'res_time' => $res_time,
                'guests' => $guests,
                'notes' => $notes,
                'updated_at' => current_time('mysql'),
            ],
            ['id' => absint($id)],
            ['%s','%s','%s','%s','%s'],
            ['%d']
        ) !== false;
    }

    private function render_edit_form($r, $front_actions = false, $manager_key = '') {
        $lang = $this->reservation_language($r);
        $time_options = $this->time_options_for_lang($lang);
        $guest_options = $this->guest_options_for_lang($lang);

        $html = '<div class="an-res-edit-form">';
        $html .= '<strong>Reservierung bearbeiten</strong>';

        if ($front_actions) {
            $html .= '<form method="post">';
            $html .= wp_nonce_field('an_res_front_action_' . $r->id, '_wpnonce', true, false);
            $html .= '<input type="hidden" name="an_key" value="' . esc_attr($manager_key) . '">';
            $html .= '<input type="hidden" name="an_res_id" value="' . absint($r->id) . '">';
            $html .= '<input type="hidden" name="an_token" value="' . esc_attr($r->token) . '">';
            $html .= '<input type="hidden" name="an_res_front_action" value="save_edit">';
        } else {
            $html .= '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
            $html .= wp_nonce_field('an_res_admin_edit_' . $r->id, '_wpnonce', true, false);
            $html .= '<input type="hidden" name="action" value="an_res_edit">';
            $html .= '<input type="hidden" name="reservation_id" value="' . absint($r->id) . '">';
        }

        $html .= '<div class="an-res-edit-row">';
        $html .= '<div><label>Datum</label><input type="date" name="edit_res_date" min="' . esc_attr($this->min_booking_date()) . '" value="' . esc_attr($r->res_date) . '" required></div>';
        $html .= '<div><label>Uhrzeit</label><select name="edit_res_time" required>' . $this->render_plain_options($time_options, $r->res_time) . '</select></div>';
        $html .= '</div>';
        $html .= '<label>Gästeanzahl</label><select name="edit_res_guests" required>' . $this->render_plain_options($guest_options, $r->guests) . '</select>';
        $html .= '<label>Notizen</label><textarea name="edit_res_notes">' . esc_textarea($r->notes) . '</textarea>';
        $html .= '<div class="an-res-actions"><button class="an-res-btn confirm" type="submit">Änderungen speichern</button></div>';
        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }

    private function render_reservation_card($r, $front_actions = false, $manager_key = '') {
        $status = esc_attr($r->status);
        $html = '<div class="an-res-card ' . $status . '">';
        $html .= '<h3>#' . absint($r->id) . ' - ' . esc_html($r->customer_name) . '</h3>';
        $html .= '<span class="an-res-status ' . $status . '">' . esc_html($this->status_label($r->status)) . '</span>';
        $html .= '<div class="an-res-meta">';
        $html .= '<div><strong>Datum</strong><span>' . esc_html($r->res_date) . '</span></div>';
        $html .= '<div><strong>Uhrzeit</strong><span>' . esc_html($r->res_time) . '</span></div>';
        $html .= '<div><strong>Gäste</strong><span>' . esc_html($r->guests) . '</span></div>';
        $html .= '<div><strong>Telefon</strong><span>' . esc_html($r->customer_phone) . '</span></div>';
        $html .= '<div><strong>E-Mail</strong><span>' . esc_html($r->customer_email) . '</span></div>';
        $html .= '<div><strong>Formular</strong><span>' . esc_html(strtoupper($this->reservation_language($r))) . '</span></div>';
        $html .= '<div><strong>Sendedatum</strong><span>' . esc_html($r->created_at) . '</span></div>';
        $html .= '</div>';
        if (!empty($r->notes)) {
            $html .= '<div class="an-res-note"><strong>Notizen:</strong><br>' . nl2br(esc_html($r->notes)) . '</div>';
        }

        $mail_subject = rawurlencode($this->restaurant_name() . ' - Reservation #' . absint($r->id));
        $mail_body = rawurlencode("Hello " . $r->customer_name . ",

");
        $mail_url = 'mailto:' . sanitize_email($r->customer_email) . '?subject=' . $mail_subject . '&body=' . $mail_body;
        $html .= '<div class="an-res-actions"><button class="an-res-btn edit" type="button" onclick="this.closest(\'.an-res-card\').classList.toggle(\'editing\')">Bearbeiten</button><a class="an-res-btn mail" href="' . esc_url($mail_url) . '">E-Mail senden</a></div>';
        $html .= $this->render_edit_form($r, $front_actions, $manager_key);

        if ($r->status === 'pending') {
            $html .= '<div class="an-res-actions">';
            if ($front_actions) {
                $html .= '<form method="post">';
                $html .= wp_nonce_field('an_res_front_action_' . $r->id, '_wpnonce', true, false);
                $html .= '<input type="hidden" name="an_key" value="' . esc_attr($manager_key) . '">';
                $html .= '<input type="hidden" name="an_res_id" value="' . absint($r->id) . '">';
                $html .= '<input type="hidden" name="an_token" value="' . esc_attr($r->token) . '">';
                $html .= '<button class="an-res-btn confirm" name="an_res_front_action" value="confirm" type="submit">Bestätigen</button>';
                $html .= '</form>';
                $html .= '<form method="post">';
                $html .= wp_nonce_field('an_res_front_action_' . $r->id, '_wpnonce', true, false);
                $html .= '<input type="hidden" name="an_key" value="' . esc_attr($manager_key) . '">';
                $html .= '<input type="hidden" name="an_res_id" value="' . absint($r->id) . '">';
                $html .= '<input type="hidden" name="an_token" value="' . esc_attr($r->token) . '">';
                $html .= '<button class="an-res-btn decline" name="an_res_front_action" value="decline" type="submit">Ablehnen</button>';
                $html .= '</form>';
            } else {
                $confirm_url = wp_nonce_url(admin_url('admin-post.php?action=an_res_action&reservation_action=confirm&reservation_id=' . absint($r->id)), 'an_res_admin_action_' . $r->id);
                $decline_url = wp_nonce_url(admin_url('admin-post.php?action=an_res_action&reservation_action=decline&reservation_id=' . absint($r->id)), 'an_res_admin_action_' . $r->id);
                $html .= '<a class="an-res-btn confirm" href="' . esc_url($confirm_url) . '">Bestätigen</a>';
                $html .= '<a class="an-res-btn decline" href="' . esc_url($decline_url) . '">Ablehnen</a>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    public function admin_reservations_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $date = sanitize_text_field($_GET['res_date'] ?? '');
        $status = sanitize_text_field($_GET['status'] ?? '');
        $reservations = $this->get_reservations($date, $status, 500);
        $export_url = wp_nonce_url(
            add_query_arg([
                'action' => 'an_res_export',
                'res_date' => $date,
                'status' => $status,
            ], admin_url('admin-post.php')),
            'an_res_export'
        );

        echo '<div class="wrap an-res-wrap">';
        echo '<div class="an-res-top"><h1 class="an-res-title">Reservierungsliste</h1><a class="an-res-btn secondary" target="_blank" href="' . esc_url($this->manager_page_url()) . '">Mobile Verwaltung öffnen</a></div>';

        if (!empty($_GET['updated'])) {
            if (sanitize_key($_GET['updated']) === 'edit') {
                echo '<div class="an-res-alert success">Reservierungsänderungen wurden erfolgreich gespeichert.</div>';
            } else {
                echo '<div class="an-res-alert success">Status aktualisiert und Ergebnis-E-Mail an den Gast gesendet.</div>';
            }
        }

        echo '<form class="an-res-filter" method="get">';
        echo '<input type="hidden" name="page" value="an-reservations">';
        echo '<input type="date" name="res_date" value="' . esc_attr($date) . '">';
        echo '<select name="status"><option value="">Alle Status</option>';
        foreach (['pending' => 'Ausstehend', 'confirmed' => 'Bestätigt', 'declined' => 'Abgelehnt'] as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($status, $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select><button type="submit">Filtern</button><a class="an-res-btn secondary" href="' . esc_url($export_url) . '">Excel exportieren</a></form>';

        if (!$reservations) {
            echo '<div class="an-res-empty">Keine Reservierungen vorhanden.</div>';
        } else {
            echo '<div class="an-res-grid">';
            foreach ($reservations as $reservation) {
                echo $this->render_reservation_card($reservation, false);
            }
            echo '</div>';
        }
        echo '</div>';
    }

    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $manager_url = $this->manager_page_url();
        $templates = $this->get_email_templates();
        $template_labels = [
            'owner_subject' => 'E-Mail an Inhaber - Betreff',
            'owner_body' => 'E-Mail an Inhaber - Inhalt',
            'customer_received_subject_en' => 'Gast EN - Betreff Anfrage erhalten',
            'customer_received_body_en' => 'Gast EN - Inhalt Anfrage erhalten',
            'customer_received_subject_de' => 'Gast DE - Betreff Anfrage erhalten',
            'customer_received_body_de' => 'Gast DE - Inhalt Anfrage erhalten',
            'confirm_subject_en' => 'Gast EN - Betreff Bestätigung',
            'confirm_body_en' => 'Gast EN - Inhalt Bestätigung',
            'decline_subject_en' => 'Gast EN - Betreff Ablehnung',
            'decline_body_en' => 'Gast EN - Inhalt Ablehnung',
            'confirm_subject_de' => 'Gast DE - Betreff Bestätigung',
            'confirm_body_de' => 'Gast DE - Inhalt Bestätigung',
            'decline_subject_de' => 'Gast DE - Betreff Ablehnung',
            'decline_body_de' => 'Gast DE - Inhalt Ablehnung',
        ];

        echo '<div class="wrap an-res-wrap">';
        echo '<h1 class="an-res-title">Reservierungseinstellungen</h1>';
        echo '<form method="post" action="options.php" style="max-width:980px;background:#fff;border:1px solid #eadfce;border-radius:16px;padding:18px;margin-top:16px">';
        settings_fields('an_res_settings_group');

        echo '<h2>Allgemeine Informationen</h2>';
        echo '<table class="form-table">';
        echo '<tr><th scope="row">Restaurantname</th><td><input type="text" name="' . esc_attr(self::OPTION_RESTAURANT_NAME) . '" value="' . esc_attr($this->restaurant_name()) . '" style="width:100%"></td></tr>';
        echo '<tr><th scope="row">Hotline / Telefon</th><td><input type="text" name="' . esc_attr(self::OPTION_RESTAURANT_PHONE) . '" value="' . esc_attr(get_option(self::OPTION_RESTAURANT_PHONE, '')) . '" style="width:100%"></td></tr>';
        echo '<tr><th scope="row">Adresse</th><td><input type="text" name="' . esc_attr(self::OPTION_RESTAURANT_ADDRESS) . '" value="' . esc_attr(get_option(self::OPTION_RESTAURANT_ADDRESS, '')) . '" style="width:100%"></td></tr>';
        echo '<tr><th scope="row">E-Mail-Empfänger für Inhaber-Benachrichtigungen</th><td><textarea name="' . esc_attr(self::OPTION_OWNER_EMAILS) . '" rows="3" style="width:100%" placeholder="owner@email.com, manager@email.com">' . esc_textarea(get_option(self::OPTION_OWNER_EMAILS)) . '</textarea><p class="description">Geben Sie mehrere E-Mails durch Kommas oder Zeilenumbrüche getrennt ein.</p></td></tr>';
        echo '<tr><th scope="row">Contact Form 7 Formular-ID</th><td><input type="number" name="' . esc_attr(self::OPTION_CF7_FORM_ID) . '" value="' . esc_attr(absint(get_option(self::OPTION_CF7_FORM_ID))) . '" min="0"><p class="description">Empfohlen: 0. Das Plugin erkennt automatisch das EN/DE-Formular anhand der Felder: your-name, your-phone, your-email, res-date, res-time, res-guests.</p></td></tr>';
        echo '</table>';

        echo '<h2>E-Mail-Inhalte anpassen</h2>';
        echo '<div class="an-res-alert"><strong>Verfügbare Platzhalter:</strong><br><code>{restaurant_name}</code> <code>{restaurant_phone}</code> <code>{restaurant_address}</code> <code>{reservation_id}</code> <code>{language}</code> <code>{name}</code> <code>{phone}</code> <code>{email}</code> <code>{date}</code> <code>{time}</code> <code>{guests}</code> <code>{notes}</code> <code>{details_table}</code> <code>{manager_button}</code> <code>{manager_link}</code></div>';
        echo '<table class="form-table">';
        foreach ($template_labels as $key => $label) {
            $value = $templates[$key] ?? '';
            echo '<tr><th scope="row">' . esc_html($label) . '</th><td>';
            if (strpos($key, 'subject') !== false) {
                echo '<input type="text" name="' . esc_attr(self::OPTION_EMAIL_TEMPLATES) . '[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" style="width:100%">';
            } else {
                echo '<textarea name="' . esc_attr(self::OPTION_EMAIL_TEMPLATES) . '[' . esc_attr($key) . ']" rows="6" style="width:100%;font-family:monospace">' . esc_textarea($value) . '</textarea>';
            }
            echo '</td></tr>';
        }
        echo '</table>';

        submit_button('Einstellungen speichern');
        echo '</form>';
        echo '<div class="an-res-alert" style="max-width:980px"><strong>Link zur mobilen Verwaltung:</strong><br><a href="' . esc_url($manager_url) . '" target="_blank">' . esc_html($manager_url) . '</a><br><small>Dieser Link enthält einen Sicherheitsschlüssel. Bitte nicht öffentlich teilen.</small></div>';
        echo '</div>';
    }
}

new AN_Table_Reservation_Manager();