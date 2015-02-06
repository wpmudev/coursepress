<?php

if ( ! class_exists( 'cp_student_name_element' ) ) {
	class cp_student_name_element extends CP_Certificate_Template_Elements {

		var $element_name = 'cp_student_name_element';
		var $element_title = '';

		function on_creation() {
			$this->element_title = apply_filters( 'coursepress_student_name_element_title', __( 'Student Name', 'cp' ) );
		}

		function template_content( $course_id = false, $user_id = false, $preview = false ) {

		}

	}

	cp_register_template_element( 'cp_student_name_element', __( 'Student Name', 'cp' ) );
}