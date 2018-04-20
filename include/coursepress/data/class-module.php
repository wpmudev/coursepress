<?php

class CoursePress_Data_Module {
	private static $mandatory_modules = array();
	private static $post_type = 'module';

	public static function module_init_hooks() {

		add_filter( 'get_comment_link', array( __CLASS__, 'discussion_module_link' ), 10, 3 );
		add_filter( 'get_edit_post_link', array( __CLASS__, 'discussion_post_link' ), 10, 3 );
		add_filter( 'comment_edit_redirect', array( __CLASS__, 'discussion_edit_redirect' ), 20, 3 );
		add_filter( 'comment_post_redirect', array( __CLASS__, 'discussion_edit_redirect' ), 20, 3 );
		//add_filter( 'cancel_comment_reply_link', array( __CLASS__, 'discussion_cancel_reply_link' ), 10, 3 );
		//add_filter( 'comment_reply_link', array( __CLASS__, 'discussion_reply_link' ), 10, 4 );
		add_filter( 'comments_open', array( __CLASS__, 'discussions_comments_open' ), 10, 2 );
		add_action( 'wp_insert_comment', array( __CLASS__, 'add_last_login_time' ), 74, 2 );
		add_filter( 'wp_list_comments_args', array( __CLASS__, 'add_instructors_to_comments_args' ) );

		/**
		 * Show by default new module on course list.
		 *
		 * @since 2.0.0
		 */
		add_action( 'coursepress_module_added', array( __CLASS__, 'show_on_list' ), 10, 3 );
	}

	public static function get_format() {

		return array(
			'post_type' => self::get_post_type_name(),
			'post_args' => array(
				'labels' => array(
					'name' => __( 'Modules', 'coursepress' ),
					'singular_name' => __( 'Module', 'coursepress' ),
					'add_new' => __( 'Create New', 'coursepress' ),
					'add_new_item' => __( 'Create New Module', 'coursepress' ),
					'edit_item' => __( 'Edit Module', 'coursepress' ),
					'edit' => __( 'Edit', 'coursepress' ),
					'new_item' => __( 'New Module', 'coursepress' ),
					'view_item' => __( 'View Module', 'coursepress' ),
					'search_items' => __( 'Search Modules', 'coursepress' ),
					'not_found' => __( 'No Modules Found', 'coursepress' ),
					'not_found_in_trash' => __( 'No Modules found in Trash', 'coursepress' ),
					'view' => __( 'View Module', 'coursepress' ),
				),
				// 'supports' => array( 'title', 'excerpt', 'comments' ),
				'public' => false,
				'show_ui' => false,
				'publicly_queryable' => false,
				'capability_type' => 'post',
				'map_meta_cap' => true,
				'query_var' => true,
			),
		);
	}

	public static function get_post_type_name() {
		return CoursePress_Data_PostFormat::prefix( self::$post_type );
	}

