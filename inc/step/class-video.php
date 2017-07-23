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

	function is_youtube_url( $url ) {
		$host = parse_url( $url, PHP_URL_HOST );
		return $host && (strpos($host, 'youtube') !== false || strpos($host, 'youtu.be') !== false);
	}

	function is_vimeo_url( $url ) {
		$host = parse_url( $url, PHP_URL_HOST );
		return $host && strpos($host, 'vimeo') !== false;
	}

	function create_video_js_setup_data() {
		$url = $this->__get( 'video_url' );
		$src = false;
		$extra_data = array();

		if( $this->is_youtube_url( $url ) ) {
			$src = 'youtube';

			$show_related_media = ! $this->__get( 'hide_related_media' );
			$extra_data['youtube'] = array( 'rel' => intval( $show_related_media ) );
		} elseif( $this->is_vimeo_url( $url ) ) {
			$src = 'vimeo';
		}

		$setup_data = array();
		$player_width = $this->__get( 'video_player_width' );

		if( !$player_width )
			$setup_data['fluid'] = true;

		if( $src ) {
			$setup_data['techOrder'] = array($src);
			$setup_data['sources'] = array(
				array(
					'type' => 'video/' . $src,
					'src' => $url
				)
			);
		}

		$setup_data = array_merge( $setup_data, $extra_data );

		return json_encode($setup_data);
	}

	function get_question() {
		$src = esc_url_raw( $this->__get( 'video_url' ) );

		$attr = array(
			'id' => $this->__get( 'ID' ),
			'class' => 'video-js vjs-default-skin vjs-big-play-centered',
			'width' => $this->__get( 'video_player_width' ),
			'height' => $this->__get( 'video_player_height' ),
			'src' => $src,
			'data-setup' => $this->create_video_js_setup_data()
		);
		$attr = array_filter( $attr );

		$html = $this->create_html( 'video', $attr );

		return $html;
	}
}