<?php
/**
 * Module Template
 **/
class CoursePress_Template_Module {
	private static $args = array();

	protected static $student_progress = array();

	/**
	 * Helper function get module attributes.
	 **/
	protected static function attributes( $module_id ) {
		return CoursePress_Data_Module::attributes( $module_id );
	}

	/**
	 * Helper function to quickly retrieve student response
	 **/
	protected static function get_response( $module_id, $student_id, $all = false ) {
		$unit_id = get_post_field( 'post_parent', $module_id );
		$course_id = get_post_field( 'post_parent', $unit_id );

		$response = CoursePress_Data_Student::get_response( $student_id, $course_id, $unit_id, $module_id );

		if ( false === $all && ! empty( $response['response'] ) ) {
			$response = $response['response'];
		}

		return $response;
	}

	public static function get_student_answer( $module_id, $student_id ) {
		$attributes = self::attributes( $module_id );
		$module_type = $attributes['module_type'];
		$unit_id = get_post_field( 'post_parent', $module_id );
		$course_id = get_post_field( 'post_parent', $unit_id );
		$content = '';
		$response = self::get_response( $module_id, $student_id );

		switch( $module_type ) {
			case 'input-checkbox': case 'input-radio':
				$answers = $attributes['answers'];
				$selected = (array) $attributes['answers_selected'];
				$content .= '<ul class="cp-answers">';

				foreach ( $answers as $key => $answer ) {
					$the_answer = in_array( $key, $selected );
					$student_answer = is_array( $response ) ? in_array( $key, $response ) : $response == $key;

					if ( 'input-radio' === $module_type ) {
						$student_answer = $response == $key;
					}

					if ( $student_answer ) {
						if ( $the_answer ) {
							$answer = '<span class="chosen-answer correct"></span>' . $answer;
						} else {
							$answer = '<span class="chosen-answer incorrect"></span>' . $answer;
						}
						$content .= sprintf( '<li>%s</li>', $answer );
					}
				}
				$content .= '</ul>';

				break;
			case 'input-textarea': case 'input-text':
				if ( ! empty( $response ) ) {
					$content .= sprintf( '<div class="cp-answer-box">%s</div>', $response );
				}
				break;

			case 'input-upload':
				if ( ! empty( $response['url'] ) ) {
					$url = $response['url'];
					$filename = basename( $url );
					$url = CoursePress_Helper_Utility::encode( $url );
					$url = trailingslashit( home_url() ) . '?fdcpf=' . $url;

					$content .= sprintf( '<a href="%s" class="button-primary cp-download">%s</a>', esc_url( $url ), $filename );
				}
				break;

			case 'input-quiz':
				if ( is_admin() ) {
					if ( ! empty( $attributes['questions'] ) ) {
						$questions = $attributes['questions'];
	
						foreach ( $questions as $q_index => $question ) {
							$options = (array) $question['options'];
							$checked = (array) $options['checked'];
							$checked = array_filter( $checked );
							$student_response = $response[ $q_index ];

							$content .= sprintf( '<div class="cp-q"><hr /><p class="description cp-question">%s</p><ul>', esc_html( $question['question']  ) );

							foreach ( $options['answers'] as $p_index => $answer ) {
								$the_answer = isset( $checked[ $p_index ] ) ? $checked[ $p_index ] : false;
								$student_answer = '';

								if ( isset( $student_response[ $p_index ] ) && $student_response[ $p_index ] ) {
									$student_answer = $student_response[ $p_index ];

									if ( $the_answer ) {
										$student_answer = '<span class="chosen-answer correct"></span>';
									} else {
										$student_answer = '<span class="chosen-answer incorrect"></span>';
									}
									$content .= '<li>' . $student_answer . esc_html( $answer ) . '</li>';
								}
							}
							$content .= '</ul></div>';

						}
					}
				} else {
					$quiz_result = CoursePress_Data_Module::get_quiz_results( $student_id, $course_id, $unit_id, $module_id );
					$content .= CoursePress_Data_Module::quiz_result_content( $student_id, $course_id, $unit_id, $module_id, $quiz_result );
				}
				break;

			case 'input-form':
				if ( ! empty( $attributes['questions'] ) ) {
					$questions = $attributes['questions'];

					foreach ( $questions as $q_index => $question ) {
						$student_response = ! empty( $response[ $q_index ] ) ? $response[ $q_index ] : '';
						$format = '<div class="cp-q"><hr /><p class="description cp-question">%s</p>';
						$content .= sprintf( $format, esc_html( $question['question'] ) );
						$content .= '<ul>';

						if ( 'selectable' == $question['type'] ) {
							$options = $question['options']['answers'];
							$checked = $question['options']['checked'];

							foreach ( $options as $ai => $answer ) {
								if ( $student_response == $ai ) {
									$the_answer = ! empty( $checked[ $ai ] );

									if ( $the_answer === $student_response ) {
										$student_answer = '<span class="chosen-answer correct"></span>';
									} else {
										$student_answer = '<span class="chosen-answer incorrect"></span>';
									}
									$content .= sprintf( '<li>%s %s</li>', $student_answer, $answer );
								}
							}
						} else {
							$content .= sprintf( '<li>%s</li>', esc_html( $student_response ) );
						}
						$content .= '</ul></div>';
					}
				}
			break;
		}

		/**
		 * Filter the student response template.
		 **/
		$content = apply_filters( 'coursepress_student_reponse_template', $content, $module_id, $module_type );

		return $content;
	}

