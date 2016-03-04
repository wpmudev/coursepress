<?php

class CoursePress_Template_Module {

	private static $args = array();

	public static function render_text( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
	}

	private static function render_module_head( $module, $attributes = false ) {
		$content = '<div class="module-container module ' . $attributes['module_type'] . ' module-' . $module->ID . ' ' . $attributes['mode'] . '" data-type="' . $attributes['module_type'] . '" data-module="' . $module->ID . '">';

		$show_title = isset( $attributes['show_title'] ) ? $attributes['show_title'] : false;
		$mandatory = isset( $attributes['mandatory'] ) ? $attributes['mandatory'] : false;

		if ( $show_title ) {
			$content .= '<h4 class="module-title">' . $module->post_title . '</h4>';
		}

		if ( $mandatory && 'input-quiz' != $attributes['module_type'] ) {
			$content .= '<div class="is-mandatory">' . esc_html__( 'Mandatory', 'CP_TD' ) . '</div>';
		}

		return $content;
	}

	public static function render_image( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		$content .= '<div class="module-content">' . self::do_caption_media( $attributes ) . '</div>';

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
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
		$content = self::render_module_head( $module, $attributes );

		$content .= '<div class="module-content">' . self::do_caption_media( $attributes ) . '</div>';

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
	}

	public static function render_audio( $module, $attributes = false ) {
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

			$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '
					<div class="file_holder">
						<a href="' . esc_url( $url ) . '">' . esc_html( $link_text ) . ' ' . CoursePress_Helper_Utility::filter_content( $filesize ) . '</a>
					</div>
				</div>
			';
		}

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
	}

	public static function render_zipped( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		if ( isset( $attributes['zip_url'] ) && ! empty( $attributes['primary_file'] ) ) {

			$url = $attributes['zip_url'];

			$url = CoursePress_Helper_Utility::encode( $url );
			$url = trailingslashit( home_url() ) . '?oacpf=' . $url . '&module=' . $module->ID . '&file=' . $attributes['primary_file'];

			$link_text = isset( $attributes['link_text'] ) ? $attributes['link_text'] : $module->post_title;
			$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '
					<div class="zip_holder">
						<a href="' . esc_url( $url ) . '">' . esc_html( $link_text ) . '</a>
					</div>
				</div>
			';
		}

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
	}

	public static function render_section( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );
		$content .= '<hr />';
		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
	}

	public static function render_discussion( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		$content .= '<div id="comments" class="comments-area">';

		if ( get_comments_number( $module->ID ) > 0 ) {

			if ( 1 == get_comments_number( $module->ID ) ) {
				/* translators: %s: post title */
				$content .= sprintf( __( 'One response to %s', 'CP_TD' ), '&#8220;' . get_the_title( $module->ID ) . '&#8221;' );
			} else {
				/* translators: 1: number of comments, 2: post title */
				$content .= sprintf( _n( '%1$s response to %2$s', '%1$s responses to %2$s', get_comments_number( $module->ID ), 'CP_TD' ),
				number_format_i18n( get_comments_number( $module->ID ) ), '&#8220;' . get_the_title( $module->ID ) . '&#8221;' );
			}
		}

		$comments = get_comments( array(
			'post_id' => $module->ID,
			'status' => 'all',// Change this to the type of comments to be displayed
		) );

		// Display the list of comments
		$content .= '<ul class="comments-list">';
		$content .= wp_list_comments( array(
			'per_page' => 10, // Allow comment pagination
			'reverse_top_level' => false, // Show the latest comments at the top of the list
			'echo' => false,
			'style' => 'ul',
			'callback' => function_exists( 'wpmudev_list_comments' ) ? 'wpmudev_list_comments' : false,
		), $comments );
		$content .= '</ul>';

		// add_filter( 'comment_post_redirect', array( __CLASS__, 'test2' ), 10, 2 );
		ob_start();
		comment_form( array(), $module->ID );
		$content .= ob_get_clean();

		$content .= '</div>'; // comments-area
		$content .= '</div>'; // module_footer

		return str_replace( array( "\n", "\r" ), '', $content );
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


	public static function render_input_checkbox( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		if ( ! empty( $attributes['answers'] ) ) {
			$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );

			$element_class = ! empty( $responses ) ? 'hide' : '';
			$response_count = ! empty( $responses ) ? count( $responses ) : 0;
			// $attributes['retry_attempts'] = 3; // DEBUG
			$disabled = ! $attributes['allow_retries'] && $response_count > 0;
			$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

			// RESUBMIT LOGIC
			$action = ! $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit Answer', 'CP_TD' ) . '</a></div>' : '';

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
				$content .= '<li class="' . $oddeven . ' ' . $alt . '">' .
							'<input type="checkbox" value="' . esc_attr( $key ) . '" name="module-' . $module->ID . '" ' . $disabled_attr . ' /> ' . esc_html( $answer ) .
							'</li>';
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

		$content .= '</div>'; // module_footer
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

		if ( cp_is_true( $attributes['assessable'] ) ) {
			if ( $grade > - 1 ) {
				$content .= '<div class="grade"><strong>' . esc_html__( 'Grade:', 'CP_TD' ) . '</strong> ' . $grade . '%</div>';
			} else {
				$content .= '<div class="grade"><strong>' . esc_html__( 'Ungraded', 'CP_TD' ) . '</strong></div>';
			}
		} else {
			$content .= '<div class="grade">' . esc_html__( 'Not assessable', 'CP_TD' ) . '</div>';
		}
		if ( ( $attributes['minimum_grade'] > $grade || ( ! cp_is_true( $attributes['assessable'] ) ) && $attributes['minimum_grade'] <= $grade ) && ! $disabled ) {
			$content .= '<div class="resubmit"><a>' . esc_html__( 'Resubmit', 'CP_TD' ) . '</a></div>';
		}
		if ( $feedback && ! empty( $feedback ) ) {
			$content .= '<div class="feedback"><strong>' . esc_html__( 'Feedback:', 'CP_TD' ) . '</strong><br/> ' . $feedback . '</div>';
		}

		$content .= '</div>';

		return $content;
	}

	public static function render_input_radio( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		if ( ! empty( $attributes['answers'] ) ) {
			$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );

			$element_class = ! empty( $responses ) ? 'hide' : '';
			$response_count = ! empty( $responses ) ? count( $responses ) : 0;
			// $attributes['retry_attempts'] = 3; // DEBUG
			$disabled = ! $attributes['allow_retries'] && $response_count > 0;
			$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

			// RESUBMIT LOGIC
			$action = ! $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit Answer', 'CP_TD' ) . '</a></div>' : '';

			$disabled_attr = $disabled ? 'disabled="disabled"' : '';
			$content .= '<div class="module-elements ' . $element_class . '">';

			$content .= '
						<input type="hidden" name="course_id" value="' . $course_id . '" />
						<input type="hidden" name="unit_id" value="' . $unit_id . '" />
						<input type="hidden" name="module_id" value="' . $module_id . '" />
						<input type="hidden" name="student_id" value="' . get_current_user_id() . '" />';

			$content .= '<ul style="list-style: none;">';

			// RESUBMIT LOGIC
			$action = '<a class="module-submit-action">' . esc_html__( 'Submit Answer', 'CP_TD' ) . '</a>';

			$oddeven = 'odd';
			$alt = '';
			foreach ( $attributes['answers'] as $key => $answer ) {
				$content .= '<li class="' . $oddeven . ' ' . $alt . '">' .
							'<input type="radio" value="' . esc_attr( $key ) . '" name="module-' . $module->ID . '" ' . $disabled_attr . ' /> ' . esc_html( $answer ) .
							'</li>';

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

		$content .= '</div>'; // module_footer.
		return str_replace( array( "\n", "\r" ), '', $content );
	}

	public static function render_input_select( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;

		$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content.
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		if ( ! empty( $attributes['answers'] ) ) {

			$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );

			$element_class = ! empty( $responses ) ? 'hide' : '';
			$response_count = ! empty( $responses ) ? count( $responses ) : 0;

			$disabled = ! $attributes['allow_retries'] && $response_count > 0;
			$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

			// RESUBMIT LOGIC.
			$action = ! $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit Answer', 'CP_TD' ) . '</a></div>' : '';

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
				// $meh = '<p><span class="label">' . esc_html__( 'Response: ', 'CP_TD' ) . '</span>
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

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
	}

	public static function render_input_text( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );

		$element_class = ! empty( $responses ) ? 'hide' : '';
		$response_count = ! empty( $responses ) ? count( $responses ) : 0;
		// $attributes['retry_attempts'] = 3; // DEBUG
		$disabled = ! $attributes['allow_retries'] && $response_count > 0;
		$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

		// RESUBMIT LOGIC
		$action = ! $disabled ? '<a class="module-submit-action">' . esc_html__( 'Submit Answer', 'CP_TD' ) . '</a>' : '';

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
				<p><span class="label">' . esc_html__( 'Response: ', 'CP_TD' ) . '</span>
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

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
	}

	public static function render_input_textarea( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );

		$element_class = ! empty( $responses ) ? 'hide' : '';
		$response_count = ! empty( $responses ) ? count( $responses ) : 0;
		// $attributes['retry_attempts'] = 3; // DEBUG
		$disabled = ! $attributes['allow_retries'] && $response_count > 0;
		$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

		// RESUBMIT LOGIC
		$action = ! $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit Answer', 'CP_TD' ) . '</a></div>' : '';

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
				<p><span class="label">' . esc_html__( 'Response: ', 'CP_TD' ) . '</span>
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

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
	}

	public static function render_input_upload( $module, $attributes = false ) {
		$course_id = CoursePress_Helper_Utility::the_course( true );
		$unit_id = $module->post_parent;
		$module_id = $module->ID;
		$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );

		$content = self::render_module_head( $module, $attributes );

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		$responses = CoursePress_Data_Student::get_responses( get_current_user_id(), $course_id, $unit_id, $module_id, true, $student_progress );

		$element_class = ! empty( $responses ) ? 'hide' : '';
		$response_count = ! empty( $responses ) ? count( $responses ) : 0;
		// $attributes['retry_attempts'] = 3; // DEBUG
		$disabled = ! $attributes['allow_retries'] && $response_count > 0;
		$disabled = ! ( ( ! $disabled ) && ( 0 === (int) $attributes['retry_attempts'] || (int) $attributes['retry_attempts'] >= $response_count ) );

		// RESUBMIT LOGIC
		$action = ! $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit File', 'CP_TD' ) . '</a></div>' : '';

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
					<p class="file_holder"><span class="label">' . esc_html__( 'Uploaded file: ', 'CP_TD' ) . '</span>
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

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );

	}

	public static function render_input_quiz( $module, $attributes = false ) {
		$content = self::render_module_head( $module, $attributes );

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

		// Content
		$content .= '<div class="module-content">' . do_shortcode( $module->post_content ) . '</div>';

		if ( ! empty( $attributes['questions'] ) && ! $already_passed && ! $disabled ) {

			// Has the user already answered?
			$element_class = ! empty( $responses ) && $disabled ? 'hide' : '';

			$unlimited = empty( $attributes['retry_attempts'] );
			$remaining = ! $unlimited ? (int) $attributes['retry_attempts'] - ( $response_count - 1 ) : 0;
			$remaining_message = ! $unlimited ? sprintf( __( 'You have %d attempts left.', 'CP_TD' ), $remaining ) : '';
			$content .= ! empty( $responses ) && ! $already_passed ? '<div class="not-passed-message">' . sprintf( esc_html__( 'Your last attempt was unsuccessful. Try again. %s', 'CP_TD' ), $remaining_message ) . '</div>' : '';

			// RESUBMIT LOGIC
			$action = ! $disabled ? '<div><a class="module-submit-action">' . esc_html__( 'Submit Answer', 'CP_TD' ) . '</a></div>' : '';

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
						<div class="module-quiz-questions" style="display: none;">
			';

			foreach ( $attributes['questions'] as $qi => $question ) {

				$content .= '<div class="module-quiz-question question-' . $qi . '" data-type="' . $question['type'] . '">';
				$content .= '<p class="question">' . $question['question'] . '</p>';
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

					$content .= '<input type="' . $type . '" name="question-' . $qi . '"><span>' . $answer . '</span><br />';

				}

				$content .= '</div>';
			}

			// RESUBMIT LOGIC
			$action = '<a class="module-submit-action">' . esc_html__( 'Submit Quiz Answers', 'CP_TD' ) . '</a>';
			$content .= $action;

			$content .= '</div>'; // module-quiz-questions
			$content .= '</div>'; // module-elements
		} else {
			if ( ! empty( $quiz_result_content ) ) {
				$content .= $quiz_result_content;
			}
		}

		$content .= '</div>'; // module_footer
		return str_replace( array( "\n", "\r" ), '', $content );
	}
}
