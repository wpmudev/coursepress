<?php
/**
 * Class CoursePress_Data_Course
 *
 * @since 3.0
 * @package CoursePress
 */
final class CoursePress_Data_Course {

	private static $current = array();
	public static $structure_visibility = false;
	public static $previewability = false;
	public static $last_course_subpage = '';
	public static $last_course_category = '';
	public static $last_course_id = 0;

	/**
	 * get courses list
	 *
	 * @since 3.0.0
	 */
	public function get_list() {
		global $CoursePress_Core;
		$args = array(
			'post_type' => $CoursePress_Core->course_post_type,
			'post_status' => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => -1,
			'suppress_filters' => true,
		);
		$list = array();
		$courses = new WP_Query( $args );
		if ( $courses->have_posts() ) {
			while ( $courses->have_posts() ) {
				$courses->the_post();
				$list[ get_the_ID() ] = get_the_title();
			}
		}
		return $list;
	}

	/**
	 * Get course invitations by course ID.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Course ID.
	 * @param string $type Invitation type.
	 *
	 * @return array Array of invitations.
	 */
	public static function get_invitations_by_course_id( $course_id, $type = 'instructor' ) {

		return get_post_meta( $course_id, $type. '_invites', true );
	}

	/**
	 * Generate invitation code and hash.
	 *
	 * @param string $email Email ID.
	 *
	 * @return array
	 */
	public static function create_invite_code_hash( $email ) {

		// Generate invite code.
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$invite_code = '';
		for ( $i = 0; $i < 20; $i ++ ) {
			$invite_code .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
		}

		$data = array(
			'code' => $invite_code,
			'hash' => sha1( sanitize_email( $email ) . $invite_code ),
		);

		return $data;
	}

	/**
	 * Return the course that is associated with current page.
	 * i.e. this function returns the course ID that is currently displayed on
	 * front end.
	 *
	 * @since  2.0.0
	 *
	 * @return int The course ID or 0 if not called inside a course/unit/module.
	 */
	public static function get_current_course_id() {

		global $wp;

		if ( empty( $wp->query_vars ) ) {
			return 0;
		}

		if ( ! is_array( $wp->query_vars ) ) {
			return 0;
		}

		if ( empty( $wp->query_vars['coursename'] ) ) {
			return 0;
		}

		$coursename = $wp->query_vars['coursename'];
		$course_id = CoursePress_Data_Course::by_name( $coursename, true );

		return (int) $course_id;
	}

	/**
	 * Get course by name.
	 *
	 * @param string $slug Course slug.
	 * @param int $id_only Need only course id?
	 *
	 * @return array|bool|int|null|WP_Post
	 */
	public static function by_name( $slug, $id_only ) {

		$res = false;

		// First try to fetch the course by the slug (name).
		$args = array(
			'name' => $slug,
			'post_type' => 'course',
			'post_status' => 'any',
			'posts_per_page' => 1,
		);

		if ( $id_only ) {
			$args['fields'] = 'ids';
		}

		$post = get_posts( $args );

		if ( $post ) {
			$res = $post[0];
		} elseif ( is_numeric( $slug ) ) {
			// If we did not find a course by name, try to fetch it via ID.
			$post = get_post( $slug );

			if ( 'course' == $post->post_type ) {
				if ( $id_only ) {
					$res = $post->ID;
				} else {
					$res = $post;
				}
			}
		}

		return $res;
	}

	/**
	 * Get meta name "last seen unit".
	 *
	 * @since 2.0.4
	 * @param integer $course_id Course ID
	 *
	 * @return string
	 */
	public static function get_last_seen_unit_meta_key( $course_id ) {

		return sprintf( 'course_%s_last_seen_unit', $course_id );
	}

	/**
	 * Get key from given args.
	 *
	 * @return string
	 */
	public static function get_key() {

		$args = func_get_args();

		foreach ( $args as $pos => $arg ) {
			$arg = is_array( $arg ) ? implode( '-', $arg ) : $arg;
			$args[ $pos ] = $arg;
		}

		return implode( '_', $args );
	}

	/**
	 * Check entry - is this course?
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post|integer|null $course Variable to check.
	 * @return boolean Answer is that course or not?
	 */
	public static function is_course( $course = null ) {

		if ( empty( $course ) ) {

			$course = get_the_ID();
		}

		$course = coursepress_get_course( $course );

		return ! is_wp_error( $course );
	}

	/**
	 * Get units for the course given.
	 *
	 * @param int $course_id Course ID.
	 * @param array $status Course status.
	 * @param bool $ids_only IDs only?
	 * @param bool $include_count
	 *
	 * @return array
	 */
	public static function get_units( $course_id, $status = array( 'publish' ), $ids_only = false, $include_count = false ) {

		// Sanitize course_id.
		if ( ! self::is_course( $course_id ) ) {
			return array();
		}

		$key = self::get_key( 'course_units', $course_id, $status, $ids_only, $include_count );

		if ( ! empty( self::$current[ $key ] ) ) {
			$query = self::$current[ $key ];
		} else {
			$post_args = array(
				'post_type' => 'unit',
				'post_parent' => $course_id,
				'post_status' => $status,
				'posts_per_page' => - 1,
				'order' => 'ASC',
				'orderby' => 'meta_value_num',
				'suppress_filters' => true,
			);

			if ( $ids_only ) {
				$post_args['fields'] = 'ids';
			}

			$query = new WP_Query( $post_args );
			self::$current[ $key ] = $query;
		}

		if ( $include_count ) {
			// Handy if using pagination.
			return array(
				'units' => $query->posts,
				'found' => $query->found_posts,
			);
		} else {
			return $query->posts;
		}
	}

