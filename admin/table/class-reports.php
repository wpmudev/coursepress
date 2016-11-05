<?php
/**
 * Reports table
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class CoursePress_Admin_Table_Reports extends WP_List_Table {
	var $courses = array();
	var $course_id = 0;
	var $last_student_progress = array();
	var $is_cache_path_writable = false;

	/** Class constructor */
	public function __construct() {

		parent::__construct( array(
			'singular' => __( 'Reports', 'CP_TD' ),
			'plural' => __( 'Reports', 'CP_TD' ),
			'ajax' => false,// should this table support ajax?
		) );

		$this->is_cache_path_writable = CoursePress_Helper_PDF::is_cache_path_writable();

		$this->courses = CoursePress_Data_Instructor::get_accessable_courses();

		if ( empty( $_REQUEST['course_id'] ) && ! empty( $this->courses ) ) {
			$this->course_id = $this->courses[0]->ID;
		} else {
			$this->course_id = (int) $_REQUEST['course_id'];
		}
	}

	public function prepare_items() {
		global $wpdb;

		$screen = get_current_screen();
		/**
		 * Per Page
		 */
		$option = $screen->get_option( 'per_page', 'option' );
		$per_page = (int) get_user_option( $option );
		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $this->get_option( 'per_page', 'default' );
			if ( ! $per_page ) {
				$per_page = 20;
			}
		}
		$per_page = $this->get_items_per_page( 'coursepress_reports_per_page', $per_page );

		$current_page = $this->get_pagenum();
		$offset = ( $current_page - 1 ) * $per_page;
		$s = isset( $_POST['s'] )? mb_strtolower( trim( $_POST['s'] ) ):false;

		if ( is_multisite() ) {
			$course_meta_key = $wpdb->prefix . 'enrolled_course_date_' . $this->course_id;
		} else {
			$course_meta_key = 'enrolled_course_date_' . $this->course_id;
		}

		$args = array(
			'meta_key' => $course_meta_key,
			'meta_compare' => 'EXISTS',
			'number' => $per_page,
			'offset' => $offset,
			'search' => $s,
		);

		$users = new WP_User_Query( $args );
		$this->items = $users->get_results();

		$total_items = $users->get_total();
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page' => $per_page,
			)
		);
	}

	public function no_items() {
		_e( 'No students found.', 'CP_TD' );
	}

	public function get_columns() {
		return array(
			'cb' => '<input type="checkbox" />',
			'id' => __( 'ID', 'CP_TD' ),
			'name' => __( 'Student Name', 'CP_TD' ),
			'responses' => __( 'Responses', 'CP_TD' ),
			'average' => __( 'Average', 'CP_TD' ),
			'report' => __( 'Download', 'CP_TD' ),
		);
	}

	public function get_bulk_actions() {
		$actions = array(
			'download' => __( 'Download', 'CP_TD' ),
			'download_summary' => __( 'Download Summary', 'CP_TD' ),
		);

		return $actions;
	}

	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="students[]" value="%s" />', $item->ID
		);
	}

	public function column_ID( $item ) {
		return $item->ID;
	}

	public function column_name( $item ) {
		$avatar = get_avatar( $item->user_email, 28 );
		$name = CoursePress_Helper_Utility::get_user_name( $item->ID, true );

		return $avatar . $name;
	}

	public function column_responses( $item ) {
		$this->last_student_progress = CoursePress_Data_Student::get_completion_data( $item->ID, $this->course_id );

		$responses = (int) CoursePress_Data_Student::count_course_responses( $item->ID, $this->course_id, $this->last_student_progress );

		return $responses;
	}

	public function column_average( $item ) {
		$average = CoursePress_Helper_Utility::get_array_val(
			$this->last_student_progress,
			'completion/average'
		);

		return (float) $average . '%';
	}

	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( 'name' !== $column_name ) {
			return '';
		}
	}

	public function column_report( $item ) {
		if ( true === $this->is_cache_path_writable ) {
			$download_url = add_query_arg(
				array(
					'student_id' => $item->ID,
					'course_id' => $this->course_id,
					'_wpnonce' => wp_create_nonce( 'coursepress_download_report' ),
				)
			);
			return sprintf(
				'<a href="%s" class="pdf" data-student="%d" data-course="%d">&nbsp;</a>',
				esc_url( $download_url ),
				esc_attr( $item->ID ),
				esc_attr( $this->course_id )
			);
		}
		return sprintf( '<span class="pdf" title="%s" data-click="false"></span>', esc_attr__( 'We can not generata PDF. Cache directory is not writable.', 'CP_TD' ) );
	}

	public function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$options = array();
		$options['value'] = $this->course_id;
		$options['class'] = 'medium dropdown';
		$options['placeholder'] = __( 'Select course', 'CP_TD' );
		$courses = CoursePress_Helper_UI::get_course_dropdown( 'course_id', 'course_id', $this->courses, $options );
		?>
		<div class="alignleft course-filter">
			<?php echo $courses; ?>
			<input type="submit" class="button action" name="action" value="<?php esc_attr_e( 'Filter', 'CP_TD' ); ?>" />
		</div>
		<?php
		$this->search_box( __( 'Search', 'CP_TD' ), 'search_students' );
	}

	public function pagination( $which ) {
		if ( 'top' !== $which ) {
			return parent::pagination( $which );
		}
	}
}
