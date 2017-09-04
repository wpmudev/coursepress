<?php
/**
 * Class CoursePress_Admin_Actions
 *
 * Handles coursepress actions.
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Actions {

	public function __construct() {

		// Hook to `admin_init` action hook to process action requests.
		add_action( 'admin_init', array( $this, 'process_action_request' ) );
	}

	/**
	 * Callback method to process action request.
	 *
	 * Actions will be processed based on the `cp_action` param set.
	 * So if the request is `duplcate_course` it's corresponding method will be `duplcate_course`.
	 * For ajax requests please use `CoursePress_Admin_Ajax` class.
	 */
	function process_action_request() {

		$request = $_REQUEST;

		// Continue only if cp_action is set, nonce found and not ajax in request.
		if ( empty( $request['cp_action'] ) || empty( $request['_wpnonce'] ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		$action = $request['cp_action'];

		// Security check.
		if ( wp_verify_nonce( $request['_wpnonce'], $action ) ) {

			// Call the corresponding method for the action.
			if ( method_exists( $this, $action ) ) {
				call_user_func( array( $this, $action ), $request );
			}
		}
	}

	/**
	 * Duplicate given course.
	 *
	 * @param array $request Request data.
	 */
	function duplicate_course( $request ) {

		if ( isset( $request['course_id'] ) ) {
			$course = coursepress_get_course( $request['course_id'] );
			if ( ! is_wp_error( $course ) ) {
				$course->duplicate_course();
			}
		}

		// Redirect back to same page without unwanted parameters.
		wp_redirect( remove_query_arg( array( 'cp_nonce', 'course_id', 'cp_action' ) ) );
	}

	/**
	 * Export given course to JSON.
	 *
	 * @param array $request Request data.
	 */
	function export_course( $request ) {

		// If course id found, export.
		if ( isset( $request['course_id'] ) ) {
			// Set the export data using course id.
			$export = new CoursePress_Export( $request['course_id'] );
			$export->export();
		}

		// Redirect back to same page without unwanted parameters.
		wp_redirect( remove_query_arg( array( 'cp_nonce', 'course_id', 'cp_action' ) ) );
	}
}