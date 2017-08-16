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

		//      $this->is_cache_path_writable = CoursePress_Helper_PDF::is_cache_path_writable();

		$this->courses = coursepress_get_accessible_courses();

		if ( empty( $_REQUEST['course_id'] ) && ! empty( $this->courses ) ) {
			$tmp = array_keys( $this->courses );
			$this->course_id = array_shift( $tmp );
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

		$users = coursepress_get_students_ids( $this->course_id, $offset, $per_page );
		$this->items = array();
		/**
		 */
		foreach ( $users as $id ) {
			$args = array(
				'student_id' => $ID,
				'course_id' => $this->course_id,
			);
			$download_url = wp_nonce_url( add_query_arg( $args ), 'coursepress_download_report' );
			$args['mode'] = 'html';
			$preview_url = wp_nonce_url( add_query_arg( $args ), 'coursepress_preview_report' );
			$user = coursepress_get_user( $id );
			$user->progress = $user->get_completion_data( $this->course_id );
			$user->responses = coursepress_count_course_responses( $user, $this->course_id, $user->progress );
			$user->urls = array(
				'download_url' => $download_url,
				'preview_url' => $preview_url,
			);
			$this->items[] = $user;
		}

		$total_items = coursepress_get_students_ids( $this->course_id, 0, 0 );
		$total_items = count( $total_items );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page' => $per_page,
				'course_id' => $this->course_id,
			)
		);
	}

	public function no_items() {
		_e( 'No students found.', 'CP_TD' );
	}

	public function get_bulk_actions() {
		$actions = array(
			'download' => __( 'Download', 'CP_TD' ),
			'download_summary' => __( 'Download Summary', 'CP_TD' ),
			'show' => __( 'Show', 'CP_TD' ),
			'show_summary' => __( 'Show Summary', 'CP_TD' ),
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

	private function get_responses( $item ) {

		CoursePress_Data_Student::get_calculated_completion_data( $item->ID, $this->course_id );
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
