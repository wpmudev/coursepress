<?php
/**
 * @copyright 2014 Incsub (http://incsub.com)
 * @author WPMU Dev
 * @license    http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 (GPL-2.0)
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
 * Hooks Course actions and filters according to Course settings.
 */
class Course_Settings {

	function __construct() {
		add_filter( 'coursepress_course_enrollment_types', array( &$this, 'enrollment_types' ) );

	}

	public function enrollment_types( $enrollment_types ) {

		if ( cp_user_can_register() ) {
			$enrollment_types['anyone']       = __( 'Anyone ', 'cp' );
			$enrollment_types['passcode']     = __( 'Anyone with a pass code', 'cp' );
			$enrollment_types['prerequisite'] = __( 'Anyone who completed the prerequisite course', 'cp' );
		} else {
			$enrollment_types['registered']   = __( 'Registered User', 'cp' );
			$enrollment_types['passcode']     = __( 'Registered user with a pass code', 'cp' );
			$enrollment_types['prerequisite'] = __( 'Registered user who completed the prerequisite course', 'cp' );
		}

		return $enrollment_types;
	}


}

new Course_Settings();