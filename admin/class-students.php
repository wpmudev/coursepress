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

	public function __construct() {
		/** Send certificate manually **/
		add_action( 'wp_ajax_certificate_send', array( __CLASS__, 'certificate_send' ) );

		parent::__construct();
	}

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

	/**
	 * Get search nonce name.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Course ID.
	 * @return string Nonce name.
	 */
	public static function get_search_nonce_name( $course_id ) {
		return sprintf(
			'search-student-%d',
			$course_id
		);
	}

	/**
	 * Search Users
	 *
	 * @since 2.0.0
	 *
	 * @return array of results
	 */
	public static function search_user() {
		$results = array(
			'items' => array(),
			'total_count' => 0,
			'incomplete_results' => false,
		);

		if (
			! isset( $_GET['q'] )
			|| empty( $_GET['q'] )
			|| ! isset( $_GET['_wpnonce'] )
			|| empty( $_GET['_wpnonce'] )
			|| ! isset( $_GET['course_id'] )
			|| empty( $_GET['course_id'] )
		) {
			echo json_encode( $results );
			die;
		}

		$nonce_request = $_GET['_wpnonce'];
		$exclude = array();

		$course_id = (int) $_GET['course_id'];
		if ( wp_verify_nonce( $nonce_request, 'coursepress_search_users' ) ) {
			// Facilitator
			$exclude = CoursePress_Data_Facilitator::get_course_facilitators( $course_id );
		} elseif ( wp_verify_nonce( $nonce_request, 'coursepress_instructor_search' ) ) {
			// Instructors
			$exclude = CoursePress_Data_Course::get_setting( $course_id, 'instructors', array() );
			$exclude = array_filter( $exclude );
		} else {
			// Student
			$nonce = self::get_search_nonce_name( $_GET['course_id'] );
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], $nonce ) ) {
				echo json_encode( $results );
				die;
			}
			$exclude = CoursePress_Data_Course::get_students( $_GET['course_id'], 0, 0, 'ID' );
		}

		$search = $_GET['q'];
		if ( ! preg_match( '/\*/', $search ) ) {
			$search = sprintf( '%s*', $search );
		}
		$limit = 20;;

		// Search first_name, last_name
		$q = explode( ' ', strtolower( $_GET['q'] ) );
		$q = array_filter( $q );

		$args = array(
			'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => 'first_name',
				'value' => $q[0],
				'compare' => 'LIKE',
				)
			),
		);

		if ( count( $q ) > 1 ) {
			$args['meta_query'][] = array(
				'key' => 'last_name',
				'value' => $q[1],
				'compare' => 'LIKE',
			);
		}

		if ( ! empty( $exclude ) ) {
			$args['exclude'] = $exclude;
		}

		$user_query = new WP_User_Query( $args );

		// Search by last name
		$args['meta_query'] = array(
			'relation' => 'AND',
			array(
				'key' => 'last_name',
				'value' => $q[0],
				'compare' => 'LIKE',
			)
		);
		$query = new WP_User_Query( $args );

		if ( ! empty( $query->results ) ) {
			if ( ! empty( $user_query->results ) ) {
				$user_query->results += $query->results;
			} else {
				$user_query = $query;
			}
		}

		// Search using other keys
		$args2 = array(
			'search' => $search,
			'number' => $limit,
			'paged' => isset( $_GET['page'] )? intval( $_GET['page'] ) : 1,
			'search_columns' => array(
				'ID',
				'user_login',
				'user_nicename',
				'user_email',
			),
		);
		$user_query2 = new WP_User_Query( $args2 );

		if ( ! empty( $user_query2->results ) ) {
			if ( empty( $user_query->results ) ) {
				$user_query->results = array();
			}
			$user_query->results += $user_query2->results;
		}

		if ( ! empty( $user_query->results ) ) {
			foreach ( $user_query->results as $user ) {
				$results['items'][] = array(
					'id' => $user->ID,
					'text' => $user->display_name,
					'display_name' => CoursePress_Helper_Utility::get_user_name( $user->ID ),
					'user_login' => $user->user_login,
					'gravatar' => get_avatar( $user->ID, 30 ),
				);
			}
			$results['total_count'] = $user_query->total_users;
			$results['incomplete_results'] = $user_query->total_users > $limit;
		}
		echo json_encode( $results );
		die;
	}

	public static function certificate_send() {
		$results = array(
			'success' => false,
			'message' => __( 'Something went wrong. Sending failed.', 'cp' ),
			'step' => 'init',
		);
		/**
		 * Check data
		 */
		if (
			! isset( $_POST['id'] )
			|| empty( $_POST['id'] )
			|| ! isset( $_POST['_wpnonce'] )
			|| empty( $_POST['_wpnonce'] )
		) {
			$results['step'] = 'params';
			echo json_encode( $results );
			die;
		}
		/**
		 * verify nonce
		 */
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'send-certificate-'.$_POST['id'] ) ) {
			$results['step'] = 'nonce';
			echo json_encode( $results );
			die;
		}
		$result = CoursePress_Data_Certificate::send_certificate( $_POST['id'] );
		if ( $result ) {
			$parent_id = wp_get_post_parent_id( $_POST['id'] );
			$results = array(
				'success' => true,
				'message' => sprintf(
					'<div class="notice notice-success certificate-send"><p>%s</p></div>',
					sprintf(
						__( 'Certificate of <b>%s</b> has been sent.', 'cp' ),
						get_the_title( $parent_id )
					)
				),
			);
		}
		echo json_encode( $results );
		die;
	}
}
