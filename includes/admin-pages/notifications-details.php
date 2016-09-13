<?php
global $action, $page;
global $page, $user_id, $cp_admin_notice;
global $coursepress;

$notification_id = '';
if ( isset( $_GET['notification_id'] ) ) {
	$notification         = new Notification( $_GET['notification_id'] );
	$notification_details = $notification->get_notification();
	$notification_id      = ( int ) $_GET['notification_id'];
} else {
	$notification    = new Notification();
	$notification_id = 0;
}

wp_reset_vars( array( 'action', 'page' ) );

if ( isset( $_POST['action'] ) && ( $_POST['action'] == 'add' || $_POST['action'] == 'update' ) ) {

	check_admin_referer( 'notifications_details' );

	$new_post_id = $notification->update_notification();

	if ( $_POST['action'] == 'update' ) {
		wp_redirect( admin_url( 'admin.php?page=' . $page . '&ms=nu' ) );
		exit;
	}

	if ( $new_post_id !== 0 ) {
		ob_start();
		// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
		//wp_redirect( admin_url( 'admin.php?page=' . $page . '&notification_id=' . $new_post_id . '&action=edit' ) );
		if ( $_POST['action'] == 'add' ) {
			wp_redirect( admin_url( 'admin.php?page=' . $page . '&ms=add' ) );
			exit;
		}
		exit;
	} else {
		//an error occured
	}
}

if ( isset( $_GET['notification_id'] ) ) {
	$meta_course_id = $notification->details->course_id;
} else {
	$meta_course_id = '';
}
?>

