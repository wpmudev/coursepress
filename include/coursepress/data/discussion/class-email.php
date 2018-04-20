<?php
/**
 * Schedule emails for discussion notifications.
 *
 * @since 2.0.0
 */
class CoursePress_Data_Discussion_Email {

	/**
	 * Send notifications to all users.
	 *
	 * @since: 2.0.0
	 *
	 * @param integer $comment_id Added comment ID.
	 */
	public static function notify_all( $comment_id ) {
		$comment = get_comment( $comment_id );
		$current_user = $comment->user_id;
		$post = get_post( $comment->comment_post_ID );
		$message = $comment->comment_content;
		self::notify_all_full_subscribers( $post, $message, $current_user, $comment_id );
		self::notify_all_only_reactions_subscribers( $post, $message, $current_user, $comment_id );
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
	public static function notify_all_only_reactions_subscribers( $post, $message, $current_user, $comment_id ) {
		$post_id = self::_get_post_id( $post );
		$users = self::_get_comment_ancestors_users( $comment_id );
		if ( empty( $users ) ) {
			return;
		}
		global $wpdb;
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
			CoursePress_Helper_Discussion::get_user_meta_name( $post_id ),
			'subscribe-reactions'
		);
		$receipients = self::_get_receipients_by_sql( $sql );
		if ( empty( $receipients ) ) {
			return;
		}
		self::_send( $post, $message, $current_user, $comment_id, $receipients );
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
	private static function _send( $post, $message, $current_user, $comment_id, $receipients ) {
		if ( empty( $receipients ) ) {
			return;
		}
		$post_id = self::_get_post_id( $post );
		$course_id = CoursePress_Data_Module::get_course_id_by_module( $post_id );
		$course = get_post( $course_id );
		$subject = __( 'New Comment: ', 'coursepress' );
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
			'discussion_link' => CoursePress_Helper_Discussion::get_comment_url( $comment_id, $post->ID ),
		);

		// Send email to each user
		if ( ! empty( $receipients ) ) {
			foreach ( $receipients as $user_id => $user_email ) {
				$args['email'] = $user_email;
				$args['unsubscribe_link'] = add_query_arg(
					array(
						'uid' => $user_id,
						'unsubscribe' => $post->ID,
					),
					CoursePress_Template_Discussion::discussion_url( $post->ID )
				);
				CoursePress_Helper_Email::send_email(
					CoursePress_Helper_Email::DISCUSSION_NOTIFICATION,
					$args
				);
			}
		}
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
	public static function notify_all_full_subscribers( $post, $message, $current_user, $comment_id ) {
		$post_id = self::_get_post_id( $post );
		$meta_key = CoursePress_Helper_Discussion::get_user_meta_name( $post_id );
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
		$receipients = self::_get_receipients_by_sql( $sql );
		/**
		 * get course ID
		 */
		$course_id = CoursePress_Data_Module::get_course_id_by_module( $post_id );
		/**
		 * get instructors
		 */
		$instructors = CoursePress_Data_Course::get_instructors( $course_id, true );
		if ( ! empty( $instructors ) ) {
			foreach ( $instructors as $instructor ) {
				if ( $current_user == $instructor->ID ) {
					continue;
				}
				$receipients[ $instructor->ID ] = $instructor->user_email;
			}
		}
		/**
		 * get facilitators
		 */
		$facilitators = CoursePress_Data_Course::get_facilitators( $course_id, true );
		if ( ! empty( $facilitators ) ) {
			foreach ( $facilitators as $facilitator ) {
				if ( $current_user == $facilitator->ID ) {
					continue;
				}
				$receipients[ $facilitator->ID ] = $facilitator->user_email;
			}
		}
		if ( empty( $receipients ) ) {
			return;
		}
		self::_send( $post, $message, $current_user, $comment_id, $receipients );
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
	private static function _get_post_id( $post ) {
		if ( is_integer( $post ) ) {
			return $post;
		}
		if ( is_string( $post ) ) {
			return intval( $post );
		}
		$post_type = CoursePress_Data_Discussion::get_post_type_name();
		if ( $post_type == $post->post_type ) {
			$discussion = get_post( $post->post_parent );
			return $discussion->post_id;
		}
		return $post->ID;
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
	private static function _get_receipients_by_sql( $sql ) {
		$key = md5( $sql );
		$result = wp_cache_get( $key, '_get_receipients_by_sql' );
		if ( ! empty( $result ) ) {
			return $result;
		}
		$receipients = array();
		global $wpdb;
		$results = $wpdb->get_results( $sql );
		foreach ( $results as $user ) {
			$receipients[ $user->ID ] = $user->user_email;
		}
		wp_cache_set( $key, $receipients, '_get_receipients_by_sql', 3600 );
		return $receipients;
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
	private static function _get_comment_ancestors_users( $comment_id, $users = array() ) {
		$result = wp_cache_get( $comment_id, '_get_comment_ancestors_users' );
		if ( ! empty( $result ) ) {
			return $result;
		}
		$comment = get_comment( $comment_id );
		if ( 0 == $comment->comment_parent ) {
			return $users;
		}
		if ( ! empty( $comment->user_id ) ) {
			$users[] = $comment->user_id;
		}
		wp_cache_set( $comment_id, $users, '_get_comment_ancestors_users', 3600 );
		return self::_get_comment_ancestors_users( $comment->comment_parent, $users );
	}
}
