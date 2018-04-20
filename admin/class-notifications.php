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
			__( 'Save', 'coursepress' ),
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
				__( 'Notify Students', 'coursepress' ),
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
			'title' => __( 'CoursePress Notifications', 'coursepress' ),
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
			$this->slug = 'coursepress_edit-notification';
			// Set before the page
			add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );
		} else {
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
			self::$error_message = __( 'No notification title!', 'coursepress' );
			return;
		} elseif ( empty( $_POST['post_content'] ) ) {
			self::$error_message = __( 'No notification content!', 'coursepress' );
			return;
		} elseif ( ! empty( $id ) && ! CoursePress_Data_Capabilities::can_update_notification( $id ) ) {
			self::$error_message = __( 'You do not have permission to edit this notification.', 'coursepress' );
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
		/**
		 * check action
		 */
		if ( ! isset( $_REQUEST['action'] ) || empty( $_REQUEST['action'] ) ) {
			return;
		}
		$action = strtolower( trim( $_REQUEST['action'] ) );
		/**
		 * check id or ids
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
			if ( ! CoursePress_Data_Notification::is_correct_post_type( $id ) ) {
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
				foreach ( $posts as $post_id ) {
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
		} else if ( ! empty( $id ) ) {
			switch ( $action ) {
				case 'filter':
				break;
				/**
				 * delete
				 */
				case 'delete' && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'coursepress_delete_notification' ) :
					if ( CoursePress_Data_Capabilities::can_delete_notification( $id ) ) {
						wp_delete_post( $id );
					}
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
									}
								}
							}
				break;

				case 'delete2':
					$id = (int) $_REQUEST['id'];

					if ( CoursePress_Data_Capabilities::can_delete_notification( $id ) ) {
						wp_delete_post( $id );
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
						}
					}
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
		printf( '<label for="course_id">%s</label>', __( 'Course:', 'coursepress' ) );

		$the_id = ! empty( $post->ID ) ? $post->ID : 'new';

		if ( empty( $the_id ) ) {
			return '';
		}
		$course_id = 'all';
		if ( 'new' !== $the_id ) {
			if ( ! CoursePress_Data_Capabilities::can_update_notification( $the_id ) ) {
				return __( 'You do not have permission to edit this notification.', 'coursepress' );
			}
			$post = get_post( $the_id );
			$attributes = CoursePress_Data_Notification::attributes( $the_id );
			$course_id = $attributes['course_id'];
		}
		$options = array();
		$options['value'] = $course_id;
		if ( CoursePress_Data_Capabilities::can_add_notification_to_all() ) {
			$options['first_option'] = array(
				'text' => __( 'All courses', 'coursepress' ),
				'value' => 'all',
			);
		} else {
			$options['courses'] = self::get_courses();
			if ( empty( $options['courses'] ) ) {
				return __( 'You do not have permission to add notification.', 'coursepress' );
			}
		}
		echo CoursePress_Helper_UI::get_course_dropdown( 'course_id', 'meta_course_id', false, $options );
		echo '</div>';

		/**
		 * an option next to 'Course' dropdown to allow admin/instructors to
		 * select which group of students can see/receive the notification
		 */

		echo '<div class="misc-pub-section misc-pub-visibility" id="visibility">';
		_e( 'Receivers:', 'coursepress' );
		$visibility_trans = __( 'Unknown', 'coursepress' );

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
			printf( '<span id="post-visibility-display">%s</span>', __( 'no option available', 'coursepress' ) );
			echo '<div class="placeholder">';
			echo '<p class="description">';
			_e( 'Please choose a course first and save the notification.', 'coursepress' );
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
				'label' => __( 'Enrolled students of this course.', 'coursepress' ),
				'info' => __( 'Enrolled', 'coursepress' ),
			),
			'passed' => array(
				'label' => __( 'All students who pass this course.', 'coursepress' ),
				'info' => __( 'Passed', 'coursepress' ),
			),
			'failed' => array(
				'label' => __( 'All students who failed this course.', 'coursepress' ),
				'info' => __( 'Failed', 'coursepress' ),
			),
		);
		if ( is_numeric( $course_id ) ) {
			$units = CoursePress_Data_Course::get_units( $course_id );
			foreach ( $units as $unit ) {
				$label = apply_filters( 'the_title', $unit->post_title, $unit->ID );
				$allowed_options[ 'unit-'.$unit->ID ] = array(
					'label' => sprintf( __( 'All students who completed "%s".', 'coursepress' ), $label ),
					'info' => sprintf( __( 'Unit "%s"', 'coursepress' ), $label ),
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
