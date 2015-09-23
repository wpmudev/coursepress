<?php

if ( ! class_exists( 'cp_website_element' ) ) {
	class cp_website_element extends CP_Certificate_Template_Elements {

		var $element_name = 'cp_website_element';
		var $element_title = '';

		function on_creation() {
			$this->element_title = apply_filters( 'coursepress_website_element_title', __( 'Website', 'cp' ) );
		}

		function template_content( $course_id = false, $user_id = false, $preview = false ) {

		}

	}

	cp_register_template_element( 'cp_website_element', __( 'Website', 'cp' ) );
}