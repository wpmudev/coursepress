<?php
/**
 * Class CoursePress_Step_Image
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_Image extends CoursePress_Step {
	protected $type = 'image';

	function get_keys() {
		$keys = parent::get_keys();
		$keys = array_merge( $keys, array(
			'image_url',
			'show_media_caption',
			'caption_field',
			'caption_custom_text',
			'image_url_thumbnail_id',
		));

		return $keys;
	}

	function get_question() {
		$image_url = $this->__get( 'image_url' );

		if ( ! empty( $image_url ) ) {
			$attr = array(
				'src' => esc_url_raw( $image_url ),
				'class' => 'course-step-image',
				'alt' => '',
			);
			$image = coursepress_create_html( 'img', $attr );
			$show_caption = $this->__get( 'show_media_caption' );

			if ( $show_caption ) {
				$caption_field = $this->__get( 'caption_field' );
				$caption = '';

				if ( 'media' == $caption_field ) {
					// Check thumbnail id
					$thumb_id = $this->__get( 'image_url_thumbnail_id' );
				} else {
					$caption = $this->__get( 'caption_custom_text' );
				}
				$caption = $this->__get( 'caption_custom_text' );
				if ( ! empty( $caption ) ) {
					$caption = coursepress_create_html(
						'p',
						array(
							'class' => 'image-caption image-description'
						),
						$caption
					);
					$image .= $caption;
				}
			}

			return $image;
		}
	}
}
