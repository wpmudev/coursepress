<?php

class CoursePress_Template_Module {

	private static function render_module_head( $module, $attributes = false ) {
		$content = '<div class="module-container module ' . $attributes['module_type'] . ' module-' . $module->ID . ' ' . $attributes['mode'] . '" data-type="' . $attributes['module_type'] . '" data-module="' . $module->ID . '">';

		$show_title = isset( $attributes['show_title'] ) ? $attributes['show_title'] : false;
		if ( $show_title ) {
			$content .= '<h4 class="module-title">' . $module->post_title . '</h4>';
		}

		return $content;
	}

	private static function render_module_result( $module, $attributes, $args ) {

		$course_id = $args['course_id'];
		$unit_id = $args['unit_id'];
		$module_id = $args['module_id'];
		$student_id = $args['student_id'];
		$student_progress = $args['student_data'];
		$response_key = $args['response_key'];
		$disabled = $args['disabled'];

		$content = '';

		$grade = CoursePress_Model_Student::get_grade( $student_id, $course_id, $unit_id, $module_id, $response_key, $student_progress );
		$feedback = CoursePress_Model_Student::get_feedback( $student_id, $course_id, $unit_id, $module_id, $response_key, $student_progress );


		$content .= '<div class="module-result">';
		if( $grade > -1 ) {
			$content .= '<div class="grade"><strong>' . esc_html__( 'Grade:', CoursePress::TD ) . '</strong> ' . $grade . '%</div>';
		} else {
			$content .= '<div class="grade"><strong>' . esc_html__( 'Ungraded', CoursePress::TD ) . '</strong></div>';
		}
		if( $attributes['minimum_grade'] > $grade && ! $disabled && CoursePress_Helper_Utility::fix_bool( $attributes['assessable'] ) ) {
			$content .= '<div class="resubmit"><a>' . esc_html__( 'Resubmit', CoursePress::TD ) . '</a></div>';
		}
		if( $feedback && ! empty( $feedback ) ) {
			$content .= '<div class="feedback"><strong>' . esc_html__( 'Feedback:', CoursePress::TD ) . '</strong><br/> ' . $feedback . '</div>';
		}

		$content .= '</div>';


		return $content;
	}

	public static function render_text( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		$content .= '</div>'; // module_footer
		return str_replace( array("\n", "\r" ), '', $content );
	}

	public static function render_image( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		$content .= '<div class="module-content">' . self::do_caption_media( $attributes ) . '</div>';

		$content .= '</div>'; // module_footer
		return str_replace( array("\n", "\r" ), '', $content );
	}

	public static function render_video( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		$content .= '<div class="module-content">' . self::do_caption_media( $attributes ) . '</div>';

		$content .= '</div>'; // module_footer
		return str_replace( array("\n", "\r" ), '', $content );
	}

	public static function render_audio( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		if ( isset( $attributes['audio_url'] ) ) {
			$loop     = isset( $attributes['loop'] ) ? CoursePress_Helper_Utility::fix_bool( $attributes['loop'] ) : false;
			$autoplay = isset( $attributes['autoplay'] ) ? CoursePress_Helper_Utility::fix_bool( $attributes['autoplay'] ) : false;
			$attr     = array(
				'src'      => $attributes['audio_url'],
				'loop'     => $loop,
				'autoplay' => $autoplay,
			);
			$content .= '<div class="module-content">
					<div class="audio_player">
						' . wp_audio_shortcode( $attr ) . '
					</div>
				</div>
			';
		}


		$content .= '</div>'; // module_footer
		return str_replace( array("\n", "\r" ), '', $content );
	}

	public static function render_download( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		if ( isset( $attributes['file_url'] ) ) {

			$url = $attributes['file_url'];

			$file_size = CoursePress_Helper_Utility::get_file_size( $url );

			if ( $file_size > 0 ) {
				$filesize = '<small>(' . esc_html( $file_size ) . ')</small>';
			} else {
				$filesize = '';
			}

			$url = CoursePress_Helper_Utility::encode( $url );
			$url = trailingslashit( home_url() ) . '?fdcpf=' . $url;

			$link_text = isset( $attributes['link_text'] ) ? $attributes['link_text'] : $module->post_title;
			$content .= '<div class="module-content">
					<div class="file_holder">
						<a href="' . esc_url( $url ) . '">' . esc_html( $link_text ) . ' ' . CoursePress_Helper_Utility::filter_content( $filesize ) . '</a>
					</div>
				</div>
			';
		}

		$content .= '</div>'; // module_footer
		return str_replace( array("\n", "\r" ), '', $content );
	}

