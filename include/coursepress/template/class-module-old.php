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
			return ''; // Nothing to process
		}
		$attributes = self::attributes( $module_id );
		$module_type = $attributes['module_type'];
		$method = 'render_' . str_replace( '-', '_', $module_type );
		$module = get_post( $module_id );
		$unit_id = $module->post_parent;
		$course_id = get_post_field( 'post_parent', $unit_id );

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

			$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );
			$element_class = ! empty( $responses ) ? 'hide' : '';
			$response_count = ! empty( $responses ) ? count( $responses ) : 0;

			$disabled = false === $attributes['allow_retries'] && 0 < $response_count;
			$disabled = ! ( ( false === $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );
			//Force disabled to true if the course is closed
			$course_status = CoursePress_Data_Course::get_course_status($course_id) == 'closed';
			if ( $course_status ){
				$disabled = true;
			}
			// RESUBMIT LOGIC
			$action = false === $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit Answer', 'coursepress' ) . '</a></div>' : '';

			$disabled_attr = $disabled ? 'disabled="disabled"' : '';
			$module_elements = call_user_func( array( __CLASS__, $method ), $module, $attributes, $student_progress );

			$module_elements = sprintf( '<div class="module-elements %s">%s</div>', $element_class, $module_elements, $disabled );

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
			$content .= sprintf( '<div class="is-mandatory">%s</div>', __( 'Required', 'coursepress' ) );
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

	// Keep for legacy
	public static function render_text( $module, $attributes = false ) {
		/*
		$content = self::render_module_head( $module, $attributes );

		// Content
		$content .= self::_wrap_content( $module->post_content );

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
		*/
	}

	public static function render_image( $module, $attributes = false ) {
/*
		$content = self::render_module_head( $module, $attributes );

		$content .= '<div class="module-content">' . self::do_caption_media( $attributes ) . '</div>';

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
*/
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
/*
		$content = self::render_module_head( $module, $attributes );

		$content .= '<div class="module-content">' . self::do_caption_media( $attributes ) . '</div>';

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
*/
	}

	public static function render_audio( $module, $attributes = false ) {
/*
		$content = self::render_module_head( $module, $attributes );

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

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
*/
	}

	public static function render_download( $module, $attributes = false ) {
/*
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
			$after_content = sprintf(
				'<div class="file_holder"><a href="%s">%s <span class="file-size">%s</span></a></div>',
				esc_url( $url ),
				esc_html( $link_text ),
				CoursePress_Helper_Utility::filter_content( $filesize )
			);
			$content .= self::_wrap_content( $module->post_content, $after_content );
		}

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
*/
	}

	public static function render_zipped( $module, $attributes = false ) {
/*
		$content = self::render_module_head( $module, $attributes );

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

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
*/
	}

	public static function render_section( $module, $attributes = false ) {
		return '<hr />';

/*
		$content = self::render_module_head( $module, $attributes );
		$content .= '<hr />';
		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
*/
	}

	public static function render_discussion( $module, $attributes = false ) {
/*
		$content = self::render_module_head( $module, $attributes );

		// Content
		$content .= self::_wrap_content( $module->post_content );

		$content .= '<div id="comments" class="comments-area">';
		$content .= '<div class="comments-list-container">' . CoursePress_Template_Discussion::get_comments( $module->ID ) . '</div>';
		//      $content .= '<div class="comments-nav-container">' . CoursePress_Template_Discussion::get_comments_nav( $module->ID, $module->post_parent, 5 ) . '</div>';

		$content .= '</div>';

		return str_replace( array( "\n", "\r" ), '', $content );
*/
	}

	public static function testing( $comment, $args, $depth ) {

		$GLOBALS['comment'] = $comment;

		?>
		<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
			<article id="comment-<?php comment_ID(); ?>" class="comment">

				<div class="comment-content"><?php comment_text(); ?></div>

				<div class="reply">
					<?php
					comment_reply_link(
						array_merge(
							$args,
							array(
								'depth' => $depth,
								'max_depth' => $args['max_depth'],
							)
						),
						$comment
					);
					?>
				</div>
			</article>
		</li>
		<?php

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
				$format = '<li class="%1$s %2$s"><label for="module-%3$s-%5$s">%4$s</label> <input type="checkbox" value="%5$s" name="module-%3$s" id="module-%3$s-%5$s" %6$s /></li>';
				$content .= sprintf( $format, $oddeven, $alt, $module->ID, esc_html__( $answer ), esc_attr( $key ), $disabled_attr );

				$oddeven = 'odd' === $oddeven ? 'even' : 'odd';
				$alt = empty( $alt ) ? 'alt' : '';
			}

			$content .= '</ul>';
		}

		return $content;
	}

	public static function render_input_checkboxOLD( $module, $attributes = false, $student_progress = false ) {
		$content = ''; //// self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content
		////$content .= self::_wrap_content( $module->post_content );

		if ( ! empty( $attributes['answers'] ) ) {
			$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );

			$element_class = ! empty( $responses ) ? 'hide' : '';
			$response_count = ! empty( $responses ) ? count( $responses ) : 0;
			// $attributes['retry_attempts'] = 3; // DEBUG
			$disabled = ! $attributes['allow_retries'] && $response_count > 0;
			$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );
			//Force disabled to true if the course is closed
			$course_status = CoursePress_Data_Course::get_course_status($course_id) == 'closed';
			if ( $course_status ){
				$disabled = true;
			}		
			// RESUBMIT LOGIC
			$action = ! $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit Answer', 'coursepress' ) . '</a></div>' : '';

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
			foreach ( $attributes['answers'] as $key => $answer ) {
				$content .= '<li class="' . $oddeven . ' ' . $alt . '"><label>' .
							'<input type="checkbox" value="' . esc_attr( $key ) . '" name="module-' . $module->ID . '" ' . $disabled_attr . ' /> ' . esc_html( $answer ) .
							'</label></li>';
				$oddeven = 'odd' === $oddeven ? 'even' : 'odd';
				$alt = empty( $alt ) ? 'alt' : '';
			}

			$content .= '</ul>';

			$content .= $action;

			$content .= '</div>'; // module-elements

			if ( ! empty( $responses ) ) {

				$last_response = (array) $responses[ $response_count - 1 ];
				$response_key = array_keys( $responses );
				$response_key = array_pop( $response_key );

				$content .= '<div class="module-response">';

				$content .= '<ul>';
				foreach ( $attributes['answers'] as $key => $answer ) {
					$the_answer = in_array( $key, $attributes['answers_selected'] );
					$student_answer = in_array( $key, $last_response );

					$class = '';
					if ( $student_answer && $the_answer ) {
						$class = 'chosen-answer correct';
					} elseif ( $student_answer && ! $the_answer ) {
						$class = 'chosen-answer incorrect';
					} elseif ( ! $student_answer && $the_answer ) {
						// $class = 'incorrect';
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
					'disabled' => $disabled,
				);
				$content .= self::render_module_result( $module, $attributes, $args );

			}
		}
		$content .= $course_status ? apply_filters('coursepress_course_readonly_message', '<div class="module-warning"><p>' . esc_html__( 'This course is completed, you can not submit answers anymore.', 'coursepress' ) . '</p></div>') : '';

