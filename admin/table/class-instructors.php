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

	protected function get_per_page() {
		$screen = get_current_screen();
		$option = $screen->get_option( 'per_page', 'option' );

		$per_page = (int) get_user_option( $option );
		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $this->get_option( 'per_page', 'default' );
			if ( ! $per_page ) {
				$per_page = 20;
			}
		}
	}

	public function prepare_items() {
		$paged = $this->get_pagenum();
		/**
		 * Search
		 */
		$usersearch = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
		/**
		 * Per Page
		 */
		$per_page = $this->get_per_page();
		$users_per_page = $per_page = $this->get_items_per_page( 'coursepress_instructors_per_page', $per_page );

		/**
		 * pagination
		 */
		$current_page = $this->get_pagenum();
		$offset = ( $current_page - 1 ) * $per_page;
		/**
		 * Query args
		 */
		$role_name = CoursePress_Data_Capabilities::get_role_instructor_name();

		$args = array(
			'number' => $users_per_page,
			'offset' => ( $paged -1 ) * $users_per_page,
			'meta_key' => $role_name,
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

		if ( '' !== $args['search'] ) {
			$args['search'] = '*' . $args['search'] . '*';
		}

		if ( $this->is_site_users ) {
			$args['blog_id'] = $this->site_id;
		}

		/**
		 * Fix multisite meta_key name
		 */
		if ( is_multisite() ) {
			$args['blog_id'] = get_current_blog_id();
		}

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
		$actions['withdraw'] = __( 'Remove as Instructors', 'coursepress' );

		return $actions;
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'user_id' => __( 'ID', 'coursepress' ),
			'instructor_name' => __( 'Name', 'coursepress' ),
			'registered' => __( 'Registered', 'coursepress' ),
			'courses' => __( 'Courses', 'coursepress' ),
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
			'text' => __( 'All courses', 'coursepress' ),
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
			<input type="submit" class="button" name="action" value="<?php esc_attr_e( 'Filter', 'coursepress' ); ?>" />
		</div>
		<?php
	}

	protected function pagination( $which ) {
		// Show pagination only at the bottom
		if ( 'top' !== $which ) {
			parent::pagination( $which );
		} else {
			$this->search_box( __( 'Search Instructors', 'coursepress' ), 'search' );
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
		$actions = array();
		$actions['user_id'] = sprintf( __( 'User ID: %d', 'coursepress' ), $user_id );

		// User avatar
		$avatar = get_avatar( $user->user_email, 32 );
		$name = CoursePress_Helper_Utility::get_user_name( $user_id, true );

		// Generate row actions
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
		$actions['courses'] = sprintf( '<a href="%s">%s</a>', esc_url( $courses_url ), __( 'View Courses', 'coursepress' ) );

		// @todo: Add sanity check/validation
		$delete_url = add_query_arg(
			array(
				'_wpnonce' => wp_create_nonce( 'coursepress_remove_instructor' ),
				'instructor_id' => $user_id,
				'action' => 'delete',
			)
		);
		$actions['delete'] = sprintf( '<a class="remove_instructor_action" href="%s">%s</a>', esc_url( $delete_url ), __( 'Remove as Instructor', 'coursepress' ) );

		return $avatar . $name . $this->row_actions( $actions );
	}

	public function column_registered( $user_id ) {
		$instructor = get_userdata( $user_id );
		$date_format = get_option( 'date_format' );
		return date_i18n( $date_format, CoursePress_Data_Course::strtotime( $instructor->user_registered ) );
	}

	public function column_courses( $user_id ) {
		$count = CoursePress_Data_Instructor::count_courses( $user_id, true );
		$courses_link = add_query_arg(
			array(
				'view' => 'courses',
				'instructor_id' => $user_id,
			)
		);
		return $count > 0 ? sprintf( '<a href="%s">%s</a>', $courses_link, $count ) : 0;
	}

	/**
	 * Show courses list.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $user_id Current row user ID.
	 * @return string List of courses or information about nothing.
	 */
	public function column_courses_list( $user_id ) {
		$assigned_courses_ids = CoursePress_Data_Instructor::get_assigned_courses_ids( $user_id );
		if ( empty( $assigned_courses_ids ) ) {
			return sprintf(
				'<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">%s</span>',
				__( 'Instructor is not assigned to any course.', 'coursepress' )
			);
		}
		$content = '<ul>';
		foreach ( $assigned_courses_ids as $course_id ) {
			if ( ! isset( $this->courses[ $course_id ] ) ) {
				$this->courses[ $course_id ] = array(
					'title' => get_the_title( $course_id ),
					'link' => add_query_arg(
						array(
							'post_type' => CoursePress_Data_Course::get_post_type_name(),
							'page' => 'coursepress_instructors',
							'course_id' => $course_id,
						),
						admin_url( 'edit.php' )
					),
				);
			}
			$content .= sprintf(
				'<li><a href="%s">%s</a></li>',
				esc_url( $this->courses[ $course_id ]['link'] ),
				$this->courses[ $course_id ]['title']
			);
		}
		$content .= '</ul>';
		return $content;
	}

	public function column_user_id( $user_id ) {
		return $user_id;
	}
}
