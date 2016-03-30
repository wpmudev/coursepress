<?php

class CoursePress_Helper_Schema {

	public static function init() {
		/**
		 * schema.org
		 */
		$add_schema = cp_is_true( CoursePress_Core::get_setting( 'general/add_structure_data', 1 ) );

		if ( ! $add_schema ) {
			return;
		}

		add_filter(
			'coursepress_schema',
			array( __CLASS__, 'add_schema' ),
			10, 2
		);
	}

	public static function add_schema( $string, $context ) {

		switch ( $context ) {
			case 'title':
			return self::itemprop_name( $string );
			case 'itemscope':
			return self::itemscope( $string );
			case 'itemscope-person':
			return self::itemscope( $string, 'Person' );
			case 'description':
			return self::description( $string );
			case 'image':
			return self::image( $string );
		}

		return $string;
	}

	public static function itemprop_name( $title ) {
		if ( empty( $title ) ) {
			return $title;
		}
		return sprintf( '<span itemprop="name">%s</span>', $title );
	}

	public static function itemscope( $item, $itemtype = 'Product' ) {
		return sprintf(
			' itemscope itemtype="http://schema.org/%s"',
			esc_attr( $itemtype )
		);
	}

	public static function description( $item ) {
		return ' itemprop="description"';
	}

	public static function image( $item ) {
		return ' itemprop="image"';
	}
}