///		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );

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
				$content .= '<p>' . esc_html__( 'This file has been successfully uploaded. It will be part of your grade in this course.', 'coursepress' ) . '</p>';
			}
			if ( $grade > - 1 ) {
				$content .= '<p><strong>' . esc_html__( 'Grade:', 'coursepress' ) . '</strong> ' . $grade . '%</p>';
			} else {
				$content .= '<p><strong>' . esc_html__( 'Ungraded', 'coursepress' ) . '</strong></p>';
			}
		} elseif ( 'input-upload' == $attributes['module_type'] ) {
			$content .= '<p>' . esc_html__( 'This file has been successfully uploaded. It will not be part of your grade in this course.', 'coursepress' ) . '</p>';
		}
		$content .= '</div>';

		if ( ( $attributes['minimum_grade'] > $grade || ( ! cp_is_true( $attributes['assessable'] ) ) && $attributes['minimum_grade'] <= $grade ) && ! $disabled ) {
			$content .= '<div class="resubmit"><a>' . esc_html__( 'Resubmit', 'coursepress' ) . '</a></div>';
		}
		if ( $feedback && ! empty( $feedback ) ) {
			$content .= '<div class="feedback"><strong>' . esc_html__( 'Feedback:', 'coursepress' ) . '</strong><br/> ' . $feedback . '</div>';
		}

		$content .= '</div>';

		return $content;
	}

	public static function render_input_radio( $module, $attributes = false, $student_progress = false, $disabled = false ) {
		
	}

	public static function render_input_radioOLD( $module, $attributes = false ) {
		$content = ''; //self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content
		//$content .= self::_wrap_content( $module->post_content );

		if ( ! empty( $attributes['answers'] ) ) {
			$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );

			$element_class = ! empty( $responses ) ? 'hide' : '';
			$response_count = ! empty( $responses ) ? count( $responses ) : 0;
			// $attributes['retry_attempts'] = 3; // DEBUG
			$disabled = ! $attributes['allow_retries'] && $response_count > 0;
			$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );
			//Force disabled to true if the course is closed
			$course_status = CoursePress_Data_Course::get_course_status($course_id) == 'closed';
			if ( $course_status ){
				$disabled = true;
			}		
			// RESUBMIT LOGIC
			$action = ! $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit Answer', 'coursepress' ) . '</a></div>' : '';

			$disabled_attr = $disabled ? 'disabled="disabled"' : '';
			$content .= '<div class="module-elements ' . $element_class . '">';

			$content .= '
						<input type="hidden" name="course_id" value="' . $course_id . '" />
						<input type="hidden" name="unit_id" value="' . $unit_id . '" />
						<input type="hidden" name="module_id" value="' . $module_id . '" />
						<input type="hidden" name="student_id" value="' . get_current_user_id() . '" />';

			$content .= '<ul style="list-style: none;">';

			// RESUBMIT LOGIC
			$action = ! $disabled ? '<a class="module-submit-action">' . esc_html__( 'Submit Answer', 'coursepress' ) . '</a>' : '';

			$oddeven = 'odd';
			$alt = '';
			foreach ( $attributes['answers'] as $key => $answer ) {
				$content .= '<li class="' . $oddeven . ' ' . $alt . '"><label>' .
							'<input type="radio" value="' . esc_attr( $key ) . '" name="module-' . $module->ID . '" ' . $disabled_attr . ' /> ' . esc_html( $answer ) .
							'</label></li>';

				$oddeven = 'odd' === $oddeven ? 'even' : 'odd';
				$alt = empty( $alt ) ? 'alt' : '';
			}

			$content .= '</ul>';

			$content .= $action;

			$content .= '</div>'; // module-elements

			if ( ! empty( $responses ) ) {

				$last_response = $responses[ $response_count - 1 ];
				$response_key = array_keys( $responses );
				$response_key = array_pop( $response_key );

				$content .= '<div class="module-response">';

				$content .= '<ul>';
				foreach ( $attributes['answers'] as $key => $answer ) {
					$the_answer = $attributes['answers_selected'] == $key;
					$student_answer = $last_response == $key;

					$class = '';
					if ( $student_answer && $the_answer ) {
						$class = 'chosen-answer correct';
					} elseif ( $student_answer && ! $the_answer ) {
						$class = 'chosen-answer incorrect';
					} elseif ( ! $student_answer && $the_answer ) {
						// $class = 'incorrect';
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
					'disabled' => $disabled,
				);
				$content .= self::render_module_result( $module, $attributes, $args );

			}
		}

		$content .= $course_status ? apply_filters('coursepress_course_readonly_message', '<div class="module-warning"><p>' . esc_html__( 'This course is completed, you can not submit answers anymore.', 'coursepress' ) . '</p></div>') : '';
		//$content .= '</div>'; // module_footer.
		return str_replace( array( "\n", "\r" ), '', $content );
	}

	public static function render_input_select( $module, $attributes = false ) {
		$content = ''; ///self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;

		$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content.
		////$content .= self::_wrap_content( $module->post_content );

		if ( ! empty( $attributes['answers'] ) ) {

			$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );

			$element_class = ! empty( $responses ) ? 'hide' : '';
			$response_count = ! empty( $responses ) ? count( $responses ) : 0;

			$disabled = ! $attributes['allow_retries'] && $response_count > 0;
			$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

			$course_status = CoursePress_Data_Course::get_course_status($course_id) == 'closed';
			if ( $course_status ){
				$disabled = true;
			}		

			// RESUBMIT LOGIC.
			$action = ! $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit Answer', 'coursepress' ) . '</a></div>' : '';

			$disabled_attr = $disabled ? 'disabled="disabled"' : '';
			$content .= '<div class="module-elements ' . $element_class . '">';

			$content .= '
				<input type="hidden" name="course_id" value="' . $course_id . '" />
				<input type="hidden" name="unit_id" value="' . $unit_id . '" />
				<input type="hidden" name="module_id" value="' . $module_id . '" />
				<input type="hidden" name="student_id" value="' . get_current_user_id() . '" />';

			$content .= '<select class="wide" name="module-' . $module->ID . '" ' . $disabled_attr . '>';

			foreach ( $attributes['answers'] as $key => $answer ) {
				$content .= '<option value="' . $key . '">' .
							esc_html( $answer ) .
							'</option>';
			}

			$content .= '</select>';
			$content .= $action;
			$content .= '</div>';

			if ( ! empty( $responses ) ) {

				$last_response = $responses[ $response_count - 1 ];
				$response_key = array_keys( $responses );
				$response_key = array_pop( $response_key );

				$content .= '<div class="module-response">';

				$content .= '<ul>';
				foreach ( $attributes['answers'] as $key => $answer ) {
					$the_answer = $attributes['answers_selected'] == $key;
					$student_answer = $last_response == $key;

					$class = '';
					if ( $student_answer && $the_answer ) {
						$class = 'chosen-answer correct';
					} elseif ( $student_answer && ! $the_answer ) {
						$class = 'chosen-answer incorrect';
					} elseif ( ! $student_answer && $the_answer ) {
						// $class = 'incorrect';
					}

					if ( $student_answer ) {
						$content .= '<li class="' . $class . '">' . $answer . '</li>';
					}
				}

				$content .= '</ul>';
				// $meh = '<p><span class="label">' . esc_html__( 'Response: ', 'coursepress' ) . '</span>
				// ' . $attributes['answers'][ (int) $last_response ] . '
				// </p>';
				$content .= '</div>';

				// Render Response and Feedback
				$args = array(
					'course_id' => $course_id,
					'unit_id' => $unit_id,
					'module_id' => $module_id,
					'student_id' => get_current_user_id(),
					'student_data' => $student_progress,
					'response_key' => $response_key,
					'disabled' => $disabled,
				);
				$content .= self::render_module_result( $module, $attributes, $args );

			}
		}
		$content .= $course_status ? apply_filters('coursepress_course_readonly_message', '<div class="module-warning"><p>' . esc_html__( 'This course is completed, you can not submit answers anymore.', 'coursepress' ) . '</p></div>') : '';
	//	$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
	}

	public static function render_input_text( $module, $attributes = false ) {
		$content = ''; ///////self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content
		///$content .= self::_wrap_content( $module->post_content );

		$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );

		$element_class = ! empty( $responses ) ? 'hide' : '';
		$response_count = ! empty( $responses ) ? count( $responses ) : 0;
		// $attributes['retry_attempts'] = 3; // DEBUG
		$disabled = ! $attributes['allow_retries'] && $response_count > 0;
		$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

		$course_status = CoursePress_Data_Course::get_course_status($course_id) == 'closed';
		if ( $course_status ){
			$disabled = true;
		}		

		// RESUBMIT LOGIC
		$action = ! $disabled ? '<a class="module-submit-action">' . esc_html__( 'Submit Answer', 'coursepress' ) . '</a>' : '';

		$placeholder_text = get_post_meta( $module_id, 'placeholder_text', true );
		$placeholder_text = ! empty( $placeholder_text ) ? $placeholder_text : '';
		$disabled_attr = $disabled ? 'disabled="disabled"' : '';
		$content .= '<div class="module-elements ' . $element_class . '">
						<input type="hidden" name="course_id" value="' . $course_id . '" />
						<input type="hidden" name="unit_id" value="' . $unit_id . '" />
						<input type="hidden" name="module_id" value="' . $module_id . '" />
						<input type="hidden" name="student_id" value="' . get_current_user_id() . '" />
						<input type="text" name="module-' . $module->ID . '" ' . $disabled_attr . ' placeholder="' . esc_attr( $placeholder_text ) . '" />
						' . $action . '
					</div>';

		if ( ! empty( $responses ) ) {

			$last_response = $responses[ $response_count - 1 ];
			$response_key = array_keys( $responses );
			$response_key = array_pop( $response_key );

			$content .= '<div class="module-response">
				<p><span class="label">' . esc_html__( 'Response: ', 'coursepress' ) . '</span>
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
				'disabled' => $disabled,
			);
			$content .= self::render_module_result( $module, $attributes, $args );

		}
		$content .= $course_status ? apply_filters('coursepress_course_readonly_message', '<div class="module-warning"><p>' . esc_html__( 'This course is completed, you can not submit answers anymore.', 'coursepress' ) . '</p></div>') : '';
