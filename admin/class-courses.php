<?php
class CoursePress_Admin_Courses {
	private static $post_type = 'course';
	private static $is_course = false;
	static $date_format = '';
	static $certified_students = 0;

	public static function init() {
		global $pagenow, $typenow;

		do_action( 'coursepress_admin_render_page' );
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

		/**
		 * when delete a course
		 */
		add_action( 'delete_post', array( 'CoursePress_Admin_Controller_Course', 'delete_course' ) );

		/**
		* add capabilities
		*/
		add_filter( 'user_has_cap', array( 'CoursePress_Data_Capabilities', 'user_has_cap_edit_course' ), 200, 4 );

		/**
		 * set sort order
		 */
		add_action( 'pre_get_posts', array( __CLASS__, 'set_sort' ) );
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

		array_push( $columns, 'taxonomy-course_category', 'date_start', 'date_end', 'date_enrollment_start', 'date_enrollment_end', 'paid' );

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
			'date_start' => __( 'Start Date', 'CP_TD' ),
			'date_end' => __( 'End Date', 'CP_TD' ),
			'date_enrollment_start' => __( 'Enrollment Start', 'CP_TD' ),
			'date_enrollment_end' => __( 'Enrollment End', 'CP_TD' ),
			'units' => __( 'Units', 'CP_TD' ),
			'paid' => __( 'Paid', 'CP_TD' ),
			'students' => __( 'Students', 'CP_TD' ),
			'certificates' => __( 'Certified', 'CP_TD' ),
			'status' => __( 'Status', 'CP_TD' ),
		) );

		// Remove date column
		unset( $columns['date'] );

		if ( ! CoursePress_Data_Capabilities::can_manage_courses() ) {
			unset( $columns['cb'], $columns['actions'], $columns['units'] );
		}

		if ( ! CoursePress_Data_Capabilities::can_delete_course( 0 ) ) {
			unset( $columns['actions'] );
		}

		/**
		 * Paid column is needed?
		 */
		if ( ! CoursePress_Helper_Integration_MarketPress::$is_active && ! CoursePress_Helper_Integration_WooCommerce::$is_active ) {
			unset( $columns['paid'] );
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
			'posts_per_page' => -1, // Fixes query default limit of 10.
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
			__( 'Units', 'CP_TD' ),
			$published,
			__( 'Published', 'CP_TD' )
		);

		wp_reset_postdata();

		return $output;
	}

	public static function column_students( $item ) {
		$count = CoursePress_Data_Course::count_students( $item->ID );

		return $count;
	}

	/**
	 * Column paid
	 *
	 * @since 2.0.7
	 *
	 * @param object $item WP_Post object.
	 */
	public static function column_paid( $item ) {
		if ( CoursePress_Data_Course::is_paid_course( $item->ID ) ) {
			return sprintf( '<span class="paid">%s</span>', __( 'paid', 'CP_TD' ) );
		}
		return sprintf( '<span class="free">%s</span>', __( 'free', 'CP_TD' ) );
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
		$post_type_object = get_post_type_object( $course->post_type );
		$title = _draft_or_post_title();

		if ( self::can_update_course( $course->ID ) ) {
			$can_update = true;

			if ( 'trash' != $course->post_status ) {
				// Add edit link
				if ( ! empty( $edit_link ) ) {
					$actions['edit'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_link ), __( 'Edit', 'CP_TD' ) );
					$edit_units = add_query_arg( 'tab', 'units', $edit_link );
					$edit_students = add_query_arg( 'tab', 'students', $edit_link );
					$actions['units'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_units ), __( 'Units', 'CP_TD' ) );
					$actions['students'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_students ), __( 'Students', 'CP_TD' ) );
				}

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
					__( 'Export', 'CP_TD' )
				);
			}
		}

		if ( 'trash' != $course->post_status && CoursePress_Data_Capabilities::can_create_course( $course->ID ) ) {
			// create a nonce
			$duplicate_nonce = wp_create_nonce( 'duplicate_course' );
			$actions['duplicate'] = sprintf( '<a data-nonce="%s" data-id="%s" class="duplicate-course-link">%s</a>', $duplicate_nonce, $course->ID, __( 'Duplicate Course', 'CP_TD' ) );
		}

		if ( 'trash' != $course->post_status ) {
			if ( $can_update && self::can_delete_course( $course->ID ) ) {
				$trash_url = get_delete_post_link( $course->ID );
				$actions['trash'] = sprintf( '<a href="%s">%s</a>', esc_url( $trash_url ), __( 'Trash', 'CP_TD' ) );
			}

			$format = '<a href="%s" target="_blank">%s</a>';
			$course_url = CoursePress_Data_Course::get_course_url( $course->ID );
			$unit_url = CoursePress_Core::get_slug( 'units/' );
			$unit_overview_url = $course_url . $unit_url;

			if ( false === $published ) {
				if ( $can_update ) {
					$actions['view'] = sprintf( $format, esc_url( $course_url ), __( 'Preview Course', 'CP_TD' ) );
					$actions['preview-units'] = sprintf( $format, esc_url( $unit_overview_url ), __( 'Preview Units', 'CP_TD' ) );
				}
			} else {
				$actions['view'] = sprintf( $format, esc_url( $course_url ), __( 'View Course', 'CP_TD' ) );
				$actions['preview-units'] = sprintf( $format, esc_url( $unit_overview_url ), __( 'View Units', 'CP_TD' ) );
			}
		}

		/**
		 * Actions when course is in Trash
		 */
		if ( 'trash' == $course->post_status ) {
			if ( 'trash' === $course->post_status ) {
				$actions['untrash'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $course->ID ) ), 'untrash-post_' . $course->ID ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Restore &#8220;%s&#8221; from the Trash', 'CP_TD' ), $title ) ),
					__( 'Restore', 'CP_TD' )
				);
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					get_delete_post_link( $course->ID ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Move &#8220;%s&#8221; to the Trash', 'CP_TD' ), $title ) ),
					_x( 'Trash', 'verb', 'CP_TD' )
				);
			}
			if ( 'trash' === $course->post_status || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					get_delete_post_link( $course->ID, '', true ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Delete &#8220;%s&#8221; permanently', 'CP_TD' ), $title ) ),
					__( 'Delete Permanently', 'CP_TD' )
				);
			}
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
					<p><span class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></span> <?php printf( __( 'Deleting course <b>%s</b>, please wait!', 'CP_TD' ), '{{{data.names}}}' ); ?></p>
					<p><?php _e( 'This page will be reloaded shortly.', 'CP_TD' ); ?></p>
				</div>
			</script>
			<script type="text/html" id="tmpl-coursepress-courses-delete-more">
				<div class="notice notice-warning">
					<p><span class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></span><?php printf( __( 'Deleting %s courses, please wait!', 'CP_TD' ), '{{{data.size}}}' ); ?></p>
					<p><?php _e( 'This page will be reloaded shortly.', 'CP_TD' ); ?></p>
					<p><?php _e( 'Deleted courses:', 'CP_TD' ) ?></p>
					{{{data.names}}}
				</div>
			</script>
			<script type="text/html" id="tmpl-coursepress-courses-duplicate">
				<div class="notice notice-warning">
					<p><span class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></span> <?php printf( __( 'Duplicating course <b>%s</b>, please wait!', 'CP_TD' ), '{{{data.names}}}' ); ?></p>
					<p><?php _e( 'This page will be reloaded shortly.', 'CP_TD' ); ?></p>
				</div>
			</script>
		<?php
	}

	/**
	 * Set sort order and save it to user meta.
	 *
	 * @since 2.0.5
	 *
	 * @param
	 */
	public static function set_sort( $query ) {
		if ( ! is_admin() ) {
			return;
		}
		if ( ! $query->is_main_query() ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! isset( $screen->post_type ) || self::$post_type != $screen->post_type ) {
			return;
		}
		$orderby = $query->get( 'orderby' );
		$order = $query->get( 'order' );
		$user_id = get_current_user_id();
		/**
		 * if empty, try to get from user meta
		 */
		if ( empty( $orderby ) ) {
			$orderby = get_user_meta( $user_id, 'coursepress_admin_courses_list_orderby', true );
			$order = get_user_meta( $user_id, 'coursepress_admin_courses_list_order', true );
		}
		/**
		 * update user meta sort order
		 */
		if ( ! empty( $orderby ) ) {
			update_user_meta( $user_id, 'coursepress_admin_courses_list_orderby', $orderby );
			update_user_meta( $user_id, 'coursepress_admin_courses_list_order', $order );
		}
		/**
		 * set sort
		 */
		switch ( $orderby ) {
			case 'title':
				$query->set( 'orderby','post_title' );
			break;
			case 'date_enrollment_start':
				$query->set( 'meta_key','cp_enrollment_start_date' );
				$query->set( 'orderby','meta_value_num' );
			break;
			case 'date_start':
				$query->set( 'meta_key','cp_course_start_date' );
				$query->set( 'orderby','meta_value_num' );
			break;
		}
		if ( ! empty( $order ) ) {
			$query->set( 'order', $order );
		}
	}
}
