<?php
if ( ( isset( $_GET['action'] ) && $_GET['action'] == 'add_new' && isset( $_GET['page'] ) && $_GET['page'] == 'notifications' ) || isset( $_GET['action'] ) && $_GET['action'] == 'edit' && isset( $_GET['page'] ) && $_GET['page'] == 'notifications' ) {
	include( 'notifications-details.php' );
} else {
	if ( isset( $_GET['s'] ) ) {
		$s = $_GET['s'];
	} else {
		$s = '';
	}

	$page = $_GET['page'];

	if ( isset( $_POST['action'] ) && isset( $_POST['notifications'] ) ) {
		check_admin_referer( 'bulk-notifications' );

		$action = $_POST['action'];

		foreach ( $_POST['notifications'] as $notification_value ) {
			if ( is_numeric( $notification_value ) ) {
				$notification_id     = ( int ) $notification_value;
				$notification        = new Notification( $notification_id );
				$notification_object = $notification->get_notification();

				switch ( addslashes( $action ) ) {
					case 'publish':
						if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_change_notification_status_cap' ) || ( current_user_can( 'coursepress_change_my_course_notification_cap' ) && $notification_object->post_author == get_current_user_id() ) ) {
							$notification->change_status( 'publish' );
							$message = __( 'Selected notifications have been published successfully.', 'cp' );
						} else {
							$message = __( "You don't have right permissions to change notification status.", 'cp' );
						}
						break;

					case 'unpublish':
						if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_change_notification_status_cap' ) || ( current_user_can( 'coursepress_change_my_notification_status_cap' ) && $notification_object->post_author == get_current_user_id() ) ) {
							$notification->change_status( 'private' );
							$message = __( 'Selected notifications have been set to private successfully.', 'cp' );
						} else {
							$message = __( "You don't have right permissions to change notification status.", 'cp' );
						}
						break;

					case 'delete':
						if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_notification_cap' ) || ( current_user_can( 'coursepress_delete_my_notification_cap' ) && $notification_object->post_author == get_current_user_id() ) ) {
							$notification->delete_notification();
							$message = __( 'Selected notifications have been deleted successfully.', 'cp' );
						} else {
							$message = __( "You don't have right permissions to delete the notification.", 'cp' );
						}
						break;
				}
			}
		}
	}

