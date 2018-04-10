<?php
/**
 * Class CoursePress_Step_Video
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_Video extends CoursePress_Step {
	protected $type = 'video';

	protected function get_keys() {
		$keys = parent::get_keys();
		$keys = array_merge( $keys, array(
			'video_url',
			'show_media_caption',
			'caption_field',
			'caption_custom_text',
			'video_player_width',
			'video_player_height',
			'video_autoplay',
			'video_loop',
			'video_hide_controls',
			'hide_related_media',
			'show_media_caption',
		));

		return $keys;
	}

	function get_question() {
		$src = esc_url_raw( $this->__get( 'video_url' ) );
		$hide_related_video = $this->__get( 'hide_related_media' );
		$width = $this->__get( 'video_player_width' );
		$attempts = $this->get_user_attempts(get_current_user_id());
		$retries_allowed = $this->__get('allow_retries');

		$attr = array(
			'id' => $this->__get( 'ID' ),
			'class' => 'video-js vjs-default-skin vjs-big-play-centered',
			'width' => $width,
			'height' => $this->__get( 'video_player_height' ),
			'src' => $src,
			'data-retries-allowed' => $retries_allowed ? 'true' : 'false',
			'data-attempts' => $attempts,
			'data-retries' => $this->__get('retry_attempts'),
			'data-setup' => $this->create_video_js_setup_data( $src, $hide_related_video, $width )
		);

		if ( ! $this->__get( 'video_hide_controls' ) ) {
			$attr['controls'] = true;
		}

		$auto_play = $this->__get( 'video_autoplay' );
		$loop = $this->__get( 'video_loop' );

		if ( $auto_play ) {
			$attr['autoplay'] = 'true';
		}
		if ( $loop ) {
			$attr['loop'] = 'true';
		}

		$attr = array_filter( $attr );

		$html = $this->create_html( 'video', $attr );

		return $html;
	}
}