	public static function get_time_estimation( $module_id, $default = '1:00', $formatted = false ) {
		$module_type = get_post_meta( $module_id, 'module_type', true );
		if ( ! preg_match( '/^input-/', $module_type ) ) {
			return '';
		}
		$user_timer = get_post_meta( $module_id, 'use_timer', true );
		$user_timer = cp_is_true( $user_timer );
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
			$duration = sprintf( '%02d:%02d:%02d', $hours, $minutes, $seconds );
			/**
			 * Allow to change duration for module.
			 *
			 * @since 2.0.6
			 *
			 * @param string $duration Current duration.
			 * @param integer $module_id Module ID.
			 * @param integer $hours Hours.
			 * @param integer $minutes minutes.
			 * @param integer $seconds seconds.
			 */
			return apply_filters( 'coursepress_module_get_time_estimation', $duration, $module_id, $hours, $minutes, $seconds );
		}
		return '';
	}

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

	public static function fix_legacy_meta( $module_id, $meta ) {
		$module_type = $meta['module_type'][0];
		$legacy = self::legacy_map();

		// Get correct new type
		if ( 'text_input_module' == $module_type ) {
			if ( ! empty( $meta['checked_length'] ) ) {
				$checked_length = maybe_unserialize( $meta['checked_length'][0] );
				if ( 'multi' == $checked_length ) {
					$module_type = $legacy['textarea_input_module'];
				} else {
					$module_type = $legacy[ $module_type ];
				}
			}
		} else {
			$module_type = $legacy[ $module_type ];
		}

		// Fix legacy meta
		if ( ! isset( $meta['answers_selected'] ) && isset( $meta['checked_answer'] ) ) {
			$value = $meta['checked_answer'][0];
			$answers = maybe_unserialize( $meta['answers'][0] );
			if ( is_array( $answers ) ) {
				$the_answer = array_keys( $answers, $value );
				//$value = array_shift( $the_answer );
			}
			$meta['answers_selected'][0] = $value;
			update_post_meta( $module_id, 'answers_selected', $value );
			//delete_post_meta( $module_id, 'checked_answer' );
		}

		if ( isset( $meta['checked_answers'] ) && ! isset( $meta['answers_selected'] ) ) {
			$value = get_post_meta( $module_id, 'checked_answers', true );
			$answers = maybe_unserialize( $meta['answers'][0] );
			$checked_answers = array();

			foreach ( $answers as $key => $val ) {
				if ( in_array( $val, $value ) ) {
					$checked_answers[ $key ] = $key;
				}
			}
			$meta['answers_selected'][0] = $checked_answers;
			update_post_meta( $module_id, 'answers_selected', $checked_answers );
			//delete_post_meta( $module_id, 'checked_answers' );
		}

		if ( empty( $meta['duration'] ) && isset( $meta['time_estimation'] ) ) {
			$value = $meta['time_estimation'][0];
			$meta['duration'][0] = $value;
			update_post_meta( $module_id, 'duration', $value );
			//delete_post_meta( $module_id, 'time_estimation' );
		}

		if ( empty( $meta['show_title'] ) && isset( $meta['show_title_on_front'] ) ) {
			$value = cp_is_true( $meta['show_title_on_front'][0] );
			$meta['show_title'][0] = $value;
			update_post_meta( $module_id, 'show_title', $value );
			//delete_post_meta( $module_id, 'show_title_on_front' );
		}

		if ( empty( $meta['mandatory'] ) && isset( $meta['mandatory_answer'] ) ) {
			$value = cp_is_true( $meta['mandatory_answer'][0] );
			$meta['mandatory'][0] = $value;
			update_post_meta( $module_id, 'mandatory', $value );
			//delete_post_meta( $module_id, 'mandatory_answer' );
		}

		if ( empty( $meta['assessable'] ) && isset( $meta['gradable_answer'] ) ) {
			$value = cp_is_true( $meta['gradable_answer'][0] );
			$meta['assessable'][0] = $value;
			update_post_meta( $module_id, 'assessable', $value );
			//delete_post_meta( $module_id, 'gradable_answer' );
		}

		if ( empty( $meta['minimum_grade'] ) && isset( $meta['minimum_grade_required'] ) ) {
			$value = floatval( $meta['minimum_grade_required'][0] );
			$meta['minimum_grade'][0] = $value;
			update_post_meta( $module_id, 'minimum_grade', $value );
			//delete_post_meta( $module_id, 'minimum_grade_required' );
		}

		if ( ! isset( $meta['retry_attempts'] ) && isset( $meta['limit_attempts_value'] ) ) {
			$value = (int) $meta['limit_attempts_value'][0];
			$meta['retry_attempts'][0] = $value;
			update_post_meta( $module_id, 'retry_attempts', $value );
			//delete_post_meta( $module_id, 'limit_attempts_value' );
		}

		if ( ! isset( $meta['allow_retries'] ) && isset( $meta['limit_attempts'] ) ) {
			$value = cp_is_true( $meta['limit_attempts'][0] );
			$value = ! $value; // inverse
			$meta['allow_retries'][0] = $value;
			update_post_meta( $module_id, 'allow_retries', $value );
			//delete_post_meta( $module_id, 'limit_attempts' );
		}

		// Update Type
		//update_post_meta( $module_id, 'module_type', $module_type );
		$meta['module_type'][0] = $module_type;

		return $meta;
	}

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
				'show_title' => cp_is_true( $meta['show_title'][0] ),
				'allow_retries' => isset( $meta['allow_retries'] ) ? cp_is_true( $meta['allow_retries'][0] ) : true,
				'retry_attempts' => isset( $meta['retry_attempts'] ) ? (int) $meta['retry_attempts'][0] : 0,
				'minimum_grade' => isset( $meta['minimum_grade'][0] ) ? floatval( $meta['minimum_grade'][0] ) : floatval( 100 ),
				'assessable' => isset( $meta['assessable'] ) ? cp_is_true( $meta['assessable'][0] ) : false,
				'mandatory' => isset( $meta['mandatory'] ) ? cp_is_true( $meta['mandatory'][0] ) : false,
			) );
		}

		foreach ( $meta as $key => $value ) {
			if ( ! array_key_exists( $key, $attributes ) ) {
				$attributes[ $key ] = maybe_unserialize( $value[0] );
			}
		}

		return $attributes;
	}

	public static function discussion_module_link( $location, $comment ) {
		/**
		 * Check WP_Comment class
		 */
		if ( ! is_a( $comment, 'WP_Comment' ) ) {
			return $location;
		}
		/**
		 * Check post type
		 */
		$post_type = get_post_type( $comment->comment_post_ID );
		if ( self::$post_type !== $post_type ) {
			return $location;
		}
		/**
		 * Check module type
		 */
		$module_type = get_post_meta( $comment->comment_post_ID, 'module_type', true );
		if ( 'discussion' !== $module_type ) {
			return $location;
		}
		$unit_id = get_post_field( 'post_parent', $comment->comment_post_ID );
		$course_id = get_post_field( 'post_parent', $unit_id );
		$course_link = get_permalink( $course_id );
		$location = esc_url_raw( $course_link . CoursePress_Core::get_slug( 'unit/' ) . get_post_field( 'post_name', $course_id ) . '#module-' . $comment->comment_post_ID );
		return $location;
	}

	public static function discussions_comments_open( $open, $post_id ) {
		$type = get_post_meta( $post_id, 'module_type', true );
		if ( 'discussion' == $type ) {
			return true;
		}
		return $open;
	}

	public static function discussion_post_link( $location, $post ) {
		/**
		 * Check WP_Post class
		 */
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return $location;
		}
		/**
		 * Check post type
		 */
		$post_type = get_post_type( $post );
		if ( self::$post_type !== $post_type ) {
			return $location;
		}
		/**
		 * Check module type
		 */
		$module_type = get_post_meta( $post->ID, 'module_type', true );
		if ( 'discussion' !== $module_type ) {
			return $location;
		}
		$unit_id = get_post_field( 'post_parent', $post );
		$course_id = get_post_field( 'post_parent', $unit_id );
		$course_link = get_permalink( $course_id );
		$location = esc_url_raw( $course_link . CoursePress_Core::get_slug( 'unit/' ) . get_post_field( 'post_name', $unit_id ) . '#module-' . $post->ID );
		return $location;
	}

	public static function discussion_edit_redirect( $location, $comment_id ) {
		$comment = get_comment( $comment_id );
		/**
		 * Check WP_Comment class
		 */
		if ( ! is_a( $comment, 'WP_Comment' ) ) {
			return $location;
		}
		$post_type = get_post_type( $comment->comment_post_ID );
		if ( self::$post_type === $post_type ) {
			$unit_id = get_post_field( 'post_parent', $comment->comment_post_ID );
			$course_id = get_post_field( 'post_parent', $unit_id );
			$course_link = get_permalink( $course_id );
			$location = esc_url_raw( $course_link . CoursePress_Core::get_slug( 'unit/' ) . get_post_field( 'post_name', $unit_id ) . '#comment-' . $comment->comment_ID );
		}

		return $location;
	}

	public static function discussion_reply_link( $location, $args, $comment, $post ) {
		/**
		 * Check WP_Post class
		 */
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return $location;
		}
		/**
		 * Check WP_Comment class
		 */
		if ( ! is_a( $comment, 'WP_Comment' ) ) {
			return $location;
		}
		// $comment = get_comment( $comment_id );
		// $post_type = get_post_type( $comment->comment_post_ID );
		//
		// if ( 'module' === $post_type ) {
		// $unit_id = get_post_field( 'post_parent', $comment->comment_post_ID );
		// $course_id = get_post_field( 'post_parent', $unit_id );
		// $course_link = get_permalink( $course_id );
		// $location = esc_url_raw( $course_link . CoursePress_Core::get_slug( 'unit/' ) . get_post_field('post_name', $unit_id ) . '#module-' . $comment->comment_post_ID );
		// }
		if ( 'module' === $post->post_type ) {
			if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
				$location = sprintf( '<a rel="nofollow" class="comment-reply-login" href="%s">%s</a>',
					esc_url( wp_login_url( get_permalink() ) ),
					$args['login_text']
				);
			} else {

				$unit_id = $post->post_parent;
				$course_id = get_post_field( 'post_parent', $unit_id );
				$course_link = get_permalink( $course_id );
				$location = $course_link . CoursePress_Core::get_slug( 'unit/' ) . get_post_field( 'post_name', $unit_id );
				$location .= '&replytocom=' . $comment->comment_ID;
				$location .= '&module=' . $post->ID;
				// $location .= '#module-' . $post->ID;
				$onclick = sprintf( 'return CoursePress.utility.addComment.moveForm( "%1$s-%2$s", "%2$s", "%3$s", "%4$s" )',
					$args['add_below'], $comment->comment_ID, $args['respond_id'], $post->ID
				);

				// $onclick = '';
				$location = sprintf( "<a rel='nofollow' class='comment-reply-link discussion' href='%s' onclick='%s' aria-label='%s'>%s</a>",
					// $location = sprintf( "<a rel='nofollow' class='comment-reply-link discussion' aria-label='%s'>%s</a>",
					esc_url( $location ) . '#' . $args['respond_id'],
					$onclick,
					esc_attr( sprintf( $args['reply_to_text'], $comment->comment_author ) ),
					$args['reply_text']
				);
			}
		}

		return $location;
	}

	public static function discussion_cancel_reply_link( $formatted_link, $location, $text ) {

		$comment_id = isset( $_GET['replytocom'] ) ? (int) $_GET['replytocom'] : '';

		// Bail if comment_id is null
		if ( 0 == (int) $comment_id ) {
			return;
		}

		$comment = get_comment( $comment_id );

		$post_type = get_post_type( $comment->comment_post_ID );

		if ( 'module' === $post_type ) {
			$unit_id = get_post_field( 'post_parent', $comment->comment_post_ID );
			$course_id = get_post_field( 'post_parent', $unit_id );
			$course_link = get_permalink( $course_id );
			$location = esc_url_raw( $course_link . CoursePress_Core::get_slug( 'unit/' ) . get_post_field( 'post_name', $unit_id ) . '#module-' . $comment->comment_post_ID );
		}

		if ( empty( $text ) ) {
			$text = __( 'Click here to cancel reply.', 'coursepress' ); }

		$style = isset( $_GET['replytocom'] ) ? '' : ' style="display:none;"';

		$formatted_link = '<a rel="nofollow" id="cancel-comment-reply-link" href="' . $location . '"' . $style . '>' . $text . '</a>';

		return $formatted_link;
	}

	public static function get_quiz_results( $student_id, $course_id, $unit_id, $module_id, $response = false, $data = false ) {
		$attributes = self::attributes( $module_id );

		if ( false === $data ) {
			$data = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
		}

		if ( false === $response ) {
			$response = CoursePress_Data_Student::get_response( $student_id, $course_id, $unit_id, $module_id, false, $data );
			$response = ! empty( $response ) ? $response['response'] : false;
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

		/**
		 * try it message
		 */
		$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
		$responses = CoursePress_Data_Student::get_responses( $student_id, $course_id, $unit_id, $module_id, true, $student_progress );
		$response_count = count( $responses );
		$unlimited = empty( $attributes['retry_attempts'] );
		$remaining = ! $unlimited ? (int) $attributes['retry_attempts'] - ( $response_count - 1 ) : 0;
		$remaining_message = ! $unlimited ? sprintf( __( 'You have %d attempts left.', 'coursepress' ), $remaining ) : '';
		$remaining_message = sprintf(
			esc_html__( 'Your last attempt was unsuccessful. Try again. %s', 'coursepress' ),
			$remaining_message
		);
		$allow_retries = cp_is_true( $attributes['allow_retries'] );

		if ( ! $allow_retries || ( ! $unlimited && 1 > $remaining ) ) {
			$remaining_message = esc_html__( 'Your last attempt was unsuccessful. You can not try anymore.', 'coursepress' );
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
	* Form results will not depend on grades, just check if mandatory and empty
	*/
	public static function get_form_results( $student_id, $course_id, $unit_id, $module_id, $response = false, $data = false ) {
		$attributes = self::attributes( $module_id );
		$is_mandatory = ! empty( $attributes['mandatory'] );
		$is_assessable = ! empty( $attributes['assessable'] );

		if ( false === $data ) {
			$data = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
		}

		if ( false === $response ) {
			$response = CoursePress_Data_Student::get_response( $student_id, $course_id, $unit_id, $module_id, false, $data );
			$response = ! empty( $response ) ? $response['response'] : false;
		}

		if ( empty( $response ) ) {
			return false;
		}

		$grades = CoursePress_Data_Student::get_grade( $student_id, $course_id, $unit_id, $module_id );
		$minimum_grade = (int) $attributes['minimum_grade'];

		$total_questions = count( $attributes['questions'] );
		$gross_correct = 0;
		$grade = CoursePress_Helper_Utility::get_array_val( $grades, 'grade', 0 );

		/*

		if ( $is_mandatory ) {
			foreach ( $attributes['questions'] as $key => $question ) {
				$answer = $response[ $key ];
				switch ( $question['type'] ) {
					case 'selectable':
						$correct_answers = $question['options']['checked'];
						$result = $total_answers = 0;
						$correct = false;
						if ( isset( $correct_answers[ $answer ] ) ) {
							$correct = cp_is_true( $correct_answers[ $answer ] );
							if ( $correct ) {
								$gross_correct++;
							}
						}
						break;
					case 'short':
					case 'long':
						// just check if empty
						$gross_correct = ( ! empty( $answer ) ) ? $gross_correct + 1 : $gross_correct;
						break;
				}
			}
			$grade = (int) ( $gross_correct / $total_questions * 100 );
		} else {
			$grade = 100;
		}
		*/

		$passed = $grade >= $minimum_grade;
		$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
		$responses = CoursePress_Data_Student::get_responses( $student_id, $course_id, $unit_id, $module_id, true, $student_progress );
		$response_count = count( $responses );
		$unlimited = empty( $attributes['retry_attempts'] );
		$remaining = ! $unlimited ? (int) $attributes['retry_attempts'] - ( $response_count - 1 ) : 0;
		$remaining_message = ! $unlimited ? sprintf( __( 'You have %d attempts left.', 'coursepress' ), $remaining ) : '';
		$remaining_message = sprintf(
			esc_html__( 'Your last attempt was unsuccessful. Try again. %s', 'coursepress' ),
			$remaining_message
		);
		$allow_retries = cp_is_true( $attributes['allow_retries'] );

		if ( ! $allow_retries || ( ! $unlimited && 1 > $remaining ) ) {
			$remaining_message = esc_html__( 'Your last attempt was unsuccessful. You can not try anymore.', 'coursepress' );
		}

		$message = array(
			'hide' => $passed,
			'text' => $remaining_message,
		);

		if ( $is_assessable ) {
			$graded_by = CoursePress_Helper_Utility::get_array_val( $grades, 'graded_by' );

			if ( 'auto' == $graded_by ) {
				return array(
					'pending' => true,
					'grade' => 0,
					'correct' => 0,
					'wrong' => 0,
					'total_questions' => (int) $total_questions,
					'passed' => false,
					'attributes' => $attributes,
					'message' => __( 'Your submission is awaiting instructor assessment.', 'coursepress' ),
				);
			}
		}

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

	// DEPRACATED!!!
	public static function quiz_result_content( $student_id, $course_id, $unit_id, $module_id, $quiz_result = false ) {

		// Get last submitted result
		if ( empty( $quiz_result ) ) {
			$quiz_result = self::get_quiz_results( $student_id, $course_id, $unit_id, $module_id );
		}
		$quiz_passed = ! empty( $quiz_result['passed'] );

		$passed_class = $quiz_passed ? 'passed' : 'not-passed';
		$passed_heading = ! empty( $quiz_result['passed'] ) ? __( 'Success!', 'coursepress' ) : __( 'Quiz not passed.', 'coursepress' );
		$passed_message = ! empty( $quiz_result['passed'] ) ? __( 'You have successfully passed the quiz. Here are your results.', 'coursepress' ) : __( 'You did not pass the quiz this time. Here are your results.', 'coursepress' );

		$template = '<div class="module-quiz-questions"><div class="coursepress-quiz-results ' . esc_attr( $passed_class ) . '">
			<div class="quiz-message">
			<h3 class="result-title">' . $passed_heading . '</h3>
			<p class="result-message">' . $passed_message . '</p>
			</div>
			<div class="quiz-results">
			<table>
			<tr><th>' . esc_html__( 'Total Questions', 'coursepress' ) . '</th><td>' . esc_html( $quiz_result['total_questions'] ) . '</td></tr>
			<tr><th>' . esc_html__( 'Correct', 'coursepress' ) . '</th><td>' . esc_html( $quiz_result['correct'] ) . '</td></tr>
			<tr><th>' . esc_html__( 'Incorrect', 'coursepress' ) . '</th><td>' . esc_html( $quiz_result['wrong'] ) . '</td></tr>
			<tr><th>' . esc_html__( 'Grade', 'coursepress' ) . '</th><td>' . esc_html( $quiz_result['grade'] ) . '%</td></tr>
			</table>
			</div>
			</div>';

		/**
		* retry button
		*/
		if ( ! $quiz_passed ) {
			$attributes = CoursePress_Data_Module::attributes( $module_id );
			$can_retry = $attributes['allow_retries'];
			if ( $can_retry ) {
				$is_enabled = false;
				if ( ! $attributes['retry_attempts'] ) {
					// Unlimited attempts.
					$is_enabled = true;
				} else {
					/**
					 * get student progress
					 */
					$student_progress = array();
					if ( $student_id ) {
						$student_progress = CoursePress_Data_Student::get_completion_data(
							$student_id,
							$course_id
						);
					}

					$responses = CoursePress_Helper_Utility::get_array_val(
						$student_progress,
						'units/' . $unit_id . '/responses/' . $module_id
					);
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
						__( 'Try again!', 'coursepress' )
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
	 * WP_Query args for mandatory modules.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $unit_id Unit id.
	 *
	 * @return array Configuration array for WP_Query.
	 */
	public static function get_args_mandatory_modules( $unit_id ) {
		return array(
			'fields'      => 'ids',
			'meta_key'    => 'mandatory',
			'meta_value'  => '1',
			'nopaging'    => true,
			'post_parent' => $unit_id,
			'post_type'   => self::get_post_type_name(),
		);
	}

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
					$completion_data = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
					$response = CoursePress_Helper_Utility::get_array_val(
						$completion_data,
						'units/' . $unit_id . '/responses/' . $module_id
					);

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
	 * Add last_login timestamp from user data to comment
	 *
	 * @since 2.0.0
	 *
	 * @param integer $comment_id Comment ID.
	 * @param object $comment WP_Comment Object.
	 *
	 */
	public static function add_last_login_time( $comment_id, $comment ) {
		/**
		 * Check WP_Comment class
		 */
		if ( ! is_a( $comment, 'WP_Comment' ) ) {
			return;
		}
		$parent_post_type = get_post_type( $comment->comment_post_ID );
		if ( $parent_post_type != self::$post_type ) {
			return;
		}
		$student_last_login_time = self::_get_last_login_time( $comment->user_id );
		add_comment_meta( $comment_id, 'last_login', $student_last_login_time, true );
	}

	/**
	 * Get modules IDS by unit id.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $unit_id Unit ID.
	 *
	 * @return array List of modules of the unit.
	 */
	public static function get_modules_ids_by_unit( $unit_id ) {
		/**
		 * Check unit_id post type
		 */
		$post_type = get_post_type( $unit_id );
		$unit_post_type = CoursePress_Data_Unit::get_post_type_name();
		if ( $unit_post_type != $post_type ) {
			return array();
		}
		/**
		 * get children
		 */
		$args = array(
			'post_type' => self::$post_type,
			'post_status' => 'publish',
			'fields' => 'ids',
			'suppress_filters' => true,
			'nopaging' => true,
			'post_parent' => $unit_id,
		);
		$query = new WP_Query( $args );
		return $query->posts;
	}

	/**
	 * Get last_login timestamp from user data
	 *
	 * @since 2.0.0
	 *
	 * @access private
	 *
	 * @param integer $user_id User ID.
	 *
	 * @return timestamp Returrn last login timestamp.
	 */
	private static function _get_last_login_time( $user_id ) {
		$last_login_time = get_user_meta( $user_id, 'last_login', true );
		if ( isset( $last_login_time['time'] ) ) {
			return $last_login_time['time'];
		}
		return 0;
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
		/**
		 * Check module is a WP_Post object?
		 */
		if ( ! is_a( $module, 'WP_Post' ) ) {
			return 0;
		}
		$post_type = self::get_post_type_name();
		if ( $module->post_type == $post_type ) {
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
	 * Get instructors.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $module_id Module ID or module WP_Post object.
	 *
	 * @return array Array of instructors assigned to course.
	 */
	public static function get_instructors( $module_id, $objects = false ) {
		$unit_id = self::get_unit_id_by_module( $module_id );
		return CoursePress_Data_Unit::get_instructors( $unit_id, $objects );
	}

	/**
	 * Add instructors list to comments walker params.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Args of comments walker params.
	 *
	 * @return array $args Args of comments walker params.
	 */
	public static function add_instructors_to_comments_args( $args ) {
		global $post;
		/**
		 * Check WP_Post class
		 */
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return $args;
		}
		$post_type = self::get_post_type_name();
		if ( $post_type != $post->post_type ) {
			return $args;
		}
		$args['coursepress_instructors'] = self::get_instructors( $post->ID );
		return $args;
	}

	/**
	 * Get modules ids by unit ids.
	 *
	 * @since 2.0.0
	 *
	 * @param array $ids unit IDs.
	 * @return array Array of module IDs.
	 */
	public static function get_module_ids_by_unit_ids( $ids ) {
		if ( empty( $ids ) ) {
			return array();
		}
		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}
		$args = array(
			'post_type' => self::$post_type,
			'nopaging' => true,
			'suppress_filters' => true,
			'ignore_sticky_posts' => true,
			'fields' => 'ids',
		);
		if ( ! empty( $ids ) ) {
			$args['post_parent__in'] = $ids;
		}
		$query = new WP_Query( $args );
		return $query->posts;
	}

	/**
	 * New module will be shown on course structure list by default.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $module_id Module ID.
	 * @param integer $unit_id unit ID.
	 * @param array $meta Meta data.
	 */
	public static function show_on_list( $module_id, $unit_id, $meta ) {
		$course_id = CoursePress_Data_Unit::get_course_id_by_unit( $unit_id );
		$visible_modules = CoursePress_Data_Course::get_setting( $course_id, 'structure_visible_modules', array() );
		$id = sprintf(
			'%d_%d_%d',
			$unit_id,
			isset( $meta['module_page'] )? $meta['module_page'] : 1,
			$module_id
		);
		$visible_modules[ $id ] = 1;
		CoursePress_Data_Course::update_setting( $course_id, 'structure_visible_modules', $visible_modules );
		/**
		 * check visibility of page
		 */
		$page_id = isset( $meta['module_page'] )? $meta['module_page']: 1;
		$visible_pages = CoursePress_Data_Course::get_setting( $course_id, 'structure_visible_pages', array() );
		$id = sprintf( '%d_%d', $unit_id, $page_id );
		if ( ! isset( $visible_modules[ $id ] ) ) {
			CoursePress_Data_Unit::show_page( $unit_id, $page_id, $course_id );
		}
	}

	/**
	 * Check entry - is this module?
	 *
	 * @since 2.0.2
	 *
	 * @param WP_Post|integer $module Variable to check.
	 * @return boolean Answer is that module or not?
	 */
	public static function is_module( $module ) {
		$post_type = get_post_type( $module );
		if ( $post_type == self::$post_type ) {
			return true;
		}
		return false;
	}

	/**
	 * Get modules by type
	 *
	 * @since 2.0.0
	 *
	 * @param string $type module type
	 * @param integer $course_id course ID.
	 * @return array Array of modules ids.
	 */
	public static function get_all_modules_ids_by_type( $type, $course_id = null ) {
		$args = array(
			'post_type' => self::get_post_type_name(),
			'fields' => 'ids',
			'suppress_filters' => true,
			'nopaging' => true,
			'meta_key' => 'module_type',
			'meta_value' => $type,
		);
		if ( ! empty( $course_id ) ) {
			$units = CoursePress_Data_Course::get_units( $course_id, array( 'any' ), true );
			if ( empty( $units ) ) {
				return array();
			}
			$args['post_parent__in'] = $units;
		}
		$modules = new WP_Query( $args );
		return $modules->posts;
	}

	/**
	 * Change page number for modules, when we delete page (section).
	 *
	 * @since 2.0.3
	 *
	 * @param integer $unit_id Unit ID.
	 * @param integer $page_number Deleted page number.
	 */
	public static function decrease_page_number( $unit_id, $page_number ) {
		if ( ! CoursePress_Data_Unit::is_unit( $unit_id ) ) {
			return;
		}
		$args = array(
			'post_type' => self::get_post_type_name(),
			'post_parent' => $unit_id,
			'post_status' => 'any',
			'meta_query' => array(
				array(
					'key' => 'module_page',
					'value' => intval( $page_number ),
					'compare' => '>',
					'type' => 'SIGNED',
				),
			),
			'fields' => 'ids',
			'posts_per_page' => -1,
		);

		$the_query = new WP_Query( $args );
		foreach ( $the_query->posts as $post_id ) {
			/**
			 * change page
			 */
			$value = get_post_meta( $post_id, 'module_page', true );
			$value--;
			update_post_meta( $post_id, 'module_page', $value );
		}
	}

	/**
	 * Move modules from deleted page/seciton to first page/section.
	 *
	 * @since 2.0.3
	 *
	 * @param integer $unit_id Unit ID.
	 * @param integer $page_number Deleted page number.
	 */
	public static function move_to_first_page( $unit_id, $page_number ) {
		if ( ! CoursePress_Data_Unit::is_unit( $unit_id ) ) {
			return;
		}
		$page_number = intval( $page_number );
		if ( empty( $page_number ) ) {
			return;
		}
		global $wpdb;
		/**
		 * find last page order for targegt page. It is always page number 1,
		 * except when we delete page number 1.
		 */
		$args = array(
			'post_type' => self::get_post_type_name(),
			'post_parent' => $unit_id,
			'post_status' => 'any',
			'meta_query' => array(
				array(
					'key' => 'module_page',
					'value' => 1 == $page_number ? 2 : 1,
					'compare' => '=',
					'type' => 'SIGNED',
				),
			),
			'fields' => 'ids',
			'posts_per_page' => -1,
		);
		$the_query = new WP_Query( $args );
		$query = $wpdb->prepare( "SELECT MAX( meta_value ) FROM {$wpdb->postmeta} WHERE meta_key = %s AND post_id IN (".implode( ', ', $the_query->posts ).')', 'module_order' );
		$increase = $wpdb->get_var( $query );
		/**
		 * Find modules to move
		 */
		$args = array(
			'post_type' => self::get_post_type_name(),
			'post_parent' => $unit_id,
			'post_status' => 'any',
			'meta_query' => array(
				array(
					'key' => 'module_page',
					'value' => $page_number,
					'compare' => '=',
					'type' => 'SIGNED',
				),
			),
			'fields' => 'ids',
			'posts_per_page' => -1,
		);
		$the_query = new WP_Query( $args );
		/**
		 * change module page number * increase module order
		 */
		foreach ( $the_query->posts as $post_id ) {
			$value = get_post_meta( $post_id, 'module_page', true );
			/**
			 * change page
			 */
			if ( 1 != $value ) {
				update_post_meta( $post_id, 'module_page', 1 );
			}
			/**
			 * change order
			 */
			$value = intval( get_post_meta( $post_id, 'module_order', true ) );
			$value += $increase;
			update_post_meta( $post_id, 'module_order', $value );
		}
	}

	/*
	 * Check free preview of module.
	 *
	 * @since 2.0.4
	 *
	 * @param integer $module_id Module ID.
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
		if (
			! empty( $preview )
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
}
