<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Unit')) {

    class Unit {

        var $id = '';
        var $output = 'OBJECT';
        var $unit = array();

        function __construct($id = '', $output = 'OBJECT') {
            $this->id = $id;
            $this->output = $output;
        }

        function Unit($id = '', $output = 'OBJECT') {
            $this->__construct($id, $output);
        }

        function get_unit() {
            $unit = get_post($this->id, $this->output);
            if ($unit->post_title == '') {
                $unit->post_title = __('Untitled', 'cp');
            }
            
            if ($unit->post_status == 'private') {
                $unit->post_status = __('unpublished', 'cp');
            }

            return $unit;
        }
        
        function update_unit() {
            global $user_id, $wpdb;
            $unit = get_post($this->id, $this->output);

            if ($_POST['unit_name'] != '' && $_POST['unit_name'] != __('Untitled', 'cp') && $_POST['unit_description'] != '') {
                if ($unit->post_status != 'publish') {
                    $post_status = 'private';
                }
            } else {
                $post_status = 'private';//draft
            }
            
            $post = array(
                'post_author' => $user_id,
                'post_content' => $_POST['unit_description'],
                'post_status' => $post_status,
                'post_title' => $_POST['unit_name'],
                'post_type' => 'unit',
            );

            if (isset($_POST['unit_id'])) {
                $post['ID'] = $_POST['unit_id']; //If ID is set, wp_insert_post will do the UPDATE instead of insert
            }

            $post_id = wp_insert_post($post);
            
            update_post_meta($post_id, 'course_id', $_POST['course_id']);
            
            if(!get_post_meta($_post_id, 'unit_order', true)){
                update_post_meta($post_id, 'unit_order', '');
            }
            
            

            return $post_id;
        }

        function delete_unit($force_delete) {
            $wpdb;
            wp_delete_post($this->id, $force_delete); //Whether to bypass trash and force deletion
        }
        
        function change_status($post_status) {
            $post = array(
                'ID' => $this->id,
                'post_status' => $post_status,
            );
     
            // Update the post status
            wp_update_post($post);
        }

    }

}
?>
