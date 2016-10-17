<?php
/**
 * Instructors Class
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Admin_Instructors extends CoursePress_Admin_Controller_Menu {
	var $parent_slug = 'coursepress';
	var $slug = 'coursepress_instructors';
	var $with_editor = false;
	protected $cap = 'coursepress_settings_cap';
	var $instructors_list;

	public function get_labels() {
		return array(
			'title' => __( 'CoursePress Instructors', 'cp' ),
			'menu_title' => __( 'Instructors', 'cp' ),
		);
	}

	public function process_form() {
		$this->switch_to_selected_course();

		if ( empty( $_REQUEST['view'] ) ) {
			// Set up instructors table
			$this->instructors_list = new CoursePress_Admin_Table_Instructors;
			$this->instructors_list->prepare_items();

			add_screen_option( 'per_page', array( 'default' => 20 ) );
		} else {
			$view = $_REQUEST['view'];
			$this->slug = 'instructor-' . $view;
		}
	}

	public function switch_to_selected_course() {
		if ( ! empty( $_REQUEST['action'] ) && 'Filter' === $_REQUEST['action'] ) {
			$return_url = remove_query_arg(
				array(
					'view',
					'course_id',
					'student_id',
				)
			);

			if ( (int) ( $_REQUEST['course_id'] ) > 0 ) {
				$course_id = (int) $_REQUEST['course_id'];
				$return_url = add_query_arg( 'course_id', $course_id );
			}
			wp_safe_redirect( $return_url );
			exit;
		}
	}
}
