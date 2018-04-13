<?php
/**
 * Class CoursePress_Admin_Actions
 *
 * Handles coursepress actions.
 *
 * @since 3.0.0
 * @package CoursePress
 */
class CoursePress_Admin_Actions {

	public function __construct() {
		// Hook to `admin_init` action hook to process action requests.
		add_action( 'admin_init', array( $this, 'process_action_request' ) );
		add_filter( 'post_type_link', array( $this, 'modify_module_discussion_link' ), 10, 2 );
		add_filter( 'posts_where', array( $this, 'modify_instructor_courselist' ) );
		add_filter( 'coursepress_pre_get_courses', array( $this, 'allow_filter_instructor_courselist' ) );
	}

	/**
	 * Allow filter for instructor courselist.
	 *
	 * @param  array $args
	 */
	public function allow_filter_instructor_courselist( $args ) {
		$user = new CoursePress_User( get_current_user_id() );
		if ( is_admin() && ( ! $user->is_super_admin() || ! CoursePress_Data_Capabilities::can_view_others_course() ) ) {
			$args['suppress_filters'] = false;
		}
		return $args;
	}

	/**
	 * Modify instructor courselist query.
	 * @param  string $where query.
	 */
	public function modify_instructor_courselist( $where ) {
		global $wpdb;
		$user = new CoursePress_User( get_current_user_id() );
		if ( is_admin() && ( ! $user->is_super_admin() || ! CoursePress_Data_Capabilities::can_view_others_course() ) ) {
			$find     = sprintf( "( %s.meta_key = 'instructor'", $wpdb->postmeta );
			$replace  = sprintf( " ( %s.post_author=%d ) OR ( %s.meta_key = 'instructor'", $wpdb->posts, $user->ID, $wpdb->postmeta );
			$where    = str_replace( $find, $replace, $where );
		}
		return $where;
	}

	/**
	 * Filters the permalink.
	 *
	 * @since 3.0.0
	 *
	 * @param string  $post_link The post's permalink.
	 * @param WP_Post $post      The post in question.
	 */
	public function modify_module_discussion_link( $post_link, $post ) {
		if ( ! is_admin() ) {
			return $post_link;
		}
		global $CoursePress_Core;
		$post_type = $CoursePress_Core->step_post_type;
		if ( $post_type === $post->post_type ) {
			$step = new CoursePress_step( $post );
			if ( 'discussion' === $step->module_type ) {
				return $step->get_permalink();
			}
		}
		return $post_link;
	}

	/**
	 * Callback method to process action request.
	 *
	 * Actions will be processed based on the `cp_action` param set.
	 * So if the request is `duplcate_course` it's corresponding method will be `duplcate_course`.
	 * For ajax requests please use `CoursePress_Admin_Ajax` class.
	 */
	public function process_action_request() {
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
	 * Export given course to JSON.
	 *
	 * @param array $request Request data.
	 */
	public function export_course( $request ) {
		// If course id found, export.
		if ( ! isset( $request['course_id'] ) || ! CoursePress_Data_Capabilities::can_update_course( $request['course_id'] ) ) {
			return;
		}
		// Set the export data using course id.
		$export = new CoursePress_Export();
		$export->export_course( $request['course_id'] );
	}

	/**
	 * Export given course to JSON.
	 *
	 * @param array $request Request data.
	 */
	public function export_courses( $request ) {
		if (
			isset( $request['coursepress'] )
			&& isset( $request['coursepress']['courses'] )
		) {
			$courses = array_keys( $request['coursepress']['courses'] );
			$export = new CoursePress_Export();
			$export->export_courses( $courses );
		}
	}
}