<div class="wrap nosubsub cp-wrap">
	<div class="icon32" id="icon-themes"><br></div>

	<h2><?php _e( 'Notification', 'cp' ); ?><?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_create_notification_cap' ) || current_user_can( 'coursepress_create_my_notification_cap' ) || current_user_can( 'coursepress_create_my_assigned_notification_cap' ) ) { ?>
			<a class="add-new-h2" href="<?php echo admin_url( 'admin.php?page=notifications&action=add_new' ); ?>"><?php _e( 'Add New', 'cp' ); ?></a><?php } ?>
	</h2>

	<?php
	$message['ca'] = __( 'New Notification added successfully!', 'cp' );
	$message['cu'] = __( 'Notification updated successfully.', 'cp' );
	?>

	<div class='wrap nocoursesub'>
		<form action='<?php echo esc_attr( admin_url( 'admin.php?page=' . $page . ( ( $notification_id !== 0 ) ? '&notification_id=' . $notification_id : '' ) . '&action=' . $action . ( ( $notification_id !== 0 ) ? '&ms=cu' : '&ms=ca' ) ) ); ?>' name='notification-add' method='post'>

			<div class='course-liquid-left'>

				<div id='course-full'>

					<?php wp_nonce_field( 'notifications_details' ); ?>

					<?php if ( isset( $notification_id ) && $notification_id > 0 ) { ?>
						<input type="hidden" name="notification_id" value="<?php echo esc_attr( $notification_id ); ?>"/>
						<input type="hidden" name="action" value="update"/>
					<?php } else { ?>
						<input type="hidden" name="action" value="add"/>
					<?php } ?>

					<div id='edit-sub' class='course-holder-wrap'>
						<div class='course-holder'>
							<div class='course-details'>
								<label for='notification_name'><?php _e( 'Notify Students in selected courses', 'cp' ); ?></label>

								<p><?php _e( 'Notifications are shown to end users in their Notifications menu item', 'cp' ); ?></p>

								<div class="full">
									<label><?php _e( 'Course', 'cp' ); ?></label>
									<select name="meta_course_id" class="chosen-select">
										<?php if ( current_user_can( 'coursepress_create_notification_cap' ) || current_user_can( 'coursepress_update_notification_cap' ) ) { ?>
											<option value="" <?php selected( $meta_course_id, '' ); ?>><?php _e( 'All Courses', 'cp' ); ?></option>
										<?php } ?>
										<?php

										$args = array(
											'post_type'      => 'course',
											'post_status'    => 'any',
											'posts_per_page' => - 1
										);

										$courses                  = get_posts( $args );
										$available_course_options = 0;
										//coursepress_create_my_assigned_notification_cap
										foreach ( $courses as $course ) {

											//if ( $notification_id == 0 ) {

											$instructor         = new Instructor( get_current_user_id() );
											$instructor_courses = $instructor->get_assigned_courses_ids();

											$my_course = in_array( $course->ID, $instructor_courses );
											$my_course = CoursePress_Capabilities::is_course_instructor( $course->ID );
											//}

											if ( $notification_id == 0 ) {
												if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_create_notification_cap' ) || ( current_user_can( 'coursepress_create_my_notification_cap' ) && $course->post_author == get_current_user_ID() ) || ( current_user_can( 'coursepress_create_my_assigned_notification_cap' ) && $my_course ) ) {
													?>
													<option value="<?php echo $course->ID; ?>" <?php selected( $meta_course_id, $course->ID ); ?>><?php echo $course->post_title; ?></option>
													<?php
													$available_course_options ++;
												}
											} else {//check for update capabilities
												if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_update_notification_cap' ) || ( current_user_can( 'coursepress_update_my_notification_cap' ) && $notification_details->post_author == get_current_user_ID() ) /* || (current_user_can('coursepress_create_my_assigned_notification_cap') && $my_course) */ ) {
													?>
													<option value="<?php echo $course->ID; ?>" <?php selected( $meta_course_id, $course->ID ); ?>><?php echo $course->post_title; ?></option>
													<?php
													$available_course_options ++;
												}
											}
										}
										?>
									</select>
									<?php
									if ( $available_course_options == 0 ) {
										?>
										<p><?php _e( "No courses available for selection." ); ?></p>
									<?php
									}
									?>

								</div>
								<br clear="all"/>

								<label for='notification_name'><?php _e( 'Notification Title', 'cp' ); ?></label>
								<input class='wide' type='text' name='notification_name' id='notification_name' value='<?php
								if ( isset( $_GET['notification_id'] ) ) {
									echo esc_attr( stripslashes( $notification->details->post_title ) );
								}
								?>'/>

								<br/><br/>
								<label for='course_name'><?php _e( 'Notification Content', 'cp' ); ?></label>
								<?php

								$editor_name    = "notification_description";
								$editor_id      = "notification_description";
								$editor_content = htmlspecialchars_decode( isset( $notification->details->post_content ) ? $notification->details->post_content : '' );

								$args = array(
									"textarea_name" => $editor_name,
									"editor_class"  => 'cp-editor',
									"textarea_rows" => 10,
								);

								// Filter $args before showing editor
								$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

								wp_editor( $editor_content, $editor_id, $args );
								?>
								<br/>

								<br clear="all"/>


								<div class="buttons">
									<?php
									if ( current_user_can( 'manage_options' ) || ( $notification_id == 0 && current_user_can( 'coursepress_create_notification_cap' ) ) || ( $notification_id != 0 && current_user_can( 'coursepress_update_notification_cap' ) ) || ( $notification_id != 0 && current_user_can( 'coursepress_update_my_notification_cap' ) && $notification_details->post_author == get_current_user_id() ) || ( current_user_can( 'coursepress_create_my_notification_cap' ) && $available_course_options > 0 ) ) {//do not show anything
										?>
										<input type="submit" value="<?php ( $notification_id == 0 ? _e( 'Create', 'cp' ) : _e( 'Update', 'cp' ) ); ?>" class="button-primary"/>
									<?php
									} else {
										_e( 'You do not have required permissions for this action', 'cp' );
									}
									?>
								</div>

								<br clear="all"/>

							</div>


						</div>
					</div>

				</div>
			</div>
			<!-- course-liquid-left -->
		</form>

	</div>
	<!-- wrap -->
</div>
