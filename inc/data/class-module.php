<?php

class CoursePress_Data_Module {

	/**
	 * List of ids of andatory modules.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $unit_id Unit id.
	 *
	 * @return array List of mandatory modules.
	 */

	public static function get_mandatory_modules( $unit_id ) {

		if ( ! empty( self::$mandatory_modules[ $unit_id ] ) ) {
			return self::$mandatory_modules[ $unit_id ];
		}

		$args = self::get_args_mandatory_modules( $unit_id );
		$the_query = new WP_Query( $args );
		$mandatory_modules = array();
		if ( $the_query->have_posts() ) {
			foreach ( $the_query->posts as $module_id ) {
				$mandatory_modules[ $module_id ] = get_post_meta( $module_id, 'module_type', true );
			}
		}

		// Store mandatory modules
		self::$mandatory_modules[ $unit_id ] = $mandatory_modules;

		return $mandatory_modules;
	}

	/**
	 * Check is module done by student?
	 *
	 * @since 2.0.0
	 *
	 * @param integer $module_id Modue ID to check
	 * @param integer $student_id student to check. Default empty.
	 *
	 * @return boolean is module done?
	 */
	public static function is_module_done_by_student( $module_id, $student_id ) {

		if ( ! $student_id ) {
			$student_id = get_current_user_id();
		}

		$unit_id = wp_get_post_parent_id( $module_id );
		$mandatory_modules = self::get_mandatory_modules( $unit_id );

		if ( isset( $mandatory_modules[ $module_id ] ) ) {
			switch ( $mandatory_modules[ $module_id ] ) {
				case 'discussion':
					$args = array(
						'post_id' => $module_id,
						'user_id' => $student_id,
						'order' => 'ASC',
						'number' => 1, // We only need one to verify if current user posted a comment.
						'fields' => 'ids',
					);
					$comments = get_comments( $args );

					return count( $comments ) > 0;
				break;

				default:
					$course_id = wp_get_post_parent_id( $unit_id );
					$student = coursepress_get_user( $student_id );
					$response = $student->get_response( $course_id, $unit_id, $module_id );

					$is_done = false;
					$last_answer = is_array( $response ) ? array_pop( $response ) : false;

					if ( ! empty( $last_answer ) ) {
						$is_done = true;
					}

					return $is_done;

			}
		}

		return true;
	}

	/**
	 * Get the attributes of module.
	 *
	 * @param int|object $module
	 * @param bool $meta
	 *
	 * @return array|bool
	 */
	public static function attributes( $module, $meta = false ) {

		if ( is_object( $module ) ) {
			$module_id = $module->ID;
		} else {
			$module_id = (int) $module;
		}

		$meta = empty( $meta ) ? get_post_meta( $module_id ) : $meta;

		$legacy = self::legacy_map();
		$module_type = isset( $meta['module_type'] ) ? $meta['module_type'][0] : false;

		if ( false === $module_type ) {
			return false;
		}

		if ( array_key_exists( $module_type, $legacy ) && empty( $meta['legacy_updated'] ) ) {
			$meta = self::fix_legacy_meta( $module_id, $meta );
			//$meta = get_post_meta( $module_id );
			$module_type = $meta['module_type'][0];
		}

		$input = preg_match( '/^input-/', $module_type );

		$attributes = array(
			'module_type' => $module_type,
			'mode' => $input ? 'input' : 'output',
		);

		if ( 'section' != $module_type ) {
			$attributes = array_merge( $attributes, array(
				'duration' => isset( $meta['duration'] ) ? $meta['duration'][0] : '0:00',
				'show_title' => coursepress_is_true( $meta['show_title'][0] ),
				'allow_retries' => isset( $meta['allow_retries'] ) ? coursepress_is_true( $meta['allow_retries'][0] ) : true,
				'retry_attempts' => isset( $meta['retry_attempts'] ) ? (int) $meta['retry_attempts'][0] : 0,
				'minimum_grade' => isset( $meta['minimum_grade'][0] ) ? floatval( $meta['minimum_grade'][0] ) : floatval( 100 ),
				'assessable' => isset( $meta['assessable'] ) ? coursepress_is_true( $meta['assessable'][0] ) : false,
				'mandatory' => isset( $meta['mandatory'] ) ? coursepress_is_true( $meta['mandatory'][0] ) : false,
			) );
		}

		foreach ( $meta as $key => $value ) {
			if ( ! array_key_exists( $key, $attributes ) ) {
				$attributes[ $key ] = maybe_unserialize( $value[0] );
			}
		}

		return $attributes;
	}

