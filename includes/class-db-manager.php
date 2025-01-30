<?php
namespace ThaiTop\Register;

class DB_Manager {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'thaitop_custom_fields';
    }

    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'thaitop_custom_fields';
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            field_label varchar(100) NOT NULL,
            field_name varchar(100) NOT NULL,
            field_type varchar(50) NOT NULL,
            meta_key varchar(100) NOT NULL,
            required tinyint(1) DEFAULT 0,
            field_order int(11) DEFAULT 0,
            layout varchar(10) DEFAULT 'full',
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function get_custom_fields() {
        global $wpdb;
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} ORDER BY field_order ASC"
            )
        );
        
        if ($wpdb->last_error) {
            error_log('DB Error in get_custom_fields: ' . $wpdb->last_error);
            return [];
        }
        
        return $results;
    }

    public function get_table_name() {
        return $this->table_name;
    }

    public function add_field($data) {
        global $wpdb;
        
        // Get max order
        $max_order = $wpdb->get_var("SELECT MAX(field_order) FROM {$this->table_name}");
        $next_order = (int)$max_order + 1;
        
        // Make sure required fields are present
        if (empty($data['field_label']) || empty($data['field_name']) || 
            empty($data['field_type']) || empty($data['meta_key'])) {
            return false;
        }
        
        $result = $wpdb->insert(
            $this->table_name,
            [
                'field_label' => $data['field_label'],
                'field_name' => $data['field_name'],
                'field_type' => $data['field_type'],
                'meta_key' => $data['meta_key'],
                'required' => !empty($data['required']) ? 1 : 0, // ปรับปรุงการตรวจสอบ
                'layout' => isset($data['layout']) ? $data['layout'] : 'full',
                'field_order' => $next_order
            ],
            [
                '%s', // field_label
                '%s', // field_name
                '%s', // field_type
                '%s', // meta_key
                '%d', // required
                '%s', // layout
                '%d'  // field_order
            ]
        );
        
        if ($result === false) {
            error_log('DB Error in add_field: ' . $wpdb->last_error);
            return false;
        }
        
        return true;
    }

    public function update_field($id, $data) {
        global $wpdb;
        return $wpdb->update($this->table_name, $data, ['id' => $id]);
    }

    public function delete_field($id) {
        global $wpdb;
        
        error_log('DB Manager: Deleting field ' . $id);
        
        // Ensure the ID is valid
        $id = absint($id);
        if (!$id) {
            error_log('DB Manager: Invalid ID');
            return false;
        }
        
        // Check if field exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE id = %d",
            $id
        ));
        
        if (!$exists) {
            error_log('DB Manager: Field not found');
            return false;
        }
        
        $result = $wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );
        
        if ($result === false) {
            error_log('DB Manager: Delete failed - ' . $wpdb->last_error);
            return false;
        }
        
        error_log('DB Manager: Field deleted successfully');
        return true;
    }
}
