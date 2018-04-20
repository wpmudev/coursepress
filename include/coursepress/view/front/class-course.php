<?php
/**
 * Front-End UI.
 *
 * @package CoursePress
 */

/**
 * Renders course-related pages.
 */
class CoursePress_View_Front_Course {

	/**
	 * Used for hooking discussion filters.
	 *
	 * @var bool
	 */
	public static $discussion = false;

	/**
	 * The page title
	 *
	 * @var string
	 */
	public static $title = '';

	/**
	 * Custom template file to load when the coursepress theme is used.
	 *
	 * @var string
	 */
	public static $template = false;

	/**
	 * Initialize the module, hook up our functions.
	 *
	 * @since  1.0.0
	 */
	public static function init() {
		if ( is_admin() ) {
			self::init_admin();
			return;
		}

		add_action(
			'pre_get_posts',
			array( __CLASS__, 'remove_canonical' )
		);

		add_action(
			'parse_request',
			array( __CLASS__, 'download_certificate' )
		);

		add_action(
			'parse_request',
			array( __CLASS__, 'parse_request' )
		);

		add_filter(
			'template_include',
			array( __CLASS__, 'template_include' )
		);

		add_action(
			'wp_enqueue_scripts',
			array( __CLASS__, 'coursepress_front_css' )
		);

		add_filter(
			'get_the_author_description',
			array( __CLASS__, 'remove_author_bio_description' ),
			10, 2
		);

		// Discussion filters.
		add_filter(
			'post_type_link',
			array( __CLASS__, 'permalink' ),
			10, 3
		);

		add_filter(
			'get_comments_number',
			array( __CLASS__, 'comment_number' ),
			10, 2
		);

		// This filter should be temporary, check if it can be removed...
		add_filter(
			'comments_array',
			array( __CLASS__, 'temp_update_discussion_comments' ),
			10, 2
		);

		add_filter(
			'widget_comments_args',
			array( __CLASS__, 'remove_discussions_from_comments' )
		);

		add_action(
			'comment_post',
			array( __CLASS__, 'add_discussion_comment_meta' )
		);

		add_action(
			'init',
			array( __CLASS__, 'maybe_save_discussion' )
		);

		// TODO: The filter is always removed... Does not look correct.
		//remove_filter( 'the_content', 'wpautop' );

		/**
		 * sort by course start date
		 */
		//add_action( 'pre_get_posts', array( __CLASS__, 'set_sort_by_start_date' ) );
		add_action( 'init', array( __CLASS__, 'handle_module_uploads' ) );

		CoursePress_View_Front_EnrollmentPopup::init();

		/**
		 * CoursePress discussion
		 */
		CoursePress_Helper_Discussion::init();

		/**
		 * admin_bar_menu
		 */
		add_action( 'admin_bar_menu', array( __CLASS__, 'add_edit_to_admin_bar_menu' ), 199 );
	}

	/**
	 * This init function is called instead of init when is_admin() is true.
	 * i.e. for ajax requests and on wp-admin side...
	 *
	 * @since  2.0.0
	 */
	public static function init_admin() {
		add_action(
			'wp_ajax_course_front',
			array( __CLASS__, 'process_course_ajax' )
		);

		CoursePress_View_Front_EnrollmentPopup::init_admin();

		/**
		 * CoursePress discussion
		 */
		CoursePress_Helper_Discussion::init();

	}

