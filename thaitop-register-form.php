<?php
/**
 * Plugin Name: ThaiTop Register Form
 * Plugin URI: https://thaitoptecs.com
 * Description: WordPress user registration form with custom fields.
 * Version: 1.1.1
 * Author: ThaiTop
 * Author URI: https://thaitoptecs.com
 * Text Domain: thaitop-register-form
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('THAITOP_REGISTER_VERSION', '1.1.1');
define('THAITOP_REGISTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('THAITOP_REGISTER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoload classes
function thaitop_register_autoloader($class) {
    // Base namespace and directory
    $namespace = 'ThaiTop\\Register\\';
    $base_dir = THAITOP_REGISTER_PLUGIN_DIR . 'includes/';
    
    // Check if class uses our namespace
    if (strpos($class, $namespace) !== 0) {
        return;
    }

    // Remove namespace from class name
    $relative_class = substr($class, strlen($namespace));
    
    // Convert class name to filename
    $file = '';
    $last_ns_pos = strrpos($relative_class, '\\');
    
    if ($last_ns_pos !== false) {
        // Handle sub-namespaces
        $namespace = substr($relative_class, 0, $last_ns_pos);
        $relative_class = substr($relative_class, $last_ns_pos + 1);
        $file = str_replace('\\', DIRECTORY_SEPARATOR, strtolower($namespace)) . DIRECTORY_SEPARATOR;
    }
    
    $file .= 'class-' . str_replace('_', '-', strtolower($relative_class)) . '.php';
    
    $file_path = $base_dir . $file;
    
    // Require file if it exists
    if (file_exists($file_path)) {
        require_once($file_path);
    }
}
spl_autoload_register('thaitop_register_autoloader');

// Initialize plugin
function thaitop_register_init() {
    // Load text domain
    load_plugin_textdomain('thaitop-register-form', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Check if required files exist
    $required_files = [
        'includes/class-plugin.php',
        'includes/class-form-handler.php',
        'includes/admin/class-admin-settings.php',
        'includes/class-recaptcha.php',
        'includes/class-ajax-handler.php'
    ];

    foreach ($required_files as $file) {
        if (!file_exists(THAITOP_REGISTER_PLUGIN_DIR . $file)) {
            add_action('admin_notices', function() use ($file) {
                echo '<div class="error"><p>';
                echo sprintf(__('ThaiTop Register Form: Required file "%s" is missing.', 'thaitop-register-form'), $file);
                echo '</p></div>';
            });
            return;
        }
    }
    
    // Initialize main plugin class
    $plugin = new ThaiTop\Register\Plugin();
    $plugin->init();

    // Initialize Ajax handler
    new ThaiTop\Register\Ajax_Handler();
}

add_action('plugins_loaded', 'thaitop_register_init');

function thaitop_check_users_can_register() {
    if (!get_option('users_can_register')) {
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-error">
                <p><?php esc_html_e('Warning: "Anyone can register" must be enabled in Settings > General for the registration form to work properly.', 'thaitop-register-form'); ?></p>
            </div>
            <?php
        });
    }
}
add_action('admin_init', 'thaitop_check_users_can_register');

// Activation hook
register_activation_hook(__FILE__, function() {
    // Check required directories
    $required_dirs = [
        'assets',
        'assets/css',
    ];

    foreach ($required_dirs as $dir) {
        $dir_path = THAITOP_REGISTER_PLUGIN_DIR . $dir;
        if (!file_exists($dir_path)) {
            wp_mkdir_p($dir_path);
        }
    }

    // Create necessary options
    add_option('thaitop_register_version', THAITOP_REGISTER_VERSION);
    add_option('thaitop_recaptcha_enabled', false);
    add_option('thaitop_recaptcha_site_key', '');
    add_option('thaitop_recaptcha_secret_key', '');

    // Create custom fields table
    $db_manager = new ThaiTop\Register\DB_Manager();
    $db_manager->create_tables();
});