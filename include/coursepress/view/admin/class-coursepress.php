<?php

class CoursePress_View_Admin_CoursePress {

	private static $slug = 'coursepress';
	private static $title = '';
	private static $menu_title = '';

	private static $admin_pages = array(
		'Course_Edit',
		'Assessment_List',
		'Assessment_Report',
	);

	public static function init() {
		self::$title = __( 'Courses/CoursePress', 'CP_TD' );
		self::$menu_title = __( 'Courses', 'CP_TD' );

		add_filter(
			'coursepress_admin_valid_pages',
			array( __CLASS__, 'add_valid' )
		);
		add_filter(
			'coursepress_admin_pages',
			array( __CLASS__, 'add_page' )
		);

		add_filter(
			'coursepress_admin_valid_pages',
			array( __CLASS__, 'add_valid' )
		);
		add_action(
			'coursepress_admin_' . self::$slug,
			array( __CLASS__, 'render_page' )
		);

		// Init CoursePress Admin Views
		foreach ( self::$admin_pages as $page ) {
			$class = 'CoursePress_View_Admin_' . $page;

			if ( method_exists( $class, 'init' ) ) {
				call_user_func( $class . '::init' );
			}
		}

		// For non dynamic editors
		add_filter(
			'tiny_mce_before_init',
			array( __CLASS__, 'init_tiny_mce_listeners' )
		);
	}

	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;

		return $valid_pages;
	}

	public static function add_page( $pages ) {
		$pages[ self::$slug ] = array(
			'title' => self::$title,
			'menu_title' => self::$menu_title,
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			'cap' => apply_filters( 'coursepress_capabilities', 'coursepress_courses_cap' ),
		);
		$category = CoursePress_Data_Course::get_post_category_name();
		$cpt = CoursePress_Data_Course::get_post_type_name();
		$pages['course_categories'] = array(
			'title' => __( 'Edit Course Categories', 'CP_TD' ),
			'menu_title' => __( 'Course Categories', 'CP_TD' ),
			'handle' => 'edit-tags.php?taxonomy=' . $category . '&post_type=' . $cpt,
			'callback' => 'none',
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			'cap' => apply_filters( 'coursepress_capabilities', 'coursepress_course_categories_edit_terms_cap' ),
		);

		return $pages;
	}

	public static function render_page() {
		$list_course = new CoursePress_Helper_Table_CourseList();
		$list_course->prepare_items();

		$content = '<div class="wrap">';
		$content .= CoursePress_Helper_UI::get_admin_page_title(
			self::$menu_title,
			__( 'New Course', 'CP_TD' ),
			admin_url( 'admin.php?page=' . CoursePress_View_Admin_Course_Edit::$slug ),
			CoursePress_Data_Capabilities::can_add_course()
		);

		$bulk_nonce = wp_create_nonce( 'bulk_action_nonce' );
		$content .= '<div class="nonce-holder" data-nonce="' . $bulk_nonce . '"></div>';
		ob_start();
		$list_course->display();
		$content .= ob_get_clean();

		$content .= '</div>';
		$content .= '</div>';

		echo apply_filters( 'coursepress_admin_page_main', $content );
	}

	public static function init_tiny_mce_listeners( $init_array ) {
		$detect_pages = array(
			'coursepress_page_coursepress_course',
			'coursepress-pro_page_coursepress_course',
		);

		$page = get_current_screen()->id;

		if ( in_array( $page, $detect_pages ) ) {
			// $init_array['height'] = '360px';
			$init_array['relative_urls'] = false;
			$init_array['url_converter'] = false;
			$init_array['url_converter_scope'] = false;

			$init_array['setup'] = 'function( ed ) {
				ed.on( \'keyup\', function( args ) {
					CoursePress.Events.trigger(\'editor:keyup\',ed);
				} );
			}';
		}

		return $init_array;
	}
}
