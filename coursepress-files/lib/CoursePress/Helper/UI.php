<?php

class CoursePress_Helper_UI {


	public static function browse_media_field( $id, $name, $args = array() ) {

		if( ! $name ) {
			$name = $id;
		}

		$args['title'] = isset( $args['title'] ) ? sanitize_text_field( $args['title'] ) : '';
		$args['container_class'] = isset( $args['container_class'] ) ? sanitize_text_field( $args['container_class'] ) : 'wide';
		$args['textbox_class'] = isset( $args['textbox_class'] ) ? sanitize_text_field( $args['textbox_class'] ) : 'medium';
		$args['title'] = isset( $args['title'] ) ? sanitize_text_field( $args['title'] ) : '';
		$args['value'] = isset( $args['value'] ) ? sanitize_text_field( $args['value'] ) : '';
		$args['placeholder'] = isset( $args['placeholder'] ) ? sanitize_text_field( $args['placeholder'] ) : __( 'Add Media URL or Browse for Media', CoursePress::TD );
		$args['button_text'] = isset( $args['button_text'] ) ? sanitize_text_field( $args['button_text'] ) : __( 'Browse', CoursePress::TD );
		$args['type'] = isset( $args['type'] ) ? sanitize_text_field( $args['type'] ) : 'image';
		$args['invalid_message'] = isset( $args['invalid_message'] ) ? sanitize_text_field( $args['invalid_message'] ) : '';
		$args['description'] = isset( $args['description'] ) ? sanitize_text_field( $args['description'] ) : '';

		if( 'image' === $args['type'] ) {
			$supported_extensions = implode( ', ', CoursePress_Helper_Utility::get_image_extensions() );
		}
		if( 'audio' === $args['type'] ) {
			$supported_extensions = implode( ', ', wp_get_video_extensions() );
		}
		if( 'video' === $args['type'] ) {
			$supported_extensions = implode( ', ', wp_get_audio_extensions() );
		}

		$content = '
		<div class="' . $args['container_class'] . '">
			<label for="' . $name . '">' .
	            esc_html( $args['title'] );

		if( ! empty( $args['description'] ) ) {
		    $content .= '<p class="description">' . esc_html( $args['description'] ) . '</p>';
	    }

		$content .= '
			</label>
			<input class="' . $args['textbox_class'] . ' ' . $args['type'] . '_url" type="text" name="' . $name . '" id="' . $name . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" value="' . esc_attr( $args['value'] ) . '"/>
			<input class="button browse-media-field" type="button" name="' . $name . '-button" value="' . esc_attr( $args['button_text'] ) . '"/>
			<div class="invalid_extension_message">' . sprintf( esc_html__( 'Extension of the file is not valid. Please use one of the following: %s', CoursePress::TD ), $supported_extensions ) . '</div>
		</div>';

		return $content;

	}


}