<?php
/**
 * Class CoursePress_Admin_Assessments
 *
 * @since 2.0
 * @package CoursePress
 */
class CoursePress_Admin_Assessments extends CoursePress_Admin_Page {

	/**
	 * Assessments page slug.
	 *
	 * @var string
	 */
	protected $slug = 'coursepress_assessments';

	/**
	 * CoursePress_Admin_Notifications constructor.
	 */
	public function __construct() {

		// Initialize parent class.
		parent::__construct();
		add_filter( 'coursepress_admin_localize_array', array( $this, 'change_localize_array' ) );
	}

	/**
	 * JS localized sstrings.
	 *
	 * @since 3.0.0
	 */
	function change_localize_array( $localize_array ) {
		$courseinstructor_id = get_current_user_id();
		$user                = new CoursePress_User( $courseinstructor_id );
		$localize_array['courseinstructor_id'] = $courseinstructor_id;
		$localize_array['instructor_name'] = $user->get_name();
		$localize_array['assessment_labels'] = array(
			'pass' => __( 'Pass', 'cp' ),
			'fail' => __( 'Fail', 'cp' ),
			'add_feedback' => __( 'Add Feedback', 'cp' ),
			'edit_feedback' => __( 'Edit Feedback', 'cp' ),
			'cancel_feedback' => __( 'Cancel', 'cp' ),
			'success' => __( 'Success', 'cp' ),
			'error' => __( 'Unable to save feedback!', 'cp' ),
			'help_tooltip' => __( 'If the submission of this grade makes a student completes the course, an email with certificate will be automatically sent.', 'cp' ),
			'minimum_help' => __( 'You may change this minimum grade from course setting.', 'cp' ),
			'submit_with_feedback' => __( 'Submit grade with feedback', 'cp' ),
			'submit_no_feedback' => __( 'Submit grade without feedback', 'cp' ),
			'edit_with_feedback' => __( 'Edit grade with feedback', 'cp' ),
			'edit_no_feedback' => __( 'Edit grade without feedback', 'cp' ),
		);
		return $localize_array;
	}

	/**
	 * render error
	 *
	 * @since 3.0.0
	 */
	private function render_error() {
		coursepress_render( 'views/admin/error-wrong', array( 'title' => __( 'Assessments' ) ) );
	}

	/**
	 * Get assessments listing page content and set pagination.
	 *
	 * @uses get_current_screen().
	 * @uses get_hidden_columns().
	 * @uses get_column_headers().
	 * @uses coursepress_render().
	 */
	public function get_page() {
		$count = 0;
		$screen = get_current_screen();
		// Set query parameters back.
		$search = isset( $_GET['s'] ) ? $_GET['s'] : '';
		$course_id = empty( $_GET['course_id'] ) ? 0 : $_GET['course_id'];
		$unit_id = empty( $_GET['student_progress'] ) ? 0 : $_GET['student_progress'];
		$graded = empty( $_GET['graded_ungraded'] ) ? 'all' : $_GET['graded_ungraded'];
		$graded = in_array( $graded, array( 'graded', 'ungraded' ) ) ? $graded : 'all';
		$units = empty( $course_id ) ? array() : coursepress_get_course_units( $course_id );
		$course = coursepress_get_course( $course_id );
		if ( isset( $_GET['course_id'] ) && is_wp_error( $course ) ) {
			$this->render_error();
			return;
		}
		// Data for template.
		$args = array(
			'columns' => get_column_headers( $screen ),
			'assessments' => $this->get_assessments( $course_id, $unit_id, $graded, $count ),
			'courses' => coursepress_get_accessible_courses(),
			'units' => $units,
			'list_table' => $this->set_pagination( $count, 'coursepress_assessments_per_page' ),
			'hidden_columns' => get_hidden_columns( $screen ),
			'page' => $this->slug,
			'course_id' => absint( $course_id ),
			'unit_id' => absint( $unit_id ),
			'graded' => $graded,
			'search' => $search,
		);
		// Render templates.
		coursepress_render( 'views/admin/assessments', $args );
		coursepress_render( 'views/admin/footer-text' );
		coursepress_render( 'views/tpl/editor-without-media' );
	}

