<?php
class CoursePress_Data_Forum {

	private $post_type = 'discussions';

	public function __construct() {
		add_action( 'pre_get_posts', array( $this, 'maybe_add_topic' ) );
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
			'post_content' => coursepress_filter_content( $_POST['content'] ),
			'post_status' => 'publish',
			'post_title' => coursepress_filter_content( $_POST['title'] ),
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

	public function get_by_topic_name( $topic ) {
		if ( empty( $topic ) ) {
			return array();
		}
		return get_page_by_title( $topic, OBJECT, $this->post_type );
	}
}
