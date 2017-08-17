<?php
/**
 * Class CoursePress_Step_Audio
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_Audio extends CoursePress_Step {
	protected $type = 'audio';

	protected function get_keys() {
		$keys = parent::get_keys();
		$keys = array_merge( $keys, array(
			'audio_url',
			'loop',
			'autoplay',
			'audio_url_thumbnail_id'
		));

		return $keys;
	}

	function get_question() {
		$src = $this->__get( 'audio_url' );
		$loop = $this->__get( 'loop' );
		$autoplay = $this->__get( 'autoplay' );

		$attr = array(
			'src' => esc_url_raw( $src ),
		);

		if ( $loop ) {
			$attr['loop'] = true;
		}
		if ( $autoplay ) {
			$attr['autoplay'] = true;
		}

		$audio = wp_audio_shortcode( $attr );

		return $this->create_html( 'div', array( 'class' => 'audio-player' ), $audio );
	}
}