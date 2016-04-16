<?php

if ( ! class_exists( 'cp_course_name_element' ) ) {
	class cp_course_name_element extends CP_Certificate_Template_Elements {

		var $element_name = 'cp_course_name_element';
		var $element_title = '';

		function on_creation() {
			$this->element_title = apply_filters( 'coursepress_course_name_element_title', __( 'Course Name', 'cp' ) );
		}

		function template_content( $course_id = false, $user_id = false, $preview = false ) {

		}

	}

	cp_register_template_element( 'cp_course_name_element', __( 'Course Name', 'cp' ) );
}