//		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
	}

	public static function render_input_textarea( $module, $attributes = false ) {
		$content = ''; //////self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content
		////$content .= self::_wrap_content( $module->post_content );

		$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );

		$element_class = ! empty( $responses ) ? 'hide' : '';
		$response_count = ! empty( $responses ) ? count( $responses ) : 0;
		// $attributes['retry_attempts'] = 3; // DEBUG
		$disabled = ! $attributes['allow_retries'] && $response_count > 0;
		$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

		$course_status = CoursePress_Data_Course::get_course_status($course_id) == 'closed';

		if ( $course_status ){
			$disabled = true;
		}

		// RESUBMIT LOGIC
		$action = ! $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit Answer', 'coursepress' ) . '</a></div>' : '';

		$placeholder_text = get_post_meta( $module_id, 'placeholder_text', true );
		$placeholder_text = ! empty( $placeholder_text ) ? $placeholder_text : '';
		$disabled_attr = $disabled ? 'disabled="disabled"' : '';
		$content .= '<div class="module-elements ' . $element_class . '">
						<input type="hidden" name="course_id" value="' . $course_id . '" />
						<input type="hidden" name="unit_id" value="' . $unit_id . '" />
						<input type="hidden" name="module_id" value="' . $module_id . '" />
						<input type="hidden" name="student_id" value="' . get_current_user_id() . '" />
						<textarea name="module-' . $module->ID . '" ' . $disabled_attr . ' placeholder="' . esc_attr( $placeholder_text ) . '"></textarea>
						' . $action . '
					</div>';

		if ( ! empty( $responses ) ) {

			$last_response = $responses[ $response_count - 1 ];
			$response_key = array_keys( $responses );
			$response_key = array_pop( $response_key );

			$content .= '<div class="module-response">
				<p><span class="label">' . esc_html__( 'Response: ', 'coursepress' ) . '</span>
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
				'disabled' => $disabled,
			);
			$content .= self::render_module_result( $module, $attributes, $args );

		}
		$content .= $course_status ? apply_filters('coursepress_course_readonly_message', '<div class="module-warning"><p>' . esc_html__( 'This course is completed, you can not submit answers anymore.', 'coursepress' ) . '</p></div>') : '';
