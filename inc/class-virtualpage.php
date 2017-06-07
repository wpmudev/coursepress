<?php
/**
 * Class CoursePress_VirtualPage
 *
 * @since 2.0
 * @package CoursePress
 */
class CoursePress_VirtualPage extends CoursePress_Utility {
	protected $cp_post_types = array( 'course', 'unit', 'module' );
	protected $post_type;
	protected $template;

	protected $pagenow;

	public function __construct( $template_type ) {
		add_filter( 'template_include', array( $this, $template_type ) );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_stylesheets' ) );

		$template_parts = array(
			'course/title',
			'course/submenu',
		);

		foreach ( $template_parts as $part ) {
			add_action( 'get_template_part_' . $part, array( $this, 'get_course_template_part' ), 10, 2 );
		}
	}

	function body_class( $class ) {
		array_push( $class, 'coursepress' );

		return $class;
	}

	function load_stylesheets() {
		global $CoursePress;

		$version = $CoursePress->version;
		$plugin_url = $CoursePress->plugin_url;
		$pagenow = $this->__get( 'pagenow' );

		$deps = array( 'jquery', 'backbone', 'underscore' );

		wp_enqueue_style( 'coursepress', $plugin_url . 'assets/css/front.min.css', array( 'dashicons' ), $version );

		// Load script at the footer
		if ( 'course-overview' == $pagenow ) {
			wp_enqueue_script( 'circle-progress', $plugin_url . 'assets/external/js/circle-progress.min.js', false, false, true );
		}

		wp_enqueue_script( 'coursepress', $plugin_url . 'assets/js/coursepress-front.min.js', $deps, $version, true );

		$local_vars = array(
			'_wpnonce' => wp_create_nonce( 'coursepress-nonce' ),
		);
		wp_localize_script( 'coursepress', '_coursepress', $local_vars );
	}

	function get_course_template_part( $part ) {
		coursepress_render( 'views/templates/' . $part );
	}

	function has_template( $template ) {
		$template = locate_template( $template, false, false );

		if ( $template ) {
			return $template;
		}

		return false;
	}

	function setUpContent( $content ) {
		if ( is_main_query() ) {
			$content = call_user_func( array( $this, $this->__get( 'template' ) ) );
		}

		return $content;
	}

	function setCourseArchive() {
		return '[course_list_box show_title="no"]';
	}

	function setCourseOverview() {
		global $CoursePress, $CoursePress_Course, $post;

		$this->__set( 'pagenow', 'course-overview' );
		$CoursePress_Course = coursepress_get_course( $post->ID );
		$template = $this->has_template( 'single-course.php' );

		if ( ! $template ) {
			$template = $CoursePress->plugin_path . '/views/templates/single-course.php';
		}

		return $template;
	}

	function setUpUnitsOverview() {
		global $CoursePress;

		// Check if the current theme have `archive-units.php`
		$template = $this->has_template( 'archive-units.php' );

		if ( ! $template ) {
			$template = $CoursePress->plugin_path . '/views/templates/archive-units.php';
		}

		return $template;
	}

	function setUnitView() {
		return 'UNIT VIEW';
	}

	function setModuleView() {
		return 'MODULE VIEW';
	}

	function setStepView() {
		return 'STEP VIEW';
	}

	function setUpModuleView() {}

}