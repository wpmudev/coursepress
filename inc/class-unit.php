<?php
/**
 * Class CoursePress_Unit
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Unit extends CoursePress_Utility {
	/**
	 * @var CoursePress_Course The parent course of this unit.
	 */
	protected $course;

	public function __construct( $unit, $course ) {
		if ( ! $unit instanceof WP_Post ) {
			$unit = get_post( $unit );
		}

		if ( ! $unit instanceof WP_Post )
			return $this->wp_error();

		if ( $course instanceof CoursePress_Course )
			$this->__set( 'course', $course );

		$this->__set( 'ID', $unit->ID );
		$this->__set( 'post_title', $unit->post_title );
		$this->__set( 'post_content', $unit->post_content );
		$this->__set( 'post_name', $unit->post_name );
		$this->__set( 'post_parent', $unit->post_parent );
		$this->__set( 'course_id', $unit->post_parent );

		// Setup meta-data
		$this->setUpMeta();
	}

	function wp_error() {
		return new WP_Error( 'wrong_param', __( 'Unable to initialized CoursePress_Unit!', 'cp' ) );
	}

	function setupMeta() {
		$id = $this->__get( 'ID' );

		$keys = array(
			'unit_availability',
			'unit_date_availability',
			'unit_delay_days',
			'force_current_unit_completion',
			'force_current_unit_successful_completion',
		);

		$date_format = coursepress_get_option( 'date_format' );
		$time_now = current_time( 'timestamp' );

		foreach ( $keys as $key ) {
			$value = get_post_meta( $id, $key, true );

			if ( 'unit_date_availability' == $key ) {
				$timestamp = strtotime( $value, $time_now );
				$value = date_i18n( $date_format, $timestamp );
				$this->__set( 'unit_availability_date_timestamp', $timestamp );
				$this->__set( 'unit_availability_date', $value );
			}

			if ( 'on' == $value || 'yes' == $value )
				$value = true;

			$this->__set( $key, $value );
		}

		$this->__set( 'preview', true );
	}

	function is_available() {
		$availability = $this->__get( 'unit_availability' );
		$time_now = current_time( 'timestamp' );
		$available = false;

		if ( 'instant' == $availability ) {
			$available = true;
		} elseif ( 'on_date' == $availability ) {
			$date = $this->__get( 'unit_availability_date' );

			if ( $time_now >= $date )
				$available = true;

		} elseif ( 'after_delay' == $availability ) {
			$course_start = $this->course->__get( 'course_start_date_timestamp' );
			$days = (int) $this->__get( 'unit_delay_days' );

			if ( $days > 0 ) {
				$days = $course_start + ( $days * DAY_IN_SECONDS );

				if ( $time_now >= $days )
					$available = true;
			}
		}

		return $available;
	}

	function is_accessible_by( $user_id = 0 ) {
		$user = coursepress_get_user( $user_id );
		$available = $this->is_available();

		if ( ! $available )
			return false;

		$previousUnit = $this->__get( 'previousUnit' );

		if ( ! $previousUnit ) {
			// @todo: Get previous unit
		}

		$course_id = $this->__get( 'post_parent' );
		$unit_id = $this->__get( 'ID' );
		$force_unit_completion = $this->__get( 'force_current_unit_completion' );

		if ( $force_unit_completion
		     && ! $user->is_unit_completed( $course_id, $unit_id ) )
				return false;

		$force_unit_pass = $this->__get( 'force_current_unit_successful_completion' );

		if ( $force_unit_pass
			&& ! $user->has_pass_course_unit( $course_id, $unit_id ) )
				return false;

		return true;
	}

	function get_unit_url() {
		$course_url = coursepress_get_course_url( $this->__get( 'post_parent' ) );
		$unit_slug = coursepress_get_setting( 'slugs/units', 'units' );

		return $course_url . trailingslashit( $unit_slug ) . trailingslashit( $this->__get( 'post_name' ) );
	}

	function get_modules() {
		$id = $this->__get( 'ID' );
		$modules = get_post_meta( $id, 'course_modules' );

		if ( empty( $modules ) ) {
			$modules = array();
			// Call legacy grouping style
			$pages = get_post_meta( $id, 'page_title', true );

			if ( ! empty( $pages ) ) {
				foreach ( $pages as $page_id => $page_title ) {
					$page_id = str_replace( 'page_', '', $page_id );
					$modules[ $page_id ] = array(
						'title' => $page_title,
						'preview' => true,
					);
				}
			}
		}

		foreach ( $modules as $pos => $module ) {
			$slug = sanitize_title( $module['title'], '' );
			$module['url'] = $this->get_unit_url() . trailingslashit( $slug );
			$modules[ $pos ] = $module;
		}

		return $modules;
	}

	/**
	 * Get course modules with all it's child steps.
	 *
	 * @param bool $published
	 *
	 * @return array|mixed
	 */
	function get_modules_with_steps( $published = true ) {
		$modules = $this->get_modules();

		foreach ( $modules as $pos => $module ) {
			$module['steps'] = $this->get_steps( $published, true, $pos );
			$modules[ $pos ] = $module;
		}

		return $modules;
	}

	/**
	 * Get course steps without parent modules.
	 *
	 * @param bool $published
	 * @param bool $with_module
	 * @param bool $module_id
	 *
	 * @return array
	 */
	function get_steps( $published = true, $with_module = false, $module_id = false ) {
		$args = array(
			'post_type' => 'module',
			'post_status' => $published ? 'publish' : 'any',
			'posts_per_page' => -1,
			'post_parent' => $this->__get( 'ID' ),
			'suppress_filter' => true,
			'orderby' => 'meta_value_num',
			'order' => 'ASC',
			'meta_key' => 'module_order',
		);

		if ( $with_module ) {
			$args['meta_key'] = 'module_page';
			$args['meta_value'] = $module_id;
		}

		$results = get_posts( $args );
		$steps = array();

		if ( ! empty( $results ) ) {
			$class = array(
				'text' => 'CoursePress_Step_Text',
				'image' => 'CoursePress_Step_Image',
				'video' => 'CoursePress_Step_Video',
				'discussion' => 'CoursePress_Step_Discussion',
				'filedownload' => 'CoursePress_Step_FileDownload',
				'input-upload' => 'CoursePress_Step_FileUpload',
				'input-quiz' => 'CoursePress_Step_Quiz',
			);

			foreach ( $results as $result ) {
				$step_type = get_post_meta( $result->ID, 'module_type', true );

				$previousStep = false;
				if ( isset( $class[ $step_type ] ) ) {
					$className = $class[ $step_type ];
					$stepClass = new $className( $result, $this );
					$stepClass->__set( 'previousStep', $previousStep );
					$stepClass->__set( 'unit', $this );
					$steps[ $result->ID ] = $stepClass;
					$previousStep = $stepClass;
				}
			}
		}

		return $steps;
	}
}