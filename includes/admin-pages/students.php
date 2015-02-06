<?php
global $coursepress;

$page = $_GET['page'];
$s    = ( isset( $_GET['s'] ) ? $_GET['s'] : '' );

if ( isset( $_POST['action'] ) && isset( $_POST['users'] ) ) {
	check_admin_referer( 'bulk-students' );

	$action = $_POST['action'];
	foreach ( $_POST['users'] as $user_value ) {

		if ( is_numeric( $user_value ) ) {

			$student_id = ( int ) $user_value;
			$student    = new Student( $student_id );

			switch ( addslashes( $action ) ) {
				case 'delete':
					if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_students_cap' ) ) {
						$student->delete_student();
						// $message = __( 'Selected students has been removed successfully.', 'cp' );
						$message = __( 'Selected students have been withdrawn from all courses successfully. Note: The user accounts still exist.', 'cp' );
					}
					break;

				case 'withdraw':
					if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_withdraw_students_cap' ) ) {
						$student->withdraw_from_all_courses();
						$message = __( 'Selected students have been withdrawn from all courses successfully.', 'cp' );
					}
					break;
			}
		}
	}
}

if ( isset( $_GET['page_num'] ) ) {
	$page_num = ( int ) $_GET['page_num'];
} else {
	$page_num = 1;
}

if ( isset( $_GET['s'] ) ) {
	$usersearch = $_GET['s'];
} else {
	$usersearch = '';
}

if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' && isset( $_GET['student_id'] ) && is_numeric( $_GET['student_id'] ) ) {
	if ( ! isset( $_GET['cp_nonce'] ) || ! wp_verify_nonce( $_GET['cp_nonce'], 'delete_student_' . $_GET['student_id'] ) ) {
		die( __( 'Cheating huh?', 'cp' ) );
	}
	$student = new Student( $_GET['student_id'] );
	$student->delete_student();
	$message = __( 'Selected student has been withdrawn from all courses successfully. Note: The user account still exists.', 'cp' );
}

