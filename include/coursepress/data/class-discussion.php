<?php

class CoursePress_Data_Discussion {

	private static $post_type = 'discussions';  // Plural because of legacy
	public static $last_discussion;

	public static function get_format() {

		return array(
			'post_type' => self::get_post_type_name(),
			'post_args' => array(
				'labels' => array(
					'name' => __( 'Forums', 'cp' ),
					'singular_name' => __( 'Forum', 'cp' ),
					'add_new' => __( 'Create New', 'cp' ),
					'add_new_item' => __( 'Create New Thread', 'cp' ),
					'edit_item' => __( 'Edit Thread', 'cp' ),
					'edit' => __( 'Edit', 'cp' ),
					'new_item' => __( 'New Thread', 'cp' ),
					'view_item' => __( 'View Thread', 'cp' ),
					'search_items' => __( 'Search Threads', 'cp' ),
					'not_found' => __( 'No Threads Found', 'cp' ),
					'not_found_in_trash' => __( 'No Threads found in Trash', 'cp' ),
					'view' => __( 'View Thread', 'cp' ),
				),
				'public' => false,
				'show_ui' => false,
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
		$course_title = ! empty( $course_id ) ? get_the_title( $course_id ) : __( 'All courses', 'cp' );
		$course_id = ! empty( $course_id ) ? $course_id : 'all';

		$unit_id = (int) get_post_meta( $n_id, 'unit_id', true );
		$unit_title = ! empty( $unit_id ) ? get_the_title( $unit_id ) : __( 'All units', 'cp' );
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
			'post_content' => CoursePress_Helper_Utility::filter_content( ! $discussion_description ? $_POST['discussion_description'] : $discussion_description ),
			'post_status'  => $post_status,
			'post_title'   => CoursePress_Helper_Utility::filter_content( ( ! $discussion_title ? $_POST['discussion_name'] : $discussion_title ), true ),
			'post_type'    => self::$post_type,
		);

		if ( isset( $_POST['discussion_id'] ) ) {
			$post['ID'] = $_POST['discussion_id']; //If ID is set, wp_insert_post will do the UPDATE instead of insert
		}

		$post_id = wp_insert_post( $post );

		//Update post meta
		if ( $post_id ) {

			if ( ! isset( $_POST['discussion_id'] ) ) {//new discussion added
				$instructors = CoursePress_Data_Course::get_setting( $course_id, 'instructors', false );
				do_action( 'new_discussion_added_instructor_notification', $user_id, $course_id, $instructors );

				$students = CoursePress_Data_Course::get_student_ids( $course_id );
				do_action( 'new_discussion_added_student_notification', $user_id, $course_id, $students );
			}

			if ( ! $unit_id ) {
				$unit_id = $_POST['units_dropdown'];
			}

			/**
			 * Try to add course_id - it should be unique post meta.
			 */
			$success == add_post_meta( $post_id, 'course_id', $course_id, true );
			if ( ! $success ) {
				update_post_meta( $post_id, 'course_id', $course_id );
			}

			/**
			 * Try to add unit_id - it should be unique post meta.
			 */
			$success = add_post_meta( $post_id, 'unit_id', $unit_id, true );
			if ( ! $success ) {
				update_post_meta( $post_id, 'unit_id', $unit_id );
			}

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

	public static function init() {
		// Auto-approved discussion comment
		add_filter( 'pre_comment_approved', array( __CLASS__, 'approved_discussion_comment' ), 100, 2 );

		// Alter comments before saving to DB.
		add_filter( 'preprocess_comment', array( __CLASS__, 'preprocess_discussion_comment' ), 100 );

		// Redirect back
		add_filter( 'comment_post_redirect', array( __CLASS__, 'redirect_back' ), 10, 2 );

		// Hooked into no-access redirect for non-loggin users
		add_filter( 'coursepress_no_access_redirect_url', array( __CLASS__, 'is_unsubscribe_link' ), 10, 2 );

		// Unsubscribe message
		add_action( 'the_content', array( __CLASS__, 'unsubscribe_from_discussion' ) );

        add_filter( 'wp_list_comments_args', array( __CLASS__, 'wp_list_comments_args' ) );
	}

	public static function approved_discussion_comment( $is_approved, $commentdata ) {
		if ( self::is_comment_in_discussion( $commentdata['comment_post_ID'] ) ) {
			/**
			 * Filter discussion comments status.
			 *
			 * @param (bool) $comment_status
			 * @param (int) $discussion_id
			 * @param (array) $commentdata
			 **/
			$is_approved = apply_filters( 'coursepress_discussion_comment_status', 1, $commentdata['comment_post_ID'], $commentdata );
		}

		return $is_approved;
	}

	public static function comment_post_types() {
		return array(
			self::get_post_type_name(),
			CoursePress_Data_Module::get_post_type_name(),
			CoursePress_Data_Unit::get_post_type_name(),
		);
	}

	/**
	 * Check if a comment is from a discussion or discussion module.
	 *
	 * @since 2.0
	 *
	 * @param (int) $comment_post_ID
	 **/
	public static function is_comment_in_discussion( $comment_id ) {
		$post_type = get_post_field( 'post_type', $comment_id );

		return in_array( $post_type, self::comment_post_types() );
	}

	public static function preprocess_discussion_comment( $comment_data ) {
		if ( empty( $comment_data['comment_post_ID'] ) ) {
			return $comment_data;
		}
		$post_id = (int) $comment_data['comment_post_ID'];
		$post_type = get_post_type( $post_id );
		$post_types = self::comment_post_types();

		if ( in_array( $post_type, $post_types ) ) {
			// Disable comment notifications
			add_filter( 'notify_moderator', '__return_null', 105 );
			add_filter( 'notify_postauthor', '__return_null', 105 );
		}

		return $comment_data;
	}

	/**
	 * Redirect back to discussion or discussion module page.
	 *
	 * @since 2.0
	 **/
	public static function redirect_back( $location, $comment ) {
		$post_id = $comment->comment_post_ID;

		if ( self::is_comment_in_discussion( $post_id ) ) {
			$location = CoursePress_Template_Discussion::discussion_url( $post_id );
		}

		return $location;
	}

	public static function is_discussion_subscriber( $user_id, $discussion_id ) {
		$key = CoursePress_Helper_Discussion::get_user_meta_name( $discussion_id );
		$value = get_user_meta( $user_id, $key, true );
		$value = CoursePress_Helper_Discussion::sanitize_cp_subscribe_to_key( $value );
		return 'subscribe-all' == $value;
	}

	/**
	 * Check user is subscribing only reactions.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $user_id User ID.
	 * @param integer $discussion_id Discussion ID.
	 * @return boolean User subscribe reactions?
	 */
	public static function is_discussion_reactions_subscriber( $user_id, $discussion_id ) {
		$key = CoursePress_Helper_Discussion::get_user_meta_name( $discussion_id );
		$value = get_user_meta( $user_id, $key, true );
		$value = CoursePress_Helper_Discussion::sanitize_cp_subscribe_to_key( $value );
		return 'subscribe-reactions' == $value;
	}

	/**
	 * Check user is subscribing all or reactions.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $user_id User ID.
	 * @param integer $discussion_id Discussion ID.
	 * @return boolean User subscribe any type.
	 */
	public static function is_subscriber( $user_id, $discussion_id ) {
		return
			self::is_discussion_subscriber( $user_id, $discussion_id )
			|| self::is_discussion_reactions_subscriber( $user_id, $discussion_id );
	}

	public static function is_unsubscribe_link( $course_url, $course_id ) {
		if ( isset( $_GET['unsubscribe'] ) && isset( $_GET['uid'] ) ) {
			$user_id = (int) $_GET['uid'];
			$post_id = (int) $_GET['unsubscribe'];

			if ( self::is_subscriber( $user_id, $post_id ) ) {
				// Set the param back to $course_url
				$course_url = add_query_arg(
					array(
						'unsubscribe' => $post_id,
						'uid' => $user_id,
					),
					$course_url
				);
			}
		}

		return $course_url;
	}

	public static function unsubscribe_from_discussion( $content ) {
		if ( isset( $_GET['unsubscribe'] ) && isset( $_GET['uid'] ) ) {
			$user_id = (int) $_GET['uid'];
			$post_id = (int) $_GET['unsubscribe'];

			if ( self::is_subscriber( $user_id, $post_id ) ) {
				// Double check
				$post_type = get_post_field( 'post_type', $post_id );
				$discussion_types = self::comment_post_types();

				if ( in_array( $post_type, $discussion_types ) ) {
					// Remove from discussion subscribers
					delete_user_meta( $user_id, 'cp_subscribe_to_' . $post_id );

					// Hooked to the content to show unsubscribe message.
					$message = sprintf( '<h3 class="cp-unsubscribe-title">%s</h3>', __( 'Unsubscribe Successful', 'cp' ) );
					$message .= '<p>' . sprintf( __( 'You have been removed from "%s" discussion.', 'cp' ), get_the_title( $post_id ) ) . '</p>';

					/**
					 * Filter the unsubscribe message before printing.
					 *
					 * @param (string) $message
					 * @param (string) $discussion_id
					 * @param (int) $user_id
					 **/
					$message = apply_filters( 'coursepress_unsubscribe_message', $message, $post_id, $user_id );

					$content = $message;
				}
			}
		}

		return $content;
	}

	/**
	 * Update user subscription status.
	 *
	 * @since 2.0.0
	 *
	 * @param string $user_id User ID.
	 * @param string $discussion_id discussion ID.
	 * @param string $new_value New value of subscribtion, default false.
	 */
	public static function update_user_subscription( $user_id, $discussion_id, $new_value = false ) {
		if ( empty( $new_value ) ) {
			$new_value = CoursePress_Helper_Discussion::get_value_from_post();
		}
		$default_key = CoursePress_Helper_Discussion::get_default_key();
		$user_meta_key = CoursePress_Helper_Discussion::get_user_meta_name( $discussion_id );
		if ( $new_value && $default_key != $new_value ) {
			update_user_meta( $user_id, $user_meta_key, $new_value );
		} else {
			delete_user_meta( $user_id, $user_meta_key );
		}
	}

	/**
	 * Add comment in ajax mode.
	 *
	 * @since 2.0.0
	 *
	 * @param object $data Data from request, see class-core.php
	 * @param array $json_data Data to send back.
	 * @param array Data to send back.
	 */
	public function comment_add_new( $data, $json_data ) {
		$json_data['success'] = false;
		if ( ! isset( $data->nonce ) ) {
			$json_data['html'] = 'no nonce';
			return $json_data;
		}
		$check_nonce = CoursePress_Helper_Discussion::check_nonce_add( $data->nonce );
		if ( false == $check_nonce ) {
			$json_data['html'] = 'wrong nonce';
			return $json_data;
		}
		$user_id = get_current_user_id();
		$commentdata = array(
			'comment_post_ID' => $data->comment_post_ID,
			'comment_content' => $data->comment_content,
			'comment_parent' => $data->comment_parent,
			'user_id' => $user_id,
		);
		$json_data['success'] = true;
		$json_data['data'] = $commentdata;
		/**
		 * Answer mode, possible values, but 'single-comment' only when we
		 * define single comment callback.
		 *
		 * - 'single-comment' - return only one comment
		 * - 'full-list'	  - return full list of comments
		 */
		$json_data['answer_mode'] = 'full-list';
		$comment_id = $json_data['data']['comment_id'] = wp_new_comment( $commentdata );
		/**
		 * update user subscribtion
		 */
		$field_name = CoursePress_Helper_Discussion::get_field_name();
		$value = isset( $data->$field_name ) ? $data->$field_name : CoursePress_Helper_Discussion::get_default_key();
		self::update_user_subscription( $user_id, $data->comment_post_ID, $value );
		/**
		 * set course_id
		 */
		$course_id = CoursePress_Data_Module::get_course_id_by_module( $data->comment_post_ID );
		CoursePress_Data_Course::set_last_course_id( $course_id );

		/**
		 * Allow to create single comment answer. It speed up comments, but
		 * this is advance settings and HTML classes must match standard WP
		 * classes. In other way it will be not work. Default it is not used.
		 * It is used by Academy site.
		 *
		 * @since 2.0.0
		 * @param mixed $content Default false.
		 * @param integer $comment_id Comment ID.
		 * @param array $data Request data.
		 */
		$single_comment_output = apply_filters( 'coursepress_discussion_single_comment', false, $comment_id, $data );

		if ( ! empty( $single_comment_output ) ) {
			$json_data['data']['html'] = $single_comment_output;
			$json_data['answer_mode'] = 'single-comment';
			$json_data['comment_parent'] = $data->comment_parent;
		} else {
			$json_data['data']['html'] = CoursePress_Template_Discussion::get_comments( $data->comment_post_ID );
		}

		// Update course progress
		$student_data = CoursePress_Data_Student::get_completion_data( $user_id, $course_id );
		if ( ! isset( $student_data['units'] ) && ! isset( $student_data['units'][ $data->comment_post_ID ] ) ) {
			CoursePress_Helper_Utility::set_array_val( $student_data, 'units/' . $data->comment_post_ID, array() );
			CoursePress_Data_Student::update_completion_data( $user_id, $course_id, $student_data );
		}
		CoursePress_Data_Student::get_calculated_completion_data( $user_id, $course_id );

		$module = get_post( $data->comment_post_ID );
		$unit_id = $module->post_parent;
		$page = CoursePress_Data_Shortcode_Template::get_module_page( $course_id, $unit_id, $module->ID );

		// Generate next nav
		$next = CoursePress_Data_Course::get_next_accessible_module(
			$course_id,
			$unit_id,
			$page,
			$data->comment_post_ID
		);
		$next_module_class = array( 'focus-nav-next' );
		$labels = array(
			'pre_text' => __( '&laquo; Previous', 'cp' ),
			'next_text' => __( 'Next &raquo;', 'cp' ),
			'next_section_title' => __( 'Proceed to the next section', 'cp' ),
			'next_module_title' => __( 'Proceed to the next module', 'cp' ),
			'next_section_text' => __( 'Next Section', 'cp' ),
		);
		extract( $labels );

		if ( 'section' == $next['type'] ) {
			$next_module_class[] = 'next-section';
			$title = '';
			$text = $next_section_text;
		} else {
			$title = $next_module_title;
			$text = $next_text;
		}

		$json_data['data']['next_nav'] = CoursePress_Data_Shortcode_Template::show_nav_button(
			$next,
			$text,
			$next_module_class,
			$title
		);

		/**
		 * notify users
		 */
		CoursePress_Data_Discussion_Cron::add_comment_id( $comment_id );

		return $json_data;
	}

	/**
	 * Update discusssion
	 *
	 * @since 2.0.0
	 */
	public static function ajax_update() {

		$data = json_decode( file_get_contents( 'php://input' ) );

		$json_data = array();
		$success = false;

		$action = isset( $data->action ) ? $data->action : '';
		$json_data['action'] = $action;

		if ( empty( $data->action ) ) {
			$json_data['message'] = __( 'Discussion Update: No action.', 'cp' );
			wp_send_json_error( $json_data );
		}

		switch ( $action ) {

			case 'delete':
				if ( wp_verify_nonce( $data->data->nonce, 'delete-discussion' ) ) {
					$discussion_id = $data->data->discussion_id;
					if ( self::is_correct_post_type( $discussion_id ) ) {
						wp_delete_post( $discussion_id );
						$json_data['discussion_id'] = $discussion_id;
						$json_data['nonce'] = wp_create_nonce( 'delete-discussion' );
						$success = true;
					}
				}
				break;

			case 'toggle':
				$discussion_id = $data->data->discussion_id;
				$json_data['ID'] = $discussion_id;
				$nounce_name = sprintf( 'publish-discussion-%d', $discussion_id );
				if ( wp_verify_nonce( $data->data->nonce, $nounce_name ) ) {
					if ( self::is_correct_post_type( $discussion_id ) ) {
						wp_update_post( array(
							'ID' => $discussion_id,
							'post_status' => $data->data->status,
						) );
						$json_data['nonce'] = wp_create_nonce( 'publish-discussion' );
						$json_data['discussion_id'] = $discussion_id;
						$json_data['state'] = $data->data->state;
						$success = true;
					} else {
						$json_data['message'] = __( 'Discussion update failed: post type missmatch.', 'cp' );
					}
				} else {
					$json_data['message'] = __( 'Discussion update failed: wrong nounce.', 'cp' );
				}
				break;

			case 'bulk_unpublish':
			case 'bulk_publish':
			case 'bulk_delete':

				$ids = $data->data->ids;

				if ( wp_verify_nonce( $data->data->nonce, 'bulk_action_nonce' ) ) {

					foreach ( $ids as $id ) {

						if ( ! self::is_correct_post_type( $id ) ) {
							continue;
						}

						if ( 'bulk_unpublish' === $action ) {
							if ( CoursePress_Data_Capabilities::can_update_discussion( $id ) ) {
								wp_update_post( array(
									'ID' => $id,
									'post_status' => 'draft',
								) );
							}
						}

						if ( 'bulk_publish' === $action ) {
							if ( CoursePress_Data_Capabilities::can_update_discussion( $id ) ) {
								wp_update_post( array(
									'ID' => $id,
									'post_status' => 'publish',
								) );
							}
						}

						if ( 'bulk_delete' === $action ) {
							if ( CoursePress_Data_Capabilities::can_delete_discussion( $id ) ) {
								wp_delete_post( $id );
							}
						}
					}
					$success = true;
				}
				$json_data['ids'] = $ids;
				break;

		}

		if ( $success ) {
			wp_send_json_success( $json_data );
		} else {
			wp_send_json_error( $json_data );
		}
	}

	/**
	 * Check is post type match?
	 *
	 * @since 2.0.0
	 *
	 * @param int|WP_Post Post ID or post object.
	 * @return boolean True on success, false on failure.
	 */
	public static function is_correct_post_type( $post ) {
		$post_type = get_post_type( $post );
		return self::$post_type == $post_type;
	}

	/**
	 * Setup comments thread data.
	 *
	 * @since 2.0.0
	 */
	public static function wp_list_comments_args( $args ) {
		global $post;
		/**
		 * No post? return!
		 */
		if ( !is_object( $post ) ) {
			return $args;
		}
		/**
		 * Wrong post type? return!
		 */
		if ( 'course_discussion' != $post->post_type ) {
			return $args;
		}
		/**
		 * How deep (in comment replies) should the comments be fetched.
		 */
		$value = get_post_meta( $post->ID, 'thread_comments_depth', true );
		if ( ! empty( $value ) ) {
			$args['max_depth'] = $value;
		}
		/**
		 * The number of items to show for each page of comments.
		 */
		$value = get_post_meta( $post->ID, 'comments_per_page', true );
		if ( ! empty( $value ) ) {
			$args['per_page'] = $value;
		}
		return $args;
	}

}
