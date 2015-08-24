<?php

class CoursePress_View_Admin_CoursePress {

	private static $slug = 'coursepress';
	private static $title = '';
	private static $menu_title = '';

	private static $admin_pages = array(
		'Course_Edit',
		'Assessment_List',
		'Assessment_Report'
	);

	public static function init() {

		self::$title      = __( 'Courses/CoursePress', CoursePress::TD );
		self::$menu_title = __( 'Courses', CoursePress::TD );

		add_filter( 'coursepress_admin_valid_pages', array( __CLASS__, 'add_valid' ) );
		add_filter( 'coursepress_admin_pages', array( __CLASS__, 'add_page' ) );

		add_filter( 'coursepress_admin_valid_pages', array( __CLASS__, 'add_valid' ) );
		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'render_page' ) );

		// Init CoursePress Admin Views
		foreach ( self::$admin_pages as $page ) {
			$class = 'CoursePress_View_Admin_' . $page;

			if ( method_exists( $class, 'init' ) ) {
				call_user_func( $class . '::init' );
			}
		}

		// Respond to bulk actions in courses list
		//add_action( 'admin_action_coursepress_course_publish', array( __CLASS__, 'publish_course_action' ) );
		//add_action( 'admin_action_coursepress_course_unpublish', array( __CLASS__, 'unpublish_course_action' ) );
		//add_action( 'admin_action_coursepress_course_delete', array( __CLASS__, 'delete_course_action' ) );

	}

	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;

		return $valid_pages;
	}

	public static function add_page( $pages ) {
		$pages[ self::$slug ] = array(
			'title'      => self::$title,
			'menu_title' => self::$menu_title,
		);

		$prefix = defined( 'COURSEPRESS_CPT_PREFIX' ) ? COURSEPRESS_CPT_PREFIX : '';
		$prefix = empty( $prefix ) ? '' : sanitize_text_field( $prefix ) . '_';
		$category = $prefix . CoursePress_Model_Course::get_post_category_name();
		$cpt = $prefix . CoursePress_Model_Course::get_post_type_name();
		$pages[ 'course_categories' ] = array(
			'title'      => __( 'Edit Course Categories', CoursePress::TD ),
			'menu_title' => __( 'Course Categories', CoursePress::TD ),
			'handle' => 'edit-tags.php?taxonomy=' . $category . '&post_type=' . $cpt,
			'callback' => 'none'
		);

		return $pages;
	}


	public static function render_page() {

		$courseListTable = new CoursePress_Helper_Table_CourseList();
		$courseListTable->prepare_items();

		$content = '<div class="coursepress_settings_wrapper">' .
		           '<h3>' . esc_html( CoursePress_Core::$name ) . ' : ' . esc_html( self::$menu_title ) . '</h3>
		            <hr />';

		$bulk_nonce = wp_create_nonce( 'bulk_action_nonce' );
		$content .= '<div class="nonce-holder" data-nonce="' . $bulk_nonce . '"></div>';
		ob_start();
		$courseListTable->display();
		$content .= ob_get_clean();

		$content .= '</div>';

		echo apply_filters( 'coursepress_admin_page_main', $content );


	}

}