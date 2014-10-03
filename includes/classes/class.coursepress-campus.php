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


if( ! class_exists( 'CoursePress_Campus' ) ) {

	/**
	 * CampusPress/Edublogs Specifics
	 *
	 * @since 1.2.1
	 *
	 */
	class CoursePress_Campus {
	
		
		function __construct() {
			
			// Administration area
			if ( is_admin() ) {
			}
			
			// Public area
			
			/**
			 * Remove CoursePress payable features.
			 *
			 * @since 1.2.1
			 */
			add_filter( 'coursepress_offer_paid_courses', array( &$this, 'remove_paid_feature' ) );
			
		}

		/**
		 * Remove CoursePress payable features.
		 *
		 * Users will no longer be able to offer courses for sale.
		 *
		 * @since 1.2.1
		 *
		 * @param bool $offer_paid Offer payable courses.
		 */
		function removed_paid_feature( $offer_paid ) {
			$offer_paid = false;
			return $offer_paid;
		}

	
	}	
}

if ( CoursePress_Capabilities::is_campus() ) {
	$coursepress_campus = new CoursePress_Campus();	
}
