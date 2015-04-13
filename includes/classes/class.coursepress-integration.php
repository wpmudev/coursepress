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
if ( ! class_exists( 'CoursePress_Integration' ) ) {

	/**
	 * CoursePress class for integrating with other plugins
	 *
	 * @since 1.2.2
	 *
	 */
	class CoursePress_Integration {

		private $data = array();

		function __construct() {

			// Ultimate Facebook (2.7.8+)
			add_action( 'wp_head', array( &$this, 'wp_head_ufb_integration' ) );

			// Post Voting Plugin (2.2.2+)
			add_filter( 'automatically_inject_voting_buttons', array( &$this, 'disable_post_voting_buttons' ) );

			// Initialise TinCan Integration
			//new CoursePress_TinCan();

		}

		/* ----- [BEGIN] ULTIMATE FACEBOOK ----- */

		/**
		 * Ultimate Facebook - OpenGraph integration.
		 *
		 * Applies UFB filters to alter OpenGraph details about a course.
		 *
		 * @since 1.2.2
		 */
		function wp_head_ufb_integration() {
			if ( 'course' == get_post_type() ) {
				$this->data['current_course'] = new Course( get_the_ID() ); // course already in object cache
				add_filter( 'wdfb-opengraph_apply_the_content_filter', array( &$this, 'ufb_remove_content_filter' ) );
				add_filter( 'wdfb-opengraph-image', array( &$this, 'ufb_opengraph_image' ) );
				add_filter( 'wdfb-opengraph-property', array( &$this, 'ufb_opengraph_property' ), 10, 3 );
			}
		}

		/**
		 * Avoid applying 'the_content' filter for OpenGraph image.
		 *
		 * This filter has a known bug, so we are just avoiding it since we don't
		 * want to pull the image from the content anyway (using 'Listing Image')
		 * instead.
		 *
		 * (Filter added in UFB 2.7.8)
		 *
		 * @param $boolean
		 *
		 * @since 1.2.2
		 *
		 * @return boolean Always false.
		 */
		function ufb_remove_content_filter( $boolean ) {
			return false;
		}

		/**
		 * Replaces Ultimate Facebook fallback image with 'Listing Image'.
		 *
		 * Using the fallback image because this will always be the case for Courses.
		 * If no 'Listing Image' is specified then it will use Ultimate Facebook's
		 * fallback image specified in Ultimate Facebook OpenGraph settings.
		 *
		 * @since 1.2.2
		 *
		 * @return string The image URL.
		 */
		function ufb_opengraph_image( $image ) {
			$list_image = get_post_meta( $this->data['current_course']->details->ID, 'featured_url', true );
			if ( ! empty( $list_image ) ) {
				$image = esc_html( $list_image );
			}

			return $image;
		}

		/**
		 * Ensure that the 'Course Excerpt' gets used for OpenGraph description.
		 *
		 * Ultimate Facebook should already take care of this, but we're just making sure.
		 *
		 * @param $meta The original meta to be added to <head>.
		 * @param $name The OpenGraph meta field.
		 * @param $value The value for the meta.
		 *
		 * @return string The meta to be added to <head>.
		 */
		function ufb_opengraph_property( $meta, $name, $value ) {

			if ( 'og:description' == $name ) {
				$value = $this->data['current_course']->details->post_excerpt;
				$meta  = '<meta content="' . esc_html( $value ) . '" property="og:description">';
			}

			return $meta;
		}

		/* ----- [END] ULTIMATE FACEBOOK ----- */

		/* ----- POST VOTING PLUGIN ---- */

		function disable_post_voting_buttons( $inject ) {

			global $post;

			$ignore_types = array( 'course', 'unit', 'virtual_page', 'discussions', 'module', 'certificates', 'notifications', 'module_response' );

			if( ! empty( $post ) && isset( $post->post_type ) && in_array( $post->post_type, $ignore_types) ) {
				return false;
			}

			$coursepress_slugs = array(
				CoursePress::instance()->get_enrollment_process_slug(),
				CoursePress::instance()->get_login_slug(),
				CoursePress::instance()->get_signup_slug(),
				CoursePress::instance()->get_student_dashboard_slug(),
				CoursePress::instance()->get_student_settings_slug(),
			);

			if( in_array( $post->post_name, $coursepress_slugs ) ) {
				return false;
			}

			return $inject;
		}

		/* ----- [END POST VOTING PLUGIN ---- */

	}

}

$coursepress_integration = new CoursePress_Integration();
