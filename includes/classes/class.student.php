<?php

if (!class_exists('Student')) {

    class Student extends WP_User {

        var $first_name = '';
        var $last_name = '';

        function __construct($id, $name = '') {
            global $wpdb;

            if ($id != 0) {
                parent::__construct($id, $name);
            }

            /* Set meta vars */

            $this->first_name = get_user_meta($id, 'first_name', true);
            $this->last_name = get_user_meta($id, 'last_name', true);
        }

        function Student($id, $name = '') {

            $this->__construct($id, $name);
        }

        //Check if the user is alrady enrolled in the course
        function user_enrolled_in_course($course_id){
            if(get_user_meta($this->id, 'enrolled_course_date_' . $course_id, true)){
                return true;
            }else{
                return false;
            }
        }
        
        //Enroll student in the course
        function enroll_in_course($course_id) {

            $current_time = current_time('mysql');
            if (update_user_meta($this->id, 'enrolled_course_date_' . $course_id, $current_time)) { //Link courses and student (in order to avoid custom tables) for easy MySql queries (get courses stats, student courses, etc.)
                if (update_user_meta($this->id, 'enrolled_course_class_' . $course_id, '')) {
                    if (update_user_meta($this->id, 'enrolled_course_group_' . $course_id, '')) {
                        return true;
                    } else {
                        //something went wrong
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
            //TO DO: add new payment status if it's paid
        }

        //Unenroll student from the course
        function unenroll_in_course($course_id) {

            $current_time = current_time('mysql');

            if (delete_user_meta($this->id, 'enrolled_course_date_' . $course_id)) {
                if (delete_user_meta($this->id, 'enrolled_course_class_' . $course_id)) {
                    if (delete_user_meta($this->id, 'enrolled_course_group_' . $course_id)) {
                        if (update_user_meta($this->id, 'unenrolled_course_date_' . $course_id, $current_time)) {//keep a record of all unenrolled students
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
       

    }

}
?>
