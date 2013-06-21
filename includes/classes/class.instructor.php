<?php

if (!class_exists('Instructor')) {

    class Instructor extends WP_User {

        var $first_name = '';
        var $last_name = '';

        function __construct($id, $name = '') {
            global $wpdb;
            
            if ($id != 0) {
                parent::__construct($id, $name);
            }
            
            /*Set meta vars*/
            
            $this->first_name = get_user_meta($id, 'first_name', true);
            $this->last_name = get_user_meta($id, 'last_name', true);
        }

        function Instructor($id, $name = '') {

            $this->__construct($id, $name);
        }


    }

}
?>
