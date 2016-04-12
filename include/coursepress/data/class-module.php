<?php

class CoursePress_Data_Module {

	private static $post_type = 'module';

	public static function module_init_hooks() {

		add_filter( 'get_comment_link', array( __CLASS__, 'discussion_module_link' ), 10, 3 );
		add_filter( 'get_edit_post_link', array( __CLASS__, 'discussion_post_link' ), 10, 3 );
		add_filter( 'comment_edit_redirect', array( __CLASS__, 'discussion_edit_redirect' ), 10, 3 );
		add_filter( 'cancel_comment_reply_link', array( __CLASS__, 'discussion_cancel_reply_link' ), 10, 3 );
		add_filter( 'comment_reply_link', array( __CLASS__, 'discussion_reply_link' ), 10, 4 );
		add_filter( 'comments_open', array( __CLASS__, 'discussions_comments_open' ), 10, 2 );
		add_action( 'parse_request', array( __CLASS__, 'parse_request' ) );

	}

	public static function get_format() {

		return array(
			'post_type' => self::get_post_type_name(),
			'post_args' => array(
				'labels' => array(
					'name' => __( 'Modules', 'CP_TD' ),
					'singular_name' => __( 'Module', 'CP_TD' ),
					'add_new' => __( 'Create New', 'CP_TD' ),
					'add_new_item' => __( 'Create New Module', 'CP_TD' ),
					'edit_item' => __( 'Edit Module', 'CP_TD' ),
					'edit' => __( 'Edit', 'CP_TD' ),
					'new_item' => __( 'New Module', 'CP_TD' ),
					'view_item' => __( 'View Module', 'CP_TD' ),
					'search_items' => __( 'Search Modules', 'CP_TD' ),
					'not_found' => __( 'No Modules Found', 'CP_TD' ),
					'not_found_in_trash' => __( 'No Modules found in Trash', 'CP_TD' ),
					'view' => __( 'View Module', 'CP_TD' ),
				),
				// 'supports' => array( 'title', 'excerpt', 'comments' ),
				'public' => false,
				'show_ui' => false,
				'publicly_queryable' => false,
				'capability_type' => 'module',
				'map_meta_cap' => true,
				'query_var' => true,
			),
		);

	}

	public static function get_post_type_name() {
		return CoursePress_Data_PostFormat::prefix( self::$post_type );
	}

	public static function get_time_estimation( $module_id, $default = '1:00', $formatted = false ) {

		$estimation = get_post_meta( $module_id, 'time_estimation', true );
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
			if ( isset( $meta['checked_length'] ) && 'multi' == $meta['checked_length'] ) {
				$module_type = $legacy['textarea_input_module'];
			} else {
				$module_type = $legacy[ $module_type ];
			}
		} else {
			$module_type = $legacy[ $module_type ];
		}

		// Fix legacy meta
		if ( isset( $meta['checked_answer'] ) ) {
			$value = $meta['checked_answer'][0];
			update_post_meta( $module_id, 'answers_selected', $value );
			delete_post_meta( $module_id, 'checked_answer' );
		}
		if ( isset( $meta['checked_answers'] ) ) {
			$value = get_post_meta( $module_id, 'checked_answers', true );
			update_post_meta( $module_id, 'answers_selected', $value );
			delete_post_meta( $module_id, 'checked_answers' );
		}

		if ( isset( $meta['time_estimation'] ) ) {
			$value = $meta['time_estimation'][0];
			update_post_meta( $module_id, 'duration', $value );
			delete_post_meta( $module_id, 'time_estimation' );
		}

		if ( isset( $meta['show_title_on_front'] ) ) {
			$value = cp_is_true( $meta['show_title_on_front'][0] );
			update_post_meta( $module_id, 'show_title', $value );
			delete_post_meta( $module_id, 'show_title_on_front' );
		}

		if ( isset( $meta['mandatory_answer'] ) ) {
			$value = cp_is_true( $meta['mandatory_answer'][0] );
			update_post_meta( $module_id, 'mandatory', $value );
			delete_post_meta( $module_id, 'mandatory_answer' );
		}

		if ( isset( $meta['gradable_answer'] ) ) {
			$value = cp_is_true( $meta['gradable_answer'][0] );
			update_post_meta( $module_id, 'assessable', $value );
			delete_post_meta( $module_id, 'gradable_answer' );
		}

