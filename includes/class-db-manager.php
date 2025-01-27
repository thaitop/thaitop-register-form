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

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            field_label varchar(100) NOT NULL,
            field_name varchar(100) NOT NULL,
            field_type varchar(50) NOT NULL,
            meta_key varchar(100) NOT NULL,
            required tinyint(1) DEFAULT 0,
            active tinyint(1) DEFAULT 1,
            sort_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        
        // Add debug logging for table creation
        error_log('Table creation result: ' . print_r($result, true));
        
        // Verify if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'");
        if (!$table_exists) {
            error_log('Table creation failed: ' . $this->table_name);
        }
    }

    public function get_custom_fields() {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT * FROM {$this->table_name} WHERE active = 1 ORDER BY sort_order ASC"
        );
    }

    public function get_table_name() {
        return $this->table_name;
    }

    public function add_field($data) {
        global $wpdb;
        
        // Make sure required fields are present
        if (empty($data['field_label']) || empty($data['field_name']) || 
            empty($data['field_type']) || empty($data['meta_key'])) {
            return false;
        }
        
        $result = $wpdb->insert(
            $this->table_name, // Changed from get_table_name() to table_name property
            [
                'field_label' => $data['field_label'],
                'field_name' => $data['field_name'],
                'field_type' => $data['field_type'],
                'meta_key' => $data['meta_key'],
                'required' => $data['required'],
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%d', '%s']
        );
        
        return $result !== false;
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
