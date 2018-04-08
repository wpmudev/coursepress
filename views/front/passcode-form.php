<form method="post" action="<?php echo esc_url( add_query_arg( 'action', 'course_enroll_passcode', admin_url( 'admin-ajax.php' ) ) ); ?>">
	<label for="course-passcode"><?php echo esc_html( $label_text ); ?></label>
	<?php if ( isset( $_COOKIE[$cookie_name] ) ) : ?>
		<p class="description"><?php esc_html_e( 'Incorrect passcode!', 'cp' ); ?></p>
	<?php endif; ?>
	<input type="password" name="course_passcode" required="required" />
	<input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>" />
	<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'coursepress_nonce' ) ); ?>" />
	<button type="submit"><?php echo esc_html( $button_text ); ?></button>
</form>
