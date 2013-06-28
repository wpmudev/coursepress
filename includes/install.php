<?php
/*
// Create a term metadata table where $type = metadata type
global $wpdb;
$table_name = $wpdb->prefix . $type . 'meta';
if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") != $table_name) {
           
    coursepress_create_metadata_table($table_name, $type);
}
 
function coursepress_create_metadata_table($table_name, $type) {
    global $wpdb;
 
    if (!empty ($wpdb->charset))
        $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
    if (!empty ($wpdb->collate))
        $charset_collate .= " COLLATE {$wpdb->collate}";
             
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            meta_id bigint(20) NOT NULL AUTO_INCREMENT,
            {$type}_id bigint(20) NOT NULL default 0,
     
            meta_key varchar(255) DEFAULT NULL,
            meta_value longtext DEFAULT NULL,
                 
            UNIQUE KEY meta_id (meta_id)
        ) {$charset_collate};";
     
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook( __FILE__, 'coursepress_activate' );
function coursepress_activate($networkwide) {
    global $wpdb;
                 
    if (function_exists('is_multisite') && is_multisite()) {
        // check if it is a network activation - if so, run the activation function for each blog id
        if ($networkwide) {
                    $old_blog = $wpdb->blogid;
            // Get all blog ids
            $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blogids as $blog_id) {
                switch_to_blog($blog_id);
                _coursepress_activate();
            }
            switch_to_blog($old_blog);
            return;
        }  
    }
    _coursepress_activate();     
}

function _coursepress_activate() {
        // Add initial plugin options here
    $current_theme = get_current_theme();
    add_option('coursepress_bg_theme', $current_theme);
 
    // Create term metadata table if necessary
    global $wpdb;
        $type = 'coursepress_term';
        $table_name = $wpdb->prefix . $type . 'meta';
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        coursepress_create_metadata_table($table_name, $type);
        }
}

add_action( 'wpmu_new_blog', 'new_blog', 10, 6);       
 
function new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
    global $wpdb;
 
    if (is_plugin_active_for_network('coursepress/coursepress.php')) {
        $old_blog = $wpdb->blogid;
        switch_to_blog($blog_id);
        _coursepress_activate();
        switch_to_blog($old_blog);
    }
}
 * */
?>