//		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
	}

	public static function render_input_upload( $module, $attributes = false ) {
		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );

		$content = ''; ////self::render_module_head( $module, $attributes );

		// Content
		///$content .= self::_wrap_content( $module->post_content );

		$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );

		$element_class = ! empty( $responses ) ? 'hide' : '';
		$response_count = ! empty( $responses ) ? count( $responses ) : 0;
		// $attributes['retry_attempts'] = 3; // DEBUG
		$disabled = ! $attributes['allow_retries'] && $response_count > 0;
		$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

		$course_status = CoursePress_Data_Course::get_course_status($course_id) == 'closed';
		if ( $course_status ){
			$disabled = true;
		}		

		// RESUBMIT LOGIC
		$action = ! $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit File', 'coursepress' ) . '</a></div>' : '';

		$disabled_attr = $disabled ? 'disabled="disabled"' : '';
		$content .= '<div class="module-elements ' . $element_class . '">
						<form method="POST" enctype="multipart/form-data">
						<input type="hidden" name="course_action" value="upload-file" />
						<input type="hidden" name="course_id" value="' . $course_id . '" />
						<input type="hidden" name="unit_id" value="' . $unit_id . '" />
						<input type="hidden" name="module_id" value="' . $module_id . '" />
						<input type="hidden" name="student_id" value="' . get_current_user_id() . '" />
						<label class="file">
							<input type="file" name="module-' . $module_id . '" ' . $disabled_attr . ' />
							<span class="button" data-change="'.esc_attr__('Change File', 'coursepress' ).'" data-upload="'.esc_attr__('Upload File', 'coursepress' ).'">'.esc_attr__('Upload File', 'coursepress' ).'</span>
						</label>
						' . $action . ' <span class="upload-progress"></span>
						</form>
					</div>';

		if ( ! empty( $responses ) ) {

			$last_response = $responses[ $response_count - 1 ];
			$response_key = array_keys( $responses );
			$response_key = array_pop( $response_key );

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
					<p class="file_holder"><span class="label">' . esc_html__( 'Uploaded file: ', 'coursepress' ) . '</span>
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
				'disabled' => $disabled,
			);
			$content .= self::render_module_result( $module, $attributes, $args );

		}
		$content .= $course_status ? apply_filters('coursepress_course_readonly_message', '<div class="module-warning"><p>' . esc_html__( 'This course is completed, you can not submit answers anymore.', 'coursepress' ) . '</p></div>') : '';
	////	$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );

	}

	public static function render_input_quiz( $module, $attributes = false ) {
		$content = ''; ////self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );
		$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );
		$response_count = count( $responses );
		$use_timer = cp_is_true( $attributes['use_timer'] );

		$quiz_result = CoursePress_Data_Module::get_quiz_results( get_current_user_id(), $course_id, $unit_id, $module_id, false, $student_progress );

		// Is the quiz already passed?
		$already_passed = ! empty( $quiz_result ) && ! empty( $quiz_result['passed'] );
		$quiz_result_content = ! empty( $quiz_result ) ? do_shortcode( '[coursepress_quiz_result course_id="' . $course_id . '" unit_id="' . $unit_id . '" module_id="' . $module_id . '" student_id="' . get_current_user_id() . '"]' ) : '';

		$disabled = ! $attributes['allow_retries'] && $response_count > 0;
		$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

		$course_status = CoursePress_Data_Course::get_course_status($course_id) == 'closed';
		if ( $course_status ){
			$disabled = true;
		}
		// Content
		///$content .= self::_wrap_content( $module->post_content );

		if ( ! empty( $attributes['questions'] ) && ! $already_passed && ! $disabled ) {

			// Has the user already answered?
			$element_class = ! empty( $responses ) && $disabled ? 'hide' : '';

			$unlimited = empty( $attributes['retry_attempts'] );
			$remaining = ! $unlimited ? (int) $attributes['retry_attempts'] - ( $response_count - 1 ) : 0;
			$remaining_message = ! $unlimited ? sprintf( __( 'You have %d attempts left.', 'coursepress' ), $remaining ) : '';

			if ( ! empty( $responses ) && ! $already_passed ) {
				$remaining_message = sprintf( esc_html__( 'Your last attempt was unsuccessful. Try again. %s', 'coursepress' ), $remaining_message );

				if ( ! $unlimited && 1 > $remaining ) {
					$remaining_message = esc_html__( 'Your last attempt was unsuccessful. You can not try anymore.', 'coursepress' );
				}
				$content .= sprintf( '<div class="not-passed-message">%s</div>', $remaining_message );
			}

			// RESUBMIT LOGIC
			$action = ! $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit Answer', 'coursepress' ) . '</a></div>' : '';

			$disabled_attr = $disabled ? 'disabled="disabled"' : '';
			$content .= '<div class="module-elements ' . $element_class . '">';

			$seconds = CoursePress_Helper_Utility::duration_to_seconds( $attributes['duration'] );
			if ( $use_timer && ! empty( $seconds ) ) {
				$data_ref = 'ref_' . strrev( CoursePress_Helper_Utility::hashcode( '' . $seconds ) );
				$content .= '<div class="quiz_timer" data-ref="' . $data_ref . '" data-time="' . $seconds . '"></div>';
			}

			$content .= '
						<input type="hidden" name="course_id" value="' . $course_id . '" />
						<input type="hidden" name="unit_id" value="' . $unit_id . '" />
						<input type="hidden" name="module_id" value="' . $module_id . '" />
						<input type="hidden" name="student_id" value="' . get_current_user_id() . '" />';

			$content .= '
						<div class="module-quiz-questions">
			';

			foreach ( $attributes['questions'] as $qi => $question ) {

				$content .= '<div class="module-quiz-question question-' . $qi . '" data-type="' . $question['type'] . '">';
				$content .= '<p class="question">' . esc_html( $question['question'] ) . '</p>';
				foreach ( $question['options']['answers'] as $ai => $answer ) {
					$type = 'checkbox';
					switch ( $question['type'] ) {
						case 'multiple':
							$type = 'checkbox';
							break;
						case 'single':
							$type = 'radio';
							break;
					}
					$answer = esc_html( $answer );
					$content .= '<label><input type="' . $type . '" name="question-' . $qi . '"><span>' . $answer . '</span></label><br />';

				}

				$content .= '</div>';
			}

			// RESUBMIT LOGIC
			$action = '<a class="module-submit-action">' . esc_html__( 'Submit Quiz Answers', 'coursepress' ) . '</a>';
			$content .= $action;

			$content .= '</div>'; // module-quiz-questions
			$content .= '</div>'; // module-elements
		} else {
			if ( ! empty( $quiz_result_content ) ) {
				$content .= $quiz_result_content;
			}
		}
		$content .= $course_status ? apply_filters('coursepress_course_readonly_message', '<div class="module-warning"><p>' . esc_html__( 'This course is completed, you can not submit answers anymore.', 'coursepress' ) . '</p></div>') : '';