	/**
	 * Get units with all it's modules.
	 *
	 * @param int $course_id Course ID.
	 * @param array $status Status.
	 *
	 * @return array|mixed
	 */
	public static function get_units_with_modules( $course_id, $status = array( 'publish' ) ) {

		$key = self::get_key( 'units_with_modules', $course_id, $status );

		if ( ! empty( self::$current[ $key ] ) ) {
			return self::$current[ $key ];
		}

		$items = array();

		$course = coursepress_get_course( $course_id );
		if ( is_wp_error( $course ) ) {
			return array();
		}

		// Get units
		$units = self::get_units( $course_id, $status );

		foreach ( $units as $unit ) {
			$items = coursepress_set_array_val( $items, $unit->ID . '/order', get_post_meta( $unit->ID, 'unit_order', true ) );
			$items = coursepress_set_array_val( $items, $unit->ID . '/unit', $unit );
			$page_titles = get_post_meta( $unit->ID, 'page_title', true );
			$page_description = (array) get_post_meta( $unit->ID, 'page_description', true );
			$page_feature_image = (array) get_post_meta( $unit->ID, 'page_feature_image', true );
			$show_page_title = (array) get_post_meta( $unit->ID, 'show_page_title', true );
			$page_path = $unit->ID . '/pages';

			if ( is_array( $page_titles ) ) {
				foreach ( $page_titles as $page_id => $page_title ) {
					$page_number = str_replace( 'page_', '', $page_id );

					$items = coursepress_set_array_val( $items, $page_path . '/' . $page_number . '/title', $page_title );

					$description = ! empty( $page_description[ $page_id ] ) ? $page_description[ $page_id ] : '';

					$items = coursepress_set_array_val( $items, $page_path . '/' . $page_number . '/description', $description );
					$items = coursepress_set_array_val( $items, $page_path . '/' . $page_number . '/feature_image', ! empty( $page_feature_image[ $page_id ] ) ? $page_feature_image[ $page_id ] : '' );
					$items = coursepress_set_array_val( $items, $page_path . '/' . $page_number . '/visible', isset( $show_page_title[ $page_number - 1 ] ) ? $show_page_title[ $page_number -1 ] : false );

					$modules = self::get_unit_modules( $unit->ID, $status, false, false, array( 'page' => $page_number ) );

					uasort( $modules, array( __CLASS__, 'uasort_modules' ) );

					$items = coursepress_set_array_val( $items, $page_path . '/' . $page_number . '/modules', array() );

					foreach ( $modules as $module ) {
						$items = coursepress_set_array_val( $items, $page_path . '/' . $page_number . '/modules/' . $module->ID, $module );
					}

					ksort( $items[ $unit->ID ]['pages'], SORT_NUMERIC );
				}
			}
		}

		// Fix legacy orphaned posts and page titles
		foreach ( $items as $post_id => $unit ) {
			if ( ! isset( $unit['unit'] ) ) {
				unset( $items[ $post_id ] );
			}

			// Fix broken page titles
			$page_titles = get_post_meta( $post_id, 'page_title', true );
			if ( empty( $page_titles ) && ! empty( $unit['pages'] ) ) {
				$page_titles = array();
				$page_visible = array();
				foreach ( $unit['pages'] as $key => $page ) {
					$page_titles[ 'page_' . $key ] = $page['title'];
					$page_visible[] = true;
				}
				update_post_meta( $post_id, 'page_title', $page_titles );
				update_post_meta( $post_id, 'show_page_title', $page_visible );
			}
		}

		self::$current[ $key ] = $items;

		return $items;
	}

	/**
	 * Get modules modules.
	 *
	 * @param int $unit_id Unit ID.
	 * @param array $status
	 * @param bool $ids_only
	 * @param bool $include_count
	 * @param array $args
	 *
	 * @return array
	 */
	public static function get_unit_modules( $unit_id, $status = array( 'publish' ), $ids_only = false, $include_count = false, $args = array() ) {

		// Sanitize unit_id
		$is_unit = CoursePress_Data_Unit::is_unit( $unit_id );
		if ( ! $is_unit ) {
			return array();
		}

		$key = self::get_key( 'unit_modules', $unit_id, $status, $ids_only, $include_count, $args );

		if ( ! empty( self::$current[ $key ] ) ) {
			$query = self::$current[ $key ];
		} else {

			$post_args = array(
				'post_type' => 'module',
				'post_parent' => $unit_id,
				'post_status' => $status,
				'posts_per_page' => -1,
				'order' => 'ASC',
				'orderby' => 'meta_value_num',
			);

			if ( $ids_only ) {
				$post_args['fields'] = 'ids';
			}

			// Get modules for specific page
			if ( isset( $args['page'] ) && (int) $args['page'] ) {
				$post_args['meta_query'] = array(
					array(
						'key' => 'module_page',
						'value' => (int) $args['page'],
						'compare' => '=',
					),
				);
			}

			$query = new WP_Query( $post_args );
			self::$current[ $key ] = $query;
		}

		if ( $include_count ) {
			// Handy if using pagination.
			return array(
				'units' => $query->posts,
				'found' => $query->found_posts,
			);
		} else {
			return $query->posts;
		}
	}

