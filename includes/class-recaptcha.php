<?php
namespace ThaiTop\Register;

class Recaptcha {
    public function __construct() {
        add_action('thaitop_register_form_after_fields', [$this, 'add_recaptcha_field']);
    }

    public function is_enabled() {
        return get_option('thaitop_recaptcha_enabled', false) && 
               !empty(get_option('thaitop_recaptcha_site_key')) && 
               !empty(get_option('thaitop_recaptcha_secret_key'));
    }

    public function add_recaptcha_field() {
        if (!$this->is_enabled()) {
            return;
        }

        $site_key = get_option('thaitop_recaptcha_site_key');
        wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js');
        echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($site_key) . '"></div>';
    }

    public function verify_response($response = '') {
        if (!$this->is_enabled()) {
            return true;
        }

        if (empty($response)) {
            return false;
        }

        $secret_key = get_option('thaitop_recaptcha_secret_key');
        if (empty($secret_key)) {
            return true;
        }

        $verify = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $secret_key,
                'response' => $response
            ]
        ]);

        if (is_wp_error($verify)) {
            return false;
        }

        $verify_body = json_decode(wp_remote_retrieve_body($verify));
        return $verify_body->success;
    }
}
