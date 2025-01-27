<?php
namespace ThaiTop\Register\Admin;

class Admin_Settings {
    private $db_manager;

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_init', [$this, 'register_settings']);
        $this->db_manager = new \ThaiTop\Register\DB_Manager();
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function add_menu_page() {
        $hook = add_menu_page(
            __('Register Form Settings', 'thaitop-register-form'), // Page title
            __('Register Form', 'thaitop-register-form'),         // Menu title
            'manage_options',                                     // Capability
            'thaitop-register',                                  // Menu slug
            [$this, 'render_settings_page'],                     // Callback function
            'dashicons-id-alt',                                  // Icon
            30                                                   // Position
        );
        add_submenu_page(
            'thaitop-register',
            __('Custom Fields', 'thaitop-register-form'),
            __('Custom Fields', 'thaitop-register-form'),
            'manage_options',
            'thaitop-register-fields',
            [$this, 'render_fields_page']
        );
    }

    public function register_settings() {
        // Add options if they don't exist
        if (false === get_option('thaitop_recaptcha_site_key')) {
            add_option('thaitop_recaptcha_site_key', '');
        }
        if (false === get_option('thaitop_recaptcha_secret_key')) {
            add_option('thaitop_recaptcha_secret_key', '');
        }

        // Add reCAPTCHA enable option
        if (false === get_option('thaitop_recaptcha_enabled')) {
            add_option('thaitop_recaptcha_enabled', false);
        }

        // Add color options if they don't exist
        if (false === get_option('thaitop_form_primary_color')) {
            add_option('thaitop_form_primary_color', '#4CAF50');
        }
        if (false === get_option('thaitop_form_secondary_color')) {
            add_option('thaitop_form_secondary_color', '#45a049');
        }

        // Register settings
        register_setting('thaitop_options_group', 'thaitop_recaptcha_site_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        register_setting('thaitop_options_group', 'thaitop_recaptcha_secret_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        register_setting('thaitop_options_group', 'thaitop_recaptcha_enabled', [
            'type' => 'boolean',
            'sanitize_callback' => function($value) {
                return (bool) $value;
            },
        ]);
        register_setting('thaitop_options_group', 'thaitop_form_primary_color', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);
        register_setting('thaitop_options_group', 'thaitop_form_secondary_color', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);

        // Add new color options
        $default_colors = [
            'form_bg' => '#ffffff',
            'form_text' => '#333333',
            'input_bg' => '#f8f9fa',
            'input_text' => '#333333',
            'input_border' => '#e0e0e0',
            'input_focus_border' => '#4CAF50',
            'button_bg_start' => '#4CAF50',
            'button_bg_end' => '#45a049',
            'button_text' => '#ffffff',
            'label_text' => '#333333',
            'error_bg' => '#fde8e8',
            'error_text' => '#dc3545',
            'error_border' => '#f5c2c7'
        ];

        foreach ($default_colors as $key => $default_value) {
            if (false === get_option('thaitop_' . $key)) {
                add_option('thaitop_' . $key, $default_value);
            }
            register_setting('thaitop_options_group', 'thaitop_' . $key, [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_hex_color'
            ]);
        }

        // Add color scheme option
        if (false === get_option('thaitop_color_scheme')) {
            add_option('thaitop_color_scheme', 'default');
        }
        
        register_setting('thaitop_options_group', 'thaitop_color_scheme', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        // Add redirect after login option
        if (false === get_option('thaitop_redirect_after_login')) {
            add_option('thaitop_redirect_after_login', '');
        }
        register_setting('thaitop_options_group', 'thaitop_redirect_after_login', [
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
        ]);
    }

    // Add function to manage color schemes
    public function get_color_schemes() {
        return [
            'default' => [
                'name' => __('Green (Default)', 'thaitop-register-form'),
                'colors' => [
                    'form_bg' => '#ffffff',
                    'form_text' => '#333333',
                    'input_bg' => '#f8f9fa',
                    'input_text' => '#333333',
                    'input_border' => '#e0e0e0',
                    'input_focus_border' => '#4CAF50',
                    'button_bg_start' => '#4CAF50',
                    'button_bg_end' => '#45a049',
                    'button_text' => '#ffffff',
                    'label_text' => '#333333',
                    'error_bg' => '#fde8e8',
                    'error_text' => '#dc3545',
                    'error_border' => '#f5c2c7'
                ]
            ],
            'blue' => [
                'name' => __('Blue', 'thaitop-register-form'),
                'colors' => [
                    'form_bg' => '#ffffff',
                    'form_text' => '#333333',
                    'input_bg' => '#f8f9fa',
                    'input_text' => '#333333',
                    'input_border' => '#e0e0e0',
                    'input_focus_border' => '#2196F3',
                    'button_bg_start' => '#2196F3',
                    'button_bg_end' => '#1976D2',
                    'button_text' => '#ffffff',
                    'label_text' => '#333333',
                    'error_bg' => '#fde8e8',
                    'error_text' => '#dc3545',
                    'error_border' => '#f5c2c7'
                ]
            ],
            'purple' => [
                'name' => __('Purple', 'thaitop-register-form'),
                'colors' => [
                    'form_bg' => '#ffffff',
                    'form_text' => '#333333',
                    'input_bg' => '#f8f9fa',
                    'input_text' => '#333333',
                    'input_border' => '#e0e0e0',
                    'input_focus_border' => '#9C27B0',
                    'button_bg_start' => '#9C27B0',
                    'button_bg_end' => '#7B1FA2',
                    'button_text' => '#ffffff',
                    'label_text' => '#333333',
                    'error_bg' => '#fde8e8',
                    'error_text' => '#dc3545',
                    'error_border' => '#f5c2c7'
                ]
            ],
            'dark' => [
                'name' => __('Dark', 'thaitop-register-form'),
                'colors' => [
                    'form_bg' => '#2c3338',
                    'form_text' => '#ffffff',
                    'input_bg' => '#1d2327',
                    'input_text' => '#ffffff',
                    'input_border' => '#3c434a',
                    'input_focus_border' => '#00a0d2',
                    'button_bg_start' => '#00a0d2',
                    'button_bg_end' => '#008db9',
                    'button_text' => '#ffffff',
                    'label_text' => '#ffffff',
                    'error_bg' => '#fde8e8',
                    'error_text' => '#dc3545',
                    'error_border' => '#f5c2c7'
                ]
            ],
            'custom' => [
                'name' => __('Custom', 'thaitop-register-form'),
                'colors' => [] // Use user-defined values
            ]
        ];
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'thaitop-register') !== false) {
            wp_enqueue_style(
                'thaitop-admin-style',
                THAITOP_REGISTER_PLUGIN_URL . 'assets/css/admin-style.css',
                [],
                THAITOP_REGISTER_VERSION
            );

            wp_enqueue_script(
                'thaitop-admin-script',
                THAITOP_REGISTER_PLUGIN_URL . 'assets/js/admin.js',
                ['jquery'],
                THAITOP_REGISTER_VERSION,
                true
            );
            wp_enqueue_style(
                'thaitop-admin-style',
                THAITOP_REGISTER_PLUGIN_URL . 'assets/css/admin-style.css',
                [],
                THAITOP_REGISTER_VERSION
            );

            wp_enqueue_script(
                'thaitop-admin-script',
                THAITOP_REGISTER_PLUGIN_URL . 'assets/js/admin.js',
                ['jquery'],
                THAITOP_REGISTER_VERSION,
                true
            );

            wp_localize_script('thaitop-admin-script', 'thaitopAdminData', [
                'nonce' => wp_create_nonce('thaitop_custom_field_action'),
                'ajaxurl' => admin_url('admin-ajax.php') // Add ajaxurl
            ]);

            // Add jQuery UI
            wp_enqueue_script('jquery-ui-sortable');

            // Add Dashicons
            wp_enqueue_style('dashicons');
        }
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php settings_errors(); ?>
            
            <div class="card">
                <h3><?php esc_html_e('Instructions', 'thaitop-register-form'); ?></h3>
                <p><?php esc_html_e('1. Place the following shortcode in the page or post where you want to display the registration form:', 'thaitop-register-form'); ?></p>
                <code style="background: #f0f0f1; padding: 5px 10px; display: inline-block; margin: 10px 0;">[thaitop_register]</code>
                
                <h4><?php esc_html_e('reCAPTCHA Setup', 'thaitop-register-form'); ?></h4>
                <p><?php esc_html_e('2. Register and get your reCAPTCHA keys at:', 'thaitop-register-form'); ?> 
                    <a href="https://www.google.com/recaptcha/admin" target="_blank">https://www.google.com/recaptcha/admin</a>
                </p>
                <p><?php esc_html_e('3. Choose reCAPTCHA v2 ("I\'m not a robot")', 'thaitop-register-form'); ?></p>
                <p><?php esc_html_e('4. Add your domain to the reCAPTCHA settings', 'thaitop-register-form'); ?></p>
                <p><?php esc_html_e('5. Copy Site Key and Secret Key into the fields below', 'thaitop-register-form'); ?></p>
            </div>

            <div class="card" style="margin-top: 20px;">
                <h3><?php esc_html_e('Features', 'thaitop-register-form'); ?></h3>
                <ul style="list-style: disc inside;">
                    <li><?php esc_html_e('Register new members', 'thaitop-register-form'); ?></li>
                    <li><?php esc_html_e('Validate data', 'thaitop-register-form'); ?></li>
                    <li><?php esc_html_e('Prevent spam with reCAPTCHA', 'thaitop-register-form'); ?></li>
                    <li><?php esc_html_e('Auto-login after registration', 'thaitop-register-form'); ?></li>
                </ul>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields('thaitop_options_group');
                do_settings_sections('thaitop-settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('Enable reCAPTCHA', 'thaitop-register-form'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="thaitop_recaptcha_enabled" 
                                    value="1" <?php checked(get_option('thaitop_recaptcha_enabled')); ?>>
                                <?php esc_html_e('Enable spam protection with reCAPTCHA', 'thaitop-register-form'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('When enabled, users must verify they are not a robot before registering', 'thaitop-register-form'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('reCAPTCHA Site Key', 'thaitop-register-form'); ?></th>
                        <td>
                            <input type="text" name="thaitop_recaptcha_site_key" 
                                value="<?php echo esc_attr(get_option('thaitop_recaptcha_site_key')); ?>" 
                                class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Key for displaying reCAPTCHA on the site', 'thaitop-register-form'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('reCAPTCHA Secret Key', 'thaitop-register-form'); ?></th>
                        <td>
                            <input type="text" name="thaitop_recaptcha_secret_key" 
                                value="<?php echo esc_attr(get_option('thaitop_recaptcha_secret_key')); ?>" 
                                class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Secret key for verifying reCAPTCHA', 'thaitop-register-form'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('After Login Redirect URL', 'thaitop-register-form'); ?></th>
                        <td>
                            <input type="text" name="thaitop_redirect_after_login"
                                value="<?php echo esc_attr(get_option('thaitop_redirect_after_login')); ?>"
                                class="regular-text" />
                            <p class="description">
                                <?php esc_html_e('Enter a custom URL to redirect users after successful auto-login.', 'thaitop-register-form'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Color Scheme Settings -->
                <h3><?php esc_html_e('Color Settings', 'thaitop-register-form'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('Color Scheme', 'thaitop-register-form'); ?></th>
                        <td>
                            <select name="thaitop_color_scheme" id="color-scheme-select">
                                <?php foreach ($this->get_color_schemes() as $key => $scheme): ?>
                                    <option value="<?php echo esc_attr($key); ?>" 
                                        <?php selected(get_option('thaitop_color_scheme'), $key); ?>>
                                        <?php echo esc_html($scheme['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e('Choose a pre-made or custom color scheme', 'thaitop-register-form'); ?></p>
                        </td>
                    </tr>
                </table>

                <!-- Custom Colors Section -->
                <div id="custom-colors" class="custom-colors-section" style="display: <?php echo get_option('thaitop_color_scheme') === 'custom' ? 'block' : 'none'; ?>">
                    <h3><?php esc_html_e('Set Custom Colors', 'thaitop-register-form'); ?></h3>
                    <?php $this->render_color_fields(); ?>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function render_fields_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle form submission
        if (isset($_POST['add_custom_field'])) {
            check_admin_referer('thaitop_custom_field_nonce');
            
            // Validate required fields
            $required_fields = ['field_label', 'field_name', 'field_type', 'meta_key'];
            $has_errors = false;
            
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    add_settings_error(
                        'thaitop_messages',
                        'thaitop_field_error',
                        sprintf(__('Field "%s" is required.', 'thaitop-register-form'), $field),
                        'error'
                    );
                    $has_errors = true;
                }
            }
            
            if (!$has_errors) {
                $result = $this->db_manager->add_field([
                    'field_label' => sanitize_text_field($_POST['field_label']),
                    'field_name' => sanitize_key($_POST['field_name']),
                    'field_type' => sanitize_key($_POST['field_type']),
                    'meta_key' => sanitize_key($_POST['meta_key']),
                    'required' => isset($_POST['required']) ? 1 : 0
                ]);

                if ($result) {
                    add_settings_error(
                        'thaitop_messages',
                        'thaitop_field_added',
                        __('Field added successfully.', 'thaitop-register-form'),
                        'updated'
                    );
                } else {
                    add_settings_error(
                        'thaitop_messages',
                        'thaitop_field_error',
                        __('Database error while adding field.', 'thaitop-register-form'),
                        'error'
                    );
                }
            }
        }

        // Show admin notices
        settings_errors('thaitop_messages');

        $custom_fields = $this->db_manager->get_custom_fields();
        $meta_keys = $this->get_available_meta_keys();
        ?>
        <div class="wrap thaitop-admin-page">
            <h1 class="wp-heading-inline"><?php esc_html_e('Custom Registration Fields', 'thaitop-register-form'); ?></h1>

            <div class="thaitop-admin-columns">
                <div class="thaitop-main-column">
                    <!-- Form for adding new field -->
                    <div class="card thaitop-field-form">
                        <h2><?php esc_html_e('Add New Field', 'thaitop-register-form'); ?></h2>
                        <form method="post">
                            <?php wp_nonce_field('thaitop_custom_field_nonce'); ?>
                            <table class="form-table">
                                <tr>
                                    <th><label for="field_label"><?php esc_html_e('Field Label', 'thaitop-register-form'); ?></label></th>
                                    <td><input type="text" name="field_label" id="field_label" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th><label for="field_name"><?php esc_html_e('Field Name', 'thaitop-register-form'); ?></label></th>
                                    <td><input type="text" name="field_name" id="field_name" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th><label for="field_type"><?php esc_html_e('Field Type', 'thaitop-register-form'); ?></label></th>
                                    <td>
                                        <select name="field_type" id="field_type" required>
                                            <option value="text"><?php esc_html_e('Text', 'thaitop-register-form'); ?></option>
                                            <option value="email"><?php esc_html_e('Email', 'thaitop-register-form'); ?></option>
                                            <option value="tel"><?php esc_html_e('Phone', 'thaitop-register-form'); ?></option>
                                            <option value="date"><?php esc_html_e('Date', 'thaitop-register-form'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="meta_key"><?php esc_html_e('Meta Key', 'thaitop-register-form'); ?></label></th>
                                    <td>
                                        <input type="text" name="meta_key" id="meta_key" class="regular-text" required>
                                        <p class="description"><?php esc_html_e('The key that will be used to store this field in user meta', 'thaitop-register-form'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="required"><?php esc_html_e('Required', 'thaitop-register-form'); ?></label></th>
                                    <td><input type="checkbox" name="required" id="required"></td>
                                </tr>
                            </table>
                            <?php submit_button(__('Add Field', 'thaitop-register-form'), 'primary', 'add_custom_field'); ?>
                        </form>
                    </div>

                    <!-- Existing fields table -->
                    <div class="card">
                        <h2><?php esc_html_e('Existing Fields', 'thaitop-register-form'); ?></h2>
                        <table class="thaitop-fields-table wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th width="30"></th>  <!-- Column for handle -->
                                    <th><?php esc_html_e('Label', 'thaitop-register-form'); ?></th>
                                    <th><?php esc_html_e('Name', 'thaitop-register-form'); ?></th>
                                    <th><?php esc_html_e('Type', 'thaitop-register-form'); ?></th>
                                    <th><?php esc_html_e('Meta Key', 'thaitop-register-form'); ?></th>
                                    <th><?php esc_html_e('Required', 'thaitop-register-form'); ?></th>
                                    <th class="column-actions"><?php esc_html_e('Actions', 'thaitop-register-form'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($custom_fields)) : ?>
                                    <?php foreach ($custom_fields as $field): ?>
                                        <tr data-id="<?php echo esc_attr($field->id); ?>">
                                            <td><span class="dashicons dashicons-menu sort-handle"></span></td>
                                            <td><?php echo esc_html($field->field_label); ?></td>
                                            <td><?php echo esc_html($field->field_name); ?></td>
                                            <td><?php echo esc_html($field->field_type); ?></td>
                                            <td><?php echo esc_html($field->meta_key); ?></td>
                                            <td><?php echo $field->required ? '✓' : ''; ?></td>
                                            <td class="column-actions">
                                                <a href="#" class="delete-field" data-id="<?php echo esc_attr($field->id); ?>">
                                                    <?php esc_html_e('Delete', 'thaitop-register-form'); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center;">
                                            <?php esc_html_e('No custom fields found.', 'thaitop-register-form'); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Side column for meta keys -->
                <div class="thaitop-side-column">
                    <div class="card">
                        <h2><?php esc_html_e('Available User Meta Keys', 'thaitop-register-form'); ?></h2>
                        <p class="description">
                            <?php esc_html_e('These are the existing user meta keys in your WordPress site. You can use these as reference when creating custom fields.', 'thaitop-register-form'); ?>
                        </p>
                        <div class="thaitop-meta-keys-list">
                            <?php if (!empty($meta_keys)) : ?>
                                <table class="widefat striped">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('Meta Key', 'thaitop-register-form'); ?></th>
                                            <th><?php esc_html_e('Action', 'thaitop-register-form'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($meta_keys as $meta_key) : ?>
                                            <tr>
                                                <td><?php echo esc_html($meta_key); ?></td>
                                                <td>
                                                    <button type="button" 
                                                            class="button button-small copy-meta-key" 
                                                            data-meta-key="<?php echo esc_attr($meta_key); ?>">
                                                        <?php esc_html_e('Copy', 'thaitop-register-form'); ?>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else : ?>
                                <p><?php esc_html_e('No custom meta keys found.', 'thaitop-register-form'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_available_meta_keys() {
        global $wpdb;
        return $wpdb->get_col(
            "SELECT DISTINCT meta_key FROM {$wpdb->usermeta} 
            WHERE meta_key NOT LIKE '\_%' 
            ORDER BY meta_key ASC"
        );
    }

    private function render_color_fields() {
        $fields = [
            'form_bg' => __('Form Background', 'thaitop-register-form'),
            'form_text' => __('Form Text Color', 'thaitop-register-form'),
            'input_bg' => __('Input Background', 'thaitop-register-form'),
            'input_text' => __('Input Text Color', 'thaitop-register-form'),
            'input_border' => __('Input Border', 'thaitop-register-form'),
            'input_focus_border' => __('Input Focus Border', 'thaitop-register-form'),
            'button_bg_start' => __('Button Start Color', 'thaitop-register-form'),
            'button_bg_end' => __('Button End Color', 'thaitop-register-form'),
            'button_text' => __('Button Text Color', 'thaitop-register-form'),
            'label_text' => __('Label Text Color', 'thaitop-register-form')
        ];

        echo '<table class="form-table">';
        foreach ($fields as $key => $label) {
            ?>
            <tr>
                <th><label><?php echo esc_html($label); ?></label></th>
                <td>
                    <input type="color" 
                           name="thaitop_<?php echo esc_attr($key); ?>" 
                           value="<?php echo esc_attr(get_option('thaitop_' . $key, '#000000')); ?>">
                    <code><?php echo esc_html(get_option('thaitop_' . $key, '#000000')); ?></code>
                </td>
            </tr>
            <?php
        }
        echo '</table>';
    }

    public function handle_color_scheme_update($value) {
        $schemes = $this->get_color_schemes();
        $scheme = sanitize_key($value);
        
        if (isset($schemes[$scheme]) && $scheme !== 'custom') {
            foreach ($schemes[$scheme]['colors'] as $key => $color) {
                update_option('thaitop_' . $key, $color);
            }
        }
        
        return $scheme;
    }
} // Close class here only