	/**
	 * Get unit ID by module
	 *
	 * @since 2.0.0
	 *
	 * @param integer/WP_Post $module Module ID or module WP_Post object.
	 *
	 * @return integer Returns unit id.
	 */
	public static function get_unit_id_by_module( $module ) {

		if ( ! is_object( $module ) && preg_match( '/^\d+$/', $module ) ) {
			$module = get_post( $module );
		}

		// Check module is a WP_Post object?
		if ( ! is_a( $module, 'WP_Post' ) ) {
			return 0;
		}

		if ( $module->post_type == 'module' ) {
			return $module->post_parent;
		}

		return 0;
	}

	/**
	 * Get course ID by module
	 *
	 * @since 2.0.0
	 *
	 * @param integer/WP_Post $module Module ID or module WP_Post object.
	 *
	 * @return integer Returns course id.
	 */
	public static function get_course_id_by_module( $module ) {

		$unit_id = self::get_unit_id_by_module( $module );

		return CoursePress_Data_Unit::get_course_id_by_unit( $unit_id );
	}

	/**
	 * Check free preview of module.
	 *
	 * @since 2.0.4
	 *
	 * @param integer $module_id Module ID.
	 *
	 * @return boolean Is free preview available for this module?
	 */
	public static function can_be_previewed( $module_id ) {

		global $wp;

		$page_id = 0;

		if ( isset( $wp->query_vars['paged'] ) ) {
			$page_id = $wp->query_vars['paged'];
		}

		if ( empty( $page_id ) ) {
			return false;
		}

		$unit_id = self::get_unit_id_by_module( $module_id );

		if ( empty( $unit_id ) ) {
			return false;
		}

		$course_id = self::get_course_id_by_module( $module_id );

		if ( empty( $course_id ) ) {
			return false;
		}

		$preview = CoursePress_Data_Course::previewability( $course_id );

		if ( ! empty( $preview )
			&& is_array( $preview )
			&& isset( $preview['structure'] )
			&& is_array( $preview['structure'] )
			&& isset( $preview['structure'][ $unit_id ] )
			&& is_array( $preview['structure'][ $unit_id ] )
			&& isset( $preview['structure'][ $unit_id ][ $page_id ] )
			&& is_array( $preview['structure'][ $unit_id ][ $page_id ] )
			&& isset( $preview['structure'][ $unit_id ][ $page_id ][ $module_id ] )
			&& cp_is_true( $preview['structure'][ $unit_id ][ $page_id ][ $module_id ] )
		) {
			return true;
		}

		return false;
	}

