<?php
namespace ThaiTop\Register;

use ThaiTop\Register\Admin\Admin_Settings;

class Form_Handler {
    private $recaptcha;
    private $db_manager;

    public function __construct() {
        add_shortcode('thaitop_register', [$this, 'render_form']);
        add_action('init', [$this, 'process_registration']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_form_assets']); // เพิ่มบรรทัดนี้
        $this->recaptcha = new Recaptcha();
        $this->db_manager = new DB_Manager();
    }

    public function render_form() {
        if (is_user_logged_in()) {
            return __('You are already logged in', 'thaitop-register-form');
        }

        // Ensure CSS is loaded
        if (wp_style_is('thaitop-register-style', 'registered')) {
            wp_enqueue_style('thaitop-register-style');
        }

        wp_enqueue_script('thaitop-register-main');

        ob_start();
        include THAITOP_REGISTER_PLUGIN_DIR . 'templates/registration-form.php';
        return ob_get_clean();
    }

    public function render_custom_fields() {
        $custom_fields = $this->db_manager->get_custom_fields();
        foreach ($custom_fields as $field) {
            ?>
            <div class="form-group">
                <label for="<?php echo esc_attr($field->field_name); ?>">
                    <?php echo esc_html($field->field_label); ?>
                    <?php echo $field->required ? ' *' : ''; ?>
                </label>
                <input type="<?php echo esc_attr($field->field_type); ?>"
                       name="<?php echo esc_attr($field->field_name); ?>"
                       id="<?php echo esc_attr($field->field_name); ?>"
                       <?php echo $field->required ? 'required' : ''; ?> />
            </div>
            <?php
        }
    }

    public function process_registration() {
        if (!isset($_POST['thaitop_register_submit'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['_wpnonce'], 'thaitop_registration_nonce')) {
            wp_die(__('Invalid operation', 'thaitop-register-form'));
        }

        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);

        $errors = new \WP_Error();

        // Validation
        if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
            $errors->add('field', __('Please fill in all required fields', 'thaitop-register-form'));
        }

        if (username_exists($username)) {
            $errors->add('username_exists', __('This username is already taken', 'thaitop-register-form'));
        }

        if (email_exists($email)) {
            $errors->add('email_exists', __('This email is already registered', 'thaitop-register-form'));
        }

        // Verify reCAPTCHA only if enabled
        if ($this->recaptcha->is_enabled()) {
            $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
            if (!$this->recaptcha->verify_response($recaptcha_response)) {
                $errors->add('recaptcha_error', __('Please verify that you are not a robot', 'thaitop-register-form'));
            }
        }

        // Process errors or create user
        if ($errors->has_errors()) {
            foreach ($errors->get_error_messages() as $error) {
                echo '<div class="thaitop-error">' . esc_html($error) . '</div>';
            }
            return;
        }

        $user_id = wp_create_user($username, $password, $email);
        if (!is_wp_error($user_id)) {
            // Update user meta
            update_user_meta($user_id, 'first_name', $first_name);
            update_user_meta($user_id, 'last_name', $last_name);
            
            // Save custom fields
            $custom_fields = $this->db_manager->get_custom_fields();
            foreach ($custom_fields as $field) {
                if (isset($_POST[$field->field_name])) {
                    $value = sanitize_text_field($_POST[$field->field_name]);
                    update_user_meta($user_id, $field->meta_key, $value);
                }
            }

            $this->login_new_user($user_id);
        }
    }

    public function enqueue_form_assets() {
        $version = THAITOP_REGISTER_VERSION . '.' . time(); // เพิ่ม timestamp เพื่อป้องกัน cache
        
        wp_register_style(
            'thaitop-form-style',
            THAITOP_REGISTER_PLUGIN_URL . 'assets/css/style.css',
            [],
            $version
        );

        $admin_settings = new Admin_Settings();
        $current_scheme = get_option('thaitop_color_scheme', 'default');
        $schemes = $admin_settings->get_color_schemes();

        // หากเป็นชุดสีที่ไม่ใช่ custom ให้ใช้ค่าจาก $schemes
        if (isset($schemes[$current_scheme]) && $current_scheme !== 'custom') {
            $colors = $schemes[$current_scheme]['colors'];
        } else {
            // หากเป็น custom หรือไม่มีอยู่ใน $schemes ให้ใช้ get_option
            $keys = ['form_bg','form_text','input_bg','input_text','input_border','input_focus_border','button_bg_start','button_bg_end','button_text','label_text'];
            $colors = [];
            foreach ($keys as $k) {
                $colors[$k] = get_option("thaitop_{$k}", '#000000');
            }
        }

        // สร้าง inline CSS
        $inline_css = "
            .thaitop-form {
                background-color: {$colors['form_bg']};
                color: {$colors['form_text']};
            }
            .thaitop-form input {
                background-color: {$colors['input_bg']};
                color: {$colors['input_text']};
                border-color: {$colors['input_border']};
            }
            .thaitop-form input:focus {
                border-color: {$colors['input_focus_border']};
                box-shadow: 0 0 0 1px {$colors['input_focus_border']};
            }
            .thaitop-form button {
                background: linear-gradient(to bottom, {$colors['button_bg_start']}, {$colors['button_bg_end']});
                color: {$colors['button_text']};
            }
            .thaitop-form label {
                color: {$colors['label_text']};
            }
        ";

        wp_add_inline_style('thaitop-form-style', $inline_css);
        
        if (has_shortcode(get_post()->post_content, 'thaitop_register')) {
            wp_enqueue_style('thaitop-form-style');
        }
    }

    // Helper function to convert hex to rgba
    private function hex2rgba($color, $opacity = false) {
        $default = 'rgb(0,0,0)';
        
        if(empty($color))
            return $default; 
        
        if ($color[0] == '#' ) {
            $color = substr($color, 1);
        }
        
        if (strlen($color) == 6) {
            $hex = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
        } elseif (strlen($color) == 3) {
            $hex = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
        } else {
            return $default;
        }
        
        $rgb = array_map('hexdec', $hex);
        
        if($opacity){
            return 'rgba('.implode(",",$rgb).','.$opacity.')';
        } else {
            return 'rgb('.implode(",",$rgb).')';
        }
    }

    // Helper function to adjust color brightness
    private function adjust_brightness($hex, $steps) {
        $hex = ltrim($hex, '#');
        
        $r = max(0, min(255, hexdec(substr($hex, 0, 2)) + $steps));
        $g = max(0, min(255, hexdec(substr($hex, 2, 2)) + $steps));
        $b = max(0, min(255, hexdec(substr($hex, 4, 2)) + $steps));
        
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

    public function login_new_user($user_id) {
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
        $redirect_url = get_option('thaitop_redirect_after_login', '');
        if (!empty($redirect_url)) {
            wp_safe_redirect($redirect_url);
            exit;
        }
        // Default redirect if empty
        wp_redirect(home_url());
        exit;
    }
}
