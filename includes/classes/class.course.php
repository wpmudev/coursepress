<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Course')) {

    class Course {

        var $id = '';
        var $output = 'OBJECT';
        var $course = array();

        function __construct($id = '', $output = 'OBJECT') {
            $this->id = $id;
            $this->output = $output;
        }

        function Course($id = '', $output = 'OBJECT') {
            $this->__construct($id, $output);
        }

        function get_course() {
            $course = get_post($this->id, $this->output);
            if($course->post_title == ''){
                $course->post_title = __('Untitled', 'cp');
            }
            if($course->post_status == 'private'){
                $course->post_status = __('unpublished', 'cp');
            }

            return $course;
        }

        function update_course() {
            global $user_id, $wpdb;

            if($_POST['course_name'] != '' && $_POST['course_name'] != __('Untitled', 'cp') && $_POST['course_description'] != ''){
                $post_status = 'private';
            }else{
                $post_status = 'draft';
            }
            
            $post = array(
                'post_author' => $user_id,
                'post_content' => $_POST['course_description'],
                'post_status' => $post_status,
                'post_title' => $_POST['course_name'],
                'post_type' => 'course',
            );

            if (isset($_POST['course_id'])) {
                $post['ID'] = $_POST['course_id']; //If ID is set, wp_insert_post will do the UPDATE instead of insert
            }

            $post_id = wp_insert_post($post);

            //Update post meta
            if ($post_id != 0) {
                foreach ($_POST as $key => $value) {
                    if (preg_match("/meta_/i", $key)) {//every field name with prefix "meta_" will be saved as post meta automatically
                        update_post_meta($post_id, str_replace('meta_', '', $key), $value);
                    }
                }

                $old_post_meta = get_post_meta($post_id, 'instructors', false); //Get last instructor ID array in order to compare with posted one

                if (serialize(array($_POST['instructor'])) !== serialize($old_post_meta)) {//If instructors IDs don't match
                    delete_post_meta($post_id, 'instructors');
                    delete_user_meta_by_key('course_' . $post_id);
                }

                update_post_meta($post_id, 'instructors', $_POST['instructor']); //Save instructors for the Course

                foreach ($_POST['instructor'] as $instructor_id) {
                    update_user_meta($instructor_id, 'course_' . $post_id, $post_id); //Link courses and instructors (in order to avoid custom tables) for easy MySql queries (get instructor stats, his courses, etc.)
                }
            }

            return $post_id;
        }
        
        function delete_course($force_delete){
            $wpdb;
            wp_delete_post( $this->id, $force_delete ); //Whether to bypass trash and force deletion
            delete_user_meta_by_key('course_' . $this->id);
        }
        
        function can_show_permalink(){
            $course = $this->get_course();
            if($course->post_status !== 'draft'){
                return true;
            }else{
                return false;
            }
        }

    }

}
?>
