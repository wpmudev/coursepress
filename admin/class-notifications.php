<?php
/**
 * Course Notification Controller
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Admin_Notifications extends CoursePress_Admin_Controller_Menu {
	var $parent_slug = 'coursepress';
	var $slug = 'coursepress_notifications';
	var $with_editor = false;
	protected $cap = 'coursepress_notifications_cap';
	protected $list_notification;

	public function get_labels() {
		return array(
			'title' => __( 'CoursePress Notifications', 'cp' ),
			'menu_title' => __( 'Notifications', 'cp' ),
		);
	}

	public function process_form() {
		self::save_notification();
		self::update_notification();

		if ( empty( $_REQUEST['action'] ) || 'edit' !== $_REQUEST['action'] ) {
			$this->slug = 'coursepress_notifications-table';

			// Prepare items
			$this->list_notification = new CoursePress_Admin_Table_Notifications();
			$this->list_notification->prepare_items();
			add_screen_option( 'per_page', array( 'default' => 20 ) );

		} elseif ( 'edit' == $_REQUEST['action'] ) {
			$this->slug = 'coursepress_edit-notification';

			// Set before the page
			add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );
		}
	}

	public static function save_notification() {

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'edit_notification' ) ) {

			// Update the notification
			$id = isset( $_REQUEST['id'] ) ? (int) $_REQUEST['id'] : false;
			$content = CoursePress_Helper_Utility::filter_content( $_POST['post_content'] );
			$title = CoursePress_Helper_Utility::filter_content( $_POST['post_title'] );

			// Validate
			if ( empty( $title ) ) {
				self::$error_message = __( 'No notification title!', 'cp' );
				return;
			} elseif ( empty( $_POST['post_content'] ) ) {
				self::$error_message = __( 'No notification content!', 'cp' );
				return;
			} elseif ( ! empty( $id ) && ! CoursePress_Capabilities::can_delete_notification( $id ) ) {
				self::$error_message = __( 'You do not have permission to edit this notification.', 'cp' );
				return;
			}

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

			$url = add_query_arg( 'id', $id );
			wp_redirect( esc_url_raw( $url ) );
			exit;
		}
	}

	public static function update_notification() {
		$actions = array(
			'delete',
			'toggle',
			'unpublish',
			'publish',
			'delete',
			'delete2',
			'filter',
		);

		if ( empty( $_REQUEST['action'] ) || ! in_array( strtolower( $_REQUEST['action'] ), $actions ) ) {
			return;
		}

		$action = strtolower( trim( $_REQUEST['action'] ) );
		$json_data = array();
		$success = false;

		switch ( $action ) {
			case 'filter':
				if ( ! empty( $_POST['course_id'] ) ) {
					$course_id = (int) $_POST['course_id'];
					$url = add_query_arg( 'course_id', $course_id );
					wp_safe_redirect( $url );
				}
				break;
			case 'publish': case 'unpublish': case 'delete':
				if ( ! empty( $_REQUEST['bulk-actions'] ) ) {
					$notification_ids = $_REQUEST['bulk-actions'];

					foreach ( $notification_ids as $id ) {
						if ( 'delete' != $action ) {
							$post_status = 'unpublish' == $action ? 'draft' : $action;

							if ( CoursePress_Data_Capabilities::can_update_notification( $id ) ) {
								wp_update_post( array(
									'ID' => $id,
									'post_status' => $post_status,
								) );
							}
						} else {
							if ( CoursePress_Data_Capabilities::can_delete_notification( $id ) ) {
								wp_delete_post( $id );
							}
						}
					}
				}
				break;

			case 'delete2':
				$id = (int) $_REQUEST['id'];

				if ( CoursePress_Data_Capabilities::can_delete_notification( $id ) ) {
					wp_delete_post( $id );
					$json_data['notification_id'] = $id;
					$json_data['nonce'] = wp_create_nonce( 'delete-notification' );
					$success = true;
				}
				break;

			case 'toggle':
				$data = json_decode( file_get_contents( 'php://input' ) );
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
		$the_id = ! empty( $post->ID ) ? $post->ID : 'new';

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
}
