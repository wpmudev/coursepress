<?php
/**
 * Class CoursePress_Shortcode
 *
 * @since 2.0
 * @package CoursePress
 */
class CoursePress_Shortcode extends CoursePress_Utility {
	protected $courses = array();

	public function __construct() {
		$shortcodes = new CoursePress_Data_Shortcodes();
		$shortcodes->init();
	}

	private function get_course_class( $course_id ) {
		if ( empty( $this->courses[ $course_id ] ) ) {
			$course = new CoursePress_Course( $course_id );
			$this->courses[ $course_id ] = $course;
		}
		return $this->courses[ $course_id ];
	}

	public function get_course( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'show' => 'summary',
			'date_format' => coursepress_get_option( 'date_format' ),
			'label_delimiter' => ',',
			'label_tag' => 'label',
			'show_title' => true,
		), $atts, 'course' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$shows = explode( ',', $atts['show'] );
		$shows = array_map( 'trim', $shows );
		$template = '';
		if ( 'yes' === $atts['show_title'] ) {
			$template .= '[course_title]';
		}
		foreach ( $shows as $show ) {
			$template = '[course_' . $show . ']';
		}
		return $this->create_html( 'div', array( 'class' => 'course-overview' ), do_shortcode( $template ) );
	}

	public function get_course_title( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'class' => '',
			'title_tag' => 'h3',
			'clickable' => 'yes',
		), $atts, 'course_title' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$class = 'course-title';
		if ( ! empty( $atts['class'] ) ) {
			$class .= ' ' . $atts['class'];
		}
		if ( 'yes' === $atts['clickable'] ) {
			$attr = array(
				'href' => $course->get_permalink(),
				'rel' => 'bookmark',
			);
			$template = $this->create_html( 'a', $attr, $course->post_title );
		} else {
			$template = $course->post_title;
		}
		return $this->create_html( $atts['title_tag'], array( 'class' => $class ), $template );
	}

	public function get_course_instructors( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'avatar_size' => 42,
			'default_avatar' => '',
			'label' => __( 'Instructor', 'cp' ),
			'label_delimiter' => ':',
			'label_plural' => __( 'Instructors', 'cp' ),
			'label_tag' => 'h3',
			'link_all' => false,
			'link_text' => __( 'View Profile', 'cp' ),
			'list_separator' => ', ',
			'show_divider' => true,
			'style' => 'block',
			'summary_length' => 50,
		), $atts, 'course_instructors' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$instructors = $course->get_instructors();
		$count = count( $instructors );
		if ( 0 == $count ) {
			return '';
		}
		$class = array( 'course-instructors', $atts['style'] );
		$link_all = 'yes' === $atts['link_all'];
		$templates = '';
		if ( ! empty( $atts['label'] ) ) {
			$templates .= $this->create_html(
				$atts['label_tag'],
				array( 'class' => 'label' ),
				_n( $atts['label'], $atts['label_plural'], $count ) . $atts['label_delimiter']
			);
		}
		$instructors_template = array();
		foreach ( $instructors as $instructor ) {
			/**
			 * @var $instructor CoursePress_User
			 */
			$template = '';
			if ( 'block' === $atts['style'] ) {
				$template .= $instructor->get_avatar( $atts['avatar_size'] );
			}
			$link = $instructor->get_instructor_profile_link();
			if ( ! $link_all ) {
				$attr = array( 'href' => esc_url( $link ), 'class' => 'fn instructor' );
				$template .= $this->create_html( 'a', $attr, $instructor->get_name() );
			} else {
				$template .= $instructor->get_name();
			}
			$instructors_template[] = $template;
		}
		if ( 'flat' === $atts['style'] ) {
			$templates .= ' ';
		}
		$templates .= implode( $atts['list_separator'], $instructors_template );
		return $this->create_html( 'div', array( 'class' => implode( ' ', $class ) ), $templates );
	}

	public function get_course_summary( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
		), $atts );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		return $this->create_html( 'div', array( 'class' => 'course-summary' ), $course->post_excerpt );
	}

	public function get_course_description( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'label' => '',
			'class' => '',
		), $atts );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$template = $atts['label'];
		$class = 'course-description';
		if ( ! empty( $atts['class'] ) ) {
			$class .= ' ' . $atts['class'];
		}
		$template .= $course->post_content;
		$template = $this->create_html( 'div', array( 'class' => $class ), $template );
		return $template;
	}

	public function get_course_start( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'label' => __( 'Start Date', 'cp' ),
			'label_delimiter' => ':',
			'label_tag' => 'strong',
			'date_format' => coursepress_get_option( 'date_format' ),
			'class' => '',
		), $atts, 'course_start' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$template = '';
		if ( ! empty( $atts['label'] ) ) {
			$template .= $this->create_html( $atts['label_tag'], array(), $atts['label'] . $atts['label_delimiter'] );
		}
		if ( $course->course_open_ended ) {
			$template .= __( 'Already started', 'cp' );
		} else {
			$template .= $course->course_start_date;
		}
		$class = 'course-start-date';
		if ( ! empty( $atts['class'] ) ) {
			$class .= ' ' . $atts['class'];
		}
		$template = $this->create_html( 'span', array( 'class' => $class ), $template );
		return $template;
	}

	public function get_course_language( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'label' => __( 'Language', 'cp' ),
			'label_tag' => 'strong',
			'label_delimiter' => ':',
			'class' => '',
		), $atts, 'course_language' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$template = '';
		if ( ! empty( $atts['label'] ) ) {
			$template .= $this->create_html( $atts['label_tag'], array(), $atts['label'] . $atts['label_delimiter'] );
		}
		$template .= $course->__get( 'course_language' );
		$class = 'course-language';
		if ( ! empty( $atts['class'] ) ) {
			$class .= ' ' . $atts['class'];
		}
		$template = $this->create_html( 'span', array( 'class' => $class ), $template );
		return $template;
	}

	public function get_course_cost( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'label' => __( 'Price', 'cp' ),
			'label_tag' => 'strong',
			'label_delimiter' => ':',
			'class' => '',
			'no_cost_text' => __( 'Free', 'cp' ),
			'show_icon' => 'yes',
		), $atts, 'course_cost' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$template = '';
		if ( ! empty( $atts['label'] ) ) {
			$template .= $this->create_html( $atts['label_tag'], array(), $atts['label'] . $atts['label_delimiter'] );
		}
		$class = 'course-cost';
		if ( ! empty( $atts['class'] ) ) {
			$class .= ' ' . $atts['class'];
		}
		if ( ! $course->__get( 'payment_paid_course' ) ) {
			$cost = $atts['no_cost_text'];
		} else {
			$cost = $course->__get( 'mp_product_price' );
			if ( $course->__get( 'mp_sale_price_enabled' ) ) {
				$sale_price = $course->__get( 'mp_product_sale_price' );
				if ( ! empty( $sale_price ) ) {
					$cost = $sale_price;
				}
			}
		}
		$template .= $this->create_html(
			'span',
			array( 'class' => $class ),
			$cost
		);
		return $template;
	}

	public function get_course_list_box( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'show_title' => 'yes',
			'button_label' => __( 'Details', 'cp' ),
		), $atts, 'course_list_box' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$class = 'course_list_box_item';
		$template = '[course_list_image]';
		if ( ! empty( $atts['class'] ) ) {
			$class .= ' ' . $atts['class'];
		}
		if ( 'yes' === $atts['show_title'] ) {
			$template .= '[course_title]';
		}
		$template .= '[course_summary][course_instructors style="flat" label_tag="span"]';
		$template .= $this->create_html(
			'div',
			array( 'class' => 'course-meta' ),
			'[course_start label=""][course_language label=""][course_cost label=""]'
		);
		$template = do_shortcode( $template );
		return $this->create_html( 'div', array( 'class' => $class ), $template );
	}

	public function get_course_list_image( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'width' => coursepress_get_setting( 'course/image_width', 235 ),
			'height' => coursepress_get_setting( 'course/image_height', 235 ),
			'class' => '',
		), $atts, 'course_list_image' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		if ( ! empty( $course->listing_image ) ) {
			$class = 'course-feature-image';
			if ( ! empty( $atts['class'] ) ) {
				$class .= ' ' . $atts['class'];
			}
			$attr = array(
				'class' => $class,
				'src' => esc_url( $course->listing_image ),
				'width' => $atts['width'],
				'height' => $atts['height'],
			);
			return $this->create_html( 'img', $attr );
		}
		return '';
	}

	public function get_course_featured_video( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'width' => coursepress_get_setting( 'course/image_width', 235 ),
			'height' => coursepress_get_setting( 'course/image_height', 235 ),
			'class' => '',
		), $atts, 'course_featured_video' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		if ( ! empty( $course->featured_video ) ) {
			$class = 'course-featured-video';
			if ( ! empty( $atts['class'] ) ) {
				$class .= ' ' . $atts['class'];
			}
			$attr = array( 'class' => $class, 'src' => esc_url( $course->featured_video ) );
			// @todo: apply CP video.js
		}
		return '';
	}

	public function get_course_media( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'class' => '',
			'height' => coursepress_get_setting( 'course/image_height', 235 ),
			'width' => coursepress_get_setting( 'course/image_width', 235 ),
			'priority' => coursepress_get_setting( 'course/listing_media_priority', 'image' ),
			'type' => coursepress_get_setting( 'course/listing_media_type', 'image' ),
			'wrapper' => 'div',
		), $atts, 'course_media' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$class = 'course-media';
		if ( ! empty( $atts['class'] ) ) {
			$class .= ' ' . $atts['class'];
		}
		if ( 'image' === $atts['type'] ) {
			$template = $this->get_course_list_image( array( 'height' => $atts['height'], 'width' => $atts['width'] ) );
		} else {
			$template = $this->get_course_featured_video( array() );
		}
		if ( ! empty( $template ) ) {
			$template = $this->create_html(
				$atts['wrapper'],
				array( 'class' => $class ),
				$template
			);
		}
		return $template;
	}

	public function get_course_structure( $atts ) {
		/**
		 * @var $coursepress_user CoursePress_User
		 **/
		global $coursepress_user;
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'show_label' => 'yes',
			'label' => __( 'Course Structure', 'cp' ),
			'label_delimiter' => ':',
			'label_tag' => 'h3',
			'deep' => 'true',
		), $atts, 'course_structure' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$template = '';
		if ( ! empty( $atts['label'] ) && 'yes' === $atts['show_label'] ) {
			$template .= $this->create_html(
				$atts['label_tag'],
				array(),
				$atts['label']
			);
		}
		$course_id = $course->__get( 'ID' );
		$has_access = $coursepress_user->has_access_at( $course_id );
		$published = $has_access ? false : true;
		$units = $course->get_units( $published );
		if ( ! empty( $units ) ) {
			$list = '';
			foreach ( $units as $unit ) {
				/**
				 * @var $unit CoursePress_Unit
				 */
				$unit_html = '';
				$unit_title = $unit->__get( 'post_title' );
				$unit_url = esc_url( $unit->get_unit_url() );
				if ( $has_access ) {
					$attr       = array( 'href' => $unit_url );
					$unit_title = $this->create_html( 'a', $attr, $unit_title );
				} else {
					if ( $unit->__get( 'preview' ) ) {
						$attr = array( 'href' => add_query_arg( 'preview', true, $unit_url ) );
						$unit_title = $this->create_html( 'a', $attr, $unit_title );
					}
				}
				$div_attr = array( 'class' => 'unit-title-wrapper' );
				$unit_html .= $this->create_html( 'div', $div_attr, $unit_title );
				if ( 'true' === $atts['deep'] ) {
					$module_list = '';
					if ( $course->__get( 'with_modules' ) ) {
						$modules = $unit->get_modules_with_steps( $published );
						if ( ! empty( $modules ) ) {
							foreach ( $modules as $module ) {
								$module_html = '';
								$module_url = esc_url( $module['url'] );
								if ( ! empty( $module['steps'] ) ) {
									$steps = '';
									foreach ( $module['steps'] as $step ) {
										/**
										 * @var $step CoursePress_Step
										 */
										if ( $has_access ) {
											$attr       = array( 'href' => esc_url( $step->get_permalink() ) );
											$step_title = $this->create_html( 'a', $attr, $step->__get( 'post_title' ) );
										} else {
											$step_title = $step->__get( 'post_title' );
											if ( $step->__get( 'preview' ) ) {
												$url = esc_url( $step->get_permalink() );
												$attr = array( 'href' => $url );
												$step_title = $this->create_html( 'a', $attr, $step_title );
											}
										}
										$steps .= $this->create_html( 'li', array(), $step_title );
									}
									$attr = array();
									$module_html .= $this->create_html( 'ol', $attr, $steps );
								}
								if ( $has_access ) {
									$attr = array( 'href' => $module_url );
									$module_title = $this->create_html( 'a', $attr, $module['title'] );
								} elseif ( $module['preview'] ) {
									$preview_url = add_query_arg( 'preview', $module_url );
									$attr = array( 'href' => $preview_url );
									$module_title = $this->create_html( 'a', $attr, $module['title'] );
								} else {
									$module_title = $module['title'];
								}
								$module_title  = $this->create_html( 'div', array( 'class' => 'module-title-wrapper' ), $module_title );
								$attr = array();
								$module_list .= $this->create_html( 'li', $attr, $module_title . $module_html );
							}
							$attr = array();
							$unit_html .= $this->create_html( 'ol', $attr, $module_list );
						}
					} else {
						$steps = $unit->get_steps( $published );
						$step_html = '';
						foreach ( $steps as $step ) {
							/**
							 * @var $step CoursePress_Step
							 */
							$step_title = $step->__get( 'post_title' );
							$step_url = esc_url( $step->get_permalink() );
							if ( $has_access ) {
								$attr = array( 'href' => $step_url );
								$step_title = $this->create_html( 'a', $attr, $step_title );
							} elseif ( $step->__get( 'preview' ) ) {
								$attr = array( 'href' => add_query_arg( 'preview', true, $step_url ) );
								$step_title = $this->create_html( 'a', $attr, $step_title );
							}
							$attr = array();
							$step_html = $this->create_html( 'li', $attr, $step_title );
						}
						$attr = array();
						$unit_html .= $this->create_html( 'ol', $attr, $step_html );
					}
				}
				$attr = array( 'class' => 'unit' );
				$list .= $this->create_html( 'li', $attr, $unit_html );
			}
			$attr = array( 'class' => 'unit-list' );
			$template .= $this->create_html( 'ul', $attr, $list );
		}
		return $template;
	}
}