//		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
	}

	public static function render_input_form( $module, $attributes = false ) {
		$content = ''; //////self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );
		$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );
		$response_count = count( $responses );
		$use_timer = cp_is_true( $attributes['use_timer'] );

		$disabled = ! $attributes['allow_retries'] && $response_count > 0;
		$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

		// Content
		///$content .= self::_wrap_content( $module->post_content );

		// Has the user already answered?
		$element_class = ! empty( $responses ) || $disabled ? 'hide' : '';

		$unlimited = empty( $attributes['retry_attempts'] );
		$remaining = ! $unlimited ? (int) $attributes['retry_attempts'] - ( $response_count - 1 ) : 0;
		$remaining_message = ! $unlimited ? sprintf( __( 'You have %d attempts left.', 'coursepress' ), $remaining ) : '';

		$content .= ! empty($remaining_message) ? sprintf( '<div class="not-passed-message">%s</div>', $remaining_message ) : '';
		

		// RESUBMIT LOGIC
		$action = ! $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit Answer', 'coursepress' ) . '</a></div>' : '';

		$disabled_attr = $disabled ? 'disabled="disabled"' : '';
		$content .= '<div class="module-elements ' . $element_class . '">';

		$seconds = CoursePress_Helper_Utility::duration_to_seconds( $attributes['duration'] );
		if ( $use_timer && ! empty( $seconds ) ) {
			$data_ref = 'ref_' . strrev( CoursePress_Helper_Utility::hashcode( '' . $seconds ) );
			$content .= '<div class="form_timer" data-ref="' . $data_ref . '" data-time="' . $seconds . '"></div>';
		}

		$content .= '
					<input type="hidden" name="course_id" value="' . $course_id . '" />
					<input type="hidden" name="unit_id" value="' . $unit_id . '" />
					<input type="hidden" name="module_id" value="' . $module_id . '" />
					<input type="hidden" name="student_id" value="' . get_current_user_id() . '" />';

		$content .= '
					<div class="module-form-questions">
		';

		foreach ( $attributes['questions'] as $qi => $question ) {

			$content .= '<div class="module-form-question question-' . $qi . '" data-type="' . $question['type'] . '">';
			$content .= '<p class="question">' . esc_html( $question['question'] ) . '</p>';

			switch ( $question['type'] ) {
				case 'short':
						$content .= '<input type="text"' . $disabled_attr . ' placeholder="' . esc_attr( $question['placeholder'] ) . '" /><br />';
						break;
				case 'long':
						$content .= '<textarea  ' . $disabled_attr . ' placeholder="' . esc_attr( $question['placeholder'] ) . '"></textarea><br />';
						break;
				case 'selectable':
						$content .= '<label><select name="question-' . $qi . '">';
						foreach ( $question['options']['answers'] as $ai => $answer ) {
							$content .= '<option value="' . $answer . '" >'. $answer . '</option>';
						}
						$content .= '</select></label><br />';
						break;
			}

			$content .= '</div>';
		}

		// RESUBMIT LOGIC
		$action = '<a class="module-submit-action">' . esc_html__( 'Submit', 'coursepress' ) . '</a>';
		$content .= $action;

		$content .= '</div>'; // module-form-questions
		$content .= '</div>'; // module-elements

		if ( ! empty( $responses ) ) {


			$last_response = $responses[ $response_count - 1 ];

			$content .= '<div class="module-response">';
			foreach ( $attributes['questions'] as $qi => $question ) {
				$content .= '<p class="question">' . esc_html( $question['question'] ) . '</p>';
				$content .= '<p><span class="label">Response: </span>'. $last_response[$qi][0] .'</p><br />';
			}
			$content .= '</div>';

			// Render Response and Feedback
			$args = array(
				'course_id' => $course_id,
				'unit_id' => $unit_id,
				'module_id' => $module_id,
				'student_id' => get_current_user_id(),
				'student_data' => $student_progress,
				'response_key' => $response_key,
				'disabled' => $disabled,
			);
			$content .= self::render_module_result( $module, $attributes, $args );

		}

/////		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
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
