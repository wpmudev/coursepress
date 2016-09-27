<?php
/**
 * Students Table
 *
 * This class extends WP_Users_List_Table to manage courses students.
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Admin_Table_Students extends CoursePress_Admin_Table_Instructors {
	static $student_progress = array();

	public function prepare_items() {
		$usersearch = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
		$per_page = ( $this->is_site_users ) ? 'site_users_network_per_page' : 'users_per_page';
		$users_per_page = $this->get_items_per_page( $per_page );

		$paged = $this->get_pagenum();
		$args = array(
			'number' => $users_per_page,
			'offset' => ( $paged-1 ) * $users_per_page,
			'meta_key' => 'role',
			'meta_value' => 'student',
			'fields' => 'all_with_meta',
			'search' => $usersearch,
		);

		if ( '' !== $args['search'] )
			$args['search'] = '*' . $args['search'] . '*';

		if ( $this->is_site_users )
			$args['blog_id'] = $this->site_id;

		// Query the user IDs for this page
		$wp_user_search = new WP_User_Query( $args );

		$this->items = $wp_user_search->get_results();

		$this->set_pagination_args( array(
			'total_items' => $wp_user_search->get_total(),
			'per_page' => $users_per_page,
		) );
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'student_name' => __( 'Name', 'cp' ),
			'last_login' => __( 'Last Login', 'cp' ),
			'courses' => __( 'Courses', 'cp' ),
		);

		if ( ! empty( $this->course_id ) ) {
			unset( $columns['courses'] );
			$columns['average'] = __( 'Average', 'cp' );
			$columns['status'] = __( 'Status', 'cp' );
		}

		return $columns;
	}

	public function get_bulk_actions() {
		$actions = array();

		// @todo: add sanity check
		$actions['withdraw'] = __( 'Withdraw', 'cp' );

		return $actions;
	}

	protected function pagination( $which ) {
		// Show pagination only at the bottom
		if ( 'top' !== $which ) {
			parent::pagination( $which );
		} else {
			$this->search_box( __( 'Search Students', 'cp' ), 'search' );
		}
	}

	public function column_courses( $user_id ) {
		$courses = CoursePress_Data_Student::count_enrolled_courses_ids( $user_id );
		$profile_link = add_query_arg(
			array( 'view' => 'profile', 'student_id' => $user_id )
		);

		return sprintf( '<a href="%s">%s</a>', $profile_link, $courses );
	}

	public function column_student_name( $user_id ) {
		$user = get_userdata( $user_id );

		// User avatar
		$avatar = get_avatar( $user->user_email, 32 );
		$name = CoursePress_Helper_Utility::get_user_name( $user_id, true );

		// Generate row actions
		$actions = array();
		$url = remove_query_arg(
			array(
				'view',
				'_wpnonce',
				'student_id',
			)
		);

		// @todo: Add sanity check/validation
		$courses_url = add_query_arg(
			array(
				'view' => 'profile',
				'student_id' => $user_id,
			)
		);
		$actions['courses'] = sprintf( '<a href="%s">%s</a>', esc_url( $courses_url ), __( 'View Profile', 'cp' ) );

		// @todo: Add sanity check/validation
		$delete_url = add_query_arg(
			array(
				'_wpnonce' => wp_create_nonce( 'coursepress_withdraw_student' ),
				'student_id' => $user_id,
			)
		);
		$withdraw_title = __( 'Withdraw to all courses', 'cp' );

		if ( ! empty( $this->course_id ) ) {
			$withdraw_title = __( 'Withdraw', 'cp' );
		}
		$actions['delete'] = sprintf( '<a href="%s">%s</a>', esc_url( $delete_url ), $withdraw_title );

		return $avatar . $name . $this->row_actions( $actions );
	}

	public function column_last_login( $user_id ) {
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$last_activity = get_user_meta( $user_id, 'latest_activity', true );

		if ( empty( $last_activity ) ) {
			$return = '-';
		} else {
			$return = date_i18n( $date_format . ' ' . $time_format, CoursePress_Data_Course::strtotime( $last_activity ) );
		}

		return $return;
	}

	public function column_average( $user_id ) {
		if ( empty( self::$student_progress ) ) {
			self::$student_progress = CoursePress_Data_Student::get_completion_data( $user_id, $this->course_id );
		}
		$average = CoursePress_Helper_Utility::get_array_val(
			self::$student_progress,
			'completion/average'
		);

		return (float) $average . '%';
	}

	public function column_status( $user_id ) {
		$status = CoursePress_Data_Student::get_course_status( $this->course_id );

		return $status;
	}
}
