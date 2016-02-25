<?php

class CoursePress_View_Front_Calendar {
    
    public static function init() {
        add_action( 'wp_ajax_refresh_course_calendar', array( __CLASS__, 'refresh_course_calendar' ) );
    }
    
    public static function refresh_course_calendar() {
        
    }
}