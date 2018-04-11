<?php
/**
 * Class CoursePress_Step_FileDownload
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_FileDownload extends CoursePress_Step {
	protected $type = 'filedownload';

	function get_keys() {
		$keys = parent::get_keys();
		$keys = array_merge( $keys, array(
			'file_url',
			'link_text',
		));

		return $keys;
	}

	function get_question() {
		$file_url = $this->__get( 'file_url' );

		if ( ! empty( $file_url ) ) {
			$file_url = esc_url( $file_url );
			$link_text = __( 'Download', 'cp' );
			$custom_link_text = $this->__get( 'link_text' );

			if ( ! empty( $custom_link_text ) ) {
				$link_text = $custom_link_text;
			}

			$download = coursepress_create_html(
				'a',
				array(
					'href' => $file_url,
					'alt' => basename( $file_url ),
				),
				$link_text
			);

			return $download;
		}
	}
}