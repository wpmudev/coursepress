<?php
/**
 * Class CoursePress_Admin_Forums
 *
 * @since 2.0
 * @package CoursePress
 */
class CoursePress_Admin_Forums extends CoursePress_Admin_Page {
	protected $slug = 'coursepress_forum';
	private $items;
	private $post_type = 'discussions';
	private $id_name = 'forum_id';

	public function __construct() {
		parent::__construct();
		add_filter( 'coursepress_admin_localize_array', array( $this, 'change_localize_array' ) );
	}

	function change_localize_array( $localize_array ) {
		$localize_array['text']['deleting_post'] = __( 'Deleting forum... please wait', 'cp' );
		$localize_array['text']['delete_post'] = __( 'Are you sure you want to delete this forum?', 'cp' );
		if ( ! isset( $localize_array['text']['forums'] ) ) {
			$localize_array['text']['forums'] = array();
		}
		$localize_array['text']['forums']['forum_title_is_empty'] = __( 'You should add title before sending.', 'cp' );
		$localize_array['text']['forums']['no_items'] = __( 'Please select at least one forum.', 'cp' );
		$localize_array['text']['forums']['delete_confirm'] = __( 'Are you sure to delete selected forums?', 'cp' );
		$localize_array['text']['forums']['deleting_forums'] = __( 'Deleting forums... please wait', 'cp' );
		return $localize_array;
	}

	function columns( $current_status ) {
		$columns = array(
			'topic' => __( 'Topic', 'cp' ),
			'course' => __( 'Course', 'cp' ),
			'comments' => __( 'Comments', 'cp' ),
		);

		if ( 'trash' !== $current_status ) {
			$columns['status'] = __( 'Status', 'cp' );
		}
		return $columns;
	}

	private function get_page_list() {
		$search = isset( $_GET['s'] ) ? $_GET['s'] : '';
		$current_status = $this->get_status();
		$args = array(
			'columns' => $this->columns( $current_status ),
			'courses' => coursepress_get_accessible_courses( false ),
			'hidden_columns' => array(),
			'forums' => $this->get_list( $current_status, $count ),
			'page' => $this->slug,
			'search' => $search,
			'edit_link' => add_query_arg(
				array(
					'page' => $this->slug,
					$this->id_name => 0,
				),
				admin_url( 'admin.php' )
			),
			'statuses' => coursepress_get_post_statuses( $this->post_type, $current_status, $this->slug ),
			'current_status' => $current_status,
			'pagination' => $this->set_pagination( $count, 'coursepress_forums_per_page' ),
			'bulk_actions' => $this->get_bulk_actions(),
		);
		coursepress_render( 'views/admin/forums', $args );
		coursepress_render( 'views/tpl/common' );
	}

	private function get_page_edit( $forum_id ) {
		$args = array(
			'page' => $this->slug,
			'post_title' => '',
			'post_content' => '',
			'course_id' => 0,
			'forum_id' => 0,
			'unit_id' => 'course',
			'email_notification' => 'yes',
			'thread_comments_depth' => 5,
			'comments_per_page' => 50,
			'comments_order' => 'newer',
			'courses' => array(),
			'units' => array(
				'course' => __( 'All units', 'cp' ),
			),
		);
		if ( isset( $forum_id ) ) {
			if ( ! empty( $forum_id ) || 0 === $forum_id ) {
				$_forum_id = $this->update( $forum_id );
			}
			if ( $_forum_id !== $forum_id ) {
				$args['forum_created'] = $_forum_id;
				$args['id_name'] = $this->id_name;
			}

			$post = get_post( $forum_id );
			if ( is_a( $post, 'WP_Post' ) ) {
				if ( $this->post_type == $post->post_type ) {
					$args['forum_id'] = $post->ID;
					$args['course_id'] = $post->post_parent;
					$args['post_title'] = $post->post_title;
					$args['post_content'] = stripslashes( $post->post_content );
					$meta_keys = array( 'email_notification', 'unit_id', 'email_notification', 'thread_comments_depth', 'comments_per_page', 'comments_order' );
					foreach ( $meta_keys as $meta_key ) {
						$meta_value = get_post_meta( $post->ID, $meta_key, true );
						if ( ! empty( $meta_value ) ) {
							$args[ $meta_key ] = $meta_value;
						}
					}
					$course = get_post( $args['course_id'] );
					if ( is_a( $course, 'WP_Post' ) ) {
						$args['courses'][ $course->ID ] = $course->post_title;
						$course = new CoursePress_Course( $course );
						$units = $course->get_units();
						foreach ( $units as $unit ) {
							$args['units'][ $unit->ID ] = $unit->post_title;
						}
					}
				}
			}
		}
		if ( 'yes' == $args['email_notification'] ) {
			$args['email_notification'] = true;
		}
		coursepress_render( 'views/admin/forum-edit', $args );
	}

	public function get_page() {
		$forum_id = filter_input( INPUT_GET, $this->id_name, FILTER_VALIDATE_INT );
		if ( $forum_id || 0 === $forum_id ) {
			$this->get_page_edit( $forum_id );
		} else {
			$this->get_page_list();
		}
		coursepress_render( 'views/admin/footer-text' );
	}