	/**
	 * Course structure visibility.
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return mixed
	 */
	public static function structure_visibility( $course_id ) {

		if ( ! isset( self::$structure_visibility[ $course_id ] ) || empty( self::$structure_visibility[ $course_id ] ) ) {
			$units = array_filter( coursepress_course_get_setting( $course_id, 'structure_visible_units', array() ) );
			$pages = array_filter( coursepress_course_get_setting( $course_id, 'structure_visible_pages', array() ) );
			$modules = array_filter( coursepress_course_get_setting( $course_id, 'structure_visible_modules', array() ) );

			$visibility = array();

			foreach ( array_keys( $units ) as $key ) {
				$visibility[ $key ] = true;
			}

			foreach ( array_keys( $pages ) as $key ) {
				list( $unit, $page ) = explode( '_', $key );

				// Include only pages of existing unit
				if ( in_array( $unit, array_keys( $units ) ) ) {
					$visibility = coursepress_set_array_val( $visibility, $unit . '/' . $page , true );
				}
			}

			foreach ( array_keys( $modules ) as $key ) {
				list( $unit, $page, $module ) = explode( '_', $key );

				$is_visible = coursepress_get_array_val( $visibility, $unit . '/' . $page );

				if ( $is_visible ) {
					$visibility = coursepress_set_array_val( $visibility, $unit . '/' . $page . '/' . $module, true );
				}
			}

			self::$structure_visibility[ $course_id ]['structure']  = $visibility;

			if ( ! empty( $units ) || ! empty( $page ) || ! empty( $modules ) ) {
				self::$structure_visibility[ $course_id ]['has_visible'] = true;
			} else {
				self::$structure_visibility[ $course_id ]['has_visible'] = false;
			}
		}

		return self::$structure_visibility[ $course_id ];
	}

	/**
	 * Check if unit can be viewed.
	 *
	 * @param int $course_id Course ID.
	 * @param int $unit_id Unit ID.
	 * @param bool|int $student_id Student ID.
	 *
	 * @return bool
	 */
	public static function can_view_unit( $course_id, $unit_id, $student_id = false ) {

		if ( ! empty( self::$previewability ) ) {
			$preview = self::$previewability;
		} else {
			$preview = self::previewability( $course_id );
		}

		if ( false === $student_id ) {
			$student_id = get_current_user_id();
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$student = coursepress_get_user( $student_id );
		$enrolled = ! empty( $student ) ? $student->is_enrolled_at( $course_id ) : false;
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id, $student_id );

		if ( ! $enrolled && ! $can_update_course ) {
			$can_preview = coursepress_get_array_val( $preview, 'structure/' . $unit_id . '/unit_has_previews' );

			return coursepress_is_true( $can_preview );
		}

		return true;
	}

