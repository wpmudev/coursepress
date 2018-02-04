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
		$attempts = $this->get_user_attempts(get_current_user_id());
		$allowed_attempts = $this->__get('retry_attempts');

		$attr = array(
			'id'         => $this->__get('ID'),
			'class'      => 'video-js vjs-default-skin',
			'src'        => esc_url_raw($src),
			'controls'   => true,
			'data-setup' => $this->create_audio_js_setup_data(),
			'data-attempts' => $attempts,
			'data-allowed-attempts' => $allowed_attempts + 1, // +1 because this is the total number of allowed attempts not just reattempts
		);

		if ( $loop ) {
			$attr['loop'] = true;
		}
		if ( $autoplay ) {
			$attr['autoplay'] = true;
		}

		return $this->create_html( 'div', array( 'class' => 'audio-player' ), $this->create_html('audio', $attr) );
	}

	private function create_audio_js_setup_data()
	{
		$data = array();
		$data["aspectRatio"] = "1:0";
		$data["fluid"] = true;
		$data["controlBar"] = array("fullscreenToggle" => false);

		return json_encode($data);
	}
}