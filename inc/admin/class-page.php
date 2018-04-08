<?php
/**
 * Class CoursePress_Page
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Page extends CoursePress_Utility {
	/**
	 * @var string the main menu slug.
	 */
	protected $slug = 'coursepress';

	/**
	 * @var string Course post type.
	 */
	private $post_type = 'course';

	/**
	 * @var array List of CP screen_id
	 */
	protected $screens = array();

	/**
	 * @var array An array of variables use for localization.
	 */
	var $localize_array = array();

	/**
	 * Statuses
	 */
	private $available_actions = array();
	protected $current_status = null;

	public function __construct() {
		// Check if user can't access coursepress
		if ( ! current_user_can( 'coursepress_dashboard_cap' ) ) {
			$this->is_error = true;
			return;
		}
		/**
		 * set available actions
		 */
		$this->available_actions = array(
			'delete'  => __( 'Delete Permanently', 'cp' ),
			'draft'   => __( 'Draft', 'cp' ),
			'publish' => __( 'Publish', 'cp' ),
			'restore' => __( 'Restore', 'cp' ),
			'trash'   => __( 'Move to Trash', 'cp' ),
		);
		// Setup CP pages
		add_action( 'admin_menu', array( $this, 'set_admin_menus' ) );
		// Set custom pages active.
		add_filter( 'parent_file', array( $this, 'set_category_menu_parent' ) );
		// Set screen option values.
		add_filter( 'set-screen-option', array( $this, 'set_courselist_options' ), 10, 3 );
		// Setup admin assets
		add_action( 'admin_enqueue_scripts', array( $this, 'set_admin_css' ) );
		// Marked coursepress page
		add_filter( 'admin_body_class', array( $this, 'add_coursepress_class' ) );
		/**
		 * allow to upload zip files
		 */
		add_filter( 'upload_mimes', array( $this, 'allow_to_upload_zip_files' ) );
	}

	/**
	 * Iterate CP admin pages.
	 *
	 * @access private
	 */
	public function set_admin_menus() {
		global $submenu, $current_user;
		$screen_id = false;
		/**
		 * can see courses submenu
		 */
		$can_see = CoursePress_Data_Capabilities::can_view_submenu_courses();
		$label = __( 'CoursePress Base', 'cp' );
		if ( $can_see ) {
			// Main CP Page
			$screen_id = add_menu_page( $label, $label, 'coursepress_courses_cap', $this->slug, array( $this, 'get_courselist_page' ), '', 25 );
			// Add screen ID to the list of valid CP pages
			array_unshift( $this->screens, $screen_id );
			// Add preload callback
			add_action( 'load-' . $screen_id, array( $this, 'process_courselist_page' ) );
			/**
			 * Allow to add page to CoursePress screens.
			 *
			 * @since 3.0.0
			 *
			 * @param array $this->screens Array of CoursePress pages hooks.
			 */
			$this->screens = apply_filters( 'coursepress_admin_menu_screens', $this->screens );
			// Set course edit page
			$edit_label = __( 'New Course', 'cp' );
			$this->add_submenu( $edit_label, 'coursepress_create_course_cap', 'coursepress_course', 'get_course_edit_page' );
			// Set categories page
			$cats_label = __( 'Categories', 'cp' );
			$this->add_submenu( $cats_label, 'coursepress_courses_cap', 'edit-tags.php?taxonomy=course_category&post_type=course' );
		} else {
			// Main CP Page
			$screen_id = add_menu_page( $label, $label, 'coursepress_courses_cap', $this->slug, '__return_null', '', 25 );
		}
		/**
		 * can see students submenu
		 */
		$can_see = CoursePress_Data_Capabilities::can_view_submenu_students();
		// Set students page
		if ( $can_see ) {
			$student_label = __( 'Students', 'cp' );
			$student_screen_id = $this->add_submenu( $student_label, 'coursepress_students_cap', 'coursepress_students', 'get_students_page' );
			// Add preload callback
			add_action( 'load-' . $student_screen_id, array( $this, 'process_studentlist_page' ) );
		}
		// Set instructor page
		/**
		 * can see instructors submenu
		 */
		$can_see = CoursePress_Data_Capabilities::can_view_submenu_instructors();
		if ( $can_see ) {
			$instructor_label = __( 'Instructors', 'cp' );
			$this->add_submenu( $instructor_label, 'coursepress_instructors_cap', 'coursepress_instructors', 'get_instructors_page' );
		}
		/**
		 * can see assessment submenu
		 */
		$can_see = CoursePress_Data_Capabilities::can_view_submenu_assessment();
		if ( $can_see ) {
			// Set assessment page
			$assessment_label = __( 'Assessments', 'cp' );
			$assesment_screen_id = $this->add_submenu( $assessment_label, 'coursepress_assessments_cap', 'coursepress_assessments', 'get_assessments_page' );
			array_unshift( $this->screens, $assesment_screen_id );
			// Add preload callback
			add_action( 'load-' . $assesment_screen_id, array( $this, 'process_assessments_page' ) );
		}
		/**
		 * can see discussions submenu
		 */
		$can_see = CoursePress_Data_Capabilities::can_view_submenu_discussions();
		if ( $can_see ) {
			// Set Forum page
			$forum_label = __( 'Forums', 'cp' );
			$this->add_submenu( $forum_label, 'coursepress_discussions_cap', 'coursepress_forum', 'get_forum_page' );
		}
		/**
		 * can see comments submenu
		 */
		$can_see = CoursePress_Data_Capabilities::can_view_submenu_comments();
		if ( $can_see ) {
			// Set Comments page
			$comment_label = __( 'Comments', 'cp' );
			$comments_screen_id = $this->add_submenu( $comment_label, 'coursepress_settings_cap', 'coursepress_comments', 'get_comments_page' );
			// Add preload callback
			add_action( 'load-' . $comments_screen_id, array( $this, 'process_commentlist_page' ) );
		}
		/**
		 * can see reports submenu
		 */
		$can_see = CoursePress_Data_Capabilities::can_view_submenu_reports();
		if ( $can_see ) {
			// Set reports page
			$label = __( 'Reports', 'cp' );
			$screen_id = $this->add_submenu( $label, 'coursepress_reports_cap', 'coursepress_reports', 'get_report_page' );
			array_unshift( $this->screens, $screen_id );
		}
		/**
		 * can see notifications submenu
		 */
		$can_see = CoursePress_Data_Capabilities::can_view_submenu_notifications();
		if ( $can_see ) {
			// Set Notification page
			$notification_label = __( 'Notifications', 'cp' );
			$notifications_screen_id = $this->add_submenu( $notification_label, 'coursepress_notifications_cap', 'coursepress_notifications', 'get_notification_page' );
			// Add preload callback
			add_action( 'load-' . $notifications_screen_id, array( $this, 'process_notifications_list_page' ) );
		}
		/**
		 * can see settings submenu
		 */
		$can_see = CoursePress_Data_Capabilities::can_view_submenu_settings();
		if ( $can_see ) {
			// Set Settings page
			$settings_label = __( 'Settings', 'cp' );
			$this->add_submenu( $settings_label, 'coursepress_settings_cap', 'coursepress_settings', 'get_settings_page' );
		}
		// Rewrite the menu text when user can access course list.
		if ( current_user_can( 'coursepress_courses_cap' ) ) {
			// Change top menu label
			$submenu['coursepress'][0][0] = __( 'Courses', 'cp' );
		}
	}

	/**
	 * Helper method to add `coursepress` submenu.
	 *
	 * @param string $label
	 * @param string $cap
	 * @param string $slug
	 * @param string $callback
	 */
	public function add_submenu( $label = '', $cap, $slug, $callback = '' ) {
		// Check if callback given.
		$callback = empty( $callback ) ? '' : array( $this, $callback );
		$menu = add_submenu_page( $this->slug, 'CoursePress ' . $label, $label, $cap, $slug, $callback );
		// Add to the list of valid CP pages
		array_unshift( $this->screens, $menu );
		return $menu;
	}

	/**
	 * Set custom taxonomy menu active.
	 *
	 * @param string $parent_file Parent slug.
	 *
	 * @return string
	 */
	public function set_category_menu_parent( $parent_file ) {
		global $submenu_file, $current_screen;
		// If current page is course category edit page.
		if ( isset( $current_screen->id ) && $current_screen->id == 'edit-course_category' ) {
			// Set submenu active.
			$submenu_file = 'edit-tags.php?taxonomy=course_category&post_type=course';
			// Set parent menu active.
			$parent_file = $this->slug;
		}
		return $parent_file;
	}

	/**
	 * Helper method to set needed JS and CSS stylesheets needed.
	 *
	 * @access private
	 */
	public function set_admin_css() {
		$coursepress_pagenow = coursepress_is_admin();
		// Global, all pages use stylesheet
		$this->enqueue_style( 'coursepress-admin-global', 'assets/css/admin-global.min.css' );
		if ( ! $coursepress_pagenow ) {
			return; // Do not continue
		}
		/**
		 * The key ID of current CP page loaded.
		 * Both JS and CSS are autoloaded base on this ID.
		 *
		 * Currents keys: {
		 *  `coursepress` - use in courses list page
		 *  `coursepress_students`
		 *  `coursepress_instructors`
		 *  `coursepress_assessments`
		 *  `coursepress_forum`
		 *  `coursepress_comments`
		 *  `coursepress_notifications`
		 *  `coursepress_settings`
		 * }
		 */
		// External CSS
		$this->enqueue_style( 'coursepress-select2', 'assets/external/css/select2.min.css' );
		// Set stylesheets
		$this->enqueue_style( 'fontawesome', 'assets/external/css/font-awesome.min.css' );
		$this->enqueue_style( 'coursepress-admin-common', 'assets/css/admin-common.min.css' );
		$this->enqueue_style( $coursepress_pagenow, 'assets/css/' . $coursepress_pagenow . '.min.css' );
		/**
		 * We'll set JS files at the bottom of the page so that we could add
		 * localize script as page running.
		 */
		add_action( 'admin_footer', array( $this, 'set_admin_scripts' ) );
	}

	private function enqueue_style( $id, $src ) {
		global $CoursePress;
		wp_enqueue_style( $id, $CoursePress->plugin_url . $src, false, $CoursePress->version );
	}

	public function set_admin_scripts() {
		global $CoursePress;
		$coursepress_pagenow = coursepress_is_admin();
		if ( ! $coursepress_pagenow ) {
			return; // Do not continue
		}
		$plugin_url = $CoursePress->plugin_url;
		$this->localize_array = wp_parse_args( $this->localize_array, array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'_wpnonce' => wp_create_nonce( 'coursepress_nonce' ),
			'cookie' => array(
				'hash' => COOKIEHASH,
				'path' => COOKIEPATH,
			),
			'coursepress_page' => add_query_arg( 'page', 'coursepress', admin_url() ),
			'pagenow' => add_query_arg( 'page', $coursepress_pagenow, admin_url( 'admin.php' ) ),
			'plugin_url' => $CoursePress->plugin_url,
			/**
			 * WordPress
			 */
			'wp' => array(
				'date_format' => get_option( 'date_format', 'Y-m-d' ),
				'start_of_week' => get_option( 'start_of_week', 0 ),
				'time_format' => get_option( 'time_format', 'H:i' ),
				'WPLANG' => get_option( 'WPLANG', 'en' ),
			),
			// Common use texts
			'text' => array(
				'untitled' => __( 'Untitled', 'cp' ),
				'ok' => __( 'Ok', 'cp' ),
				'close' => __( 'Close', 'cp' ),
				'cancel' => __( 'Cancel', 'cp' ),
				'error' => __( 'Error', 'cp' ),
				'warning' => __( 'Warning', 'cp' ),
				'info' => __( 'Info', 'cp' ),
				'success' => __( 'Success', 'cp' ),
				'media' => array(
					'select_image' => __( 'Select Image', 'cp' ),
					'select_feature_image' => __( 'Select Feature Image', 'cp' ),
					'select_video' => __( 'Select Video', 'cp' ),
				),
				'server_error' => __( 'An unexpected error occur while processing. Please try again.', 'cp' ),
				'invalid_file_type' => __( 'Invalid file type!', 'cp' ),
				'delete_course' => __( 'Deleting this course will also delete all units, modules, steps and any other data associated to this course. Are you sure you want to continue?', 'cp' ),
				'delete_courses' => __( 'Deleting selected courses will also delete all units, modules, steps and any other data associated to those courses. Are you sure you want to continue?', 'cp' ),
				'deleting_course' => __( 'Deleting course... please wait', 'cp' ),
				'deleting_courses' => __( 'Deleting courses... please wait', 'cp' ),
				'importing_courses' => __( 'Importing courses... please wait', 'cp' ),
				'importing_failed' => __( 'Import failed. Please check the file and try again.', 'cp' ),
				'duplicate_confirm' => __( 'Are you sure you want to create a duplicate copy of this course?', 'cp' ),
				'noname_module' => __( 'You have unnamed module(s)!', 'cp' ),
				'nosteps' => __( 'You need to create at least a single step!', 'cp' ),
				'all_students' => __( 'Students from All Courses', 'cp' ),
				'student_search' => __( 'Enter username, first name and last name, or email', 'cp' ),
				'unit' => array(
					'no_title' => __( 'One of the active unit has no title!', 'cp' ),
					'no_feature_image' => __( 'One of the active unit has feature image enabled but no image set!', 'cp' ),
					'no_content' => __( 'One of the active unit enabled the use of unit description but no description set!', 'cp' ),
					'no_modules' => __( 'One of the active unit has no modules!', 'cp' ),
					'no_steps' => __( 'One of the active unit contains no steps!', 'cp' ),
				),
				'export' => array(
					'no_items' => __( 'Please select at least one course to export.', 'cp' ),
				),
				'step' => array(
					'answer_a' => __( 'Answer A', 'cp' ),
					'answer_b' => __( 'Answer B', 'cp' ),
					'answer_c' => __( 'Answer C', 'cp' ),
				),
				'confirm' => array(
					'student' => array(
						'withdraw' => __( 'Please confirm that you want to remove the student from this course.', 'cp' ),
					),
					'steps' => array(
						'question_delete' => __( 'Are you sure to delete this question?', 'cp' ),
					),
					'invite' => array(
						'remove' => __( 'Please confirm that you want to remove this invite from this course.', 'cp' ),
					)
				),
				'course' => array(
					'students' => array(
						'no_items' => __( 'Please select at least one student to withdraw.', 'cp' ),
						'confirm' => __( 'Are you sure to withdraw students?', 'cp' ),
					),
				),
				'select_module' => __( 'Select a module', 'cp' ),
				'units_menu_help_overlay' => array(
					'title'   => __( 'Create Your First Unit', 'cp' ),
					'content' => __( 'Welcome! This wizard will help you set up your course content. First up, you can click Add Unit to create new units. We have created one for you!', 'cp' ),
				),
				'unit_title_help_overlay' => array(
					'title'   => __( 'Type Unit Title', 'cp' ),
					'content' => __( 'Great start, you now have a unit. Now give it a name above.', 'cp' ),
				),
				'unit_steps_help_overlay' => array(
					'title'   => __( 'Add Step to Unit', 'cp' ),
					'content' => __( 'Doing great, lets set up the first step in your unit. You can have as few or as many steps as you like, and you can change their order later on.', 'cp' ),
				),
			),
			'is_paginated' => isset( $_GET['paged'] ) ? 1 : 0,
			'editor_visual' => __( 'Visual', 'cp' ),
			'editor_text' => _x( 'Text', 'Name for the Text editor tab (formerly HTML)', 'cp' ),
		) );
		/**
		 * External scripts: select2
		 */
		$this->enqueue_script( 'coursepress-select2', 'assets/external/js/select2.min.js', array( 'jquery' ), '4.0.3' );
		/**
		 * Add min if not debug!
		 */
		$min = defined( 'WP_DEBUG' ) && WP_DEBUG? '':'.min';
		/**
		 * General admin js
		 */
		$src = 'assets/js/admin-general'.$min.'.js';
		$this->enqueue_script( 'coursepress-admin-general', $src, array( 'jquery', 'backbone', 'underscore', 'jquery-ui-autocomplete' ) );
		/**
		 * page related JS
		 */
		$src = 'assets/js/'.$coursepress_pagenow.$min.'.js';
		$this->enqueue_script( $coursepress_pagenow, $src ); // Change to .min
		// Set local vars
		$localize_array = apply_filters( 'coursepress_admin_localize_array', $this->localize_array );
		/**
		 * get extensions
		 */
		$localize_array['extensions'] = $this->get_extensions();
		wp_localize_script( 'coursepress-admin-general', '_coursepress', $localize_array );
	}

	/**
	 * Enqueue script wrapper
	 *
	 * @since 3.0.0
	 *
	 * @param string $handle Name of the script. Should be unique.
	 * @param string $src Path of the script.
	 * @param array $deps An array of registered script handles this script depends on. Default value: array()
	 * @param string $ver String specifying script version number, if empty it will be CP version. Default value: false
	 */
	public function enqueue_script( $handle, $src, $deps = array(), $version = false ) {
		global $CoursePress;
		if ( empty( $version ) ) {
			$version = $CoursePress->version;
		}
		$src = sprintf( '%s%s', $CoursePress->plugin_url, $src );
		wp_enqueue_script( $handle, $src, $deps, $version, true );
	}

	public function add_coursepress_class( $class ) {
		if ( coursepress_is_admin() ) {
			$class .= ' coursepress';
		}
		return $class;
	}

	public function courselist_columns() {
		$columns = array(
			'category' => __( 'Categories', 'cp' ),
			'units' => __( 'Units', 'cp' ),
			'students' => __( 'Students', 'cp' ),
			'start_date' => __( 'Start Date', 'cp' ),
			'end_date' => __( 'End Date', 'cp' ),
			'enrollment_start' => __( 'Enrollment Start', 'cp' ),
			'enrollment_end' => __( 'Enrollment End', 'cp' ),
			'certified' => __( 'Certified', 'cp' ),
		);
		return $columns;
	}

	/**
	 * Columns to be hidden by default.
	 *
	 * @return array
	 */
	public function hidden_columns() {
		return array( 'category', 'start_date', 'end_date', 'enrollment_start', 'enrollment_end' );
	}

	/**
	 * Custom screen options for course listing page.
	 */
	public function process_courselist_page() {
		$screen_id = get_current_screen()->id;
		add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ) );
		add_filter( 'manage_' . $screen_id . '_columns', array( $this, 'courselist_columns' ) );
		// Courses per page.
		add_screen_option( 'per_page', array( 'default' => 20, 'option' => 'coursepress_course_per_page' ) );
	}

	/**
	 * Set custom screen options for the students listing page.
	 */
	public function process_studentlist_page() {
		global $CoursePress;
		$students = $CoursePress->get_class( 'CoursePress_Admin_Students' );
		if ( $students ) {
			return $students->screen_options();
		}
	}

	/**
	 * Set custom screen options for the comments listing page.
	 */
	public function process_commentlist_page() {
		global $CoursePress;
		$comments = $CoursePress->get_class( 'CoursePress_Admin_Comments' );
		if ( $comments ) {
			return $comments->screen_options();
		}
	}

	/**
	 * Set custom screen options for the listing page.
	 */
	public function process_notifications_list_page() {
		global $CoursePress;
		$notifications = $CoursePress->get_class( 'CoursePress_Admin_Notifications' );
		if ( $notifications ) {
			return $notifications->screen_options();
		}
	}

	/**
	 * Process assesment listing page screen.
	 */
	public function process_assessments_page() {
		global $CoursePress;
		$assessments = $CoursePress->get_class( 'CoursePress_Admin_Assessments' );
		if ( $assessments ) {
			return $assessments->screen_options();
		}
	}

	/**
	 * Set/save custom screen options value.
	 *
	 * @param bool|int $status
	 * @param string   $option
	 * @param int      $value
	 *
	 * @return mixed
	 */
	public function set_courselist_options( $status, $option, $value ) {
		$options = array(
			'coursepress_course_per_page',
			'coursepress_students_per_page',
			'coursepress_assessments_per_page',
			'coursepress_notifications_per_page',
			'coursepress_comments_per_page',
		);
		// Return value for our custom option.
		// For other options, return default.
		if ( in_array( $option, $options ) ) {
			return $value;
		}
		return $status;
	}

	public function get_courselist_page() {
		global $CoursePress_User, $CoursePress_Core;
		$count = 0;
		$screen = get_current_screen();
		$page = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : 'coursepress';
		$search = isset( $_GET['s'] ) ? $_GET['s'] : '';
		$current_status = $this->get_status();
		$post_type = $CoursePress_Core->__get( 'course_post_type' );
		$args = array(
			'columns' => get_column_headers( $screen ),
			'hidden_columns' => get_hidden_columns( $screen ),
			'courses' => $CoursePress_User->get_accessible_courses( $current_status, false, $count ),
			'pagination' => $this->set_courses_pagination( $count ),
			'course_edit_link' => add_query_arg( 'page', 'coursepress_course', admin_url( 'admin.php' ) ),
			'page' => $page,
			'statuses' => coursepress_get_post_statuses( $this->post_type, $current_status, $this->slug ),
			'search' => $search,
			'bulk_actions' => $this->get_bulk_actions(),
			'placeholder_text' => '',
			'current_status' => $current_status,
			'course_post_type_object' => get_post_type_object( $post_type ),
		);
		coursepress_render( 'views/admin/courselist', $args );
		coursepress_render( 'views/admin/footer-text' );
		coursepress_render( 'views/tpl/common' );
	}

	/**
	 * Get bulk actions for courses list
	 *
	 * @since 3.0
	 *
	 * @return array $actions Bulk actions for courses.
	 */
	public function get_bulk_actions() {
		$status = $this->get_status();
		if ( 'trash' === $status ) {
			$actions = array( 'restore', 'delete' );
		} else {
			$actions = array( 'publish', 'draft', 'trash' );
		}
		$a = array();
		foreach ( $actions as $action ) {
			$a[ $action ] = $this->available_actions[ $action ];
		}
		return $a;
	}

	/**
	 * Set pagination for courses listing page.
	 *
	 * We are using WP_Listing_Table class to set pagination.
	 *
	 * @param int $count Total courses.
	 *
	 * @return object
	 */
	public function set_courses_pagination( $count ) {
		// Get no. of courses per page.
		$per_page = get_user_meta( get_current_user_id(), 'coursepress_course_per_page', true );
		$per_page = empty( $per_page ) ? coursepress_get_option( 'posts_per_page', 20 )  : $per_page;
		// Using WP_List table for pagination.
		$listing = new WP_List_Table();
		$args = array(
			'total_items' => $count,
			'per_page' => $per_page,
		);
		$listing->set_pagination_args( $args );
		return $listing;
	}

	public function get_course_edit_page() {
		global $CoursePress;
		// We need the image editor here, enqueue it!!!
		wp_enqueue_media();
		// Include datepicker
		wp_enqueue_script( 'jquery-ui-datepicker' );
		// Datepicker UI
		$this->enqueue_style( 'datepicker-ui', 'assets/external/css/jquery-ui.min.css' );
		// Include color picker
		wp_enqueue_script( 'iris' );
		// Sorter
		wp_enqueue_script( 'jquery-ui-sortable' );
		// Include resize sensor
		wp_enqueue_script( 'resize-sensor', $CoursePress->plugin_url . '/assets/external/js/resize-sensor.js' );
		$course_id = filter_input( INPUT_GET, 'cid', FILTER_VALIDATE_INT );
		/**
		 * check is course
		 */
		if ( ! empty( $course_id ) ) {
			$is_course = coursepress_is_course( $course_id );
			if ( false === $is_course ) {
				$args = array(
					'title' => __( 'Course does not exist', 'cp' ),
				);
				coursepress_render( 'views/admin/error-wrong', $args );
				return;
			}
		}
		// Check capabilities before showing edit/create form.
		$can_access = empty( $course_id )? CoursePress_Data_Capabilities::can_create_course() : CoursePress_Data_Capabilities::can_update_course( $course_id );
		if ( ! $can_access ) {
			$args = array(
				'title' => __( 'Access Denied', 'cp' ),
				'message' => __( 'Sorry, you are not allowed to do this.', 'cp' ),
			);
			coursepress_render( 'views/admin/error-wrong', $args );
			return;
		}
		// If it's a new course, create a draft course
		if ( empty( $course_id ) ) {
			$course = coursepress_get_course( get_default_post_to_edit( $this->post_type, true ) );
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
			'settings' => coursepress_get_setting(),
		);
		$this->localize_array = wp_parse_args( $local_vars, $this->localize_array );
		$menu_list = array(
			'course-type' => __( 'Type of Course', 'cp' ),
			'course-settings' => __( 'Course Settings', 'cp' ),
			'course-units' => __( 'Units', 'cp' ),
			'course-completion' => __( 'Course Completion', 'cp' ),
			'course-students' => __( 'Students', 'cp' ),
		);

		if ( ! CoursePress_Data_Capabilities::can_view_units( $course_id )  ) {
			unset( $menu_list['course-units'] );
		}

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
			'courses' => coursepress_get_courses( array( 'post_status' => 'publish', 'posts_per_page' => -1 ) ),
		);
		coursepress_render( 'views/tpl/common' );
		coursepress_render( 'views/admin/course-edit', $args );
		coursepress_render( 'views/admin/footer-text' );
		// Load templates
		coursepress_render( 'views/tpl/common' );
		$sample_courses = array(
			'introduction-to-upfront' => array(
				'title' => __( 'Introduction to Upfront', 'cp' ),
				'file' => 'wpmudev-thewordpressexperts.coursepress.2017-07-03.introduction-to-upfront-4.json',
			),
			'wordpress-multisite-masterclass' => array(
				'title' => __( 'WordPress Multisite Master Class', 'cp' ),
				'file' => 'wpmudev-thewordpressexperts.coursepress.2017-07-03.wordpress-multisite-masterclass-2.json',
			),
		);
		coursepress_render( 'views/tpl/course-type', array( 'course_id' => $course_id, 'sample_courses' => $sample_courses ) );
		$invited_instructors = (array) get_post_meta( $course_id, 'instructor_invites', true );
		$invited_facilitators = (array) get_post_meta( $course_id, 'facilitator_invites', true );
		$this->localize_array['invited_instructors'] = $invited_instructors;
		$this->localize_array['invited_facilitators'] = $invited_facilitators;
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
		$this->localize_array['unit_help_dismissed'] = (bool) get_user_meta( get_current_user_id(), 'unit_help_dismissed', true );
		coursepress_render( 'views/tpl/course-completion', array( 'completion_pages' => $completion_pages ) );
		$steps = array(
			'text' => __( 'Text', 'cp' ),
			'image' => __( 'Image', 'cp' ),
			'video' => __( 'Video', 'cp' ),
			'audio' => __( 'Audio', 'cp' ),
			'download' => __( 'Download', 'cp' ),
			'zipped' => __( 'Zip', 'cp' ),
			'input-upload' => __( 'File Upload', 'cp' ),
			'input-quiz' => __( 'Quiz', 'cp' ),
			'input-written' => __( 'Written', 'cp' ),
			'discussion' => __( 'Discussion', 'cp' ),
		);
		$this->localize_array['steps'] = $steps;
		$file_types = array(
			'image' => __( 'Image', 'cp' ),
			'pdf' => __( 'PDF', 'cp' ),
			'zip' => __( 'Zip', 'cp' ),
			'txt' => __( 'Text', 'cp' ),
		);
		$question_types = array(
			'multiple' => __( 'Multiple Choice', 'cp' ),
			'single' => __( 'Single Choice', 'cp' ),
			'select' => __( 'Selectable', 'cp' ),
		);
		$this->localize_array['questions'] = $question_types;
		$unit_args = array(
			'steps' => $steps,
			'course' => $course,
			'can_create_unit' => CoursePress_Data_Capabilities::can_create_unit( $course_id ),
		);
		coursepress_render( 'views/tpl/course-units', $unit_args );
		coursepress_render( 'views/tpl/steps-template', array( 'file_types' => $file_types, 'questions' => $question_types ) );
		$paged = $this->get_pagenum();
		$all_student_count = $course->count_students();
		$certified_student_ids = $course->get_certified_student_ids();
		$certified_student_count = count( $certified_student_ids );
		$invited_students = $course->get_invited_students();
		$show_certified_students = isset( $_REQUEST['certified'] ) ? $_REQUEST['certified'] : 'all';
		$per_page = get_option( 'posts_per_page', 20 );
		$student_query_args = array(
			'number' => $per_page,
			'offset' => $per_page * ($paged - 1),
			'paged'  => $paged,
		);
		/**
		 * Students
		 */
		$students = array();
		if ( $show_certified_students == 'yes' ) {
			$students = $course->get_certified_students( $student_query_args );
			$total_students = $certified_student_count;
		} elseif ( $show_certified_students == 'no' ) {
			$students = $course->get_non_certified_students( $student_query_args );
			$total_students = $all_student_count - $certified_student_count;
		} else {
			$students = $course->get_students( $student_query_args );
			$total_students = $all_student_count;
		}
		/**
		 * row actions
		 */
		foreach ( $students as $student_id => $data ) {
			$students[ $student_id ]->row_actions = array(
				'id' => sprintf( __( 'ID: %d', 'cp' ), $student_id ),
			);
		}
		/**
		 * can see students submenu
		 */
		$can_see = CoursePress_Data_Capabilities::can_view_submenu_students();
		if ( $can_see ) {
			$link = add_query_arg(
				array(
					'page' => 'coursepress_students',
					'view' => 'profile',
				),
				admin_url( 'admin.php' )
			);
			foreach ( $students as $student_id => $data ) {
				$cp_profile = add_query_arg( 'student_id', $student_id, $link );
				$students[ $student_id ]->coursepress_student_link = $cp_profile;
				$students[ $student_id ]->row_actions['cp_profile'] = sprintf(
					'<a href="%s">%s</a>',
					$cp_profile,
					__( 'Student Profile', 'cp' )
				);
				$can_edit = current_user_can( 'edit_user', $student_id );
				if ( $can_edit ) {
					$students[ $student_id ]->row_actions['wp_profile'] = sprintf(
						'<a href="%s">%s</a>',
						get_edit_user_link( $student_id ),
						__( 'Edit User Profile', 'cp' )
					);
				}
			}
		}
		/**
		 * can see assessment submenu
		 */
		$can_see = CoursePress_Data_Capabilities::can_view_submenu_assessment();
		if ( $can_see ) {
			$link = add_query_arg(
				array(
					'course_id' => $course_id,
					'page' => 'coursepress_assessments',
					'tab' => 'details',
				),
				admin_url( 'admin.php' )
			);
			foreach ( $students as $student_id => $data ) {
				$cp_profile = add_query_arg( 'student_id', $student_id, $link );
				$students[ $student_id ]->row_actions['assessments'] = sprintf(
					'<a href="%s">%s</a>',
					$cp_profile,
					__( 'Assessments', 'cp' )
				);
			}
		}
		/**
		 * Prepare arguments to template
		 */
		$args = array(
			'course_id'          => $course_id,
			'total_students'     => $total_students,
			'students'           => $students,
			'redirect'           => remove_query_arg( 'dummy' ),
			'pagination'         => $this->set_pagination( $total_students, 'coursepress_students', ceil( $total_students / $per_page ) ),
			'invited_students'   => $invited_students,
			'certified_students' => $certified_student_ids,
			'statuses'           => $this->get_course_certification_links( $all_student_count, $certified_student_count ),
			'show'               => $show_certified_students,
			'all_student_count'  => $all_student_count,
		);
		$this->localize_array['invited_students'] = $invited_students;
		coursepress_render( 'views/tpl/course-students', $args );
	}

	private function get_course_certification_links( $all_student_count, $certified_student_count ) {
		$format = '<li><a class="%1$s" href="%2$s">%3$s</a></li>';
		$url = remove_query_arg( array( 'certified', 'paged' ) );
		$certification_statuses = array(
			'all' => sprintf( esc_html__( 'All (%s)', 'cp' ), sprintf( '<span>%d</span>', $all_student_count ) ),
			'yes' => sprintf( esc_html__( 'Certified (%s)', 'cp' ), sprintf( '<span>%d</span>', $certified_student_count ) ),
			'no'  => sprintf( esc_html__( 'Not Certified (%s)', 'cp' ), sprintf( '<span>%d</span>', $all_student_count - $certified_student_count ) ),
		);
		$links = array();
		$selected = isset( $_REQUEST['certified'] ) ? $_REQUEST['certified'] : 'all';
		foreach ( $certification_statuses as $status_id => $status_text ) {
			$classes = array(
				sprintf( 'status-%s', $status_id ),
			);
			if ( $status_id == $selected ) {
				$classes[] = 'current';
			}
			$status_url = add_query_arg( 'certified', $status_id, $url );
			$links[] = sprintf( $format, esc_attr( implode( ' ', $classes ) ), esc_url( $status_url ), $status_text );
		}
		return $links;
	}

	public function get_students_page() {
		$students = new CoursePress_Admin_Students();
		$students->get_page();
	}

	public function get_instructors_page() {
		$instructors = new CoursePress_Admin_Instructors();
		$instructors->get_page();
	}

	public function get_forum_page() {
		$this->lib3();
		$forums = new CoursePress_Admin_Forums();
		$forums->get_page();
	}

	public function get_comments_page() {
		$students = new CoursePress_Admin_Comments();
		$students->get_page();
	}

	/**
	 * Assessments listing page cotent.
	 */
	public function get_assessments_page() {
		global $CoursePress;
		$assessments = $CoursePress->get_class( 'CoursePress_Admin_Assessments' );
		if ( is_wp_error( $assessments ) ) {
			echo $assessments->get_error_message();
		} elseif ( $assessments ) {
			// If it is details page
			if ( isset( $_GET['tab'] ) && 'details' === $_GET['tab'] ) {
				return $assessments->get_details_page();
			} else {
				return $assessments->get_page();
			}
		}
	}

	public function get_notification_page() {
		global $CoursePress;
		$students = $CoursePress->get_class( 'CoursePress_Admin_Notifications' );
		if ( $students ) {
			$students->get_page();
		}
	}

	public function get_settings_page() {
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
		$extensions = $this->get_extensions();
		$this->localize_array['extensions'] = $extensions;
		coursepress_render( 'views/admin/settings' );
		// Add TPL
		coursepress_render( 'views/tpl/common' );
		coursepress_render( 'views/tpl/settings-general' );
		coursepress_render( 'views/tpl/settings-slugs' );
		$emails = $CoursePress->get_class( 'CoursePress_Email' );
		$sections = $emails->get_settings_sections();
		if ( empty( $this->localize_array['settings']['email'] ) ) {
			$this->localize_array['settings']['email'] = $emails->get_defaults();
		} else {
			$defaults = $emails->get_defaults();
			foreach ( $defaults as $key => $conf ) {
				if ( isset( $this->localize_array['settings']['email'][ $key ] ) ) {
					continue;
				}
				$this->localize_array['settings']['email'][ $key ] = $conf;
			}
		}
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

	/**
	 * Get admin Reports page
	 *
	 * @since 3.0.0
	 */
	public function get_report_page() {
		global $CoursePress;
		$instance = $CoursePress->get_class( 'CoursePress_Admin_Reports' );
		if ( $instance ) {
			$instance->get_page();
		}
	}

	/**
	 * Load lib3 assets files
	 *
	 * @since 3.0.0
	 */
	private function lib3() {
		lib3()->ui->add( 'core' );
		lib3()->ui->add( 'html' );
	}

	/**
	 * get extensions
	 */
	private function get_extensions() {
		/**
		 * Fire to get all available extensions.
		 *
		 * @since 3.0
		 * @param array $extensions
		 */
		$extensions = apply_filters( 'coursepress_extensions', array() );
		if ( ! $extensions ) {
			return array();
		}
		return $extensions;
	}

	/**
	 * Check current status and sanitise it
	 *
	 * @since 3.0.0
	 *
	 * @return current status of courses
	 */
	protected function get_status() {
		if ( null != $this->current_status ) {
			return $this->current_status;
		}
		$this->current_status = $status = 'any';
		if ( isset( $_REQUEST['status'] ) ) {
			$status = $_REQUEST['status'];
		}
		$allowed_statuses = array(
			'any',
			'publish',
			'draft',
			'trash',
		);
		if ( in_array( $status, $allowed_statuses ) ) {
			$this->current_status = $status;
		}
		return $this->current_status;
	}

	/**
	 * Allow to upload zip files
	 *
	 * @since 3.0.0
	 */
	public function allow_to_upload_zip_files( $mimes = array() ) {
		$mimes['zip'] = 'application/zip';
		return $mimes;
	}
}
