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
	protected static $template = false;

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

		add_filter(
			'get_the_archive_title',
			array( __CLASS__, 'get_the_archive_title' )
		);

		add_filter( 'the_content', array( __CLASS__, 'the_content_on_single' ) );
		add_filter( 'the_content', array( __CLASS__, 'the_content_on_archive_page' ) );
		add_filter( 'the_excerpt', array( __CLASS__, 'the_excerpt_on_archive_page' ) );
		add_filter( 'post_class', array( __CLASS__, 'post_class_on_archive_page' ) );

		if ( ! CP_IS_WPMUDEV ) {
			remove_filter( 'the_content', 'wpautop' );
		}

		self::handle_module_uploads();
		CoursePress_View_Front_EnrollmentPopup::init();
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

		update_post_meta( $id, 'course_id', $course_id );
		update_post_meta( $id, 'unit_id', $unit_id );

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
		$student_id = false;
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
			$json_data['response'] = __( 'Invalid data submitted', 'CP_TD' );
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
					$json_data['response'] = __( 'Maximum allowed retries exceeded.', 'CP_TD' );
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
				$json_data['response'] = __( 'No files uploaded.', 'CP_TD' );
				$json_data['success'] = false;
			}
		}

		if ( $ajax ) {
			echo json_encode( $json_data );
			exit;
		}
	}

	/**
	 * Render the course overview.
	 *
	 * @since  2.0.0
	 * @see    self::parse_request()
	 * @return string The HTML code.
	 */
	public static function render_course_main() {
		$theme_file = locate_template( array( 'single-course.php' ) );

		if ( $theme_file ) {
			self::$template = $theme_file;
			$content = '';
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
			$content = 'Oh no, not done yet!'; // TODO this is missing!
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
	 * Prevent WordPress from doing a canonical-redirect.
	 *
	 * Canonical redirect is a SEO measurement to avoid "duplicate content"
	 * penalties, when same content is available under different URLs. However,
	 * many CoursePress pages have no real permalink, so the redirect would
	 * send the user to the wrong page. It's save to disable it for CP pages.
	 *
	 * @since  2.0.0
	 * @param  WP_Query $wp_query The global WP_Query object.
	 */
	public static function remove_canonical( $wp_query ) {
		if ( is_admin() || ! $wp_query ) {
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

	// Only enqueue when needed.
	public static function coursepress_front_css() {
		if ( ! self::_check_add_style() ) {
			return;
		}

		$style = CoursePress::$url . 'asset/css/coursepress_front.css';
		wp_enqueue_style( 'coursepress_general', $style, array( 'dashicons' ), CoursePress::$version );

		$style = CoursePress::$url . 'asset/css/bbm.modal.css';
		wp_enqueue_style( 'coursepress_bbm_modal', $style, array(), CoursePress::$version );
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
			$json_data['message'] = __( 'Course Update: No action.', 'CP_TD' );
			wp_send_json_error( $json_data );
		}

		$action = sanitize_text_field( $data->action );
		$json_data['action'] = $action;

		switch ( $action ) {
			case 'record_module_response':
				// Update Course.

				$course_id = (int) $data->course_id;
				$unit_id = (int) $data->unit_id;
				$module_id = (int) $data->module_id;
				$student_id = (int) $data->student_id;
				$response = $data->response;
				$module_type = $data->module_type;

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

				$json_data = array_merge( $json_data, $data );
				$success = true;
				break;

			case 'calculate_completion':

				$course_id = (int) $data->course_id;
				$student_id = (int) $data->student_id;

				if ( $student_id > 0 ) {
					$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
					CoursePress_Data_Student::get_calculated_completion_data( $student_id, $course_id, $student_progress );
					CoursePress_Data_Student::update_completion_data( $student_id, $course_id, $student_progress );
				}

				$success = true;
				break;
		}

		if ( $success ) {
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

	public static function get_the_archive_title( $title ) {
		if ( CoursePress_Data_Course::is_archvie() ) {
			return __( 'All Courses', 'CP_TD' );
		}
		return $title;
	}

	public static function the_excerpt_on_archive_page( $excerpt ) {
		if ( CoursePress_Data_Course::is_archvie() ) {
			//            return false;
		}
		return $excerpt;
	}

	public static function the_content_on_single( $content ) {
		if ( ! CoursePress_Data_Course::is_single() ) {
			return $content;
		}
		global $post;

		$cp_action = get_query_var( 'cp_action' );

		switch ( $cp_action ) {
			case 'show_single_unit':
				$unitname = get_query_var( 'unitname' );
				$unit_id = CoursePress_Data_Unit::by_name( $unitname, true );
				CoursePress_Helper_Utility::set_the_post( $unit_id );
			return CoursePress_Template_Unit::unit_with_modules();

			case 'show_units':
				return CoursePress_Template_Unit::unit_archive();

			case 'notifications_archive':
				return CoursePress_Template_Communication::render_notifications();

			case 'discussions_archive':
				return CoursePress_Template_Communication::render_discussions();

			case 'discussion_new':
				return CoursePress_Template_Communication::render_new_discussion();

			case 'discussion_show':
				return CoursePress_Template_Communication::render_discussion();

			case 'workbook':
				return self::render_course_workbook();

			default:
				$args = array(
				'course_id' => $post->ID,
				);
			return CoursePress_Data_Shortcode_Template::course_page( $args );
		}
		return $content;
	}

	public static function the_content_on_archive_page( $content ) {
		if (
			CoursePress_Data_Course::is_archvie()
			|| CoursePress_Data_Course::is_course_category()
		) {
			global $post;
			$args = array(
				'course_id' => $post->ID,
				'show_title' => false,
				'show_excerpt' => false,
			);
			return CoursePress_Data_Shortcode_Template::course_list_box( $args );
		}
		return $content;
	}

	public static function post_class_on_archive_page( $classes ) {
		/**
		 * fix twentysixteen styles
		 */
		if (
			CoursePress_Data_Course::is_archvie()
			|| CoursePress_Data_Course::is_single()
			|| CoursePress_Data_Course::is_course_category()
		) {
			array_unshift( $classes, 'type-page' );
		}
		return $classes;
	}

	/**
	 * Check when enqueue styles
	 *
	 * @since 2.0.0
	 *
	 * @global WP_Post $post Current WP Post object.
	 *
	 * @return boolean Enqueue styles or not enqueue?
	 */
	private static function _check_add_style() {
		$valid_types = self::get_valid_post_types();
		$post_type = get_post_type();

		// Only enqueue when needed.
		if ( in_array( $post_type, $valid_types ) ) {
			return true;
		}

		/**
		 * check is maybe some of CoursePress pages?
		 */
		if ( is_page() ) {
			global $post;
			$pages = CoursePress_Core::get_setting( 'pages' );
			return in_array( $post->ID, $pages );
		}

		/**
		 * by default return false
		 */
		return false;
	}
}
