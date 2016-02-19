<?php

class CoursePress_View_Admin_Communication_Notification {

	public static $slug = 'coursepress_notifications';
	private static $title = '';
	private static $menu_title = '';

	public static function init() {
		self::$title = __( 'Notifications', 'CP_TD' );
		self::$menu_title = __( 'Notifications', 'CP_TD' );

		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'render_page' ) );
		add_filter( 'coursepress_admin_valid_pages', array( __CLASS__, 'add_valid' ) );
		add_filter( 'coursepress_admin_pages', array( __CLASS__, 'add_page' ) );
		add_action( 'coursepress_settings_page_pre_render_' . self::$slug, array( __CLASS__, 'process_form' ) );

		// Update Notification
		add_action( 'wp_ajax_update_notification', array( __CLASS__, 'update_notification' ) );
	}

	public static function add_page( $pages ) {
		$pages[ self::$slug ] = array(
			'title' => self::$title,
			'menu_title' => self::$menu_title,
		);

		return $pages;
	}

	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;

		return $valid_pages;
	}

	public static function process_form() {

		//
		// if ( isset( $_GET['action'] ) && isset( $_GET['id'] ) && 'edit' === $_GET['action'] && 'new' === $_GET['id'] ) {
		// }
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'edit_notification' ) ) {

			// Update the notification
			$id = isset( $_REQUEST['id'] ) ? (int) $_REQUEST['id'] : false;

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

		$url = admin_url( 'admin.php?page=' . self::$slug . '&action=edit&id=new' );

		$content = '<div class="coursepress_communications_wrapper notifications wrap">' .
			'<h3>' . esc_html( CoursePress::$name ) . ' : ' . esc_html( self::$menu_title ) . '
			<a class="add-new-h2" href="' . esc_url_raw( $url ) . '">' . esc_html__( 'New Notification', 'CP_TD' ) . '</a>
			</h3>
			<hr />';

		if ( empty( $action ) ) {

			$bulk_nonce = wp_create_nonce( 'bulk_action_nonce' );
			$content .= '<div class="nonce-holder" data-nonce="' . $bulk_nonce . '"></div>';
			ob_start();
			$list_notification->display();
			$content .= ob_get_clean();
		} else {
			switch ( $action ) {
				case 'edit':
					$content .= self::render_edit_page();
					break;
			}
		}

		$content .= '</div>';

		echo $content;
	}

	public static function render_edit_page() {
		$the_id = isset( $_GET['id'] ) ? $_GET['id'] : false;
		$the_id = 'new' === $the_id ? $the_id : (int) $the_id;

		if ( empty( $the_id ) ) {
			return '';
		}

		if ( 'new' !== $the_id ) {
			$post = get_post( $the_id );
			$attributes = CoursePress_Data_Notification::attributes( $the_id );
			$course_id = $attributes['course_id'];
			$post_status = $post->post_status;
			$post_title = $post->post_title;
			$post_content = $post->post_content;
		} else {
			$course_id = 'all';
			$post_status = 'publish';
			$post_title = '';
			$post_content = '';
		}

		$options = array();
		$options['value'] = $course_id;
		$options['class'] = 'medium';
		$options['first_option'] = array(
			'text' => __( 'All courses', 'CP_TD' ),
			'value' => 'all',
		);

		$content = '';

		$content .= '<form method="POST" class="edit">';

		$content .= '
			<input type="hidden" name="post_status" value="' . esc_attr( $post_status ) . '" />
			' . wp_nonce_field( 'edit_notification', '_wpnonce', true, false ) . '
			<label><strong>' . esc_html__( 'Notification Title', 'CP_TD' ). '</strong><br />
			<input type="text" class="wide" name="post_title" value="' . esc_attr( $post_title ) . '" /></label>

			<label><strong>' . esc_html__( 'Notification Content', 'CP_TD' ). '</strong><br />';

		$editor_name = 'post_content';
		$editor_id = 'postContent';
		$args = array(
			'textarea_name' => $editor_name,
			'editor_class' => 'cp-editor',
			'textarea_rows' => 10,
		);

		// Filter $args
		$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

		ob_start();
		wp_editor( $post_content, $editor_id, $args );
		$content .= ob_get_clean();
		$content .= '</label>';

		$content .= '
			<label><strong>' . esc_html__( 'Related Course', 'CP_TD' ) . '</strong><br />
			' . CoursePress_Helper_UI::get_course_dropdown( 'course_id', 'meta_course_id', false, $options ) . '
			</label>
			<label class="right">
				<input type="submit" class="button button-primary" value="' . esc_attr__( 'Save Notification', 'CP_TD' ) . '" />
			</label>
		';

		$content .= '</form>';

		return $content;
	}

	public static function update_notification() {

		$data = json_decode( file_get_contents( 'php://input' ) );
		$json_data = array();
		$success = false;

		$action = isset( $data->action ) ? $data->action : '';
		$json_data['action'] = $action;

		if ( empty( $data->action ) ) {
			$json_data['message'] = __( 'Notification Update: No action.', 'CP_TD' );
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
							wp_update_post( array(
								'ID' => $id,
								'post_status' => 'draft',
							) );
						}

						if ( 'bulk_publish' === $action ) {
							wp_update_post( array(
								'ID' => $id,
								'post_status' => 'publish',
							) );
						}

						if ( 'bulk_delete' === $action ) {
							wp_delete_post( $id );
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
}
