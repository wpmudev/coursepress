<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CoursePress_Helper_Table_CourseStudents extends WP_List_Table {

	private $course_id = 0;
	private $add_new = false;
	private $students = array();

	/** Class constructor */
	public function __construct() {

		//$post_format = CoursePress_Model_Course::get_format();

		parent::__construct( array(
			'singular' => __( 'Student', CoursePress::TD ),
			'plural'   => __( 'Students', CoursePress::TD ),
			'ajax'     => false //should this table support ajax?
		) );

		//$this->post_type = CoursePress_Model_PostFormats::prefix() . $post_format['post_type'];
		//$this->count     = wp_count_posts( $this->post_type );

	}

	public function set_course( $id ) {
		$this->course_id = (int) $id;
	}

	public function set_add_new( $bool ) {
		$this->add_new = $bool;
	}

	public function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'ID'           => __( 'ID', CoursePress::TD ),
			'display_name' => __( 'Username', CoursePress::TD ),
			'first_name'   => __( 'First Name', CoursePress::TD ),
			'last_name'    => __( 'Last Name', CoursePress::TD ),
			'profile'      => __( 'Profile', CoursePress::TD ),
			'actions'      => __( 'Withdraw', CoursePress::TD ),
		);

		return $columns;
	}

	public function get_hidden_columns() {
		return array();
	}

	public function get_sortable_columns() {
		return array( 'title' => array( 'title', false ) );
	}

	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-actions[]" value="%s" />', $item->ID
		);
	}

	public function column_ID( $item ) {
		$this->students[] = $item->ID;
		return sprintf(
			'%d', $item->ID
		);
	}

	public function column_display_name( $item ) {
		return sprintf(
			'%s', $item->display_name
		);
	}

	public function column_first_name( $item ) {
		return sprintf(
			'%s', get_user_option( 'first_name', $item->ID )
		);
	}

	public function column_last_name( $item ) {
		return sprintf(
			'%s', get_user_option( 'last_name', $item->ID )
		);
	}

	public function column_actions( $item ) {
		$nonce = wp_create_nonce( 'withdraw-single-student' );
		return sprintf(
			'<a href="" class="withdraw-student" data-id="%s" data-nonce="%s"><i class="fa fa-times-circle remove-btn"></i></a>', $item->ID, $nonce

		);
	}

	public function prepare_items() {
		global $wpdb;

		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$perPage     = 20;
		$currentPage = $this->get_pagenum();

		$offset = ( $currentPage - 1 ) * $perPage;

		$this->_column_headers = array( $columns, $hidden, $sortable );

		//$post_args             = array(
		//	'post_type'      => $this->post_type,
		//	'post_status'    => $post_status,
		//	'posts_per_page' => $perPage,
		//	'offset'         => $offset,
		//	's'              => isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : ''
		//);

		if ( is_multisite() ) {
			$course_meta_key = $wpdb->prefix . 'enrolled_course_date_' . $this->course_id;
		} else {
			$course_meta_key = 'enrolled_course_date_' . $this->course_id;
		}

		// Could use the Course Model methods here, but lets try stick to one query
		$users = new WP_User_Query( array(
			'meta_key'     => $course_meta_key,
			'meta_compare' => 'EXISTS',
			'number'       => $perPage,
			'offset'       => $offset
		) );

		$this->items = $users->get_results();

		$totalItems = $users->get_total();
		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page'    => $perPage
		) );

	}

	public function extra_tablenav( $which ) {

		if ( 'bottom' === $which && $this->add_new ) {

			?>
			<div class="coursepress_course_add_student_wrapper">
			<?php

			echo CoursePress_Helper_UI::get_user_dropdown( 'student-add', 'student-add', array(
				'placeholder' => __( 'Choose student...', CoursePress::TD ),
				'class'       => 'chosen-select narrow',
				'exclude'     => $this->students
			) );

			$nonce = wp_create_nonce( 'add_student' );
			$withdraw_nonce = wp_create_nonce( 'withdraw_all_students' );
			?>

			<input type="button" class="add-new-student-button button" data-nonce="<?php echo $nonce; ?>" value="<?php esc_attr_e( 'Add Student', CoursePress::TD ); ?>" />
			<a class="withdraw-all-students" data-nonce="<?php echo $withdraw_nonce; ?>" href="#"><?php esc_html_e('Withdraw all students', CoursePress::TD ); ?></a>
			</div>
		<?php

		}

	}

	public function no_items() {
		_e( 'There are no students enrolled in this course. Add them below.', CoursePress::TD );
	}


}