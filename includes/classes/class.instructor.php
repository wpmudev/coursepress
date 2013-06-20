<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Instructor')) {

    class Instructor extends WP_User {

        function __construct() {
            
        }

        function Instructor() {
            $this->__construct();
        }

        function add_new() {
            
        }

        function publish() {
            
        }

        function unpublish() {
            
        }

        function assign_to_course($instructor_id, $course_id) {
            return $instructor_id.','.$course_id;
        }
    

    }

}
?>
