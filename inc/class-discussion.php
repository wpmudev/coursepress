<?php
/**
 * Class CoursePress_Discussion
 */
class CoursePress_Discussion extends CoursePress_Utility {
	var $post_id = 0;
	var $default_key = 'subscribe-reactions';
	var $field_name = 'coursepress_subscribe';

	public function __construct( $post_id = 0 ) {
		$this->post_id = $post_id;
	}

	function comment_reply_link( $link, $args, $comment ) {
		$post = coursepress_get_post_object( $this->post_id );
		$url = $post->get_permalink();

		$link = coursepress_create_html(
			'a',
			array(
				'href' => $url,
				'class' => 'comment-reply-link',
				'data-comid' => $comment->comment_ID,
			),
			__( 'Reply', 'cp' )
		);

		return $link;
	}

	function add_comment( $comments ) {
		global $cp_coursepress;

		$student_id = get_current_user_id();
		$comments = wp_parse_args(
			$comments,
			array(
				'comment_author' => '',
				'comment_author_url' => '',
				'comment_author_email' => '',
				'comment_type' => '',
				'user_id' => $student_id,
			)
		);
		$comments['comment_content'] = $comments['comment'];
		$comment_id = wp_new_comment( $comments );
		$comment_post_id = (int) $comments['comment_post_ID'];

		if ( ! empty( $comments['coursepress_subscribe'] ) ) {
			// Send notification
			$discussionClass = $cp_coursepress->get_class( 'CoursePress_Cron_Discussion' );

			if ( $discussionClass ) {
				if ( 'do-not-subscribe' === $comments['coursepress_subscribe'] ) {
					$discussionClass->un_subscribe( $comment_post_id, get_current_user_id() );
				} else {
					$discussionClass->add_comment_id( $comment_id );
					$discussionClass->subscribe( $comment_post_id, get_current_user_id(), $comments['coursepress_subscribe'] );
				}
			}
		}

		if ( ! empty( $comments['referer_url'] ) ) {
			$referer = $comments['referer_url'];
			wp_safe_redirect( $referer );
			exit;
		}

		return $comment_id;
	}

	protected function get_comments() {
		ob_start();

		$comments = get_comments( array( 'post_id' => $this->post_id ) );

		$args = array(
			'style'	   => 'ol',
			'short_ping'  => true,
			'avatar_size' => 42,
		);

		/**
		 * Fire before fetching comments of this discussion.
		 *
		 * @since 2.0
		 * @param array $args
		 */
		$args = apply_filters( 'coursepress_comment_list_args', $args );

		wp_list_comments( $args, $comments );
		$comments = ob_get_clean();

		if ( ! empty( $comments ) ) {
			$attr = array( 'class' => 'comment-list' );

			return $this->create_html( 'ol', $attr, $comments );
		}
	}

	protected function get_comment_form() {
		$form_class = array( 'comment-form', 'cp-comment-form' );
		$comment_order = get_option( 'comment_order' );
		$form_class[] = 'comment-form-' . $comment_order;

		$comment_field = $this->create_html(
			'textarea',
			array(
				'id' => 'comment',
				'name' => 'comment',
				'cols' => 45,
				'rows' => 8,
				'maxlength' => 65525,
			)
		);

		$redirect_to = coursepress_create_html(
			'input',
			array(
				'type' => 'hidden',
				'name' => 'redirect_to',
				'value' => remove_query_arg( 'dummy' ),
			)
		);

		$args = array(
			'class_form' => implode( ' ', $form_class ),
			'title_reply' => __( 'Post Here', 'cp' ),
			'label_submit' => __( 'Post', 'cp' ),
			'must_log_in' => '',
			'logged_in_as' => '',
			'class_submit' => 'submit cp-comment-submit',
			'comment_field' => $this->create_html( 'p', array( 'class' => 'comment-form-comment' ), $comment_field )
			                   . $redirect_to
		);

		ob_start();
		comment_form( $args, $this->__get( 'ID' ) );
		$comment_form = ob_get_clean();

		$comment_form = preg_replace( '%<form(.*)>|<\/form>%', '', $comment_form );

		return $comment_form;
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
	 * Returns status of subscriptions for selected module and current user.
	 *
	 * @since 2.0.0
	 *
	 * @param insteger $module_id Module ID.
	 * @return string Subscription status.
	 */
	public function get_subscription_status( $module_id ) {
		$user_id = get_current_user_id();
		$meta_key = $this->get_user_meta_name( $module_id );
		$user_subscribe = get_user_meta( $user_id, $meta_key, true );

		if ( is_numeric( $user_subscribe ) && $user_subscribe > 0 ) {
			$user_subscribe = 'subscribe-all';
			update_user_meta( $user_id, $meta_key, $user_subscribe );
		}
		if ( empty( $user_subscribe ) ) {
			return $this->default_key;
		}
		return $user_subscribe;
	}

	/**
	 * Returns array of all possible statuses.
	 *
	 * @since 2.0.0
	 *
	 * @return array Array of possible statuses.
	 */
	public function get_subscription_statuses_array() {
		return array(
			'subscribe-reactions' => __( 'Notify me of new posts in this thread', 'cp' ),
			'do-not-subscribe' => __( 'Do not notify me of new posts', 'cp' ),
			'subscribe-all' => __( 'Notify me of all new posts', 'cp' ),
		);
	}

	public function add_subscribe_button( $submit_button ) {

		$user_subscribe = $this->get_subscription_status( $this->post_id );
		$options = $this->get_subscription_statuses_array();
		$subscribe = sprintf(
			'<span class="comment-notification"><select name="%s">',
			esc_attr( $this->__get( 'field_name' ) )
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

	function comment_open() {
		return true;
	}

	function render() {
		global $post;

		$template = '';

		$post = get_post( (int) $this->post_id );
		add_filter( 'comment_open', array( $this, 'comment_open' ) );
		add_filter( 'comment_reply_link', array( $this, 'comment_reply_link' ), 10, 3 );
		add_filter( 'comment_form_submit_button', array( $this, 'add_subscribe_button' ) );
		wp_enqueue_script( 'comment-reply' );

		$comments = $this->get_comments();
		$template .= $this->create_html(
			'div',
			array( 'class' => 'comments-area', 'id' => 'comments' ),
			$comments
		);
		$template .= $this->get_comment_form();

		remove_filter( 'comment_open', array( $this, 'comment_open' ) );
		remove_filter( 'comment_reply_link', array( $this, 'comment_reply_link' ), 10, 3 );

		wp_reset_postdata();

		return coursepress_create_html( 'div', array( 'class' => 'coursepress-comments' ), $template );
	}
}