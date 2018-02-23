<?php
/**
 * Unsubscribe emails feature.
 *
 * @since 2.0
 **/
class CoursePress_Data_Unsubscribe {

	/**
	 * Initialize the class.
	 *
	 * Adding unsubscribe popup content.
	 */
	public function init() {

		add_action( 'wp_footer', array( $this, 'show_unsubscribe_message' ) );
	}

	/**
	 * Get the list of email types to allow unsubscribe.
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	private function unsubscribable() {

		return array(
			CoursePress_Data_Email::UNIT_STARTED_NOTIFICATION,
			CoursePress_Data_Email::COURSE_START_NOTIFICATION,
		);
	}

	/**
	 * Check if the user already unsubscribed.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return bool
	 */
	public function is_unsubscriber( $user_id ) {

		// Get user profile data.
		$user = get_userdata( $user_id );

		if ( is_object( $user ) && ! empty( $user->ID ) ) {
			// Check if unsubscribe meta exists.
			$is_unsubscriber = get_user_meta( $user->ID, 'cp_unsubscriber', true );

			return coursepress_is_true( $is_unsubscriber );
		}

		return false;
	}

	/**
	 * Hook to handle unsubscribed emails before sending emails.
	 *
	 * @param string $message Email body.
	 *
	 * @return mixed
	 */
	public function hook_unsubscribe_link( $message ) {

		$user_id = get_current_user_id();

		// Get slug for the courses listing page.
		$courses_link = coursepress_get_setting( 'slugs/course', 'courses' );

		// Include unsubscribe link
		$unsubscribe_link = add_query_arg( array( 'uid' => $user_id, 'unsubscriber' => 1 ), $courses_link );

		// Replace variables with actual values.
		$message = coursepress_replace_vars( $message, array( 'UNSUBSCRIBE_LINK' => $unsubscribe_link ) );

		// Remove the filter
		remove_filter( 'coursepress_email_message', array( $this, 'hook_unsubscribe_link' ) );

		return $message;
	}

	/**
	 * Check if an email should be send to designated user.
	 *
	 * @param string $email_type Email type.
	 * @param array $email_fields Array of fields.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 **/
	public function can_send( $email_type, $email_fields ) {

		$mail_to = '';
		$send = true;

		// Set email address.
		if ( ! empty( $email_fields['to'] ) ) {
			$mail_to = $email_fields['to'];
		} elseif ( ! empty( $email_fields['email'] ) ) {
			$mail_to = $email_fields['email'];
		}

		// Continue only if email is available.
		if ( ! empty( $mail_to ) ) {

			// Get the user account using email.
			$user = get_user_by( 'email', $mail_to );
			// Check if the user is already unsubscribed.
			$is_unsubscriber = is_object( $user ) ? $this->is_unsubscriber( $user->ID ) : false;
			// Check if current email type can be unsubscribed.
			$can_unsubscribe = in_array( $email_type, $this->unsubscribable() );

			// Check if we can send the email alert.
			if ( $can_unsubscribe && $is_unsubscriber ) {
				$send = false;
			} elseif ( $can_unsubscribe ) {
				// If not, remove the unsubscribe link.
				add_filter( 'coursepress_email_message', array( $this, 'hook_unsubscribe_link' ), 10, 2 );
			}
		}

		return $send;
	}

	/**
	 * Get the user ID of unsubscriber from url.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed
	 **/
	private function get_unsubscriber_id() {

		if ( isset( $_GET['uid'] ) && isset( $_GET['unsubscriber'] ) ) {
			// User ID from link.
			$user_id = (int) $_GET['uid'];
			// Load user data.
			$user = get_userdata( $user_id );
			// Check if it is a valid user.
			if ( is_object( $user ) && ! empty( $user->ID ) ) {
				return $user_id;
			}
		}

		return false;
	}

	/**
	 * Message to show after successful removal from subscribers list.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed|string|void
	 */
	private function unsubscribe_message() {

		$msg = sprintf( '<h3 class="cp-unsubscribe-message-head">%s</h3>', __( 'Unsubscribe Successful!', 'CP_TD' ) );
		$msg .= sprintf( '<p>%s</p>', __( 'You have been removed from our subscribers list.', 'CP_TD' ) );

		/**
		 * Filter the unsubscribe message.
		 *
		 * @since 2.0
		 **/
		return apply_filters( 'coursepress_unsubscribe_message', $msg );
	}

	/**
	 * Process the unsubscription and update the meta.
	 *
	 * Update the user meta `cp_unsubscriber` to mark as unsubscriber.
	 *
	 * @since 2.0.0
	 *
	 * @param int $unsubscribe_id User ID.
	 */
	public function unsubscribe( $unsubscribe_id ) {

		// We have an ID, unsubscribe from the list.
		if ( (int) $unsubscribe_id > 0 ) {

			/**
			 * Fires before the user marked as unsubscriber.
			 *
			 * @since 2.0
			 *
			 * @param (int) $unsubscribe_id User ID.
			 **/
			do_action( 'coursepress_remove_subscriber', $unsubscribe_id );

			// Marked the user as unsubscriber.
			update_user_meta( $unsubscribe_id, 'cp_unsubscriber', true );

			/**
			 * Fires after the user marked as unsubscriber.
			 *
			 * @since 2.0
			 *
			 * @param (int) $unsubscribe_id User ID.
			 **/
			do_action( 'coursepress_removed_subscriber', $unsubscribe_id );
		}
	}

	/**
	 * Show unsubscribe popup message to user.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function show_unsubscribe_message() {

		// Get the valid user id.
		$subscriber_id = $this->get_unsubscriber_id();

		// Continue only is not already unsubscribed.
		if ( (int) $subscriber_id > 0 ) {

			// Do not show if already unsubscribed.
			if ( $this->is_unsubscriber( $subscriber_id ) ) {
				return;
			}

			// Process the unsubscribe action.
			$this->unsubscribe( $subscriber_id );

			// Get the unsubscribe message.
			$content = $this->unsubscribe_message();

			?>
			<script type="text/template" id="cp-unsubscribe-message">
				<div class="coursepress-popup-body-front">
					<?php echo $content; ?>
					<div class="coursepress-popup-footer-front">
						<button type="button" class="cp-btn cp-btn-active step-next cp-close"><?php _e( 'OK', 'cp' ); ?></button>
					</div>
				</div>
			</script>
			<?php
		}
	}
}