		if ( isset( $meta['minimum_grade_required'] ) ) {
			$value = floatval( $meta['minimum_grade_required'][0] );
			update_post_meta( $module_id, 'minimum_grade', $value );
			delete_post_meta( $module_id, 'minimum_grade_required' );
		}

		if ( isset( $meta['limit_attempts_value'] ) ) {
			$value = (int) $meta['limit_attempts_value'][0];
			update_post_meta( $module_id, 'retry_attempts', $value );
			delete_post_meta( $module_id, 'limit_attempts_value' );
		}

		if ( isset( $meta['limit_attempts'] ) ) {
			$value = cp_is_true( $meta['limit_attempts'][0] );
			$value = ! $value; // inverse
			update_post_meta( $module_id, 'allow_retries', $value );
			delete_post_meta( $module_id, 'limit_attempts' );
		}

		// Update Type
		update_post_meta( $module_id, 'module_type', $module_type );
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

		if ( array_key_exists( $module_type, $legacy ) ) {
			self::fix_legacy_meta( $module_id, $meta );
			$meta = get_post_meta( $module_id );
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

	public static function discussion_module_link( $link, $comment, $args ) {
		$post_type = get_post_type( $comment->comment_post_ID );

		if ( 'module' === $post_type ) {
			$unit_id = get_post_field( 'post_parent', $comment->comment_post_ID );
			$course_id = get_post_field( 'post_parent', $unit_id );
			$course_link = get_permalink( $course_id );
			$link = esc_url_raw( $course_link . CoursePress_Core::get_slug( 'unit/' ) . get_post_field( 'post_name', $course_id ) . '#module-' . $comment->comment_post_ID );
		}

		return $link;
	}

	public static function discussions_comments_open( $open, $post_id ) {
		$type = get_post_meta( $post_id, 'module_type', true );

		if ( 'discussion' == $type ) {
			return true;
		}

		return $open;
	}

	public static function discussion_post_link( $link, $post, $args ) {
		$post_type = get_post_type( $post );

		if ( 'module' === $post_type ) {
			$unit_id = get_post_field( 'post_parent', $post );
			$course_id = get_post_field( 'post_parent', $unit_id );
			$course_link = get_permalink( $course_id );
			$link = esc_url_raw( $course_link . CoursePress_Core::get_slug( 'unit/' ) . get_post_field( 'post_name', $unit_id ) . '#module-' . $post );
		}

		return $link;
	}

	public static function discussion_edit_redirect( $location, $comment_id ) {
		$comment = get_comment( $comment_id );

		$post_type = get_post_type( $comment->comment_post_ID );

		if ( 'module' === $post_type ) {
			$unit_id = get_post_field( 'post_parent', $comment->comment_post_ID );
			$course_id = get_post_field( 'post_parent', $unit_id );
			$course_link = get_permalink( $course_id );
			$location = esc_url_raw( $course_link . CoursePress_Core::get_slug( 'unit/' ) . get_post_field( 'post_name', $unit_id ) . '#module-' . $comment->comment_post_ID );
		}

		return $location;
	}

	public static function discussion_reply_link( $link, $args, $comment, $post ) {

		// $comment = get_comment( $comment_id );
		// $post_type = get_post_type( $comment->comment_post_ID );
		//
		// if ( 'module' === $post_type ) {
		// $unit_id = get_post_field( 'post_parent', $comment->comment_post_ID );
		// $course_id = get_post_field( 'post_parent', $unit_id );
		// $course_link = get_permalink( $course_id );
		// $link = esc_url_raw( $course_link . CoursePress_Core::get_slug( 'unit/' ) . get_post_field('post_name', $unit_id ) . '#module-' . $comment->comment_post_ID );
		// }
		if ( 'module' === $post->post_type ) {
			if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
				$link = sprintf( '<a rel="nofollow" class="comment-reply-login" href="%s">%s</a>',
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
				$link = sprintf( "<a rel='nofollow' class='comment-reply-link discussion' href='%s' onclick='%s' aria-label='%s'>%s</a>",
					// $link = sprintf( "<a rel='nofollow' class='comment-reply-link discussion' aria-label='%s'>%s</a>",
					esc_url( $location ) . '#' . $args['respond_id'],
					$onclick,
					esc_attr( sprintf( $args['reply_to_text'], $comment->comment_author ) ),
					$args['reply_text']
				);
			}
		}

		return $link;
	}

	public static function discussion_cancel_reply_link( $formatted_link, $link, $text ) {

		$comment_id = isset( $_GET['replytocom'] ) ? (int) $_GET['replytocom'] : '';

		// Bail if comment_id is null
		if ( 0 == (int) $comment_id ) {
			return;
		}

		$comment = get_comment( $comment_id );

		$post_type = get_post_type( $comment->comment_post_ID );

		switch ( $post_type ) {

			case 'module':
				$unit_id = get_post_field( 'post_parent', $comment->comment_post_ID );
				$course_id = get_post_field( 'post_parent', $unit_id );
				$course_link = get_permalink( $course_id );
				$location = esc_url_raw( $course_link . CoursePress_Core::get_slug( 'unit/' ) . get_post_field( 'post_name', $unit_id ) . '#module-' . $comment->comment_post_ID );
			break;

			case 'discussions':
				$slug = get_query_var( 'course' );
				$course = get_page_by_path( $slug, OBJECT, 'course' );
				$course_link = get_permalink( $course->ID );
				$location = esc_url_raw( $course_link . CoursePress_Core::get_slug( 'discussion/' ) . get_post_field( 'post_name', $comment->comment_post_ID ) . '#comment' . $comment->comment_ID );
			break;

			default:
			return;

		}

		if ( empty( $text ) ) {
			$text = __( 'Click here to cancel reply.', 'CP_TD' );
		}

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

		return array(
			'grade' => (int) $grade,
			'correct' => (int) $gross_correct,
			'wrong' => (int) $total_questions - (int) $gross_correct,
			'total_questions' => (int) $total_questions,
			'passed' => $passed,
			'attributes' => $attributes,
		);

	}

	public static function quiz_result_content( $student_id, $course_id, $unit_id, $module_id, $quiz_result = false ) {

		// Get last submitted result
		if ( empty( $quiz_result ) ) {
			$quiz_result = self::get_quiz_results( $student_id, $course_id, $unit_id, $module_id );
		}

		$passed_class = ! empty( $quiz_result['passed'] ) ? 'passed' : 'not-passed';
		$passed_heading = ! empty( $quiz_result['passed'] ) ? __( 'Success!', 'CP_TD' ) : __( 'Quiz not passed.', 'CP_TD' );
		$passed_message = ! empty( $quiz_result['passed'] ) ? __( 'You have successfully passed the quiz. Here are your results.', 'CP_TD' ) : __( 'You did not pass the quiz this time. Here are your results.', 'CP_TD' );

		$template = '<div class="coursepress-quiz-results ' . esc_attr( $passed_class ) . '">
			<div class="quiz-message">
				<h3 class="result-title">' . $passed_heading . '</h3>
				<p class="result-message">' . $passed_message . '</p>
			</div>
			<div class="quiz-results">
				<table>
					<tr><th>' . esc_html__( 'Total Questions', 'CP_TD' ) . '</th><td>' . esc_html( $quiz_result['total_questions'] ) . '</td></tr>
					<tr><th>' . esc_html__( 'Correct', 'CP_TD' ) . '</th><td>' . esc_html( $quiz_result['correct'] ) . '</td></tr>
					<tr><th>' . esc_html__( 'Incorrect', 'CP_TD' ) . '</th><td>' . esc_html( $quiz_result['wrong'] ) . '</td></tr>
					<tr><th>' . esc_html__( 'Grade', 'CP_TD' ) . '</th><td>' . esc_html( $quiz_result['grade'] ) . '%</td></tr>
				</table>
			</div>
		</div>
		';

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
	 * @since  2.0.0
	 * @param  WP $wp The main WP object.
	 */
	public static function parse_request( $wp ) {
		if ( ! array_key_exists( 'coursepress_focus', $wp->query_vars ) ) {
			return;
		}
		$course_id = (int) $wp->query_vars['course'];
		$unit_id = (int) $wp->query_vars['unit'];
		$type = sanitize_text_field( $wp->query_vars['type'] );
		$item_id = (int) $wp->query_vars['item'];

		// Focus mode means:
		// We display the course item, no other theme/page elements.
		$shortcode = sprintf(
			'[coursepress_focus_item course="%d" unit="%d" type="%s" item_id="%d"]',
			$course_id,
			$unit_id,
			$type,
			$item_id
		);
		echo do_shortcode( $shortcode );
		die();
	}
}
