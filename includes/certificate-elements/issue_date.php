<?php

if ( ! class_exists( 'cp_issue_date_element' ) ) {
	class cp_issue_date_element extends CP_Certificate_Template_Elements {

		var $element_name = 'cp_issue_date_element';
		var $element_title = '';

		function on_creation() {
			$this->element_title = apply_filters( 'coursepress_issue_date_element_title', __( 'Issue Date', 'cp' ) );
		}

		function template_content( $course_id = false, $user_id = false, $preview = false ) {

		}

	}

	cp_register_template_element( 'cp_issue_date_element', __( 'Issue Date', 'cp' ) );
}