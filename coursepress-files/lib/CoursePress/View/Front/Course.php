<?php

class CoursePress_View_Front_Course {

	public static $discussion = false;  // Used for hooking discussion filters
	public static $title = ''; // The page title
	public static $args = array();

	public static function init() {

		//add_action( 'wp', array( __CLASS__, 'load_plugin_templates' ) );

		add_action( 'pre_get_posts', array( __CLASS__, 'remove_canonical' ) );
		add_action( 'parse_request', array( __CLASS__, 'parse_request' ) );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'coursepress_front_css' ) );
		add_filter( 'get_the_author_description', array( __CLASS__, 'remove_author_bio_description' ), 10, 2 );

		// Discussion filters
		add_filter( 'post_type_link', array( __CLASS__, 'permalink' ), 10, 3 );
		add_filter( 'get_comments_number', array( __CLASS__, 'comment_number' ), 10, 2 );
		add_filter( 'comments_array', array(
			__CLASS__,
			'update_discussion_comments'
		), 10, 2 ); // Leave this here for a few updates
		add_filter( 'widget_comments_args', array( __CLASS__, 'remove_discussions_from_comments' ) );
		add_action( 'comment_post', array( __CLASS__, 'add_discussion_comment_meta' ) );

		self::handle_module_uploads();

		if( ! CoursePress_Model_Capabilities::is_wpmudev() ) {
			remove_filter( 'the_content', 'wpautop' );
		}

		add_action( 'init', array( __CLASS__, 'handle_form_posts' ) );
		//self::handle_form_posts();

		CoursePress_View_Front_EnrollmentPopup::init();
	}

	public static function test2( $location, $comment ) {

		$x = '';
		return self::$args['discussion_url'];

	}

	public static function init_ajax() {
		add_action( 'wp_ajax_course_front', array( __CLASS__, 'process_course_ajax' ) );

		CoursePress_View_Front_EnrollmentPopup::init_ajax();
	}

	public static function handle_form_posts() {

		// Handle comments post
		if( ! empty( $_POST['comment_post_ID'] ) ) {
			$module = get_post( $_POST['comment_post_ID'] );
			$course_link = get_permalink( get_post_field( 'post_parent', $module->post_parent ) );

			$return_url = esc_url_raw( $course_link . trailingslashit( CoursePress_Core::get_slug( 'unit' ) ) . get_post_field('post_name', $module->post_parent) . '#module-' . $module->ID );
			self::$args['discussion_url'] = $return_url;

			//add_filter( 'comment_post_redirect', array( __CLASS__, 'test2' ), 10, 2 );
		}

		// Add new discussion post
		if ( is_user_logged_in() && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'add-new-discussion' ) ) {

			// Update the discussion
			$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : false;

			$content     = CoursePress_Helper_Utility::filter_content( $_POST['discussion_content'] );
			$title       = CoursePress_Helper_Utility::filter_content( $_POST['discussion_title'] );
			$course_id   = 'all' === $_POST['course_id'] ? sanitize_text_field( $_POST['course_id'] ) : (int) $_POST['course_id'];
			$unit_id     = 'course' === $_POST['unit_id'] ? sanitize_text_field( $_POST['unit_id'] ) : (int) $_POST['unit_id'];
			$post_status = 'publish';

			$args = array(
				'post_title'     => $title,
				'post_content'   => $content,
				'post_type'      => CoursePress_Model_Discussion::get_post_type_name(),
				'post_status'    => $post_status,
				'post_author'    => get_current_user_id(),
				'comment_status' => 'open'
			);

			if ( ! empty( $id ) ) {
				$args['ID'] = $id;
			}

			$id = wp_insert_post( $args );

			update_post_meta( $id, 'course_id', $course_id );
			update_post_meta( $id, 'unit_id', $unit_id );

			$url = trailingslashit( CoursePress_Core::get_slug( 'course', true ) ) . get_post_field( 'post_name', $course_id ) . '/' . trailingslashit( CoursePress_Core::get_slug( 'discussions' ) );;
			wp_redirect( esc_url_raw( $url ) );
			exit;

		}

	}

	public static function handle_module_uploads() {

		if ( ! empty( $_REQUEST['course_action'] ) && 'upload-file' === $_REQUEST['course_action'] ) {

			$json_data       = array();
			$error           = false;
			$ajax            = false;
			$skip_processing = false;

			$course_id  = isset( $_REQUEST['course_id'] ) ? (int) $_REQUEST['course_id'] : false;
			$unit_id    = isset( $_REQUEST['unit_id'] ) ? (int) $_REQUEST['unit_id'] : false;
			$module_id  = isset( $_REQUEST['module_id'] ) ? (int) $_REQUEST['module_id'] : false;
			$student_id = isset( $_REQUEST['student_id'] ) ? (int) $_REQUEST['student_id'] : false;

			$student_progress = array();
			if ( $student_id ) {
				$student_progress = CoursePress_Model_Student::get_completion_data( $student_id, $course_id );
			}

			// Check if we should continue with this upload (in case students cheat with the code)
			$attributes     = CoursePress_Model_Module::attributes( $module_id );
			$responses      = CoursePress_Helper_Utility::get_array_val( $student_progress, 'units/' . $unit_id . '/responses/' . $module_id );
			$response_count = ! empty( $responses ) ? count( $responses ) : 0;
			$disabled       = ! $attributes['allow_retries'];
			$disabled       = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

			if ( $disabled ) {
				$json_data['response'] = __( 'Maximum allowed retries exceeded.', CoursePress::TD );
				$json_data['success']  = false;
				$skip_processing       = true;
			}

			if ( ! $course_id && ! $unit_id && ! $module_id ) {
				$json_data['response'] = __( 'Invalid data submitted', CoursePress::TD );
				$json_data['success']  = false;
				$skip_processing       = true;
			}

			if ( ! $skip_processing ) {
				if ( isset( $_REQUEST['src'] ) && 'ajax' === $_REQUEST['src'] ) {
					$ajax = true;
				}


				if ( ! function_exists( 'wp_handle_upload' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
				}

				$upload_overrides = array(
					'test_form' => false,
					'mimes'     => CoursePress_Helper_Utility::allowed_student_mimes()
				);

				$response = false;
				if ( isset( $_FILES ) ) {

					foreach ( $_FILES as $file ) {

						try {

							if ( ! function_exists( 'get_userdata' ) ) {
								require_once( ABSPATH . 'wp-includes/pluggable.php' );
							}

							$response         = wp_handle_upload( $file, $upload_overrides );
							$response['size'] = $file['size'];
							if ( isset( $response['error'] ) ) {
								$json_data['response'] = $response['error'];
								$json_data['success']  = false;
							} else {
								$json_data['response'] = $response;
								$json_data['success']  = true;

								// Record the response
								if ( $student_id ) {
									CoursePress_Model_Student::module_response( $student_id, $course_id, $unit_id, $module_id, $response, $student_progress );
								}

							}

						} catch ( Exception $e ) {
							$json_data['response'] = $e->getMessage();
							$json_data['success']  = false;
						}

					}

				} else {
					$json_data['response'] = __( 'No files uploaded.', CoursePress::TD );
					$json_data['success']  = false;
				}

			}

			if ( $ajax ) {

				// Response
				echo json_encode( $json_data );
				exit;

			}

		}

	}

	public static function load_plugin_templates() {
		global $wp_query;

		if ( array_key_exists( 'coursename', $wp_query->query_vars ) && array_key_exists( 'unitname', $wp_query->query_vars ) ) {

			$wp_query->is_page     = true;
			$wp_query->is_singular = true;
			$wp_query->is_home     = false;
			$wp_query->is_archive  = false;
			$wp_query->is_category = false;
			unset( $wp_query->query['error'] );
			$wp_query->query_vars['error'] = '';
			$wp_query->is_404              = false;

		}

		$x = '';


		//$post_type = get_query_var( 'post_type' );
		//$is_archive = get_query_var( 'is_archive' );
		//$post_parent = get_query_var( 'post_parent' );
		//
		//if( isset( $wp_query->query['page_id'] ) ) {
		//	set_query_var( 'page_id', (int) $wp_query->query['page_id'] );
		//}
		//
		//$name = get_query_var( 'course' );
		//if( ! empty( $name ) ) {
		//	$post_type = CoursePress_Model_Course::get_post_type_name( true );
		//}
		//
		//$coursename = get_query_var( 'coursename' );
		//$unitname = get_query_var( 'unitname' );
		//
		//if( ! empty( $coursename ) && ! empty( $unitname ) ) {
		//	$post_parent = CoursePress_Model_Course::by_name( $coursename, true );
		//	$post_type = CoursePress_Model_Unit::get_post_type_name( true );
		//} else if ( ! empty( $coursename ) ) {
		//	$post_parent = CoursePress_Model_Course::by_name( $coursename, true );
		//	$is_archive = true;
		//}
		//
		//$wp_query->posts = array( get_post( $post_parent ) );
		//$wp_query->post_count = 1;
		//
		//set_query_var( 'post_type', $post_type );
		//set_query_var( 'post_parent', $post_parent );
		//set_query_var( 'is_archive', $is_archive );
		//
		//// Render Main Course
		//if( ! empty( $name ) ) {
		//	//set_query_var( 'post_type', CoursePress_Model_Course::get_post_type_name( true ) );
		//	add_filter( 'the_content', array( __CLASS__, 'render_course_main' ), 1 );
		//}
		//
		//// Render Unit
		//if( empty( $name ) && ! empty( $coursename ) && ! empty( $unitname ) ) {
		//	//set_query_var( 'post_type', CoursePress_Model_Course::get_post_type_name( true ) );
		//	add_filter( 'the_content', array( __CLASS__, 'render_course_unit' ), 1 );
		//}
		//
		//// Render Unit Archive Display
		//if( empty( $name ) && ! empty( $coursename ) && empty( $unitname ) ) {
		//	//set_query_var( 'post_type', CoursePress_Model_Course::get_post_type_name( true ) );
		//	add_filter( 'the_content', array( __CLASS__, 'render_course_unit_archive' ), 1 );
		//}
		//
		//
		//
		//$x = '';
		//
		//
		//if ( get_post_type() == 'course' && is_archive() ) {
		//	//add_filter( 'the_content', array( &$this, 'courses_archive_custom_content' ), 1 );
		//	//add_filter( 'the_excerpt', array( &$this, 'courses_archive_custom_content' ), 1 );
		//	//add_filter( 'get_the_excerpt', array( &$this, 'courses_archive_custom_content' ), 1 );
		//}
		//
		//if ( get_post_type() == 'discussions' && is_single() ) {
		//	//add_filter( 'the_content', array( &$this, 'add_custom_before_discussion_single_content' ), 1 );
		//}
		//
		//if ( is_post_type_archive( 'course' ) ) {
		//	//add_filter( 'post_type_archive_title', array( &$this, 'courses_archive_title' ), 1 );
		//}
	}

	public static function render_course_main() {


		if ( $theme_file = locate_template( array( 'single-course.php' ) ) ) {
		} else {
			//wp_enqueue_style( 'front_course_single', $this->plugin_url . 'css/front_course_single.css', array(), $this->version );
//			if ( locate_template( array( 'single-course.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
				//just output the content
//			} else {
				//
				//	//if ( get_post_type( $wpdb->last_result[ 0 ]->post_id ) == 'course' ) {
				//	if ( get_post_type() == 'course' ) {
				//		$prepend_content = $this->get_template_details( $this->plugin_dir . 'includes/templates/single-course-before-details.php' );
				//		$content		 = do_shortcode( $prepend_content . $content );
				//	} else {
				//		return $content;
				//	}

				$content = CoursePress_Template_Course::course();

//			}
		}

		return $content;
	}

	public static function render_course_unit( $post_ID ) {
		// Set the post so we can get it in Templates
		CoursePress_Helper_Utility::set_the_post( $post_ID );

		/**
		 * @notes
		 *
		 * Catalin, I've commented this out, could you please try to reproduce the functionality in the templates. Not working at the moment with templates.
		 *
		 */

		if ( $theme_file = locate_template( array( 'single-unit.php' ) ) ) {
			ob_start();
			require $theme_file;
			$content = ob_get_clean();
		} else {
			$content = CoursePress_Template_Unit::unit_with_modules();
		}

		return $content;
	}

	public static function render_course_unit_archive() {

		if ( $theme_file = locate_template( array( 'archive-unit.php' ) ) ) {
		} else {
			//wp_enqueue_style( 'front_course_single', $this->plugin_url . 'css/front_course_single.css', array(), $this->version );
			if ( locate_template( array( 'archive-unit.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
				//just output the content
			} else {

				$content = CoursePress_Template_Unit::unit_archive();

			}
		}

		return $content;
	}

	public static function render_course_archive_bak() {

		$category               = CoursePress_Helper_Utility::the_course_category();
		$category_template_file = locate_template( array( 'archive-course-' . $category . '.php' ) );

		if ( ! empty( $category_template_file ) ) {

		} elseif ( $theme_file = locate_template( array( 'archive-course.php' ) ) ) {
			ob_start();
			require $theme_file;
			$content = ob_get_clean();
		} else {
				$content = CoursePress_Template_Course::course_archive();
		}

		return $content;

	}

	public static function render_course_archive() {

		$content = CoursePress_Template_Course::course_archive();
		return $content;
	}

	public static function render_course_discussion() {
		if ( $theme_file = locate_template( array( 'single-course-discussion.php' ) ) ) {
		} else {
			//wp_enqueue_style( 'front_course_single', $this->plugin_url . 'css/front_course_single.css', array(), $this->version );
			if ( locate_template( array( 'single-course-discussion.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
				//just output the content
			} else {

				$content = CoursePress_Template_Communication::render_discussion();

			}
		}

		return $content;
	}

	public static function render_new_course_discussion() {
		if ( $theme_file = locate_template( array( 'page-add-new-discussion.php' ) ) ) {
		} else {
			//wp_enqueue_style( 'front_course_single', $this->plugin_url . 'css/front_course_single.css', array(), $this->version );
			if ( locate_template( array( 'page-add-new-discussion.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
				//just output the content
			} else {

				$content = CoursePress_Template_Communication::render_new_discussion();

			}
		}

		return $content;
	}

	public static function render_course_discussion_archive() {
		if ( $theme_file = locate_template( array( 'archive-course-discussions.php' ) ) ) {
		} else {
			//wp_enqueue_style( 'front_course_single', $this->plugin_url . 'css/front_course_single.css', array(), $this->version );
			if ( locate_template( array( 'archive-course-discussions.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
				//just output the content
			} else {

				$content = CoursePress_Template_Communication::render_discussions();

			}
		}

		return $content;
	}

	public static function render_course_grades_archive() {
		return 'Grades....';
	}

	public static function render_course_workbook() {
		if ( $theme_file = locate_template( array( 'archive-unit-workbook.php' ) ) ) {
		} else {
			//wp_enqueue_style( 'front_course_single', $this->plugin_url . 'css/front_course_single.css', array(), $this->version );
			if ( locate_template( array( 'archive-unit-workbook.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
				//just output the content
			} else {

				$content = CoursePress_Template_Workbook::render_workbook();

			}
		}

		return $content;
	}

	public static function render_course_notifications_archive() {
		if ( $theme_file = locate_template( array( 'archive-course-notifications.php' ) ) ) {
		} else {
			//wp_enqueue_style( 'front_course_single', $this->plugin_url . 'css/front_course_single.css', array(), $this->version );
			if ( locate_template( array( 'archive-course-notifications.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
				//just output the content
			} else {

				$content = CoursePress_Template_Communication::render_notifications();

			}
		}

		return $content;
	}

	public static function remove_canonical( $wp_query ) {

		global $wp_query;
		if ( is_admin() || empty( $wp_query ) ) {
			return;
		}

		$page       = get_query_var( 'pagename' );
		$course     = get_query_var( 'course' );
		$coursename = get_query_var( 'coursename' );

		if ( $page == 'dashboard' || ! empty( $course ) || ! empty( $coursename ) ) {
			remove_action( 'template_redirect', 'redirect_canonical' );
		}
	}

	public static function no_access_redirect( $course_id ) {
		$course_url = trailingslashit( CoursePress_Core::get_slug( 'courses', true ) ) . get_post_field( 'post_name', $course_id );

		wp_redirect( esc_url_raw( $course_url ) );
		exit;
	}

	public static function archive_redirect() {

		$archive_url = trailingslashit( CoursePress_Core::get_slug( 'courses', true ) );
		wp_redirect( esc_url_raw( $archive_url ) );
		exit;
	}

	public static function parse_request( &$wp ) {
		global $wp_query;
		$context = '';

		// Check Focus Mode First
		if( array_key_exists( 'coursepress_focus', $wp->query_vars ) && (int) $wp->query_vars['coursepress_focus'] === 1 ) {
			$course_id = (int) $wp->query_vars['course'];
			$unit_id = (int) $wp->query_vars['unit'];
			$type = sanitize_text_field( $wp->query_vars['type'] );
			$item_id = (int) $wp->query_vars['item'];
			echo do_shortcode('[coursepress_focus_item course="' . $course_id . '" unit="' . $unit_id . '" type="' . $type . '" item_id="' . $item_id . '"]');
			die(); // We only want the content, so die() here.
		}

		CoursePress_Helper_Utility::$is_singular = false;

		$is_category_page = false;

		// THIS IS WHERE WE WANT TO DO ACCESS CONTROL

		$student_id = is_user_logged_in() ? get_current_user_id() : false;

		// Do nothing if its a normal course page
		if ( array_key_exists( 'course', $wp->query_vars ) ) {

			$course_id = CoursePress_Model_Course::by_name( $wp->query_vars['course'], true );
			if ( empty( $course_id ) ) {
				//self::archive_redirect();
			}

			$title = sprintf( '%s | %s', __( 'Course', CoursePress::TD ), get_post_field( 'post_title', $course_id ) );

			CoursePress_Helper_Utility::set_the_course( $course_id );
			CoursePress_Helper_Utility::set_the_course_subpage( '' );

			// Warning: A course should not have the same post_name as the category slug, it will be skipped
			$is_category_page = $wp->query_vars['course'] === CoursePress_Core::get_slug( 'category' );

			if ( ! $is_category_page ) {

				CoursePress_Helper_Utility::$is_singular = true;

				$args = array(
					'slug'    => 'course_' . $course_id,
					'title'   => get_the_title( $course_id ),
					'show_title'  => false,
					'content' => apply_filters( 'coursepress_view_course', self::render_course_main(), $course_id, 'main' ),
					'type'    => CoursePress_Model_Course::get_post_type_name( true ),
				);

				$pg = new CoursePress_Model_VirtualPage( $args );

				self::$title = $title;
				add_filter( 'wp_title', array( __CLASS__, 'the_title' ) );

				return $pg;

			}

		}

		// Course Category
		if ( array_key_exists( 'course_category', $wp->query_vars ) || $is_category_page ) {

			$category = $is_category_page ? '' : $wp->query_vars['course_category'];
			CoursePress_Helper_Utility::set_the_course_category( $category );

			$course_taxonomy = CoursePress_Model_Course::get_taxonomy();
			$tax = get_term_by( 'slug', $category, $course_taxonomy['taxonomy_type'] );
			$tax = ! empty( $tax ) ? $tax->name : '';
			$title = ! empty( $tax ) ? sprintf( '%s %s', __( 'Courses in', CoursePress::TD ), $tax ) : '';
			$title = empty( $title ) && 'all' === $category ? __( 'All Courses', CoursePress::TD ) : $title;

			// Redirect...
			if( empty( $title ) ) {
				self::archive_redirect();
			}

			//'course_category'
			$args = apply_filters( 'coursepress_category_page_args', array(
				'slug'       => 'course_archive',
				'title'		 => $title,
				'show_title' => true,
				'content'    => apply_filters( 'coursepress_view_course_archive', self::render_course_archive(), $category ),
				'type'       => CoursePress_Model_Course::get_post_type_name( true ) . '_archive',
				'is_archive' => true
			), $category );

			$pg = new CoursePress_Model_VirtualPage( $args );

			self::$title = $title;
			add_filter( 'wp_title', array( __CLASS__, 'the_title' ) );

//			$category               = CoursePress_Helper_Utility::the_course_category();
//			$category_template_file = locate_template( array( 'archive-course-' . $category . '.php' ) );
//			$theme_file = locate_template( array( 'archive-course.php' ) );
//
//			if ( ! empty( $category_template_file ) ) {
//				require $category_template_file;
//			} elseif ( $theme_file ) {
//				require $theme_file;
//			} else {
				return $pg;
//			}

		}

		// Unit Archive and other unit pages
		if ( array_key_exists( 'coursename', $wp->query_vars ) && ! array_key_exists( 'unitname', $wp->query_vars ) ) {

			$post_parent = CoursePress_Model_Course::by_name( $wp->query_vars['coursename'], true );
			CoursePress_Helper_Utility::set_the_course( $post_parent );

			$preview  = CoursePress_Model_Course::previewability( $post_parent );
			$enrolled = ! empty( $student_id ) ? CoursePress_Model_Course::student_enrolled( $student_id, $post_parent ) : false;
			$instructors = CoursePress_Model_Course::get_instructors( $post_parent );
			$is_instructor = in_array( $student_id, $instructors );

			if ( ! $is_instructor && ! $enrolled && ! $preview['has_previews'] ) {
				self::no_access_redirect( $post_parent );
			}

			// Discussion
			if ( array_key_exists( 'discussion_name', $wp->query_vars ) ) {
				if ( ! $is_instructor && ! $enrolled ) {
					self::no_access_redirect( $post_parent );
				}

				$title = sprintf( '%s | %s', __( 'Discussions', CoursePress::TD ), get_post_field( 'post_title', $post_parent ) );

				// Are we adding a new discussion?
				if ( CoursePress_Core::get_slug( 'discussion_new' ) === $wp->query_vars['discussion_name'] ) {
					$discussion_content = self::render_new_course_discussion();
				} else {
					$discussion_content = self::render_course_discussion();
				}

				CoursePress_Helper_Utility::set_the_course_subpage( 'discussions' );

				$discussion_title                              = get_the_title( $post_parent );
				$post_name                                     = $wp->query_vars['discussion_name'];
				$discussion                                    = get_page_by_path( $post_name, OBJECT, CoursePress_Model_Discussion::get_post_type_name() );
				$comment_status                                = ! empty( $discussion ) ? 'open' : 'closed';
				CoursePress_Model_Discussion::$last_discussion = ! empty( $discussion ) ? $discussion->ID : '';

				$args = array(
					'ID'             => ! empty( $discussion ) ? $discussion->ID : '',
					'slug'           => 'discussion_' . $post_parent,
					'title'          => $discussion_title,
					'content'        => apply_filters( 'coursepress_view_course', $discussion_content, $post_parent, 'discussion' ),
					'type'           => 'course_discussion',
					'comment_status' => $comment_status,

				);

				$pg = new CoursePress_Model_VirtualPage( $args );

				self::$title = $title;
				add_filter( 'wp_title', array( __CLASS__, 'the_title' ) );
				return;
			}

			// Discussion Archive
			if ( array_key_exists( 'discussion_archive', $wp->query_vars ) ) {
				if ( ! $is_instructor && ! $enrolled ) {
					self::no_access_redirect( $post_parent );
				}
				CoursePress_Helper_Utility::set_the_course_subpage( 'discussions' );

				$title = sprintf( '%s | %s', __( 'Discussions', CoursePress::TD ), get_post_field( 'post_title', $post_parent ) );

				$args = array(
					'slug'    => 'discussion_archive_' . $post_parent,
					'title'   => get_the_title( $post_parent ),
					//'show_title'  => false,
					'content'        => apply_filters( 'coursepress_view_course', self::render_course_discussion_archive(), $post_parent, 'discussion_archive' ),
					'type'    => 'course_discussion_archive',
				);

				$pg = new CoursePress_Model_VirtualPage( $args );

				self::$title = $title;
				add_filter( 'wp_title', array( __CLASS__, 'the_title' ) );
				return;
			}

			// Grades
			if ( array_key_exists( 'grades_archive', $wp->query_vars ) ) {
				if ( ! $is_instructor && ! $enrolled ) {
					self::no_access_redirect( $post_parent );
				}

				CoursePress_Helper_Utility::set_the_course_subpage( 'grades' );

				$title = sprintf( '%s | %s', __( 'Grades', CoursePress::TD ), get_post_field( 'post_title', $post_parent ) );

				$args = array(
					'slug'    => 'grades_archive_' . $post_parent,
					'title'   => get_the_title( $post_parent ),
					//'show_title'  => false,
					'content'        => apply_filters( 'coursepress_view_course', self::render_course_grades_archive(), $post_parent, 'grades_archive' ),
					'type'    => 'course_grades_archive',
				);

				$pg = new CoursePress_Model_VirtualPage( $args );

				self::$title = $title;
				add_filter( 'wp_title', array( __CLASS__, 'the_title' ) );
				return;
			}

			// Workbook
			if ( array_key_exists( 'workbook', $wp->query_vars ) ) {
				if ( ! $is_instructor && ! $enrolled ) {
					self::no_access_redirect( $post_parent );
				}

				CoursePress_Helper_Utility::set_the_course_subpage( 'workbook' );

				$title = sprintf( '%s | %s', __( 'Workbook', CoursePress::TD ), get_post_field( 'post_title', $post_parent ) );

				$args = array(
					'slug'    => 'workbook_' . $post_parent,
					'title'   => get_the_title( $post_parent ),
					//'show_title'  => false,
					'content'        => apply_filters( 'coursepress_view_course', self::render_course_workbook(), $post_parent, 'workbook' ),
					'type'    => 'course_workbook',
				);

				$pg = new CoursePress_Model_VirtualPage( $args );

				self::$title = $title;
				add_filter( 'wp_title', array( __CLASS__, 'the_title' ) );
				return;
			}

			// Notifications
			if ( array_key_exists( 'notifications_archive', $wp->query_vars ) ) {
				if ( ! $is_instructor && ! $enrolled ) {
					self::no_access_redirect( $post_parent );
				}

				CoursePress_Helper_Utility::set_the_course_subpage( 'notifications' );

				$title = sprintf( '%s | %s', __( 'Notifications', CoursePress::TD ), get_post_field( 'post_title', $post_parent ) );

				$args = array(
					'slug'    => 'notifications_archive_' . $post_parent,
					'title'   => get_the_title( $post_parent ),
					//'show_title'  => false,
					'content'        => apply_filters( 'coursepress_view_course', self::render_course_notifications_archive(), $post_parent, 'workbook' ),
					'type'    => 'course_notifications_archive',
				);

				$pg = new CoursePress_Model_VirtualPage( $args );

				self::$title = $title;
				add_filter( 'wp_title', array( __CLASS__, 'the_title' ) );
				return;
			}


			// If nothing else got rendered, then its most likely the Unit Archive
			// Units Archive
			CoursePress_Helper_Utility::set_the_course_subpage( 'units' );

			$title = sprintf( '%s | %s', __( 'Units', CoursePress::TD ), get_post_field( 'post_title', $post_parent ) );

			$args = array(
				'slug'    => 'unit_archive_' . $post_parent,
				'title'   => get_the_title( $post_parent ),
				//'show_title'  => false,
				'content'        => apply_filters( 'coursepress_view_course', self::render_course_unit_archive(), $post_parent, 'workbook' ),
				'type'    => CoursePress_Model_Unit::get_post_type_name( true ) . '_archive',
			);

			$pg = new CoursePress_Model_VirtualPage( $args );

			self::$title = $title;
			add_filter( 'wp_title', array( __CLASS__, 'the_title' ) );
			return;
		}

		// Unit With Modules
		if ( array_key_exists( 'coursename', $wp->query_vars ) && array_key_exists( 'unitname', $wp->query_vars ) ) {

			CoursePress_Helper_Utility::$is_singular = true;
			$post_parent                             = CoursePress_Model_Course::by_name( $wp->query_vars['coursename'], true );
			CoursePress_Helper_Utility::set_the_course( $post_parent );
			CoursePress_Helper_Utility::set_the_course_subpage( '' );

			// Access control
			$student_id = get_current_user_id();
			$instructors = CoursePress_Model_Course::get_instructors( $post_parent );
			$is_instructor = in_array( $student_id, $instructors );

			$preview  = CoursePress_Model_Course::previewability( $post_parent );
			$enrolled = ! empty( $student_id ) ? CoursePress_Model_Course::student_enrolled( $student_id, $post_parent ) : false;
			if ( ! $enrolled && ! $preview['has_previews'] && ! $is_instructor ) {
				self::no_access_redirect( $post_parent );
			}

			//$student_progress = CoursePress_Model_Student::get_completion_data( $student_id, $post_parent );
			//error_log( print_r( $student_progress, true ) );

			// Unit page
			$unit_page = array_key_exists( 'paged', $wp->query_vars ) ? (int) $wp->query_vars['paged'] : 1;
			CoursePress_Helper_Utility::set_the_post_page( $unit_page );

			$post_ID = CoursePress_Model_Unit::by_name( $wp->query_vars['unitname'], true, $post_parent );
			// If not by post name, perhaps its the actual ID
			$post_ID = empty( $post_ID ) ? (int) $wp->query_vars['unitname'] : $post_ID;

			$title = sprintf( '%s', get_post_field( 'post_title', $post_parent ) );


			$args = array(
				'slug'        => $wp->query_vars['unitname'],
				'title'       => get_the_title( $post_parent ),
				//'show_title'  => false,
				'content'        => apply_filters( 'coursepress_view_course_unit', self::render_course_unit( $post_ID ), $post_parent, $post_ID ),
				'type'        => CoursePress_Model_Unit::get_post_type_name( true ),
				'post_parent' => $post_parent,
				'ID'          => $post_ID // Will load the real post
			);

			$pg = new CoursePress_Model_VirtualPage( $args );

			self::$title = $title;
			add_filter( 'wp_title', array( __CLASS__, 'the_title' ) );
//			return $pg;
		}

		// All other conditions have failed, so if post type is course, it must be the archive
		if ( isset( $wp->query_vars['post_type'] ) && CoursePress_Model_Course::get_post_type_name() === $wp->query_vars['post_type'] && CoursePress_Core::get_slug( 'courses' ) === $wp->request ) {

			$title = sprintf( '%s | %s', __( 'Courses', CoursePress::TD ), __( 'All Courses', CoursePress::TD ) );

			$args = array(
				'slug'       => 'course_archive',
				'title'		 => get_the_title( $post_parent ),
				'show_title' => false,
				'content'    => apply_filters( 'coursepress_view_course_archive', self::render_course_archive() ),
				'type'       => CoursePress_Model_Course::get_post_type_name( true ) . '_archive',
				'is_archive' => true
			);

			$pg = new CoursePress_Model_VirtualPage( $args );

			self::$title = $title;
			add_filter( 'wp_title', array( __CLASS__, 'the_title' ) );
			return $pg;

		}


	}

	public static function the_title( $title ) {
		return self::$title;
	}

	public static function get_valid_post_types() {
		return array(
			CoursePress_Model_Course::get_post_type_name( true ),
			CoursePress_Model_Course::get_post_type_name( true ) . '_archive',
			CoursePress_Model_Course::get_post_type_name( true ) . '_workbook',
			CoursePress_Model_Unit::get_post_type_name( true ),
			CoursePress_Model_Unit::get_post_type_name( true ) . '_archive',
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
		global $wp_query;

		$valid_types = self::get_valid_post_types();

		$post_type = get_post_type();

		// Only enqueue when needed
		if ( in_array( $post_type, $valid_types ) ) {

			$style = CoursePress_Core::$plugin_lib_url . 'styles/coursepress_front.css';
			wp_enqueue_style( 'coursepress_general', $style, array( 'dashicons' ), CoursePress_Core::$version );

			$style = CoursePress_Core::$plugin_lib_url . 'styles/bbm.modal.css';
			wp_enqueue_style( 'coursepress_bbm_modal', $style, array(), CoursePress_Core::$version );

		}

	}

	// Some themes think having an author bio makes it ok to display it... not for CoursePress.
	public static function remove_author_bio_description( $description, $user_id ) {

		$valid_types = self::get_valid_post_types();
		$post_type   = get_post_type();

		if ( in_array( $post_type, $valid_types ) ) {
			return '';
		}

	}

	public static function process_course_ajax() {

		$data      = json_decode( file_get_contents( 'php://input' ) );
		$json_data = array();
		$success   = false;

		if ( empty( $data->action ) ) {
			$json_data['message'] = __( 'Course Update: No action.', CoursePress::TD );
			wp_send_json_error( $json_data );
		}

		$action              = sanitize_text_field( $data->action );
		$json_data['action'] = $action;

		switch ( $action ) {

			// Update Course
			case 'record_module_response':

				$course_id  = (int) $data->course_id;
				$unit_id    = (int) $data->unit_id;
				$module_id  = (int) $data->module_id;
				$student_id = (int) $data->student_id;
				$response   = $data->response;
				$module_type = $data->module_type;

				CoursePress_Model_Student::module_response( $student_id, $course_id, $unit_id, $module_id, $response );

				$data = CoursePress_Helper_Utility::object_to_array( $data );

				if( 'input-quiz' == $module_type ) {

					$quiz_result = CoursePress_Model_Module::get_quiz_results( $student_id, $course_id, $unit_id, $module_id );
					$json_data['quiz_result_screen'] = CoursePress_Model_Module::quiz_result_content( $student_id, $course_id, $unit_id, $module_id, $quiz_result );
					$json_data['results'] = $quiz_result;

				}

				$json_data = array_merge( $json_data, $data );
				$success   = true;

				break;

			case 'calculate_completion':

				$course_id  = (int) $data->course_id;
				$student_id = (int) $data->student_id;

				if ( $student_id > 0 ) {
					$progress = CoursePress_Model_Student::calculate_completion( $student_id, $course_id );
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
			case CoursePress_Model_Discussion::get_post_type_name():

				$course_id      = (int) get_post_meta( $post->ID, 'course_id', true );
				$course_id      = ! empty( $course_id ) ? $course_id : CoursePress_Helper_Utility::the_course( true );

				if( ! empty( $course_id ) ) {
					$course         = get_post( $course_id );
					$discussion_url = trailingslashit( CoursePress_Core::get_slug( 'courses', true ) ) . $course->post_name . '/';
					$permalink      = trailingslashit( $discussion_url . CoursePress_Core::get_slug( 'discussion' ) ) . $post->post_name;
				} else {
					return '';
				}


				break;

			case CoursePress_Model_Notification::get_post_type_name():

				break;

			case CoursePress_Model_Unit::get_post_type_name():
				break;

		}

		return $permalink;

	}

	public static function comment_number( $count, $post_id ) {
		global $wp;

		if ( array_key_exists( 'discussion_name', $wp->query_vars ) ) {
			$comments = wp_count_comments( $post_id );
			$count    = $comments->approved;

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

		$course_id      = get_post_meta( self::$discussion, 'course_id', true );
		$discussion_url = trailingslashit( CoursePress_Core::get_slug( 'courses', true ) ) . get_post_field( 'post_name', $course_id ) . '/';
		$discussion_url = trailingslashit( $discussion_url . CoursePress_Core::get_slug( 'discussion' ) ) . get_post_field( 'post_name', self::$discussion );

		if ( ! empty( $comment_page ) ) {
			$result = $discussion_url . '?cpage=' . $comment_page . '#comments';
		} else {
			$result = $discussion_url . '#comments';
		}

		return $result;

	}

	public static function add_discussion_comment_meta( $comment_id ) {

		if ( ! empty( CoursePress_Model_Discussion::$last_discussion ) ) {
			add_comment_meta( $comment_id, 'context', CoursePress_Model_Discussion::get_post_type_name() );
		}

	}

	public static function update_discussion_comments( $comments, $post_id ) {

		$discussion_type = CoursePress_Model_Discussion::get_post_type_name();
		if ( $discussion_type === get_post_field( 'post_type', $post_id ) ) {

			foreach ( $comments as $comment ) {
				$meta = get_comment_meta( $comment->ID, 'context', true );
				if ( $discussion_type !== $meta ) {
					update_comment_meta( $comment->ID, 'context', $discussion_type );
				}
			}

		}

		return $comments;

	}


	public static function remove_discussions_from_comments( $args ) {

		$discussion_type = CoursePress_Model_Discussion::get_post_type_name();
		if ( ! empty( $discussion_type ) ) {

			$args['meta_query'] = array(
				array(
					'key'     => 'context',
					'value'   => $discussion_type,
					'compare' => '!='
				)
			);

		}

		return $args;
	}


}