// Query the notifications
	if ( isset( $_GET['page_num'] ) ) {
		$page_num = ( int ) $_GET['page_num'];
	} else {
		$page_num = 1;
	}

	if ( isset( $_GET['s'] ) ) {
		$notificationsearch = $_GET['s'];
	} else {
		$notificationsearch = '';
	}

	$wp_notification_search = new Notification_Search( $notificationsearch, $page_num );

	if ( isset( $_GET['notification_id'] ) ) {
		$notification = new Notification( $_GET['notification_id'] );
	}

	if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' && isset( $_GET['notification_id'] ) && is_numeric( $_GET['notification_id'] ) ) {

		if ( ! isset( $_GET['cp_nonce'] ) || ! wp_verify_nonce( $_GET['cp_nonce'], 'delete_notification_' . $_GET['notification_id'] ) ) {
			die( __( 'Cheating huh?', 'cp' ) );
		}

		$notification_object = $notification->get_notification();

		if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_notification_cap' ) || ( current_user_can( 'coursepress_delete_my_notification_cap' ) && $notification_object->post_author == get_current_user_id() ) ) {
			$notification->delete_notification( $force_delete = true );
			$message = __( 'Selected notification has been deleted successfully.', 'cp' );
		} else {
			$message = __( "You don't have right permissions to delete the notification.", 'cp' );
		}
	}

	if ( isset( $_GET['action'] ) && $_GET['action'] == 'change_status' && isset( $_GET['notification_id'] ) && is_numeric( $_GET['notification_id'] ) ) {

		if ( ! isset( $_GET['cp_nonce'] ) || ! wp_verify_nonce( $_GET['cp_nonce'], 'change_status_' . $_GET['notification_id'] ) ) {
			die( __( 'Cheating huh?', 'cp' ) );
		}

		$notification->change_status( $_GET['new_status'] );
		$message = __( 'Status for the selected notification has been changed successfully.', 'cp' );
	}
	?>
	<div class="wrap nosubsub notifications cp-wrap">
		<div class="icon32" id="icon-themes"><br></div>
		<h2><?php _e( 'Notifications', 'cp' ); ?><?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_create_notification_cap' ) || current_user_can( 'coursepress_create_my_notification_cap' ) || current_user_can( 'coursepress_create_my_assigned_notification_cap' ) ) { ?>
				<a class="add-new-h2" href="<?php echo admin_url( 'admin.php?page=notifications&action=add_new' ); ?>"><?php _e( 'Add New', 'cp' ); ?></a><?php } ?>
		</h2>
		<?php
		$ms['add'] = __( 'New notification added successfully!', 'cp' );
		$ms['nu']  = __( 'Notification updated successfully.', 'cp' );

		if ( isset( $_GET['ms'] ) ) {
			$message = $ms[ $_GET['ms'] ];
		}
		?>
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
						<label class="screen-reader-text"><?php _e( 'Search Notifications', 'cp' ); ?>:</label>
						<input type="text" value="<?php echo esc_attr( $s ); ?>" name="s">
						<input type="submit" class="button" value="<?php _e( 'Search Notifications', 'cp' ); ?>">
					</p>
				</form>
			</div>
			<!--/alignright-->

			<form method="post" action="<?php echo esc_attr( admin_url( 'admin.php?page=' . $page ) ); ?>" id="posts-filter">

				<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_change_notification_status_cap' ) || current_user_can( 'coursepress_delete_notification_cap' ) ) { ?>
					<div class="alignleft actions">
						<select name="action">
							<option selected="selected" value=""><?php _e( 'Bulk Actions', 'cp' ); ?></option>
							<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_change_notification_status_cap' ) ) { ?>
								<option value="publish"><?php _e( 'Publish', 'cp' ); ?></option>
								<option value="unpublish"><?php _e( 'Private', 'cp' ); ?></option>
							<?php } ?>
							<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_notification_cap' ) ) { ?>
								<option value="delete"><?php _e( 'Delete', 'cp' ); ?></option>
							<?php } ?>
						</select>
						<input type="submit" class="button-secondary action" id="doaction" name="doaction" value="<?php _e( 'Apply', 'cp' ); ?>"/>
					</div>
				<?php } ?>


				<br class="clear">

		</div>
		<!--/tablenav-->


		<?php
		wp_nonce_field( 'bulk-notifications' );

		$columns = array(
			"notification_title" => __( 'Notification', 'cp' ),
			"course"             => __( 'Course', 'cp' ),
			"status"             => __( 'Status', 'cp' ),
		);


		$col_sizes = array(
			'3',
			'57',
			'25',
			'10',
			'5'
		);

		if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_notification_cap' ) || ( current_user_can( 'coursepress_delete_my_notification_cap' ) ) ) {
			$columns["remove"] = __( 'Remove', 'cp' );
			$col_sizes[]       = '7';
		}
		?>

		<table cellspacing="0" class="widefat shadow-table">
			<thead>
			<tr>
				<th style="" class="manage-column column-cb check-column" id="cb" scope="col" width="<?php echo $col_sizes[0] . '%'; ?>">
					<input type="checkbox"></th>
				<?php
				$n = 1;
				foreach ( $columns as $key => $col ) {
					?>
					<th style="" class="manage-column column-<?php echo str_replace( '_', '-', $key ); ?>" width="<?php echo $col_sizes[ $n ] . '%'; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
					<?php
					$n ++;
				}
				?>
			</tr>
			</thead>

			<tbody>
			<?php
			$style = '';

			foreach ( $wp_notification_search->get_results() as $notification ) {

				$notification_obj    = new Notification( $notification->ID );
				$notification_object = $notification_obj->get_notification();
				$style               = ( ' alternate' == $style ) ? '' : ' alternate';
				?>
				<?php
				if ( isset( $notification_object->course_id ) && $notification_object->course_id !== '' ) {
					$course      = new Course( $notification_object->course_id );
					$course_name = $course->details->post_title;
				} else {
					$course_name = __( 'All Courses', 'cp' );
				}
				?>
				<tr id='user-<?php echo $notification_object->ID; ?>' class="<?php echo $style; ?>">
					<th scope='row' class='check-column'>
						<input type='checkbox' name='notifications[]' id='user_<?php echo $notification_object->ID; ?>' class='' value='<?php echo $notification_object->ID; ?>'/>
					</th>
					<td class="column-notification-title <?php echo $style; ?>">
						<a href="<?php echo admin_url( 'admin.php?page=notifications&action=edit&notification_id=' . $notification_object->ID ); ?>"><strong><?php echo $notification_object->post_title; ?></strong></a>

						<div class="visible-small visible-extra-small">
							<strong><?php _e( 'Course:', 'cp' ); ?></strong> <?php echo $course_name; ?></div>
						<div class="visible-small visible-extra-small">
							<strong><?php _e( 'Status:', 'cp' ); ?></strong> <?php echo ( $notification_object->post_status == 'publish' ) ? ucfirst( $notification_object->post_status ) . 'ed' : ucfirst( $notification_object->post_status ); ?>
						</div>
						<div class="course_excerpt"><?php echo cp_get_the_course_excerpt( $notification_object->ID ); ?></div>
						<div class="row-actions">
							<span class="edit_notification"><a href="<?php echo admin_url( 'admin.php?page=notifications&action=edit&notification_id=' . $notification_object->ID ); ?>"><?php _e( 'Edit', 'cp' ); ?></a> | </span>
							<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_change_notification_status_cap' ) || ( current_user_can( 'coursepress_change_my_notification_status_cap' ) && $notification_object->post_author == get_current_user_id() ) ) { ?>
								<span class="notification_publish_unpublish"><a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=notifications&notification_id=' . $notification_object->ID . '&action=change_status&new_status=' . ( ( $notification_object->post_status == 'private' ) ? 'publish' : 'private' ) ), 'change_status_' . $notification_object->ID, 'cp_nonce' ); ?>"><?php ( $notification_object->post_status == 'private' ) ? _e( 'Publish', 'cp' ) : _e( 'Private', 'cp' ); ?></a> | </span>
							<?php } ?>
							<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_notification_cap' ) || ( current_user_can( 'coursepress_delete_my_notification_cap' ) && $notification_object->post_author == get_current_user_id() ) ) { ?>
								<span class="course_remove"><a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=notifications&action=delete&notification_id=' . $notification_object->ID ), 'delete_notification_' . $notification_object->ID, 'cp_nonce' ); ?>" onClick="return removeNotification();"><?php _e( 'Delete', 'cp' ); ?></a> | </span>
							<?php } ?>
						</div>
					</td>
					<td class="column-course <?php echo $style; ?>"> <?php echo $course_name; ?> </td>
					<td class="column-status <?php echo $style; ?>"><?php echo ( $notification_object->post_status == 'publish' ) ? ucfirst( $notification_object->post_status ) . 'ed' : ucfirst( $notification_object->post_status ); ?></td>
					<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_notification_cap' ) || ( current_user_can( 'coursepress_delete_my_notification_cap' ) ) ) { ?>
						<td class="<?php echo $style; ?>">
							<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_delete_notification_cap' ) || ( current_user_can( 'coursepress_delete_my_notification_cap' ) && $notification_object->post_author == get_current_user_id() ) ) { ?>
								<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=notifications&action=delete&notification_id=' . $notification_object->ID ), 'delete_notification_' . $notification_object->ID, 'cp_nonce' ); ?>" onClick="return removeNotification();">
									<i class="fa fa-times-circle cp-move-icon remove-btn"></i>
								</a>
							<?php } ?>
						</td>
					<?php } ?>
				</tr>
			<?php
			}
			?>

			<?php
			if ( count( $wp_notification_search->get_results() ) == 0 ) {
				?>
				<tr>
					<td colspan="6">
						<div class="zero-courses"><?php _e( 'No notifications found.', 'cp' ) ?></div>
					</td>
				</tr>
			<?php
			}
			?>
			</tbody>
		</table>
		<!--/widefat shadow-table-->

		<div class="tablenav">
			<div class="tablenav-pages"><?php $wp_notification_search->page_links(); ?></div>
		</div>
		<!--/tablenav-->

		</form>

	</div><!--/wrap-->

<?php } ?>