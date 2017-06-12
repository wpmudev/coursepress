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
	}

	function register_post_types() {
		// Course
		$course_slug = coursepress_get_setting( 'slugs/course', 'courses' );
		register_post_type( $this->course_post_type, array(
			'public' => true,
			'label' => __( 'All Courses', 'cp' ),
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
			'supports' => array( 'thumbnail' ),
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
		$vars[] = 'topic';

		return $vars;
	}

	function add_rewrite_rules( $rules ) {
		$course_slug = coursepress_get_setting( 'slugs/course', 'courses' );
		$unit_slug = coursepress_get_setting( 'slugs/units', 'units' );
		$workbook_slug = coursepress_get_setting( 'slugs/workbook', 'workbook' );
		$notification_slug = coursepress_get_setting( 'slugs/notifications', 'notifications' );
		$discussion_slug = coursepress_get_setting( 'slugs/discussions', 'discussions' );
		$new_discussion_slug = coursepress_get_setting( 'slugs/discussions_new', 'new-discussion' );
		$grade_slug = coursepress_get_setting( 'slugs/grades', 'grades' );
		$instructor_slug = coursepress_get_setting( 'slugs/instructor_profile', 'instructor' );
		$student_dashboard = coursepress_get_setting( 'slugs/student_dashboard', 'courses-dashboard' );
		$student_settings = coursepress_get_setting( 'slugs/student_settings', 'student-settings' );
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
			// New forum/discussion
			$base . $discussion_slug . '/' . $new_discussion_slug . '/?' => 'index.php?coursename=$matches[1]&coursepress=forum&topic=new',
           //Forum|Discussions
			$base . $discussion_slug . '/?' => 'index.php?coursename=$matches[1]&coursepress=forum',
			// Grades
			$base . $grade_slug . '/?' => 'index.php?coursename=$matches[1]&coursepress=grades',
			// Course Instructor Profile
			'^' . $instructor_slug . '/([^/]*)/?' => 'index.php?coursepress=instructor&instructor=$matches[1]',
			// Student Dashboard
			'^' . $student_dashboard . '/?' => 'index.php?coursepress=student-dashboard',
			// Student Settings
			'^' . $student_settings . '/?' => 'index.php?coursepress=student-settings',
		);

		return array_merge( $new_rules, $rules );
	}
}