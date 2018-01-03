<?php
class CoursePress_Data_Forum {

	private $post_type = 'discussions';

	public function __construct() {
		add_action( 'pre_get_posts', array( $this, 'maybe_add_topic' ) );
		add_action( 'wp_insert_comment', array( $this, 'insert_comment' ), 10, 2 );
		add_filter( 'comment_post_redirect', array( $this, 'redirect_back' ), 10, 2 );
		add_action( 'comment_form', array( $this, 'add_topic_field' ) );
	}

	public function maybe_add_topic( $wp ) {
		if ( ! $wp->is_main_query() ) {
			return;
		}
		if (
			! isset( $_POST['_wpnonce'] )
			|| ! isset( $_POST['course_id'] )
			|| ! isset( $_POST['unit_id'] )
			|| ! isset( $_POST['action'] )
			|| ! isset( $_POST['title'] )
			|| ! isset( $_POST['content'] )
			|| 'add_new_discussion' != $_POST['action']
			|| ! wp_verify_nonce( $_POST['_wpnonce'], 'add-new-discussion' )
		) {
			return;
		}
		$args = array(
			'post_type' => $this->post_type,
			'post_author' => get_current_user_id(),
			'post_content' => CoursePress_Utility::filter_content( $_POST['content'] ),
			'post_status' => 'publish',
			'post_title' => CoursePress_Utility::filter_content( $_POST['title'] ),
			'meta_input' => array(
				'course_id' => $_POST['course_id'],
				'unit_id' => $_POST['unit_id'],
			),
		);
		if ( isset( $_POST['id'] )  ) {
			$args['ID'] = $_POST['id'];
		}
		wp_insert_post( $args );
	}

	public function insert_comment( $id, $comment ) {
		$is_course = coursepress_is_course( $comment->comment_post_ID );
		if ( ! $is_course ) {
			return;
		}
		wp_set_comment_status( $id, 'approve' );
		$metas = array( 'topic_id', 'course_id' );
		foreach ( $metas as $meta ) {
			if ( isset( $_REQUEST[ $meta ] ) ) {
				$value = intval( $_REQUEST[ $meta ] );
				if ( $value ) {
					add_comment_meta( $id, $meta, $value, true );
				}
			}
		}
	}

	/**
	 * Redirect back to discussion or discussion module page.
	 *
	 * @since 2.0
	 **/
	public function redirect_back( $location, $comment ) {
		$post_id = $comment->comment_post_ID;
		$is_course = coursepress_is_course( $post_id );
		if ( ! $is_course ) {
			return $location;
		}
		$course = coursepress_get_course( $post_id );
		$topic_id = get_comment_meta( $comment->comment_ID, 'topic_id', true );
		$topic = get_post( $topic_id );
		return $course->get_discussion_url(). trailingslashit( $topic->post_name );
	}

	public function add_topic_field( $post_id ) {
		$is_course = coursepress_is_course( $post_id );
		if ( ! $is_course ) {
			return $location;
		}
		$topic = get_query_var( 'topic' );
		$discussion = coursepress_get_discussion();
		printf(
			'<input type="hidden" name="topic_id" value="%d" />',
			esc_attr( $discussion->ID )
		);
	}
}
