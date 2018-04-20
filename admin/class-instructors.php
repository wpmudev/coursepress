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

	public static function init() {

	}

	public function get_labels() {

		add_action(
			'admin_init',
			array( $this, 'process_action' ),
			20
		);

		return array(
			'title' => __( 'CoursePress Instructors', 'coursepress' ),
			'menu_title' => __( 'Instructors', 'coursepress' ),
		);
	}

	public function process_form() {
		$this->switch_to_selected_course();
		if ( empty( $_REQUEST['view'] ) ) {
			// Set up instructors table
			$this->instructors_list = new CoursePress_Admin_Table_Instructors;
			$this->instructors_list->prepare_items();
			add_screen_option( 'per_page', array( 'default' => 20, 'option' => 'coursepress_instructors_per_page', 'label' => __( 'Number of instructors per page', 'coursepress' ) ) );

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

	public function process_action() {
		if ( isset( $_REQUEST['action'] ) && ! empty( $_REQUEST['action'] ) && isset( $_REQUEST['instructor_id'] ) && ! empty( $_REQUEST['instructor_id'] ) ) {
			$data = ! empty( $_REQUEST ) ? stripslashes_deep( $_REQUEST ) : array();
			$instructor_id = (int) $data['instructor_id'];
			$return_url = add_query_arg(
				array(
					'post_type' => 'course',
					'page' => 'coursepress_instructors',
				),
				admin_url( 'edit.php' )
			);
			switch ( $data['action'] ) {
				case 'delete':
					if ( isset( $data['_wpnonce'] ) && wp_verify_nonce( $data['_wpnonce'], 'coursepress_remove_instructor' ) ) {
						if ( isset( $data['course_id'] ) && ! empty( $data['course_id'] ) ) {
							// remove from this course
							CoursePress_Data_Course::remove_instructor( (int) $data['course_id'], $instructor_id );
						} else {
							// remove from all courses associated
							$instructor = get_userdata( $instructor_id );
							$assigned_courses_ids = CoursePress_Data_Instructor::get_assigned_courses_ids( $instructor );
							foreach ( $assigned_courses_ids as $course_id ) {
								CoursePress_Data_Course::remove_instructor( (int) $course_id, $instructor_id );
							}
						}
						/**
					 * remove user meta
					 */
						$role_name = CoursePress_Data_Capabilities::get_role_instructor_name();
						delete_user_meta( $instructor_id, $role_name );
						/**
					 * Back to instructors page
					 */
						wp_safe_redirect( $return_url );
						exit;
					}
				break;
			}
		}
	}
}
