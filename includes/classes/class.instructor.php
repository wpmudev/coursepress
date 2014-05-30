<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Instructor')) {

    class Instructor extends WP_User {

        var $first_name = '';
        var $last_name = '';
        var $courses_number = 0;

        function __construct($ID, $name = '') {
            global $wpdb;

            if ($ID != 0) {
                parent::__construct($ID, $name);
            }

            /* Set meta vars */

            $this->first_name = get_user_meta($ID, 'first_name', true);
            $this->last_name = get_user_meta($ID, 'last_name', true);
            $this->courses_number = $this->get_courses_number();
        }

        function Instructor($ID, $name = '') {
            $this->__construct($ID, $name);
        }

        function get_assigned_courses_ids($status = 'all') {
            global $wpdb;

            $assigned_courses = array();
            $courses = $wpdb->get_results($wpdb->prepare("SELECT meta_key FROM $wpdb->usermeta WHERE meta_key LIKE 'course_%%' AND user_id = %d", $this->ID), OBJECT);

            foreach ($courses as $course) {
                $course_id = str_replace('course_', '', $course->meta_key);
                if ($status !== 'all') {
                    if (get_post_status($course_id) == $status) {
                        $assigned_courses[] = $course_id;
                    }
                } else {
                    $assigned_courses[] = $course_id;
                }
            }

            return $assigned_courses;
        }

        function unassign_from_course($course_id = 0) {
            delete_user_meta($this->ID, 'course_' . $course_id);
            delete_user_meta($this->ID, 'enrolled_course_date_' . $course_id);
            delete_user_meta($this->ID, 'enrolled_course_class_' . $course_id);
            delete_user_meta($this->ID, 'enrolled_course_group_' . $course_id);
        }

        function unassign_from_all_courses() {
            $courses = $this->get_assigned_courses_ids();
            foreach ($courses as $course_id) {
                $this->unassign_from_course($course_id);
            }
        }

        //Get number of instructor's assigned courses
        function get_courses_number() {
            global $wpdb;
            $courses_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) as cnt FROM $wpdb->usermeta um, $wpdb->posts p WHERE (um.user_id = %d AND um.meta_key LIKE 'course_%%') AND (p.ID = um.meta_value)", $this->ID));
            return $courses_count;
        }

        function delete_instructor($delete_user = true) {
            if ($delete_user) {
                wp_delete_user($this->ID); //without reassign
            }else{//just delete the meta which says that user is an instructor
                delete_user_meta($this->ID, 'role_ins', 'instructor'); 
            }
        }

    }

}
?>
