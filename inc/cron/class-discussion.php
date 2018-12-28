<?php
/**
 * Schedule emails for discussion notifications.
 *
 * @since 2.0.0
 */
class CoursePress_Cron_Discussion extends CoursePress_Utility {
	public $option_name = 'new_comment_id';
	public $hook = 'coursepress_send_comment_notification';
	public $recurrance = 'cp_quarter';

	/**
	 * Class must be loaded, before we call WP_Cron
	 */
	public function __construct() {
		add_action( $this->hook, array( $this, 'send_notification' ) );
		add_filter( 'cron_schedules', array( $this, 'add_cron_interval' ) );
		$this->send_notification();
	}

	/**
	 * Add own interval name to WP Cron
	 *
	 * @since 2.0.0
	 *
	 * @param array $schedules Array of WP Cron intervals.
	 * @return array Array of WP Cron intervals.
	 */
	public function add_cron_interval( $schedules ) {
		/**
		 *
		 * Time between calling cron with schedule email send process. We need to
		 * observe how much emails is in out queue and we can increase or decrease
		 * interval if there is too much waiting emails.
		 */
		$schedules[ $this->recurrance ] = array(
			'interval' => 900,
			'display'  => esc_html__( 'Every quarter of an hour', 'cp' ),
		);

		return $schedules;
	}

	/**
	 * Run scheduled action.
	 *
	 * @since 2.0.0
	 *
	 */
	public function send_notification() {
		$comment_id = $this->_get_comment_id();

		do {
			if ( empty( $comment_id ) ) {
				return;
			}

			$this->notify_all( $comment_id );
			$comment_id = $this->_get_comment_id();

		} while ( $comment_id );
	}

	/**
	 * Add comment id to process queue.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $comment_id Comment ID.
	 */
	public function add_comment_id( $comment_id ) {
		$comments = get_option( $this->option_name, array() );
		$comments[] = $comment_id;
		update_option( $this->option_name, $comments, false );

		$this->schedule();
	}

	/**
	 * Unsubscribe user from discussion notification.
	 *
	 * @param int $comment_post_id Comment Post ID.
	 * @param int $user_id User ID.
	 */
	public function un_subscribe( $comment_post_id, $user_id ) {
		// Get meta key for this comment subscription.
		$meta_key = $this->get_user_meta_name( $comment_post_id );
		// Mark this user as an un-subscriber.
		update_user_meta( $user_id, $meta_key, 'do-not-subscribe' );
	}

	/**
	 * Subscribe user from discussion notification.
	 *
	 * @param int $comment_post_id Comment Post ID.
	 * @param int $user_id User ID.
	 */
	public function subscribe( $comment_post_id, $user_id, $subscribe_type = 'subscribe-reactions' ) {
		if ( in_array( $subscribe_type, array( 'subscribe-reactions', 'subscribe-all' ) ) ) {
			// Get meta key for this comment subscription.
			$meta_key = $this->get_user_meta_name( $comment_post_id );
			// Mark this user as an un-subscriber.
			update_user_meta( $user_id, $meta_key, $subscribe_type );
		}
	}