	public static function get_module_status( $module_id, $student_id ) {
		$attributes = self::attributes( $module_id );
		$module_type = $attributes['module_type'];
		$assessables = array( 'input-text', 'input-textarea', 'input-upload' );
		$response = self::get_response( $module_id, $student_id, true );
		$grades = CoursePress_Helper_Utility::get_array_val( $response, 'grades' );
		$grades = array_pop( $grades );
		$grade = (int) CoursePress_Helper_Utility::get_array_val( $grades, 'grade' );
		$minimum_grade = (int) $attributes['minimum_grade'];
		$require_assessment = false;

		if ( ! empty( $attributes['assessable'] ) || ! empty( $attributes['instructor_assessable'] ) ) {
			$graded_by = CoursePress_Helper_Utility::get_array_val( $grades, 'graded_by' );

			if ( 'auto' == $graded_by ) {
				$grade = 0;
				$require_assessment = true;
			}
		}
		$pass = $grade >= $minimum_grade;
		$status = $pass ? __( 'Pass', 'cp' ) : __( 'Fail', 'cp' );
		$status = $require_assessment ? __( 'Pending', 'cp' ) : $status;

		return $status;
	}

	public static function template( $module_id = 0, $is_focus = false ) {
		if ( empty( $module_id ) ) {
			return ''; // Nothing to process, bail!
		}

		$attributes = self::attributes( $module_id );
		$module_type = $attributes['module_type'];
		$method = 'render_' . str_replace( '-', '_', $module_type );
		$module = get_post( $module_id );
		$unit_id = $module->post_parent;
		$course_id = get_post_field( 'post_parent', $unit_id );
		$course_status = CoursePress_Data_Course::get_course_status( $course_id );
		$is_module_answerable = preg_match( '%input-%', $module_type );
		$disabled = false;
		$element_class = '';
		$student_id = get_current_user_id();

		// Module header
		$content = self::render_module_head( $module, $attributes );
		// The question or text content
		$content .= self::_wrap_content( $module->post_content );

		if ( $is_focus ) {
			$content .= sprintf( '<input type="hidden" name="course_id" value="%s" />', $course_id );
			$content .= sprintf( '<input type="hidden" name="unit_id" value="%s" />', $unit_id );
			$content .= wp_nonce_field( 'coursepress_submit_modules', '_wpnonce', false, false );
		}

		if ( method_exists( __CLASS__, $method ) ) {
			// Get student progress if it is not retrieve yet
			if ( empty( self::$student_progress ) ) {
				$student_progress = self::$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
			} else {
				$student_progress = self::$student_progress;
			}

			// Record module visit
			CoursePress_Data_Student::visited_module( $student_id, $course_id, $unit_id, $module_id, self::$student_progress );

			$retry = 'TRY';
			if ( $is_module_answerable ) {
				$responses = CoursePress_Data_Student::get_responses( $student_id, $course_id, $unit_id, $module_id, true, $student_progress );
				$element_class = ! empty( $responses ) ? 'hide' : '';
				$response_count = ! empty( $responses ) ? count( $responses ) : 0;

				$retry = sprintf( '<a data-module="%s" class="module-submit-action button-reload-module">%s</a>', $module_id, __( 'Try again', 'cp' ) );

				// Check if retry is disabled
				if ( ! empty( $attributes['allow_retries'] ) && 0 < $response_count ) {
					$attempts = (int) $attributes['retry_attempts'];
					if ( $attempts >= $response_count ) {
						$disabled = true;
						$retry = '';
					}
				}
			}

			if ( 'closed' == $course_status ){
				$disabled = true;
				$retry = '';
			}

			$disabled_attr = $disabled ? 'disabled="disabled"' : '';
			$module_elements = call_user_func( array( __CLASS__, $method ), $module, $attributes, $student_progress );

			$module_elements = sprintf( '<div id="cp-element-%s" class="module-elements %s">%s</div>', $module_id, $element_class, $module_elements, $disabled );

			if ( $is_module_answerable && ! empty( $responses ) ) {

				$status = self::get_module_status( $module->ID, $student_id );
				$student_answers = sprintf( '<span class="cp-status">%s</span>', $status );
				$student_answers = sprintf( '<div class="cp-student-status">%s</div>', $student_answers . $retry );
				$student_answers .= self::get_student_answer( $module->ID, $student_id );

				$module_elements .= sprintf( '<div id="cp-response-%s" class="module-response">%s</div>', $module_id, $student_answers );
			}

			if ( 'closed' == $course_status ) {
				$format = '<div class="module-warnings"><p>%s</p></div>';
				$module_warning = sprintf( $format, esc_html__( 'This course is completed, you can not submit answers anymore.', 'cp' ) );

				/**
				 * Filter the warning message.
				 *
				 * @since 2.0
				 **/
				$module_elements .= apply_filters( 'coursepress_course_readonly_message', $module_warning, $module_id, $course_id );
			}

			/**
			 * Filter the module elements template.
			 *
			 * @since 2.0
			 **/
			$content .= apply_filters( 'coursepress_module_template', $module_elements, $module_type, $module_id );
		}

		return $content;
	}