	/**
	 * Check if page can be viewed.
	 *
	 * @param int $course_id
	 * @param int $unit_id
	 * @param int $page
	 * @param bool $student_id
	 *
	 * @return bool
	 */
	public static function can_view_page( $course_id, $unit_id, $page = 1, $student_id = false ) {

		if ( ! empty( self::$previewability ) ) {
			$preview = self::$previewability;
		} else {
			$preview = self::previewability( $course_id );
		}

		if ( false === $student_id ) {
			$student_id = get_current_user_id();
		}

		$student = coursepress_get_user( $student_id );
		$course = coursepress_get_course( $course_id );

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$enrolled = is_wp_error( $student ) ? false : $student->is_enrolled_at( $course_id );
		$instructors = array_filter( $course->get_instructors() );
		$is_instructor = in_array( $student_id, array_keys( $instructors ) );

		$can_preview_page = isset( $preview['has_previews'] ) && isset( $preview['structure'][ $unit_id ] ) && isset( $preview['structure'][ $unit_id ][ $page ] ) && ! empty( $preview['structure'][ $unit_id ][ $page ] );
		$can_preview_page = ! $can_preview_page && isset( $preview['structure'][ $unit_id ] ) && true === $preview['structure'][ $unit_id ] ? true : $can_preview_page;
		if ( ! $enrolled && ! $can_preview_page && ! $is_instructor ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if module can be viewed.
	 *
	 * @param int $course_id
	 * @param int $unit_id
	 * @param int $module_id
	 * @param int $page
	 * @param bool $student_id
	 *
	 * @return bool
	 */
	public static function can_view_module( $course_id, $unit_id, $module_id, $page = 1, $student_id = false ) {

		if ( ! empty( self::$previewability ) ) {
			$preview = self::$previewability;
		} else {
			$preview = self::previewability( $course_id );
		}

		if ( false === $student_id ) {
			$student_id = get_current_user_id();
		}

		$student = coursepress_get_user( $student_id );
		$course = coursepress_get_course( $course_id );

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$enrolled = is_wp_error( $student ) ? false : $student->is_enrolled_at( $course_id );
		$instructors = array_filter( $course->get_instructors() );
		$is_instructor = in_array( $student_id, array_keys( $instructors ) );

		$preview_modules = array();
		if ( isset( $preview['structure'][ $unit_id ][ $page ] ) && is_array( $preview['structure'][ $unit_id ][ $page ] ) ) {
			$preview_modules = array_keys( $preview['structure'][ $unit_id ][ $page ] );
		}
		$can_preview_module = in_array( $module_id, $preview_modules ) || ( isset( $preview['structure'][ $unit_id ] ) && ! is_array( $preview['structure'][ $unit_id ] ) );

		if ( ! $enrolled && ! $can_preview_module && ! $is_instructor ) {
			return false;
		}

		return true;
	}

	/**
	 * Get prevewability.
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return bool
	 */
	public static function previewability( $course_id ) {

		if ( empty( self::$previewability ) ) {
			$units = array_filter( coursepress_course_get_setting( $course_id, 'structure_preview_units', array() ) );
			$pages = array_filter( coursepress_course_get_setting( $course_id, 'structure_preview_pages', array() ) );
			$modules = array_filter( coursepress_course_get_setting( $course_id, 'structure_preview_modules', array() ) );

			$preview_structure = array();

			foreach ( array_keys( $units ) as $key ) {
				$preview_structure[ $key ] = true;
			}

			foreach ( array_keys( $pages ) as $key ) {
				list( $unit, $page ) = explode( '_', $key );
				$preview_structure = coursepress_set_array_value( $preview_structure, $unit . '/' . $page, true );
				$preview_structure = coursepress_set_array_value( $preview_structure, $unit . '/unit_has_previews', true );
			}

			foreach ( array_keys( $modules ) as $key ) {
				list( $unit, $page, $module ) = explode( '_', $key );
				$preview_structure = coursepress_set_array_value( $preview_structure, $unit . '/' . $page . '/' . $module, true );
				$preview_structure = coursepress_set_array_value( $preview_structure, $unit . '/' . $page . '/page_has_previews', true );
				$preview_structure = coursepress_set_array_value( $preview_structure, $unit . '/unit_has_previews', true );
			}

			self::$previewability['structure'] = $preview_structure;

			if ( ! empty( $units ) || ! empty( $page ) || ! empty( $modules ) ) {
				self::$previewability['has_previews'] = true;
			} else {
				self::$previewability['has_previews'] = false;
			}
		}

		return self::$previewability;
	}

	/**
	 * Get course status.
	 *
	 * @param $course_id
	 *
	 * @return string
	 */
	public static function get_course_status( $course_id ) {

		global $CoursePress_Core;

		// Sanitize course_id.
		if ( ! self::is_course( $course_id ) ) {
			return 'unknown';
		}

		$start_date = coursepress_course_get_setting( $course_id, 'course_start_date', 0 );
		$end_date = coursepress_course_get_setting( $course_id, 'course_end_date', 0 );
		$open_ended = coursepress_course_get_setting( $course_id, 'course_open_ended', 0 );
		$now = $CoursePress_Core->date_time_now();
		$status = 'open';
		if ( ! empty( $end_date ) ) {
			$end_date += DAY_IN_SECONDS;
		}

		if ( $start_date > 0 && $start_date > $now ) {
			$status = 'future';
		} elseif ( ! $open_ended && ! empty( $end_date ) && $end_date < $now ) {
			$status = 'closed';
		}

		return $status;
	}

	/**
	 * Set last course id.
	 *
	 * @param int $course_id Course ID.
	 */
	public static function set_last_course_id( $course_id ) {

		self::$last_course_id = intval( $course_id );
	}

	/**
	 * Set last course id.
	 *
	 * @param int|WP_Post $post
	 */
	public static function set_the_course( $post ) {

		if ( is_object( $post ) ) {
			self::set_last_course_id( (int) $post->ID );
		} else {
			self::set_last_course_id( (int) $post );
		}
	}

	/**
	 * Get course class string.
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id User ID.
	 *
	 * @return array
	 */
	public static function course_class( $course_id, $user_id = 0 ) {

		global $CoursePress_Core;

		// Sanitize course_id.
		if ( ! self::is_course( $course_id ) ) {
			return array();
		}

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$course = coursepress_get_course( $course_id );
		$user = coursepress_get_user( $user_id );

		$is_course_available = $course->is_available();
		$now = $CoursePress_Core->date_time_now();
		$status = array( 'course-list-box' );
		$is_completed = false;
		$start_date = coursepress_course_get_setting( $course_id, 'course_start_date' );
		$start_date = $CoursePress_Core->strtotime( $start_date );
		$open_ended = coursepress_course_get_setting( $course_id, 'course_open_ended' );
		$end_date = coursepress_course_get_setting( $course_id, 'course_end_date' );
		$end_date = $CoursePress_Core->strtotime( $end_date );
		$has_ended = false == coursepress_is_true( $open_ended ) && $end_date < $now;
		$course_image = coursepress_course_get_setting( $course_id, 'listing_image' );
		$is_enrolled = false;

		if ( empty( $course_image ) ) {
			$status[] = 'no-thumb';
		}

		if ( $user_id > 0 ) {
			$is_enrolled = $user->is_enrolled_at( $course_id );

			if ( $is_enrolled ) {
				$is_completed = $user->is_course_completed( $course_id );
			}
		}

		if ( $is_course_available && false === $has_ended ) {
			$status[] = 'course-available';
		} else {

			if ( $start_date > $now ) {
				$status[] = 'course-starting-soon';
			} else {

				if ( $end_date > 0 && $end_date <= $now ) {
					if ( $is_enrolled && ! $is_completed ) {
						$status[] = 'course-incomplete';
					}
				}
			}
		}

		if ( $user_id > 0 ) {
			if ( coursepress_is_true( $is_enrolled ) ) {
				$status[] = 'student-enrolled';

				if ( $is_completed ) {
					$status[] = 'course-completed';
				}
			}
		}

		return $status;
	}

	/**
	 * Get course availability status.
	 *
	 * @param int $course_id Course ID.
	 * @param int $user_id User ID.
	 *
	 * @return mixed|string|void
	 */
	public static function get_course_availability_status( $course_id ) {

		global $CoursePress_Core;

		$course = coursepress_get_course( $course_id );
		$is_course_available = $course->is_available();
		$date_format = get_option( 'date_format' );
		$now = $CoursePress_Core->date_time_now();
		$status = '';

		if ( ! $is_course_available ) {
			$start_date = coursepress_course_get_setting( $course_id, 'course_start_date' );
			$start_date = $CoursePress_Core->strtotime( $start_date );

			if ( $start_date > $now ) {
				$status = sprintf( __( 'This course will open on %s', 'cp' ), date_i18n( $date_format, $start_date ) );
			} else {
				// Check if it has end date
				$is_open_ended = coursepress_course_get_setting( $course_id, 'course_open_ended' );
				$end_date = coursepress_course_get_setting( $course_id, 'course_end_date' );
				$status = $end_date;

				if ( ! $is_open_ended && ! empty( $end_date ) ) {
					$end_date = $CoursePress_Core->strtotime( $end_date );

					if ( $end_date < $now ) {
						$status = __( 'This course is already closed.', 'cp' );
					}
				}
			}
		}

		if ( ! empty( $status ) ) {
			/**
			 * Filter status messages.
			 *
			 * @since 2.0
			 *
			 * @param (string) $status		The status message.
			 * @param (int) $course_id
			 **/
			$status = apply_filters( 'coursepress_course_availability_status', $status, $course_id );
		}

		return $status;
	}

	/**
	 * Helper function to get IDs
	 *
	 * @param WP_Post $course
	 *
	 * @return bool|int
	 **/
	public static function get_course_id( $course ) {

		if ( is_a( $course, 'WP_Post' ) ) {
			return $course->ID;
		}

		return false;
	}

	/**
	 * Check if current course, unit, or module is accessable.
	 *
	 * @since 2.0.0
	 *
	 * @param int $course_id Course ID.
	 * @param int $unit_id Unit ID.
	 * @param int $module_id Module ID.
	 * @param int $student_id Student ID.
	 * @param int $page Page ID.
	 *
	 * @return string
	 */
	public static function can_access( $course_id, $unit_id = 0, $module_id = 0, $student_id = 0, $page = 1 ) {

		global $CoursePress_Core;

		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}

		$student = coursepress_get_user( $student_id );

		 // Sanitize $course_id.
		if ( ! self::is_course( $course_id ) ) {
			return '';
		}

		$date_format = get_option( 'date_format' );
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id );
		$page = ! $page ? 1 : $page;

		// If administrator or instructor, bail
		if ( $can_update_course ) {
			return '';
		}

		// Check if the course is already available
		$error_message = self::get_course_availability_status( $course_id );

		if ( empty( $error_message ) ) {
			if ( ! empty( $unit_id ) ) {
				$previous_unit_id = CoursePress_Data_Unit::get_previous_unit_id( $course_id, $unit_id );
				$is_unit_available = CoursePress_Data_Unit::is_unit_available( $course_id, $unit_id, $previous_unit_id );

				if ( ! $is_unit_available ) {
					$unit_availability_date = CoursePress_Data_Unit::get_unit_availability_date( $unit_id, $course_id );

					if ( ! empty( $unit_availability_date ) ) {
						$error_message = sprintf( __( 'This unit will be available on %s', 'cp' ), date_i18n( $date_format, $CoursePress_Core->strtotime( $unit_availability_date ) ) );
					} else {
						if ( $previous_unit_id > 0 ) {
							$shortcode = sprintf( '[module_status unit_id="%s" previous_unit="%s"]', $unit_id, $previous_unit_id );
							$error_message = strip_tags( do_shortcode( $shortcode ) );
						}
					}
				}

				if ( empty( $error_message ) && ! empty( $previous_unit_id ) ) {
					$previous_modules = self::get_unit_modules( $previous_unit_id, array( 'publish' ) );
					$previous_modules = array_map( array( __CLASS__, 'get_course_id' ), $previous_modules );

					if ( $previous_modules ) {
						foreach ( $previous_modules as $prev_module_index => $_module_id ) {
							$is_done = CoursePress_Data_Module::is_module_done_by_student( $_module_id, $student_id );

							if ( ! $is_done ) {
								$first_line = __( 'You need to complete all the REQUIRED modules before this unit.', 'cp' );
								$error_message = $CoursePress_Core->get_message_required_modules( $first_line );
								continue;
							}
						}
					}
				}

				if ( empty( $error_message ) && $page > 1 ) {
					// Get previous modules
					$previous_modules = array();

					for ( $i = 1; $i < $page; $i++ ) {
						$prev_section = self::get_unit_modules( $unit_id, array( 'publish' ), false, false, array( 'page' => $i ) );
						$previous_modules = array_merge( $previous_modules, $prev_section );
					}
					$previous_modules = array_map( array( __CLASS__, 'get_course_id' ), $previous_modules );

					foreach ( $previous_modules as $prev_module_index => $_module_id ) {
						$is_done = CoursePress_Data_Module::is_module_done_by_student( $_module_id, $student_id );

						if ( ! $is_done ) {
							$first_line = __( 'You need to complete all the REQUIRED modules before this section.', 'cp' );
							$error_message = $CoursePress_Core->get_message_required_modules( $first_line );
							continue;
						}
					}
				}

				$modules = self::get_unit_modules( $unit_id, array( 'publish' ), false, false, array( 'page' => (int) $page ) );
				$modules = array_map( array( __CLASS__, 'get_course_id' ), $modules );
				$modules = self::reorder_modules( $modules );
				$module_index = 0;
				foreach ( $modules as $index => $_module_id ) {
					if ( $module_id == $_module_id ) {
						$module_index = $index;
					}
				}
				if ( $module_index > 0 ) {
					$modules = array_slice( $modules, 0, $module_index );
					// Remove the last module
					array_pop( $modules );
				} else {
					$modules = array();
				}
				if ( count( $modules ) ) {
					foreach ( $modules as $module_index => $_module_id ) {
						$is_done = CoursePress_Data_Module::is_module_done_by_student( $_module_id, $student_id );

						if ( ! $is_done ) {
							$first_line = __( 'You need to complete all the REQUIRED modules before this module.', 'cp' );
							$error_message = $CoursePress_Core->get_message_required_modules( $first_line );
							continue;
						} else {
							/**
							 * Check current student pass the minimum grade requirement.
							 **/
							$attributes = CoursePress_Data_Module::attributes( $_module_id );
							$is_assessable = $attributes['assessable'];
							$is_required = $attributes['mandatory'];
							$module_type = $attributes['module_type'];

							if ( coursepress_is_true( $is_assessable ) && coursepress_is_true( $is_required ) ) {
								$minimum_grade = $attributes['minimum_grade'];
								$grade = $student->get_step_grade( $course_id, $unit_id, $_module_id );
								$pass = (int) $grade >= (int) $minimum_grade;
								$excluded_modules = array(
									'input-textarea',
									'input-text',
								);

								if ( ! $pass && ! in_array( $module_type, $excluded_modules ) ) {
									$first_line = __( 'You need to complete all the REQUIRED modules before this module.', 'cp' );
									$error_message = $CoursePress_Core->get_message_required_modules( $first_line );
									continue;
								}
							}
						}
					}
				}
			}
		}

		/**
		 * Filter the error message to show
		 *
		 * @since 2.0
		 *
		 * @param (string) $error_message
		 * @param (int) $course_id
		 * @param (int) $unit_id
		 * @param (int) $module_id
		 **/
		$error_message = apply_filters( 'coursepress_inaccessable_error_message', $error_message, $course_id, $unit_id, $module_id );

		return $error_message;
	}

