<?php

class CoursePress_View_Admin_Student {
	private static $slug = 'coursepress_students';
	private static $title = '';
	private static $menu_title = '';
	private static $table_manager = null;

	public static function init() {
		self::$title = __( 'Courses/Students', 'coursepress' );
		self::$menu_title = __( 'Students', 'coursepress' );

		add_filter(
			'coursepress_admin_valid_pages',
			array( __CLASS__, 'add_valid' )
		);
		add_filter(
			'coursepress_admin_pages',
			array( __CLASS__, 'add_page' )
		);

		add_filter(
			'coursepress_admin_valid_pages',
			array( __CLASS__, 'add_valid' )
		);
		add_action(
			'coursepress_admin_' . self::$slug,
			array( __CLASS__, 'render_page' )
		);

		add_action(
			'coursepress_settings_page_pre_render_' . self::$slug,
			array( __CLASS__, 'pre_process' )
		);

		// Search Users
		add_action( 'wp_ajax_coursepress_user_search', array( __CLASS__, 'search_user' ) );

		/** Send certificate manually **/
		add_action( 'wp_ajax_certificate_send', array( __CLASS__, 'certificate_send' ) );
	}

	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;

		return $valid_pages;
	}

	public static function add_page( $pages ) {
		$pages[ self::$slug ] = array(
			'title' => self::$title,
			'menu_title' => self::$menu_title,
			'cap' => self::$slug . '_cap',
			'order' => 20,
		);

		return $pages;
	}

	public static function pre_process() {
		$view = ! empty( $_GET['view'] ) ? $_GET['view'] : '';

		if ( empty( $view ) ) {
			self::$table_manager = new CoursePress_Helper_Table_Student;
			self::$table_manager->prepare_items();
		}
	}

	public static function render_page() {
		$view = ! empty( $_GET['view'] ) ? $_GET['view'] : '';

		if ( empty( $view ) ) {
			self::$table_manager->display();
		} elseif ( 'workbook' == $view ) {
			CoursePress_View_Admin_Student_Workbook::display();
		} elseif ( 'profile' == $view ) {
			CoursePress_View_Admin_Student_Profile::display();
		}
	}

	/**
	 * return slug.
	 *
	 * @since 2.0.0
	 *
	 * @return string slug
	 */
	public static function get_slug() {
		return self::$slug;
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

	public static function certificate_send() {
		$results = array(
			'success' => false,
			'message' => __( 'Something went wrong. Sending failed.', 'coursepress' ),
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
						__( 'Certificate of <b>%s</b> has been sent.', 'coursepress' ),
						get_the_title( $parent_id )
					)
				),
			);
		}
		echo json_encode( $results );
		die;
	}
}
