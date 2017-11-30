<?php
/**
 * Class CoursePress_Comments
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Comments extends CoursePress_Admin_Page {
	/**
	 * @var string the main menu slug.
	 */
	protected $slug = 'coursepress_comments';

	public function __construct() {
		$this->list = new CoursePress_Admin_Table_Comments();
		add_filter( 'set-screen-option', array( $this, 'set_options' ), 10, 3 );
	}

	function columns() {
		$columns = array(
			'author' => __( 'Author', 'cp' ),
			'comment' => __( 'Comment', 'cp' ),
			'in_response_to' => __( 'In response to', 'cp' ),
			'added' => __( 'Added', 'cp' ),
		);

		return $columns;
	}

	/**
	 * Columns to be hidden by default.
	 *
	 * @return array
	 */
	function hidden_columns() {
		return array();
	}

	/**
	 * Custom screen options for course listing page.
	 */
	function process_page() {

		$screen_id = get_current_screen()->id;

		add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ) );
		add_filter( 'manage_' . $screen_id . '_columns', array( $this, 'columns' ) );

		// Courses per page.
		add_screen_option( 'per_page', array( 'default' => 20, 'option' => 'coursepress_comments_per_page' ) );
	}

	function get_page() {
		global $CoursePress_User;

		$screen = get_current_screen();
		$page = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : 'coursepress';
		$search = isset( $_GET['s'] ) ? $_GET['s'] : '';
		$statuses = array();
		$get_status = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'any';

		$this->list->prepare_items();
		$count = $this->list->get_count();

		$args = array(
			'columns' => $this->columns(),
			'course_edit_link' => add_query_arg( 'page', 'coursepress_course', admin_url( 'admin.php' ) ),
			'courses' => coursepress_get_accessible_courses(),
			'hidden_columns' => $this->hidden_columns(),
			'items' => $this->list->items,
			'page' => $page,
			'pagination' => $this->set_pagination( $count, 'coursepress_comments_per_page' ),
			'search' => $search,
			'statuses' => $statuses,
		);
		coursepress_render( 'views/admin/comments', $args );
		coursepress_render( 'views/admin/footer-text' );
	}

	function get_edit_page() {
	    global $CoursePress;

		// We need the image editor here, enqueue it!!!
		wp_enqueue_media();
		// Include datepicker
		wp_enqueue_script( 'jquery-ui-datepicker' );
		// Include color picker
		wp_enqueue_script( 'iris' );

		$course_id = filter_input( INPUT_GET, 'cid', FILTER_VALIDATE_INT );

		// If it's a new course, create a draft course
		if ( empty( $course_id ) ) {
			$course = coursepress_get_course( get_default_post_to_edit( 'course', true ) );
			$course->post_title = '';
		} else {
			$course = coursepress_get_course( $course_id );
		}

		// Set course category
		$category = array_values( $course->get_category() );
		$course->__set( 'course_category', $category );

		// Add $course object to localize array for quick editing
		$local_vars = array(
			'course' => $course, // Use in most steps
			'categories' => coursepress_get_categories(),
		);
		$this->localize_array = wp_parse_args( $local_vars, $this->localize_array );

		$menu_list = array(
			'course-type' => __( 'Type of Course', 'cp' ),
			'course-settings' => __( 'Course Settings', 'cp' ),
			'course-units' => __( 'Units', 'cp' ),
			'course-completion' => __( 'Course Completion', 'cp' ),
			'course-students' => __( 'Students', 'cp' ),
		);

		/**
		 * Allow population of additional menu list.
		 *
		 * @since 3.0
		 * @param array $menu_list
		 */
		$menu_list = apply_filters( 'coursepress_course_edit_menus', $menu_list );

		$args = array(
			'course_id' => $course_id,
			'page_title' => $course_id > 0 ? get_the_title( $course_id ) : __( 'New Course', 'cp' ),
			'menu_list' => $menu_list,
		);

		// Data for course settings form.
		$settings_data = array(
			'course_id' => $course_id,
			'post_content' => $course->post_content,
			'post_excerpt' => htmlspecialchars_decode( $course->post_excerpt ),
		);

		coursepress_render( 'views/admin/course-edit', $args );
		coursepress_render( 'views/admin/footer-text' );

		// Load templates
		coursepress_render( 'views/tpl/common' );
		coursepress_render( 'views/tpl/course-type', array( 'course_id' => $course_id ) );
		coursepress_render( 'views/tpl/course-completion' );
		coursepress_render( 'views/tpl/course-settings', $settings_data );

		$certClass = $CoursePress->get_class( 'CoursePress_Certificate' );
		$tokens = array(
			'COURSE_NAME',
			'COURSE_OVERVIEW',
			'COURSE_UNIT_LIST',
			'DOWNLOAD_CERTIFICATE_LINK',
			'DOWNLOAD_CERTIFICATE_BUTTON',
			'STUDENT_WORKBOOK',
		);
		$format = sprintf( '<p>%1$s</p> <p>%2$s</p>', __( 'These codes will be replaced with actual data:', 'cp' ), '<b>%s</b>' );
		$page_tokens = sprintf( $format, implode( ', ', $tokens ) );
		$cert_tokens = sprintf( $format, implode( ', ', array_keys( $certClass->get_tokens() ) ) );
		$completion_pages = array(
			'tokens' => $page_tokens,
			'pre_completion' => array(
				'title' => __( 'Pre Completion Page', 'cp' ),
				'description' => __( 'The page content to appear after an student completed the course and is awaiting instructor\'s final grade.', 'cp' ),
			),
			'course_completion' => array(
				'title' => __( 'Successful Completion Page', 'cp' ),
				'description' => __( 'The content to use when an student successfully completed the course.', 'cp' ),
			),
			'course_failed' => array(
				'title' => __( 'Failure Notice', 'cp' ),
				'description' => __( 'The content to use when an student failed to pass the course.', 'cp' ),
			),
			'cert_tokens' => $cert_tokens,
		);
		$this->localize_array['completion_pages'] = $completion_pages;

		coursepress_render( 'views/tpl/course-completion', array( 'completion_pages' => $completion_pages ) );
		coursepress_render( 'views/tpl/course-units' );
	}

	function get_students_page() {
		$students = new CoursePress_Admin_Students();
		$students->get_page();
	}

	function get_instructors_page() {
		coursepress_render( 'views/admin/instructors' );
	}

	function get_forum_page() {
		coursepress_render( 'views/admin/forum' );
	}

	function get_comments_page() {
		$students = new CoursePress_Admin_Comments();
		$students->get_page();
	}

	function get_assessments_page() {
		coursepress_render( 'views/admin/assessments' );
	}

	function get_notification_page() {
		coursepress_render( 'views/admin/notifications' );
	}

	function get_settings_page() {
	    global $CoursePress;

	    // Include wp.media
		wp_enqueue_media();

	    // Include color picker
		wp_enqueue_script( 'iris' );

		// Include jquery-iframe
		wp_enqueue_script( 'jquery-iframe', $CoursePress->plugin_url . '/assets/external/js/jquery.iframe-transport.js' );

		$this->lib3();
		// Add global setting to localize array
		$this->localize_array['settings'] = coursepress_get_setting( true );
		$this->localize_array['messages'] = array(
		    'no_mp_woo' => sprintf( __( '%s and %s cannot be activated simultaneously!', 'cp' ), 'MarketPress', 'WooCommerce' ),
		);

		/**
		 * Fire to get all available extensions.
		 *
		 * @since 3.0
		 * @param array $extensions
		 */
		$extensions = apply_filters( 'coursepress_extensions', array() );

		if ( ! $extensions ) {
			$extensions = array();
		}
		$this->localize_array['extensions'] = $extensions;

		coursepress_render( 'views/admin/settings' );

		// Add TPL
		coursepress_render( 'views/tpl/common' );
		coursepress_render( 'views/tpl/settings-general' );
		coursepress_render( 'views/tpl/settings-slugs' );

		$emails = $CoursePress->get_class( 'CoursePress_Email' );
		$sections = $emails->get_settings_sections();
		$this->localize_array['settings']['email'] = $emails->get_defaults();
		$this->localize_array['email_sections'] = $sections;

		$email_vars = array(
			'sections' => $sections,
			'config' => array(),
		);

		coursepress_render( 'views/tpl/settings-emails', $email_vars );
		coursepress_render( 'views/tpl/settings-capabilities' );
		coursepress_render( 'views/tpl/settings-certificate' );
		coursepress_render( 'views/tpl/settings-shortcodes' );
		coursepress_render( 'views/tpl/settings-extensions', array( 'extensions' => $extensions ) );
		coursepress_render( 'views/extensions/marketpress' );
		coursepress_render( 'views/extensions/woocommerce' );
		coursepress_render( 'views/tpl/settings-import-export' );
	}
}
