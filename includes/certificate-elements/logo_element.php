<?php

class cp_logo_element extends CP_Certificate_Template_Elements {

	var $element_name	 = 'cp_logo_element';
	var $element_title	 = 'Logo';

	function on_creation() {
		$this->element_title = apply_filters( 'cp_logo_element_title', __( 'Logo', 'cp' ) );
	}

	function admin_content() {
		echo parent::get_cell_alignment();
		echo parent::get_element_margins();
	}

	function ticket_content( $course_id = false, $user_id = false, $preview = false  ) {
		/*
		  if ( $certificate_logo ) {
		  return '<img src="' . $certificate_logo . '" />';
		  } else {
		  return '';
		  } */
	}

}

cp_register_template_element( 'cp_logo_element', __( 'Logo', 'cp' ) );