	/**
	 * Reorder given modules.
	 *
	 * @param array $results
	 *
	 * @return array
	 */
	public static function reorder_modules( $results ) {

		$posts = array();

		if ( is_array( $results ) ) {
			foreach ( $results as $post ) {
				$post_id = is_object( $post ) ? $post->ID : $post;
				$module_order = (int) get_post_meta( $post_id, 'module_order', true );
				if ( isset( $posts[ $module_order ] ) ) {
					$module_order++;
				}
				$posts[ $module_order ] = $post;
			}
		}

		ksort( $posts );

		// Recalculate indexes!
		$i = 1;
		$reordered_posts = array();
		foreach ( $posts as $post ) {
			$reordered_posts[ $i++ ] = $post;
		}

		return $reordered_posts;
	}

	/**
	 * Return the module ID of the next available module.
	 *
	 * @since 2.0.0
	 *
	 * @param int $course_id
	 * @param int $unit_id
	 * @param int $current_page
	 * @param in  $current_module
	 *
	 * @return int ID of next available module.
	 */
	public static function get_next_accessible_module( $course_id, $unit_id, $current_page = 1, $current_module = 0 ) {

		$next = array( 'id' => false );

		// Sanitize $course_id
		if ( ! self::is_course( $course_id ) ) {
			return $next;
		}

		$current_page = (int) $current_page > 1 ? $current_page : 1;

		$nav_sequence = self::get_course_navigation_items( $course_id );

		// Remove "prev" items from the nav-sequence
		$new_sequence = array();
		$valid = false;
		foreach ( $nav_sequence as $ind => $item ) {
			if ( $valid ) {
				$new_sequence[] = $item;
			}

			if ( $unit_id == $item['unit'] ) {
				if ( $current_page == $item['id'] ) {
					$valid = true;
				}
			}
		}

		if ( $current_module > 0 ) {
			$valid = false;
			$new_sequence2 = array();

			foreach ( $new_sequence as $ind => $item ) {
				if ( $valid ) {
					$new_sequence2[] = $item;
				}

				if ( $item['id'] == $current_module ) {
					$valid = true;
				}
			}

			$new_sequence = $new_sequence2;
		}

		$nav_sequence = $new_sequence;

		// Return the next item in the navigation sequence.
		if ( count( $nav_sequence ) > 0 ) {
			$next = $nav_sequence[0];
		}

		return $next;
	}

