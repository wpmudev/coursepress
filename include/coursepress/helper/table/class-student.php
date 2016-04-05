<?php
if( ! class_exists( 'WP_Users_List_Table' ) ) {
	require ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php';
}

class CoursePress_Helper_Table_Student extends WP_Users_List_Table {
	public function prepare_items() {
		add_filter( 'manage_users_custom_column', array( __CLASS__, 'custom_columns' ), 10, 3 );
		add_filter( 'users_list_table_query_args', array( __CLASS__, 'filter_args' ) );
		add_filter( 'user_row_actions', array( __CLASS__, 'user_row_actions' ), 10, 2 );

		self::delete_student();
		parent::prepare_items();
	}

	public static function filter_args( $args ) {
		$args['meta_value'] = 'student';

		return $args;
	}

	public static function user_row_actions( $actions, $user_object ) {
		$profile_link = add_query_arg(
			array( 'view' => 'profile', 'student_id' => $user_object->ID )
		);
		$workbook_link = add_query_arg(
			array( 'view' => 'workbook', 'student_id' => $user_object->ID )
		);
		$delete_link = add_query_arg(
			array(
				'student_id' => $user_object->ID,
				'nonce' => wp_create_nonce( 'coursepress_remove_student' ),
				)
		);
		$actions = array(
			'profile' => sprintf( '<a href="%s">%s</a>', $profile_link, __( 'Profile', 'CP_TD' ) ),
			'workbook' => sprintf( '<a href="%s">%s</a>', $workbook_link, __( 'Workbook', 'CP_TD' ) ),
			'delete' => sprintf( '<a href="%s">%s</a>', $delete_link, __( 'Remove', 'CP_TD' ) )
		);

		return $actions;
	}

	/**
	 * Withdraw student to all courses
	 **/
	public static function delete_student() {
		if ( isset( $_GET['nonce'] )
			&& wp_verify_nonce( $_GET['nonce'], 'coursepress_remove_student' )
			&& isset( $_GET['student_id'] ) )
		{
			$student_id = (int) $_GET['student_id'];
			$courses = CoursePress_Data_Student::get_enrolled_courses_ids( $student_id );

			foreach ( $courses as $course_id ) {
				CoursePress_Data_Course::withdraw_student( $student_id, $course_id );
			}

			// Return to student's list.
			$return_url = remove_query_arg(
				array(
					'view',
					'student_id',
					'nonce',
				)
			);
			wp_safe_redirect( $return_url ); exit;
		}
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'username' => __( 'Username', 'CP_TD' ),
			'name' => __( 'Name', 'CP_TD' ),
			'registered' => __( 'Registered', 'CP_TD' ),
			'last_activity' => __( 'Last Activity', 'CP_TD' ),
			'courses' => __( 'Courses', 'CP_TD' ),
		);

		if ( ! CoursePress_Data_Capabilities::can_delete_student() ) {
			unset( $columns['remove'] );
		}
		return $columns;
	}

	public static function custom_columns( $null, $column, $user_id ) {
		$student = get_userdata( $user_id );
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$return = '';

		switch( $column ) {
			case 'id':
				$return = $user_id;
				break;

			case 'registered':
				$return = date_i18n( $date_format, strtotime( $student->user_registered, current_time( 'timestamp' ) ) );
				break;

			case 'last_activity':
				$last_activity = get_user_meta( $user_id, 'latest_activity', true );
				$return = date_i18n( $date_format . ' ' . $time_format, strtotime( $last_activity, current_time( 'timestamp' ) ) );
				break;

			case 'courses':
				$courses = CoursePress_Data_Student::get_enrolled_courses_ids( $user_id );
				$profile_link = add_query_arg(
					array( 'view' => 'profile', 'student_id' => $user_id )
				);
				$return = sprintf( '<a href="%s">%s</a>', $profile_link, count( $courses ) );
				break;

		}

		return $return;
	}

	public function extra_tablenav( $which ) { return; }

	public function no_items() {
		esc_html_e( 'No students found.', 'CP_TD' );
	}

	public function display() {
		?>
		<div class="wrap">
			<h2>
				<?php
					esc_html_e( 'Students', 'CP_TD' );

					if ( CoursePress_Data_Capabilities::can_create_student() ) {
						$add_link = admin_url( 'user-new.php' );
				?>
					<a href="<?php echo $add_link; ?>" class="add-new-h2">
						<?php
							esc_html_e( 'Add New', 'CP_TD' );
						?>
					</a>
				<?php
					}
				?>
			</h2>
			<hr />
			<form method="post">
				<?php parent::display(); ?>
			</form>
		</div>
		<?php
	}
}
