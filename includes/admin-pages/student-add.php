<?php
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'coursepress_add_new_students_cap' ) ) {
	die( 'You do not have required permissions to access this page.' );
}
?>

<?php
$student = new Student( 0 );

if ( isset( $_POST['submit'] ) ) {

	check_admin_referer( 'student_signup' );

	$student_data = array();
	$form_errors  = 0;

	if ( $_POST['username'] != '' && $_POST['first_name'] != '' && $_POST['last_name'] != '' && $_POST['email'] != '' && $_POST['password'] != '' && $_POST['password_confirmation'] != '' ) {

		if ( ! username_exists( $_POST['username'] ) ) {

			if ( ! email_exists( $_POST['email'] ) ) {

				if ( $_POST['password'] == $_POST['password_confirmation'] ) {
					$student_data['user_pass'] = $_POST['password'];
				} else {
					$form_message       = __( "Passwords don't match", 'cp' );
					$form_message_class = 'error';
					$form_errors ++;
				}

				//$student_data['role'] = 'student';
				$student_data['user_login'] = $_POST['username'];
				$student_data['user_email'] = $_POST['email'];
				$student_data['first_name'] = $_POST['first_name'];
				$student_data['last_name']  = $_POST['last_name'];

				if ( ! is_email( $_POST['email'] ) ) {
					$form_message       = __( 'E-mail address is not valid.', 'cp' );
					$form_message_class = 'error';
					$form_errors ++;
				}

				if ( $form_errors == 0 ) {
					if ( $student_id = $student->add_student( $student_data ) !== 0 ) {
						$global_option = ! is_multisite();
						update_user_option( $student_id, 'role', 'student', $global_option );
						$form_message       = __( 'Account created successfully!', 'cp' );
						$form_message_class = 'updated';
						/* $email_args['email_type'] = 'student_registration';
						  $email_args['student_id'] = $student_id;
						  $email_args['student_email'] = $student_data['user_email'];
						  $email_args['student_first_name'] = $student_data['first_name'];
						  $email_args['student_last_name'] = $student_data['last_name'];
						  coursepress_send_email( $email_args ); */
					} else {
						$form_message       = __( 'An error occured while creating the account. Please check the form and try again.', 'cp' );
						$form_message_class = 'error';
					}
				}
			} else {
				$form_message       = __( 'Sorry, that email address is already used!', 'cp' );
				$form_message_class = 'error';
			}
		} else {
			$form_message       = __( 'Username already exists. Please choose another one.', 'cp' );
			$form_message_class = 'error';
		}
	} else {
		$form_message       = __( 'All fields are required.', 'cp' );
		$form_message_class = 'error';
	}
}
?>

<form id="student-settings" name="student-settings" method="post" class="student-settings cp-wrap">
	<div class="wrap nosubsub">
		<div class="icon32" id="icon-users"><br></div>
		<h2><?php _e( 'Add New Student', 'cp' ); ?></h2>

		<?php
		if ( isset( $_POST['submit'] ) ) {
			?>
			<div id="message" class="<?php echo $form_message_class; ?> fade"><p><?php echo $form_message; ?></p></div>
		<?php
		}
		?>



		<div id="edit-sub" class="course-holder-wrap">

			<div class="sidebar-name no-movecursor">
				<h3><?php _e( 'Course Details', 'cp' ); ?></h3>
			</div>

			<div class="course-holder">
				<div class="course-details">
					<div class="half">
						<label>
							<?php _e( 'First Name', 'cp' ); ?>:<br/>
							<input type="text" name="first_name" value=""/>
						</label>
					</div>

					<div class="half">
						<label>
							<?php _e( 'Last Name', 'cp' ); ?>:<br/>
							<input type="text" name="last_name" value=""/>
						</label>
					</div>

					<br clear="all">

					<div class="half">
						<label>
							<?php _e( 'Username', 'cp' ); ?>:<br/>
							<input type="text" name="username" value=""/>
						</label>
					</div>

					<div class="half">
						<label>
							<?php _e( 'E-mail', 'cp' ); ?>:<br/>
							<input type="text" name="email" value="" autocomplete="off"/>
						</label>
					</div>

					<br clear="all">

					<div class="half">
						<label>
							<?php _e( 'Password', 'cp' ); ?>:<br/>
							<input type="password" name="password" value=""/>
						</label>
					</div>

					<div class="half">
						<label class="left">
							<?php _e( 'Confirm Password', 'cp' ); ?>:<br/>
							<input type="password" name="password_confirmation" value=""/>
						</label>
					</div>

					<br clear="all">

				</div>
				<!--course-details-->

			</div>
		</div>
	</div>

	<?php wp_nonce_field( 'student_signup' ); ?>

	<p class="save-shanges">
		<?php submit_button( __( 'Add New Student', 'cp' ) ); ?>
	</p>
</form>