if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'edit' || $_GET['action'] == 'view' ) && isset( $_GET['student_id'] ) && is_numeric( $_GET['student_id'] ) ) {
	include( 'student-profile.php' );
} else if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'workbook' ) ) {
	include( 'student-workbook.php' );
} else if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'add_new' ) ) {
	include( 'student-add.php' );
} else {
// Query the users
	$wp_user_search = new Student_Search( $usersearch, $page_num );
	?>
	<div class="wrap nosubsub students cp-wrap">
		<div class="icon32" id="icon-users"><br></div>
		<h2><?php _e( 'Students', 'cp' ); ?><?php if ( current_user_can( 'manage_options' ) ) { ?>
				<a class="add-new-h2" href="user-new.php"><?php _e( 'Add New', 'cp' ); ?></a><?php } ?><?php if ( current_user_can( 'coursepress_add_new_students_cap' ) && ! current_user_can( 'manage_options' ) ) { ?>
				<a class="add-new-h2" href="<?php echo admin_url( 'admin.php?page=students&action=add_new' ); ?>"><?php _e( 'Add New', 'cp' ); ?></a><?php } ?>
		</h2>

		<?php
		if ( isset( $message ) ) {
			?>
			<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
		<?php
		}
		?>

		<div class="tablenav tablenav-top">

			<div class="alignright actions new-actions">
				<form method="get" action="<?php echo esc_attr( admin_url( 'admin.php?page=' . $page ) ); ?>" class="search-form">
					<p class="search-box">
						<input type='hidden' name='page' value='<?php echo esc_attr( $page ); ?>'/>
						<label class="screen-reader-text"><?php _e( 'Search Students', 'cp' ); ?>:</label>
						<input type="text" value="<?php echo esc_attr( $s ); ?>" name="s">
						<input type="submit" class="button" value="<?php _e( 'Search Students', 'cp' ); ?>">
					</p>
				</form>
			</div>

			<form method="post" action="<?php echo esc_attr( admin_url( 'admin.php?page=' . $page ) ); ?>" id="posts-filter">

				<?php wp_nonce_field( 'bulk-students' ); ?>

				<div class="alignleft actions">
					<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_withdraw_students_cap' ) || current_user_can( 'coursepress_delete_students_cap' ) ) { ?>
						<select name="action">
							<option selected="selected" value=""><?php _e( 'Bulk Actions', 'cp' ); ?></option>
							<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_students_cap' ) ) { ?>
								<option value="delete"><?php _e( 'Delete', 'cp' ); ?></option>
							<?php } ?>
							<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_withdraw_students_cap' ) ) { ?>
								<option value="withdraw"><?php _e( 'Withdraw from all courses', 'cp' ); ?></option>
							<?php } ?>
						</select>
						<input type="submit" class="button-secondary action" id="doaction" name="doaction" value="<?php _e( 'Apply', 'cp' ); ?>"/>
					<?php } ?>
				</div>


				<br class="clear">

		</div>
		<!--/tablenav-->

		<input type='hidden' name='page' value='<?php echo esc_attr( $page ); ?>'/>

		<?php
		$columns = array(
			"ID"                => __( 'ID', 'cp' ),
			"username"          => __( 'Username', 'cp' ),
			"user_fullname"     => __( 'Full Name', 'cp' ),
			"user_firstname"    => __( 'First Name', 'cp' ),
			"user_lastname"     => __( 'Surname', 'cp' ),
			"registration_date" => __( 'Registered', 'cp' ),
			"latest_activity"   => __( 'Latest Activity', 'cp' ),
			"courses"           => __( 'Courses', 'cp' ),
			"workbook"          => __( 'Workbook', 'cp' ),
			"edit"              => __( 'Profile', 'cp' ),
		);

		$col_sizes = array(
			'8',
			'7',
			'7',
			'7',
			'15',
			'15',
			'10',
			'7',
			'7'
		);

		if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_students_cap' ) ) {
			$columns["delete"] = __( 'Remove', 'cp' );
			$col_sizes[]       = '5';
		}
		?>

		<table cellspacing="0" class="widefat fixed shadow-table unit-control-buttons">
			<thead>
			<tr>
				<th style="" class="manage-column column-cb check-column" width="1%" id="cb" scope="col">
					<input type="checkbox"></th>
				<?php
				$n = 0;
				foreach ( $columns as $key => $col ) {
					?>
					<th style="" class="manage-column column-<?php echo str_replace( '_', '-', $key ); ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
					<?php
					$n ++;
				}
				?>
			</tr>
			</thead>

			<tbody>
			<?php
			$style = '';

			foreach ( $wp_user_search->get_results() as $user ) {

				$user_object = new Student( $user->ID );
				$roles       = $user_object->roles;
				$role        = array_shift( $roles );

				$style = ( 'alternate' == $style ) ? '' : 'alternate';
				?>
				<tr id='user-<?php echo $user_object->ID; ?>' class="<?php echo $style; ?>">
					<th scope='row' class='check-column'>
						<input type='checkbox' name='users[]' id='user_<?php echo $user_object->ID; ?>' value='<?php echo $user_object->ID; ?>'/>
					</th>
					<td class="column-ID <?php echo $style; ?>"><?php echo $user_object->ID; ?></td>
					<td class="column-user-username <?php echo $style; ?>"><?php echo $user_object->user_login; ?></td>
					<td class="column-user-fullname visible-small visible-extra-small <?php echo $style; ?>">
						<a href="<?php echo admin_url( 'admin.php?page=students&action=view&student_id=' . $user_object->ID ); ?>">
							<?php echo $user_object->first_name; ?>
						</a>
						<a href="<?php echo admin_url( 'admin.php?page=students&action=view&student_id=' . $user_object->ID ); ?>">
							<?php echo $user_object->last_name; ?>
						</a>

						<div class="visible-extra-small">
							<?php _e( 'Latest Activity:', 'cp' ); ?>
							<span class="latest_activity"><?php echo( isset( $user_object->latest_activity ) && $user_object->latest_activity !== '' ? date_i18n( 'Y-m-d h:i:s', $user_object->latest_activity ) : __( 'N/A', 'cp' ) ); ?></span> <?php if ( $coursepress->user_is_currently_active( $user_object->ID ) ) { ?>
								<a class="activity_circle" alt="<?php _e( 'User is currently active on the website', 'cp' ); ?>"  title="<?php _e( 'User is currently active on the website', 'cp' ); ?>"></a><?php } ?>
						</div>
					</td>
					<td class="column-user-firstname <?php echo $style; ?>">
						<a href="<?php echo admin_url( 'admin.php?page=students&action=view&student_id=' . $user_object->ID ); ?>">
							<?php echo $user_object->first_name; ?>
						</a>
					</td>
					<td class="column-user-lastname <?php echo $style; ?>">
						<a href="<?php echo admin_url( 'admin.php?page=students&action=view&student_id=' . $user_object->ID ); ?>">
							<?php echo $user_object->last_name; ?>
						</a>
					</td>
					<td class="column-registration-date <?php echo $style; ?>"><?php echo $user_object->user_registered; ?></td>
					<td class="column-latest-activity <?php echo $style; ?>">
						<span class="latest_activity"><?php echo( isset( $user_object->latest_activity ) && $user_object->latest_activity !== '' ? date_i18n( 'Y-m-d h:i:s', $user_object->latest_activity ) : __( 'N/A', 'cp' ) ); ?></span> <?php if ( $coursepress->user_is_currently_active( $user_object->ID ) ) { ?>
							<a class="activity_circle" alt="<?php _e( 'User is currently active on the website', 'cp' ); ?>"  title="<?php _e( 'User is currently active on the website', 'cp' ); ?>"></a><?php } ?>
					</td>
					<td class="column-courses <?php echo $style; ?>" style="padding-left: 30px;"><?php echo $user_object->courses_number; ?></td>
					<td class="column-workbook <?php echo $style; ?>" style="padding-top:13px;">
						<a href="<?php echo admin_url( 'admin.php?page=students&action=workbook&student_id=' . $user_object->ID ); ?>">
							<i class="fa fa-book cp-move-icon remove-btn"></i>
						</a>
					</td>
					<td class="column-edit <?php echo $style; ?>" style="padding-top:13px;">
						<a href="<?php echo admin_url( 'admin.php?page=students&action=view&student_id=' . $user_object->ID ); ?>">
							<i class="fa fa-user cp-move-icon remove-btn"></i>
						</a>
					</td>
					<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_students_cap' ) ) { ?>
						<td class="column-delete <?php echo $style; ?>" style="padding-top:13px;">
							<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=students&action=delete&student_id=' . $user_object->ID ), 'delete_student_' . $user_object->ID, 'cp_nonce' ); ?>" onclick="return removeStudent();">
								<i class="fa fa-times-circle cp-move-icon remove-btn"></i>
							</a></td>
					<?php } ?>
				</tr>

			<?php
			}
			?>
			<?php
			if ( count( $wp_user_search->get_results() ) == 0 ) {
				?>
				<tr>
					<td colspan="8">
						<div class="zero"><?php _e( 'No students found.', 'cp' ); ?></div>
					</td>
				</tr>
			<?php
			}
			?>
			</tbody>
		</table>

		<div class="tablenav">
			<div class="tablenav-pages"><?php $wp_user_search->page_links(); ?></div>
		</div>
		<!--/tablenav-->

		</form>

	</div>

<?php } ?>