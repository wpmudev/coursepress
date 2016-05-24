<?php
if ( isset( $_GET['instructor_id'] ) && is_numeric( $_GET['instructor_id'] ) ) {
	$instructor = new Instructor( $_GET['instructor_id'] );
}
?>
<div class='wrap nocoursesub cp-wrap'>

	<div class='course-liquid-left'>

		<div id='course-left'>

			<?php wp_nonce_field( 'instructor_profile_' . $instructor->ID ); ?>

			<div id='edit-sub' class='course-holder-wrap'>

				<?php
				/*$columns = array(
					"course" => __( ' ', 'cp' ),
					"additional_info" => __( ' ', 'cp' ),
				);*/
				?>

			</div>

			<div id='edit-sub' class='course-holder-wrap'>

				<div class='sidebar-name no-movecursor'>
					<h3><?php _e( 'Courses', 'cp' ); ?></h3>

				</div>

				<?php
				/*$columns = array(
					"course" => __( ' ', 'cp' ),
					"additional_info" => __( ' ', 'cp' ),
				);*/
				?>
				<!--COURSES START-->
				<table cellspacing="0" class="widefat shadow-table">
					<tbody>
					<?php
					$style = '';

					$assigned_courses = $instructor->get_assigned_courses_ids();

					if ( count( $assigned_courses ) == 0 ) {
						?>
						<tr>
							<td>
								<div class="zero-row"><?php _e( '0 courses assigned to the instructor', 'cp' ); ?></div>
							</td>
							<td></td>
						</tr>
					<?php
					}

					foreach ($assigned_courses as $course_id) {

					$course_object = new Course( $course_id );
					$course_object = $course_object->get_course();

					if ($course_object) {

					$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
					?>
					<tr id='user-<?php echo $course_object->ID; ?>' <?php echo $style; ?>>
						<td <?php echo $style; ?>>
							<a href="<?php echo admin_url( 'admin.php?page=course_details&course_id=' . $course_object->ID ); ?>" width="75%">
								<div class="course_title"><?php echo $course_object->post_title; ?></a>
			</div>
		<?php echo cp_get_the_course_excerpt( $course_object->ID ); ?></div>
                                        </td>

                                        <td <?php echo $style; ?> width="25%">
                                            <div class="course_additional_info">
                                                <div><span class="info_caption"><?php _e( 'Start', 'cp' ); ?>:</span><span class="info"><?php if ( $course_object->open_ended_course == 'on' ) {
			_e( 'Open-ended', 'cp' );
		} else {
			echo $course_object->course_start_date;
		} ?></span></div>
                                                <div><span class="info_caption"><?php _e( 'End', 'cp' ); ?>:</span><?php if ( $course_object->open_ended_course == 'on' ) {
			_e( 'Open-ended', 'cp' );
		} else {
			echo $course_object->course_end_date;
		} ?></span></div>
                                                <div><span class="info_caption"><?php _e( 'Duration', 'cp' ); ?>:</span><span class="info"><?php if ( $course_object->open_ended_course == 'on' ) {
			echo '&infin;';
		} else {
			echo cp_get_number_of_days_between_dates( $course_object->course_start_date, $course_object->course_end_date );
		} ?> <?php _e( 'Days', 'cp' ); ?></span></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
		}
		}
		?>

			<?php

			?>
			</tbody>
			</table>
			<!--COURSES END-->

		</div>

	</div>
</div> <!-- course-liquid-left -->

<?php if ( 1 == 1 ) { ?>
	<div class='course-liquid-right'>

		<div class="course-holder-wrap">

			<div class="sidebar-name no-movecursor">
				<h3><?php _e( 'Profile', 'cp' ); ?></h3>
			</div>

			<div class="instructor-profile-holder" id="sidebar-levels">
				<div class='sidebar-inner'>

					<div class="instructors-info" id="instructors-info">
						<!--PROFILE START-->
						<table cellspacing="0" class="widefat instructor-profile">
							<tbody>
							<tr>
								<?php if ( isset( $_GET['action'] ) && $_GET['action'] == 'view' ) { ?>
									<td><?php echo get_avatar( $instructor->ID, '80' ); ?></td>
									<td>
										<div class="instructor_additional_info">
											<div><span class="info_caption"><?php _e( 'Username', 'cp' ); ?>:</span>
												<span class="info"><?php echo $instructor->user_login; ?></span></div>
											<div><span class="info_caption"><?php _e( 'First Name', 'cp' ); ?>:</span>
												<span class="info"><?php echo $instructor->user_firstname; ?></span>
											</div>
											<div><span class="info_caption"><?php _e( 'Last Name', 'cp' ); ?>:</span>
												<span class="info"><?php echo $instructor->user_lastname; ?></span>
											</div>
											<div><span class="info_caption"><?php _e( 'Email', 'cp' ); ?>:</span>
												<span class="info"><a href="mailto:<?php echo $instructor->user_email; ?>"><?php echo $instructor->user_email; ?></a></span>
											</div>
											<div><span class="info_caption"><?php _e( 'Courses', 'cp' ); ?>:</span>
												<span class="info"><?php echo Instructor::get_courses_number( $instructor->ID ); ?></span>
											</div>
										</div>
									</td>
								<?php } else { ?>
									<td>
										<label class="ins-box"><?php _e( 'First Name', 'cp' ); ?>
											<input type="user_firstname" value="<?php echo esc_attr( $instructor->user_firstname ); ?>"/>
										</label>

										<label class="ins-box"><?php _e( 'Last Name', 'cp' ); ?>
											<input type="user_lastname" value="<?php echo esc_attr( $instructor->user_lastname ); ?>"/>
										</label>

										<label class="ins-box"><?php _e( 'E-mail', 'cp' ); ?>
											<input type="user_email" value="<?php echo esc_attr( $instructor->user_email ); ?>"/>
										</label>
									</td>
									<td>

									</td>
								<?php } ?>

							</tr>

							</tbody>
						</table>
						<!--PROFILE END-->

						<div class="edit-profile-link">
							<a href="user-edit.php?user_id=<?php echo $instructor->ID; ?>"><?php _e( 'Edit Profile', 'cp' ); ?></a>
						</div>
					</div>

					<div class="clearfix"></div>

				</div>
			</div>
		</div>
		<!-- course-holder-wrap -->

	</div> <!-- course-liquid-right -->
<?php } ?>

</div> <!-- wrap -->