	public static function render_zipped( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		if ( isset( $attributes['zip_url'] ) && ! empty( $attributes['primary_file'] ) ) {

			$url = $attributes['zip_url'];

			$url = CoursePress_Helper_Utility::encode( $url );
			$url = trailingslashit( home_url() ) . '?oacpf=' . $url . '&file=' . $attributes['primary_file'];

			$link_text = isset( $attributes['link_text'] ) ? $attributes['link_text'] : $module->post_title;
			$content .= '<div class="module-content">
					<div class="zip_holder">
						<a href="' . esc_url( $url ) . '">' . esc_html( $link_text ) . '</a>
					</div>
				</div>
			';
		}

		$content .= '</div>'; // module_footer
		return str_replace( array("\n", "\r" ), '', $content );
	}

	public static function render_section( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );
		$content .= '<hr />';
		$content .= '</div>'; // module_footer
		return str_replace( array("\n", "\r" ), '', $content );
	}

	public static function render_input_checkbox( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Model_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		if( ! empty( $attributes['answers'] ) ) {
			$responses = CoursePress_Helper_Utility::get_array_val( $student_progress, 'units/' . $unit_id . '/responses/' . $module_id );

			$element_class = ! empty( $responses ) ? 'hide' : '';
			$response_count = ! empty( $responses ) ? count( $responses ) : 0;
			//$attributes['retry_attempts'] = 3; // DEBUG
			$disabled = ! $attributes['allow_retries'];
			$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

			// RESUBMIT LOGIC
			$action = ! $disabled ? '<a class="module-submit-action button">' . esc_html__( "Submit Answer", CoursePress::TD ) . '</a>' : '';

			$disabled_attr = $disabled ? 'disabled="disabled"' : '';
			$content .= '<div class="module-elements ' . $element_class . '">';

			$content .= '
						<input type="hidden" name="course_id" value="' . $course_id . '" />
						<input type="hidden" name="unit_id" value="' . $unit_id . '" />
						<input type="hidden" name="module_id" value="' . $module_id . '" />
						<input type="hidden" name="student_id" value="' . get_current_user_id() . '" />';

			$content .= '<ul style="list-style: none;">';

			$oddeven = 'odd';
			$alt = '';
			foreach( $attributes['answers'] as $key => $answer ) {
				$content .= '<li class="' . $oddeven . ' ' . $alt . '">' .
				            '<input type="checkbox" value="' . esc_attr( $key ) .'" name="module-' . $module->ID . '" ' . $disabled_attr . ' /> ' .  esc_html( $answer ) .
				            '</li>';
				$oddeven = 'odd' === $oddeven ? 'even' : 'odd';
				$alt = empty( $alt ) ? 'alt' : '';
			}

			$content .= '</ul>';

			$content .= $action;

			$content .= '</div>'; // module-elements

			if( ! empty( $responses ) ) {

				$last_response = (array) $responses[ $response_count - 1 ];
				$response_key = array_pop( array_keys( $responses ) );

				$content .= '<div class="module-response">';

				$content .= '<ul>';
				foreach( $attributes['answers'] as $key => $answer ) {
					$the_answer = in_array( $key, $attributes['answers_selected'] );
					$student_answer = in_array( $key,$last_response );

					$class = '';
					if( $student_answer && $the_answer ) {
						$class = 'chosen-answer correct';
					} elseif( $student_answer && ! $the_answer ) {
						$class = 'chosen-answer incorrect';
					} elseif( ! $student_answer && $the_answer ) {
						$class = 'incorrect';
					}

					$content .= '<li class="' . $class . '">' . $answer . '</li>';

				}
				$content .= '</ul>';

				$content .= '</div>';

				// Render Response and Feedback
				$args = array(
					'course_id' => $course_id,
					'unit_id' => $unit_id,
					'module_id' => $module_id,
					'student_id' => get_current_user_id(),
					'student_data' => $student_progress,
					'response_key' => $response_key,
					'disabled' => $disabled
				);
				$content .= self::render_module_result( $module, $attributes, $args);

			}
		}


		$content .= '</div>'; // module_footer
		return str_replace( array("\n", "\r" ), '', $content );
	}

	public static function render_input_radio( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Model_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		if( ! empty( $attributes['answers'] ) ) {
			$responses = CoursePress_Helper_Utility::get_array_val( $student_progress, 'units/' . $unit_id . '/responses/' . $module_id );

			$element_class = ! empty( $responses ) ? 'hide' : '';
			$response_count = ! empty( $responses ) ? count( $responses ) : 0;
			//$attributes['retry_attempts'] = 3; // DEBUG
			$disabled = ! $attributes['allow_retries'];
			$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

			// RESUBMIT LOGIC
			$action = ! $disabled ? '<a class="module-submit-action button">' . esc_html__( "Submit Answer", CoursePress::TD ) . '</a>' : '';

			$disabled_attr = $disabled ? 'disabled="disabled"' : '';
			$content .= '<div class="module-elements ' . $element_class . '">';

			$content .= '
						<input type="hidden" name="course_id" value="' . $course_id . '" />
						<input type="hidden" name="unit_id" value="' . $unit_id . '" />
						<input type="hidden" name="module_id" value="' . $module_id . '" />
						<input type="hidden" name="student_id" value="' . get_current_user_id() . '" />';

			$content .= '<ul style="list-style: none;">';

			// RESUBMIT LOGIC
			$action = '<a class="module-submit-action button">' . esc_html__( "Submit Answer", CoursePress::TD ) . '</a>';

			$oddeven = 'odd';
			$alt = '';
			foreach( $attributes['answers'] as $key => $answer ) {
				$content .= '<li class="' . $oddeven . ' ' . $alt . '">' .
				            '<input type="radio" value="' . esc_attr( $key ) .'" name="module-' . $module->ID . '" ' . $disabled_attr . ' /> ' .  esc_html( $answer ) .
				            '</li>';

				$oddeven = 'odd' === $oddeven ? 'even' : 'odd';
				$alt = empty( $alt ) ? 'alt' : '';
			}

			$content .= '</ul>';

			$content .= $action;

			$content .= '</div>'; // module-elements

			if( ! empty( $responses ) ) {

				$last_response = $responses[ $response_count - 1 ];
				$response_key = array_pop( array_keys( $responses ) );

				$content .= '<div class="module-response">';

				$content .= '<ul>';
				foreach( $attributes['answers'] as $key => $answer ) {
					$the_answer = (int) $attributes['answers_selected'] === (int) $key;
					$student_answer = (int) $last_response === (int) $key;

					$class = '';
					if( $student_answer && $the_answer ) {
						$class = 'chosen-answer correct';
					} elseif( $student_answer && ! $the_answer ) {
						$class = 'chosen-answer incorrect';
					} elseif( ! $student_answer && $the_answer ) {
						$class = 'incorrect';
					}

					$content .= '<li class="' . $class . '">' . $answer . '</li>';

				}
				$content .= '</ul>';

				$content .= '</div>';

				// Render Response and Feedback
				$args = array(
					'course_id' => $course_id,
					'unit_id' => $unit_id,
					'module_id' => $module_id,
					'student_id' => get_current_user_id(),
					'student_data' => $student_progress,
					'response_key' => $response_key,
					'disabled' => $disabled
				);
				$content .= self::render_module_result( $module, $attributes, $args);

			}
		}

		$content .= '</div>'; // module_footer
		return str_replace( array("\n", "\r" ), '', $content );
	}

	public static function render_input_select( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Model_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		if( ! empty( $attributes['answers'] ) ) {

			$responses = CoursePress_Helper_Utility::get_array_val( $student_progress, 'units/' . $unit_id . '/responses/' . $module_id );

			$element_class = ! empty( $responses ) ? 'hide' : '';
			$response_count = ! empty( $responses ) ? count( $responses ) : 0;
			//$attributes['retry_attempts'] = 3; // DEBUG
			$disabled = ! $attributes['allow_retries'];
			$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

			// RESUBMIT LOGIC
			$action = ! $disabled ? '<a class="module-submit-action button">' . esc_html__( "Submit Answer", CoursePress::TD ) . '</a>' : '';

			$disabled_attr = $disabled ? 'disabled="disabled"' : '';
			$content .= '<div class="module-elements ' . $element_class . '">';

			$content .= '
						<input type="hidden" name="course_id" value="' . $course_id . '" />
						<input type="hidden" name="unit_id" value="' . $unit_id . '" />
						<input type="hidden" name="module_id" value="' . $module_id . '" />
						<input type="hidden" name="student_id" value="' . get_current_user_id() . '" />';

			$content .= '<select class="wide" name="module-' . $module->ID . '" ' . $disabled_attr . '>';


			foreach( $attributes['answers'] as $key => $answer ) {
				$content .= '<option value="' . $key . '">' .
				            esc_html( $answer ) .
				            '</option>';
			}

			$content .= '</select>';

			$content .= $action;

			$content .= '</div>';

			if( ! empty( $responses ) ) {

				$last_response = $responses[ $response_count - 1 ];
				$response_key = array_pop( array_keys( $responses ) );

				$content .= '<div class="module-response">';

				$content .= '<ul>';
				foreach( $attributes['answers'] as $key => $answer ) {
					$the_answer = (int) $attributes['answers_selected'] === (int) $key;
					$student_answer = (int) $last_response === (int) $key;

					$class = '';
					if( $student_answer && $the_answer ) {
						$class = 'chosen-answer correct';
					} elseif( $student_answer && ! $the_answer ) {
						$class = 'chosen-answer incorrect';
					} elseif( ! $student_answer && $the_answer ) {
						$class = 'incorrect';
					}

					$content .= '<li class="' . $class . '">' . $answer . '</li>';

				}
				$content .= '</ul>';
				//$meh = '<p><span class="label">' . esc_html__( 'Response: ', CoursePress::TD ) . '</span>
				//	' . $attributes['answers'][ (int) $last_response ] . '
				//</p>';

				$content .= '</div>';

				// Render Response and Feedback
				$args = array(
					'course_id' => $course_id,
					'unit_id' => $unit_id,
					'module_id' => $module_id,
					'student_id' => get_current_user_id(),
					'student_data' => $student_progress,
					'response_key' => $response_key,
					'disabled' => $disabled
				);
				$content .= self::render_module_result( $module, $attributes, $args);

			}


		}

		$content .= '</div>'; // module_footer
		return str_replace( array("\n", "\r" ), '', $content );
	}

	public static function render_input_text( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Model_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		$responses = CoursePress_Helper_Utility::get_array_val( $student_progress, 'units/' . $unit_id . '/responses/' . $module_id );

		$element_class = ! empty( $responses ) ? 'hide' : '';
		$response_count = ! empty( $responses ) ? count( $responses ) : 0;
		//$attributes['retry_attempts'] = 3; // DEBUG
		$disabled = ! $attributes['allow_retries'];
		$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

		// RESUBMIT LOGIC
		$action = ! $disabled ? '<a class="module-submit-action button">' . esc_html__( "Submit Answer", CoursePress::TD ) . '</a>' : '';

		$disabled_attr = $disabled ? 'disabled="disabled"' : '';
		$content .= '<div class="module-elements ' . $element_class . '">
						<input type="hidden" name="course_id" value="' . $course_id . '" />
						<input type="hidden" name="unit_id" value="' . $unit_id . '" />
						<input type="hidden" name="module_id" value="' . $module_id . '" />
						<input type="hidden" name="student_id" value="' . get_current_user_id() . '" />
						<input type="textbox" name="module-' . $module->ID . '" ' . $disabled_attr . ' />
						' . $action . '
					</div>';

		if( ! empty( $responses ) ) {

			$last_response = $responses[ $response_count - 1 ];
			$response_key = array_pop( array_keys( $responses ) );

			$content .= '<div class="module-response">
				<p><span class="label">' . esc_html__( 'Response: ', CoursePress::TD ) . '</span>
					' . $last_response . '
				</p>
			</div>';

			// Render Response and Feedback
			$args = array(
				'course_id' => $course_id,
				'unit_id' => $unit_id,
				'module_id' => $module_id,
				'student_id' => get_current_user_id(),
				'student_data' => $student_progress,
				'response_key' => $response_key,
				'disabled' => $disabled
			);
			$content .= self::render_module_result( $module, $attributes, $args);

		}

		$content .= '</div>'; // module_footer
		return str_replace( array("\n", "\r" ), '', $content );
	}

	public static function render_input_textarea( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Model_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		$responses = CoursePress_Helper_Utility::get_array_val( $student_progress, 'units/' . $unit_id . '/responses/' . $module_id );

		$element_class = ! empty( $responses ) ? 'hide' : '';
		$response_count = ! empty( $responses ) ? count( $responses ) : 0;
		//$attributes['retry_attempts'] = 3; // DEBUG
		$disabled = ! $attributes['allow_retries'];
		$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

		// RESUBMIT LOGIC
		$action = ! $disabled ? '<a class="module-submit-action button">' . esc_html__( "Submit Answer", CoursePress::TD ) . '</a>' : '';

		$disabled_attr = $disabled ? 'disabled="disabled"' : '';
		$content .= '<div class="module-elements ' . $element_class . '">
						<input type="hidden" name="course_id" value="' . $course_id . '" />
						<input type="hidden" name="unit_id" value="' . $unit_id . '" />
						<input type="hidden" name="module_id" value="' . $module_id . '" />
						<input type="hidden" name="student_id" value="' . get_current_user_id() . '" />
						<textarea name="module-' . $module->ID . '" ' . $disabled_attr . '></textarea>
						' . $action . '
					</div>';

		if( ! empty( $responses ) ) {

			$last_response = $responses[ $response_count - 1 ];
			$response_key = array_pop( array_keys( $responses ) );

			$content .= '<div class="module-response">
				<p><span class="label">' . esc_html__( 'Response: ', CoursePress::TD ) . '</span>
					' . $last_response . '
				</p>
			</div>';

			// Render Response and Feedback
			$args = array(
				'course_id' => $course_id,
				'unit_id' => $unit_id,
				'module_id' => $module_id,
				'student_id' => get_current_user_id(),
				'student_data' => $student_progress,
				'response_key' => $response_key,
				'disabled' => $disabled
			);
			$content .= self::render_module_result( $module, $attributes, $args);

		}

		$content .= '</div>'; // module_footer
		return str_replace( array("\n", "\r" ), '', $content );
	}

	public static function render_input_upload( $module, $attributes = false ) {
		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Model_Student::get_completion_data( get_current_user_id(), $course_id );

		$content = self::render_module_head( $module, $attributes );

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		$responses = CoursePress_Helper_Utility::get_array_val( $student_progress, 'units/' . $unit_id . '/responses/' . $module_id );

		$element_class = ! empty( $responses ) ? 'hide' : '';
		$response_count = ! empty( $responses ) ? count( $responses ) : 0;
		//$attributes['retry_attempts'] = 3; // DEBUG
		$disabled = ! $attributes['allow_retries'];
		$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

		// RESUBMIT LOGIC
		$action = ! $disabled ? '<a class="module-submit-action button">' . esc_html__( "Submit File", CoursePress::TD ) . '</a>' : '';

		$disabled_attr = $disabled ? 'disabled="disabled"' : '';
		$content .= '<div class="module-elements ' . $element_class . '">
						<form method="POST" enctype="multipart/form-data">
						<input type="hidden" name="course_action" value="upload-file" />
						<input type="hidden" name="course_id" value="' . $course_id . '" />
						<input type="hidden" name="unit_id" value="' . $unit_id . '" />
						<input type="hidden" name="module_id" value="' . $module_id . '" />
						<input type="hidden" name="student_id" value="' . get_current_user_id() . '" />
						<input type="file" name="module-' . $module_id . '" ' . $disabled_attr . ' />
						' . $action . ' <span class="upload-progress"></span>
						</form>
					</div>';

		if( ! empty( $responses ) ) {

			$last_response = $responses[ $response_count - 1 ];
			$response_key = array_pop( array_keys( $responses ) );

			if ( isset( $last_response['url'] ) ) {

				$url = $last_response['url'];

				$file_size = CoursePress_Helper_Utility::get_file_size( $url );

				if ( $file_size > 0 ) {
					$filesize = '<small>(' . esc_html( $file_size ) . ')</small>';
				} else {
					$filesize = '';
				}

				$url = CoursePress_Helper_Utility::encode( $url );
				$url = trailingslashit( home_url() ) . '?fdcpf=' . $url;

				$file_name = explode( '/', $last_response['url'] );
				$file_name = array_pop( $file_name );

				$content .= '<div class="module-response">
					<p class="file_holder"><span class="label">' . esc_html__( 'Your file: ', CoursePress::TD ) . '</span>
						<a href="' . esc_url( $url ) . '">' . esc_html( $file_name ) . ' ' . CoursePress_Helper_Utility::filter_content( $filesize ) . '</a>
					</p>
				</div>';
			}

			// Render Response and Feedback
			$args = array(
				'course_id' => $course_id,
				'unit_id' => $unit_id,
				'module_id' => $module_id,
				'student_id' => get_current_user_id(),
				'student_data' => $student_progress,
				'response_key' => $response_key,
				'disabled' => $disabled
			);
			$content .= self::render_module_result( $module, $attributes, $args);

		}

		$content .= '</div>'; // module_footer
		return str_replace( array("\n", "\r" ), '', $content );

	}

	private static function do_caption_media( $data ) {

		if ( empty( $data['image_url'] ) && empty( $data['video_url'] ) ) {
			return '';
		}

		$the_caption = '';
		$alt_text    = '';
		$media_width = '';

		$type = $data['module_type'];
		if ( 'video' === $type ) {
			$url = $data['video_url'];
		}
		if ( 'image' === $type ) {
			$url = $data['image_url'];
		}


		$caption_source = isset( $data['caption_field'] ) ? $data['caption_field'] : 'media';
		$attachment     = CoursePress_Helper_Utility::attachment_from_url( $url );

		if ( 'media' === $caption_source ) {

			if ( ! empty( $attachment ) ) {

				$alt_text = CoursePress_Helper_Utility::filter_content( get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) );
				if ( empty( $alt_text ) ) {
					$alt_text = isset( $data['caption_custom_text'] ) ? CoursePress_Helper_Utility::filter_content( $data['caption_custom_text'] ) : '';
				}

				$meta        = wp_get_attachment_metadata( $attachment->ID );
				$media_width = $meta['width'];

				$the_caption = $attachment->post_excerpt;

			} else {
				$the_caption = '';
				$alt_text    = isset( $data['caption_custom_text'] ) ? CoursePress_Helper_Utility::filter_content( $data['caption_custom_text'] ) : '';
			}

		} else {

			$alt_text = isset( $data['caption_custom_text'] ) ? CoursePress_Helper_Utility::filter_content( $data['caption_custom_text'] ) : '';

			global $content_width;
			if ( ! empty( $content_width ) ) {
				$media_width = $content_width;
			} else {
				$media_width = get_option( 'large_size_w' );
			}

			// Get the custom caption text
			$the_caption = isset( $data['caption_custom_text'] ) ? CoursePress_Helper_Utility::filter_content( $data['caption_custom_text'] ) : '';
		}

		$html = '';

		$show_caption = isset( $data['show_media_caption'] ) ? CoursePress_Helper_Utility::fix_bool( $data['show_media_caption'] ) : false;

		if ( ! empty( $attachment ) ) {
			$attachment_id = ' id="attachment_' . $attachment->ID . '" ';
		} else {
			$attachment_id = ' id="attachment_' . time() . '" ';
		}

		if ( 'image' === $type ) {

			if ( $show_caption ) {

				$html .= '<div class="image_holder">';
				$img = '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt_text ) . '" />';
				$html .= do_shortcode( '[caption width="' . $media_width . '"' . $attachment_id . ']' . $img . ' ' . $the_caption . '[/caption]' );
				$html .= '</div>';
			} else {

				$html .= '<div class="image_holder">';
				$html .= '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt_text ) . '" />';
				$html .= '</div>';
			}
		}

		if ( 'video' === $type ) {

			$video_extension = pathinfo( $url, PATHINFO_EXTENSION );
			$hide_related    = isset( $data['hide_related_media'] ) ? CoursePress_Helper_Utility::fix_bool( $data['hide_related_media'] ) : false;

			if ( $hide_related ) {
				add_filter( 'oembed_result', array( __CLASS__, 'remove_related_videos' ), 10, 3 );
			}

			$video = '';
			if ( ! empty( $video_extension ) ) {//it's file, most likely on the server
				$attr  = array(
					'src' => $url,
				);
				$video = wp_video_shortcode( $attr );
			} else {
				$embed_args = array();

				$video = wp_oembed_get( $url, $embed_args );
				if ( ! $video ) {
					$video = apply_filters( 'the_content', "[embed]" . $url . "[/embed]" );
				}
			}

			if ( $show_caption ) {

				$html .= '<div class="video_holder">';
				$html .= '<figure ' . $attachment_id . ' class="wp-caption" style="width: ' . $media_width . 'px;">';
				$html .= '<div class="video_player">';
				$html .= $video;
				$html .= '</div>';
				if ( ! empty( $the_caption ) ) {
					$html .= '<figcaption class="wp-caption-text">' . $the_caption . '</figcaption>';
				}
				$html .= '</figure>';
				$html .= '</div>';

			} else {

				$html .= '<div class="video_player">';
				$html .= $video;
				$html .= '</div>';
			}


		}

		return $html;
	}

	public static function remove_related_videos( $html, $url, $args ) {

		$newargs                   = $args;
		$newargs['rel']            = 0;
		$newargs['modestbranding'] = 1;

		// build the query url
		$parameters = http_build_query( $newargs );

		// YouTube
		$html = str_replace( 'feature=oembed', 'feature=oembed&' . $parameters, $html );

		return $html;
	}

}