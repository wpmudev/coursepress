<?php
/**
 * Courses Table
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
if ( ! class_exists( 'WP_Posts_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php';
}
class CoursePress_Admin_Table_Courses extends WP_Posts_List_Table {
	protected $student_id = 0;
	static $student_progress = null;

	public function __construct( $student_id = 0 ) {
		if ( ! empty( $student_id ) ) {
			$this->student_id = $student_id;
		}

		$post_format = CoursePress_Data_Course::get_format();
		parent::__construct( array(
			'singular' => $post_format['post_args']['labels']['singular_name'],
			'plural' => $post_format['post_args']['labels']['name'],
			'ajax' => false,
		) );

		// Course caps
		CoursePress_Data_Capabilities::course_capabilities();
	}

	public function prepare_items() {
		global $wp_query;
		$old_query = $wp_query;

		$post_status = 'any';
		$per_page = get_post( 'per_page' );

		$args = array(
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'post_status' => $post_status,
		);

		$wp_query = new WP_Query( $args );

		$total_items = $wp_query->found_posts;
		$this->items = $wp_query->posts;

		$this->set_pagination_args(
			array(
			'total_items' => $total_items,
			'per_page'	=> $per_page,
			'total_pages' => $wp_query->max_num_pages,
			)
		);
	}

	public function get_bulk_actions() {
		$actions = array();

		return $actions;
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Title', 'cp' ),
		);

		if ( ! empty( $this->student_id ) ) {
			$columns = array_merge(
				$columns,
				array(
					'date_enrolled' => __( 'Date Enrolled', 'cp' ),
					'last_login' => __( 'Last Active', 'cp' ),
					'average' => __( 'Average', 'cp' ),
					'certificate' => __( 'Certificate', 'cp' ),
				)
			);
		}

		return $columns;
	}

	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( 'title' !== $column_name ) {
			return '';
		}

		$actions = array();

		$course_url = CoursePress_Data_Course::get_course_url( $item->ID );

		if ( ! empty( $this->student_id ) ) {
			$actions['view'] = sprintf( '<a href="%s" target="_blank">%s</a>', $course_url, __( 'View Course', 'cp' ) );

			$workbook_url = add_query_arg(
					array(
						'page' => 'coursepress_assessments',
						'student_id' => $this->student_id,
						'course_id' => $item->ID,
						'view_answer' => 1,
						'display' => 'all_answered',
					)
				);
			$actions['workbook'] = sprintf( '<a href="%s">%s</a>', $workbook_url, __( 'Workbook', 'cp' ) );
		}

		return $this->row_actions( $actions );
	}

	public function column_date_enrolled( $course ) {
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$date_enrolled = get_user_meta( $this->student_id, 'enrolled_course_date_' . $course->ID );
		if ( is_array( $date_enrolled ) ) {
			$date_enrolled = array_pop( $date_enrolled );
		}
		$date_enrolled = date_i18n( $date_format . ' ' . $time_format, CoursePress_Data_Course::strtotime( $date_enrolled ) );

		return $date_enrolled;
	}

	public function column_last_login( $course_id ) {
		
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$last_activity = get_user_meta( $this->student_id, 'latest_activity', true );

		if ( empty( $last_activity ) ) {
			$return = '-';
		} else {
			$return = date_i18n( $date_format . ' ' . $time_format, CoursePress_Data_Course::strtotime( $last_activity ) );
		}

		return $return;
	}

	public function column_average( $course ) {
		if ( empty( self::$student_progress ) ) {
			self::$student_progress = CoursePress_Data_Student::get_completion_data( $this->student_id, $course->ID );
		}
		$average = CoursePress_Helper_Utility::get_array_val(
			self::$student_progress,
			'completion/average'
		);

		return (float) $average . '%';
	}

	public function column_certificate( $course ) {
		$completed = CoursePress_Data_Student::is_course_complete( $this->student_id, $course->ID, self::$student_progress );
		$download_certificate = __( 'Not available', 'cp' );

		if ( $completed ) {
			$certificate_link = CoursePress_Data_Certificate::get_encoded_url( $course->ID, $this->student_id );
			$download_certificate = sprintf( '<a href="%s" class="button-primary">%s</a>', $certificate_link, __( 'Download', 'cp' ) );
		}

		return $download_certificate;
	}

	public function extra_tablenav( $which ) {
		
	}
}
