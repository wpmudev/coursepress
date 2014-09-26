<?php

class cp_issued_by_element extends CP_Certificate_Template_Elements {

	var $element_name	 = 'cp_issued_by_element';
	var $element_title	 = '';

	function on_creation() {
		$this->element_title = apply_filters( 'cp_issued_by_element_title', __( 'Issued By', 'cp' ) );
	}

	function template_content( $course_id = false, $user_id = false, $preview = false  ) {

	}

}

cp_register_template_element( 'cp_issued_by_element', __( 'Issued By', 'cp' ) );