	/**
	 * Get assessment details page content and set pagination.
	 *
	 * @uses get_current_screen().
	 * @uses get_hidden_columns().
	 * @uses get_column_headers().
	 * @uses coursepress_render().
	 */
	public function get_details_page() {

		$screen = get_current_screen();
		$course_id = empty( $_GET['course_id'] ) ? 0 : $_GET['course_id'];
		$student_id = empty( $_GET['student_id'] ) ? 0 : $_GET['student_id'];
		$display = empty( $_GET['display'] ) ? 'all' : $_GET['display'];
		// Data for template.
		$args = array(
			'assessments' => $this->get_assessment_details( $student_id, $course_id, $display ),
			'columns' => get_column_headers( $screen ),
			'hidden_columns' => get_hidden_columns( $screen ),
			'courses' => coursepress_get_accessible_courses(),
			'page' => $this->slug,
			'course_id' => absint( $course_id ),
			'student_id' => absint( $student_id ),
			'display' => $display,
		);
		// Render templates.
		coursepress_render( 'views/admin/assessments-details', $args );
		coursepress_render( 'views/admin/footer-text' );
		coursepress_render( 'views/tpl/editor-without-media' );
	}

	/**
	 * Get assessments data.
	 *
	 * @param int $course_id Course ID.
	 * @param int $unit_id Unit id.
	 * @param string $graded Graded or ungraded.
	 * @param int $count Total count of the students (pass by ref.).
	 *
	 * @return array
	 */
	public function get_assessments( $course_id, $unit_id, $graded = 'all', &$count = 0 ) {
		// We need course id.
		if ( empty( $course_id ) ) {
			return array();
		}
		$assessments = new CoursePress_Data_Assessments( $course_id );
		return $assessments->get_assessments( $unit_id, $graded, $count );
	}

	/**
	 * Get details of an assessment for detailed view.
	 *
	 * @param int $student_id Student ID.
	 * @param int $course_id Course ID.
	 * @param inti $unit_id Unit ID.
	 * @param string $progress Units.
	 *
	 * @return array
	 */
	public function get_assessment_details( $student_id, $course_id, $progress = 'all' ) {
		// We need course id and student id.
		if ( empty( $course_id ) || empty( $student_id ) ) {
			return array();
		}
		$assessments = new CoursePress_Data_Assessments( $course_id );
		return $assessments->get_assessment_details( $student_id, $progress );
	}

	/**
	 * Custom screen options for assessments listing page.
	 *
	 * @uses get_current_screen().
	 */
	public function screen_options() {
		$screen_id = get_current_screen()->id;
		// Setup columns.
		add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ) );
		add_filter( 'manage_' . $screen_id . '_columns', array( $this, 'get_columns' ) );
		// Assessments per page.
		add_screen_option( 'per_page', array( 'default' => 20, 'option' => 'coursepress_assessments_per_page' ) );
	}

	/**
	 * Get column for the listing page.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'student' => __( 'Student', 'cp' ),
			'last_active' => __( 'Last active', 'cp' ),
			'grade' => __( 'Grade', 'cp' ),
			'modules_progress' => __( 'Modules progress', 'cp' ),
		);
		/**
		 * Trigger to allow custom column values.
		 *
		 * @since 3.0
		 * @param array $columns
		 */
		$columns = apply_filters( 'coursepress_assessments_columns', $columns );
		return $columns;
	}

	/**
	 * Default columns to be hidden on listing page.
	 *
	 * @return array
	 */
	public function hidden_columns() {
		/**
		 * Trigger to modify hidden columns.
		 *
		 * @since 3.0
		 * @param array $hidden_columns.
		 */
		return apply_filters( 'coursepress_assessments_hidden_columns', array() );
	}
}
