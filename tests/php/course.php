<?php
/**
 * Test upgrade cycle
 **/
$data_dir = dirname( __DIR__ ) . '/data/';

require_once $data_dir . 'course.php';

class CoursePress_Admin_Courses extends WP_UnitTestCase {
	static $table = false;
	function setUp() {
		parent::setUp();
		set_current_screen( 'edit-page' );
		$GLOBALS['hook_suffix'] = '';
		self::$table = _get_list_table( 'WP_Posts_List_Table' );
	}

	/**
	 * @before
	 **/
	public static function bootstrap() {
		if ( defined( 'WP_COURSEPRESS_DIR' ) ) {
			return;
		}

		$bootstrap = WP_COURSEPRESS_DIR . 'tests/bootstrap.php';

		require $bootstrap;
	}

	public static function require_coursepress( $version = '' ) {
		require_once WP_COURSEPRESS_DIR . $version . 'coursepress.php';
	}

	public static function generate_courses() {
		self::require_coursepress();

		$course_ids = array();
		// Create 10 course
		for( $i=1; $i <= 10; $i++ ) {
			$course = CoursePressData::course_data( array( 'post_title' => 'Course ' . $i ) );
			$course_ids[] = self::factory()->post->create_and_get( $course );
		}

		return $course_ids;
	}

	public static function _display( $args = array(), $expected_ids = array() ) {
		$_REQUEST['paged']   = $args['paged'];
		$GLOBALS['per_page'] = $args['posts_per_page'];

		$args = array_merge( $args, array(
			'post_type' => 'course',
		));

		$courses = new WP_Query( $args );

		ob_start();
		self::$table->display_rows( $courses->posts );
		$output = ob_get_clean();

		return $output;
	}

	public static function _test_course_list() {
		$matches = array();
		$duplicate_links = array();
		$course_ids = self::generate_courses();

		$output = self::_display( array( 'paged' => 1, 'posts_per_page' => 20 ), $course_ids );

		preg_match_all( '|<tr[^>]*>|', $output, $matches );
		preg_match_all( '|duplicate-course-link|', $output, $duplicate_links );

		$this->assertTrue( count( $matches ) == 10 );
		$this->assertTrue( count( $duplicate_links ) == 10 );
	}

	public function test_course_list() {
		add_action( 'admin_init', array( __CLASS__, '_test_course_list' ) );
	}
}