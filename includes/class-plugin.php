<?php
namespace ThaiTop\Register;

use ThaiTop\Register\Admin\Admin_Settings;

class Plugin {
    private $form_handler;
    private $admin_settings;

    public function init() {
        // Initialize components
        try {
            $this->form_handler = new Form_Handler();
            $this->admin_settings = new Admin_Settings();
            
            // Register assets
            add_action('wp_enqueue_scripts', [$this, 'register_assets']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        } catch (\Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="error"><p>';
                echo esc_html('ThaiTop Register Form Error: ' . $e->getMessage());
                echo '</p></div>';
            });
        }
    }

    public function register_assets() {
        $css_file = THAITOP_REGISTER_PLUGIN_URL . 'assets/css/style.css';
        
        if (file_exists(THAITOP_REGISTER_PLUGIN_DIR . 'assets/css/style.css')) {
            wp_register_style(
                'thaitop-register-style',
                $css_file,
                [],
                THAITOP_REGISTER_VERSION
            );
        } else {
            add_action('admin_notices', function() {
                echo '<div class="error"><p>';
                echo esc_html__('ThaiTop Register Form: CSS file is missing.', 'thaitop-register-form');
                echo '</p></div>';
            });
        }
    }

    public function enqueue_assets() {
        if (wp_style_is('thaitop-register-style', 'registered')) {
            wp_enqueue_style('thaitop-register-style');
        }
    }
}
