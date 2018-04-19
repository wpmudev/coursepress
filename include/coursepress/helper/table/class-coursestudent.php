<?php

/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary. In this tutorial, we are
 * going to use the WP_List_Table class directly from WordPress core.
 *
 * IMPORTANT:
 * Please note that the WP_List_Table class technically isn't an official API,
 * and it could change at some point in the distant future. Should that happen,
 * I will update this plugin with the most current techniques for your reference
 * immediately.
 *
 * If you are really worried about future compatibility, you can make a copy of
 * the WP_List_Table class (file path is shown just below) to use and distribute
 * with your plugins. If you do that, just remember to change the name of the
 * class to avoid conflicts with core.
 *
 * Since I will be keeping this tutorial up-to-date for the foreseeable future,
 * I am going to work with the copy of the class provided in WordPress core.
 */
if ( ! class_exists( 'WP_Users_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php' );
}

/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 *
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 *
 * Our theme for this list table is going to be movies.
 */

class CoursePress_Helper_Table_CourseStudent extends WP_Users_List_Table {

	protected $course_id = 0;
	private $add_new = false;
	protected $students = array();
	protected $can_withdraw_students = false;
	private $filter_show = 'all';
	private $filter_options = array();

	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Student', 'CP_TD' ),
			'plural' => __( 'Students', 'CP_TD' ),
			'ajax' => false,// should this table support ajax?
		) );

		/**
		 * add filters
		 */
		add_filter( 'user_row_actions', array( $this, 'student_row_actions' ), 10, 2 );
		add_filter( 'manage_users_custom_column', array( $this, 'columns' ), 10, 3 );
		add_filter( 'views_course', array( $this, 'views_array_filter' ) );

		/**
		 * set course ID
		 */
		if ( CoursePress_Data_Course::is_course( $this->course_id ) ) {
			$this->can_withdraw_students = CoursePress_Data_Capabilities::can_withdraw_students( $this->course_id );
		}

		/**
		 * filter options
		 */
		$this->filter_options = array(
			'all' => __( 'All', 'CP_TD' ),
			'yes' => __( 'Certified', 'CP_TD' ),
			'no' => __( 'Not certified', 'CP_TD' ),
		);
		if ( isset( $_REQUEST['certified'] ) && array_key_exists( $_REQUEST['certified'], $this->filter_options ) ) {
			$this->filter_show = $_REQUEST['certified'];
		}
	}

	/**
	 * Show quick filter.
	 *
	 * @since 2.0.8
	 */
	public function views_array_filter( $views ) {
		global $post;
		$views = array();
		$pattern = '<a href="%s" class="%s">%s</a>';
		$url = add_query_arg(
			array(
				'post_type' => $post->post_type,
				'post' => $post->ID,
				'action' => 'edit',
				'tab' => 'students',
			),
			admin_url( 'post.php' )
		);
		foreach ( $this->filter_options as $key => $label ) {
			$action_url = add_query_arg( 'certified', $key, $url );
			$class = $key == $this->filter_show? 'current':'';
			$views[ $key ] = sprintf(
				$pattern,
				esc_url( $action_url ),
				$class,
				esc_html( $label )
			);
		}
		return $views;
	}

	/**
	 * Get student object by student id.
	 *
	 * @since 2.0.8
	 *
	 * @param integer $ID Student ID.
	 * @return null|WP User Student object.
	 */
	protected function get_student( $ID ) {
		foreach ( $this->items as $item ) {
			if ( $ID == $item->ID ) {
				return $item;
			}
		}
		return null;
	}

	public function columns( $content, $column_name, $item_id ) {
		switch ( $column_name ) {
			case 'display_name':
			case 'first_name':
			case 'last_name':
			return sprintf(
				'%s', get_user_option( $column_name, $item_id )
			);
			case 'certificates':
			return $this->column_certificates( $item_id );
		}
		return $content;
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

	/** ************************************************************************
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value
	 * is the column's title text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 *
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a column_cb() method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	public function get_columns() {
		$course_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : null;
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'username' => __( 'Username', 'CP_TD' ),
		    'display_name' => __( 'Display Name', 'CP_TD' ),
			'first_name' => __( 'First Name', 'CP_TD' ),
			'last_name' => __( 'Last Name', 'CP_TD' ),
			'certificates' => __( 'Certified', 'CP_TD' ),
		);

		if ( ! CoursePress_Data_Capabilities::can_withdraw_students( $course_id ) ) {
			unset( $columns['actions'] );
		}

		return $columns;
	}

	public function get_hidden_columns() {
		return array();
	}

	/** ************************************************************************
	 * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
	 * you will need to register it here. This should return an array where the
	 * key is the column that needs to be sortable, and the value is db column to
	 * sort by. Often, the key and value will be the same, but this is not always
	 * the case (as the value is a column name from the database, not the list table).
	 *
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting. You still need to detect
	 * the ORDERBY and ORDER querystring variables within prepare_items() and sort
	 * your data accordingly (usually by modifying your query).
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 **************************************************************************/
	public function get_sortable_columns() {
		$c = array(
			'display_name' => array( 'display_name', false ),
			'first_name' => array( 'first_name', false ),
			'last_name' => array( 'last_name', false ),
			'username' => array( 'login', false ),
		);
		return $c;
	}

	/** ************************************************************************
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
	 * is given special treatment when columns are processed. It ALWAYS needs to
	 * have it's own method.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-actions[]" value="%s" />', $item->ID
		);
	}

	public function student_row_actions( $actions, $item ) {
		$this->students[] = $item->ID;
		$profile_link = CoursePress_Data_Student::get_admin_profile_url( $item->ID );
		$workbook_link = add_query_arg(
			array(
				'post_type' => CoursePress_Data_Course::get_post_type_name(),
				'page' => 'coursepress_assessments',
				'view' => 'profile',
				'student_id' => $item->ID,
				'course_id' => $this->course_id,
			),
			remove_query_arg(
				array(
					'tab',
					'post',
					'action',
				),
				admin_url( 'edit.php' )
			)
		);

		$actions = array(
			'id' => sprintf( '<span>%s</span>', esc_html( sprintf( __( 'ID: %d', 'CP_TD' ), $item->ID ) ) ),
			'profile' => sprintf( '<a href="%s">%s</a>', $profile_link, esc_html__( 'Student Profile', 'CP_TD' ) ),
			'workbook' => sprintf( '<a href="%s">%s</a>', $workbook_link, esc_html__( 'Workbook', 'CP_TD' ) ),
		);

		if ( current_user_can( 'edit_users' ) ) {
			$actions['edit_user_profile'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'courses' => 'show',
						),
						get_edit_user_link( $item->ID )
					)
				),
				__( 'Edit User Profile', 'CP_TD' )
			);
		}

		if ( $this->can_withdraw_students ) {
			$actions['trash'] = sprintf(
				'<a href="#" class="withdraw-student" data-id="%s" data-nonce="%s">%s</a>',
				esc_attr( $item->ID ),
				esc_attr( wp_create_nonce( 'withdraw-single-student-'.$item->ID ) ),
				esc_html__( 'Withdraw', 'CP_TD' )
			);
		}
		return $actions;
	}

	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 *
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
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
		$query_args = array(
			'meta_query' => array(
				array(
					'key' => $course_meta_key,
					'compare' => 'EXISTS',
				),
			),
			'number' => $per_page,
			'offset' => $offset,
		);
		$usersearch = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

		if ( ! empty( $usersearch ) ) {
			$query_args['search'] = '*' . $usersearch . '*';
		}

		if ( isset( $_REQUEST['orderby'] ) ) {
			$query_args['orderby'] = $_REQUEST['orderby'];
			switch ( $_REQUEST['orderby'] ) {
				case 'first_name':
				case 'last_name':
					$query_args['meta_query'] = array(
					'relation' => 'AND',
					array(
						'key' => $_REQUEST['orderby'],
						'compare' => 'EXISTS',
					),
					array(
						'key' => $course_meta_key,
						'compare' => 'EXISTS',
					),
					);
					$query_args['orderby'] = 'meta_value';
					break;
			}
		}
		if ( isset( $_REQUEST['order'] ) ) {
			$query_args['order'] = $_REQUEST['order'];
		}

		/**
		 * fil certificates
		 */
		$certificates = CoursePress_Data_Certificate::get_certificated_students_by_course_id( $this->course_id );

		/**
		 * Certificates
		 */
		if ( ! empty( $certificates ) ) {
			switch ( $this->filter_show ) {
				case 'no':
					$query_args['exclude'] = $certificates;
				break;
				case 'yes':
					$query_args['include'] = $certificates;
				break;
			}
		}

		$users = new WP_User_Query( $query_args );

		$this->items = array();
		foreach ( $users->get_results() as $one ) {
			$one->data->certified = in_array( $one->ID, $certificates )? 'yes' : 'no';
			$this->items[] = $one;
		}

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
				$class_limited = CoursePress_Data_Course::get_setting( $course_id, 'class_limited' );
				$class_limited = cp_is_true( $class_limited );
				$add_form_to_add_student = false;
				if ( $class_limited ) {
					$class_size = (int) CoursePress_Data_Course::get_setting( $course_id, 'class_size' );
					$total_items = count( $this->items );
					if ( 0 === $class_size || $class_size > $total_items ) {
						$add_form_to_add_student = true;
					} else {
						$add_form_to_add_student = false;
						printf(
							'<span>%s</span>',
							__( 'You can not add a student, the class limit is reached.', 'CP_TD' )
						);
					}
				} else {
					$add_form_to_add_student = true;
				}
				if ( $add_form_to_add_student ) {
					$name = 'student-add';
					$id = 'student-add';
					if ( apply_filters( 'coursepress_use_default_student_selector', false ) ) {
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
					} else if ( apply_filters( 'coursepress_use_select2_student_selector', true ) ) {
						$nonce_search = CoursePress_Admin_Students::get_search_nonce_name( $course_id );
						$nonce_search = wp_create_nonce( $nonce_search );
						$user_selector = sprintf(
							'<select name="%s" id="%s" data-nonce="%s" data-nonce-search="%s"></select>',
							$name,
							$id,
							esc_attr( $nonce ),
							esc_attr( $nonce_search )
						);
					} else {
						$user_selector = '<input type="text" id="' . $id .'" name="' . $name . '" placeholder="' . esc_attr__( 'Enter user ID', 'CP_TD' ) . '" />';
					}
					$user_selector = apply_filters( 'coursepress_student_selector', $user_selector, $id, $name );
					echo $user_selector;
					printf(
						' <input type="button" class="add-new-student-button button" data-nonce="%s" value="%s" >',
						esc_attr( $nonce ),
						esc_attr__( 'Add Student', 'CP_TD' )
					);
				}
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

	/**
	 * Column contain number of certified students.
	 *
	 * @since 2.0.0
	 */
	public function column_certificates( $item_id ) {
		$item = $this->get_student( $item_id );
		if ( 'yes' == $item->data->certified ) {
			return sprintf( '<span class="cp-certified">%s</span>', esc_html__( 'Certified', 'CP_TD' ) );
		}
		return '<span class="dashicons dashicons-no"></span>';
	}
}
