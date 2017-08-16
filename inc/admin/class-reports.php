<?php
/**
 * Class CoursePress_Reports
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Reports extends CoursePress_Admin_Page {
	/**
	 * @var string the main menu slug.
	 */
	protected $slug = 'coursepress_reports';

	public function __construct() {
		$this->list = new CoursePress_Admin_Table_Reports();
		add_filter( 'set-screen-option', array( $this, 'set_options' ), 10, 3 );
	}

	function columns() {
		$columns = array(
			'ID' => __( 'ID', 'cp' ),
			'student' => __( 'Student Name', 'cp' ),
			'responses' => __( 'Responses', 'cp' ),
			'average' => __( 'Average', 'cp' ),
			'download' => __( 'Download', 'cp' ),
			'view' => __( 'View', 'cp' ),
		);

		return $columns;
	}

	/**
	 * Columns to be hidden by default.
	 *
	 * @return array
	 */
	function hidden_columns() {
		return array();
	}

	/**
	 * Custom screen options for course listing page.
	 */
	function process_page() {
		$screen_id = get_current_screen()->id;
		add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ) );
		add_filter( 'manage_' . $screen_id . '_columns', array( $this, 'columns' ) );
		// Courses per page.
		add_screen_option( 'per_page', array( 'default' => 20, 'option' => 'coursepress_reports_per_page' ) );
	}

	public function get_page() {
		$course_id = filter_input( INPUT_GET, 'course_id', FILTER_VALIDATE_INT );
		$mode = filter_input( INPUT_GET, 'mode' );
		$nonce = filter_input( INPUT_GET, '_wpnonce' );
		if ( $course_id && 'html' == $mode && wp_verify_nonce( $nonce, 'coursepress_preview_report' ) ) {
			$this->course_id = $course_id;
			$this->get_page_preview();
		} else {
			$this->get_page_list();
		}
		coursepress_render( 'views/admin/footer-text' );
	}

	private function get_page_list() {
		global $CoursePress_User;
		$this->list->prepare_items();
		$count = $this->list->get_count();
		$args = array(
			'columns' => $this->columns(),
			'courses' => coursepress_get_accessible_courses(),
			'hidden_columns' => $this->hidden_columns(),
			'items' => $this->list->items,
			'page' => $this->slug,
			'pagination' => $this->set_courses_pagination( $count ),
		);
		coursepress_render( 'views/admin/reports', $args );
	}

	private function get_page_preview() {
		$student_id = filter_input( INPUT_GET, 'student_id', FILTER_VALIDATE_INT );
		$course = coursepress_get_course( $this->course_id );
		$args = array(
			'page' => $this->slug,
			'colors' => $this->get_colors(),
			'course' => $course,
			'units' => $course->get_units( false ),
			'student' => coursepress_get_user( $student_id ),
		);
		coursepress_render( 'views/admin/report-preview', $args );
	}

	private function get_colors() {
		$colors = apply_filters(
			'coursepress_report_colors',
			array(
				'title_bg' => '#0091cd',
				'title' => '#ffffff',
				'unit_bg' => '#f5f5f5',
				'unit' => '#000000',
				'no_items' => '#858585',
				'item_bg' => '#ffffff',
				'item' => '#000000',
				'item_line' => '#f5f5f5',
				'footer_bg' => '#0091cd',
				'footer' => '#ffffff',
				'row_even_bg' => '#fdfdf0',
				'row_odd_bg' => '#fff',
			)
		);
		return $colors;
	}
}
