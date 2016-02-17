<?php

class CoursePress_View_Admin_Communication_Discussion {

	public static $slug = 'coursepress_discussions';
	private static $title = '';
	private static $menu_title = '';

	public static function init() {

		self::$title = __( 'Discussions', CoursePress::TD );
		self::$menu_title = __( 'Discussions', CoursePress::TD );


		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'render_page' ) );
		add_filter( 'coursepress_admin_valid_pages', array( __CLASS__, 'add_valid' ) );
		add_filter( 'coursepress_admin_pages', array( __CLASS__, 'add_page' ) );
		add_action( 'coursepress_settings_page_pre_render_' . self::$slug, array( __CLASS__, 'process_form' ) );

		// Update Discussion
		add_action( 'wp_ajax_update_discussion', array( __CLASS__, 'update_discussion' ) );
	}

	public static function add_page( $pages ) {
		$pages[ self::$slug ] = array(
			'title'      => self::$title,
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
		//if( isset( $_GET['action'] ) && isset( $_GET['id'] ) && 'edit' === $_GET['action'] && 'new' === $_GET['id'] ) {
		//}


		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'edit_discussion' ) ) {

			// Update the discussion
			$id = isset( $_REQUEST['id'] ) ? (int) $_REQUEST['id'] : false;

			$content = CoursePress_Helper_Utility::filter_content( $_POST['post_content'] );
			$title = CoursePress_Helper_Utility::filter_content( $_POST['post_title'] );
			$course_id = 'all' === $_POST['meta_course_id'] ? $_POST['meta_course_id'] : (int) $_POST['meta_course_id'];
			$unit_id = 'course' === $_POST['meta_unit_id'] ? $_POST['meta_unit_id'] : (int) $_POST['meta_unit_id'];
			$post_status = isset( $_POST['post_status'] ) ? $_POST['post_status'] : 'draft';

			$args = array(
				'post_title' => $title,
				'post_content' => $content,
				'post_type' => CoursePress_Model_Discussion::get_post_type_name(),
				'post_status' => $post_status
			);

			if( ! empty( $id ) ) {
				$args['ID'] = $id;
			}

			$id = wp_insert_post( $args );

			update_post_meta( $id, 'course_id', $course_id );
			update_post_meta( $id, 'unit_id', $unit_id );

			$url        = admin_url( 'admin.php?page=' . self::$slug );
			wp_redirect( esc_url_raw( $url ) );
			exit;

		}

	}

	public static function render_page() {

		$allowed_actions = array( 'edit' );

		$action = isset( $_GET['action'] ) && in_array( $_GET['action'], $allowed_actions ) ? sanitize_text_field( $_GET['action'] ) : '';

		$discussionListTable = new CoursePress_Helper_Table_DiscussionList();
		$discussionListTable->prepare_items();

		$url        = admin_url( 'admin.php?page=' . self::$slug . '&action=edit&id=new' );

		$content = '<div class="coursepress_communications_wrapper discussions wrap">' .
		           '<h3>' . esc_html( CoursePress::$name ) . ' : ' . esc_html( self::$menu_title ) . '
		            <a class="add-new-h2" href="' . esc_url_raw( $url ) . '">' . esc_html__( 'New Discussion', CoursePress::TD ) . '</a>
		            </h3>
		            <hr />';

		if( empty( $action ) ) {

			$bulk_nonce = wp_create_nonce( 'bulk_action_nonce' );
			$content .= '<div class="nonce-holder" data-nonce="' . $bulk_nonce . '"></div>';
			ob_start();
			$discussionListTable->display();
			$content .= ob_get_clean();

		} else {

			switch( $action ) {
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

		if( empty( $the_id ) ) {
			return '';
		}

		if( 'new' !== $the_id ) {
			$post = get_post( $the_id );
			$attributes = CoursePress_Model_Discussion::attributes( $the_id );
			$course_id = $attributes['course_id'];
			$unit_id = $attributes['unit_id'];
			$post_status = $post->post_status;
			$post_title = $post->post_title;
			$post_content = $post->post_content;
		} else {
			$course_id = 'all';
			$unit_id = 'course';
			$post_status = 'publish';
			$post_title = '';
			$post_content = '';
		}

		$options = array();
		$options['value'] = $course_id;
		$options['class'] = 'medium';
		//$options['first_option'] = array(
		//	'text' => __( 'All courses', CoursePress::TD ),
		//	'value' => 'all'
		//);

		$options_unit = array();
		$options_unit['value'] = $unit_id;
		$options_unit['class'] = 'medium';
		$options_unit['first_option'] = array(
			'text' => __( 'All units', CoursePress::TD ),
			'value' => 'course'
		);

		$content = '';

		$content .= '<form method="POST" class="edit">';

		$content .= '
			<input type="hidden" name="post_status" value="' . esc_attr( $post_status ) . '" />
			' . wp_nonce_field( 'edit_discussion', '_wpnonce', true, false ) . '
			<label><strong>' . esc_html__('Discussion Title', CoursePress::TD ). '</strong><br />
			<input type="text" class="wide" name="post_title" value="' . esc_attr( $post_title ) . '" /></label>

			<label><strong>' . esc_html__('Discussion Content', CoursePress::TD ). '</strong><br />';

		$editor_name = 'post_content';
		$editor_id = 'postContent';
		$args = array(
			"textarea_name" => $editor_name,
			"editor_class"  => 'cp-editor',
			"textarea_rows" => 10,
		);

		// Filter $args
		$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

		ob_start();
		wp_editor( $post_content, $editor_id, $args );
		$content .= ob_get_clean();
		$content .= '</label>';


		$content .= '
			<label><strong>' . esc_html__( 'Related Course', CoursePress::TD ) . '</strong><br />
			' . CoursePress_Helper_UI::get_course_dropdown( 'course_id', 'meta_course_id', false, $options ) . '
			</label>
			<label><strong>' . esc_html__( 'Related Unit', CoursePress::TD ) . '</strong><br />
			' . CoursePress_Helper_UI::get_unit_dropdown( 'unit_id', 'meta_unit_id', $course_id, false, $options_unit ) . '
			</label>
			<label class="right">
				<input type="submit" class="button button-primary" value="' . esc_attr__( 'Save Discussion', CoursePress::TD ) . '" />
			</label>
		';

		$content .= '</form>';

		return $content;
	}

	public static function update_discussion() {

		$data      = json_decode( file_get_contents( 'php://input' ) );
		$json_data = array();
		$success   = false;

		$action = isset( $data->action ) ? $data->action : '';
		$json_data['action'] = $action;

		if ( empty( $data->action ) ) {
			$json_data['message'] = __( 'Discussion Update: No action.', CoursePress::TD );
			wp_send_json_error( $json_data );
		}

		switch ( $action ) {

			case 'delete':

				if ( wp_verify_nonce( $data->data->nonce, 'delete-discussion' ) ) {

					$discussion_id = $data->data->discussion_id;

					wp_delete_post( $discussion_id );

					$json_data['discussion_id'] = $discussion_id;
					$json_data['nonce']     = wp_create_nonce( 'delete-discussion' );
					$success                = true;
				}

				break;

			case 'toggle':

				$discussion_id = $data->data->discussion_id;

				if ( wp_verify_nonce( $data->data->nonce, 'publish-discussion' ) ) {

					wp_update_post( array(
						'ID'          => $discussion_id,
						'post_status' => $data->data->status,
					) );

					$json_data['nonce'] = wp_create_nonce( 'publish-discussion' );
					$success            = true;

				}

				$json_data['discussion_id'] = $discussion_id;
				$json_data['state']     = $data->data->state;

				break;

			case 'bulk_unpublish':
			case 'bulk_publish':
			case 'bulk_delete':

				$ids = $data->data->ids;

				if ( wp_verify_nonce( $data->data->nonce, 'bulk_action_nonce' ) ) {

					foreach( $ids as $id ) {

						if( 'bulk_unpublish' === $action ) {
							wp_update_post( array(
								'ID'          => $id,
								'post_status' => 'draft',
							) );
						}

						if( 'bulk_publish' === $action ) {
							wp_update_post( array(
								'ID'          => $id,
								'post_status' => 'publish',
							) );
						}

						if( 'bulk_delete' === $action ) {
							wp_delete_post( $id );
						}

					}

					$success = true;

				}

				$json_data['ids'] = $ids;

				break;

			case 'unit_items':

				$course_id = $discussion_id = $data->data->course_id;
				$json_data['items'] = array();

				$units = get_posts( array(
					'post_type' => CoursePress_Model_Unit::get_post_type_name(),
					'post_parent' => $course_id
				) );

				// Sort units
				if( 'all' !== $course_id && ! empty( $units ) ) {
					foreach ( $units as $unit ) {
						$unit->unit_order = (int) get_post_meta( $unit->ID, 'unit_order', true );
					}
					$units = CoursePress_Helper_Utility::sort_on_object_key( $units, 'unit_order' );

					foreach ( $units as $unit ) {
						$json_data['items'][] = array( 'key' => $unit->ID, 'value' => $unit->post_title );
					}

					$success = true;
				}

				break;

		}

		if ( $success ) {
			wp_send_json_success( $json_data );
		} else {
			wp_send_json_error( $json_data );
		}

	}

}