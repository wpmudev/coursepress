<?php
class CoursePress_Admin_Courses {
	private static $post_type = 'course';
	private static $is_course = false;
	static $date_format = '';
	static $certified_students = 0;

	public static function init() {
		global $pagenow, $typenow;

		self::$post_type = $post_type = CoursePress_Data_Course::get_post_type_name();
		self::$date_format = get_option( 'date_format' );

		add_filter( 'default_hidden_columns', array( __CLASS__, 'hidden_columns' ) );
		add_filter( 'manage_edit-' . $post_type . '_sortable_columns', array( __CLASS__, 'sortable_columns' ) );
		// Disable months dropdown
		add_filter( 'disable_months_dropdown', array( __CLASS__, 'disable_months_dropdown' ), 10, 2 );

		// Don't allow columns to be customized (for now)
		if ( $typenow == $post_type ) {
			remove_all_filters( 'manage_posts_columns' );
		}

		remove_all_filters( 'manage_' . $post_type . '_posts_columns' );
		add_filter( 'manage_' . $post_type . '_posts_columns', array( __CLASS__, 'header_columns' ) );
		add_action( 'manage_' . $post_type . '_posts_custom_column', array( __CLASS__, 'courselist_columns' ), 10, 2 );

		add_filter( 'post_row_actions', array( __CLASS__, 'row_actions' ) , 10, 2 );

		// Print templates at footer
		add_action( 'admin_footer', array( __CLASS__, 'templates' ) );
	}

	public static function _is_course( $post ) {
		return self::$post_type == $post->post_type;
	}

	protected static function can_update_course( $course_id ) {
		return CoursePress_Data_Capabilities::can_update_course( $course_id );
	}
	protected static function can_delete_course( $course_id ) {
		return CoursePress_Data_Capabilities::can_delete_course( $course_id );
	}

	public static function hidden_columns( $columns ) {

		array_push( $columns, 'taxonomy-course_category', 'date_start', 'date_end', 'date_enrollment_start', 'date_enrollment_end' );

		return $columns;
	}

	public static function sortable_columns( $columns ) {
		$columns = array_merge( $columns, array(
			'date_start' => 'date_start',
			'date_enrollment_start' => 'date_enrollment_start',
		) );

		return $columns;
	}

	public static function disable_months_dropdown( $false, $post_type ) {
		if ( $post_type == self::$post_type ) {
			$false = true;
		}
		return $false;
	}

	public static function header_columns( $columns ) {
		self::$is_course = true;

		$columns = array_merge( $columns, array(
			'date_start' => __( 'Start Date', 'cp' ),
			'date_end' => __( 'End Date', 'cp' ),
			'date_enrollment_start' => __( 'Enrollment Start', 'cp' ),
			'date_enrollment_end' => __( 'Enrollment End', 'cp' ),
			'units' => __( 'Units', 'cp' ),
			'students' => __( 'Students', 'cp' ),
			'certificates' => __( 'Certified', 'cp' ),
			'status' => __( 'Status', 'cp' ),
		) );

		// Remove date column
		unset( $columns['date'] );

		if ( ! CoursePress_Data_Capabilities::can_manage_courses() ) {
			unset( $columns['cb'], $columns['actions'], $columns['units'] );
		}

		if ( ! CoursePress_Data_Capabilities::can_delete_course( 0 ) ) {
			unset( $columns['actions'] );
		}

		return $columns;
	}

	public static function courselist_columns( $column_name, $course_id ) {
		$method = 'column_' . $column_name;

		if ( method_exists( __CLASS__, $method ) ) {
			$course = get_post( $course_id );

			echo call_user_func( array( __CLASS__, $method ), $course );
		}
	}

	private static function _get_course_meta_date( $name, $item ) {
		$meta_key = sprintf( 'cp_%s_date', $name );
		$date = get_post_meta( $item->ID, $meta_key, true );
		if ( empty( $date ) ) {
			return '-';
		} else {
			$date = date_i18n( self::$date_format, $date );
		}
		return $date;
	}

	/**
	 * Start date
	 */
	public static function column_date_start( $item ) {
		return self::_get_course_meta_date( 'course_start', $item );
	}

	/**
	 * end date
	 */
	public static function column_date_end( $item ) {
		return self::_get_course_meta_date( 'course_end', $item );
	}

	/**
	 * enrollment_end date
	 */
	public static function column_date_enrollment_end( $item ) {
		return self::_get_course_meta_date( 'enrollment_end', $item );
	}

	/**
	 * enrollment_start date
	 */
	public static function column_date_enrollment_start( $item ) {
		return self::_get_course_meta_date( 'enrollment_start', $item );
	}

	public static function column_units( $item ) {
		$post_args = array(
			'post_type' => CoursePress_Data_Unit::get_post_type_name(),
			'post_parent' => $item->ID,
			'post_status' => array( 'publish', 'private', 'draft' ),
		);

		$query = new WP_Query( $post_args );
		$published = 0;
		foreach ( $query->posts as $post ) {
			if ( 'publish' === $post->post_status ) {
				$published += 1;
			}
		}
		$output = sprintf( '<div><p>%d&nbsp;%s<br />%d&nbsp;%s</p>',
			$query->found_posts,
			__( 'Units', 'cp' ),
			$published,
			__( 'Published', 'cp' )
		);

		wp_reset_postdata();

		return $output;
	}

	public static function column_students( $item ) {
		$count = CoursePress_Data_Course::count_students( $item->ID );

		return $count;
	}

