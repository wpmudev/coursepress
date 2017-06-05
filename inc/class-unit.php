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

		// Setup meta-data
		$this->setUpMeta();
	}

	function wp_error() {
		return new WP_Error( 'wrong_param', __( 'Unable to initialized CoursePress_Unit!', 'cp' ) );
	}

	function setupMeta() {
		$keys = array(

		);

		$this->__set( 'preview', true );
	}

	function get_unit_url() {
		$course_url = coursepress_get_course_url( $this->__get( 'post_parent' ) );

		return $course_url . trailingslashit( $this->__get( 'post_name' ) );
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

				if ( isset( $class[ $step_type ] ) ) {
					$className = $class[ $step_type ];
					$steps[ $result->ID ] = new $className( $result, $this );
				}
			}
		}

		return $steps;
	}
}