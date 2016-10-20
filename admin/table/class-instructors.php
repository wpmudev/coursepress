<?php
/**
 * Instructors Table List
 *
 * This class extends WP_Users_List_Table to manage course instructors.
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
if ( ! class_exists( 'WP_Users_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php';
}
class CoursePress_Admin_Table_Instructors extends WP_Users_List_Table {
	var $course_id = 0;

	public function __construct() {
		parent::__construct();

		// Set our custom columns
		add_filter( 'manage_users_custom_column', array( $this, 'set_custom_columns' ), 10, 3 );

		if ( ! empty( $_GET['course_id'] ) ) {
			$this->course_id = (int) $_GET['course_id'];
		}
	}

	public function prepare_items() {
		$usersearch = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
		$per_page = ( $this->is_site_users ) ? 'site_users_network_per_page' : 'users_per_page';
		$users_per_page = $this->get_items_per_page( $per_page );

		$paged = $this->get_pagenum();
		$args = array(
			'number' => $users_per_page,
			'offset' => ( $paged-1 ) * $users_per_page,
			'meta_key' => 'role_ins',
			'meta_value' => 'instructor',
			'fields' => 'all_with_meta',
			'search' => $usersearch,
		);

		if ( ! empty( $_GET['course_id'] ) ) {
			// Show only students of current course
			$course_id = (int) $_GET['course_id'];
			$instructor_ids = CoursePress_Data_Course::get_instructors( $course_id );
			$args['include'] = $instructor_ids;
		}

		if ( '' !== $args['search'] )
			$args['search'] = '*' . $args['search'] . '*';

		if ( $this->is_site_users )
			$args['blog_id'] = $this->site_id;

		// Query the user IDs for this page
		$wp_user_search = new WP_User_Query( $args );

		$this->items = $wp_user_search->get_results();

		$this->set_pagination_args( array(
			'total_items' => $wp_user_search->get_total(),
			'per_page' => $users_per_page,
		) );
	}

	public function get_bulk_actions() {
		$actions = array();

		// @todo: Add sanity check/filter
		$actions['withdraw'] = __( 'Remove as Instructors', 'cp' );

		return $actions;
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'instructor_name' => __( 'Name', 'cp' ),
			'registered' => __( 'Registered', 'cp' ),
			'courses' => __( 'Courses', 'cp' ),
		);

		return $columns;
	}

	public function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$options = array();
		$options['value'] = $this->course_id;
		$options['class'] = 'medium dropdown';
		$options['first_option'] = array(
			'text' => __( 'All courses', 'cp' ),
			'value' => 'all',
		);

		if ( current_user_can( 'manage_options' ) ) {
			$assigned_courses = false;
		} else {
			$assigned_courses = CoursePress_Data_Instructor::get_assigned_courses_ids( get_current_user_id() );
			$assigned_courses = array_filter( $assigned_courses );
			$assigned_courses = array_map( 'get_post', $assigned_courses );
		}

		$courses = CoursePress_Helper_UI::get_course_dropdown( 'course_id', 'course_id', $assigned_courses, $options );
		?>
		<div class="alignleft actions category-filter">
			<?php echo $courses; ?>
			<input type="submit" class="button" name="action" value="<?php esc_attr_e( 'Filter', 'cp' ); ?>" />
		</div>
		<?php
	}

	protected function pagination( $which ) {
		// Show pagination only at the bottom
		if ( 'top' !== $which ) {
			parent::pagination( $which );
		} else {
			$this->search_box( __( 'Search Instructors', 'cp' ), 'search' );
		}
	}

	// Remove row actions on any other columns.
	public function get_primary_column_name() {
		return '';
	}

	/**
	 * Helper function to individually add custom column as method
	 **/
	public function set_custom_columns( $null, $column, $user_id ) {
		$method = 'column_' . $column;

		if ( method_exists( $this, $method ) ) {
			return call_user_func( array( $this, $method ), $user_id );
		}
	}

	public function column_instructor_name( $user_id ) {
		$user = get_userdata( $user_id );

		// User avatar
		$avatar = get_avatar( $user->user_email, 32 );
		$name = CoursePress_Helper_Utility::get_user_name( $user_id, true );

		// Generate row actions
		$actions = array();
		$url = remove_query_arg(
			array(
				'view',
				'_wpnonce',
				'instructor_id',
			)
		);

		// @todo: Add sanity check/validation
		$courses_url = add_query_arg(
			array(
				'view' => 'courses',
				'instructor_id' => $user_id,
			)
		);
		$actions['courses'] = sprintf( '<a href="%s">%s</a>', esc_url( $courses_url ), __( 'View Courses', 'cp' ) );

		// @todo: Add sanity check/validation
		$delete_url = add_query_arg(
			array(
				'_wpnonce' => wp_create_nonce( 'coursepress_remove_instructor' ),
				'instructor_id' => $user_id,
				'action' => 'delete',
			)
		);
		$actions['delete'] = sprintf( '<a class="remove_instructor_action" href="%s">%s</a>', esc_url( $delete_url ), __( 'Remove as Instructor', 'cp' ) );

		return $avatar . $name . $this->row_actions( $actions );
	}

	public function column_registered( $user_id ) {
		$instructor = get_userdata( $user_id );
		$date_format = get_option( 'date_format' );
		return date_i18n( $date_format, CoursePress_Data_Course::strtotime( $instructor->user_registered ) );
	}

	public function column_courses( $user_id ) {
		$count = CoursePress_Data_Instructor::count_courses( $user_id );
		$courses_link = add_query_arg(
			array(
				'view' => 'courses',
				'instructor_id' => $user_id,
			)
		);

		return $count > 0 ? sprintf( '<a href="%s">%s</a>', $courses_link, $count ) : 0;
	}
}
