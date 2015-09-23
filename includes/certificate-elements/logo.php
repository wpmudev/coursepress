<?php

if ( ! class_exists( 'cp_logo_element' ) ) {
	class cp_logo_element extends CP_Certificate_Template_Elements {

		var $element_name = 'cp_logo_element';
		var $element_title = 'Logo';

		function on_creation() {
			$this->element_title = apply_filters( 'coursepress_logo_element_title', __( 'Logo', 'cp' ) );
		}

		function admin_content() {
			echo parent::get_cell_alignment();
			echo parent::get_element_margins();
			?>
			<label>
				<input class="file_url" type="text" size="36" name="meta_featured_url" value="" placeholder="<?php echo esc_attr( 'Enter a URL or Browse for a file.', 'cp' ); ?>"/>
				<input class="file_url_button button-secondary" type="button" value="<?php _e( 'Browse', 'cp' ); ?>">
			</label>
		<?php
		}

		function template_content( $course_id = false, $user_id = false, $preview = false ) {
			/*
			  if ( $certificate_logo ) {
			  return '<img src="' . $certificate_logo . '" />';
			  } else {
			  return '';
			  } */
		}

	}

	cp_register_template_element( 'cp_logo_element', __( 'Logo', 'cp' ) );
}