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

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'CoursePress_Object' ) ) {

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
		
		const TYPE_COURSE = 'coursepress_course';
		const TYPE_UNIT   = 'coursepress_unit';
		const TYPE_MODULE = 'coursepress_module';
		
		protected $msg = 'Cool!';
		
		protected function load( $type, $key, &$object = null ) {
			$found = false;
			$object = wp_cache_get( $key, $type, false, $found );
			return $found;
		}
		
		protected function cache( $type, $key, $object ) {
			wp_cache_set( $key, $object, $type );
		}
		
		protected function kill( $type, $key ) {
			wp_cache_delete( $key, $type );
		}
	
		
		
		
	}
}