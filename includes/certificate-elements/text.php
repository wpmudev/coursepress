<?php

if ( ! class_exists( 'cp_text_element' ) ) {
	class cp_text_element extends CP_Certificate_Template_Elements {

		var $element_name = 'cp_text_element';
		var $element_title = '';

		function on_creation() {
			$this->element_title = apply_filters( 'coursepress_certificate_title_element_title', __( 'Custom Text', 'cp' ) );

		}

		function template_content( $course_id = false, $user_id = false, $preview = false ) {

		}

	}

	cp_register_template_element( 'cp_text_element', __( 'Custom Text', 'cp' ) );
}