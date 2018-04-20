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
	}

	public function prepare_items() {
		global $wp_query;
		$old_query = $wp_query;

		$post_status = 'any';
		$per_page = get_post( 'per_page' );
		$student_courses = CoursePress_Data_Student::get_enrolled_courses_ids( $this->student_id );

		/**
		 * Do not continue if user is not enrolled.
		 * WP_Query with empty 'post__in' should return all published posts.
		 */
		if ( empty( $student_courses ) ) {
			return;
		}

		$args = array(
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'post_status' => $post_status,
			'post__in' => $student_courses,
			'ignore_sticky_posts' => true,
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
			'title' => __( 'Title', 'coursepress' ),
		);

		if ( ! empty( $this->student_id ) ) {
			$columns = array_merge(
				$columns,
				array(
					'date_enrolled' => __( 'Date Enrolled', 'coursepress' ),
					'last_login' => __( 'Last Active', 'coursepress' ),
					'average' => __( 'Average', 'coursepress' ),
					'certificate' => __( 'Certificate', 'coursepress' ),
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
		$actions['course_id'] = sprintf( __( 'Course ID: %d', 'coursepress' ), $item->ID );

		if ( ! empty( $this->student_id ) ) {
			$actions['view'] = sprintf( '<a href="%s" target="_blank">%s</a>', $course_url, __( 'View Course', 'coursepress' ) );

			$workbook_url = add_query_arg(
				array(
						'page' => 'coursepress_assessments',
						'student_id' => $this->student_id,
						'course_id' => $item->ID,
						'view_answer' => 1,
						'display' => 'all_answered',
					)
			);
			$can_update = CoursePress_Data_Capabilities::can_update_course( $item->ID );
			if ( $can_update ) {
				$actions['workbook'] = sprintf( '<a href="%s">%s</a>', $workbook_url, __( 'Workbook', 'coursepress' ) );
			}
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

		if ( empty( $date_enrolled ) ) {
			return sprintf(
				'<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">%s</span>',
				__( 'Unknown enrolled date.', 'coursepress' )
			);
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
		$average = CoursePress_Data_Student::average_course_responses( $this->student_id, $course->ID );

		return (float) $average . '%';
	}

	public function column_certificate( $course ) {
		$completed = CoursePress_Data_Student::is_course_complete( $this->student_id, $course->ID );
		$download_certificate = __( 'Not available', 'coursepress' );

		if ( $completed ) {
			$certificate_link = CoursePress_Data_Certificate::get_encoded_url( $course->ID, $this->student_id );
			$download_certificate = sprintf( '<a href="%s" class="button-primary">%s</a>', $certificate_link, __( 'Download', 'coursepress' ) );
		}

		return $download_certificate;
	}

	public function extra_tablenav( $which ) {
	}

	protected function pagination( $which ) {
		// Show pagination only at the bottom
		if ( 'top' !== $which ) {
			parent::pagination( $which );
		}
	}
}
