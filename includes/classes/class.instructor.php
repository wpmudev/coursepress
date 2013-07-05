<?php

if (!class_exists('Instructor')) {

    class Instructor extends WP_User {

        var $first_name = '';
        var $last_name = '';
        var $courses_number = 0;

        function __construct($id, $name = '') {
            global $wpdb;
            
            if ($id != 0) {
                parent::__construct($id, $name);
            }
            
            /*Set meta vars*/
            
            $this->first_name = get_user_meta($id, 'first_name', true);
            $this->last_name = get_user_meta($id, 'last_name', true);
            $this->courses_number = $this->get_courses_number();
        }

        function Instructor($id, $name = '') {
            $this->__construct($id, $name);
        }
        
        //Get number of instructor's assigned courses
        function get_courses_number(){
            global $wpdb;
            $courses_count = $wpdb->get_var("SELECT COUNT(*) as cnt FROM $wpdb->usermeta um, $wpdb->posts p WHERE (um.user_id = ".$this->id." AND um.meta_key LIKE 'course_%') AND (p.ID = um.meta_value)");
            return $courses_count;
        }


    }

}
?>
