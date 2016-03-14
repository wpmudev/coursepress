<?php

if( ! class_exists( 'WP_Users_List_Table' ) ) {
	require ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php';
}

class CoursePress_Helper_Table_Instructor extends WP_Users_List_Table {

	public function prepare_items() {
		add_filter( 'manage_users_custom_column', array( __CLASS__, 'custom_columns' ), 10, 3 );
		add_filter( 'users_list_table_query_args', array( __CLASS__, 'filter_args' ) );

		parent::prepare_items();
	}

	public static function filter_args( $args ) {
		$args[ 'meta_value' ] = 'instructor';

		return $args;
	}

	public static function custom_columns( $null, $column, $user_id ) {
		$instructor = get_userdata( $user_id );
		$return = '';

		switch( $column ) {
			case 'id':
				$return = $user_id;
				break;
			case 'user':
				$return = $instructor->user_login;
				break;
			case 'first_name':
				$return = $instructor->first_name;
				break;
			case 'last_name':
				$return = $instructor->last_name;
				break;
			case 'registered':
				$date_format = get_option( 'date_format' );
				$return = date_i18n( $date_format, strtotime( $instructor->user_registered ) );
				break;
			case 'profile':
				$profile_link = add_query_arg(
					array( 'action' => 'view', 'instructor_id' => $user_id )
				);
				$return = sprintf( '<a href="%s"><i class="fa fa-user cp-move-icon remove-btn"></i></a>', $profile_link );
				break;
			case 'courses':
				$return = CoursePress_Data_Instructor::get_courses_number( $instructor );
				break;
			case 'remove':
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

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'id' => __( 'ID', 'CP_TD' ),
			'user' => __( 'Username', 'CP_TD' ),
			'first_name' => __( 'First Name', 'CP_TD' ),
			'last_name' => __( 'Last Name', 'CP_TD' ),
			'registered' => __( 'Registered', 'CP_TD' ),
			'courses' => __( 'Courses', 'CP_TD' ),
			'profile' => __( 'Profile', 'CP_TD' ),
			'remove' => __( 'Remove', 'CP_TD' ),
		);

		return $columns;
	}

	public function no_items() {
		esc_html_e( 'No instructors found...', 'CP_TD' );
	}

	public function extra_tablenav( $which ) { return; }
	public function row_actions( $actions, $always_show = false ) { return; }

	public function display() {
		?>
		<div class="wrap">
			<h2>
				<?php esc_html_e( 'Instructors', 'CP_TD' ); ?>
				<?php if ( current_user_can( 'manage_options' ) ): ?>
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