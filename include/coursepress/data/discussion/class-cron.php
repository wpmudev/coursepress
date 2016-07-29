<?php
/**
 * Schedule emails for discussion notifications.
 *
 * @since 2.0.0
 */
class CoursePress_Data_Discussion_Cron {

	private static $option_name = 'new_comment_id';
	private static $hook = 'coursepress_send_comment_notification';
	private static $recurrance = 'cp_quarter';

	/**
	 * Class must be loaded, before we call WP_Cron
	 */
	public static function init() {
		add_action( self::$hook, array( __CLASS__, 'send_notification' ) );
		add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_interval' ) );
	}

	/**
	 * Add own interval name to WP Cron
	 *
	 * @since 2.0.0
	 *
	 * @param array $schedules Array of WP Cron intervals.
	 * @return array Array of WP Cron intervals.
	 */
	public static function add_cron_interval( $schedules ) {
		/**
		 *
		 * Time between calling cron with schedule email send process. We need to
		 * observe how much emails is in out queue and we can increase or decrease
		 * interval if there is too much waiting emails.
		 */
		$schedules[ self::$recurrance ] = array(
			'interval' => 900,
			'display'  => esc_html__( 'Every quarter of an hour' ),
		);
		return $schedules;
	}

	/**
	 * Run scheduled action.
	 *
	 * @since 2.0.0
	 *
	 */
	public static function send_notification() {
		$comment_id = self::_get_comment_id();
		do {
			if ( empty( $comment_id ) ) {
				return;
			}
			CoursePress_Data_Discussion_Email::notify_all( $comment_id );
			$comment_id = self::_get_comment_id();
		} while ( $comment_id );
	}

	/**
	 * Add comment id to process queue.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $comment_id Comment ID.
	 */
	public static function add_comment_id( $comment_id ) {
		$comments = get_option( self::$option_name, array() );
		$comments[] = $comment_id;
		update_option( self::$option_name, $comments, false );
		self::schedule();
	}

	/**
	 * Schedule event
	 */
	public static function schedule() {
		if ( ! wp_next_scheduled( self::$hook ) ) {
			wp_schedule_event( time(), self::$recurrance, self::$hook );
		}
	}

	/**
	 * Return first comment_id from comments queue.
	 *
	 * @access private
	 *
	 * @since 2.0.0
	 *
	 * @return integer Comment ID.
	 */
	private static function _get_comment_id() {
		$comments = get_option( self::$option_name, array() );
		if ( empty( $comments ) ) {
			return false;
		}
		$comment_id = array_shift( $comments );
		update_option( self::$option_name, $comments, false );
		return $comment_id;
	}
}
