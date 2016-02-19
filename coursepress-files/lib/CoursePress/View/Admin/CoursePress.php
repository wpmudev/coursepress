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

		// For non dynamic editors
		add_filter( 'tiny_mce_before_init', array( __CLASS__, 'init_tiny_mce_listeners' ) );

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

		$category                   = CoursePress_Data_Course::get_post_category_name();
		$cpt                        = CoursePress_Data_Course::get_post_type_name();
		$pages['course_categories'] = array(
			'title'      => __( 'Edit Course Categories', CoursePress::TD ),
			'menu_title' => __( 'Course Categories', CoursePress::TD ),
			'handle'     => 'edit-tags.php?taxonomy=' . $category . '&post_type=' . $cpt,
			'callback'   => 'none',
		);

		return $pages;
	}


	public static function render_page() {

		$courseListTable = new CoursePress_Helper_Table_CourseList();
		$courseListTable->prepare_items();

		$url = admin_url( 'admin.php?page=' . CoursePress_View_Admin_Course_Edit::$slug );

		$content = '<div class="coursepress_settings_wrapper wrap">' .
			'<h3>' . esc_html( CoursePress::$name ) . ' : ' . esc_html( self::$menu_title ) . '
			<a class="add-new-h2" href="' . esc_url_raw( $url ) . '">' . esc_html__( 'New Course', CoursePress::TD ) . '</a>
			</h3>
			<hr />';

		$bulk_nonce = wp_create_nonce( 'bulk_action_nonce' );
		$content .= '<div class="nonce-holder" data-nonce="' . $bulk_nonce . '"></div>';
		ob_start();
		$courseListTable->display();
		$content .= ob_get_clean();

		$content .= '</div>';

		echo apply_filters( 'coursepress_admin_page_main', $content );

	}

	public static function init_tiny_mce_listeners( $initArray ) {

		$detect_pages = array(
			'coursepress_page_coursepress_course',
			'coursepress-pro_page_coursepress_course',
		);

		$page = get_current_screen()->id;

		if ( in_array( $page, $detect_pages ) ) {
			// $initArray['height']              = '360px';
			$initArray['relative_urls']       = false;
			$initArray['url_converter']       = false;
			$initArray['url_converter_scope'] = false;

			$initArray['setup'] = 'function( ed ) {
						ed.on( \'keyup\', function( args ) {
							CoursePress.Events.trigger(\'editor:keyup\',ed);
						} );
				}';
		}

		return $initArray;
	}
}
