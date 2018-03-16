<?php

class CoursePress_Data_Schema {

	/**
	 * Init function
	 *
	 * @since 2.0.0
	 */
	public static function init() {
		// schema.org
		$schema_enabled = coursepress_is_true( coursepress_get_setting( 'general/add_structure_data', 1 ) );
		if ( $schema_enabled ) {
			/**
			 * Schema.org filter
			 *
			 * @param string $string value to change
			 * @param string $context context of usage
			 *
			 * @since 2.0.0
			 */
			add_filter( 'coursepress_schema', array( __CLASS__, 'add_schema' ), 10, 2 );
		}
	}

	/**
	 * Add schema information.
	 *
	 * @param string $string value to change
	 * @param string $context context of usage
	 *
	 * @since 2.0.0
	 *
	 * @return string $string Value improved by schema.
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
	 * @param string $title title or name
	 *
	 * @since 2.0.0
	 *
	 * @return string $string vValue wrapped by schema.
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
	 * @param string $item item value - unused
	 * @param string $itemtype type of item
	 *
	 * @since 2.0.0
	 *
	 * @return string $string Value improved by schema.
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
	 * @param string $item item value - unused
	 *
	 * @since 2.0.0
	 *
	 * @return string $string Value improved by schema.
	 */
	public static function description( $item ) {
		return ' itemprop="description"';
	}

	/**
	 * Schema function for image
	 *
	 * @param string $item item value - unused
	 *
	 * @since 2.0.0
	 *
	 * @return string $string Value improved by schema.
	 */
	public static function image( $item ) {
		return ' itemprop="image"';
	}
}
