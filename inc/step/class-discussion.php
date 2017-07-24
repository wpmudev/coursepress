<?php
/**
 * Class CoursePress_Step_Discussion
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_Discussion extends CoursePress_Step {
	protected $type = 'discussion';

	protected function get_comments() {
		ob_start();

		$comments = get_comments( array( 'post_id' => $this->__get( 'ID' ) ) );

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

			return $this->create_html( 'ul', $attr, $comments );
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
			//'action' => '',
			'class_submit' => 'submit cp-comment-submit',
			'comment_field' => $this->create_html( 'p', array( 'class' => 'comment-form-comment' ), $comment_field )
				. $redirect_to
		);

		ob_start();
		comment_form( $args, $this->__get( 'ID' ) );
		$comment_form = ob_get_clean();

		return $comment_form;
	}

	function get_question() {
		$template = '';

		$id = $this->__get( 'ID' );
		$post = get_post( $id );
		setup_postdata( $post );

		add_filter( 'comments_open', '__return_true' );

		$comments = $this->get_comments();

		if ( ! empty( $comments ) ) {
			$template .= $this->create_html( 'div', array(), $comments );
		}

		$template .= $this->get_comment_form();

		remove_filter( 'comments_open', '__return_true' );
		wp_reset_postdata();

		return $template;
	}
}