<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CoursePress_Helper_Table_CourseStudent extends WP_List_Table {

	private $course_id = 0;
	private $add_new = false;
	private $students = array();

	/** Class constructor */
	public function __construct() {
		// $post_format = CoursePress_Data_Course::get_format();
		parent::__construct( array(
			'singular' => __( 'Student', 'CP_TD' ),
			'plural' => __( 'Students', 'CP_TD' ),
			'ajax' => false,// should this table support ajax?
		) );

		// $this->post_type = CoursePress_Data_PostFormat::prefix( $post_format['post_type'] );
		// $this->count = wp_count_posts( $this->post_type );
	}

	public function set_course( $id ) {
		$this->course_id = (int) $id;
	}

	/**
	 * get course_id
	 *
	 * @since 2.0.0
	 *
	 * return integer course id
	 */
	public function get_course_id() {
		return $this->course_id;
	}

	public function set_add_new( $bool ) {
		$this->add_new = $bool;
	}

	public function get_columns() {
		$course_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : null;
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'ID' => __( 'ID', 'CP_TD' ),
			'display_name' => __( 'Username', 'CP_TD' ),
			'first_name' => __( 'First Name', 'CP_TD' ),
			'last_name' => __( 'Last Name', 'CP_TD' ),
			'profile' => __( 'Profile', 'CP_TD' ),
			'actions' => __( 'Withdraw', 'CP_TD' ),
		);

		if ( ! CoursePress_Data_Capabilities::can_withdraw_students( $course_id ) ) {
			unset( $columns['actions'] );
		}

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

	public function column_profile( $item ) {
		if ( current_user_can( 'edit_users' ) ) {
			return sprintf(
				'<a href="%s#courses">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'courses' => 'show',
						),
						get_edit_user_link( $item->ID )
					)
				),
				__( 'Edit Profile', 'CP_TD' )
			);
		}
		return ' ';
	}

	public function column_actions( $item ) {
		$course_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : null;
		$nonce = wp_create_nonce( 'withdraw-single-student' );
		$withdraw = sprintf(
			'<a href="" class="withdraw-student" data-id="%s" data-nonce="%s"><i class="fa fa-times-circle remove-btn"></i></a>', $item->ID, $nonce
		);

		if ( CoursePress_Data_Capabilities::can_withdraw_students( $course_id ) ) {
			echo $withdraw;
		}
	}

	public function prepare_items() {
		global $wpdb;

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$per_page = 20;
		$current_page = $this->get_pagenum();

		$offset = ( $current_page - 1 ) * $per_page;

		$this->_column_headers = array( $columns, $hidden, $sortable );

		if ( is_multisite() ) {
			$course_meta_key = $wpdb->prefix . 'enrolled_course_date_' . $this->course_id;
		} else {
			$course_meta_key = 'enrolled_course_date_' . $this->course_id;
		}

		// Could use the Course Model methods here, but lets try stick to one query
		$users = new WP_User_Query(
			array(
				'meta_key' => $course_meta_key,
				'meta_compare' => 'EXISTS',
				'number' => $per_page,
				'offset' => $offset,
			)
		);

		$this->items = $users->get_results();

		$total_items = $users->get_total();
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page' => $per_page,
			)
		);

	}

	public function extra_tablenav( $which ) {
		$course_id = (int) $_GET['id'];

		if ( 'bottom' === $which && $this->add_new ) {

			?>
			<div class="coursepress_course_add_student_wrapper">
			<?php
			$nonce = wp_create_nonce( 'add_student' );
			$withdraw_nonce = wp_create_nonce( 'withdraw_all_students' );

			if ( CoursePress_Data_Capabilities::can_assign_course_student( $course_id ) ) {
				$name = 'student-add';
				$id = 'student-add';
				if ( apply_filters( 'coursepress_use_default_student_selector', true ) ) {
					$user_selector = CoursePress_Helper_UI::get_user_dropdown(
						$id,
						$name,
						array(
							'placeholder' => __( 'Choose student...', 'CP_TD' ),
							'class' => 'chosen-select narrow',
							'exclude' => $this->students,
							'context' => 'students',
						)
					);
				} else {
					$user_selector = '<input type="text" id="' . $id .'" name="' . $name . '" placeholder="' . esc_attr__( 'Enter user ID', 'CP_TD' ) . '" />';
				}
	
				$user_selector = apply_filters( 'coursepress_student_selector', $user_selector, $id, $name );
				echo $user_selector;
			?>
			<input type="button" class="add-new-student-button button" data-nonce="<?php echo $nonce; ?>" value="<?php esc_attr_e( 'Add Student', 'CP_TD' ); ?>" />
			<?php
			}

			if ( CoursePress_Data_Capabilities::can_withdraw_students( $course_id ) ) {
			?>
				<a class="withdraw-all-students" data-nonce="<?php echo $withdraw_nonce; ?>" href="#"><?php esc_html_e( 'Withdraw all students', 'CP_TD' ); ?></a>
			<?php
			}
			?>
			<br />
			</div>
		<?php

		}

	}

	public function no_items() {
		$course_id = (int) $_GET['id'];

		if ( CoursePress_Data_Capabilities::can_assign_course_student( $course_id ) || CoursePress_Data_Capabilities::can_invite_students( $course_id ) ) {
			esc_html_e( 'There are no students enrolled in this course. Add them below.', 'CP_TD' );
		} else {
			esc_html_e( 'There are no students enrolled in this course.', 'CP_TD' );
		}
	}
}
