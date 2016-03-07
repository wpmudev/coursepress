<?php

class CoursePress_Data_Discussion {

	private static $post_type = 'discussions';  // Plural because of legacy
	public static $last_discussion;

	public static function get_format() {

		return array(
			'post_type' => self::get_post_type_name(),
			'post_args' => array(
				'labels' => array(
					'name' => __( 'Discussions', 'CP_TD' ),
					'singular_name' => __( 'Discussion', 'CP_TD' ),
					'add_new' => __( 'Create New', 'CP_TD' ),
					'add_new_item' => __( 'Create New Discussion', 'CP_TD' ),
					'edit_item' => __( 'Edit Discussion', 'CP_TD' ),
					'edit' => __( 'Edit', 'CP_TD' ),
					'new_item' => __( 'New Discussion', 'CP_TD' ),
					'view_item' => __( 'View Discussion', 'CP_TD' ),
					'search_items' => __( 'Search Discussions', 'CP_TD' ),
					'not_found' => __( 'No Discussions Found', 'CP_TD' ),
					'not_found_in_trash' => __( 'No Discussions found in Trash', 'CP_TD' ),
					'view' => __( 'View Discussion', 'CP_TD' ),
				),
				'public' => false,
				'show_ui' => true,
				'publicly_queryable' => false,
				'capability_type' => 'discussion',
				'map_meta_cap' => true,
				'query_var' => true,
				// 'rewrite' => array(
				// 'slug' => trailingslashit( CoursePress_Core::get_slug( 'course' ) ) . '%course%/' . CoursePress_Core::get_slug( 'discussion' )
				// )
			),
		);

	}

	public static function get_post_type_name() {
		return CoursePress_Data_PostFormat::prefix( self::$post_type );
	}

	public static function attributes( $n_id ) {

		if ( is_object( $n_id ) ) {
			$n_id = $n_id->ID;
		} else {
			$n_id = (int) $n_id;
		}

		$course_id = (int) get_post_meta( $n_id, 'course_id', true );
		$course_title = ! empty( $course_id ) ? get_the_title( $course_id ) : __( 'All courses', 'CP_TD' );
		$course_id = ! empty( $course_id ) ? $course_id : 'all';

		$unit_id = (int) get_post_meta( $n_id, 'unit_id', true );
		$unit_title = ! empty( $unit_id ) ? get_the_title( $unit_id ) : __( 'All units', 'CP_TD' );
		$unit_id = ! empty( $unit_id ) ? $unit_id : 'course';
		$unit_id = 'all' === $course_id ? 'course' : $unit_id;

		return array(
			'course_id' => $course_id,
			'course_title' => $course_title,
			'unit_id' => $unit_id,
			'unit_title' => $unit_title,
		);

	}

	public static function get_discussions( $course ) {

		$course = (array) $course;

		$args = array(
			'post_type' => self::get_post_type_name(),
			'meta_query' => array(
				array(
					'key' => 'course_id',
					'value' => $course,
					'compare' => 'IN',
				),
			),
			'post_per_page' => 20,
		);

		return get_posts( $args );

	}


	// Hook from CoursePress_View_Front
	public static function permalink( $permalink, $post, $leavename ) {

		$x = '';

	}

	public static function update_discussion( $discussion_title = '', $discussion_description = '', $course_id = '', $unit_id = '' ) {
			global $wpdb;

			$post_status = 'publish';

			$post = array(
				'post_author'  => get_current_user_id(),
				'post_content' => CoursePress_Helper_Utility::filter_content( $discussion_description == '' ? $_POST['discussion_description'] : $discussion_description ),
				'post_status'  => $post_status,
				'post_title'   => CoursePress_Helper_Utility::filter_content( ( $discussion_title == '' ? $_POST['discussion_name'] : $discussion_title ), true ),
				'post_type'    => self::$post_type,
			);

			if ( isset( $_POST['discussion_id'] ) ) {
				$post['ID'] = $_POST['discussion_id']; //If ID is set, wp_insert_post will do the UPDATE instead of insert
			}

			$post_id = wp_insert_post( $post );

			//Update post meta
			if ( $post_id != 0 ) {

				if ( ! isset( $_POST['discussion_id'] ) ) {//new discussion added
					$instructors = CoursePress_Data_Course::get_setting( $course_id, 'instructors', false );
					do_action( 'new_discussion_added_instructor_notification', $user_id, $course_id, $instructors );

					$students = CoursePress_Data_Course::get_student_ids( $course_id );
					do_action( 'new_discussion_added_student_notification', $user_id, $course_id, $students );
				}

				if ( $unit_id == '' ) {
					$unit_id = $_POST['units_dropdown'];
				}

				update_post_meta( $post_id, 'course_id', $course_id );
				update_post_meta( $post_id, 'unit_id', $unit_id );

				foreach ( $_POST as $key => $value ) {
					if ( preg_match( '/meta_/i', $key ) ) {//every field name with prefix "meta_" will be saved as post meta automatically
						update_post_meta( $post_id, str_replace( 'meta_', '', $key ), CoursePress_Helper_Utility::filter_content( $value ) );
					}
				}
			}

			return $post_id;
	}

	/**
	 * Get single discussions
	 *
	 * Description.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $post_id Optional. Post id of discussion.
	 * @return null/WP_Post Discussion post object or null.
	 */
	public static function get_one( $post_id = 0 ) {
		$post = array(
			'ID'           => 0,
			'post_title'   => '',
			'post_content' => '',
		);
		/**
		 * if no $post_id try guess from $_GET
		 */
		if ( empty( $post_id ) ) {
			if ( isset( $_GET['id'] ) ) {
				$post_id = intval( $_GET['id'] );
			}
		}
		/**
		 * if still no $post_id, then it is new
		 */
		if ( empty( $post_id ) ) {
			return $post;
		}
		/**
		 * check post if not exists, then new
		 */
		$discussion = get_post( $post_id );
		if ( empty( $discussion ) ) {
			return $post;
		}
		/**
		 * check post_type to avoid geting any content
		 */
		if ( self::$post_type != $discussion->post_type ) {
			return $post;
		}
		/**
		 * check post author
		 */
		if ( get_current_user_id() != $discussion->post_author ) {
			return $post;
		}
		/**
		 * finally!
		 */
		$post['post_title']   = $discussion->post_title;
		$post['post_content'] = $discussion->post_content;
		$post['ID']           = $discussion->ID;
		return $post;
	}
}