	/**
	 * Return the module ID of the previous available module.
	 *
	 * @since  2.0.0
	 *
	 * @param int $course_id
	 * @param int $unit_id
	 * @param int $current_page
	 * @param int $current_module
	 *
	 * @return int ID of next available module.
	 */
	public static function get_prev_accessible_module( $course_id, $unit_id, $current_page = 1, $current_module = 0 ) {

		// Sanitize $course_id.
		if ( ! self::is_course( $course_id ) ) {
			return false;
		}

		$nav_sequence = self::get_course_navigation_items( $course_id );
		$current_index = self::_get_current_index( $nav_sequence, $unit_id, $current_page, $current_module );

		// Check and remove units, sections, or modules that are not yet accessible.
		$new_sequence = array();
		$valid = true;
		$hide_section = coursepress_course_get_setting( $course_id, 'focus_hide_section', false );

		foreach ( $nav_sequence as $item ) {
			if ( 'completion_page' === $item['id'] ) {
				continue;
			}

			if ( 'unit' == $hide_section && 'section' == $item['type'] ) {
				continue;
			}
			if ( $valid ) {
				$new_sequence[] = $item;
			}

			if ( $current_module ) {
				if ( $current_module == $item['id'] ) {
					$valid = false;
					array_pop( $new_sequence );
				}
			} else {
				if ( $unit_id == $item['unit'] && $current_page == $item['id'] ) {
					$valid = false;
				}
			}
		}

		$nav_sequence = $new_sequence;

		if ( 1 > $current_index || $current_index > count( $nav_sequence ) ) {
			$current_index = count( $nav_sequence );
		}

		return $nav_sequence[ $current_index - 1 ];
	}