	/**
	 * Module header
	 **/
	private static function render_module_head( $module, $attributes = false ) {
		if ( false === $module || empty( $module ) ) {
			return; // Bail
		}

		$attributes = false === $attributes ? self::attributes( $module->ID ) : $attributes;
		$content = '';

		$show_title = isset( $attributes['show_title'] ) ? $attributes['show_title'] : false;
		$mandatory = isset( $attributes['mandatory'] ) ? $attributes['mandatory'] : false;

		if ( $show_title ) {
			$content .= sprintf( '<h4 class="module-title">%s</h4>', $module->post_title );
		}

		if ( $mandatory ) {
			$content .= sprintf( '<div class="is-mandatory">%s</div>', __( 'Required', 'cp' ) );
		}

		$format = '<div class="module-header module %1$s module-%2$s %3$s" data-type="%1$s" data-module="%2$s">%4$s</div>';
		$content = sprintf( $format, $attributes['module_type'], $module->ID, $attributes['mode'], $content );

		/**
		 * Filter the module header template.
		 *
		 * @since 2.0
		 **/
		$content = apply_filters( 'coursepress_module_header', $content, $module );

		return $content;
	}

	public static function render_image( $module, $attributes = false ) {
		$content = self::do_caption_media( $attributes );

		return $content;
	}

