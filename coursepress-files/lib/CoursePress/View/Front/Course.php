<?php

class CoursePress_View_Front_Course {

	public static function init() {

		add_action( 'wp', array( __CLASS__, 'load_plugin_templates' ) );

		add_action( 'pre_get_posts', array( __CLASS__, 'remove_canonical' ) );
		add_action( 'parse_request', array( __CLASS__, 'parse_request' ) );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'coursepress_front_css' ) );
		add_filter( 'get_the_author_description', array( __CLASS__, 'remove_author_bio_description' ), 10, 2 );

	}

	public static function load_plugin_templates() {
		global $wp_query;

		if( array_key_exists( 'coursename', $wp_query->query_vars ) && array_key_exists( 'unitname', $wp_query->query_vars ) ) {

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
			if ( locate_template( array( 'single-course.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
				//just output the content
			} else {
			//
			//	//if ( get_post_type( $wpdb->last_result[ 0 ]->post_id ) == 'course' ) {
			//	if ( get_post_type() == 'course' ) {
			//		$prepend_content = $this->get_template_details( $this->plugin_dir . 'includes/templates/single-course-before-details.php' );
			//		$content		 = do_shortcode( $prepend_content . $content );
			//	} else {
			//		return $content;
			//	}

				//$content = CoursePress_Template_Course::course_enroll_box();
				//$content .= CoursePress_Template_Course::course_about();
				//$content .= CoursePress_Template_Course::course_instructors();
				//$content .= CoursePress_Template_Course::course_structure();
				$content = CoursePress_Template_Course::test_shortcodes();

			}
		}

		error_log( $content );

		return $content;
	}

	public static function render_course_unit( $post_ID ) {
		// Set the post so we can get it in Templates
		CoursePress_Helper_Utility::set_the_post( $post_ID );

		// Post can be retrieved with CoursePress_Helper_Utility::the_post();

		if ( $theme_file = locate_template( array( 'single-unit.php' ) ) ) {
		} else {
			//wp_enqueue_style( 'front_course_single', $this->plugin_url . 'css/front_course_single.css', array(), $this->version );
			if ( locate_template( array( 'single-unit.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
				//just output the content
			} else {

				$content = CoursePress_Template_Unit::unit_with_modules();

			}
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

	public static function render_course_archive() {

		$category = CoursePress_Helper_Utility::the_course_category();
		$category_template_file = locate_template( array( 'archive-course-' . $category . '.php' ) );

		if( ! empty( $category_template_file ) ) {

		} elseif ( $theme_file = locate_template( array( 'archive-course.php' ) ) ) {

		} else {

			if ( locate_template( array( 'archive-course.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
				//just output the content
			} else {

				$content = CoursePress_Template_Course::course_archive();

			}

		}

		//if ( $category_template_file != '' ) {
		//	do_shortcode( '[courses_loop]' );
		//	require_once( $category_template_file );
		//	exit;
		//} else {
		//	$theme_file = locate_template( array( 'archive-course.php' ) );
		//
		//	if ( $theme_file != '' ) {
		//		do_shortcode( '[courses_loop]' );
		//		require_once( $theme_file );
		//		exit;
		//	} else {
		//		$theme_file = locate_template( array( 'archive.php' ) );
		//		if ( $theme_file != '' ) {
		//			do_shortcode( '[courses_loop]' );
		//			require_once( $theme_file );
		//			exit;
		//		}
		//	}
		//}
		return $content;

	}

	public static function render_course_discussion() {
		return 'Discussion....';
	}

	public static function render_course_discussion_archive() {
		return 'Discussion Archive....';
	}

	public static function render_course_grades_archive() {
		return 'Grades....';
	}

	public static function render_course_workbook() {
		return 'Workbook....';
	}

	public static function render_course_notifications_archive() {
		return 'Notifications....';
	}

	public static function remove_canonical( $wp_query ) {

		global $wp_query;
		if ( is_admin() || empty( $wp_query ) ) {
			return;
		}

		$page	 = get_query_var( 'pagename' );
		$course	 = get_query_var( 'course' );
		$coursename = get_query_var( 'coursename' );

		if ( $page == 'dashboard' || ! empty( $course ) || ! empty( $coursename ) ) {
			remove_action( 'template_redirect', 'redirect_canonical' );
		}
	}


	public static function parse_request( &$wp ) {
		global $wp_query;
		$context = '';

		CoursePress_Helper_Utility::$is_singular = false;

		$is_categoty_page = false;

		// Do nothing if its a normal course page
		if( array_key_exists( 'course', $wp->query_vars ) ) {

			$course_id = CoursePress_Model_Course::by_name( $wp->query_vars['course'], true );
			CoursePress_Helper_Utility::set_the_course( $course_id );
			CoursePress_Helper_Utility::set_the_course_subpage( '' );

			// Warning: A course should not have the same post_name as the category slug, it will be skipped
			$is_categoty_page = $wp->query_vars['course'] === CoursePress_Core::get_slug( 'category' );

			if( ! $is_categoty_page ) {

				CoursePress_Helper_Utility::$is_singular = true;

				$args = array(
					'slug'        => 'course_' . $course_id,
					'title'		 => get_the_title( $course_id ),
					//'show_title'  => false,
					'content'     => self::render_course_main(),
					'type'        => CoursePress_Model_Course::get_post_type_name( true ),
				);

				$pg = new CoursePress_Model_VirtualPage( $args );
				return;

			}

		}


		// Course Category
		if ( array_key_exists( 'course_category', $wp->query_vars ) || $is_categoty_page ) {

			$course_id = CoursePress_Model_Course::by_name( $wp->query_vars['course'], true );
			CoursePress_Helper_Utility::set_the_course( $course_id );
			CoursePress_Helper_Utility::set_the_course_subpage( '' );

			$category = $is_categoty_page ? '' : $wp->query_vars['course_category'];
			CoursePress_Helper_Utility::set_the_course_category( $category );

			//'course_category'
			$args = array(
				'slug'        => 'course_archive',
				//'title'		 => get_the_title( $post_parent ),
				'show_title'  => false,
				'content'     => self::render_course_archive(),
				'type'        => CoursePress_Model_Course::get_post_type_name( true ) . '_archive',
			);

			$pg = new CoursePress_Model_VirtualPage( $args );
			return;
		}

		// Unit Archive and other unit pages
		if( array_key_exists( 'coursename', $wp->query_vars ) && ! array_key_exists( 'unitname', $wp->query_vars ) ) {
			$post_parent = CoursePress_Model_Course::by_name( $wp->query_vars['coursename'], true );
			CoursePress_Helper_Utility::set_the_course( $post_parent );


			// Discussion
			if( array_key_exists( 'discussion_name', $wp->query_vars ) ) {
				CoursePress_Helper_Utility::set_the_course_subpage( 'discussions' );

				$args = array(
					'slug'        => 'discussion_' . $post_parent,
					'title'		 => get_the_title( $post_parent ),
					//'show_title'  => false,
					'content'     => self::render_course_discussion(),
					'type'        => 'course_discussion',
				);

				$pg = new CoursePress_Model_VirtualPage( $args );

				return;
			}

			// Discussion Archive
			if( array_key_exists( 'discussion_archive', $wp->query_vars ) ) {
				CoursePress_Helper_Utility::set_the_course_subpage( 'discussions' );

				$args = array(
					'slug'        => 'discussion_archive_' . $post_parent,
					'title'		 => get_the_title( $post_parent ),
					//'show_title'  => false,
					'content'     => self::render_course_discussion_archive(),
					'type'        => 'course_discussion_archive',
				);

				$pg = new CoursePress_Model_VirtualPage( $args );

				return;
			}

			// Grades
			if( array_key_exists( 'grades_archive', $wp->query_vars ) ) {
				CoursePress_Helper_Utility::set_the_course_subpage( 'grades' );

				$args = array(
					'slug'        => 'grades_archive_' . $post_parent,
					'title'		 => get_the_title( $post_parent ),
					//'show_title'  => false,
					'content'     => self::render_course_grades_archive(),
					'type'        => 'course_grades_archive',
				);

				$pg = new CoursePress_Model_VirtualPage( $args );

				return;
			}

			// Workbook
			if( array_key_exists( 'workbook', $wp->query_vars ) ) {
				CoursePress_Helper_Utility::set_the_course_subpage( 'workbook' );

				$args = array(
					'slug'        => 'workbook_' . $post_parent,
					'title'		 => get_the_title( $post_parent ),
					//'show_title'  => false,
					'content'     => self::render_course_workbook(),
					'type'        => 'course_workbook',
				);

				$pg = new CoursePress_Model_VirtualPage( $args );

				return;
			}

			// Notifications
			if( array_key_exists( 'notifications_archive', $wp->query_vars ) ) {
				CoursePress_Helper_Utility::set_the_course_subpage( 'notifications' );

				$args = array(
					'slug'        => 'notifications_archive_' . $post_parent,
					'title'		 => get_the_title( $post_parent ),
					//'show_title'  => false,
					'content'     => self::render_course_notifications_archive(),
					'type'        => 'course_notifications_archive',
				);

				$pg = new CoursePress_Model_VirtualPage( $args );

				return;
			}


			// If nothing else got rendered, then its most likely the Unit Archive
			// Units Archive
			CoursePress_Helper_Utility::set_the_course_subpage( 'units' );

			$args = array(
				'slug'        => 'unit_archive_' . $post_parent,
				'title'		 => get_the_title( $post_parent ),
				//'show_title'  => false,
				'content'     => self::render_course_unit_archive(),
				'type'        => CoursePress_Model_Unit::get_post_type_name( true ) . '_archive',
			);

			$pg = new CoursePress_Model_VirtualPage( $args );
			return;
		}

		// Unit With Modules
		if( array_key_exists( 'coursename', $wp->query_vars ) && array_key_exists( 'unitname', $wp->query_vars ) ) {
			CoursePress_Helper_Utility::$is_singular = true;
			$post_parent = CoursePress_Model_Course::by_name( $wp->query_vars['coursename'], true );
			CoursePress_Helper_Utility::set_the_course( $post_parent );
			CoursePress_Helper_Utility::set_the_course_subpage( '' );

			// Unit page
			$unit_page = array_key_exists( 'paged', $wp->query_vars ) ? (int) $wp->query_vars['paged'] : 1;
			CoursePress_Helper_Utility::set_the_post_page( $unit_page );

			$post_ID = CoursePress_Model_Unit::by_name( $wp->query_vars['unitname'], true, $post_parent );
			// If not by post name, perhaps its the actual ID
			$post_ID = empty( $post_ID ) ? (int) $wp->query_vars['unitname'] : $post_ID;

			$args = array(
				'slug'        => $wp->query_vars['unitname'],
				'title'		 => get_the_title( $post_parent ),
				//'show_title'  => false,
				'content'     => self::render_course_unit( $post_ID ),
				'type'        => CoursePress_Model_Unit::get_post_type_name( true ),
				'post_parent' => $post_parent,
				'ID'          => $post_ID // Will load the real post
			);

			$pg = new CoursePress_Model_VirtualPage( $args );
			return;
		}


	}

	public static function get_valid_post_types() {
		return array(
			CoursePress_Model_Course::get_post_type_name( true ),
			CoursePress_Model_Course::get_post_type_name( true ) . '_archive',
			CoursePress_Model_Unit::get_post_type_name( true ) ,
			CoursePress_Model_Unit::get_post_type_name( true ) . '_archive',
		);
	}


	public static function coursepress_front_css() {
		global $wp_query;

		$valid_types = self::get_valid_post_types();

		$post_type = get_post_type();

		// Only enqueue when needed
		if( in_array( $post_type, $valid_types ) ) {

			$style = CoursePress_Core::$plugin_lib_url . 'styles/coursepress_front.css';
			wp_enqueue_style( 'coursepress_general', $style, array(), CoursePress_Core::$version );


		}

	}

	// Some themes think having an author bio makes it ok to display it... not for CoursePress.
	public static function remove_author_bio_description( $description, $user_id ) {

		$valid_types = self::get_valid_post_types();
		$post_type = get_post_type();

		if( in_array( $post_type, $valid_types ) ) {
			return '';
		}

	}

}