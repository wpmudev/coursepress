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

	/**
	 * Class init
	 */
	public static function init() {
		self::$post_type = CoursePress_Data_Notification::get_post_type_name();
		self::set_labels();
	}

	/**
	 * Edit screen init
	 */
	public static function init_edit() {
		if ( ! CoursePress_Data_Capabilities::can_add_notifications() ) {
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
			__( 'Save', 'CP_TD' ),
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
				__( 'Notify Students', 'CP_TD' ),
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
			'title' => __( 'CoursePress Notifications', 'CP_TD' ),
			'menu_title' => self::get_label_by_name( 'name' ),
		);
	}

	public function process_form() {
		self::init();
		self::save_notification();
		self::update_notification();
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
		/**
		 * build
		 */
		if ( 'edit' == $action ) {
			$this->slug = 'coursepress_edit-notification';
			// Set before the page
			add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );
		} else {
			if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'bulk-posts' ) ) {
				l( $_REQUEST );
				if ( isset( $_POST['post'] ) && is_array( $_POST['post'] ) ) {
					foreach ( $_POST['post'] as $post_id ) {
						if ( CoursePress_Data_Notification::is_correct_post_type( $post_id ) ) {
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
			$this->slug = 'coursepress_notifications-table';
			// Prepare items
			$this->list_notification = new CoursePress_Admin_Table_Notifications();
			$this->list_notification->prepare_items();
			add_screen_option( 'per_page', array( 'default' => 20, 'option' => 'coursepress_notifications_per_page' ) );
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
			self::$error_message = __( 'No notification title!', 'CP_TD' );
			return;
		} elseif ( empty( $_POST['post_content'] ) ) {
			self::$error_message = __( 'No notification content!', 'CP_TD' );
			return;
		} elseif ( ! empty( $id ) && ! CoursePress_Data_Capabilities::can_update_notification( $id ) ) {
			self::$error_message = __( 'You do not have permission to edit this notification.', 'CP_TD' );
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
		if ( empty( $_REQUEST['action'] ) ) {
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
				/**
				 * delete
				 */
			case 'delete' && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'coursepress_delete_notification' ) :

				break;
			case 'publish': case 'unpublish': case 'delete':
						if ( ! empty( $_REQUEST['post'] ) ) {
							$notification_ids = $_REQUEST['post'];

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
				/**
				 * trash
				 */
			case 'trash' && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'coursepress_trash_notification' ) :
				$id = (int) $_REQUEST['id'];
				$is_correct_post_type = CoursePress_Data_Notification::is_correct_post_type( $id );
				if ( $is_correct_post_type ) {
					wp_trash_post( $id );
				}
				break;
				/**
				 * untrash
				 */
			case 'untrash' && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'coursepress_untrash_notification' ) :
				$id = (int) $_REQUEST['id'];
				$is_correct_post_type = CoursePress_Data_Notification::is_correct_post_type( $id );
				if ( $is_correct_post_type ) {
					wp_untrash_post( $id );
				}
				break;
		}
	}

	/**
	 * Content of related courses
	 *
	 * @since 2.0.0
	 *
	 * @return string Content of related courses.
	 */
	public static function get_release_courses( $post ) {
		echo '<div class="misc-pub-section misc-pub-post-course">';
		printf( '<label for="course_id">%s</label>', __( 'Course:', 'CP_TD' ) );

		$the_id = ! empty( $post->ID ) ? $post->ID : 'new';

		if ( empty( $the_id ) ) {
			return '';
		}
		$course_id = 'all';
		if ( 'new' !== $the_id ) {
			if ( ! CoursePress_Data_Capabilities::can_update_notification( $the_id ) ) {
				return __( 'You do not have permission to edit this notification.', 'CP_TD' );
			}
			$post = get_post( $the_id );
			$attributes = CoursePress_Data_Notification::attributes( $the_id );
			$course_id = $attributes['course_id'];
		}
		$options = array();
		$options['value'] = $course_id;
		if ( CoursePress_Data_Capabilities::can_add_notification_to_all() ) {
			$options['first_option'] = array(
				'text' => __( 'All courses', 'CP_TD' ),
				'value' => 'all',
			);
		} else {
			$options['courses'] = self::get_courses();
			if ( empty( $options['courses'] ) ) {
				return __( 'You do not have permission to add notification.', 'CP_TD' );
			}
		}
		echo CoursePress_Helper_UI::get_course_dropdown( 'course_id', 'meta_course_id', false, $options );
		echo '</div>';

		/**
		 * an option next to 'Course' dropdown to allow admin/instructors to
		 * select which group of students can see/receive the notification
		 */

		echo '<div class="misc-pub-section misc-pub-visibility" id="visibility">';
		_e( 'Receivers:', 'CP_TD' );
		$visibility_trans = __( 'Unknown', 'CP_TD' );

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
			printf( '<span id="post-visibility-display">%s</span>', __( 'no option available', 'CP_TD' ) );
			echo '<div class="placeholder">';
			echo '<p class="description">';
			_e( 'Please choose a course first and save the notification.', 'CP_TD' );
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
		add_action( 'coursepress_submitbox_misc_actions', array( __CLASS__, 'get_release_courses' ), 11 );
		self::submitbox( $post, 'can_change_status_notification' );
	}

	public static function get_allowed_options( $course_id ) {
		$allowed_options = array(
			'enrolled' => array(
				'label' => __( 'Enrolled students of this course.', 'CP_TD' ),
				'info' => __( 'Enrolled', 'CP_TD' ),
			),
			'passed' => array(
				'label' => __( 'All students who pass this course.', 'CP_TD' ),
				'info' => __( 'Passed', 'CP_TD' ),
			),
			'failed' => array(
				'label' => __( 'All students who failed this course.', 'CP_TD' ),
				'info' => __( 'Failed', 'CP_TD' ),
			),
		);
		if ( is_numeric( $course_id ) ) {
			$units = CoursePress_Data_Course::get_units( $course_id );
			foreach ( $units as $unit ) {
				$label = apply_filters( 'the_title', $unit->post_title, $unit->ID );
				$allowed_options[ 'unit-'.$unit->ID ] = array(
					'label' => sprintf( __( 'All students who completed "%s".', 'CP_TD' ), $label ),
					'info' => sprintf( __( 'Unit "%s"', 'CP_TD' ), $label ),
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
}
