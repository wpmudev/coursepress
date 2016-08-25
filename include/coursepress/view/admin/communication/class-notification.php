<?php

class CoursePress_View_Admin_Communication_Notification {

	public static $slug = 'coursepress_notifications';
	private static $title = '';
	private static $menu_title = '';
	private static $the_id = false;

	public static function init() {
		self::$title = __( 'Notifications', 'cp' );
		self::$menu_title = __( 'Notifications', 'cp' );

		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'render_page' ) );
		add_filter( 'coursepress_admin_valid_pages', array( __CLASS__, 'add_valid' ) );
		add_filter( 'coursepress_admin_pages', array( __CLASS__, 'add_page' ) );
		add_action( 'coursepress_settings_page_pre_render_' . self::$slug, array( __CLASS__, 'process_form' ) );

		// Update Notification
		add_action( 'wp_ajax_update_notification', array( __CLASS__, 'update_notification' ) );

		/**
		 * load admin page hook
		 *
		 * @since: 2.0.0
		 *
		 */
		add_action( 'load-coursepress-base_page_coursepress_notifications', array( __CLASS__, 'load' ) );

		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );

		add_filter( 'get_user_option_closedpostboxes_notifications', array( __CLASS__, 'fix_closed_boxes' ) );
	}

	public static function add_page( $pages ) {
		$pages[ self::$slug ] = array(
			'title' => self::$title,
			'menu_title' => self::$menu_title,
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			'cap' => apply_filters( 'coursepress_capabilities', 'coursepress_notifications_cap' ),
		);

		return $pages;
	}

	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;

		return $valid_pages;
	}

	public static function process_form() {

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'edit_notification' ) ) {

			// Update the notification
			$id = isset( $_REQUEST['id'] ) ? (int) $_REQUEST['id'] : false;

			/**
			 * check permissions
			 */
			if ( ! empty( $id ) ) {
				if ( ! CoursePress_Data_Capabilities::can_update_notification( $id ) ) {
					return __( 'You do not have permission to edit this notification.', 'cp' );
				}
			}

			$content = CoursePress_Helper_Utility::filter_content( $_POST['post_content'] );
			$title = CoursePress_Helper_Utility::filter_content( $_POST['post_title'] );
			$course_id = 'all' === $_POST['meta_course_id'] ? $_POST['meta_course_id'] : (int) $_POST['meta_course_id'];
			$post_status = isset( $_POST['post_status'] ) ? $_POST['post_status'] : 'draft';

			$args = array(
				'post_title' => $title,
				'post_content' => $content,
				'post_type' => CoursePress_Data_Notification::get_post_type_name(),
				'post_status' => $post_status,
			);

			if ( ! empty( $id ) ) {
				$args['ID'] = $id;
			}

			$id = wp_insert_post( $args );

			update_post_meta( $id, 'course_id', $course_id );

			$url = admin_url( 'admin.php?page=' . self::$slug );
			wp_redirect( esc_url_raw( $url ) );
			exit;
		}
	}

	public static function render_page() {

		$allowed_actions = array( 'edit' );

		$action = isset( $_GET['action'] ) && in_array( $_GET['action'], $allowed_actions ) ? sanitize_text_field( $_GET['action'] ) : '';

		$list_notification = new CoursePress_Helper_Table_NotificationList();
		$list_notification->prepare_items();

		$content = '<div class="wrap">';

		if ( empty( $action ) ) {
			$content .= CoursePress_Helper_UI::get_admin_page_title(
				self::$menu_title,
				__( 'New Notification', 'cp' ),
				admin_url( 'admin.php?page=' . self::$slug . '&action=edit&id=new' ),
				CoursePress_Data_Capabilities::can_add_notification( 0 )
			);
			$bulk_nonce = wp_create_nonce( 'bulk_action_nonce' );
			$content .= '<div class="nonce-holder" data-nonce="' . $bulk_nonce . '"></div>';
			ob_start();
			$list_notification->display();
			$content .= ob_get_clean();
		} else {
			switch ( $action ) {
				case 'edit':
					$title = __( 'Edit Notification', 'cp' );
					if ( isset( $_GET['id'] ) && 'new' == $_GET['id'] ) {
						$title = __( 'Add New Notification', 'cp' );
					}
					$content .= CoursePress_Helper_UI::get_admin_page_title( $title );
					$post_type = CoursePress_Data_Notification::get_post_type_name();
					$admin = self::render_edit_page();
					$content .= '<form method="post" class="edit">';
					$content .= CoursePress_Helper_UI::get_admin_screen( $post_type, $admin );
					$content .= '</form>';
				break;
			}
		}

		$content .= '</div>';

		echo $content;
	}

	public static function render_edit_page() {
		$the_id = self::_get_the_id();

		if ( empty( $the_id ) ) {
			return '';
		}

		$post = null;

		$box = CoursePress_Data_Notification::get_post_type_name();

		do_action( 'add_meta_boxes', $box, $post );

		/** This action is documented in wp-admin/edit-form-advanced.php */
		do_action( 'do_meta_boxes', $box, 'normal', $post );
		/** This action is documented in wp-admin/edit-form-advanced.php */
		do_action( 'do_meta_boxes', $box, 'advanced', $post );
		/** This action is documented in wp-admin/edit-form-advanced.php */
		do_action( 'do_meta_boxes', $box, 'side', $post );

		add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );

		/**
		 * WP control for meta boxes
		 */
		include_once ABSPATH.'/wp-admin/includes/meta-boxes.php';
		wp_enqueue_script( 'post' );

		$content = '';

		$content .= wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false, false );
		$content .= wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false, false );
		$content .= '<div id="post-body-content">';

		if ( 'new' !== $the_id ) {
			if ( ! CoursePress_Data_Capabilities::can_update_notification( $the_id ) ) {
				return __( 'You do not have permission to edit this notification.', 'cp' );
			}
			$post = get_post( $the_id );
			$attributes = CoursePress_Data_Notification::attributes( $the_id );
			$course_id = $attributes['course_id'];
			$post_status = $post->post_status;
			$post_title = $post->post_title;
			$post_content = $post->post_content;
		} else {
			if ( ! CoursePress_Data_Capabilities::can_add_notification( 0 ) ) {
				return __( 'You do not have permission to add notification.', 'cp' );
			}
			$course_id = 'all';
			$post_status = 'publish';
			$post_title = '';
			$post_content = '';
		}

		$options = array();
		$options['value'] = $course_id;
		if ( CoursePress_Data_Capabilities::can_add_notification_to_all() ) {
			$options['first_option'] = array(
				'text' => __( 'All courses', 'cp' ),
				'value' => 'all',
			);
			$courses = false;
		} else {
			$courses = self::get_courses();
			if ( empty( $courses ) ) {
				return __( 'You do not have permission to add notification.', 'cp' );
			}
		}

		$content .= '<input type="hidden" name="post_status" value="' . esc_attr( $post_status ) . '" />';
		$content .= wp_nonce_field( 'edit_notification', '_wpnonce', true, false );
		$content .= CoursePress_Helper_UI::get_admin_edit_title_field(
			$post_title,
			__( 'Notification Title', 'cp' )
		);

		$editor_name = 'post_content';
		$editor_id = 'postContent';
		$args = array(
			'textarea_name' => $editor_name,
			'editor_class' => 'cp-editor',
			'textarea_rows' => 10,
		);

		// Filter $args
		$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

		$content .= '<br /><div id="postdivrich" class="postarea wp-editor-expand">';
		ob_start();
		wp_editor( $post_content, $editor_id, $args );
		$content .= ob_get_clean();
		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	public static function update_notification() {

		$data = json_decode( file_get_contents( 'php://input' ) );
		$json_data = array();
		$success = false;

		$action = isset( $data->action ) ? $data->action : '';
		$json_data['action'] = $action;

		if ( empty( $data->action ) ) {
			$json_data['message'] = __( 'Notification Update: No action.', 'cp' );
			wp_send_json_error( $json_data );
		}

		switch ( $action ) {

			case 'delete':

				if ( wp_verify_nonce( $data->data->nonce, 'delete-notification' ) ) {

					$notification_id = $data->data->notification_id;

					wp_delete_post( $notification_id );

					$json_data['notification_id'] = $notification_id;
					$json_data['nonce'] = wp_create_nonce( 'delete-notification' );
					$success = true;
				}

				break;

			case 'toggle':

				$notification_id = $data->data->notification_id;

				if ( wp_verify_nonce( $data->data->nonce, 'publish-notification' ) ) {

					wp_update_post( array(
						'ID' => $notification_id,
						'post_status' => $data->data->status,
					) );

					$json_data['nonce'] = wp_create_nonce( 'publish-notification' );
					$success = true;

				}

				$json_data['notification_id'] = $notification_id;
				$json_data['state'] = $data->data->state;

				break;

			case 'bulk_unpublish':
			case 'bulk_publish':
			case 'bulk_delete':

				$ids = $data->data->ids;

				if ( wp_verify_nonce( $data->data->nonce, 'bulk_action_nonce' ) ) {

					foreach ( $ids as $id ) {

						if ( 'bulk_unpublish' === $action ) {
							if ( CoursePress_Data_Capabilities::can_update_notification( $id ) ) {
								wp_update_post( array(
									'ID' => $id,
									'post_status' => 'draft',
								) );
							}
						}

						if ( 'bulk_publish' === $action ) {
							if ( CoursePress_Data_Capabilities::can_update_notification( $id ) ) {
								wp_update_post( array(
									'ID' => $id,
									'post_status' => 'publish',
								) );
							}
						}

						if ( 'bulk_delete' === $action ) {
							if ( CoursePress_Data_Capabilities::can_delete_notification( $id ) ) {
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
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_create_my_assigned_notification_cap' );
			$is_instructor = user_can( $user_id, $capability );
			$capability2 = apply_filters( 'coursepress_capabilities', 'coursepress_create_my_notification_cap' );
			$is_author = user_can( $user_id, $capability2 );

			foreach ( $courses as $index => $course ) {
				if ( CoursePress_Data_Capabilities::is_course_instructor( $course ) && ! $is_instructor ) {
					unset( $courses[ $index ] );
				}
				if ( $user_id == $course->post_author && ! $is_author ) {
					unset( $courses[ $index ] );
				}
			}
		}

		return $courses;
	}

	/**
	 * Acction called when page is loaded.
	 *
	 * @since 2.0.0
	 *
	 */
	public static function load() {
		CoursePress_Helper_UI::admin_per_page_add_options(
			'notifications',
			__( 'Notifications', 'cp' )
		);
	}

	/**
	 * Add meta boxes
	 *
	 * @since 2.0.0
	 *
	 */
	public static function add_meta_boxes() {
		$screen = get_current_screen();
		if ( empty( $screen ) || ! isset( $screen->base ) ) {
			return;
		}
		$user_settings = CoursePress_Helper_UI::get_user_boxes_settings();
		add_meta_box(
			'submitdiv',
			__( 'Save', 'cp' ),
			array( __CLASS__, 'box_submitdiv' ),
			CoursePress_Data_Notification::get_post_type_name(),
			isset( $user_settings['submitdiv'] )? $user_settings['submitdiv'] : 'side',
			'high'
		);
		add_meta_box(
			'related_courses',
			__( 'Related courses', 'cp' ),
			array( __CLASS__, 'box_release_courses' ),
			CoursePress_Data_Notification::get_post_type_name(),
			isset( $user_settings['related_courses'] )? $user_settings['related_courses'] : 'side'
		);
	}

	/**
	 * Content of box related courses
	 *
	 * @since 2.0.0
	 *
	 * @return string Content of related courses.
	 */
	public static function box_release_courses() {
		$the_id = self::_get_the_id();

		if ( empty( $the_id ) ) {
			return '';
		}
			$course_id = 'all';
		if ( 'new' !== $the_id ) {
			if ( ! CoursePress_Data_Capabilities::can_update_notification( $the_id ) ) {
				return __( 'You do not have permission to edit this notification.', 'cp' );
			}
			$post = get_post( $the_id );
			$attributes = CoursePress_Data_Notification::attributes( $the_id );
			$course_id = $attributes['course_id'];
		}
		$options = array();
		$options['value'] = $course_id;
		if ( CoursePress_Data_Capabilities::can_add_notification_to_all() ) {
			$options['first_option'] = array(
				'text' => __( 'All courses', 'cp' ),
				'value' => 'all',
			);
		} else {
			$options['courses'] = self::get_courses();
			if ( empty( $options['courses'] ) ) {
				return __( 'You do not have permission to add notification.', 'cp' );
			}
		}
		echo CoursePress_Helper_UI::get_course_dropdown( 'course_id', 'meta_course_id', false, $options );
	}

	/**
	 * Content of box submitbox
	 *
	 * @since 2.0.0
	 *
	 * @return string Content of submitbox.
	 */
	public static function box_submitdiv() {
		echo '<div class="submitbox" id="submitpost"><div id="major-publishing-actions"><div id="publishing-action"><span class="spinner"></span>';
		printf(
			'<input type="submit" class="button button-primary" value="%s" />',
			esc_attr__( 'Save Notification', 'cp' )
		);
		echo '</div><div class="clear"></div></div></div>';
	}

	/**
	 * fix option name
	 *
	 * @since 2.0.0
	 *
	 * @param $value value of autonamed option (always empty).
	 *
	 * @return array Currently closed boxes.
	 */
	public static function fix_closed_boxes( $value ) {
		return get_user_option( 'closedpostboxes_coursepress-base_page_coursepress_notifications' );
	}

	private static function _get_the_id() {
		if ( ! empty( self::$the_id ) ) {
			return self::$the_id;
		}
		self::$the_id = isset( $_GET['id'] ) ? $_GET['id'] : false;
		self::$the_id = 'new' === self::$the_id ? self::$the_id : (int) self::$the_id;
		return self::$the_id;
	}
}
