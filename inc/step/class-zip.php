<?php
/**
 * Class CoursePress_Step_Zip
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_Zip extends CoursePress_Step {
	protected $type = 'zipped';

	public function get_keys() {
		$keys = parent::get_keys();
		$keys = array_merge( $keys, array(
			'zip_url',
			'primary_file',
			'link_text',
		));

		return $keys;
	}

	public function get_question() {
		$zip_url = $this->__get( 'zip_url' );

		if ( ! empty( $zip_url ) ) {
			$link_text = $this->__get( 'link_text' );
			$primary_file = $this->__get( 'primary_file' );
			$zip_url = add_query_arg( array(
				'oacpf' => $this->__get( 'ID' ),
				'file' => $primary_file,
			), home_url() );

			if ( ! $link_text ) {
				$link_text = __( 'View Document', 'cp' );
			}

			if ( $this->is_preview() ) {
				$zip_url = '#';
			}

			$doc = sprintf( '<a href="%1$s" rel="nofollow" target="_blank">%2$s</a>', $zip_url, $link_text );

			return $doc;
		}
	}
}
