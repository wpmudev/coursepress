<?php
if( ! class_exists( 'WP_Users_List_Table' ) ) {
	require ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php';
}

class CoursePress_Helper_Table_Student extends WP_Users_List_Table {
	public function prepare_items() {
		add_filter( 'manage_users_custom_column', array( __CLASS__, 'custom_columns' ), 10, 3 );
		add_filter( 'users_list_table_query_args', array( __CLASS__, 'filter_args' ) );

		parent::prepare_items();
	}

	public static function filter_args( $args ) {
		$args['meta_value'] = 'student';

		return $args;
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'id' => __( 'ID', 'CP_TD' ),
			'user' => __( 'Username', 'CP_TD' ),
			'first_name' => __( 'First Name', 'CP_TD' ),
			'last_name' => __( 'Last Name', 'CP_TD' ),
			'registered' => __( 'Registered', 'CP_TD' ),
			'last_activity' => __( 'Last Activity', 'CP_TD' ),
			'courses' => __( 'Courses', 'CP_TD' ),
			'workbook' => __( 'Workbook', 'CP_TD' ),
			'profile' => __( 'Profile', 'CP_TD' ),
			'remove' => __( 'Remove', 'CP_TD' ),
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

			case 'user':
				$return = $student->user_login;
				break;

			case 'first_name':
				$return = $student->first_name;
				break;

			case 'last_name':
				$return = $student->last_name;
				break;

			case 'registered':
				$return = date_i18n( $date_format, strtotime( $student->user_registered, current_time( 'timestamp' ) ) );
				break;

			case 'last_activity':
				$last_activity = get_user_meta( $user_id, 'latest_activity', true );
				$return = date_i18n( $date_format . ' ' . $time_format, strtotime( $last_activity, current_time( 'timestamp' ) ) );
				break;

			case 'workbook':
				$workbook_link = add_query_arg(
					array( 'view' => 'workbook', 'student_id' => $user_id )
				);
				$return = sprintf( '<a href="%s"><i class="fa fa-book cp-move-icon remove-btn"></i></a>', $workbook_link );
				break;

			case 'profile':
				$profile_link = add_query_arg(
					array( 'view' => 'profile', 'student_id' => $user_id )
				);
				$return = sprintf( '<a href="%s"><i class="fa fa-user cp-move-icon remove-btn"></i></a>', $profile_link );
				break;

			case 'courses':
				$courses = CoursePress_Data_Student::get_enrolled_courses_ids( $user_id );
				$return = count( $courses );
				break;

			case 'remove' && CoursePress_Data_Capabilities::can_delete_student():
				$delete_link = add_query_arg(
					array(
						'action' => 'delete',
						'instructor_id' => $user_id,
						'nonce' => wp_create_nonce( 'coursepress_remove_instructor' )
					)
				);
				$return = sprintf( '<a href="%s"><i class="fa fa-times-circle cp-move-icon remove-btn"></i></a>', $delete_link );
				break;
		}

		return $return;
	}

	public function extra_tablenav( $which ) { return; }
	public function row_actions( $actions, $always_show = false ) { return; }

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