	private static function do_caption_media( $data ) {
		if ( empty( $data['image_url'] ) && empty( $data['video_url'] ) ) {
			return '';
		}

		$the_caption = '';
		$alt_text = '';
		$media_width = '';

		$type = $data['module_type'];
		if ( 'video' === $type ) {
			$url = $data['video_url'];
		}
		if ( 'image' === $type ) {
			$url = $data['image_url'];
		}

		$caption_source = isset( $data['caption_field'] ) ? $data['caption_field'] : 'media';
		$attachment = CoursePress_Helper_Utility::attachment_from_url( $url );

		if ( 'media' === $caption_source ) {

			if ( ! empty( $attachment ) ) {

				$alt_text = CoursePress_Helper_Utility::filter_content( get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) );
				if ( empty( $alt_text ) ) {
					$alt_text = isset( $data['caption_custom_text'] ) ? CoursePress_Helper_Utility::filter_content( $data['caption_custom_text'] ) : '';
				}

				$meta = wp_get_attachment_metadata( $attachment->ID );
				$media_width = $meta['width'];

				$the_caption = $attachment->post_excerpt;

			} else {
				$the_caption = '';
				$alt_text = isset( $data['caption_custom_text'] ) ? CoursePress_Helper_Utility::filter_content( $data['caption_custom_text'] ) : '';
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

		$show_caption = isset( $data['show_media_caption'] ) ? cp_is_true( $data['show_media_caption'] ) : false;

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
			$hide_related = isset( $data['hide_related_media'] ) ? cp_is_true( $data['hide_related_media'] ) : false;

			if ( $hide_related ) {
				add_filter( 'oembed_result', array( 'CoursePress_Helper_Utility', 'remove_related_videos' ), 10, 3 );
			}

			$video = '';
			if ( ! empty( $video_extension ) ) {// it's file, most likely on the server
				$attr = array(
					'src' => $url,
				);
				$video = wp_video_shortcode( $attr );
			} else {
				$embed_args = array();

				$video = wp_oembed_get( $url, $embed_args );
				if ( ! $video ) {
					$video = apply_filters( 'the_content', '[embed]' . $url . '[/embed]' );
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

	public static function render_video( $module, $attributes = false ) {
		$content = self::do_caption_media( $attributes );

		return $content;
	}

	public static function render_audio( $module, $attributes = false ) {
		$content = '';

		if ( isset( $attributes['audio_url'] ) ) {
			$loop = isset( $attributes['loop'] ) ? cp_is_true( $attributes['loop'] ) : false;
			$autoplay = isset( $attributes['autoplay'] ) ? cp_is_true( $attributes['autoplay'] ) : false;
			$attr = array(
				'src' => $attributes['audio_url'],
				'loop' => $loop,
				'autoplay' => $autoplay,
			);
			$content .= '<div class="module-content">
					<div class="audio_player">
						' . wp_audio_shortcode( $attr ) . '
					</div>
				</div>
			';
		}

		return $content;
	}

	public static function render_download( $module, $attributes = false ) {
		$content = '';

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
			$after_content = sprintf(
				'<div class="file_holder"><a href="%s">%s <span class="file-size">%s</span></a></div>',
				esc_url( $url ),
				esc_html( $link_text ),
				CoursePress_Helper_Utility::filter_content( $filesize )
			);
			$content .= self::_wrap_content( $module->post_content, $after_content );
		}

		return $content;
	}

	public static function render_zipped( $module, $attributes = false ) {
		$content = '';

		if ( isset( $attributes['zip_url'] ) && ! empty( $attributes['primary_file'] ) ) {

			$url = $attributes['zip_url'];

			$url = CoursePress_Helper_Utility::encode( $url );
			$url = trailingslashit( home_url() ) . '?oacpf=' . $url . '&module=' . $module->ID . '&file=' . $attributes['primary_file'];

			$link_text = isset( $attributes['link_text'] ) ? $attributes['link_text'] : $module->post_title;
			$after_content = sprintf(
				'<div class="zip_holder"><a href="%s">%s</a></div>',
				esc_url( $url ),
				esc_html( $link_text )
			);
			$content .= self::_wrap_content( $module->post_content, $after_content );
		}

		return $content;
	}

	public static function render_section( $module, $attributes = false ) {
		return '<hr />';
	}

	public static function render_discussion( $module, $attributes = false ) {

	}

	public static function render_input_checkbox( $module, $attributes = false, $student_progress = false, $disabled = false ) {
		$attributes = false === $attributes ? self::attributes( $module->ID ) : $attributes;
		$student_progress = false === $student_progress ? self::$student_progress : $student_progress;
		$content = '';

		if ( ! empty( $attributes['answers'] ) ) {
			$disabled_attr = true === $disabled ? 'disabled="disabled"' : '';
			$oddeven = 'odd';
			$alt = '';
			$response = self::get_response( $module->ID, get_current_user_id() );

			$content .= '<ul style="list-style:none;">';
			foreach ( $attributes['answers'] as $key => $answer ) {
				$checked = ' ' . checked( 1, is_array( $response ) && in_array( $key, $response ), false );

				$format = '<li class="%1$s %2$s"><label for="module-%3$s-%5$s">%4$s</label> <input type="checkbox" value="%5$s" name="module[%3$s][]" id="module-%3$s-%5$s" %6$s /></li>';
				$content .= sprintf( $format, $oddeven, $alt, $module->ID, esc_html__( $answer ), esc_attr( $key ), $disabled_attr . $checked );

				$oddeven = 'odd' === $oddeven ? 'even' : 'odd';
			}
			$content .= '</ul>';
		}

		return $content;
	}

	public static function render_input_radio( $module, $attributes = false, $student_progress = false, $disabled = false ) {
		$attributes = false === $attributes ? self::attributes( $module->ID ) : $attributes;
		$student_progress = false === $student_progress ? self::$student_progress : $student_progress;
		$content = '';

		if ( ! empty( $attributes['answers'] ) ) {
			$disabled_attr = $disabled ? 'disabled="disabled"' : '';
			$oddeven = 'odd';
			$alt = '';
			$response = self::get_response( $module->ID, get_current_user_id() );

			$content .= '<ul style="list-style:none;">';

			foreach ( $attributes['answers'] as $key => $answer ) {
				$checked = ' ' . checked( 1, ! empty( $response ) && $response == $key, false );

				$format = '<li class="%1$s %2$s"><label for="module-%3$s-%5$s">%4$s</label> <input type="radio" value="%5$s" name="module[%3$s]" id="module-%3$s-%5$s" %6$s /></li>';
				$content .= sprintf( $format, $oddeven, $alt, $module->ID, esc_html__( $answer ), esc_attr( $key ), $disabled_attr . $checked );

				$oddeven = 'odd' === $oddeven ? 'even' : 'odd';
				$alt = empty( $alt ) ? 'alt' : '';
			}

			$content .= '</ul>';
		}

		return $content;
	}

	public static function render_input_select( $module, $attributes = false, $student_progress = false, $disabled = false ) {
		$attributes = false === $attributes ? self::attributes( $module->ID ) : $attributes;
		$student_progress = false === $student_progress ? self::$student_progress : $student_progress;
		$content = '';

		if ( ! empty( $attributes['answers'] ) ) {
			$disabled_attr = $disabled ? 'disabled="disabled"' : '';
			$options = '';
			$response = self::get_response( $module->ID, get_current_user_id() );

			foreach ( $attributes['answers'] as $key => $answer ) {
				$options .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( 1, $response == $key, false ), esc_html( $answer ) );
			}
			$content .= sprintf( '<select class="wide" name="module[%s]" %s>%s</select>', $module->ID, $disabled_attr, $options );
		}

		return $content;
	}

	public static function render_input_text( $module, $attributes = false, $student_progress = false, $disabled = false ) {
		$attributes = false === $attributes ? self::attributes( $module->ID ) : $attributes;
		$student_progress = false === $student_progress ? self::$student_progress : $student_progress;
		$placeholder_text = get_post_meta( $module->ID, 'placeholder_text', true );
		$placeholder_text = ! empty( $placeholder_text ) ? $placeholder_text : '';
		$disabled_attr = $disabled ? 'disabled="disabled"' : '';
		$response = self::get_response( $module->ID, get_current_user_id() );
		$format = '<input type="text" name="module[%s]" placeholder="%s" value="%s" %s />';

		$content = sprintf( $format, $module->ID, esc_attr( $placeholder_text ), esc_attr( $response ), $disabled_attr );

		return $content;
	}

	public static function render_input_textarea( $module, $attributes = false, $student_progress = false, $disabled = false ) {
		$attributes = false === $attributes ? self::attributes( $module->ID ) : $attributes;
		$student_progress = false === $student_progress ? self::$student_progress : $student_progress;
		$placeholder_text = get_post_meta( $module->ID, 'placeholder_text', true );
		$placeholder_text = ! empty( $placeholder_text ) ? $placeholder_text : '';
		$disabled_attr = $disabled ? 'disabled="disabled"' : '';
		$response = self::get_response( $module->ID, get_current_user_id() );
		$format = '<textarea name="module[%s]" placeholder="%s" %s rows="3">%s</textarea>';

		$content = sprintf( $format, $module->ID, esc_attr( $placeholder_text ), $disabled_attr, esc_textarea( $response ) );

		return $content;
	}

	public static function render_input_upload( $module, $attributes = false, $student_progress = false, $disabled = false ) {
		$attributes = false === $attributes ? self::attributes( $module->ID ) : $attributes;
		$student_progress = false === $student_progress ? self::$student_progress : $student_progress;
		$disabled_attr = $disabled ? 'disabled="disabled"' : '';
		$response = self::get_response( $module->ID, get_current_user_id() );

		$format = '<label class="file"><input type="file" name="module[%s]" %s /><span class="button" data-change="%s" data-upload="%s">%s</label>';
		$content = sprintf( $format, $module->ID, $disabled_attr, __( 'Change File', 'cp' ), __( 'Upload File', 'cp' ), __( 'Upload File', 'cp' ) );

		return $content;
	}

	public static function render_input_quiz( $module, $attributes = false, $student_progress = false, $disabled = false ) {
		$attributes = false === $attributes ? self::attributes( $module->ID ) : $attributes;
		$student_progress = false === $student_progress ? self::$student_progress : $student_progress;
		$disabled_attr = $disabled ? 'disabled="disabled"' : '';
		$content = '';

		if ( ! empty( $attributes['questions'] ) ) {
			$response = self::get_response( $module->ID, get_current_user_id() );

			foreach ( $attributes['questions'] as $qi => $question ) {
				$questions = '<ul style="list-style: none;">';

				foreach ( $question['options']['answers'] as $ai => $answer ) {
					$module_name = sprintf( 'module[%s][%s]', $module->ID, $qi );
					$quiz_id = 'quiz-module-' . $module->ID . '-' . $qi . '-' . $ai;
					$type = 'radio';

					if ( 'multiple' == $question['type'] ) {
						$type = 'checkbox';
						$module_name .= '[]';
					}

					$checked = ' ';
					if ( is_array( $response ) && ! empty( $response[ $qi ] ) ) {
						$checked .= checked( 1, ! empty( $response[ $qi ][ $ai ] ), false );
						
					}

					$format = '<li><label for="%1$s">%2$s</label> <input type="%3$s" id="%1$s" name="%4$s" value="%5$s" %6$s/></li>';
					$questions .= sprintf( $format, $quiz_id, esc_html( $answer ), $type, $module_name, $ai, $disabled_attr . $checked );
				}

				$questions .= '</ul>';
				$questions = sprintf('<p class"question">%s</p>%s', esc_html( $question['question'] ), $questions );
				$container_format = '<div class="module-quiz-question question-%s" data-type="%s">%s</div>';
				$content .= sprintf( $container_format, $qi, $question['type'], $questions );
			}
		}

		return $content;
	}

	public static function render_input_form( $module, $attributes = false, $student_progress = false, $disabled = false ) {
		$attributes = false === $attributes ? self::attributes( $module->ID ) : $attributes;
		$student_progress = false === $student_progress ? self::$student_progress : $student_progress;
		$disabled_attr = $disabled ? 'disabled="disabled"' : '';
		$content = '';
		$response = self::get_response( $module->ID, get_current_user_id() );

		foreach ( $attributes['questions'] as $qi => $question ) {
			$field_id = 'form-module-' . $module->ID . '-' . $qi;
			$label = sprintf( '<label for="%s">%s</label>', $field_id, esc_html( $question['question'] ) );

			switch ( $question['type'] ) {
				case 'short':
					$value = ! empty( $response[ $qi ] ) ? $response[ $qi ] : '';
					$field = '<input type="text" name="module[%s][%s]" placeholder="%s" id="%s" value="%s" %s />';
					$field = sprintf( $field, $module->ID, $qi, esc_attr( $question['placeholder'] ), $field_id, esc_attr( $value ), $disabled_attr );
					break;

				case 'long':
					$value = ! empty( $response[ $qi ] ) ? $response[ $qi ] : '';
					$field = '<textarea name="module[%s][%s]" placeholder="%s" id="%s" %s>%s</textarea>';
					$field = sprintf( $field, $module->ID, $qi, esc_attr( $question['placeholder'] ), $field_id, $disabled_attr, esc_textarea( $value ) );
					break;

				case 'selectable':
					$options = '';
					foreach ( $question['options']['answers'] as $ai => $answer ) {
						$selected = selected( 1, isset( $response[ $qi ] ) && $response[ $qi ] == $ai, false );
						$options .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $ai ), $selected, $answer );
					}
					$field = '<select class="wide" name="module[%s][%s]" id="%s" %s>%s</select>';
					$field = sprintf( $field, $module->ID, $qi, $field_id, $disabled_attr, $options );
					break;
			}

			$container_format = '<div class="module-quiz-question question-%s" data-type="%s">%s</div>';
			$content .= sprintf( $container_format, $qi, $question['type'], $label . $field );
		}

		return $content;
	}

	/**
	 * Wrap Content by div, apply filter and shortcodes.
	 *
	 * @access private
	 *
	 * @since 2.0.0
	 *
	 * @param string $content Content to wrap.
	 * @param string $after_content String to paste after content, but before * wrapper.
	 * @return string Wrapped and processed content.
	 */
	private static function _wrap_content( $content, $after_content = '' ) {
		return sprintf(
			'<div class="module-content">%s%s</div>',
			do_shortcode( apply_filters( 'the_content', $content ) ),
			$after_content
		);
	}
}
