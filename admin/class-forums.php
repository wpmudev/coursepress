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
		if ( ! CoursePress_Data_Capabilities::can_add_discussions() ) {
			wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );
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
			__( 'Save', 'cp' ),
			array( __CLASS__, 'box_submitdiv' ),
			self::$post_type,
			'side',
			'high'
		);
		add_meta_box(
			'related_courses',
			__( 'Related Courses', 'cp' ),
			array( __CLASS__, 'box_release_courses' ),
			self::$post_type,
			'side'
		);
		add_meta_box(
			'settings',
			__( 'Settings', 'cp' ),
			array( __class__, 'box_settings' ),
			self::$post_type,
			'side'
		);

	}

	public function get_labels() {
		return array(
			'title' => __( 'CoursePress Forums', 'cp' ),
			'menu_title' => __( 'Forums', 'cp' ),
		);
	}

	public function process_form() {
		self::init();
		self::save_discussion();
		self::update_discussion();

		if ( empty( $_REQUEST['action'] ) || 'edit' !== $_REQUEST['action'] ) {
			$this->slug = 'coursepress_forums-table';

			// Prepare items
			$this->list_forums = new CoursePress_Admin_Table_Forums();
			$this->list_forums->prepare_items();
			add_screen_option( 'per_page', array( 'default' => 20 ) );

		} elseif ( 'edit' == $_REQUEST['action'] ) {
			$this->slug = 'coursepress_edit-forum';

			// Set before the page
			add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );
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
			self::$error_message = __( 'The topic title is required!', 'cp' );
			return;
		} elseif ( empty( $_POST['post_content'] ) ) {
			self::$error_message = __( 'The topic description is required!', 'cp' );
			return;
		} elseif ( ! empty( $id ) && ! CoursePress_Data_Capabilities::can_update_discussion( $id ) ) {
			self::$error_message = __( 'You have no permission to edit this topic!', 'cp' );
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
		// Discussion actions
		if ( empty( $_REQUEST['action'] ) ) {
			return;
		}
		$actions = array(
			'delete',
			'delete2',
			'filter',
		);
		$action = strtolower( trim( $_REQUEST['action'] ) );
		switch ( $action ) {
			case 'delete' && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'coursepress_delete_discussion' ) :
				$id = (int) $_REQUEST['id'];
				// @todo: Add vlidation
				wp_delete_post( $id );
				$url = remove_query_arg(
					array(
					'id',
					'action',
					'_wpnonce',
					)
				);
				wp_safe_redirect( $url ); exit;
			break;

			case 'filter':
				$id = (int) $_REQUEST['course_id'];
				if ( 0 < $id ) {
					$url = add_query_arg( 'course_id', $id );
				} else {
					$url = remove_query_arg( 'course_id' );
				}
				wp_safe_redirect( $url ); exit;
			break;
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
				_e( 'You do not have permission to edit this discussion.', 'cp' );
				return;
			}
			$post = get_post( $the_id );
			$attributes = CoursePress_Data_Discussion::attributes( $the_id );
			$course_id = $attributes['course_id'];
			$unit_id = $attributes['unit_id'];
		} else {
			if ( ! CoursePress_Data_Capabilities::can_add_discussion( 0 ) ) {
				_e( 'You do not have permission to add discussion.', 'cp' );
				return;
			}
		}
		$options = array();
		$options['value'] = $course_id;
		if ( ! CoursePress_Data_Capabilities::can_add_discussion_to_all() ) {
			$options['courses'] = self::get_courses();
			if ( empty( $options['courses'] ) ) {
				_e( 'You do not have permission to add discussion.', 'cp' );
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
			'text' => __( 'All units', 'cp' ),
			'value' => 'course',
		);
		printf( '<h4>%s</h4>', esc_html__( 'Select Unit', 'cp' ) );
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

		$courses = CoursePress_Data_Instructor::get_accessable_courses();

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
		if ( ! CoursePress_Data_Capabilities::can_add_notifications() ) {
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
		printf( '<h4>%s</h4>', __( 'Enable email notification', 'cp' ) );
		printf( '<input type="checkbox" name="email_notification" value="yes" %s id="meta_email_notification" />', checked( $email_notification, 'yes', false ) );
		printf( ' <label for="meta_email_notification">%s</label>', __( 'Enable email notification', 'cp' ) );
		/**
		 * thread_comments_depth
		 */
		$thread_comments_depth = get_post_meta( $post->ID, 'thread_comments_depth', true );
		if ( empty( $thread_comments_depth ) ) {
			$thread_comments_depth = get_option( 'thread_comments_depth', 5 );
		}
		printf( '<h4>%s</h4>', __( 'Threaded comments level', 'cp' ) );
		printf( '<input type="number" min="0" value="%d" name="thread_comments_depth" class="small-text" />', $thread_comments_depth );
		/**
		 * comments_per_page
		 */
		$comments_per_page = get_post_meta( $post->ID, 'comments_per_page', true );
		if ( empty( $comments_per_page ) ) {
			$comments_per_page = get_option( 'comments_per_page', 20 );
		}
		printf( '<h4>%s</h4>', __( 'Number of comments per page', 'cp' ) );
		printf( '<input type="number" min="0" value="%d" name="comments_per_page" class="small-text" />', $comments_per_page );
		/**
		 * comments_order
		 */
		$attr = array(
			'older' => __( 'Older first', 'cp' ),
			'newer' => __( 'Newer first', 'cp' ),
		);
		$comments_order = get_post_meta( $post->ID, 'comments_order', true );
		if ( empty( $comments_order ) || ! array_key_exists( $comments_order, $attr ) ) {
			$comments_order = 'newer';
		}
		printf( '<h4>%s</h4>', __( 'Comments order', 'cp' ) );
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
}