	// DEPRACATED!!!
	public static function quiz_result_content( $student_id, $course_id, $unit_id, $module_id, $quiz_result = false ) {

		// Get last submitted result
		if ( empty( $quiz_result ) ) {
			$quiz_result = self::get_quiz_results( $student_id, $course_id, $unit_id, $module_id );
		}

		$quiz_passed = ! empty( $quiz_result['passed'] );

		$passed_class = $quiz_passed ? 'passed' : 'not-passed';
		$passed_heading = ! empty( $quiz_result['passed'] ) ? __( 'Success!', 'cp' ) : __( 'Quiz not passed.', 'cp' );
		$passed_message = ! empty( $quiz_result['passed'] ) ? __( 'You have successfully passed the quiz. Here are your results.', 'cp' ) : __( 'You did not pass the quiz this time. Here are your results.', 'cp' );

		$template = '<div class="module-quiz-questions"><div class="coursepress-quiz-results ' . esc_attr( $passed_class ) . '">
			<div class="quiz-message">
			<h3 class="result-title">' . $passed_heading . '</h3>
			<p class="result-message">' . $passed_message . '</p>
			</div>
			<div class="quiz-results">
			<table>
			<tr><th>' . esc_html__( 'Total Questions', 'cp' ) . '</th><td>' . esc_html( $quiz_result['total_questions'] ) . '</td></tr>
			<tr><th>' . esc_html__( 'Correct', 'cp' ) . '</th><td>' . esc_html( $quiz_result['correct'] ) . '</td></tr>
			<tr><th>' . esc_html__( 'Incorrect', 'cp' ) . '</th><td>' . esc_html( $quiz_result['wrong'] ) . '</td></tr>
			<tr><th>' . esc_html__( 'Grade', 'cp' ) . '</th><td>' . esc_html( $quiz_result['grade'] ) . '%</td></tr>
			</table>
			</div>
			</div>';

		// Retry button
		if ( ! $quiz_passed ) {
			$attributes = CoursePress_Data_Module::attributes( $module_id );
			$can_retry = $attributes['allow_retries'];
			if ( $can_retry ) {
				$is_enabled = false;
				if ( ! $attributes['retry_attempts'] ) {
					// Unlimited attempts.
					$is_enabled = true;
				} else {
					// Get student progress.
					$responses = array();
					if ( $student_id ) {
						$student = coursepress_get_user( $student_id );
						$responses = $student->get_response( $student_id, $course_id, $unit_id, $module_id );
					}

					$response_count = 0;
					if ( $responses && is_array( $responses ) ) {
						$response_count = count( $responses );
					}
					if ( (int) $attributes['retry_attempts'] >= $response_count ) {
						// Retry limit not yet reached.
						$is_enabled = true;
					}
				}
				if ( $is_enabled ) {
					$template .= sprintf(
						'<div class="module-elements focus-nav-reload" data-id="%d" data-type="module">',
						esc_attr( $module_id )
					);
					$template .= sprintf(
						'<a class="module-submit-action button-reload-module" href="#module-%d">%s</a>',
						esc_attr( $module_id ),
						__( 'Try again!', 'cp' )
					);
					$template .= ' </div>';
				}
			}
		}

		$template .= '</div>';

		$attributes = array(
			'course_id' => $course_id,
			'unit_id' => $unit_id,
			'module_id' => $module_id,
			'student_id' => $student_id,
			'quiz_result' => $quiz_result,
		);

		// Can't use shortcodes this time as this also loads via AJAX
		$template = apply_filters( 'coursepress_template_quiz_results', $template, $attributes );

		return $template;
	}