	/**
	 * Get current index in navigation units/modules list.
	 *
	 * @since 2.0.0
	 *
	 * @access private
	 *
	 * @param array $nav_sequence Whole navigation over course.
	 * @param integer $unit_id Unit ID.
	 * @param integer $current_page Currently dislayed page.
	 * @param integer $current_module Currently displayed module (0 if section)
	 *
	 * @return integer current index in $nav_sequence.
	 */
	private static function _get_current_index( $nav_sequence, $unit_id, $current_page, $current_module ) {

		foreach ( $nav_sequence as $ind => $item ) {
			switch ( $item['type'] ) {
				case 'module':
					if ( ! $current_module ) { break; }
					if ( $current_module != $item['id'] ) { break; }
					return $ind;

				case 'section':
					if ( 0 != $current_module ) { break; }
					if ( $unit_id != $item['unit'] ) { break; }
					if ( $current_page != $item['id'] ) { break; }
					return $ind;
			}
		}

		return 0;
	}

	/**
	 * Returns a flat, ordered array of all navigation items in the course.
	 *
	 * i.e. list of units / sections / modules in the correct sequene for the
	 * next/prev navigation.
	 *
	 * @since 2.0.0
	 *
	 * @param int  $course_id The course.
	 * @param bool $for_preview If true then only return previewable items.
	 *
	 * @return array Ordered list of navigation points.
	 */
	public static function get_course_navigation_items( $course_id ) {

		static $Items = array();
		/**
		 * Sanitize $course_id
		 */
		if ( ! self::is_course( $course_id ) ) {
			return false;
		}
		if ( ! isset( $Items[ $course_id ] ) ) {
			$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id );
			$student_id = get_current_user_id();
			$instructors = array_filter( CoursePress_Data_Course::get_instructors( $course_id ) );
			$is_instructor = in_array( $student_id, $instructors );
			$is_enrolled = CoursePress_Data_Course::student_enrolled( $student_id, $course_id );
			$has_full_access = false;
			$is_student = false;

			// 1. Find out if we need to return preview items or full item list.
			//
			if ( $can_update_course ) {
				// User is admin or instructor, he can access all modules.
				$has_full_access = true;
			} elseif ( $is_instructor ) {
				// User is instructor, he can access all modules.
				$has_full_access = true;
			} elseif ( $is_enrolled ) {
				// User is enrolled to the course, allow access to all modules.
				$has_full_access = true;
				$is_student = true;
			}

			// 2. Generate the list of navigation items.
			$items = array();
			$course_link = self::get_course_url( $course_id );
			$unit_url = CoursePress_Core::get_slug( 'units/' );
			$units_overview_url = $course_link . $unit_url;

			// First node always is the course overview (clicking prev on first page).
			$items[] = array(
				'id' => $course_id,
				'type' => 'course',
				'section' => 0,
				'unit' => 0,
				'url' => trailingslashit( $units_overview_url ),
				'course_id' => $course_id,
			);

			if ( $has_full_access ) {
				$statuses = $can_update_course ? array( 'publish', 'private', 'draft' ) : array( 'publish' );
				$units = CoursePress_Data_Course::get_units_with_modules( $course_id, $statuses );
				$units = CoursePress_Helper_Utility::sort_on_key( $units, 'order' );
				$prev_unit_id = false;
				$unit_restricted = false;

				// Get a full list of all modules in the course.
				foreach ( $units as $unit_id => $unit ) {

					if ( $is_student ) {
						// For students we observe the available-date options.
						// Note: If not a student, the user is admin/instructor.
						$is_available = CoursePress_Data_Unit::is_unit_available(
							$course_id,
							$unit_id,
							$prev_unit_id
						);

						$prev_unit_id = $unit_id;

						if ( ! $is_available && ! $unit_restricted ) {
							$is_available = true;
							//$unit_restricted = true;
						}

						if ( ! $is_available ) { continue; }
					}

					$unit_link = CoursePress_Data_Unit::get_unit_url( $unit_id );

					if ( empty( $unit['pages'] ) ) {
						$unit['pages'] = array();
					}

					foreach ( $unit['pages'] as $page_id => $page ) {
						$page_link = sprintf( '%spage/%s', $unit_link, $page_id );

						$items[] = array(
							'id' => $page_id,
							'type' => 'section',
							'unit' => $unit_id,
							'url' => $page_link,
							'restricted' => $unit_restricted,
							'course_id' => $course_id,
						);

						foreach ( $page['modules'] as $module_id => $module ) {
							$module_link = sprintf( '%spage/%s/module_id/%s', $unit_link, $page_id, $module_id );//sprintf( '%s#module-%s', $page_link, $module_id );

							$items[] = array(
								'course_id' => $course_id,
								'id' => $module_id,
								'type' => 'module',
								'section' => $page_id,
								'unit' => $unit_id,
								'url' => $module_link,
								'restricted' => $unit_restricted,
							);
						}
					}
				}

				$completion_url = $course_link . trailingslashit( CoursePress_Core::get_slug( 'completion' ) );
				$completion_page = array(
					'id' => 'completion_page',
					'type' => 'section',
					'section' => null,
					'unit' => true,
					'url' => $completion_url,
				);
				array_push( $items, $completion_page );
			} else {
				// Get a list of all previewable modules in the course.
				$preview_course = CoursePress_Data_Course::get_setting(
					$course_id,
					'structure_preview_modules',
					array()
				);

				foreach ( $preview_course as $key => $flag ) {
					if ( empty( $flag ) ) { continue; }
					list( $unit, $page, $module ) = explode( '_', $key );

					$items[] = array(
						'id' => $module,
						'type' => 'module',
						'section' => isset( $section )? $section : null,
						'unit' => $unit,
					);
				}
			}

			$Items[ $course_id ] = $items;
		}

