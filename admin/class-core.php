<?php
/**
 * Course Admin Core class.
 *
 * This class is responsible on loading admin related pages.
 *
 * @class CoursePress_Admin_Core
 * @version 2.0.x
 **/
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'CoursePress_Admin_Core' ) ) :
	class CoursePress_Admin_Core {
		/**
		 * List of valid admin pages.
		 *
		 * @var (array)
		 **/
		static $valid_pages = array( 'coursepress' );

		/**
		 * The current valid pages.
		 *
		 * @var (string) $valid_page
		 **/
		static $valid_page = null;

		/**
		 * List of classes that are included in admin area.
		 *
		 * @var (array)
		 **/
		static $css = array();

		/**
		 * List of scripts that are included in admin area.
		 *
		 * @var (array)
		 **/
		static $scripts = array();

		/**
		 * List of external stylehsheets that are included in admin area.
		 *
		 * @var (array)
		 **/
		static $external_css = array();

		 /**
		  * List of external scripts that are included in admin area.
		  *
		  * @var (array)
		  **/
		 static $external_scripts = array();

		/**
		 * Add css to the list
		 *
		 * @since 2.0.x
		 *
		 * @param (string) $id
		 * @param (string) $src
		 * @param (boolean) $external
		 **/
		public static function add_css( $id, $src, $external = false ) {
			if ( $exteral ) {
				self::$external_css[ $id ] = $src;
			} else {
				self::$css[ $id ] = $src;
			}
		}

		/**
		 * Add script to the list.
		 *
		 * @since 2.0.x
		 *
		 * @param (string) $id
		 * @param (string) $src
		 * @param (boolean) $external
		 **/
		public static function add_script( $id, $src, $external = false ) {
			if ( $external ) {
				self::$external_scripts[ $id ] = $src;
			} else {
				self::$scripts[ $id ] = $src;
			}
		}

		/**
		 * Adds valid page to the list of admin valid pages.
		 *
		 * @since 2.0.x
		 *
		 * @param (string) $valid_page
		 **/
		public static function add_valid_page( $valid_page ) {
			array_push( self::$valid_pages, $valid_page );
		}

		/**
		 * Check if the current loaded page is valid CP page.
		 *
		 * @param (string) $current_page
		 **/
		public static function is_valid_page( $current_page ) {
			/**
			 * Filter the list of valid pages before validating.
			 *
			 * @since 2.0.x
			 *
			 * @param (array) $valid_pages
			 **/
			$valid_pages = apply_filters( 'coursepress_admin_valid_pages', self::$valid_pages );

			$is_valid = in_array( $current_page, $valid_pages );

			if ( $is_valid ) {
				self::$valid_page = $current_page;
			}

			return $is_valid;
		}

		public static function load_scripts() {
			global $pagenow, $typenow;

			if ( ! self::is_valid_page( $pagenow ) || ! self::is_valid_page( $typenow ) ) {
				// Not CP page? return!
				return;
			}

			$url = CoursePress::$url;
			$version = CoursePress::$version;

			// Load main admin JS
			wp_enqueue_script( 'coursepress-admin', $url . 'assets/js/admin.min.js', array( 'jquery', 'backbone', 'underscore' ), $version );
		}
	}
endif;
