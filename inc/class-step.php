<?php
/**
 * Class CoursePress_Step
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step extends CoursePress_Unit {
	/**
	 * @var CoursePress_Unit The unit where the step belongs to.
	 */
	protected $unit;

	public function __construct( $step, $unit = false ) {
		if ( ! $step instanceof WP_Post ) {
			$step = get_post( $step ); }
		if ( ! $step instanceof  WP_Post ) {
			$this->is_error = true;
			return;
		}
		$this->__set( 'ID', $step->ID );
		$this->__set( 'post_title', $step->post_title );
		$this->__set( 'post_excerpt', $step->post_excerpt );
		$this->__set( 'post_content', $step->post_content );
		$this->__set( 'post_name', $step->post_name );
		$this->__set( 'unit_id', $step->post_parent );
		$this->__set( 'post_parent', $step->post_parent );
		$this->__set( 'menu_order', $step->menu_order );
		$this->__set( 'course_id', get_post_field( 'post_parent', $step->post_parent ) );
		// Setup meta-data
		$this->setUpStepMeta();
	}

	protected function get_keys() {
		$keys = array(
			'module_order',
			'show_title',
			'mandatory',
			'assessable',
			'use_timer',
			'allow_retries',
			'retry_attempts',
			'minimum_grade',
			'duration',
			'module_type',
			'module_page',
			'show_content',
			'allowed_file_types',
			'preview',
		);
		return $keys;
	}

	public function setUpStepMeta() {
		$keys = $this->get_keys();
		$id = $this->__get( 'ID' );
		foreach ( $keys as $key ) {
			$value = get_post_meta( $id, $key, true );
			if ( is_array( $value ) ) {
				$value = $this->to_array( $value );
			}
			if ( 'on' == $value ) {
				$value = true;
			}
			if ( 'module_type' == $key ) {
				if ( 'input-checkbox' == $value ) {
					$value = 'input-quiz';
				}
			}
			$this->__set( $key, $value );
			$this->__set( 'meta_' . $key, $value );
		}
	}

	public function get_settings() {
		$keys = $this->get_keys();
		$settings = array();
		foreach ( $keys as $key ) {
			$value = $this->__get( $key );
			$settings[ $key ] = $value;
		}
		return $settings;
	}

	public function update_settings( $key, $value ) {
		$step_id = $this->__get( 'ID' );
		$settings = $this->get_settings();
		if ( true === $key ) {
			$settings = $value;
			foreach ( $settings as $key => $value ) {
				update_post_meta( $step_id, $key, $value );
				$this->__set( $key, $value );
			}
		} else {
			$settings[ $key ] = $value;
			update_post_meta( $step_id, $key, $value );
			$this->__set( $key, $value );
		}
		return $settings;
	}

	public function get_the_title() {
		return $this->__get( 'post_title' );
	}

	public function get_unit() {
		$unit_id = $this->__get( 'post_parent' );
		return coursepress_get_unit( $unit_id );
	}

	public function get_permalink() {
		$module_number = $this->__get( 'module_page' );
		if ( ! $module_number ) {
			$module_number = 1;
		}
		$post_name = $this->__get( 'post_name' );
		$unit = $this->get_unit();
		$course = $unit->get_course();
		$with_modules = $course->is_with_modules();
		if ( $with_modules && (int) $module_number > 0 ) {
			$modules = $unit->get_modules();
			if ( ! empty( $modules ) && $modules[ $module_number ] ) {
				$module = $modules[ $module_number ];
				return $module['url'] . trailingslashit( $post_name );
			}
		} else if ( $unit ) {
			$slug = coursepress_get_setting( 'slugs/step', 'step' );
			return $unit->get_permalink() . trailingslashit( $slug ) . trailingslashit( $post_name );
		}
		return '';
	}

	public function is_answerable() {
		$module_type = $this->__get( 'module_type' );
		$is_answerable = preg_match( '%input-%', $module_type );
		return $is_answerable;
	}

	public function has_seen_by( $user_id ) {
		$user = coursepress_get_user( $user_id );
		$step_id = $this->__get( 'ID' );
		$course_id = $this->__get( 'course_id' );
		$unit_id = $this->__get( 'unit_id' );
		if ( is_wp_error( $user )
			|| ! $user->is_enrolled_at( $course_id ) ) {
			return false;
		}
		$progress = $user->get_completion_data( $course_id );
		return coursepress_get_array_val( $progress, 'completion/' . $unit_id . '/modules_seen/' . $step_id );
	}

	public function is_completed_by( $user_id = 0 ) {
		$user = coursepress_get_user( $user_id );
		$step_id   = $this->__get( 'ID' );
		$course_id = $this->__get( 'course_id' );
		$unit_id   = $this->__get( 'unit_id' );
		if ( is_wp_error( $user )
		     || ! $user->is_enrolled_at( $course_id )
		) {
			return false;
		}
		$step_progress = $user->get_step_progress( $course_id, $unit_id, $step_id );
		return (int) $step_progress >= 100;
	}

	public function is_previous_step_completed_by( $user_id = 0 ) {
		$user = coursepress_get_user( $user_id );
		$course_id = $this->__get( 'course_id' );
		if ( is_wp_error( $user )
			|| ! $user->is_enrolled_at( $course_id ) ) {
			return false;
		}
		if ( ( $prev = $this->__get( 'previousStep' ) ) ) {
			return $prev->has_completed_by( $user_id );
		}
		return true;
	}

	public function is_show_title() {
		return $this->__get( 'show_title' );
	}

	public function is_show_content() {
		return $this->__get( 'show_content' );
	}

	public function is_required() {
		return $this->__get( 'mandatory' );
	}

	public function is_assessable() {
		return $this->__get( 'assessable' );
	}

	public function get_course() {
		$unit = $this->get_unit();
		return $unit->get_course();
	}

	public function get_user_response( $user_id = 0 ) {
		$user = coursepress_get_user( $user_id );
		$unit = $this->get_unit();
		$course = $unit->get_course();
		$response = $user->get_response( $course->ID, $unit->ID, $this->__get( 'ID' ) );
		return ( isset( $response['response'] ) && ! empty( $response['response'] ) ) ? $response['response'] : false;
	}

	public function get_user_attempts( $user_id = 0 ) {
		$user = coursepress_get_user( $user_id );
		$unit = $this->get_unit();
		$course = $unit->get_course();
		$response = $user->get_response( $course->ID, $unit->ID, $this->__get( 'ID' ) );
		return ( isset( $response['attempts'] ) && ! empty( $response['attempts'] ) ) ? $response['attempts'] : 0;
	}

	public function get_previous_step() {
		$user = coursepress_get_user();
		$unit = $this->get_unit();
		$course = $unit->get_course();
		$with_modules = $course->is_with_modules();
		$has_access = $user->has_access_at( $course->__get( 'ID' ) );
		$module_page = $this->__get( 'module_page' );
		$steps = $unit->get_steps( ! $has_access, $with_modules, $module_page );
		$prev = false;
		if ( $steps ) {
			$previous = array();
			foreach ( $steps as $step ) {
				$previous[] = $step;
				if ( $step->__get( 'ID' ) == $this->__get( 'ID' ) ) {
					break;
				}
			}
			array_pop( $previous );
			$prev = array_pop( $previous );
		}
		return $prev;
	}

	public function is_preview() {
		return ! empty( $_REQUEST['preview'] );
	}

	public function get_next_step() {
		$user = coursepress_get_user();
		$unit = $this->get_unit();
		$course = $unit->get_course();
		$with_modules = $course->is_with_modules();
		$has_access = $user->has_access_at( $course->__get( 'ID' ) );
		$module_page = $this->__get( 'module_page' );
		$steps = $unit->get_steps( ! $has_access, $with_modules, $module_page );
		$next = false;
		if ( $steps ) {
			$found = false;
			foreach ( $steps as $step ) {
				if ( $found ) {
					$next = $step;
					break;
				}
				if ( $step->__get( 'ID' ) == $this->__get( 'ID' ) ) {
					$found = true;
				}
			}
		}
		return $next;
	}

	/** Must be overriden in a sub class */
	public function get_question() {}

	/** Must be overriden in a sub class */
	public function get_answer_template( $user_id = 0 ) {
		$template = '';
		if ( $this->is_answerable() ) {
			$unit = $this->get_unit();
			$course = $unit->get_course();
			$user = coursepress_get_user( $user_id );
			$status = $user->get_step_grade_status( $course->ID, $unit->ID, $this->ID );
			$statuses = array(
				'failed' => __( 'Failed', 'cp' ),
				'pass' => __( 'Pass', 'cp' ),
				'pending' => __( 'Pending', 'cp' ),
			);
			if ( ! empty( $status ) ) {
				$template .= coursepress_create_html(
					'span',
					array( 'class' => 'step-status step-status-' . $status ),
					$statuses[ $status ]
				);
			}
			$allow_retries = $this->__get( 'allow_retries' );
			$retry_attempts = $this->__get( 'retry_attempts' );
			$total_allowed_attempts = $retry_attempts + 1;
			if ( 'pass' !== $status && $allow_retries && ($this->get_user_attempts() < $total_allowed_attempts || $retry_attempts == 0) ) {
				$template .= coursepress_create_html(
					'button',
					array(
						'type' => 'button',
						'class' => 'button cp-button cp-button-retry',
					),
					__( 'Retry', 'cp' )
				);
			}
		}
		return $template;
	}

	public function validate_response( $response = array() ) {}

	public function template( $user_id = 0 ) {
		$template = '';
		$user = coursepress_get_user( $user_id );
		$course = coursepress_get_course();
		$course_id = $course->__get( 'ID' );
		$class = 'course-module-step-template step-template-' . $this->__get( 'module_type' );
		if ( ! $user->is_enrolled_at( $course_id ) && ! $this->is_preview() ) {
			$template .= coursepress_create_html( 'p', array(), __( 'You are not enrolled to this course!', 'cp' ) );
			return $template;
		}
		if ( $this->is_show_title() ) {
			$attr = array( 'class' => 'module-step-title' );
			$template .= $this->create_html( 'h4', $attr, $this->get_the_title() );
		}
		if ( $this->is_required() ) {
			$required = $this->create_html( 'span', false, __( '* Required', 'cp' ) );
			$template .= $this->create_html( 'div', array( 'class' => 'required' ), $required );
		}
		$error = coursepress_get_cookie( 'cp_step_error' );
		if ( $error ) {
			$template .= $this->create_html(
				'p',
				array( 'class' => 'error cp-error' ),
				$error
			);
		}
		if ( $this->is_show_content() ) {
			$attr        = array( 'class' => 'course-module-step-description' );
			$description = apply_filters( 'the_content', $this->__get( 'post_content' ) );
			$template   .= $this->create_html( 'div', $attr, $description );
		}
		$question = $this->get_question();
		if ( ! empty( $question ) ) {
			$attr = array( 'class' => 'course-module-step-question' );
			$template .= $this->create_html( 'div', $attr, $question );
		}
		$response = $this->get_user_response( $user->ID );
		if ( $this->has_seen_by( $user->ID ) && ! empty( $response ) ) {
			$answer_template = $this->get_answer_template( $user->ID );
			$class .= ' module-step-seen';
		}
		if ( ! empty( $answer_template ) ) {
			$attr = array( 'class' => 'course-module-answer' );
			$template .= $this->create_html( 'div', $attr, $answer_template );
		}
		return $this->create_html( 'div', array( 'class' => $class ), $template );
	}

	/**
	 * Duplicate current step/module and set given unit ID.
	 *
	 * This class object is created based on a WP_Post object. So using the current
	 * step post data, create new post of type "module". If success, then copy the
	 * unit metadata to newly created course post.
	 *
	 * @param int $unit_id Unit ID of the module.
	 *
	 * @return bool Success or Fail?
	 */
	public function duplicate_step( $unit_id = 0 ) {
		// If in case unit post object is not and ID not found, bail.
		// Step ID is set when this class is instantiated.
		if ( empty( $this->ID ) ) {
			/**
			 * Perform actions if the duplication was failed.
			 *
			 * Note: We don't have step/module ID here.
			 *
			 * @since 3.0
			 */
			do_action( 'coursepress_module_duplicate_failed', false );
			return false;
		}
		// If unit id is empty, current step's unit id will be used.
		if ( empty( $unit_id ) ) {
			$unit_id = $this->unit_id;
		}
		/**
		 * Allow module duplication to be cancelled when filter returns true.
		 *
		 * @since 3.0
		 */
		if ( apply_filters( 'coursepress_module_cancel_duplicate', false, $this->ID ) ) {
			/**
			 * Perform actions if the duplication was cancelled.
			 *
			 * @since 3.0
			 */
			do_action( 'coursepress_module_duplicate_cancelled', $this->ID );
			return false;
		}
		// Make a copy of step object.
		$new_step = clone $this;
		// Unset the step ID.
		unset( $new_step->ID );
		// Set parent to our unit.
		$new_step->post_parent = $unit_id;
		// Set post type as module.
		$new_step->post_type = 'module';
		// Insert new step for the unit.
		$new_step_id = wp_insert_post( $new_step );
		// Copy the old module metadata to duplicated step.
		$step_metas = get_post_meta( $this->ID );
		if ( ! empty( $step_metas ) && $new_step_id ) {
			foreach ( $step_metas as $key => $value ) {
				$value = array_pop( $value );
				$value = maybe_unserialize( $value );
				update_post_meta( $new_step_id, $key, $value );
			}
		}
		/**
		 * Perform action when the module is duplicated.
		 *
		 * @param int $new_step_id New step ID.
		 * @param int $this->ID Old step ID.
		 *
		 * @since 3.0
		 */
		do_action( 'coursepress_module_duplicated', $new_step_id, $this->ID );
		return true;
	}
}
