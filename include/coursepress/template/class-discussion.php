<?php
class CoursePress_Template_Discussion {
	public static function get_comments( $post_id, $paged = 0 ) {
		global $post, $wp_query;

		$orig_post = $post;
		$orig_query = $wp_query;
		$post_type = get_post_field( 'post_type', $post_id );
		$course_id = Coursepress_Helper_Utility::the_course( true );
		$is_focus = 'focus' == CoursePress_Data_Course::get_setting( $course_id, 'course_view' );

		/**
		 * turn off scripts
		 *
		 * @since 2.0.0
		 *
		 * Default true, but we can setup it to false to remove scripts from
		 * AJAX answer. It is used in Academy.
		 */
		$use_scripts = apply_filters( 'coursepress_discussion_use_scripts', true );

		// Cheat
		ob_start();

		$post = get_post( $post_id );

		// Because comments only show at single post, let's pretend it's a single post.
		$wp_query->is_single = true;

		// If course ended then discussion are read only.
		if ( CoursePress_Data_Course::get_course_status( $course_id ) == 'closed' ) {
			add_filter( 'comments_open', '__return_false' );
		} else {
			add_filter( 'comments_open', '__return_true' );
		}
		add_filter( 'comment_reply_link', array( __CLASS__, 'comment_reply_link' ), 10, 4 );
		// Show all comments
		add_filter( 'parse_comment_query', array( __CLASS__, 'show_all_comments' ) );
		add_filter( 'comment_form_submit_button', array( __CLASS__, 'add_subscribe_button' ) );

		if ( $use_scripts ) {
			// Remove all enqueued scripts in focus mode
			if ( $is_focus ) {
				remove_all_actions( 'wp_enqueue_scripts' );
				wp_enqueue_script( 'comment-reply' );
				wp_print_scripts();
			} else {
				wp_enqueue_script( 'comment-reply' );
			}
		} else {
			remove_all_actions( 'wp_enqueue_scripts' );
		}

		setup_postdata( $post );

		comments_template();

		$content = ob_get_clean();

		// Restore the page to it's original
		$wp_query = $orig_query;
		$post = $orig_post;
		wp_reset_postdata();

		remove_filter( 'comments_open', '__return_true' );
		remove_filter( 'parse_comment_query', array( __CLASS__, 'show_all_comments' ) );
		remove_filter( 'comment_form_submit_button', array( __CLASS__, 'add_subscribe_button' ) );

		return $content;

	}

	public static function show_all_comments( $comments_array ) {
		/**
		 * filter to alow set number of comments
		 */
		$comments_array->query_vars['number'] = apply_filters( 'coursepress_show_all_comments', null );
		return $comments_array;
	}

	/**
	 * Check if current user is a subscriber.
	 *
	 * @since 2.0
	 **/
	public static function is_discussion_subscriber() {
		$user_id = get_current_user_id();
		$post_id = get_the_ID();
		return CoursePress_Data_Discussion::is_discussion_subscriber( $user_id, $post_id );
	}

	public static function add_subscribe_button( $submit_button ) {
		global $post;
		$user_subscribe = CoursePress_Helper_Discussion::get_subscription_status( $post->ID );
		$options = CoursePress_Helper_Discussion::get_subscription_statuses_array();
		$subscribe = sprintf(
			'<span class="comment-notification"><select name="%s">',
			esc_attr( CoursePress_Helper_Discussion::get_field_name() )
		);
		foreach ( $options as $value => $label ) {
			$subscribe .= sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $value ),
				selected( $value, $user_subscribe, false ),
				esc_html( $label )
			);
		}
		$subscribe .= '</select></span>';
		$submit_button .= $subscribe;
		return $submit_button;
	}

	public static function discussion_url( $post_id ) {

		if ( CoursePress_Data_Discussion::is_comment_in_discussion( $post_id ) ) {
			$post_type = get_post_type( $post_id );

			if ( CoursePress_Data_Discussion::get_post_type_name() == $post_type ) {
				$unit_id = get_post_field( 'post_parent', $post_id );
				$course_id = get_post_field( 'post_parent', $unit_id );

				$url = CoursePress_Core::get_slug( 'courses/', true ) . get_post_field( 'post_name', $course_id );
				$url .= '/' . CoursePress_Core::get_slug( 'unit/' ) . get_post_field( 'post_name', $unit_id );
				$url .= '#module-' . $post_id;
				return $url;
			}
		}
	}

	public static function comment_url( $comment_id, $comment_post_id ) {
		return CoursePress_Helper_Discussion::get_comment_url( $comment_id, $comment_post_id );
	}

	/**
	 * Shows navigation tab whenever applicable.
	 *
	 * @since 2.0
	 *
	 * @param (int) $post_id 	`course_id` | `module_id` | course_unit_id
	 * @param (int) $course_id
	 * @param (int) $per_page	The number of comments displayed per page.
	 **/
	public static function get_comments_nav( $post_id, $course_id, $per_page = 5 ) {
		global $wp_query;

		// Store ORIG max number of pages.
		$old_max_num_pages = $wp_query->max_num_pages;

		$post_type = get_post_type( $post_id );
		$paged = (int) get_query_var( 'cpage' );
		$total = get_comments_number( $post_id );
		$wp_query->max_num_pages = $max_page = ceil( $total / $per_page );

		$link_args = array(
			'base' => add_query_arg( 'cpage', '%#%', self::discussion_url( $post_id ) ),
			'format' => '',
			'current' => $paged,
			'echo' => false,
			'total' => $max_page,
			'end_size' => 5,
			'mid_size' => 1,
		);

		$navs = paginate_links( $link_args );

		$navs = apply_filters( 'coursepress_discussion_navs', $navs );

		// Return max num pages to it's original
		$wp_query->max_num_pages = $old_max_num_pages;

		return sprintf( '<div class="discussion-page-nav" data-id="%s" data-type="%s">%s</div>', $post_id, $post_type, $navs );
	}

	public static function comment_reply_link( $link, $args, $comment, $post ) {
		$post_id = $post->ID;

		if ( CoursePress_Data_Discussion::is_comment_in_discussion( $post_id ) ) {
			$discussion_link = self::discussion_url( $post_id );
			$discussion_link = add_query_arg( 'replytocom', $comment->comment_ID, $discussion_link );
			$link = preg_replace( '%href=([\'"])(.*?)([\'"])%', 'href=$1' . $discussion_link . '$3', $link );
			$link = sprintf( '<span data-comid="%s" data-parentid="%s">%s</span>', $comment->comment_ID, $post_id, $link );
		}

		return $link;
	}

	public static function comment_post_types() {
		return array(
			CoursePress_Data_Discussion::get_post_type_name(),
			CoursePress_Data_Module::get_post_type_name(),
			CoursePress_Data_Unit::get_post_type_name(),
		);
	}

	public static function get_single_comment( $comment_id ) {
		$comments = get_comments(
			array(
				'ID' => $comment_id,
				'number' => 1
			)
		);

		ob_start();

		wp_list_comments( array(
			'style'       => 'ol',
			'short_ping'  => true,
			'avatar_size' => 42,
		), $comments );

		$comment_output = ob_get_clean();

		return $comment_output;
	}
}
