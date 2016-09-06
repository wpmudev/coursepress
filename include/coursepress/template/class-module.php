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

	public static function template( $module_id = 0 ) {
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
		$is_module_asnwerable = preg_match( '%input-%', $module_type );
		$disabled = false;
		$element_class = '';

		// Module header
		$content = self::render_module_head( $module, $attributes );
		// The question or text content
		$content .= self::_wrap_content( $module->post_content );

		if ( method_exists( __CLASS__, $method ) ) {
			// Get student progress if it is not retrieve yet
			if ( empty( self::$student_progress ) ) {
				$student_progress = self::$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );
			} else {
				$student_progress = self::$student_progress;
			}

			if ( $is_module_asnwerable ) {
				$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );
				$element_class = ! empty( $responses ) ? 'hide' : '';
				$response_count = ! empty( $responses ) ? count( $responses ) : 0;

				// Check if retry is enable
				if ( ! empty( $attributes['allow_retries'] ) && 0 < $response_count ) {
					$attempts = (int) $attributes['retry_attempts'];
					if ( $attempts >= $reponse_count ) {
						$disabled = true;
					}
				}
			}

			if ( $course_status ){
				$disabled = true;
			}

			// RESUBMIT LOGIC
			$action = false === $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit Answer', 'cp' ) . '</a></div>' : '';

			$disabled_attr = $disabled ? 'disabled="disabled"' : '';
			$module_elements = call_user_func( array( __CLASS__, $method ), $module, $attributes, $student_progress );

			$module_elements = sprintf( '<div class="module-elements %s">%s</div>', $element_class, $module_elements, $disabled );

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
	}

	public static function render_audio( $module, $attributes = false ) {
	}

	public static function render_download( $module, $attributes = false ) {
	}

	public static function render_zipped( $module, $attributes = false ) {
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
			$content .= '<ul style="list-style:none;">';

			foreach ( $attributes['answers'] as $key => $answer ) {
				$format = '<li class="%1$s %2$s"><label for="module-%3$s-%5$s">%4$s</label> <input type="checkbox" value="%5$s" name="module[%3$s]" id="module-%3$s-%5$s" %6$s /></li>';
				$content .= sprintf( $format, $oddeven, $alt, $module->ID, esc_html__( $answer ), esc_attr( $key ), $disabled_attr );

				$oddeven = 'odd' === $oddeven ? 'even' : 'odd';
				$alt = empty( $alt ) ? 'alt' : '';
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
			$content .= '<ul style="list-style:none;">';

			foreach ( $attributes['answers'] as $key => $answer ) {
				$format = '<li class="%1$s %2$s"><label for="module-%3$s-%5$s">%4$s</label> <input type="radio" value="%5$s" name="module[%3$s]" id="module-%3$s-%5$s" %6$s /></li>';
				$content .= sprintf( $format, $oddeven, $alt, $module->ID, esc_html__( $answer ), esc_attr( $key ), $disabled_attr );

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

			foreach ( $attributes['answers'] as $key => $answer ) {
				$options .= sprintf( '<option value="%s">%s</option>', esc_attr( $key ), esc_html( $answer ) );
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
		$format = '<input type="text" name="module[%s]" placeholder="%s" %s />';

		$content = sprintf( $format, $module->ID, esc_attr( $placeholder_text ), $disabled_attr );

		return $content;
	}

	public static function render_input_textarea( $module, $attributes = false, $student_progress = false, $disabled = false ) {
		$attributes = false === $attributes ? self::attributes( $module->ID ) : $attributes;
		$student_progress = false === $student_progress ? self::$student_progress : $student_progress;
		$placeholder_text = get_post_meta( $module->ID, 'placeholder_text', true );
		$placeholder_text = ! empty( $placeholder_text ) ? $placeholder_text : '';
		$disabled_attr = $disabled ? 'disabled="disabled"' : '';
		$format = '<textarea name="module[%s]" placeholder="%s" %s rows="3"></textarea>';

		$content = sprintf( $format, $module->ID, esc_attr( $placeholder_text ), $disabled_attr );

		return $content;
	}

	public static function render_input_upload( $module, $attributes = false, $student_progress = false, $disabled = false ) {
		$attributes = false === $attributes ? self::attributes( $module->ID ) : $attributes;
		$student_progress = false === $student_progress ? self::$student_progress : $student_progress;
		$disabled_attr = $disabled ? 'disabled="disabled"' : '';

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

					$format = '<li><label for="%1$s">%2$s</label> <input type="%3$s" id="%1$s" name="module[%4$s]" value="%5$s" %6$s/></li>';
					$questions .= sprintf( $format, $quiz_id, esc_html( $answer ), $type, $module_name, $ai, $disabled_attr );
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

		foreach ( $attributes['questions'] as $qi => $question ) {
			$field_id = 'form-module-' . $module->ID . '-' . $qi;
			$label = sprintf( '<label for="%s">%s</label>', $field_id, esc_html( $question['question'] ) );

			switch ( $question['type'] ) {
				case 'short':
					$field = '<input type="text" name="module[%s][%s]" placeholder="%s" id="%s" %s />';
					$field = sprintf( $field, $module->ID, $qi, esc_attr( $question['placeholder'] ), $field_id, $disabled_attr );
					break;

				case 'long':
					$field = '<textarea name="form_module[%s][%s]" placeholder="%s" id="%s" %s></textarea>';
					$field = sprintf( $field, $module->ID, $qi, esc_attr( $question['placeholder'] ), $field_id, $disabled_attr );
					break;

				case 'selectable':
					$options = '';

					foreach ( $question['options']['answers'] as $ai => $answer ) {
						$options .= sprintf( '<option value="%s">%s</option>', esc_attr( $ai ), $answer );
					}
					$field = '<select class="wide" name="form_module[%s][%s]" id="%s" %s>%s</select>';
					$field = sprintf( $field, $module->ID, $qi, $field_id, $disabled_attr, $options );
					break;
			}

			$container_format = '<div class="module-quiz-question question-%s" data-type="%s">%s</div>';
			$content .= sprintf( $container_format, $qi, $question['type'], $label . $field );
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

		$grade = CoursePress_Data_Student::get_grade( $student_id, $course_id, $unit_id, $module_id, $response_key, false, $student_progress );
		$grade = $grade['grade'];
		$feedback = CoursePress_Data_Student::get_feedback( $student_id, $course_id, $unit_id, $module_id, $response_key, false, $student_progress );
		$feedback = $feedback['feedback'];

		$content .= '<div class="module-result">';

		/**
		 * grade
		 */
		$content .= '<div class="grade">';
		if ( cp_is_true( $attributes['assessable'] ) ) {
			if ( 'input-upload' == $attributes['module_type'] ) {
				$content .= '<p>' . esc_html__( 'This file has been successfully uploaded. It will be part of your grade in this course.', 'cp' ) . '</p>';
			}
			if ( $grade > - 1 ) {
				$content .= '<p><strong>' . esc_html__( 'Grade:', 'cp' ) . '</strong> ' . $grade . '%</p>';
			} else {
				$content .= '<p><strong>' . esc_html__( 'Ungraded', 'cp' ) . '</strong></p>';
			}
		} elseif ( 'input-upload' == $attributes['module_type'] ) {
			$content .= '<p>' . esc_html__( 'This file has been successfully uploaded. It will not be part of your grade in this course.', 'cp' ) . '</p>';
		}
		$content .= '</div>';

		if ( ( $attributes['minimum_grade'] > $grade || ( ! cp_is_true( $attributes['assessable'] ) ) && $attributes['minimum_grade'] <= $grade ) && ! $disabled ) {
			$content .= '<div class="resubmit"><a>' . esc_html__( 'Resubmit', 'cp' ) . '</a></div>';
		}
		if ( $feedback && ! empty( $feedback ) ) {
			$content .= '<div class="feedback"><strong>' . esc_html__( 'Feedback:', 'cp' ) . '</strong><br/> ' . $feedback . '</div>';
		}

		$content .= '</div>';

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
