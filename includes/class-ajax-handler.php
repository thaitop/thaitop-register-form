<?php
namespace ThaiTop\Register;

class Ajax_Handler {
    private $db_manager;

    public function __construct() {
        $this->db_manager = new DB_Manager();
        add_action('wp_ajax_delete_custom_field', [$this, 'delete_custom_field']);
        add_action('wp_ajax_update_fields_order', [$this, 'update_fields_order']);
        add_action('wp_ajax_update_color_scheme', [$this, 'update_color_scheme']);
        add_action('wp_ajax_thaitop_update_field_order', [$this, 'update_field_order']);
        add_action('wp_ajax_thaitop_delete_field', [$this, 'delete_field']);
    }

    public function delete_custom_field() {
        try {
            error_log('Delete request received');
            
            // Verify nonce
            if (!check_ajax_referer('thaitop_custom_field_action', 'nonce', false)) {
                error_log('Invalid nonce');
                wp_send_json_error(['message' => 'Invalid security token']);
                die();
            }

            // Verify permissions
            if (!current_user_can('manage_options')) {
                error_log('Insufficient permissions');
                wp_send_json_error(['message' => 'Insufficient permissions']);
                die();
            }

            $field_id = isset($_POST['field_id']) ? intval($_POST['field_id']) : 0;
            error_log('Field ID: ' . $field_id);

            if (!$field_id) {
                wp_send_json_error(['message' => 'Invalid field ID']);
                die();
            }

            $result = $this->db_manager->delete_field($field_id);
            error_log('Delete result: ' . var_export($result, true));

            if ($result === false) {
                wp_send_json_error(['message' => 'Database error']);
                die();
            }

            wp_send_json_success(['message' => 'Field deleted successfully']);
            die();

        } catch (Exception $e) {
            error_log('Exception: ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
            die();
        }
    }

    public function update_fields_order() {
        // Verify nonce
        if (!check_ajax_referer('thaitop_custom_field_action', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid nonce']);
        }

        // Verify permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        $fields = isset($_POST['fields']) ? $_POST['fields'] : [];
        
        if (empty($fields)) {
            wp_send_json_error(['message' => 'No fields to update']);
        }

        foreach ($fields as $item) {
            $this->db_manager->update_field(
                intval($item['id']), 
                ['sort_order' => intval($item['order'])]
            );
        }

        wp_send_json_success();
    }

    public function update_color_scheme() {
        if (!check_ajax_referer('thaitop_custom_field_action', 'nonce', false)) {
            wp_send_json_error();
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error();
            return;
        }

        $scheme = isset($_POST['scheme']) ? sanitize_key($_POST['scheme']) : '';
        $schemes = (new Admin\Admin_Settings())->get_color_schemes();

        if (isset($schemes[$scheme]) && $scheme !== 'custom') {
            foreach ($schemes[$scheme]['colors'] as $key => $value) {
                update_option('thaitop_' . $key, $value);
            }
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    public function update_field_order() {
        check_ajax_referer('thaitop_custom_field_action', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $field_order = isset($_POST['field_order']) ? $_POST['field_order'] : [];
        
        if (empty($field_order)) {
            wp_send_json_error('No field order data received');
            return;
        }

        global $wpdb;
        $table_name = $this->db_manager->get_table_name();
        $success = true;

        foreach ($field_order as $position => $field_id) {
            $result = $wpdb->update(
                $table_name,
                ['field_order' => $position],
                ['id' => absint($field_id)],
                ['%d'],
                ['%d']
            );

            if ($result === false) {
                $success = false;
                error_log('Error updating field order: ' . $wpdb->last_error);
            }
        }

        if ($success) {
            wp_send_json_success('Field order updated');
        } else {
            wp_send_json_error('Error updating field order');
        }
    }
}
