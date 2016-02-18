<?php

class CoursePress_Model_PostFormats {

	private static $registered_formats = array();
	private static $registered_taxonomies = array();
	private static $prefix = '';

	public static function init() {

		add_action( 'init', array( __CLASS__, 'register_post_types' ) );

	}

	public static function register_post_types() {

		/**
		 * Override post type prefixes with COURSEPRESS_CPT_PREFIX defined in wp-config.php
		 *
		 * Use this to help with conflicts
		 */
		$prefix = defined( 'COURSEPRESS_CPT_PREFIX' ) ? COURSEPRESS_CPT_PREFIX : '';
		$prefix = empty( $prefix ) ? '' : sanitize_text_field( $prefix ) . '_';
		self::$prefix = $prefix;

		foreach ( self::_get_formats() as $format_class ) {
			if ( method_exists( 'CoursePress_Model_' . $format_class, 'get_format' ) ) {
				$format = call_user_func( 'CoursePress_Model_' . $format_class . '::get_format' );
				self::$registered_formats[] = self::prefix() . $format['post_type'];
				register_post_type( self::prefix() . $format['post_type'], $format['post_args'] );
			}
			if ( method_exists( 'CoursePress_Model_' . $format_class, 'get_taxonomy' ) ) {
				$format = call_user_func( 'CoursePress_Model_' . $format_class . '::get_taxonomy' );
				self::$registered_taxonomies[] = self::prefix() . $format['taxonomy_type'];
				register_taxonomy( self::prefix() . $format['taxonomy_type'], $format['post_type'], $format['taxonomy_args'] );
			}
		}

	}

	private static function _get_formats() {
		// Add the formats array in CoursePress_Core
		return apply_filters( 'coursepress_post_formats', array() );
	}

	public static function registered_formats() {
		return self::$registered_formats;
	}

	public static function prefix() {
		return self::$prefix;
	}
}
