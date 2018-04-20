<?php
/**
 * Unsubscribe feature.
 *
 * @since 2.0
 **/
class CoursePress_Data_Unsubscribe {
	public static function init() {

		add_action( 'wp_footer', array( __CLASS__, 'show_popup_unsubscribe_message' ) );
	}

	public static function unsubscribable() {
		return array(
			CoursePress_Helper_Email::UNIT_STARTED_NOTIFICATION,
			CoursePress_Helper_Email::COURSE_START_NOTIFICATION,
		);
	}

	public static function is_unsubscriber( $user_id ) {
		$user = get_userdata( $user_id );

		if ( is_object( $user ) && ! empty( $user->ID ) ) {
				$is_unsubscriber = get_user_meta( $user->ID, 'cp_unsubscriber', true );

				return cp_is_true( $is_unsubscriber );
		}

		return false;
	}

	public static function hook_unsubscribe_link( $message ) {
		$user_id = get_current_user_id();
		$user = get_userdata( $user_id );

		// Include unsubscribe link
		$unsubscribe_link = add_query_arg(
			array(
				'uid' => $user_id,
				'unsubscriber' => 1,
			),
			CoursePress_Core::get_slug( 'courses/', true )
		);

		$message = CoursePress_Helper_Utility::replace_vars(
			$message,
			array(
				'UNSUBSCRIBE_LINK' => $unsubscribe_link,
			)
		);

		// Remove the filter
		remove_filter( 'coursepress_email_message', array( __CLASS__, 'hook_unsubscribe_link' ) );

		return $message;
	}

	/**
	 * Check if an email should be send to designated user.
	 **/
	public static function is_send( $email_type, $email_fields ) {
		$mail_to = '';
		$send = true;

		if ( ! empty( $email_fields['to'] ) ) {
			$mail_to = $email_fields['to'];
		} elseif ( ! empty( $email_fields['email'] ) ) {
			$mail_to = $email_fields['email'];
		}

		if ( ! empty( $mail_to ) ) {
			$user = get_user_by( 'email', $mail_to );
			$is_unsubscriber = is_object( $user ) ? self::is_unsubscriber( $user->ID ) : false;
			$unsubscribable = self::unsubscribable();

			if ( in_array( $email_type, $unsubscribable ) ) {
				if ( $is_unsubscriber ) {
					$send = false;
				} else {
					add_filter( 'coursepress_email_message', array( __CLASS__, 'hook_unsubscribe_link' ), 10, 2 );
				}
			}
		}

		return $send;
	}

	/**
	 * Get the user ID of unsubscriber
	 **/
	public static function get_unsubscriber_id() {
		if ( isset( $_GET['uid'] ) && isset( $_GET['unsubscriber'] ) ) {
			$user_id = (int) $_GET['uid'];

			$user = get_userdata( $user_id );

			if ( is_object( $user ) && ! empty( $user->ID ) ) {
				return $user_id;
			}
		}

		return false;
	}

	public static function unsubscribe_message() {
		$msg = sprintf( '<h3 class="bbm-modal__title cp-unsubscribe-message">%s</h3>', __( 'Unsubscribe Successful!', 'coursepress' ) );
		$msg .= sprintf( '<div class="bbm-modal__section"><p>%s</p></div>', __( 'You have been removed from our subscribers list.', 'coursepress' ) );
		$msg .= sprintf( '<div class="bbm-modal__bottombar"><a class="cancel-link">%s</a></div>', __( 'Done', 'coursepress' ) );
		/**
		 * Filter the unsubscribe message.
		 *
		 * @since 2.0
		 **/
		$msg = apply_filters( 'coursepress_unsubscribe_message', $msg );

		return $msg;
	}

	public static function unsubscribe( $unsubscribe_id ) {
		if ( (int) $unsubscribe_id > 0 ) {
			// We have an ID, unsubscribe from the list.

			/**
			 * Fires before the user marked as unsubscriber.
			 *
			 * @since 2.0
			 *
			 * @param (int) $user_id
			 **/
			do_action( 'coursepress_remove_subscriber', $unsubscribe_id );

			// Marked the user as unsubscriber.
			update_user_meta( $unsubscribe_id, 'cp_unsubscriber', true );

			/**
			 * Fires after the user marked as unsubscriber.
			 **/
			do_action( 'coursepress_removed_subscriber', $unsubscribe_id );
		}
	}

	public static function show_popup_unsubscribe_message() {
		$unsubscribe_id = self::get_unsubscriber_id();

		if ( (int) $unsubscribe_id > 0 && ! self::is_unsubscriber( $unsubscribe_id ) ) {
			// Unsubscribe
			self::unsubscribe( $unsubscribe_id );
			$content = self::unsubscribe_message();
			?>
			<script type="text/template" id="modal-template">
				<div class="enrollment-modal-container"></div>
			</script>
			<script type="text/template" id="cp-unsubscribe-message" data-type="modal-step" data-modal-action="unsubscribe">
				<?php echo $content; ?>
			</script>
			<?php
		}
	}
}