	/**
	 * Column contain number of certified students.
	 *
	 * @since 2.0.0
	 */
	public static function column_certificates( $item ) {
		$certified = CoursePress_Data_Course::get_certified_student_ids( $item->ID );

		return count( $certified );
	}

	public static function column_status( $item ) {

		$user_id = get_current_user_id();
		$publish_toggle = ucfirst( $item->post_status );

		if ( CoursePress_Data_Capabilities::can_change_course_status( $item->ID, $user_id ) ) {
			// Publish Course Toggle
			$course_id = $item->ID;
			$status = get_post_status( $course_id );
			$ui = array(
				'label' => '',
				'left' => '<i class="fa fa-ban"></i>',
				'left_class' => 'red',
				'right' => '<i class="fa fa-check"></i>',
				'right_class' => 'green',
				'state' => 'publish' === $status ? 'on' : 'off',
				'data' => array(
					'nonce' => wp_create_nonce( 'publish-course' ),
				),
			);
			$ui['class'] = 'course-' . $course_id;
			$publish_toggle = ! empty( $course_id ) ? CoursePress_Helper_UI::toggle_switch( 'publish-course-toggle-' . $course_id, 'publish-course-toggle-' . $course_id, $ui ) : '';
		}

		return $publish_toggle;
	}

	public static function row_actions( $actions, $course ) {
		// Bail if not a course
		if ( false === self::_is_course( $course ) || ! empty( $actions['restore'] ) ) {
			return $actions;
		}

		// Reconstruct row actions
		$actions = array();
		$edit_link = get_edit_post_link( $course->ID );
		$published = 'publish' == $course->post_status;
		$course_url = CoursePress_Data_Course::get_course_url( $course->ID );
		$can_update = false;

		if ( self::can_update_course( $course->ID ) ) {
			$can_update = true;

			// Add edit link
			$actions['edit'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_link ), __( 'Edit', 'cp' ) );

			$edit_units = add_query_arg( 'tab', 'units', $edit_link );
			$edit_students = add_query_arg( 'tab', 'students', $edit_link );

			$actions['units'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_units ), __( 'Units', 'cp' ) );
			$actions['students'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_students ), __( 'Students', 'cp' ) );

			/**
			 * single course export
			 */
			$action = 'coursepress_export';
			$nonce = wp_create_nonce( $action );
			$url = add_query_arg(
				array(
					'page' => $action,
					'coursepress' => array( 'courses' => array( absint( $course->ID ) ) ),
					'coursepress_export' => $nonce,
				),
				admin_url( 'admin.php' )
			);

			$url = wp_nonce_url( $url, $action, $nonce );
			$actions['export'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $url ),
				__( 'Export', 'cp' )
			);
		}

		if ( CoursePress_Data_Capabilities::can_create_course( $course->ID ) ) {
			// create a nonce
			$duplicate_nonce = wp_create_nonce( 'duplicate_course' );
			$actions['duplicate'] = sprintf( '<a data-nonce="%s" data-id="%s" class="duplicate-course-link">%s</a>', $duplicate_nonce, $course->ID, __( 'Duplicate Course', 'cp' ) );
		}

		if ( $can_update && self::can_delete_course( $course->ID ) ) {
			$trash_url = get_delete_post_link( $course->ID );
			$actions['trash'] = sprintf( '<a href="%s">%s</a>', esc_url( $trash_url ), __( 'Trash', 'cp' ) );
		}

		$format = '<a href="%s" target="_blank">%s</a>';
		$unit_overview_url = $course_url . 'units/';

		if ( false === $published ) {
			if ( $can_update ) {
				$actions['view'] = sprintf( $format, esc_url( $course_url ), __( 'Preview Course', 'cp' ) );
				$actions['preview-units'] = sprintf( $format, esc_url( $unit_overview_url ), __( 'Preview Units', 'cp' ) );
			}
		} else {
			$actions['view'] = sprintf( $format, esc_url( $course_url ), __( 'View Course', 'cp' ) );
			$actions['preview-units'] = sprintf( $format, esc_url( $unit_overview_url ), __( 'View Units', 'cp' ) );
		}

		return $actions;
	}

	public static function templates() {
		if ( false === self::$is_course ) {
			return;
		}
		?>
		<script type="text/html" id="tmpl-coursepress-courses-delete-one">
				<div class="notice notice-warning">
					<p><span class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></span> <?php _e( 'Deleting course <b>{{{data.names}}}</b>, please wait!', 'cp' ); ?></p>
					<p><?php _e( 'This page will be reloaded shortly.', 'cp' ); ?></p>
				</div>
			</script>
			<script type="text/html" id="tmpl-coursepress-courses-delete-more">
				<div class="notice notice-warning">
					<p><span class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></span><?php _e( 'Deleting {{{data.size}}} courses, please wait!', 'cp' ); ?></p>
					<p><?php _e( 'This page will be reloaded shortly.', 'cp' ); ?></p>
					<p><?php _e( 'Deleted courses:', 'cp' ) ?></p>
					{{{data.names}}}
				</div>
			</script>
			<script type="text/html" id="tmpl-coursepress-courses-duplicate">
				<div class="notice notice-warning">
					<p><span class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></span> <?php _e( 'Duplicating course <b>{{{data.names}}}</b>, please wait!', 'cp' ); ?></p>
					<p><?php _e( 'This page will be reloaded shortly.', 'cp' ); ?></p>
				</div>
			</script>
		<?php
	}
}