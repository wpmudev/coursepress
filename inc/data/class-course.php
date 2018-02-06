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
}
