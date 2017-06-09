<?php
/**
 * Class CoursePress_Data_Core
 *
 * @since 3.0
 * @package CoursePress
 */
final class CoursePress_Core extends CoursePress_Utility {
	var $courses = array();
	var $users = array();
	var $students = array();
	var $instructors = array();

	protected $course_post_type = 'course';
	protected $unit_post_type = 'unit';
	protected $step_post_type = 'module';

	public function __construct() {
		// Register CP post types
		add_action( 'init', array( $this, 'register_post_types' ) );
		// Register CP query vars
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		// Add CP rewrite rules
		add_filter( 'rewrite_rules_array', array( $this, 'add_rewrite_rules' ) );
		// Handle CP pages
		add_action( 'parse_query', array( $this, 'parse_query' ) );
	}

	function register_post_types() {
		// Course
		$course_slug = coursepress_get_setting( 'slugs/course', 'courses' );
		register_post_type( $this->course_post_type, array(
			'public' => true,
			'label' => __( 'CoursePress', 'cp' ),
			'show_ui' => false,
			'show_in_nav_menu' => false,
			'has_archive' => true,
			'can_export' => false, // CP have it's own export mechanism
			'delete_with_user' => false,
			'rewrite' => array(
				'slug' => $course_slug,
			)
		) );

		$category_slug = coursepress_get_setting( 'slugs/category', 'course_category' );

		register_taxonomy( 'course_category',
			array( $this->course_post_type ),
			array(
				'public' => true,
				'rewrite' => array(
					'slug' => $category_slug,
				)
			)
		);

		// Unit
		register_post_type( $this->unit_post_type, array(
			'public' => false,
			'can_export' => false,
			'label' => 'Units', // debugging only,
			'query_var' => false,
			'publicly_queryable' => false,
		) );

		// Module
		register_post_type( $this->step_post_type, array(
			'public' => false,
			'show_ui' => false,
			'can_export' => false,
			'label' => 'Modules', // dbugging only
			'query_var' => false,
			'publicly_queryable'=> false,
		) );
	}

	function add_query_vars( $vars ) {
		$vars[] = 'coursepress';
		$vars[] = 'unit';
		$vars[] = 'coursename';
		$vars[] = 'module';
		$vars[] = 'step';
		$vars[] = 'instructor';

		return $vars;
	}

	function add_rewrite_rules( $rules ) {
		$course_slug = coursepress_get_setting( 'slugs/course', 'courses' );
		$unit_slug = coursepress_get_setting( 'slugs/units', 'units' );
		$workbook_slug = coursepress_get_setting( 'slugs/workbook', 'workbook' );
		$notification_slug = coursepress_get_setting( 'slugs/notifications', 'notifications' );
		$discussion_slug = coursepress_get_setting( 'slugs/discussions', 'discussions' );
		$grade_slug = coursepress_get_setting( 'slugs/grades', 'grades' );
		$instructor_slug = coursepress_get_setting( 'slugs/instructor_profile', 'instructor' );
		$base = '^' . $course_slug . '/([^/]*)/';

		$new_rules = array(
			// Unit
			$base . $unit_slug . '/([^/]*)/?$' => 'index.php?coursename=$matches[1]&unit=$matches[2]&coursepress=unit',
			$base . $unit_slug . '/([^/]*)/([^/]*)/?$' => 'index.php?coursename=$matches[1]&unit=$matches[2]&module=$matches[3]&coursepress=module',
			$base . $unit_slug . '/([^/]*)/([^/]*)/([^/]*)/?$' => 'index.php?coursename=$matches[1]&unit=$matches[2]&module=$matches[3]&step=$matches[4]&coursepress=step',
			// Units archive
			$base . $unit_slug . '/?' => 'index.php?coursename=$matches[1]&coursepress=unit-archive',
			// Workbook
			$base . $workbook_slug . '/?' => 'index.php?coursename=$matches[1]&coursepress=workbook',
			// Notifications
			$base . $notification_slug . '/?' => 'index.php?coursename=$matches[1]&coursepress=notifications',
			// Forum|Discussions
			$base . $discussion_slug . '/?' => 'index.php?coursename=$matches[1]&coursepress=forum',
			// Grades
			$base . $grade_slug . '/?' => 'index.php?coursename=$matches[1]&coursepress=grades',
			// Course Instructor Profile
			'^' . $instructor_slug . '/([^/]*)/?' => 'index.php?coursepress=instructor&instructor=$matches[1]',
		);


		return array_merge( $new_rules, $rules );
	}

	private function reset_wp( $wp, $course_name ) {
		$wp->is_home = false;
		$wp->is_singular = $wp->is_single = true;
		$wp->query_vars = wp_parse_args( array(
			'page' => '',
			'course' => $course_name,
			'post_type' => 'course',
			'name' => $course_name,
		), $wp->query_vars );
	}

	function parse_query( $wp ) {
		global $CoursePress_VirtualPage;

		$post_type = $wp->get( 'post_type' );
		$course_name = $wp->get( 'coursename' );
		$type = $wp->get( 'coursepress' );
		$cp = array();

		if ( ! empty( $course_name ) ) {
			$cp['course'] = $course_name;
			$cp['type']   = $type;

			if ( in_array( $type, array( 'unit', 'module', 'step' ) ) ) {
				$cp['unit'] = $wp->get( 'unit' );

				if ( ( $module = $wp->get( 'module' ) ) )
					$cp['module'] = $module;
				if ( ( $step = $wp->get( 'step' ) ) )
					$cp['step'] = $step;
			}

			$this->reset_wp( $wp, $course_name );
		} elseif ( ! empty( $type ) ) {
			// Use for CP specific pages
			$cp['course'] = false;
			$cp['type'] = $type;
			$this->reset_wp( $wp, false );
		} elseif ( $this->course_post_type == $post_type && $wp->is_main_query() ) {
			$course_name = $wp->get( 'name' );
			$cp['course'] = $course_name;
			$cp['type'] = 'single-course';
			$this->reset_wp( $wp, $course_name );
		}

		if ( ! empty( $cp ) ) {
			$CoursePress_VirtualPage = new CoursePress_VirtualPage( $cp );
		}
	}
}