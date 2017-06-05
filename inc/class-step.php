<?php
/**
 * Class CoursePress_Step
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step extends CoursePress_Utility {
	/**
	 * @var CoursePress_Unit The unit where the step belongs to.
	 */
	protected $unit;

	public function __construct( $step, $unit ) {
		if ( ! $step instanceof WP_Post )
			$step = get_post( $step );

		if ( ! $step instanceof  WP_Post ) {
			$this->is_error = true;

			return;
		}

		if ( $unit instanceof CoursePress_Unit )
			$this->__set( 'unit', $unit );

		$this->__set( 'ID', $step->ID );
		$this->__set( 'post_title', $step->post_title );
		$this->__set( 'post_excerpt', $step->post_excerpt );
		$this->__set( 'post_content', $step->post_content );
		$this->__set( 'post_name', $step->post_name );

		// Setup meta-data
		$this->setUpStepMeta();
	}

	function setUpStepMeta() {
		$keys = array(
			'module_order',
			'show_title',
			'mandatory',
			'assessable',
			'use_timer',
			'allow_retries',
			'minimum_grade',
			'duration',
			'module_type',
			'module_page',
		);

		$id = $this->__get( 'ID' );

		foreach ( $keys as $key ) {
			$value = get_post_meta( $id, $key, true );

			if ( 'on' == $value )
				$value = true;

			$this->__set( $key, $value );
		}

		$this->__set( 'preview', true );
	}

	function get_permalink() {
		$module_number = $this->__get( 'module_page' );
		$post_name = $this->__get( 'post_name' );

		if ( (int) $module_number > 0 ) {
			$modules = $this->unit->get_modules();
			$module = $modules[ $module_number ];

			return $module['url'] . trailingslashit( $post_name );
		} else {
			if ( $this->unit ) {
				return $this->unit->get_unit_url() . trailingslashit( $post_name );
			}
		}
	}
}