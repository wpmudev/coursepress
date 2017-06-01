<?php
/**
 * Class CoursePress_Unit
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Unit extends CoursePress_Utility {
	public function __construct( $unit ) {
		if ( ! $unit instanceof WP_Post ) {
			$unit = get_post( $unit );
		}

		if ( ! $unit instanceof WP_Post )
			return $this->wp_error();

		foreach ( $unit as $key => $value ) {
			$this->__set( $key, $value );
		}
	}

	function wp_error() {
		return new WP_Error( 'wrong_param', __( 'Unable to initialized CoursePress_Unit!', 'cp' ) );
	}

	function get_unit_url() {
		$course_url = coursepress_get_url( $this->__get( 'post_parent' ) );
		$unit_slug = coursepress_get_setting( 'slugs/unit', 'units' );

		return $course_url . trailingslashit( $unit_slug );
	}

	function get_modules() {
		$modules = get_post_meta( $this->ID, 'course_modules' );

		if ( empty( $modules ) ) {
			$modules = array();
			// Call legacy grouping style
			$pages = get_post_meta( $this->ID, 'page_title', true );

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
			'post_parent' => $this->ID,
			'suppress_filter' => true,
		);

		if ( $with_module ) {
			$args['meta_key'] = 'page_number';
			$args['meta_value'] = $module_id;
		}

		$results = get_posts( $args );
		$steps = array();

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$step = new CoursePress_Step( $result );
				$steps[] = $step;
			}
		}

		return $steps;
	}
}