	/**
	 * Create or update discussion entry, if the user submitted the form.
	 * Called during `init` action.
	 *
	 * @since  2.0.0
	 */
	public static function maybe_save_discussion() {
		// Handle comments post.
		if ( ! empty( $_POST['comment_post_ID'] ) ) {
			$module = get_post( $_POST['comment_post_ID'] );
			$course_link = get_permalink(
				get_post_field( 'post_parent', $module->post_parent )
			);

			$return_url = $course_link .
				CoursePress_Core::get_slug( 'unit/' ) .
				get_post_field( 'post_name', $module->post_parent ) .
				'#module-' . $module->ID;
			$return_url = esc_url_raw( $return_url );
			// TODO: The $return_url is not used...?
		}

		// Add new discussion post.
		if ( ! is_user_logged_in() ) { return; }
		if ( ! isset( $_POST['_wpnonce'] ) ) { return; }
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'add-new-discussion' ) ) { return; }

		// Update the discussion.
		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : false;

		$content = CoursePress_Helper_Utility::filter_content(
			$_POST['discussion_content']
		);
		$title = CoursePress_Helper_Utility::filter_content(
			$_POST['discussion_title']
		);

		if ( 'all' == $_POST['course_id'] ) {
			$course_id = 'all';
		} else {
			$course_id = (int) $_POST['course_id'];
		}
		if ( 'course' == $_POST['unit_id'] ) {
			$unit_id = 'course';
		} else {
			$unit_id = (int) $_POST['unit_id'];
		}

		$args = array(
			'post_title' => $title,
			'post_content' => $content,
			'post_type' => CoursePress_Data_Discussion::get_post_type_name(),
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
			'comment_status' => 'open',
		);

		if ( ! empty( $id ) ) {
			$args['ID'] = $id;
		}

		$id = wp_insert_post( $args );

		/**
		 * Try to add course_id - it should be unique post meta.
		 */
		$success = add_post_meta( $id, 'course_id', $course_id, true );
		if ( ! $success ) {
			update_post_meta( $id, 'course_id', $course_id );
		}

		/**
		 * Try to add unit_id - it should be unique post meta.
		 */
		$success = add_post_meta( $id, 'unit_id', $unit_id, true );
		if ( ! $success ) {
			update_post_meta( $id, 'unit_id', $unit_id );
		}

		$url = CoursePress_Core::get_slug( 'course/', true ) .
			get_post_field( 'post_name', $course_id ) . '/' .
			CoursePress_Core::get_slug( 'discussions/' );

		wp_redirect( esc_url_raw( $url ) );
		exit;
	}

	/**
	 * Upload files from students (i.e. answer to a file-upload module).
	 * Called during `plugins_loaded` action.
	 *
	 * @since  2.0.0
	 */
	public static function handle_module_uploads() {

		if ( empty( $_REQUEST['course_action'] ) ) { return; }
		if ( 'upload-file' != $_REQUEST['course_action'] ) { return; }

		$json_data = array();
		$error = false;
		$ajax = false;
		$process_file = true;

		$course_id = false;
		$unit_id = false;
		$module_id = false;
		$student_id = get_current_user_id();
		$response_count = 0;
		$can_retry = false;
		$is_enabled = false;
		$response = false;

		if ( isset( $_REQUEST['course_id'] ) ) {
			$course_id = (int) $_REQUEST['course_id'];
		}
		if ( isset( $_REQUEST['unit_id'] ) ) {
			$unit_id = (int) $_REQUEST['unit_id'];
		}
		if ( isset( $_REQUEST['module_id'] ) ) {
			$module_id = (int) $_REQUEST['module_id'];
		}
		if ( isset( $_REQUEST['student_id'] ) ) {
			$student_id = (int) $_REQUEST['student_id'];
		}

		if ( ! $course_id && ! $unit_id && ! $module_id ) {
			// We have invalid/missing Form data...
			$json_data['response'] = __( 'Invalid data submitted', 'coursepress' );
			$json_data['success'] = false;
			$process_file = false;
		} else {
			// Form data complete, check course-settings.
			$student_progress = array();
			if ( $student_id ) {
				$student_progress = CoursePress_Data_Student::get_completion_data(
					$student_id,
					$course_id
				);
			}

			// Check if we should continue with this upload (in case students cheat with the code)
			$attributes = CoursePress_Data_Module::attributes( $module_id );
			$can_retry = ! $attributes['allow_retries'];

			if ( $can_retry ) {
				$responses = CoursePress_Helper_Utility::get_array_val(
					$student_progress,
					'units/' . $unit_id . '/responses/' . $module_id
				);
				if ( $responses && is_array( $responses ) ) {
					$response_count = count( $responses );
				}
				if ( ! $attributes['retry_attempts'] ) {
					// Unlimited attempts.
					$is_enabled = true;
				} elseif ( (int) $attributes['retry_attempts'] >= $response_count ) {
					// Retry limit not yet reached.
					$is_enabled = true;
				}

				if ( ! $is_enabled ) {
					$json_data['response'] = __( 'Maximum allowed retries exceeded.', 'coursepress' );
					$json_data['success'] = false;
					$process_file = false;
				}
			}
		}

		if ( $process_file ) {
			if ( isset( $_REQUEST['src'] ) && 'ajax' === $_REQUEST['src'] ) {
				$ajax = true;
			}

			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			$upload_overrides = array(
				'test_form' => false,
				'mimes' => CoursePress_Helper_Utility::allowed_student_mimes(),
			);

			if ( isset( $_FILES ) ) {
				foreach ( $_FILES as $file ) {

					try {
						if ( ! function_exists( 'get_userdata' ) ) {
							require_once ABSPATH . 'wp-includes/pluggable.php';
						}

						$response = wp_handle_upload( $file, $upload_overrides );
						$response['size'] = $file['size'];

						if ( isset( $response['error'] ) ) {
							$json_data['response'] = $response['error'];
							$json_data['success'] = false;
						} else {
							$json_data['response'] = $response;
							$json_data['success'] = true;

							// Record the response
							if ( $student_id ) {
								CoursePress_Data_Student::module_response(
									$student_id,
									$course_id,
									$unit_id,
									$module_id,
									$response,
									$student_progress
								);
							}
						}
					} catch ( Exception $e ) {
						$json_data['response'] = $e->getMessage();
						$json_data['success'] = false;
					}
				}
			} else {
				$json_data['response'] = __( 'No files uploaded.', 'coursepress' );
				$json_data['success'] = false;
			}
		}

		if ( $ajax ) {
			$attr = array(
				'course' => $course_id,
				'unit' => $unit_id,
				'item_id' => $module_id,
				'type' => 'module',
				'echo' => false,
			);
			$json_data['html'] = CoursePress_Data_Shortcode_Template::coursepress_focus_item( $attr );

			$return = ! empty( $_REQUEST['in_admin'] );
			if ( false === $return ) {
				echo json_encode( $json_data );
				exit;
			} else {
				return $json_data;
			}
		}
	}

	/**
	 * Render the course overview.
	 *
	 * @since  2.0.0
	 * @see    self::parse_request()
	 * @return string The HTML code.
	 */
	public static function render_course_main( $course_id = 0 ) {
		$content = '';
		$theme_file = locate_template( array( 'single-course.php' ) );
		if ( $theme_file ) {
			self::$template = $theme_file;
			$course_post = get_post( $course_id );
			if ( $course_post && CoursePress_Admin_Courses::_is_course( $course_post ) ) {
				$content = apply_filters( 'the_content', $course_post->post_content );
			}
		} else {
			$content = CoursePress_Template_Course::course();
		}
		return $content;
	}

	/**
	 * Render a single unit of a course.
	 *
	 * @since  2.0.0
	 * @see    self::parse_request()
	 * @param  int $post_id The post that is displayed.
	 * @return string The HTML code.
	 */
	public static function render_course_unit( $post_id ) {
		$theme_file = locate_template( array( 'single-unit.php' ) );

		if ( $theme_file ) {
			self::$template = $theme_file;
			$content = '';
		} else {
			$content = CoursePress_Template_Unit::unit_with_modules();
		}

		return $content;
	}

	/**
	 * Render the unit-archive list of a course.
	 *
	 * @since  2.0.0
	 * @see    self::parse_request()
	 * @return string The HTML code.
	 */
	public static function render_course_unit_archive() {
		$theme_file = locate_template( array( 'archive-unit.php' ) );

		if ( $theme_file ) {
			self::$template = $theme_file;
			$content = '';
		} else {
			$content = CoursePress_Template_Unit::unit_archive();
		}

		return $content;
	}

	/**
	 * Render the course-archive list.
	 *
	 * @since  2.0.0
	 * @see    self::parse_request()
	 * @return string The HTML code.
	 */
	public static function render_course_archive() {
		$category = CoursePress_Helper_Utility::the_course_category();

		$theme_file = locate_template(
			array(
				'archive-course-' . $category . '.php',
				'archive-course.php',
			)
		);

		if ( $theme_file ) {
			self::$template = $theme_file;
			$content = '';
		} else {
			$content = CoursePress_Template_Course::course_archive();
		}

		return $content;
	}

	/**
	 * Render a single discussion page of a course.
	 *
	 * @since  2.0.0
	 * @see    self::parse_request()
	 * @return string The HTML code.
	 */
	public static function render_course_discussion() {
		global $post;

		if ( ! is_object( $post ) ) {
			// Get the current
			$post = get_post();
		}

		if ( is_object( $post ) ) {
			$post->comment_status = 'closed';
		}

		$theme_file = locate_template( array( 'single-course-discussion.php' ) );

		if ( $theme_file ) {
			self::$template = $theme_file;
			$content = '';
		} else {
			$content = CoursePress_Template_Communication::render_discussion();
		}

		return $content;
	}

	/**
	 * Render the "new discussion entry" page of a course.
	 *
	 * @since  2.0.0
	 * @see    self::parse_request()
	 * @return string The HTML code.
	 */
	public static function render_new_course_discussion() {
		$theme_file = locate_template( array( 'page-add-new-discussion.php' ) );

		if ( $theme_file ) {
			self::$template = $theme_file;
			$content = '';
		} else {
			$content = CoursePress_Template_Communication::render_new_discussion();
		}

		return $content;
	}

	/**
	 * Render a discussion-list page of a course.
	 *
	 * @since  2.0.0
	 * @see    self::parse_request()
	 * @return string The HTML code.
	 */
	public static function render_course_discussion_archive() {
		$theme_file = locate_template( array( 'archive-course-discussions.php' ) );

		if ( $theme_file ) {
			self::$template = $theme_file;
			$content = '';
		} else {
			$content = CoursePress_Template_Communication::render_discussions();
		}

		return $content;
	}

	/**
	 * Render a grades/assessment results of the current student.
	 *
	 * @since  2.0.0
	 * @see    self::parse_request()
	 * @return string The HTML code.
	 */
	public static function render_course_grades_archive() {
		$theme_file = locate_template( array( 'archive-unit-grades.php' ) );

		if ( $theme_file ) {
			self::$template = $theme_file;
			$content = '';
		} else {
			$content = CoursePress_Template_Grades::render();
		}

		return $content;
	}

	/**
	 * Render a student workbook results of a course.
	 *
	 * @since  2.0.0
	 * @see    self::parse_request()
	 * @return string The HTML code.
	 */
	public static function render_course_workbook() {
		$theme_file = locate_template( array( 'archive-unit-workbook.php' ) );

		if ( $theme_file ) {
			self::$template = $theme_file;
			$content = '';
		} else {
			$content = CoursePress_Template_Workbook::render_workbook();
		}

		return $content;
	}

	/**
	 * Render the page with course notificatons.
	 *
	 * @since  2.0.0
	 * @see    self::parse_request()
	 * @return string The HTML code.
	 */
	public static function render_course_notifications_archive() {
		$theme_file = locate_template( array( 'archive-course-notifications.php' ) );

		if ( $theme_file ) {
			self::$template = $theme_file;
			$content = '';
		} else {
			$content = CoursePress_Template_Communication::render_notifications();
		}

		return $content;
	}

	/**
	 * Prevent WordPress from doing a rel = canonical redirect.
	 *
	 * Canonical redirect is an SEO action that prevents duplicate content
	 * page penalties. However, many CoursePress theme pages have virtual
	 * slugs for the URL. So the canonical redirect would send the user to the
	 * wrong page. It's safe to disable this for those custom post types.
	 *
	 * @since  2.0.0
	 * @param  WP_Query $wp_query The global WP_Query object.
	 */
	public static function remove_canonical( $wp_query ) {
		if ( is_admin() || ! $wp_query ) {
			return;
		}
		/**
		 * Do it only on main query
		 */
		if ( ! $wp_query->is_main_query() ) {
			return;
		}
		$page = get_query_var( 'pagename' );
		$course = get_query_var( 'course' );
		$coursename = get_query_var( 'coursename' );
		// Is the user on a CoursePress page?
		if ( $course || $coursename || 'dashboard' == $page ) {
			remove_action(
				'template_redirect',
				'redirect_canonical'
			);
		}
	}

	/**
	 * Redirect the user to the course-overview page, if he tries to access
	 * some restricted page (unit, discussion, etc.)
	 *
	 * @since  2.0.0
	 * @param  int $course_id The course-ID.
	 */
	public static function no_access_redirect( $course_id ) {
		$course_url = CoursePress_Data_Course::get_permalink( $course_id );

		/**
		 * Fires before the page is redirected back to main course page.
		 *
		 * @since 2.0
		 *
		 * @param (string) $course_url 	The location of the course.
		 * @param (int) $course_id	Course ID
		 **/
		do_action( 'coursepress_no_access_redirect', $course_url, $course_id );

		/**
		 * Add query arg to handle message
		 *
		 * @since 2.0.0
		 *
		 */
		if ( is_user_logged_in() ) {
			$course_url = CoursePress_Helper_Message::add_message_query_arg( $course_url, 'only-enroled' );
		} else {
			$course_url = CoursePress_Helper_Message::add_message_query_arg( $course_url, 'no-access' );
		}

		/**
		 * Filter the redirect url before redirecting the user.
		 *
		 * @since 2.0
		 *
		 * @param (string) $course_url
		 * @param (int) $course_id
		 **/
		$course_url = apply_filters( 'coursepress_no_access_redirect_url', $course_url, $course_id );

		wp_redirect( esc_url_raw( $course_url ) );
		exit;
	}

	/**
	 * Redirect user to the main course-overview page.
	 *
	 * @since  2.0.0
	 */
	public static function archive_redirect() {
		$archive_url = CoursePress_Core::get_slug( 'courses/', true );
		wp_redirect( esc_url_raw( $archive_url ) );
		exit;
	}

	/**
	 * Overwrites the WP theme file that is used to render current page.
	 *
	 * @since  2.0.0
	 * @return string $template Default template filename.
	 * @return string Custom template filename.
	 */
	public static function template_include( $template ) {
		if ( self::$template ) {
			$template = self::$template;
		}

		return $template;
	}

	/**
	 * The heart of this class: This is the logic that decides, which
	 * CoursePress-template (theme) or VirtualPage to display.
	 *
	 * @since  2.0.0
	 * @param  WP $wp The main WP object.
	 */
	public static function parse_request( $wp ) {
		$cp = (object) array(
			'vp_args' => false,
			'title' => '',
			'cp_course' => '',
			'cp_category' => '',
			'discussion' => '',
			'is_focus' => false,
			'is_course' => false,
			'is_category' => false,
			'is_unit' => false,
			'is_modules' => false,
			'is_enrolled' => false,
			'is_instructor' => false,
			'is_unit_discussion' => false,
			'is_unit_discussion_list' => false,
			'is_unit_grades' => false,
			'is_unit_workbook' => false,
			'is_unit_notification' => false,
			'is_completion_page' => false,
			'can_preview' => false,
			'course_id' => 0,
			'student_id' => get_current_user_id(),
			'pagination' => 0,
		);

		CoursePress_Helper_Utility::$is_singular = false;
		CoursePress_Helper_Utility::set_the_course_subpage( '' );
		$is_other_cp_page = false;
		$is_focus = false;

		if ( ! empty( $wp->query_vars['coursename'] ) ) {
			$course_name = $wp->query_vars['coursename'];
			$cp->course_id = CoursePress_Data_Course::by_name( $cp->cp_course, true );
			$mode = get_post_meta( $cp->course_id, 'cp_course_view', true );
			$is_focus = 'focus' == $mode;
		}

		if ( array_key_exists( 'coursepress_focus', $wp->query_vars ) ) {
			$cp->is_focus = (1 == $wp->query_vars['coursepress_focus']);
		}

		// THIS IS WHERE WE WANT TO DO ACCESS CONTROL!
		// ------------------------------ Do something -------------------------

		// Check Focus Mode First.
		if ( $cp->is_focus ) {
			$cp->course_id = (int) $wp->query_vars['course'];
			$unit_id = (int) $wp->query_vars['unit'];
			$type = sanitize_text_field( $wp->query_vars['type'] );
			$item_id = $wp->query_vars['item'];
			$item_id = 'completion_page' != $item_id ? (int) $item_id : $item_id;

			/**
			 * fix from comment to module
			 */
			if ( 'comment' == $type ) {
				$type = 'module';
				$comment = get_comment( $item_id );
				$item_id = $comment->comment_post_ID;
			}

			// Focus mode means:
			// We display the course item, no other theme/page elements.
			$shortcode = sprintf(
				'[coursepress_focus_item course="%d" unit="%d" type="%s" item_id="%d"]',
				$cp->course_id,
				$unit_id,
				$type,
				$item_id
			);
			echo do_shortcode( $shortcode );
			die();
		}

		// -- If not in focus mode we will continue here -----------------------

		// Find out which template/VirtualPage we need.
		if ( isset( $wp->query_vars['course'] ) ) {
			$cp->is_course = true;
			$cp->cp_course = $wp->query_vars['course'];
		}

		if ( $cp->is_course && CoursePress_Core::get_slug( 'category' ) == $cp->cp_course ) {
			// Warning: A course should not have the same post_name as the
			// category slug, it will be skipped!
			$cp->is_course = false;
			$cp->is_category = true;
			$cp->cp_category = $cp->cp_course;
		} elseif ( isset( $wp->query_vars['course_category'] ) ) {
			$cp->is_category = true;
			$cp->cp_category = $wp->query_vars['course_category'];
		}
		if ( isset( $wp->query_vars['coursename'] ) ) {
			$cp->is_unit = true;
			$cp->cp_course = $wp->query_vars['coursename'];
		}

		if ( ! empty( $wp->query_vars['course_completion'] ) ) {
			$cp->is_unit = false;
			$cp->cp_course = $wp->query_vars['coursename'];
			$cp->is_completion_page = true;
		}

		if ( isset( $wp->query_vars['unitname'] ) ) {
			$cp->is_unit = false;
			$cp->is_modules = true;
		}

		// Find course-ID by course-name.
		if ( $cp->cp_course ) {
			$cp->course_id = CoursePress_Data_Course::by_name( $cp->cp_course, true );
			$cp->can_preview = CoursePress_Data_Capabilities::can_update_course( $cp->course_id );

			/**
			 * handle student enroll
			 */
			if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'enroll_student' ) ) {
					$user_id = get_current_user_id();
					$type = CoursePress_Data_Course::get_setting( $cp->course_id, 'enrollment_type' );
					//Enroll user only if passcode not required for course
					if ( 'passcode' != $type ) {
						CoursePress_Data_Course::enroll_student( $user_id, $cp->course_id );
					}
				}
			}

			// The course-name did not resolve to a course_id. Back to start!
			if ( ! $cp->course_id ) { self::archive_redirect(); }
		}

		// Find out if user is enrolled in current course or even instructor.
		if ( $cp->student_id && $cp->course_id ) {
			$cp->is_enrolled = CoursePress_Data_Course::student_enrolled(
				$cp->student_id,
				$cp->course_id
			);

			$cp->is_instructor = in_array(
				$cp->student_id,
				CoursePress_Data_Course::get_instructors( $cp->course_id )
			);
		}

		if ( $cp->is_unit ) {
			// All unit-contents require a permission check.
			if ( ! $cp->is_instructor && ! $cp->is_enrolled && ! $cp->can_preview ) {
				self::no_access_redirect( $cp->course_id );
			}

			if ( isset( $wp->query_vars['discussion_name'] ) ) {
				$cp->is_unit_discussion = true;
				$cp->discussion = $wp->query_vars['discussion_name'];
			} elseif ( isset( $wp->query_vars['discussion_archive'] ) ) {
				$cp->is_unit_discussion_list = true;
			} elseif ( isset( $wp->query_vars['grades_archive'] ) ) {
				$cp->is_unit_grades = true;
			} elseif ( isset( $wp->query_vars['workbook'] ) ) {
				$cp->is_unit_workbook = true;
			} else if ( isset( $wp->query_vars['notifications_archive'] ) ) {
				$cp->is_unit_notification = true;
			}
		} elseif ( isset( $wp->query_vars['post_type'] ) ) {
			$post_type = $wp->query_vars['post_type'];

			// Note: $wp->request is the slug, e.g. "courses/coursename/units".
			// Here we check, if the request starts with courses-slug.
			$is_cp = (0 == strpos( $wp->request, CoursePress_Core::get_slug( 'courses' ) ) );

			if ( $is_cp && CoursePress_Data_Course::get_post_type_name() == $post_type ) {
				$is_other_cp_page = true;
			}
		}

		if ( isset( $wp->query_vars['paged'] ) ) {
			$cp->pagination = (int) $wp->query_vars['paged'];
			CoursePress_Helper_Utility::set_the_pagination( $cp->pagination );
		}

		// We have a course-slug, find the ID.
		if ( $cp->course_id ) {
			$cp->can_preview = CoursePress_Data_Capabilities::can_update_course( $cp->course_id );
			//$preview = CoursePress_Data_Course::previewability( $cp->course_id );
			//$cp->can_preview = $preview['has_previews'];

			$cp->title = sprintf(
				'%s | %s',
				__( 'Course', 'coursepress' ),
				get_post_field( 'post_title', $cp->course_id )
			);

			CoursePress_Helper_Utility::set_the_course( $cp->course_id );
		}

		// ---------------------------------------------------------------------
		if ( $cp->is_course ) {
			// This is a single course page!
			CoursePress_Helper_Utility::$is_singular = true;

			$user_id = get_current_user_id();
			$can_update_course = CoursePress_Data_Capabilities::can_update_course( $cp->course_id );
			$course_url = CoursePress_Data_Course::get_course_url( $cp->course_id );

			// Redirect user to units overview
			if ( false === $can_update_course && CoursePress_Data_Course::student_enrolled( $user_id, $cp->course_id ) ) {
				$units_overview = $course_url . CoursePress_Core::get_slug( 'unit/' );

				//	wp_safe_redirect( $units_overview ); exit;???
			}

			/**
			 * Filter whether to display the course title.
			 *
			 * @since 2.0
			 *
			 * @param (bool) $show_title	Whether to show the title or not.
			 * @param (int) $course_id	The current course ID.
			 **/
			$show_title = apply_filters( 'coursepress_single_show_title', true, $cp->course_id );
			$theme_file = locate_template( array( 'single-course.php' ) );

			if ( $theme_file ) {
				self::$template = $theme_file;
			}

			$cp->vp_args = array(
				'slug' => 'course_' . $cp->course_id,
				'title' => get_the_title( $cp->course_id ),
				'show_title' => $show_title,
				'callback' => array( 'CoursePress_Template_Course', 'course' ),
				'context' => 'main',
				'content' => '',
				'type' => CoursePress_Data_Course::get_post_type_name(),
				'is_singular' => true,
				'ID' => $cp->course_id,
			);
			// -----------------------------------------------------------------
		} elseif ( $cp->is_completion_page ) {
			// Render completion page
			if ( $cp->is_enrolled ) {
				$student_progress = CoursePress_Data_Student::get_completion_data( $cp->student_id, $cp->course_id );
				CoursePress_Data_Student::get_calculated_completion_data( $cp->student_id, $cp->course_id, $student_progress );
				$is_failed = CoursePress_Helper_Utility::get_array_val(
					$student_progress,
					'completion/failed'
				);

				if ( cp_is_true( $is_failed ) ) {
					$title_slug = 'course_failed_title';
					$content_slug = 'course_failed_content';
				} else {
					$is_course_completed = CoursePress_Data_Student::is_course_complete( $cp->course_id, $cp->student_id, $student_progress );
					$title_slug = $is_course_completed ? 'course_completion_title' : 'pre_completion_title';
					$content_slug = $is_course_completed ? 'course_completion_content' : 'pre_completion_content';
				}
				$page_title = CoursePress_Data_Course::get_setting( $cp->course_id, $title_slug );
				$page_content = CoursePress_Data_Course::get_setting( $cp->course_id, $content_slug );

				// Replace content tokens
				$course = get_post( $cp->course_id );
				$workbook = do_shortcode( sprintf( '[student_workbook_table course_id="%s"]', $cp->course_id ) );

				$tokens = array(
					'COURSE_NAME' => $course->post_title,
					'COURSE_SUB_TITLE' => CoursePress_Data_Course::get_setting( $cp->course_id, 'course_subtitle' ),
					'COURSE_OVERVIEW' => $course->post_excerpt,
					'COURSE_UNIT_LIST' => self::course_unit_list( $cp->course_id ),
					'DOWNLOAD_CERTIFICATE_LINK' => self::download_certificate_link( $cp->course_id ),
					'DOWNLOAD_CERTIFICATE_BUTTON' => sprintf( '<a href="%s" class="download-certificate-button">%s</a>', esc_url( self::download_certificate_link( $cp->course_id ) ), __( 'Download Certificate', 'coursepress' ) ),
					'STUDENT_WORKBOOK' => $workbook,
				);

				$page_content = CoursePress_Helper_Utility::replace_vars( $page_content, $tokens );

				$cp->vp_args = array(
					'slug' => 'course_'. $cp->course_id,
					'title' => $page_title,
					'show_title' => true,
					'content' => $page_content,
					'type' => CoursePress_Data_Course::get_post_type_name(),
					'ID' => $cp->course_id,
				);
			}
		} elseif ( $cp->is_category ) {
			// Course Category Overview.
			CoursePress_Helper_Utility::set_the_course_category( $cp->cp_category );

			$course_taxonomy = CoursePress_Data_Course::get_taxonomy();
			$tax = get_term_by(
				'slug',
				$cp->cp_category,
				$course_taxonomy['taxonomy_type']
			);

			if ( $tax ) {
				$cp->title = sprintf(
					'%s %s',
					__( 'Courses in', 'coursepress' ),
					$tax->name
				);
			} elseif ( 'all' === $cp->cp_category ) {
				$cp->title = __( 'All Courses', 'coursepress' );
			} else {
				self::archive_redirect();
				// Invalid category... Redirect to course-list!
			}

			$theme_file = locate_template(
				array(
					'archive-course-' . $cp->cp_category . '.php',
					'archive-course.php',
				)
			);
			if ( $theme_file ) {
				self::$template = $theme_file;
			}

			$cp->vp_args = apply_filters(
				'coursepress_category_page_args',
				array(
					'slug' => 'course_archive',
					'title' => $cp->title,
					'show_title' => true,
					'content' => '',
					'callback' => array( 'CoursePress_Template_Course', 'course_archive' ),
					'context' => $cp->cp_category,
					'type' => CoursePress_Data_Course::get_post_type_name() . '_archive',
					'is_archive' => false,
					),
				$cp->cp_category
			);
		} elseif ( $cp->is_unit_discussion ) {
			// Unit discussion details.
			if ( ! $cp->is_instructor && ! $cp->is_enrolled ) {
				self::no_access_redirect( $cp->course_id );
			}

			CoursePress_Helper_Utility::set_the_course_subpage( 'discussions' );

			// from this point 'self::$template' is not yet set because callback will be called on 'the_content' hook,
			// 'template_include' hook will be called first so call the render functions below to set 'self::$template'

			// Are we adding a new discussion?
			$slug_new = CoursePress_Core::get_setting( 'slugs/discussions_new', 'add_new_discussion' );
			if ( $slug_new == $cp->discussion ) {
				self::render_new_course_discussion();
				$callback = array( __CLASS__, 'render_new_course_discussion' );
				/**
				 * return false to avoid comments on "Add new discussion"
				 * page.
				 */
				add_filter( 'get_comments_number', '__return_false' );
			} else {
				self::render_course_discussion();
				$callback = array( __CLASS__, 'render_course_discussion' );
			}

			$discussion = get_page_by_path(
				$cp->discussion,
				OBJECT,
				CoursePress_Data_Discussion::get_post_type_name()
			);
			if ( $discussion ) {
				$comment_status = 'open';
				CoursePress_Data_Discussion::$last_discussion = $discussion->ID;
			} else {
				$comment_status = 'closed';
				CoursePress_Data_Discussion::$last_discussion = '';
			}

			$cp->title = sprintf(
				'%s | %s',
				__( 'Discussions', 'coursepress' ),
				get_post_field( 'post_title', $cp->course_id )
			);

			$cp->vp_args = array(
				'ID' => ! empty( $discussion ) ? $discussion->ID : '',
				'slug' => 'discussion_' . $cp->course_id,
				'title' => get_the_title( $cp->course_id ),
				'callback' => $callback,
				'content' => '',
				'filter' => 'coursepress_view_course',
				'type' => 'course_discussion',
				'comment_status' => $comment_status,
			);
			// -----------------------------------------------------------------
		} elseif ( $cp->is_unit_discussion_list ) {
			// Unit discussion archive.
			if ( ! $cp->is_instructor && ! $cp->is_enrolled ) {
				self::no_access_redirect( $cp->course_id );
			}

			CoursePress_Helper_Utility::set_the_course_subpage( 'discussions' );

			$cp->title = sprintf(
				'%s | %s',
				__( 'Discussions', 'coursepress' ),
				get_post_field( 'post_title', $cp->course_id )
			);

			$cp->vp_args = array(
				'slug' => 'discussion_archive_' . $cp->course_id,
				'title' => get_the_title( $cp->course_id ),
				'content' => apply_filters(
					'coursepress_view_course',
					self::render_course_discussion_archive(),
					$cp->course_id,
					'discussion_archive'
				),
				'type' => 'course_discussion_archive',
			);
			// -----------------------------------------------------------------
		} elseif ( $cp->is_unit_grades ) {
			// Unit grades.
			if ( ! $cp->is_instructor && ! $cp->is_enrolled ) {
				self::no_access_redirect( $cp->course_id );
			}

			CoursePress_Helper_Utility::set_the_course_subpage( 'grades' );

			$cp->title = sprintf(
				'%s | %s',
				__( 'Grades', 'coursepress' ),
				get_post_field( 'post_title', $cp->course_id )
			);

			$cp->vp_args = array(
				'slug' => 'grades_archive_' . $cp->course_id,
				'title' => get_the_title( $cp->course_id ),
				'content' => apply_filters(
					'coursepress_view_course',
					self::render_course_grades_archive(),
					$cp->course_id,
					'grades_archive'
				),
				'type' => 'course_grades_archive',
			);
			// -----------------------------------------------------------------
		} elseif ( $cp->is_unit_workbook ) {
			// Unit workbook.
			if ( ! $cp->is_instructor && ! $cp->is_enrolled ) {
				self::no_access_redirect( $cp->course_id );
			}

			CoursePress_Helper_Utility::set_the_course_subpage( 'workbook' );

			$cp->title = sprintf(
				'%s | %s',
				__( 'Workbook', 'coursepress' ),
				get_post_field( 'post_title', $cp->course_id )
			);

			$cp->vp_args = array(
				'slug' => 'workbook_' . $cp->course_id,
				'title' => get_the_title( $cp->course_id ),
				'content' => apply_filters(
					'coursepress_view_course',
					self::render_course_workbook(),
					$cp->course_id,
					'workbook'
				),
				'type' => 'course_workbook',
			);
			// -----------------------------------------------------------------
		} elseif ( $cp->is_unit_notification ) {
			// Unit notifications.
			if ( ! $cp->is_instructor && ! $cp->is_enrolled ) {
				self::no_access_redirect( $cp->course_id );
			}

			CoursePress_Helper_Utility::set_the_course_subpage( 'notifications' );

			$cp->title = sprintf(
				'%s | %s',
				__( 'Notifications', 'coursepress' ),
				get_post_field( 'post_title', $cp->course_id )
			);

			$cp->vp_args = array(
				'slug' => 'notifications_archive_' . $cp->course_id,
				'title' => get_the_title( $cp->course_id ),
				'content' => apply_filters(
					'coursepress_view_course',
					self::render_course_notifications_archive(),
					$cp->course_id,
					'workbook'
				),
				'type' => 'course_notifications_archive',
			);
			// -----------------------------------------------------------------
		} elseif ( $cp->is_unit ) {
			// A differnet unit page can only be the Unit-Archive!
			CoursePress_Helper_Utility::set_the_course_subpage( 'units' );

			$cp->title = sprintf(
				'%s | %s',
				__( 'Units', 'coursepress' ),
				get_post_field( 'post_title', $cp->course_id )
			);

			$theme_file = locate_template( array( 'archive-unit.php' ) );
			if ( $theme_file ) {
				self::$template = $theme_file;
			}

			$cp->vp_args = array(
				'slug' => 'unit_archive_' . $cp->course_id,
				'title' => get_the_title( $cp->course_id ),
				'content' => '',
				'callback' => array( 'CoursePress_Template_Unit', 'unit_archive' ),
				'context' => 'units',
				'ID' => $cp->course_id,
				'type' => CoursePress_Data_Unit::get_post_type_name() . '_archive',
				'is_singular' => true,
			);
			// -----------------------------------------------------------------
		} elseif ( $cp->is_modules ) {
			// Unit With Modules.
			if ( ! $cp->is_enrolled && ! $cp->can_preview && ! $cp->is_instructor ) {
				$can_be_previewed = false;
				$view_mode = CoursePress_Data_Course::get_setting( $cp->course_id, 'course_view', 'normal' );
				/**
				 * check free preview
				 */
				if ( isset( $wp->query_vars['module_id'] ) ) {
					if ( 'focus' == $view_mode ) {
						$module_id = $wp->query_vars['module_id'];
						$can_be_previewed = CoursePress_Data_Module::can_be_previewed( $module_id );
					}
				} else {
					$unit_id = CoursePress_Data_Unit::by_name(
						$wp->query_vars['unitname'],
						true,
						$cp->course_id
					);
					$can_be_previewed = CoursePress_Data_Unit::can_be_previewed( $unit_id );
				}
				if ( ! $can_be_previewed ) {
					self::no_access_redirect( $cp->course_id );
				}
			}

			$post_id = CoursePress_Data_Unit::by_name(
				$wp->query_vars['unitname'],
				true,
				$cp->course_id
			);
			CoursePress_Helper_Utility::$is_singular = true;

			CoursePress_Helper_Utility::set_the_post( $post_id );

			$cp->title = sprintf(
				'%s',
				get_post_field( 'post_title', $cp->course_id )
			);

			$cp->vp_args = array(
				'slug' => $wp->query_vars['unitname'],
				'title' => get_the_title( $cp->course_id ),
				'content' => apply_filters(
					'coursepress_view_course_unit',
					self::render_course_unit( $post_id ),
					$cp->course_id,
					$post_id
				),
				'type' => CoursePress_Data_Unit::get_post_type_name(),
				'post_parent' => $cp->course_id,
				'ID' => $post_id, // Will load the real post.
			);
			// -----------------------------------------------------------------
		} elseif ( isset( $is_other_cp_page ) && $is_other_cp_page ) {
			// All other conditions have failed but post type is 'course':
			// It must be the archive!
			$cp->title = sprintf(
				'%s | %s',
				__( 'Courses', 'coursepress' ),
				__( 'All Courses', 'coursepress' )
			);

			$cp->vp_args = array(
				'slug' => 'course_archive',
				'title' => get_the_title( $cp->course_id ),
				'show_title' => false,
				'content' => apply_filters(
					'coursepress_view_course_archive',
					self::render_course_archive()
				),
				'type' => CoursePress_Data_Course::get_post_type_name() . '_archive',
				'is_archive' => true,
			);
		}

		/**
		 * Filter the virtual page arguments.
		 *
		 * @since 2.0
		 *
		 * @param (array) $cp->vp_args.	 The arguments to use to create a virtual page.
		 * @param (object) $cp.
		 **/
		$cp->vp_args = apply_filters( 'coursepress_virtual_page', $cp->vp_args, $cp );

		// Finally set up the virtual page, if we found a special CP page.
		if ( $cp->vp_args ) {
			// Marked the current page is CP page
			CoursePress_Core::$is_cp_page = true;

			$pg = new CoursePress_Data_VirtualPage( $cp->vp_args );
			self::$title = $cp->title;

			add_filter(
				'wp_title',
				array( __CLASS__, 'the_title' )
			);
		}
	}

	/**
	 * Returns the custom page title for our Virtual Pages.
	 *
	 * @since  2.0.0
	 * @param  string $title Default title by WordPress.
	 * @return string Custom page title.
	 */
	public static function the_title( $title ) {
		return self::$title;
	}

	public static function get_valid_post_types() {
		$pt_course = CoursePress_Data_Course::get_post_type_name();
		$pt_unit = CoursePress_Data_Unit::get_post_type_name();

		return array(
			$pt_course,
			$pt_course . '_archive',
			$pt_course . '_workbook',
			$pt_unit,
			$pt_unit . '_archive',
			'course_notifications_archive',
			'course_workbook',
			'course_discussion_archive',
			'course_discussion',
			'coursepress_instructor',
			'coursepress_student_dashboard',
			'coursepress_student_login',
			'coursepress_student_signup',
		);
	}


	public static function coursepress_front_css() {

		$valid_types = self::get_valid_post_types();
		$post_type = get_post_type();

		// Only enqueue when needed.
		if ( in_array( $post_type, $valid_types ) ) {
			$style = CoursePress::$url . 'asset/css/coursepress_front.css';
			//wp_enqueue_style( 'coursepress_general', $style, array( 'dashicons' ), CoursePress::$version );

			$style = CoursePress::$url . 'asset/css/bbm.modal.css';
			//wp_enqueue_style( 'coursepress_bbm_modal', $style, array(), CoursePress::$version );
		}
	}

	// Some themes think having an author bio makes it ok to display it... not for CoursePress.
	public static function remove_author_bio_description( $description, $user_id ) {
		$valid_types = self::get_valid_post_types();
		$post_type = get_post_type();

		if ( in_array( $post_type, $valid_types ) ) {
			return '';
		}
	}

	public static function process_course_ajax() {
		$data = json_decode( file_get_contents( 'php://input' ) );
		$json_data = array();
		$success = false;

		if ( empty( $data->action ) ) {
			$json_data['message'] = __( 'Course Update: No action.', 'coursepress' );
			wp_send_json_error( $json_data );
		}

		$action = sanitize_text_field( $data->action );
		$json_data['action'] = $action;

		l( $action, __FUNCTION__ );

		switch ( $action ) {
			case 'record_module_response':
				// Update Course.

				$course_id = (int) $data->course_id;
				$unit_id = (int) $data->unit_id;
				$module_id = (int) $data->module_id;
				$student_id = (int) $data->student_id;
				$response = $data->response;
				$module_type = $data->module_type;

				if ( CoursePress_Data_Course::get_course_status( $course_id ) == 'closed' ) {
					$json_data['message'] = __( 'This course is completed, you can not submit answers anymore.', 'coursepress' );
					wp_send_json_error( $json_data );
				}

				CoursePress_Data_Student::module_response( $student_id, $course_id, $unit_id, $module_id, $response );

				$data = CoursePress_Helper_Utility::object_to_array( $data );

				if ( 'input-quiz' == $module_type ) {

					$quiz_result = CoursePress_Data_Module::get_quiz_results( $student_id, $course_id, $unit_id, $module_id );
					$json_data['quiz_result_screen'] = CoursePress_Data_Module::quiz_result_content( $student_id, $course_id, $unit_id, $module_id, $quiz_result );
					$json_data['results'] = $quiz_result;

				}

				// Check if it is the last unit
				$units = CoursePress_Data_Course::get_units( $course_id, array( 'publish' ) );
				if ( $units ) {
					$last_unit = array_pop( $units );

					if ( ! empty( $last_unit->ID ) && $last_unit->ID == $unit_id ) {
						// Check if it is the last module
						$modules = CoursePress_Data_Course::get_unit_modules( $unit_id );
						$last_module = array_pop( $modules );

						if ( ! empty( $last_module->ID ) && $last_module->ID == $module_id ) {
							$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
							CoursePress_Data_Student::get_calculated_completion_data( $student_id, $course_id, $student_progress );

							$is_completed = CoursePress_Helper_Utility::get_array_val(
								$student_progress,
								'completion/completed'
							);
							if ( $is_completed ) {
								$json_data['completed'] = true;
							}
						}
					}
				}

				$attr = array(
					'course' => $course_id,
					'unit' => $unit_id,
					'item_id' => $module_id,
					'type' => 'module',
					'echo' => false,
				);
				$json_data['html'] = CoursePress_Data_Shortcode_Template::coursepress_focus_item( $attr );

				$json_data = array_merge( $json_data, $data );
				$success = true;
				break;

			case 'calculate_completion':

				$course_id = (int) $data->course_id;
				$student_id = (int) $data->student_id;

				if ( $student_id > 0 && $course_id > 0 ) {
					CoursePress_Data_Student::get_calculated_completion_data( $student_id, $course_id );
					CoursePress_Debugger::log( sprintf( 'Course progress updated via ajax ID: %d, STUDENT: %d', $course_id, $student_id ) );
				}

				$success = true;
				break;

			case 'comment_add_new':
				$json_data = CoursePress_Data_Discussion::comment_add_new( $data, $json_data );
				$success = true;
		}

		if ( $success ) {
			CoursePress_Data_Student::log_student_activity( 'module_answered', $json_data['student_id'] );
			wp_send_json_success( $json_data );
		} else {
			wp_send_json_error( $json_data );
		}

	}

	public static function permalink( $permalink, $post, $leavename ) {
		switch ( $post->post_type ) {

			case 'course_discussion':
			case CoursePress_Data_Discussion::get_post_type_name():

				$course_id = (int) get_post_meta( $post->ID, 'course_id', true );
				$course_id = ! empty( $course_id ) ? $course_id : CoursePress_Helper_Utility::the_course( true );

				if ( ! empty( $course_id ) ) {
					$course = get_post( $course_id );
					$discussion_url = CoursePress_Core::get_slug( 'courses/', true ) . $course->post_name . '/';
					$permalink = $discussion_url . CoursePress_Core::get_slug( 'discussion/' ) . $post->post_name;
				} else {
					return '';
				}
				break;

			case CoursePress_Data_Notification::get_post_type_name():
				break;

			case CoursePress_Data_Unit::get_post_type_name():
				break;

		}

		return $permalink;
	}

	public static function comment_number( $count, $post_id ) {
		global $wp;

		if ( array_key_exists( 'discussion_name', $wp->query_vars ) ) {
			$comments = wp_count_comments( $post_id );
			$count = $comments->approved;

			self::$discussion = $post_id;

			// If overriding the comment count, we also need to override the title.
			add_filter( 'the_title', array( __CLASS__, 'discussion_title' ), 10, 2 );

			// Also override the page num links
			add_filter( 'get_comments_pagenum_link', array( __CLASS__, 'discussion_page_num_link' ) );

		}

		return $count;
	}

	public static function discussion_title( $title, $post_id ) {
		$title = get_post_field( 'post_title', $post_id );

		return $title;
	}

	public static function discussion_page_num_link( $result ) {
		$result = preg_replace( '/.*discussion_\d*/', '', $result );
		preg_match( '/((\d*)\D*)$/', $result, $matches );
		$comment_page = (int) $matches[2];

		$course_id = get_post_meta( self::$discussion, 'course_id', true );
		$discussion_url = CoursePress_Core::get_slug( 'courses/', true ) . get_post_field( 'post_name', $course_id ) . '/';
		$discussion_url = $discussion_url . CoursePress_Core::get_slug( 'discussion/' ) . get_post_field( 'post_name', self::$discussion );

		if ( ! empty( $comment_page ) ) {
			$result = $discussion_url . '?cpage=' . $comment_page . '#comments';
		} else {
			$result = $discussion_url . '#comments';
		}

		return $result;
	}

	public static function add_discussion_comment_meta( $comment_id ) {
		if ( ! empty( CoursePress_Data_Discussion::$last_discussion ) ) {
			add_comment_meta( $comment_id, 'context', CoursePress_Data_Discussion::get_post_type_name() );
		}
	}

	/**
	 * This is a temporary fix, it can be removed later.
	 * It is needed only to update old comment data (not sure how old).
	 *
	 * @since  2.0.0
	 * @param  array $comments List of comments.
	 * @param  int   $post_id The post-ID.
	 * @return array List of comments.
	 */
	public static function temp_update_discussion_comments( $comments, $post_id ) {
		$discussion_type = CoursePress_Data_Discussion::get_post_type_name();

		if ( get_post_field( 'post_type', $post_id ) == $discussion_type ) {

			foreach ( $comments as $comment ) {
				$meta = get_comment_meta( $comment->ID, 'context', true );

				if ( $discussion_type != $meta ) {
					update_comment_meta(
						$comment->ID,
						'context',
						$discussion_type
					);
				}
			}
		}

		return $comments;
	}


	public static function remove_discussions_from_comments( $args ) {
		$discussion_type = CoursePress_Data_Discussion::get_post_type_name();

		if ( ! empty( $discussion_type ) ) {
			$args['meta_query'] = array(
				array(
					'key' => 'context',
					'value' => $discussion_type,
					'compare' => '!=',
				),
			);
		}

		return $args;
	}

	/**
	 * Set default order on Courses list pageg.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Query $query WP Query object.
	 *
	 */
	public static function set_sort_by_start_date( $query ) {
		if ( 'course' != $query->get( 'post_type' ) ) {
			return;
		}
		if ( is_admin() ) {
			return;
		}
		if ( $query->is_main_query() ) {
			$query->set( 'meta_key', 'course_start_date' );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Add "Edit Course" link to admin bar.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Admin_Bar $bar Reference to current $bar object.
	 *
	 */
	public static function add_edit_to_admin_bar_menu( $bar ) {
		global $post;
		if ( ! isset( $post->post_type ) ) {
			return;
		}
		$href = $title = $tab = false;
		switch ( $post->post_type ) {
			case 'unit_archive':
				if ( ! preg_match( '/^unit_archive_(\d+)$/', $post->post_name, $matches ) ) {
					return;
				}
				$course_id = $matches[1];
			break;
			case 'unit':
				$course_id = CoursePress_Data_Unit::get_course_id_by_unit( $post );
				$tab = 'units';
			break;
		}
		if ( empty( $course_id ) ) {
			return;
		}
		if ( ! CoursePress_Data_Capabilities::can_update_course( $course_id ) ) {
			return;
		}
		$post_type = CoursePress_Data_Course::get_post_type_name();
		$type = get_post_type_object( $post_type );
		$url_args = array(
			'action' => 'edit',
			'post' => $course_id,
		);
		if ( $tab ) {
			$url_args['tab'] = $tab;
		}
		$args = array(
			'id'	 => 'edit',
			'title'  => $type->labels->edit_item,
			'href'   => add_query_arg( $url_args, admin_url( 'post.php' ) ),
		);
		$bar->add_menu( $args );
	}

	/**
	 * Generates unit archive list
	 **/
	public static function course_unit_list( $course_id, $student_id = 0 ) {
		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}

		$units = CoursePress_Data_Course::get_units_with_modules( $course_id );
		$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );

		$list = '';

		foreach ( $units as $unit_id => $unit ) {
			$the_unit = $unit['unit'];
			$unit_class = array();
			$unit_completed = CoursePress_Helper_Utility::get_array_val(
				$student_progress,
				'completion/' . $unit_id . '/completed'
			);

			if ( cp_is_true( $unit_completed ) ) {
				$unit_class[] = 'unit-completed';
			}
			$page_list = '';

			if ( ! empty( $unit['pages'] ) ) {
				foreach ( $unit['pages'] as $page_number => $page ) {
					if ( ! empty( $page['modules'] ) ) {
						$module_list = '';

						foreach ( $page['modules'] as $module_id => $module ) {
							$module_list .= sprintf( '<li><span>%s</li>', $module->post_title );
						}

						if ( ! empty( $module_list ) ) {
							$page_list .= sprintf( '<ol>%s</ol>', $module_list );
						}
					}
				}
			}

			$list .= sprintf( '<li class="unit-list %s"><span>%s</span>%s</li>', implode( ' ', $unit_class ), $the_unit->post_title, $page_list );
		}

		$list = sprintf( '<ul class="course-unit-list">%s</ul>', $list );

		return $list;
	}

	/**
	 * Generates download certificate link
	 *
	 * @since 2.0
	 *
	 * @param (int) $course_id			The course to download the certificate from
	 * @param (int) $student_id			The student's user ID. Will use current user ID if empty.
	 * @return (string) $download_link	The URL to downloadable certificate.
	 **/
	public static function download_certificate_link( $course_id, $student_id = 0 ) {
		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}

		$download_link = add_query_arg(
			array(
				'course_id' => $course_id,
				'student_id' => $student_id,
				'action' => 'certificate',
				'nonce' => wp_create_nonce( 'coursepress_download_certificate' ),
			),
			site_url( '/' )
		);

		return $download_link;
	}

	/**
	 * Generates downloadable course certificate
	 *
	 * @since 2.0
	 **/
	public static function download_certificate() {
		if ( isset( $_REQUEST['action'] ) && 'certificate' == $_REQUEST['action']
			&& ! empty( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], 'coursepress_download_certificate' ) && isset( $_REQUEST['course_id'] ) ) {
			$course_id = (int) $_REQUEST['course_id'];
			$student_id = (int) isset( $_REQUEST['student_id'] )? $_REQUEST['student_id']:get_current_user_id();
			/**
			 * check privileges
			 */
			$current_user_id = get_current_user_id();
			if ( $student_id != $current_user_id ) {
				if ( ! CoursePress_Data_Capabilities::can_update_course( $course_id ) ) {
					_e( 'Cheatin&#8217; uh?', 'coursepress' );
					exit;
				}
			}
			CoursePress_Data_Certificate::generate_pdf_certificate( $course_id, $student_id, true );
			exit;
		}
	}
}
