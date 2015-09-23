<?php
global $action, $page;
global $page, $user_id, $cp_admin_notice;
global $coursepress;

$discussion_id = '';

if ( isset( $_GET['discussion_id'] ) ) {
	$discussion         = new Discussion( $_GET['discussion_id'] );
	$discussion_details = $discussion->get_discussion();
	$discussion_id      = ( int ) $_GET['discussion_id'];
} else {
	$discussion    = new Discussion();
	$discussion_id = 0;
}

wp_reset_vars( array( 'action', 'page' ) );

if ( isset( $_POST['action'] ) && ( $_POST['action'] == 'add' || $_POST['action'] == 'update' ) ) {

	check_admin_referer( 'discussion_details' );

	$new_post_id = $discussion->update_discussion();

	if ( $_POST['action'] == 'update' ) {
		wp_redirect( admin_url( 'admin.php?page=' . $page . '&ms=du' ) );
		exit;
	}

	if ( $new_post_id !== 0 ) {
		ob_start();
		// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
		if ( $_POST['action'] == 'add' ) {
			wp_redirect( admin_url( 'admin.php?page=' . $page . '&ms=da' ) );
			exit;
		}
		exit;
	} else {
		//an error occured
	}
}

if ( isset( $_GET['discussion_id'] ) ) {
	$meta_course_id = $discussion->details->course_id;
} else {
	$meta_course_id = '';
}
?>

<div class="wrap nosubsub discussions-details cp-wrap">
	<div class="icon32" id="icon-themes"><br></div>

	<h2><?php _e( 'Discussion', 'cp' ); ?><?php if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_create_discussion_cap' ) || current_user_can( 'coursepress_create_my_discussion_cap' ) || current_user_can( 'coursepress_create_my_assigned_discussion_cap' ) ) { ?>
			<a class="add-new-h2" href="<?php echo admin_url( 'admin.php?page=discussions&action=add_new' ); ?>"><?php _e( 'Add New', 'cp' ); ?></a><?php } ?>
	</h2>

	<div class='wrap nocoursesub'>
		<form action='<?php echo esc_attr( admin_url( 'admin.php?page=' . $page . ( ( $discussion_id !== 0 ) ? '&discussion_id=' . $discussion_id : '' ) . '&action=' . $action . ( ( $discussion_id !== 0 ) ? '&ms=du' : '&ms=da' ) ) ); ?>' name='discussion-add' method='post'>

			<div class='course-liquid-left'>

				<div id='course-full'>

					<?php wp_nonce_field( 'discussion_details' ); ?>

					<?php if ( isset( $discussion_id ) && $discussion_id > 0 ) { ?>
						<input type="hidden" name="discussion_id" value="<?php echo esc_attr( $discussion_id ); ?>"/>
						<input type="hidden" name="action" value="update"/>
					<?php } else { ?>
						<input type="hidden" name="action" value="add"/>
					<?php } ?>

					<div id='edit-sub' class='course-holder-wrap'>
						<div class='course-holder'>
							<div class='course-details'>
								<label for='discussion_name'><?php _e( 'Discussion Title', 'cp' ); ?></label>
								<input class='wide' type='text' name='discussion_name' id='discussion_name' value='<?php
								if ( isset( $_GET['discussion_id'] ) ) {
									echo esc_attr( stripslashes( $discussion->details->post_title ) );
								}
								?>'/>

								<br/><br/>
								<label for='course_name'><?php _e( 'Discussion Content', 'cp' ); ?></label>
								<?php

								$editor_name    = "discussion_description";
								$editor_id      = "discussion_description";
								$editor_content = htmlspecialchars_decode( isset( $discussion->details->post_content ) ? $discussion->details->post_content : '' );

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
								<br clear="all"/>

								<div class="full">
									<label><?php _e( 'Course', 'cp' ); ?></label>
									<select name="meta_course_id">
										<?php
										$args = array(
											'post_type'      => 'course',
											'post_status'    => 'any',
											'posts_per_page' => - 1
										);

										$courses = get_posts( $args );

										foreach ( $courses as $course ) {

											//if ( $notification_id == 0 ) {

											$instructor         = new Instructor( get_current_user_id() );
											$instructor_courses = $instructor->get_assigned_courses_ids();

											$my_course = in_array( $course->ID, $instructor_courses );
											$my_course = CoursePress_Capabilities::is_course_instructor( $course->ID );
											//}

											if ( $discussion_id == 0 ) {
												if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_create_discussion_cap' ) || ( current_user_can( 'coursepress_create_my_discussion_cap' ) && $course->post_author == get_current_user_ID() ) || ( current_user_can( 'coursepress_create_my_assigned_discussion_cap' ) && $my_course ) ) {
													?>
													<option value="<?php echo $course->ID; ?>" <?php selected( $meta_course_id, $course->ID ); ?>><?php echo $course->post_title; ?></option>
													<?php
													$available_course_options ++;
												}
											} else {//check for update capabilities
												if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_update_discussion_cap' ) || ( current_user_can( 'coursepress_update_my_discussion_cap' ) && $notification_details->post_author == get_current_user_ID() ) /* || (current_user_can('coursepress_create_my_assigned_notification_cap') && $my_course) */ ) {
													?>
													<option value="<?php echo $course->ID; ?>" <?php selected( $meta_course_id, $course->ID ); ?>><?php echo $course->post_title; ?></option>
													<?php
													$available_course_options ++;
												}
											}
										}
										?>
									</select>

								</div>


								<div class="buttons">
									<?php
									if ( current_user_can( 'manage_options' ) || ( $discussion_id == 0 && current_user_can( 'coursepress_create_discussion_cap' ) ) || ( $discussion_id != 0 && current_user_can( 'coursepress_update_discussion_cap' ) ) || ( $discussion_id != 0 && current_user_can( 'coursepress_update_my_discussion_cap' ) && $discussion_details->post_author == get_current_user_id() ) ) {//do not show anything
										?>
										<input type="submit" value="<?php ( $discussion_id == 0 ? _e( 'Create', 'cp' ) : _e( 'Update', 'cp' ) ); ?>" class="button-primary"/>
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
