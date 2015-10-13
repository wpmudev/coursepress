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

if ( ! class_exists( 'CoursePress_Cache' ) ) {

	/**
	 * CoursePress object class.
	 *
	 * Provide similar feature to WP_Object_Cache with the difference that the cache is always non-persistent.
	 * WP_Object_Cache may cause sync issues if there is a caching plugin in place that doesn't implement wp_cache_add_non_persistent_groups().
	 *
	 * @since 1.2.6.4
	 *
	 * @return object
	 */
	class CoursePress_Cache {
		public static $cache = array();

		public static function cp_cache_set($key, $value){
			if(empty($key)) return false;

			//Note that we don't need to differentiate the key between blogs for multisite because this method is not aimed to be persistent.
			CoursePress_Cache::$cache[$key] = $value;

			return true;
		}

		public static function cp_cache_get($key){
			//return false;
			if(empty($key)) return false;

			//Note that we don't need to differentiate the key between blogs for multisite because this method is not aimed to be persistent.
			if( isset( CoursePress_Cache::$cache[$key] ) ){
				return CoursePress_Cache::$cache[$key];
			}

			return false;
		}

		public static function cp_cache_purge($key = null){
			if(!empty($key)){
				unset( CoursePress_Cache::$cache[$key] );
			} else {
				CoursePress_Cache::$cache = array();
			}

			return true;
		}

	}

}