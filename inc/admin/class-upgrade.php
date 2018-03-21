<?php
/**
 * Class CoursePress_Upgrade
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Upgrade  extends CoursePress_Admin_Page {

	protected $cp;
	private $status;
	private $count = 0;
	private $courses = array();

	public function __construct( CoursePress $cp ) {
		$this->status = get_option( 'coursepress_upgrade', 'no upgrade required' );
		$this->cp = $cp;
		if ( 'need to be upgraded' !== $this->status ) {
			return;
		}
		add_action( 'init', array( $this, 'count_courses' ), PHP_INT_MAX );
		add_action( 'admin_notices', array( $this, 'upgrade_is_needed_notice' ) );
		add_filter( 'coursepress_admin_menu_screens', array( $this, 'add_admin_submenu' ), 11 );
		add_filter( 'coursepress_admin_localize_array', array( $this, 'i18n' ) );
	}

	/**
	 * Add i18n to JavaScript _coursepress object.
	 *
	 * @since 3.0.0
	 */
	public function i18n( $data ) {
		$data['text']['upgrade'] = array(
			'status' => array(
				'in_progress' => __( 'Upgrading in progress, please wait.', 'cp' ),
				'upgraded' => __( 'Upgraded.', 'cp' ),
			),
		);
		return $data;
	}

	public function add_admin_submenu( $screens ) {
		$menu = $this->add_submenu(
			__( 'Upgrade courses', 'cp' ),
			'coursepress_create_course_cap',
			'coursepress_upgrade',
			'get_upgrade_page'
		);
		array_unshift( $screens, $menu );
		return $screens;
	}

	public function process_page() {
	}

	private function set_courses() {
		if ( empty( $this->courses ) ) {
			$this->courses = coursepress_get_accessible_courses( false );
		}
	}

	public function get_upgrade_page() {
		$this->set_courses();
		$courses_to_upgrade = array();
		foreach ( $this->courses as $course ) {
			if ( 0 < version_compare( 3, $course->coursepress_version ) ) {
				$courses_to_upgrade[] = $course;
			}
		}
		$args = array(
			'count' => $this->count,
			'courses' => $courses_to_upgrade,
			'nonce' => wp_create_nonce( __CLASS__ ),
		);
		coursepress_render( 'views/admin/upgrade', $args );
		coursepress_render( 'views/tpl/common' );
	}

	public function count_courses() {
		$this->set_courses();
		$this->count = 0;
		foreach ( $this->courses as $course ) {
			if ( 0 < version_compare( 3, $course->coursepress_version ) ) {
				$this->count++;
			}
		}
	}

	public function upgrade_is_needed_notice() {
		if ( 1 > $this->count ) {
			return;
		}
		$screen_id = get_current_screen()->id;
		if ( preg_match( '/page_coursepress_upgrade$/', $screen_id ) ) {
			return;
		}

		$class = 'notice notice-error';
		$message = esc_html( sprintf( _n( 'You have %d course to update.', 'You have %d courses to update.', $this->count, 'cp' ), $this->count ) );
		$message .= PHP_EOL.PHP_EOL;
		$message .= sprintf(
			'<a href="%s">%s</a>',
			esc_url( add_query_arg( 'page', 'coursepress_upgrade', admin_url( 'admin.php' ) ) ),
			esc_html__( 'Go to CoursePress Upgrade page.', 'cp' )
		);
		printf( '<div class="%s">', esc_attr( $class ) );
		printf( '<h2>%s</h2>', esc_html__( 'CoursePress Upgrade', 'cp' ) );
		echo wpautop( $message );
		echo '</div>';
	}

	public function upgrade_course_by_id( $course_id ) {
		$meta = get_post_meta( $course_id );
		$result = array(
			'students' => array(
				'total' => 0,
				'added' => 0,
			),
			'course_id' => $course_id,
			'message' => __( 'Course was upgraded successfully.', 'cp' ),
		);
		/**
		 * Instructors
		 */
		$value = get_post_meta( $course_id, 'upgrade_3_instructors', true );
		if ( empty( $value ) || 'upgraded' !== $value ) {

		}
		/**
		 * Facilitators
		 */
		/**
		 * course_enrolled_student_id
		 */
		$course = new CoursePress_Course( $course_id );
		$students = get_post_meta( $course_id, 'course_enrolled_student_id', false );
		if ( ! empty( $students ) && is_array( $students ) ) {
			$result['students']['total'] = count( $students );
			foreach ( $students as $student_id ) {
				$student = new CoursePress_User( $student_id );
				if ( $student->add_course_student( $course, false ) ) {
					$result['students']['added']++;
					$meta_name = sprintf( 'course_%d_progress', $course_id );
					$progress = get_user_meta( $student_id, $meta_name, true );
					if ( ! empty( $progress ) ) {
						$student->add_student_progress( $course_id, $progress );
					}
				}
			}
		}
		/**
		 * Visibility
		 */
		$visible_keys = array(
			'units',
			'pages',
			'modules',
		);
		foreach ( $visible_keys as $key ) {
			$key = 'cp_structure_visible_'.$key;
			$visible[ $key ] = array();
			if (
				isset( $meta[ $key ] )
				&& is_array( $meta[ $key ] )
				&& ! empty( $meta[ $key ] )
			) {
				$visible[ $key ] = maybe_unserialize( $meta[ $key ][0] );
			}
		}

		return $result;
	}
}
