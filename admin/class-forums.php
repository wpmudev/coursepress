<?php
/**
 * Forum admin controller
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Admin_Forums extends CoursePress_Admin_Controller_Menu {
	var $parent_slug = 'coursepress';
	var $slug = 'coursepress_discussions';
	var $with_editor = false;
	protected $cap = 'coursepress_discussions_cap';
	protected $list_forums;

	/**
	 * Class init
	 */
	public static function init() {
		self::$post_type = CoursePress_Data_Discussion::get_post_type_name();
		self::set_labels();
	}

	/**
	 * Edit screen init
	 */
	public static function init_edit() {
		$id = intval( isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0 );
		if ( empty( $id ) ) {
			/**
			 * Check if user can not add new discussion
			 */
			if ( ! CoursePress_Data_Capabilities::can_add_discussions() ) {
				wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );
			}
		} else {
			/**
			 * Check if user can not update this discussion
			 */
			if ( ! CoursePress_Data_Capabilities::can_update_discussion( $id ) ) {
				wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );
			}
		}
		wp_reset_vars( array( 'action' ) );
		if ( wp_is_mobile() ) {
			wp_enqueue_script( 'jquery-touch-punch' );
		}
		include_once ABSPATH.'/wp-admin/includes/meta-boxes.php';
		wp_enqueue_script( 'post' );
		self::init();
		/**
		 * Add meta boxe save
		 */
		add_meta_box(
			'submitdiv',
			__( 'Save', 'coursepress' ),
			array( __CLASS__, 'box_submitdiv' ),
			self::$post_type,
			'side',
			'high'
		);
		add_meta_box(
			'related_courses',
			__( 'Related Courses', 'coursepress' ),
			array( __CLASS__, 'box_release_courses' ),
			self::$post_type,
			'side'
		);
		add_meta_box(
			'settings',
			__( 'Settings', 'coursepress' ),
			array( __class__, 'box_settings' ),
			self::$post_type,
			'side'
		);
	}

	public function get_labels() {
		return array(
			'title' => __( 'CoursePress Forums', 'coursepress' ),
			'menu_title' => __( 'Forums', 'coursepress' ),
		);
	}

	public function process_form() {
		self::init();
		self::save_discussion();
		self::update_discussion();
		/**
		 * Find action
		 */
		$action = -1;
		if ( ! empty( $_REQUEST['action'] ) ) {
			$action = $_REQUEST['action'];
		}
		if ( -1 == $action && ! empty( $_REQUEST['action2'] ) ) {
			$action = $_REQUEST['action2'];
		}
		$action = strtolower( trim( $action ) );
		/**
		 * filter
		 */
		if ( 'filter' === $action ) {
			self::filter_redirect();
		}
		/**
		 * build
		 */
		if ( 'edit' == $action ) {
			$this->slug = 'coursepress_edit-forum';
			// Set before the page
			add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );
		} else {
			$this->slug = 'coursepress_forums-table';
			// Prepare items
			add_screen_option( 'per_page', array( 'default' => 20, 'option' => 'coursepress_forum_per_page' ) );
			$this->list_forums = new CoursePress_Admin_Table_Forums();
			$this->list_forums->prepare_items();
		}
	}

	public static function save_discussion() {
		// Add or edit discussion
		if ( ! isset( $_POST['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'edit_discussion' ) ) {
			return;
		}

		// Update the discussion
		$id = isset( $_REQUEST['id'] ) ? (int) $_REQUEST['id'] : false;
		$content = CoursePress_Helper_Utility::filter_content( $_POST['post_content'] );
		$title = CoursePress_Helper_Utility::filter_content( $_POST['post_title'] );

		// Validate
		if ( empty( $title ) ) {
			self::$error_message = __( 'The topic title is required!', 'coursepress' );
			return;
		} elseif ( empty( $_POST['post_content'] ) ) {
			self::$error_message = __( 'The topic description is required!', 'coursepress' );
			return;
		} elseif ( ! empty( $id ) && ! CoursePress_Data_Capabilities::can_update_discussion( $id ) ) {
			self::$error_message = __( 'You have no permission to edit this topic!', 'coursepress' );
			return;
		}

		$course_id = 'all' === $_POST['meta_course_id'] ? $_POST['meta_course_id'] : (int) $_POST['meta_course_id'];
		$unit_id = 'course' === $_POST['meta_unit_id'] ? $_POST['meta_unit_id'] : (int) $_POST['meta_unit_id'];
		$post_status = isset( $_POST['post_status'] ) ? $_POST['post_status'] : 'draft';

		$args = array(
			'post_title' => $title,
			'post_content' => $content,
			'post_type' => self::$post_type,
			'post_status' => $post_status,
		);

		if ( empty( $id ) || 'new' == $id ) {
			$id = wp_insert_post( $args );
		} else {
			$args['ID'] = $id;
			wp_update_post( $args );
		}

		CoursePress_Helper_Utility::add_meta_unique( $id, 'course_id', $course_id );

		/**
		 * Try to add unit_id - it should be unique post meta.
		 */
		CoursePress_Helper_Utility::add_meta_unique( $id, 'unit_id', $unit_id );

		/**
		 * email_notification
		 */
		$name = 'email_notification';
		$value = isset( $_POST[ $name ] )? $_POST[ $name ]:'no';
		if ( ! preg_match( '/^(yes|no)$/', $value ) ) {
			$value = 'no';
		}
		CoursePress_Helper_Utility::add_meta_unique( $id, $name, $value );

		/**
		 * thread_comments_depth
		 */
		$name = 'thread_comments_depth';
		$value = isset( $_POST[ $name ] )? intval( $_POST[ $name ] ):get_option( 'thread_comments_depth', 5 );
		if ( ! is_numeric( $value ) || 0 > $value ) {
			$value = 0;
		}
		CoursePress_Helper_Utility::add_meta_unique( $id, $name, $value );

		/**
		 * comments_per_page
		 */
		$name = 'comments_per_page';
		$value = isset( $_POST[ $name ] )? intval( $_POST[ $name ] ):get_option( 'comments_per_page', 20 );
		if ( ! is_numeric( $value ) || 1 > $value ) {
			$value = 1;
		}
		CoursePress_Helper_Utility::add_meta_unique( $id, $name, $value );

		/**
		 * comments_order
		 */
		$name = 'comments_order';
		$value = isset( $_POST[ $name ] )? $_POST[ $name ]:'newer';
		if ( ! preg_match( '/^(older|newer)$/', $value ) ) {
			$value = 'newer';
		}
		CoursePress_Helper_Utility::add_meta_unique( $id, $name, $value );

		$url = add_query_arg( 'id', $id );
		wp_redirect( esc_url_raw( $url ) );
		exit;
	}

	public static function update_discussion() {
		/**
		 * check action
		 */
		if ( ! isset( $_REQUEST['action'] ) || empty( $_REQUEST['action'] ) ) {
			return;
		}
		$action = strtolower( trim( $_REQUEST['action'] ) );
		/**
		 * check id
		 */
		$id = 0;
		if ( isset( $_REQUEST['id'] ) && ! empty( $_REQUEST['id'] ) ) {
			$id = $_REQUEST['id'];
			if ( is_string( $id ) ) {
				$id = (int) $id;
			}
			if ( ! is_numeric( $id ) ) {
				$id = 0;
			}
			if ( ! CoursePress_Data_Discussion::is_correct_post_type( $id ) ) {
				$id = 0;
			}
		}
		/**
		 * check post (bulk action)
		 */
		$posts = array();
		if ( isset( $_REQUEST['post'] ) && ! empty( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) ) {
			$posts = $_REQUEST['post'];
		}
		/**
		 * have we id or ids to update?
		 */
		if ( empty( $id ) && empty( $posts ) ) {
			return;
		}
		/**
		 * first bulk!
		 */
		if ( ! empty( $posts ) ) {
			if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'bulk-posts' ) ) {
				if ( isset( $_POST['post'] ) && is_array( $_POST['post'] ) ) {
					foreach ( $_POST['post'] as $post_id ) {
						if ( CoursePress_Data_Discussion::is_correct_post_type( $post_id ) ) {
							switch ( $action ) {
								case 'delete':
									wp_delete_post( $post_id );
								break;
								case 'draft':
									$post = array(
									'ID' => $post_id,
									'post_status' => 'draft',
									);
									wp_update_post( $post );
								break;
								case 'publish':
									wp_publish_post( $post_id );
								break;
								case 'trash':
									wp_trash_post( $post_id );
								break;
								case 'untrash':
									wp_untrash_post( $post_id );
								break;
							}
						}
					}
				}
			}
		} else {
			/**
			 * do action
			 */
			switch ( $action ) {
				/**
				 * delete
				 */
				case 'delete' && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'coursepress_delete_discussion' ) :
					$is_correct_post_type = CoursePress_Data_Discussion::is_correct_post_type( $id );
					if ( $is_correct_post_type ) {
						wp_delete_post( $id );
					}
					$url = remove_query_arg(
						array(
						'id',
						'action',
						'_wpnonce',
						)
					);
					wp_safe_redirect( $url ); exit;
				break;
				/**
				 * trash
				 */
				case 'trash' && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'coursepress_trash_discussion' ) :
					$is_correct_post_type = CoursePress_Data_Discussion::is_correct_post_type( $id );
					if ( $is_correct_post_type ) {
						wp_trash_post( $id );
					}
				break;
				/**
				 * untrash
				 */
				case 'untrash' && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'coursepress_untrash_discussion' ) :
					$is_correct_post_type = CoursePress_Data_Discussion::is_correct_post_type( $id );
					if ( $is_correct_post_type ) {
						wp_untrash_post( $id );
					}
				break;
			}
		}
	}

	/**
	 * Content of box related courses
	 *
	 * @since 2.0.0
	 *
	 * @return string Content of related courses.
	 */
	public static function box_release_courses( $post ) {
		$the_id = isset( $post->ID ) ? $post->ID : 'new';

		if ( empty( $the_id ) ) {
			return '';
		}
		$course_id = 'all';
		$unit_id = 'course';
		if ( 'new' !== $the_id && ! empty( $the_id ) ) {
			if ( ! CoursePress_Data_Capabilities::can_update_discussion( $the_id ) ) {
				_e( 'You do not have permission to edit this discussion.', 'coursepress' );
				return;
			}
			$post = get_post( $the_id );
			$attributes = CoursePress_Data_Discussion::attributes( $the_id );
			$course_id = $attributes['course_id'];
			$unit_id = $attributes['unit_id'];
		} else {
			if ( ! CoursePress_Data_Capabilities::can_add_discussion( 0 ) ) {
				_e( 'You do not have permission to add discussion.', 'coursepress' );
				return;
			}
		}
		$options = array();
		$options['value'] = $course_id;
		if ( ! CoursePress_Data_Capabilities::can_add_discussion_to_all() ) {
			$options['courses'] = self::get_courses();
			if ( empty( $options['courses'] ) ) {
				_e( 'You do not have permission to add discussion.', 'coursepress' );
				return;
			}
		}

		printf( '<h4>%s</h4>', __( 'Select Course', 'unit' ) );
		echo CoursePress_Helper_UI::get_course_dropdown( 'course_id', 'meta_course_id', false, $options );
		/**
		 * units
		 */
		$options_unit = array();
		$options_unit['value'] = $unit_id;
		$options_unit['first_option'] = array(
			'text' => __( 'All units', 'coursepress' ),
			'value' => 'course',
		);
		printf( '<h4>%s</h4>', esc_html__( 'Select Unit', 'coursepress' ) );
		echo CoursePress_Helper_UI::get_unit_dropdown( 'unit_id', 'meta_unit_id', $course_id, false, $options_unit );
	}

	/**
	 * Get courses list if curen user do not have 'manage_options'
	 *
	 * @since 2.0.0
	 *
	 * @return array $courses Array of WP_Post objects
	 */
	public static function get_courses() {
		$user_id = get_current_user_id();
		if ( empty( $user_id ) ) {
			return array();
		}

		$courses = self::get_accessable_courses();

		if ( ! empty( $courses ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_create_my_discussion_cap' );
			$is_author = user_can( $user_id, $capability );
			$capability2 = apply_filters( 'coursepress_capabilities', 'coursepress_create_my_assigned_discussion_cap' );
			$is_instructor = user_can( $user_id, $capability2 );

			foreach ( $courses as $index => $course ) {
				if ( $course->post_author == $user_id && ! $is_author ) {
					unset( $courses[ $index ] );
				}
				if ( CoursePress_Data_Capabilities::is_course_instructor( $course ) && ! $is_instructor ) {
					unset( $courses[ $index ] );
				}
			}
		}

		return $courses;
	}
	/**
	 * Content of box submitbox
	 *
	 * @since 2.0.0
	 *
	 * @return string Content of submitbox.
	 */
	public static function box_submitdiv( $post ) {
		self::submitbox( $post, 'can_change_status_discussion' );
	}

	/**
	 * Add button "Add new Notification".
	 *
	 * @since 2.0.0
	 */
	public static function add_button_add_new() {
		if ( ! CoursePress_Data_Capabilities::can_add_discussions() ) {
			return;
		}
		$label = self::get_label_by_name( 'add_new' );
		self::button_add( $label );
	}

	/**
	 * Get label
	 *
	 * @since @2.0.0
	 *
	 * @param string $label Label Name.
	 * @return string Label value.
	 */
	public static function get_label_by_name( $label ) {
		self::set_labels();
		if ( isset( self::$labels[ self::$post_type ]->$label ) ) {
			return self::$labels[ self::$post_type ]->$label;
		}
		return '';
	}

	/**
	 * 'Settings' metabox. This metabox must contain the following options.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Current post or empty post object.
	 */
	public static function box_settings( $post ) {
		/**
		 * email_notification
		 */
		$email_notification = get_post_meta( $post->ID, 'email_notification', true );
		printf( '<h4>%s</h4>', __( 'Enable email notification', 'coursepress' ) );
		printf( '<input type="checkbox" name="email_notification" value="yes" %s id="meta_email_notification" />', checked( $email_notification, 'yes', false ) );
		printf( ' <label for="meta_email_notification">%s</label>', __( 'Enable email notification', 'coursepress' ) );
		/**
		 * thread_comments_depth
		 */
		$thread_comments_depth = get_post_meta( $post->ID, 'thread_comments_depth', true );
		if ( empty( $thread_comments_depth ) ) {
			$thread_comments_depth = get_option( 'thread_comments_depth', 5 );
		}
		printf( '<h4>%s</h4>', __( 'Threaded comments level', 'coursepress' ) );
		printf( '<input type="number" min="0" value="%d" name="thread_comments_depth" class="small-text" />', $thread_comments_depth );
		/**
		 * comments_per_page
		 */
		$comments_per_page = get_post_meta( $post->ID, 'comments_per_page', true );
		if ( empty( $comments_per_page ) ) {
			$comments_per_page = get_option( 'comments_per_page', 20 );
		}
		printf( '<h4>%s</h4>', __( 'Number of comments per page', 'coursepress' ) );
		printf( '<input type="number" min="0" value="%d" name="comments_per_page" class="small-text" />', $comments_per_page );
		/**
		 * comments_order
		 */
		$attr = array(
			'older' => __( 'Older first', 'coursepress' ),
			'newer' => __( 'Newer first', 'coursepress' ),
		);
		$comments_order = get_post_meta( $post->ID, 'comments_order', true );
		if ( empty( $comments_order ) || ! array_key_exists( $comments_order, $attr ) ) {
			$comments_order = 'newer';
		}
		printf( '<h4>%s</h4>', __( 'Comments order', 'coursepress' ) );
		echo '<ul>';
		foreach ( $attr as $key => $label ) {
			printf(
				'<li><label><input type="radio" name="comments_order" value="%s" %s /> %s</label></li>',
				esc_attr( $key ),
				checked( $comments_order, $key, false ),
				$label
			);
		}
		echo '</ul>';
	}

	/**
	 * Get courses depend on discussions capabilities.
	 *
	 * @since 2.0.0
	 *
	 * @param integer|null Checked user ID.
	 * @param string|null Course status.
	 * @return array Array of courses.
	 */
	public static function get_accessable_courses( $user_id = '', $post_status = 'publish' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		} elseif ( is_object( $user_id ) ) {
			$user_id = $user_id->ID;
		}
		$args = array(
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'post_status' => $post_status,
			'posts_per_page' => -1,
		);
		if ( ! user_can( $user_id, 'manage_options' ) ) {
			$can_search = false;
			if ( user_can( $user_id, 'coursepress_create_my_discussion_cap' ) ) {
				$args['author'] = $user_id;
				$can_search = true;
			}
			if ( user_can( $user_id, 'coursepress_create_my_assigned_discussion_cap' ) ) {
				$assigned_courses = CoursePress_Data_Instructor::get_assigned_courses_ids( $user_id );
				$args['include'] = $assigned_courses;
				if ( $can_search ) {
					// Let's add the author param via filter hooked.
					unset( $args['author'] );
					add_filter( 'posts_where', array( __CLASS__, 'filter_by_where' ) );
				}
				$can_search = true;
			}
			if ( ! $can_search ) {
				// Bail early
				return array();
			}
		}
		$posts = get_posts( $args );
		return $posts;
	}
}
