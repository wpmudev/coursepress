<?php
/**
 * Admin Students
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Admin_Students extends CoursePress_Admin_Controller_Menu {
	var $parent_slug = 'coursepress';
	var $slug = 'coursepress_students';
	var $with_editor = false;
	protected $cap = 'coursepress_students_cap';
	var $students_list = null;
	var $enrolled_courses = null;

	public function get_labels() {
		return array(
			'title' => __( 'CoursePress Students', 'cp' ),
			'menu_title' => __( 'Students', 'cp' ),
		);
	}

	public function process_form() {
		$this->switch_to_selected_course();

		if ( empty( $_REQUEST['view'] ) ) {
			// Set up students table
			$this->students_list = new CoursePress_Admin_Table_Students;
			$this->students_list->prepare_items();

			add_screen_option( 'per_page', array( 'default' => 20 ) );
		} else {
			$view = $_REQUEST['view'];
			$this->slug = 'student-' . $view;

			if ( 'profile' == $view ) {
				$student_id = (int) $_GET['student_id'];
				$this->enrolled_courses = new CoursePress_Admin_Table_Courses( $student_id );
				$this->enrolled_courses->prepare_items();
			}
		}
	}

	public function switch_to_selected_course() {
		if ( $this->is_valid_page() && ! empty( $_REQUEST['action'] ) && 'Filter' === $_REQUEST['action'] ) {
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
