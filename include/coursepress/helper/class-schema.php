<?php

class CoursePress_Helper_Schema {

	/**
	 * Init function
	 *
	 * @since 2.0.0
	 */
	public static function init() {
		/**
		 * schema.org
		 */
		$add_schema = cp_is_true( CoursePress_Core::get_setting( 'general/add_structure_data', 1 ) );

		if ( ! $add_schema ) {
			return;
		}

		/**
		 * Schema filter
		 *
		 * @since 2.0.0
		 *
		 * @param string $string value to change
		 * @param string $context context of usage
		 *
		 */
		add_filter(
			'coursepress_schema',
			array( __CLASS__, 'add_schema' ),
			10, 2
		);
	}

	/**
	 * Add schema information
	 *
	 * @since 2.0.0
	 *
	 * @param string $string value to change
	 * @param string $context context of usage
	 *
	 * @return $string value improved by schema
	 */
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

	/**
	 * Add schema information
	 *
	 * @since 2.0.0
	 *
	 * @param string $title title or name
	 *
	 * @return $string value wrapped by schema
	 */
	public static function itemprop_name( $title ) {
		if ( empty( $title ) ) {
			return $title;
		}
		return sprintf( '<span itemprop="name">%s</span>', $title );
	}

	/**
	 * Schema function for itemscope
	 *
	 * @since 2.0.0
	 *
	 * @param string $item item value - unused
	 * @param string $itemtype type of item
	 *
	 * @return $string value improved by schema
	 */
	public static function itemscope( $item, $itemtype = 'Product' ) {
		return sprintf(
			' itemscope itemtype="http://schema.org/%s"',
			esc_attr( $itemtype )
		);
	}

	/**
	 * Schema function for description
	 *
	 * @since 2.0.0
	 *
	 * @param string $item item value - unused
	 *
	 * @return $string value improved by schema
	 */
	public static function description( $item ) {
		return ' itemprop="description"';
	}

	/**
	 * Schema function for image
	 *
	 * @since 2.0.0
	 *
	 * @param string $item item value - unused
	 *
	 * @return $string value improved by schema
	 */
	public static function image( $item ) {
		return ' itemprop="image"';
	}
}
