<?php

if ( ! class_exists( 'WP_Users_List_Table' ) ) {
	require ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php';
}

class CoursePress_Helper_Table_Instructor extends WP_Users_List_Table {

	public function prepare_items() {
		// Remove possible query injections
		remove_all_filters( 'users_list_table_query_args' );
		remove_all_filters( 'get_role_list' );
		remove_all_filters( 'pre_user_query' );

		add_filter( 'manage_users_custom_column', array( __CLASS__, 'custom_columns' ), 10, 3 );
		add_filter( 'users_list_table_query_args', array( __CLASS__, 'filter_args' ) );
		add_filter( 'user_row_actions', array( __CLASS__, 'user_row_actions' ), 10, 2 );

		parent::prepare_items();
	}

	public static function filter_args( $args ) {
		$args['meta_value'] = 'instructor';
		$args['meta_key'] = CoursePress_Data_Capabilities::get_role_instructor_name();
		return $args;
	}

	public static function user_row_actions( $actions, $user_object ) {
		$profile_link = add_query_arg(
			array( 'action' => 'view', 'instructor_id' => $user_object->ID )
		);
		$delete_link = add_query_arg(
			array(
				'action' => 'delete',
				'instructor_id' => $user_object->ID,
				'nonce' => wp_create_nonce( 'coursepress_remove_instructor' ),
			)
		);
		$actions = array(
			'profile' => sprintf( '<a href="%s">%s</a>', $profile_link, __( 'Profile', 'CP_TD' ) ),
			'delete' => sprintf( '<a href="%s">%s</a>', $delete_link, __( 'Remove', 'CP_TD' ) ),
		);

		$actions = apply_filters( 'coursepress_instructor_row_actions', $actions, $user_object );

		return $actions;
	}

	public static function custom_columns( $null, $column, $user_id ) {
		$instructor = get_userdata( $user_id );
		$return = '';

		switch ( $column ) {
			case 'id':
				$return = $user_id;
				break;

			case 'user':
				$return = $instructor->user_login;
				break;

			case 'registered':
				$date_format = get_option( 'date_format' );
				$return = date_i18n( $date_format, CoursePress_Data_Course::strtotime( $instructor->user_registered ) );
				break;

			case 'courses':
				$count = CoursePress_Data_Instructor::count_courses( $user_id );
				$courses_link = add_query_arg(
					array(
						'page' => 'coursepress',
						'instructor_id' => $user_id,
					),
					admin_url( 'admin.php' )
				);
				$return = $count > 0 ? sprintf( '<a href="%s">%s</a>', $courses_link, $count ) : 0;
				break;
		}

		return $return;
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			//'id' => __( 'ID', 'CP_TD' ),
			'username' => __( 'Username', 'CP_TD' ),
			'name' => __( 'Name', 'CP_TD' ),
			'registered' => __( 'Registered', 'CP_TD' ),
			'courses' => __( 'Courses', 'CP_TD' ),
		);

		return $columns;
	}

	public function no_items() {
		esc_html_e( 'No instructors found...', 'CP_TD' );
	}

	public function extra_tablenav( $which ) {
		// Do nothing.
	}

	public function display() {
		?>
		<div class="wrap">
			<h2>
				<?php esc_html_e( 'Instructors', 'CP_TD' ); ?>
				<?php if ( current_user_can( 'manage_options' ) ) : ?>
					<a href="user-new.php" class="add-new-h2">
						<?php esc_html_e( 'Add New', 'CP_TD' ); ?>
					</a>
				<?php endif; ?>
			</h2>
			<hr />
			<form method="post">
				<?php parent::display(); ?>
			</form>
		</div>
		<?php
	}
}
