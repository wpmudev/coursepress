<?php

class cp_certificate_title_element extends CP_Certificate_Template_Elements {

	var $element_name	 = 'cp_certificate_title_element';
	var $element_title	 = '';

	function on_creation() {
		$this->element_title = apply_filters( 'coursepress_certificate_title_element_title', __( 'Certificate Title', 'cp' ) );
		
	}

	function template_content( $course_id = false, $user_id = false, $preview = false  ) {

	}

}

cp_register_template_element( 'cp_certificate_title_element', __( 'Certificate Title', 'cp' ) );