	public function get_list( $current_status, &$count = 0 ) {
		/**
		 * search
		 */
		$s = isset( $_POST['s'] )? mb_strtolower( trim( $_POST['s'] ) ):false;
		/**
		 * Per Page
		 */
		$per_page = $this->items_per_page( 'coursepress_forums_per_page' );
		/**
		 * Pagination
		 */
		$current_page = $this->get_pagenum();
		$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$post_args = array(
			'post_type' => $this->post_type,
			'posts_per_page' => $per_page,
			'paged' => $paged,
			's' => $s,
			'post_status' => $current_status,
		);
		/**
		 * Course ID
		 */
		$course_id = isset( $_GET['course_id'] ) ? sanitize_text_field( $_GET['course_id'] ) : '';
		if ( ! empty( $course_id ) ) {
			$post_args['post_parent'] = (int) $course_id;
		}
		$wp_query = new WP_Query( $post_args );
		$count = $wp_query->found_posts;
		$this->items = array();
		$base_url = add_query_arg( 'page', $this->slug, admin_url( 'admin.php' ) );
		foreach ( $wp_query->posts as $one ) {
			$one->course_id = $one->post_parent;
			$one->unit_id = get_post_meta( $one->ID, 'unit_id', true );
			$one->comments_number = get_comments_number( $one->ID );
			$one->edit_link = wp_nonce_url(
				add_query_arg( $this->id_name, $one->ID, $base_url ),
				$this->get_nonce_action( $one->ID )
			);
			$this->items[] = $one;
		}
		return $this->items;
	}

	/**
	 * Get nonce action.
	 *
	 * @since 3.0.0
	 *
	 * @param integer $id Forum ID.
	 * @returns strinng nonce action.
	 */
	private function get_nonce_action( $id ) {
		return sprintf( '%s_%d', $this->slug, $id );
	}

	private function update( $forum_id ) {
		/**
		 * check input
		 */
		if ( ! isset( $_POST['_wpnonce'] ) || ! isset( $_POST[ $this->id_name ] ) ) {
			return $forum_id;
		}
		/**
		 * check nonce
		 */
		$nonce_action = 'coursepress-update-forum-'.$_POST[ $this->id_name ];
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], $nonce_action ) ) {
			return $forum_id;
		}
		// Check capabilities.
		if( ! empty( $forum_id ) && ! CoursePress_Data_Capabilities::can_update_discussion( $forum_id ) ) {
			// If user is no allowed to updated dicussion we need to check if user have course discussion access.
			if ( ! empty( $_POST['course_id'] ) && ! CoursePress_Data_Capabilities::can_add_discussion( (int) $_POST['course_id'] ) ) {
				return $forum_id;
			}
		} elseif ( ! CoursePress_Data_Capabilities::can_add_discussions() ) {
			return $forum_id;
		}
		$postarr = array(
			'ID' => $_POST[ $this->id_name ],
			'post_title' => sanitize_text_field( isset( $_POST['post_title'] )? $_POST['post_title']:'' ),
			'post_parent' => intval( isset( $_POST['course_id'] )? $_POST['course_id']:0 ),
			'post_content' => isset( $_POST['post_content'] )? $_POST['post_content']:'',
			'post_type' => $this->post_type,
			'post_status' => 'publish',
			'meta_input' => array(
				'unit_id' => isset( $_POST['unit_id'] )? $_POST['unit_id']:'course',
				'email_notification' => isset( $_POST['email_notification'] )? 'yes':'no',
				'thread_comments_depth' => intval( isset( $_POST['thread_comments_depth'] )? $_POST['thread_comments_depth']:5 ),
				'comments_per_page' => intval( isset( $_POST['comments_per_page'] )? $_POST['comments_per_page']:50 ),
				'comments_order' => sanitize_text_field( isset( $_POST['comments_order'] )? $_POST['comments_order']:'newer' ),
			),
		);
		$postarr = sanitize_post( $postarr, 'db' );
		$post_id = wp_insert_post( $postarr );
		if ( 0 == $postarr['ID'] ) {
			return $post_id;
		}
		return $forum_id;
	}

	/**
	 * get forum list by course_ID
	 *
	 * @since 3.0.0
	 *
	 * @param integer $course_id Course ID.
	 * @returns array Ids of forums.
	 */
	public function get_by_course_id( $course_id ) {
		$is_course = coursepress_is_course( $course_id );
		if ( false == $is_course ) {
			return array();
		}
		$args = array(
			'post_type' => $this->post_type,
			'fields' => 'ids',
			'nopaging' => true,
			'post_parent' => $course_id,
			'post_status' => 'any',
		);
		$wp_query = new WP_Query( $args );
		if ( $wp_query->have_posts() ) {
			return $wp_query->posts;
		}
		return array();
	}

	/**
	 * Get bulk actions for students listing page.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {

		$status = $this->get_status();
		if ( 'trash' == $status ) {
			$actions = array(
				'restore' => __( 'Restore', 'cp' ),
				'delete' => __( 'Delete Permanently', 'cp' ),
			);
		} else {
			$actions = array(
				'publish' => __( 'Publish', 'cp' ),
				'draft' => __( 'Draft', 'cp' ),
				'trash' => __( 'Move to trash', 'cp' ),
			);
		}

		return $actions;
	}
}
