<?php
/* NEVER LOOP THROUGH BLOGS!
register_activation_hook( __FILE__, 'coursepress_activate' );
register_deactivation_hook( __FILE__, 'coursepress_deactivate' );
     
function my_network_propagate($pfunction, $networkwide) {
    global $wpdb;
 
    if (function_exists('is_multisite') && is_multisite()) {
        // check if it is a network activation - if so, run the activation function
        // for each blog id
        if ($networkwide) {
            $old_blog = $wpdb->blogid;
            // Get all blog ids
            $blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
            foreach ($blogids as $blog_id) {
                switch_to_blog($blog_id);
                call_user_func($pfunction, $networkwide);
            }
            switch_to_blog($old_blog);
            return;
        }  
    }
    call_user_func($pfunction, $networkwide);
}
 
function coursepress_activate($networkwide) {
    my_network_propagate('_my_activate', $networkwide);
}
 
function coursepress_deactivate($networkwide) {
    my_network_propagate('_my_deactivate', $networkwide);
}
 * */
?>
