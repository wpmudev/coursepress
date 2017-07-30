<form method="post" action="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=course_enroll_passcode">
	<label for="course-passcode"><?php _e( 'Enter passcode', 'cp' ); ?></label>
	<?php if ( isset( $_COOKIE[$cookie_name] ) ) : ?>
		<p class="description"><?php _e( 'Incorrect passcode!', 'cp' ); ?></p>
	<?php endif; ?>
	<input type="text" name="course_passcode" required="required" />
	<input type="hidden" name="course_id" value="<?php echo $course_id; ?>" />
	<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'coursepress_nonce' ); ?>" />
	<button type="submit"><?php _e( 'Enroll', 'cp' ); ?></button>
</form>