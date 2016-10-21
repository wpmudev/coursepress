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
		add_filter( 'default_hidden_columns', array( __CLASS__, 'hidden_columns' ) );
	}

	public function get_labels() {
		return array(
			'title' => __( 'CoursePress Instructors', 'cp' ),
			'menu_title' => __( 'Instructors', 'cp' ),
		);
	}

	public function process_form() {
		/**
		 * Check actions
		 */
		$action = '';
		if ( isset( $_REQUEST['action'] ) ) {
			$action = $_REQUEST['action'];
		} elseif ( isset( $_REQUEST['action2'] ) ) {
			$action = $_REQUEST['action2'];
		}
		if ( isset( $_REQUEST['_wpnonce'] ) ) {
			$user_id = get_current_user_id();
			switch ( $action ) {
				/**
				 * Remove single instructor
				 */
				case 'remove_instructor':
					if ( ! isset( $_REQUEST['instructor_id'] ) ) {
						break;
					}
					$nonce_action = CoursePress_Data_Instructor::get_nonce_action( $action, $_REQUEST['instructor_id'] );
					if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $nonce_action ) ) {
						break;
					}
					CoursePress_Data_Instructor::remove_from_all_courses( $_REQUEST['instructor_id'] );
				break;
				/**
				 * Bulk action - remove instructors
				 */
				case 'withdraw':
					if ( ! isset( $_REQUEST['users'] ) ) {
						break;
					}
					if ( empty( $_REQUEST['users'] ) ) {
						break;
					}
					if ( ! is_array( $_REQUEST['users'] ) ) {
						break;
					}
					$nonce_action = 'bulk-users';
					if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $nonce_action ) ) {
						break;
					}
					$course_id = intval( isset( $_REQUEST['course_id'] )? $_REQUEST['course_id'] : 'all' );
					foreach ( $_REQUEST['users'] as $instructor_id ) {
						if ( 0 === $course_id ) {
							CoursePress_Data_Instructor::remove_from_all_courses( $instructor_id );
						} else {
							CoursePress_Data_Instructor::removed_from_course( $instructor_id, $course_id );
						}
					}
				break;
			}
		}
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

	/**
	 * Hide "courses_list" column by default
	 *
	 * @since 2.0.0
	 *
	 * @param array $columns List of hidden columns.
	 * @return array List of hidden columns.
	 */
	public static function hidden_columns( $columns ) {
		$screen = get_current_screen();
		if ( 'course_page_coursepress_instructors' != $screen->id ) {
			return;
		}
		array_push( $columns, 'courses_list' );
		return $columns;
	}
}