		return $Items[ $course_id ];
	}

	/**
	 * Get unread messages count.
	 *
	 * @return null|string
	 */
	public static function get_unread_messages_count() {

		global $wpdb;

		$sql = '
		SELECT COUNT(1)
		FROM ' . $wpdb->base_prefix . 'messages
		WHERE message_to_user_ID = %d AND message_status = %s
		';

		$tmp_unread_message_count = $wpdb->get_var( $wpdb->prepare( $sql, get_current_user_id(), 'unread' ) );

		return $tmp_unread_message_count;
	}

	/**
	 * Check if pages are allowed.
	 *
	 * @param int $course_id
	 *
	 * @return array
	 */
	public static function allow_pages( $course_id ) {

		$pages = array(
			'course_discussion' => coursepress_is_true( coursepress_course_get_setting( $course_id, 'allow_discussion', true ) ),
			'workbook' => coursepress_is_true( coursepress_course_get_setting( $course_id, 'allow_workbook', true ) ),
			'grades' => coursepress_is_true( coursepress_course_get_setting( $course_id, 'allow_grades', true ) ),
		);

		return $pages;
	}

	/**
	 * Get course time estimate.
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return string
	 */
	public static function get_time_estimation( $course_id ) {

		$units = self::get_units_with_modules( $course_id );

		$seconds = 0;
		$minutes = 0;
		$hours = 0;

		foreach ( $units as $unit ) {
			$estimations = CoursePress_Data_Unit::get_time_estimation( $unit['unit']->ID, $units );
			$components = explode( ':', $estimations['unit']['estimation'] );

			$part = array_pop( $components );
			$seconds += ! empty( $part ) ? (int) $part : 0;
			$part = count( $components > 0 ) ? array_pop( $components ) : 0;
			$minutes += ! empty( $part ) ? (int) $part : 0;
			$part = count( $components > 0 ) ? array_pop( $components ) : 0;
			$hours += ! empty( $part ) ? (int) $part : 0;
		}

		$total_seconds = $seconds + ( $minutes * 60 ) + ( $hours * 3600 );

		$hours = floor( $total_seconds / 3600 );
		$total_seconds = $total_seconds % 3600;
		$minutes = floor( $total_seconds / 60 );
		$seconds = $total_seconds % 60;

		$estimation = sprintf( '%02d:%02d:%02d', $hours, $minutes, $seconds );

		return $estimation;
	}
}
