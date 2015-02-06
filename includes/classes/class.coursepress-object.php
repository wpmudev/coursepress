<?php

/**
 * @copyright Incsub ( http://incsub.com/ )
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 ( GPL-2.0 )
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301 USA
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'CoursePress_Object' ) ) {

	/**
	 * CoursePress object class.
	 *
	 * Add's features to CoursePress objects (for example caching).
	 *
	 * @since 1.2.1
	 *
	 * @return object
	 */
	class CoursePress_Object {

		// Primary CoursePress types
		const TYPE_COURSE = 'coursepress_course';
		const TYPE_UNIT = 'coursepress_unit';
		const TYPE_MODULE = 'coursepress_module';
		const TYPE_MODULE_RESPONSE = 'coursepress_module_response';
		const TYPE_UNIT_MODULES = 'coursepress_unit_modules';
		const TYPE_UNIT_STATIC = 'coursepress_unit_static';

		protected static function load( $type, $key, &$object = null ) {
			$found = false;
			// USE OBJECT CACHE
			$object = wp_cache_get( $key, $type, false, $found );
			return $found;
		}

		protected static function cache( $type, $key, $object ) {
			if ( ! empty( $key ) ) {
				// USE OBJECT CACHE
				wp_cache_set( $key, $object, $type );
			}
		}

		protected static function kill( $type, $key ) {
			// REMOVE OBJECT CACHE OBJECT
			wp_cache_delete( $key, $type );
		}

		protected static function kill_related( $type, $key ) {
			switch ( $type ) {

				case self::TYPE_COURSE:
					// Course related caches to kill
					self::kill( self::TYPE_UNIT_STATIC, 'list-publish-' . $key );
					self::kill( self::TYPE_UNIT_STATIC, 'list-any-' . $key );
					self::kill( self::TYPE_UNIT_STATIC, 'object-publish-' . $key );
					self::kill( self::TYPE_UNIT_STATIC, 'object-any-' . $key );
					break;
			}
		}

	}

}