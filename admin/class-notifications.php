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

    public function init() {
		self::$post_type = CoursePress_Data_Notification::get_post_type_name();
		self::set_labels();
    }

	public static function init_edit() {
		if ( ! CoursePress_Data_Capabilities::can_add_notifications() ) {
			wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );
		}
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
		/**
		 * Notification box
		 * /
		 $add_box_notify_students = apply_filters( 'coursepress_notifications_send_notify_to_students', true );
		if ( $add_box_notify_students ) {
			add_meta_box(
				'notify-students',
				__( 'Notify Students', 'cp' ),
				array( $this, 'box_notify_students' ),
				$post_type,
				'side'
			);
		}
		 */
	}

	public function get_labels() {
		self::init();
		return array(
			'title' => __( 'CoursePress Notifications', 'cp' ),
			'menu_title' => self::get_label_by_name('name'),
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

		if ( ! isset( $_POST['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'edit_notification' ) ) {
			return;
		}

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
		} elseif ( ! empty( $id ) && ! CoursePress_Data_Capabilities::can_update_notification( $id ) ) {
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

		CoursePress_Helper_Utility::add_meta_unique( $id, 'course_id', $course_id );

		/**
		 * Visibility
		 */
		if ( 'all' == $course_id ) {
			delete_post_meta( $id, 'receivers' );
		} else {
			$receivers = isset( $_POST['visibility'] )? $_POST['visibility']:'enrolled';
			$allowed_options = self::get_allowed_options( $course_id );
			if ( ! isset( $allowed_options[ $receivers ] ) ) {
				$receivers = 'enrolled';
			}
			CoursePress_Helper_Utility::add_meta_unique( $id, 'receivers', $receivers );
		}

		$url = add_query_arg( 'id', $id );
		wp_redirect( esc_url_raw( $url ) );
		exit;
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
					$url = 0 == $course_id ? remove_query_arg( 'course_id' ) : add_query_arg( 'course_id', $course_id );
					wp_safe_redirect( $url );
				}
				break;
			case 'publish': case 'unpublish': case 'delete':
						if ( ! empty( $_REQUEST['bulk-actions'] ) ) {
							$notification_ids = $_REQUEST['bulk-actions'];

							foreach ( $notification_ids as $id ) {
								if ( 'delete' != $action ) {
									if ( CoursePress_Data_Capabilities::can_change_status_notification( $id ) ) {
										$post_status = 'unpublish' == $action ? 'draft' : $action;
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
					if ( CoursePress_Data_Capabilities::can_change_status_notification( $notification_id ) ) {
						wp_update_post( array(
							'ID' => $notification_id,
							'post_status' => $data->data->status,
						) );
						$success = true;
					}
					$json_data['nonce'] = wp_create_nonce( 'publish-notification' );
				}

				$json_data['notification_id'] = $notification_id;
				$json_data['state'] = $data->data->state;

				break;
		}
	}

	private static function get_statuses( $post ) {
		$allowed_statuses = array(
			'draft'         => __( 'Draft', 'cp' ),
			'publish'       => __( 'Published', 'cp' ),
		);
		if ( isset( $post ) ) {
			if ( ! array_key_exists( $post->post_status, $allowed_statuses ) ) {
				$post->post_status = 'draft';
			}
		} else {
			if ( ! is_object( $post ) ) {
				$post = new stdClass();
				$post->ID = 0;
			}
			$post->post_status = 'draft';
		}
?>
<div class="misc-pub-section misc-pub-post-status">
<label for="post_status"><?php _e( 'Status:' ) ?></label>
<span id="post-status-display">
<?php
switch ( $post->post_status ) {
	case 'private':
		_e( 'Privately Published' );
		break;
	case 'publish':
		_e( 'Published' );
		break;
	case 'future':
		_e( 'Scheduled' );
		break;
	case 'pending':
		_e( 'Pending Review' );
		break;
	case 'draft':
	case 'auto-draft':
	default:
		_e( 'Draft' );
		break;
}

?>
</span>
<?php
if ( CoursePress_Data_Capabilities::can_change_status_notification( $post->ID ) ) {
?>
<a href="#post_status" <?php if ( 'private' == $post->post_status ) { ?>style="display:none;" <?php } ?>class="edit-post-status hide-if-no-js"><span aria-hidden="true"><?php _e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php _e( 'Edit status' ); ?></span></a>

<div id="post-status-select" class="hide-if-js">
<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( ('auto-draft' == $post->post_status ) ? 'draft' : $post->post_status ); ?>" />
<select name='post_status' id='post_status'>
<?php
foreach ( $allowed_statuses as $status => $label ) {
	printf(
		'<option %s value="%s">%s</option>',
		selected( $post->post_status, $status ),
		esc_attr( $status ),
		$label
	);
}
?>
</select>
 <a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e( 'OK' ); ?></a>
 <a href="#post_status" class="cancel-post-status hide-if-no-js button-cancel"><?php _e( 'Cancel' ); ?></a>
</div>
<?php } ?>
</div>
<?php
	}

	/**
	 * Content of related courses
	 *
	 * @since 2.0.0
	 *
	 * @return string Content of related courses.
	 */
	private static function get_release_courses( $post ) {
		echo '<div class="misc-pub-section misc-pub-post-course">';
		printf( '<label for="course_id">%s</label>', __( 'Course:', 'cp' ) );

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
		echo '</div>';

		/**
		 * an option next to 'Course' dropdown to allow admin/instructors to
		 * select which group of students can see/receive the notification
		 */

		echo '<div class="misc-pub-section misc-pub-visibility" id="visibility">';
		_e( 'Receivers:', 'cp' );
		$visibility_trans = __( 'Unknown', 'cp' );

		if ( is_numeric( $course_id ) ) {

			$receivers = get_post_meta( $post->ID, 'receivers', true );
			if ( empty( $receivers ) ) {
				$receivers = 'all';
			}

			$allowed_options = self::get_allowed_options( $course_id );

			if ( isset( $allowed_options[ $receivers ] ) ) {
				$visibility_trans = $allowed_options[ $receivers ]['info'];
			}
?>
	<span id="post-visibility-display"><?php echo esc_html( $visibility_trans ); ?></span>
<a href="#visibility" class="edit-visibility hide-if-no-js"><span aria-hidden="true"><?php _e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php _e( 'Edit visibility' ); ?></span></a>

<div id="post-visibility-select" class="hide-if-js">
<input type="hidden" name="hidden_post_visibility" id="hidden-post-visibility" value="<?php echo esc_attr( $receivers ); ?>" />
<?php
foreach ( $allowed_options as $key => $data ) {
	printf(
		'<input type="radio" name="visibility" id="visibility-radio-%s" value="%s" %s data-info="%s"/> <label for="visibility-radio-%s">%s</label><br />',
		$key,
		$key,
		checked( $receivers, $key, false ),
		esc_attr( $data['info'] ),
		$key,
		$data['label']
	);
}
?>
<p>
 <a href="#visibility" class="save-post-visibility hide-if-no-js button"><?php _e( 'OK' ); ?></a>
 <a href="#visibility" class="cancel-post-visibility hide-if-no-js button-cancel"><?php _e( 'Cancel' ); ?></a>
</p>
<?php
		} else {
			printf( '<span id="post-visibility-display">%s</span>', __( 'no option available', 'cp' ) );
			echo '<div class="placeholder">';
			echo '<p class="description">';
			_e( 'Please choose a course first and save the notification.', 'cp' );
			echo '</p>';
			echo '</div>';
		}
		echo '</div>';
		echo '</div>';
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
	 * Content of box submitbox
	 *
	 * @since 2.0.0
	 *
	 * @return string Content of submitbox.
	 */
	public static function box_submitdiv( $post ) {
		if ( ! is_object( $post ) || ! isset( $post->post_status ) || empty( $post->post_status ) || 'draft' == $post->post_status ) {
			$post_id = is_object( $post )? $post->ID : 0;
			if ( CoursePress_Data_Capabilities::can_change_status_notification( $post_id ) ) {
?>
<div id="minor-publishing-actions">
<div id="save-action">
<input type="submit" name="save" id="save-post" value="<?php esc_attr_e( 'Save Draft', 'cp' ); ?>" class="button">
<span class="spinner"></span>
</div>
<div class="clear"></div>
</div>
<?php
			}
		}
		/**
		 * misc actions
		 */
		printf( '<div id="misc-publishing-actions" data-no-options="%s">', esc_attr__( 'no option available', 'cp' ) );
		self::get_statuses( $post );
		self::get_release_courses( $post );
		echo '</div>';
		/**
		 * major actions
		 */
		echo '<div id="major-publishing-actions"><div id="publishing-action"><span class="spinner"></span>';
		$label = __( 'Publish', 'cp' );
		if ( is_object( $post ) && 'publish' == $post->post_status ) {
			$label = __( 'Update', 'cp' );
		}
		printf(
			'<input type="submit" class="button button-primary" value="%s" />',
			esc_attr( $label )
		);
		echo '</div>';
		echo '<div class="clear"></div>';
		echo '</div>';
	}

	public static function get_allowed_options( $course_id ) {
		$allowed_options = array(
			'enrolled' => array(
				'label' => __( 'Enrolled students of this course.', 'cp' ),
				'info' => __( 'Enrolled', 'cp' ),
			),
			'passed' => array(
				'label' => __( 'All students who pass this course.', 'cp' ),
				'info' => __( 'Passed', 'cp' ),
			),
			'failed' => array(
				'label' => __( 'All students who failed this course.', 'cp' ),
				'info' => __( 'Failed', 'cp' ),
			),
		);
		if ( is_numeric( $course_id ) ) {
			$units = CoursePress_Data_Course::get_units( $course_id );
			foreach ( $units as $unit ) {
				$label = apply_filters( 'the_title', $unit->post_title, $unit->ID );
				$allowed_options[ 'unit-'.$unit->ID ] = array(
					'label' => sprintf( __( 'All students who completed "%s".', 'cp' ), $label ),
					'info' => sprintf( __( 'Unit "%s"', 'cp' ), $label ),
				);
			}
		}
		return $allowed_options;
	}

	/**
	 * Add button "Add new Notification".
	 *
	 * @since 2.0.0
	 */
	public static function add_button_add_new() {
		if ( !CoursePress_Data_Capabilities::can_add_notifications() ) {
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
		if ( isset( self::$labels[self::$post_type]->$label ) ) {
			return self::$labels[self::$post_type]->$label;
		}
		return '';
	}

}
