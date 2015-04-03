<?php $page = $_GET['page']; ?>

<div id="poststuff" class="metabox-holder m-settings email-settings cp-wrap">
	<form action='' method='post'>

		<input type='hidden' name='page' value='<?php echo esc_attr( $page ); ?>'/>
		<input type='hidden' name='action' value='updateoptions'/>

		<?php
		wp_nonce_field( 'update-coursepress-options' );
		?>
		<div class="postbox">
			<h3 class="hndle" style='cursor:auto;'><span><?php _e( 'User Registration E-mail', 'cp' ); ?></span></h3>

			<div class="inside">
				<p class="description"><?php _e( 'Settings for an e-mail student get upon account registration.', 'cp' ); ?></p>
				<table class="form-table">
					<tbody id="items">
					<tr>
						<th><?php _e( 'From Name', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_registration_from_name" value="<?php echo esc_attr( coursepress_get_registration_from_name() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'From E-mail', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_registration_from_email" value="<?php echo esc_attr( coursepress_get_registration_from_email() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'E-mail Subject', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_registration_email_subject" value="<?php echo esc_attr( coursepress_get_registration_email_subject() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'E-mail Content', 'cp' ); ?></th>
						<td>
							<p class="description"><?php _e( 'These codes will be replaced with actual data: STUDENT_FIRST_NAME, STUDENT_USERNAME, STUDENT_PASSWORD, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS', 'cp' ); ?></p>
							<?php
							$editor_name    = "option_registration_content_email";
							$editor_id      = "option_registration_content_email";
							$editor_content = stripslashes( coursepress_get_registration_content_email() );

							$args = array( "textarea_name" => $editor_name, "textarea_rows" => 10, 'wpautop' => true );
							// Filter $args before showing editor
							$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );
							wp_editor( $editor_content, $editor_id, $args );
							?>
						</td>
					</tr>

					</tbody>
				</table>
			</div>
			<!--/inside-->

		</div>
		<!--/postbox-->

		<?php
			do_action( 'coursepress_email_settings_post_registration' );
		?>

		<div class="postbox">
			<h3 class="hndle" style='cursor:auto;'>
				<span><?php _e( 'Course Enrollment Confirmation E-mail', 'cp' ); ?></span></h3>

			<div class="inside">
				<p class="description"><?php _e( 'Settings for an e-mail student get upon enrollment', 'cp' ); ?></p>
				<table class="form-table">
					<tbody id="items">
					<tr>
						<th><?php _e( 'From Name', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_enrollment_from_name" value="<?php echo esc_attr( coursepress_get_enrollment_from_name() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'From E-mail', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_enrollment_from_email" value="<?php echo esc_attr( coursepress_get_enrollment_from_email() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'E-mail Subject', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_enrollment_email_subject" value="<?php echo esc_attr( coursepress_get_enrollment_email_subject() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'E-mail Content', 'cp' ); ?></th>
						<td>
							<p class="description"><?php _e( 'These codes will be replaced with actual data: STUDENT_FIRST_NAME, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS, COURSE_ADDRESS', 'cp' ); ?></p>
							<?php
							$editor_name    = "option_enrollment_content_email";
							$editor_id      = "option_enrollment_content_email";
							$editor_content = stripslashes( coursepress_get_enrollment_content_email() );

							$args = array( "textarea_name" => $editor_name, "textarea_rows" => 10, 'wpautop' => true );
							// Filter $args before showing editor
							$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );
							wp_editor( $editor_content, $editor_id, $args );
							?>
						</td>
					</tr>

					</tbody>
				</table>
			</div>
			<!--/inside-->

		</div>
		<!--/postbox-->

		<?php
		do_action( 'coursepress_email_settings_post_enrollment' );
		?>


		<?php if ( $this->is_marketpress_active() ) { ?>
			<div class="postbox">
				<h3 class="hndle" style='cursor:auto;'><span><?php _e( 'MarketPress New Order E-mail', 'cp' ); ?></span>
				</h3>

				<div class="inside">
					<p class="description"><?php _e( 'Settings for an e-mail student get upon placing an order', 'cp' ); ?></p>
					<table class="form-table">
						<tbody id="items">
						<tr>
							<th><?php _e( 'From Name', 'cp' ); ?></th>
							<td>
								<input type="text" name="option_mp_order_from_name" value="<?php echo esc_attr( coursepress_get_mp_order_from_name() ); ?>"/>
							</td>
						</tr>

						<tr>
							<th><?php _e( 'From E-mail', 'cp' ); ?></th>
							<td>
								<input type="text" name="option_mp_order_from_email" value="<?php echo esc_attr( coursepress_get_mp_order_from_email() ); ?>"/>
							</td>
						</tr>

						<tr>
							<th><?php _e( 'E-mail Subject', 'cp' ); ?></th>
							<td>
								<input type="text" name="option_mp_order_email_subject" value="<?php echo esc_attr( coursepress_get_mp_order_email_subject() ); ?>"/>
							</td>
						</tr>

						<tr>
							<th><?php _e( 'E-mail Content', 'cp' ); ?></th>
							<td>
								<p class="description"><?php _e( 'These codes will be replaced with actual data: CUSTOMER_NAME, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS, COURSE_ADDRESS, ORDER_ID, ORDER_STATUS_URL', 'cp' ); ?></p>
								<?php
								$editor_name    = "option_mp_order_content_email";
								$editor_id      = "option_mp_order_content_email";
								$editor_content = stripslashes( coursepress_get_mp_order_content_email() );

								$args = array(
									"textarea_name" => $editor_name,
									"textarea_rows" => 10,
									'wpautop'       => true
								);
								// Filter $args before showing editor
								$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );
								wp_editor( $editor_content, $editor_id, $args );
								?>
							</td>
						</tr>

						</tbody>
					</table>
				</div>
				<!--/inside-->

			</div><!--/postbox-->
		<?php } ?>

		<?php
			do_action( 'coursepress_email_settings_post_marketpress' );
		?>


		<div class="postbox">
			<h3 class="hndle" style='cursor:auto;'>
				<span><?php _e( 'Student Invitation to a Course E-mail', 'cp' ); ?></span></h3>

			<div class="inside">
				<p class="description"><?php _e( 'Settings for an e-mail student get upon receiving an invitation to a course.', 'cp' ); ?></p>
				<table class="form-table">
					<tbody id="items">
					<tr>
						<th><?php _e( 'From Name', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_invitation_from_name" value="<?php echo esc_attr( coursepress_get_invitation_from_name() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'From E-mail', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_invitation_from_email" value="<?php echo esc_attr( coursepress_get_invitation_from_email() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'E-mail Subject', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_invitation_email_subject" value="<?php echo esc_attr( coursepress_get_invitation_email_subject() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'E-mail Content', 'cp' ); ?></th>
						<td>
							<p class="description"><?php _e( 'These codes will be replaced with actual data: STUDENT_FIRST_NAME, COURSE_NAME, COURSE_EXCERPT, COURSE_ADDRESS, WEBSITE_ADDRESS', 'cp' ); ?></p>
							<?php
							$editor_name    = "option_invitation_content_email";
							$editor_id      = "option_invitation_content_email";
							$editor_content = stripslashes( coursepress_get_invitation_content_email() );

							$args = array( "textarea_name" => $editor_name, "textarea_rows" => 10, 'wpautop' => true );
							// Filter $args before showing editor
							$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );
							wp_editor( $editor_content, $editor_id, $args );

							?>
						</td>
					</tr>

					</tbody>
				</table>
			</div>
			<!--/inside-->

		</div>
		<!--/postbox-->
		<?php
			do_action( 'coursepress_email_settings_post_course_invite' );
		?>


		<div class="postbox">
			<h3 class="hndle" style='cursor:auto;'>
				<span><?php _e( 'Student Invitation with Passcode to a Course E-mail', 'cp' ); ?></span></h3>

			<div class="inside">
				<p class="description"><?php _e( 'Settings for an e-mail student get upon receiving an invitation ( with passcode ) to a course.', 'cp' ); ?></p>
				<table class="form-table">
					<tbody id="items">
					<tr>
						<th><?php _e( 'From Name', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_invitation_passcode_from_name" value="<?php echo esc_attr( coursepress_get_invitation_passcode_from_name() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'From E-mail', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_invitation_passcode_from_email" value="<?php echo esc_attr( coursepress_get_invitation_passcode_from_email() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'E-mail Subject', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_invitation_passcode_email_subject" value="<?php echo esc_attr( coursepress_get_invitation_passcode_email_subject() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'E-mail Content', 'cp' ); ?></th>
						<td>
							<p class="description"><?php _e( 'These codes will be replaced with actual data: STUDENT_FIRST_NAME, COURSE_NAME, COURSE_EXCERPT, COURSE_ADDRESS, WEBSITE_ADDRESS, PASSCODE', 'cp' ); ?></p>
							<?php
							$editor_name    = "option_invitation_content_passcode_email";
							$editor_id      = "option_invitation_content_passcode_email";
							$editor_content = stripslashes( coursepress_get_invitation_content_passcode_email() );

							$args = array( "textarea_name" => $editor_name, "textarea_rows" => 10, 'wpautop' => true );
							// Filter $args before showing editor
							$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );
							wp_editor( $editor_content, $editor_id, $args );

							?>
						</td>
					</tr>

					</tbody>
				</table>
			</div>
			<!--/inside-->

		</div>
		<!--/postbox-->

		<?php
			do_action( 'coursepress_email_settings_post_course_invite_passcode' );
		?>

		<div class="postbox">
			<h3 class="hndle" style='cursor:auto;'><span><?php _e( 'Instructor Invitation E-mail', 'cp' ); ?></span>
			</h3>

			<div class="inside">
				<p class="description"><?php _e( 'Settings for an e-mail an instructor will get upon receiving an invitation.', 'cp' ); ?></p>
				<table class="form-table">
					<tbody id="items">
					<tr>
						<th><?php _e( 'From Name', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_instructor_invitation_from_name" value="<?php echo esc_attr( coursepress_get_instructor_invitation_from_name() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'From E-mail', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_instructor_invitation_from_email" value="<?php echo esc_attr( coursepress_get_instructor_invitation_from_email() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'E-mail Subject', 'cp' ); ?></th>
						<td>
							<input type="text" name="option_instructor_invitation_email_subject" value="<?php echo esc_attr( cp_get_instructor_invitation_email_subject() ); ?>"/>
						</td>
					</tr>

					<tr>
						<th><?php _e( 'E-mail Content', 'cp' ); ?></th>
						<td>
							<p class="description"><?php _e( 'These codes will be replaced with actual data: INSTRUCTOR_FIRST_NAME, INSTRUCTOR_LAST_NAME, INSTRUCTOR_EMAIL, CONFIRMATION_LINK, COURSE_NAME, COURSE_EXCERPT, COURSE_ADDRESS, WEBSITE_ADDRESS, WEBSITE_NAME', 'cp' ); ?></p>
							<?php
							$editor_name    = "option_instructor_invitation_email";
							$editor_id      = "option_instructor_invitation_email";
							$editor_content = stripslashes( cp_get_instructor_invitation_email() );

							$args = array( "textarea_name" => $editor_name, "textarea_rows" => 10, 'wpautop' => true );
							// Filter $args before showing editor
							$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );
							wp_editor( $editor_content, $editor_id, $args );
							?>
						</td>
					</tr>

					</tbody>
				</table>
			</div>
			<!--/inside-->

		</div>
		<!--/postbox-->

		<?php
			do_action( 'coursepress_email_settings_post_instructor_invite' );
		?>

		<?php
			do_action( 'coursepress_email_settings' );
		?>

		<p class="save-shanges">
			<?php submit_button( __( 'Save Changes', 'cp' ) ); ?>
		</p>

	</form>
</div><!--/poststuff-->