	/**
	 * Get quiz result content.
	 *
	 * @param int $student_id
	 * @param int $course_id
	 * @param int $unit_id
	 * @param int $module_id
	 * @param bool|array $response
	 * @param bool|array $data
	 *
	 * @return array
	 */
	public static function get_quiz_results( $student_id, $course_id, $unit_id, $module_id, $response = false, $data = false ) {

		$attributes = self::attributes( $module_id );

		if ( false === $data ) {
			$student = coursepress_get_user( $student_id );
			$data = $student->get_completion_data( $course_id );
		}

		$responses = false;
		if ( false === $response ) {
			$responses = $student->get_response( $course_id, $unit_id, $module_id, $data );
			$response = ! empty( $responses ) ? $responses['response'] : false;
		}

		if ( empty( $response ) ) {
			return false;
		}

		$minimum_grade = (int) $attributes['minimum_grade'];

		$total_questions = count( $attributes['questions'] );
		$gross_correct = 0;

		foreach ( $attributes['questions'] as $key => $question ) {

			switch ( $question['type'] ) {

				case 'single':
				case 'multiple':
					$correct_answers = $question['options']['checked'];
					$total_answers = count( $correct_answers );
					$correct_responses = 0;

					if ( isset( $response[ $key ] ) && is_array( $response[ $key ] ) ) {
						foreach ( $response[ $key ] as $a_key => $answer ) {
							if ( $answer === $correct_answers[ $a_key ] ) {
								$correct_responses += 1;
							}
						}
					}

					$result = (int) ( $correct_responses / $total_answers * 100 );
					// If multiple choice passed, add it to the total
					$gross_correct = 100 === $result ? $gross_correct + 1 : $gross_correct;
					break;

				case 'single1':
					$correct_answers = $question['options']['checked'];
					$total_answers = count( $correct_answers );
					$correct_responses = 0;

					if ( is_array( $response[ $key ] ) ) {
						foreach ( $response[ $key ] as $a_key => $answer ) {
							if ( $answer === $correct_answers[ $a_key ] ) {
								$correct_responses += 1;
							}
						}
					}

					$result = (int) ( $correct_responses / $total_answers * 100 );
					// If multiple choice passed, add it to the total
					$gross_correct = 100 === $result ? $gross_correct + 1 : $gross_correct;

					break;

				case 'short':
					break;
				case 'long':
					break;
			}
		}

		$grade = (int) ( $gross_correct / $total_questions * 100 );
		$passed = $grade >= $minimum_grade;

		// Try it message
		if ( empty( $responses ) ) {
			$responses = $student->get_response( $course_id, $unit_id, $module_id, $data );
		}
		$response_count = count( $responses );
		$unlimited = empty( $attributes['retry_attempts'] );
		$remaining = ! $unlimited ? (int) $attributes['retry_attempts'] - ( $response_count - 1 ) : 0;
		$remaining_message = ! $unlimited ? sprintf( __( 'You have %d attempts left.', 'cp' ), $remaining ) : '';
		$remaining_message = sprintf(
			esc_html__( 'Your last attempt was unsuccessful. Try again. %s', 'cp' ),
			$remaining_message
		);

		$allow_retries = coursepress_is_true( $attributes['allow_retries'] );

		if ( ! $allow_retries || ( ! $unlimited && 1 > $remaining ) ) {
			$remaining_message = esc_html__( 'Your last attempt was unsuccessful. You can not try anymore.', 'cp' );
		}

		$message = array(
			'hide' => $passed,
			'text' => $remaining_message,
		);

		return array(
			'grade' => (int) $grade,
			'correct' => (int) $gross_correct,
			'wrong' => (int) $total_questions - (int) $gross_correct,
			'total_questions' => (int) $total_questions,
			'passed' => $passed,
			'attributes' => $attributes,
			'message' => $message,
		);
	}

	/**
	 * Get time estimate.
	 *
	 * @param int $module_id
	 * @param string $default
	 * @param bool $formatted
	 *
	 * @return mixed|string
	 */
	public static function get_time_estimation( $module_id, $default = '1:00', $formatted = false ) {

		$module_type = get_post_meta( $module_id, 'module_type', true );

		if ( ! preg_match( '/^input-/', $module_type ) ) {
			return '';
		}

		$user_timer = get_post_meta( $module_id, 'use_timer', true );
		$user_timer = coursepress_is_true( $user_timer );
		if ( ! $user_timer ) {
			return '';
		}

		$estimation = get_post_meta( $module_id, 'duration', true );
		$estimation = empty( $estimation ) ? $default : $estimation;
		if ( ! $formatted ) {
			return empty( $estimation ) ? $default : $estimation;
		} else {
			$parts = explode( ':', $estimation );
			$seconds = (int) array_pop( $parts );
			$minutes = (int) array_pop( $parts );
			if ( ! empty( $parts ) ) {
				$hours = (int) array_pop( $parts );
			} else {
				$hours = 0;
			}

			return sprintf( '%02d:%02d:%02d', $hours, $minutes, $seconds );
		}

	}

	/**
	 * Legacy mapping.
	 *
	 * @return array
	 */
	public static function legacy_map() {

		return array(
			'audio_module' => 'audio',
			'chat_module' => 'chat',
			'checkbox_input_module' => 'input-checkbox',
			'file_module' => 'download',
			'file_input_module' => 'input-upload',
			'image_module' => 'image',
			'page_break_module' => 'legacy',
			'radio_input_module' => 'input-radio',
			'page_break_module' => 'section',
			'section_break_module' => 'section',
			'text_module' => 'text',
			'text_input_module' => 'input-text',
			'textarea_input_module' => 'input-textarea',
			'video_module' => 'video',
		);
	}
}