	/**
	 * Schedule event
	 */
	public function schedule() {
		if ( ! wp_next_scheduled( $this->hook ) ) {
			wp_schedule_event( time(), $this->recurrance, $this->hook );
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
	private function _get_comment_id() {
		$comments = get_option( $this->option_name, array() );

		if ( empty( $comments ) ) {
			return false;
		}

		$comment_id = array_shift( $comments );
		update_option( $this->option_name, $comments, false );

		return $comment_id;
	}

	/**
	 * Send notifications to all users.
	 *
	 * @since: 2.0.0
	 *
	 * @param integer $comment_id Added comment ID.
	 */
	public function notify_all( $comment_id ) {
		$comment = get_comment( $comment_id );
		$current_user = $comment->user_id;
		$post = get_post( $comment->comment_post_ID );
		$message = $comment->comment_content;

		$this->notify_all_full_subscribers( $post, $message, $current_user, $comment_id );
		$this->notify_all_only_reactions_subscribers( $post, $message, $current_user, $comment_id );
	}

	/**
	 * Helper function to get post ID from discussion.
	 *
	 * @since 2.0.0
	 *
	 * @access private
	 *
	 * @param integer/WP_Post $post WP Post object or post ID.
	 * @return integer Post id.
	 */
	private function _get_post_id( $post ) {
		if ( is_integer( $post ) ) {
			return $post;
		}

		if ( is_string( $post ) ) {
			return intval( $post );
		}

		$post_type = 'discussion';

		if ( $post_type === $post->post_type ) {
			$discussion = get_post( $post->post_parent );

			return $discussion->post_id;
		}

		return $post->ID;
	}

	/**
	 * Return User Meta key for subscription data.
	 *
	 * @since 2.0.0
	 *
	 * @return string name of user meta.
	 */
	public function get_user_meta_name( $id ) {
		return sprintf( 'cp_subscribe_to_%d', $id );
	}

	/**
	 * Send notification to full subscribers.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post WP Post object.
	 * @param string $message Message to send.
	 * @param integer $current_user Current user ID.
	 * @param integer $comment_id Comment ID.
	 */
	public function notify_all_full_subscribers( $post, $message, $current_user, $comment_id ) {
		$post_id = $this->_get_post_id( $post );

		$meta_key = $this->get_user_meta_name( $post_id );
		/**
		 * get users
		 */
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT DISTINCT u.ID, u.user_email
			FROM {$wpdb->users} u
			INNER JOIN {$wpdb->usermeta} m ON ( u.ID = m.user_id )
			WHERE
			(
				(
					m.meta_key = %s
					AND m.meta_value = %s
				)
				OR
				(
					m.meta_key = %s
					AND CAST(m.meta_value AS SIGNED) > 0
				)
			) AND u.ID != %d",
			$meta_key,
			'subscribe-all',
			$meta_key,
			$current_user
		);
		$receipients = $this->_get_receipients_by_sql( $sql );

		/**
		 * get course
		 */
		$course = coursepress_get_course_object( $post_id );

		/**
		 * get instructors
		 */
		$instructors = $course->get_instructors();

		if ( ! empty( $instructors ) ) {
			foreach ( $instructors as $instructor ) {
				if ( $current_user == $instructor->ID ) {
					continue;
				}
				// Do not send if unsubscribed.
				$meta = get_user_meta( $instructor->ID, $meta_key, true );
				if ( $meta && 'do-not-subscribe' === $meta ) {
					continue;
				}
				$receipients[ $instructor->ID ] = $instructor->__get( 'user_email' );
			}
		}
		/**
		 * get facilitators
		 */
		$facilitators = $course->get_facilitators();

		if ( ! empty( $facilitators ) ) {
			foreach ( $facilitators as $facilitator ) {
				if ( $current_user == $facilitator->ID ) {
					continue;
				}
				// Do not send if unsubscribed.
				$meta = get_user_meta( $facilitator->ID, $meta_key, true );
				if ( $meta && 'do-not-subscribe' === $meta ) {
					continue;
				}
				$receipients[ $facilitator->ID ] = $facilitator->__get( 'user_email' );
			}
		}

		if ( empty( $receipients ) ) {
			return;
		}
		$this->_send( $post, $message, $current_user, $comment_id, $receipients );
	}

	/**
	 * Get users by raw SQL.
	 *
	 * @since 2.0.0
	 *
	 * @access private
	 *
	 * @param string $sql SQL request.
	 * @return array Array of users key is user->ID value, user_email.
	 */
	private function _get_receipients_by_sql( $sql ) {
		global $wpdb;

		$key = md5( $sql );
		$result = wp_cache_get( $key, '_get_receipients_by_sql' );
		if ( ! empty( $result ) ) {
			return $result;
		}
		$receipients = array();

		$results = $wpdb->get_results( $sql );

		foreach ( $results as $user ) {
			$receipients[ $user->ID ] = $user->user_email;
		}
		wp_cache_set( $key, $receipients, '_get_receipients_by_sql', 3600 );

		return $receipients;
	}

	/**
	 * Send notification to only reactions subscribers.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post WP Post object.
	 * @param string $message Message to send.
	 * @param integer $current_user Current user ID.
	 * @param integer $comment_id Comment ID.
	 */
	public function notify_all_only_reactions_subscribers( $post, $message, $current_user, $comment_id ) {
		global $wpdb;

		$post_id = $this->_get_post_id( $post );
		$users = $this->_get_comment_ancestors_users( $comment_id );

		if ( empty( $users ) ) {
			return;
		}

		$sql = $wpdb->prepare(
			"SELECT u.ID, u.user_email
			FROM {$wpdb->users} u
			INNER JOIN {$wpdb->usermeta} m ON ( u.ID = m.user_id )
			WHERE
			u.ID IN ( " . implode( ', ', array_map( 'intval', $users ) ) . ' )
			AND u.ID != %d
			AND m.meta_key = %s
			AND m.meta_value = %s',
			$current_user,
			$this->get_user_meta_name( $post_id ),
			'subscribe-reactions'
		);

		$receipients = $this->_get_receipients_by_sql( $sql );

		if ( empty( $receipients ) ) {
			return;
		}

		$this->_send( $post, $message, $current_user, $comment_id, $receipients );
	}

	/**
	 * Get users ids by comment and comment ancestors.
	 *
	 * @since 2.0.0
	 *
	 * @access private
	 *
	 * @param integer $comment_id Comment ID.
	 * @param array $users Array of current users IDs.
	 * @return array Array of users IDs.
	 */
	private function _get_comment_ancestors_users( $comment_id, $users = array() ) {
		$result = wp_cache_get( $comment_id, '_get_comment_ancestors_users' );

		if ( ! empty( $result ) ) {
			return $result;
		}
		$comment = get_comment( $comment_id );

		if ( ! $comment->comment_parent ) {
			return $users;
		}
		if ( ! empty( $comment->user_id ) ) {
			$users[] = $comment->user_id;
		}
		wp_cache_set( $comment_id, $users, '_get_comment_ancestors_users', 3600 );

		return $this->_get_comment_ancestors_users( $comment->comment_parent, $users );
	}

	/**
	 * Send message to user list.
	 *
	 * @since 2.0.0
	 *
	 * @access private
	 *
	 * @param WP_Post $post WP Post object.
	 * @param string $message Message to send.
	 * @param integer $current_user Current user ID.
	 * @param integer $comment_id Comment ID.
	 * @param array $receipients Array of notification receipients.
	 */
	private function _send( $post, $message, $current_user, $comment_id, $receipients ) {
		if ( empty( $receipients ) ) {
			return;
		}

		$post_id = $this->_get_post_id( $post );
		$course = coursepress_get_course_object( $post_id );
		$subject = __( 'New Comment: ', 'cp' );
		$subject .= substr( $course->post_title . ' at ' . $post->post_title , 0, 50 );

		/**
		 * Filter subject
		 *
		 * @since 2.0
		 *
		 * @param (string) $mail_subject
		 * @param (int) $discussion_id
		 **/
		$subject = apply_filters( 'coursepress_discussion_subject', $subject, $post->ID );

		// Add comment author
		$comment_author = get_userdata( $current_user );
		$author_name = array( $comment_author->first_name, $comment_author->last_name );
		$author_name = array_filter( $author_name );

		if ( empty( $author_name ) ) {
			$author_name = $comment_author->display_name;
		} else {
			$author_name = implode( ' ', $author_name );
		}

		$args = array(
			'course_id' => $post->ID,
			'uniq_subject' => $subject,
			'comment_author' => $author_name,
			'comment' => $message,
			'discussion_link' => $this->get_comment_url( $comment_id, $post->ID ),
		);

		// Send email to each user
		if ( ! empty( $receipients ) ) {
			$object = coursepress_get_post_object( $post->ID );
			$discussion_url = $object->get_permalink();
			foreach ( $receipients as $user_id => $user_email ) {
				// Unsubscribe link.
				$unsubscribe_link = add_query_arg(
					array(
						'uid' => $user_id,
						'unsubscribe_id' => $post->ID,
					),
					$discussion_url
				);
				// Remove http and https to avoid auto linking when added this string to text editor.
				$unsubscribe_link = preg_replace( '(^https?://)', '', $unsubscribe_link );
				$args['email'] = $user_email;
				$args['unsubscribe_link'] = $unsubscribe_link;

				CoursePress_Data_Email::send_email(
					CoursePress_Data_Email::DISCUSSION_NOTIFICATION,
					$args
				);
			}
		}
	}

	/**
	 * Get comment url.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $comment_id Comment ID.
	 * @param integer $post_id post ID.
	 * @return string Comment url.
	 */
	public function get_comment_url( $comment_id, $comment_post_id ) {
		$post = coursepress_get_post_object( $comment_post_id );
		$url = $post->get_permalink();
		$url .= '#comment-' . $comment_id;

		return $url;
	}
}
