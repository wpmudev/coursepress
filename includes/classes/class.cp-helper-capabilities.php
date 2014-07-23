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

/**
 * Helper class for getting capabilities.
 *
 * @since 1.0.0
 *
 * @return object
 */
class CP_Helper_Capabilities {

	/**
	 * Can the user create a course?  
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */	
	public static function can_create_course( $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		
		return user_can( $user_id, 'coursepress_create_course_cap' );
	}		
	
	/**
	 * Can the user update this course? 
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */	
	public static function can_update_course( $course_id, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
				
		$my_course = self::is_course_instructor( $course_id, $user_id );	
		
		return ($my_course && user_can( $user_id, 'coursepress_update_my_course_cap' ) ) || user_can( $user_id, 'coursepress_update_course_cap' ) ? true : false;
	}

	/**
	 * Can the user delete this course? 
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */	
	public static function can_delete_course( $course_id, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
				
		$my_course = self::is_course_instructor( $course_id, $user_id );	
		
		return ($my_course && user_can( $user_id, 'coursepress_delete_my_course_cap' ) ) || user_can( $user_id, 'coursepress_delete_course_cap' ) ? true : false;
	}

	/**
	 * Can the user change the course status? 
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */	
	public static function can_change_course_status( $course_id, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
				
		$my_course = self::is_course_instructor( $course_id, $user_id );	
		
		return ($my_course && user_can( $user_id, 'coursepress_change_my_course_status_cap' ) ) || user_can( $user_id, 'coursepress_change_course_status_cap' ) ? true : false;
	}	

	/**
	 * Can the user create units? 
	 *
	 * @since 1.0.0
	 *
	 *
	 * @return bool
	 */	
	public static function can_create_unit( $user_id = '' ) {		
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		
		return user_can( $user_id, 'coursepress_create_course_unit_cap' ) ? true : false;
	}

	/**
	 * Can the user create units in this course? 
	 *
	 * @since 1.0.0
	 *
	 *
	 * @return bool
	 */	
	public static function can_create_course_unit( $course_id, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$can_update_course = self::can_update_course( $course_id, $user_id );
		$can_create_units = self::can_create_unit( $user_id );
		
		return ( $can_update_course && $can_create_units ) ? true : false;
	}


	/**
	 * Can the user view units? 
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */	
	public static function can_view_course_units( $course_id, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
				
		$my_course = self::is_course_instructor( $course_id, $user_id );	
		
		return ( $my_course || user_can( $user_id, 'coursepress_view_all_units_cap' ) ) ? true : false;
	}		
	
	
	/**
	 * Can the user update the units? 
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */	
	public static function can_update_course_unit( $course_id, $unit_id ='', $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		
		$my_unit = self::is_unit_creator( $unit_id, $user_id );				
		$my_course = self::is_course_instructor( $course_id, $user_id );	
		
		return ( $my_course && ( ( $my_unit && user_can( $user_id, 'coursepress_update_my_course_unit_cap' ) ) || user_can( $user_id, 'coursepress_update_course_unit_cap' ) ) ) || user_can( $user_id, 'coursepress_update_all_courses_unit_cap' ) ? true : false;
	}		
	
	/**
	 * Can the user delete the units? 
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */	
	public static function can_delete_course_unit( $course_id, $unit_id ='', $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		
		$my_unit = self::is_unit_creator( $unit_id, $user_id );								
		$my_course = self::is_course_instructor( $course_id, $user_id );	
		
		return ( $my_course && ( ( $my_unit && user_can( $user_id, 'coursepress_delete_my_course_units_cap' ) ) || user_can( $user_id, 'coursepress_delete_course_units_cap' ) ) ) || user_can( $user_id, 'coursepress_delete_all_courses_units_cap' ) ? true : false;
	}		

	/**
	 * Can the user change the unit state? 
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */	
	public static function can_change_course_unit_status( $course_id, $unit_id ='', $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
				
		$my_unit = self::is_unit_creator( $unit_id, $user_id );								
		$my_course = self::is_course_instructor( $course_id, $user_id );		
		
		return ( $my_course && ( ( $my_unit && user_can( $user_id, 'coursepress_change_my_course_unit_status_cap' ) ) || user_can( $user_id, 'coursepress_change_course_unit_status_cap' ) ) ) || user_can( $user_id, 'coursepress_change_all_courses_unit_status_cap' ) ? true : false;
	}		


	/**
	 * Is the user an instructor of this course?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */	
	public static function is_course_instructor( $course_id, $user_id = '' ){
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		
		$instructor = new Instructor( $user_id );
		$instructor_courses = $instructor->get_assigned_courses_ids();

		return in_array( $course_id, $instructor_courses );	
	}
	
	/**
	 * Is the user the unit author?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */	
	public static function is_unit_creator( $unit_id = '', $user_id = '' ){
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		
		if( empty( $unit_id ) ) {
			return false;
		} else {
			return $user_id == get_post_field( 'post_author', $unit_id ) ? true : false;
		}
	}
	
	
	
}