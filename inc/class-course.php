<?php
/**
 * Class CoursePress_Course
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Course extends CoursePress_Utility {
	protected $progress_table;
	protected $student_table;

	/**
	 * Course Number
	 */
	protected $count_title_name = 'course_number_by_title';

	/**
	 * CoursePress_Course constructor.
	 *
	 * @param int|WP_Post $course
	 */
	public function __construct( $course ) {
		global $wpdb;
		if ( ! $course instanceof WP_Post ) {
			if ( is_object( $course ) ) {
				if ( isset( $course->ID ) ) {
					$course = $course->ID;
				}
			}
			$course = get_post( (int) $course );
		}
		if ( ! $course instanceof WP_Post || 'course' != $course->post_type ) {
			$this->is_error = true;
			return $this->wp_error();
		}
		$this->progress_table = $wpdb->prefix . 'coursepress_student_progress';
		$this->student_table = $wpdb->prefix . 'coursepress_students';
		$this->setUp( array(
			'ID' => $course->ID,
			'post_title' => $course->post_title,
			'post_excerpt' => $course->post_excerpt,
			'post_content' => $course->post_content,
			'post_status' => $course->post_status,
			'post_name' => $course->post_name,
			'post_author' => $course->post_author,
		) );
		// Set course meta
		$this->setUpCourseMetas();
		/**
		 * action before_delete_post
		 */
		add_action( 'before_delete_post', array( $this, 'delete_course_number' ) );
		/**
		 * filter placeholders
		 */
		add_filter( 'coursepress_replace_placeholders', array( $this, 'replace_placeholders' ), 10, 3 );
	}

	/**
	 * Update course CoursePress version.
	 *
	 * @since 3.0.0
	 */
	public function update_coursepress_version() {
		global $CoursePress;
		$course_id = $this->__get( 'ID' );
		$result = add_post_meta( $course_id, 'coursepress_version', $CoursePress->version, true );
		if ( false === $result ) {
			update_post_meta( $course_id, 'coursepress_version', $CoursePress->version );
		}
	}

	public function wp_error() {
		return new WP_Error( 'wrong_param', __( 'Invalid course ID!', 'cp' ) );
	}

	public function setUpCourseMetas() {
		$course_id = $this->__get( 'ID' );
		$settings = $this->get_settings();
		$date_format = get_option( 'date_format', 'F j, Y' );
		$time_now = current_time( 'timestamp' );
		$date_keys = array( 'course_start_date', 'course_end_date', 'enrollment_start_date', 'enrollment_end_date' );
		foreach ( $settings as $key => $value ) {
			if ( in_array( $key, $date_keys ) ) {
				if ( preg_match( '/_end_date/', $key ) ) {
					$value .= ' 23:59:59';
				}
				$timestamp = strtotime( $value, $time_now );
				$value = date_i18n( $date_format, $timestamp );
				// Add timestamp info
				$this->__set( $key . '_timestamp', $timestamp );
			}
			// Legacy fixes
			if ( 'enrollment_type' === $key && 'anyone' === $value ) {
				$value = 'registered';
			}
			if ( 'on' === $value || 'yes' === $value ) {
				$value = true;
			}
			if ( 'off' === $value ) {
				$value = false;
			}
			$this->__set( $key, $value );
			$this->__set( 'meta_' . $key, $value );
		}
		$this->__set( 'course_view', 'focus' );
		// Legacy: fix course_type meta
		$cpv = get_post_meta( $course_id, 'cp_cpv', true );
		if ( ! $cpv ) {
			$this->__set( 'with_modules', true );
			$this->__set( 'course_type', 'auto-moderated' );
		}
		/**
		 * Course CoursePress Version
		 */
		$version = get_post_meta( $course_id, 'coursepress_version', true );
		$this->__set( 'coursepress_version', $version );
	}

	public function get_settings() {
		global $CoursePress;
		$pre_completion_content = sprintf( '<h3>%s</h3>', __( 'Congratulations! You have completed COURSE_NAME!', 'cp' ) );
		$pre_completion_content .= sprintf( '<p>%s</p>', __( 'Your course instructor will now review your work and get back to you with your final grade before issuing you a certificate of completion.', 'cp' ) );
		$completion_content = sprintf( '<h3>%s</h3><p>%s</p><p>DOWNLOAD_CERTIFICATE_BUTTON</p>',
			__( 'Congratulations! You have successfully completed and passed COURSE_NAME!', 'cp' ),
			__( 'You can download your certificate here.', 'cp' )
		);
		$failed_content = sprintf( '<p>%s</p><p>%s</p>',
			__( 'Unfortunately, you didn\'t pass COURSE_NAME.', 'cp' ),
			__( 'Better luck next time!', 'cp' )
		);
		$id = $this->__get( 'ID' );
		$course_meta = array(
			'course_type' => 'auto-moderated',
			'course_language' => __( 'English', 'cp' ),
			'allow_discussion' => false,
			'allow_workbook' => false,
			'allow_grades' => false,
			'payment_paid_course' => false,
			'listing_image' => '',
			'listing_image_thumbnail_id' => 0,
			'featured_video' => '',
			'enrollment_type' => 'registered',
			'enrollment_passcode' => '',
			'enrollment_prerequisite' => array(),
			'course_view' => 'focus',
			'structure_level' => 'unit',
			'course_open_ended' => true,
			'course_start_date' => 0,
			'course_end_date' => '',
			'enrollment_open_ended' => false,
			'enrollment_start_date' => '',
			'enrollment_end_date' => '',
			'class_limited' => '',
			'class_size' => '',
			'pre_completion_title' => __( 'Almost there!', 'cp' ),
			'pre_completion_content' => $pre_completion_content,
			'minimum_grade_required' => 100,
			'course_completion_title' => __( 'Congratulations, You Passed!', 'cp' ),
			'course_completion_content' => $completion_content,
			'course_failed_title' => __( 'Sorry, you did not pass this course!', 'cp' ),
			'course_failed_content' => $failed_content,
			'basic_certificate_layout' => '',
			'basic_certificate' => false,
			'certificate_background' => '',
			'cert_margin' => array(
				'top' => 0,
				'left' => 0,
				'right' => 0,
			),
			'certificate_logo' => '',
			'certificate_logo_position' => array(
				'x' => 0,
				'y' => 0,
				'w' => 0,
			),
			'page_orientation' => 'L',
			'cert_text_color' => '#5a5a5a',
			'with_modules' => true,
			/**
			 * paid course defaults
			 */
			'mp_auto_sku' => true,
			'mp_product_price' => '',
			'mp_product_sale_price' => '',
			'mp_sale_price_enabled' => false,
			'mp_sku_placeholder' => sprintf( __( 'e.g. %s-%06d', 'cp' ), 'CP', $id ),
			'mp_sku' => '',
			'cpv' => 3,
		);
		$settings = get_post_meta( $id, 'course_settings', true );
		$settings = wp_parse_args( $settings, $course_meta );
		/**
		 * MarketPress plugin status
		 */
		$MarketPress = $CoursePress->get_class( 'CoursePress_Extension_MarketPress' );
		$settings['mp_is_instaled'] = $MarketPress->installed();
		$settings['mp_is_activated'] = $MarketPress->activated();
		return $settings;
	}

	public function update_setting( $key, $value = array() ) {
		global $CoursePress_Core;
		$course_id = $this->__get( 'ID' );
		$settings = $this->get_settings();
		if ( true === $key ) {
			$settings = $value;
			foreach ( $settings as $key => $value ) {
				update_post_meta( $course_id, 'cp_' . $key, $value );
			}
		} else {
			$settings[ $key ] = $value;
			update_post_meta( $course_id, 'cp_' . $key, $value );
		}
		update_post_meta( $course_id, 'course_settings', $settings );
		// We need date types in most queries, store them as seperate meta key
		if ( true === $key ) {
			foreach ( $settings as $key => $value ) {
				update_post_meta( $course_id, $key, $value );
			}
		} else {
			update_post_meta( $course_id, $key, $value );
		}
		// Set post thumbnail ID if not empty
		if ( ! empty( $settings['listing_image_thumbnail_id'] ) ) {
			set_post_thumbnail( $course_id, $settings['listing_image_thumbnail_id'] );
		}
		$category_type = $CoursePress_Core->__get( 'category_type' );
		if ( ! empty( $settings['course_category'] ) ) {
			wp_set_object_terms( $course_id, $settings['course_category'], $category_type );
		} else {
			wp_set_object_terms( $course_id, array(), $category_type );
		}
		/**
		 * Fire whenever a course is created or updated.
		 *
		 * @param int $course_id
		 * @param array $course_meta
		 */
		do_action( 'coursepress_course_updated', $course_id, $settings );
		/**
		 * update CoursePress version
		 */
		$this->update_coursepress_version();
		return true;
	}

	/**
	 * Returns course title.
	 *
	 * @return string
	 */
	public function get_the_title() {
		return $this->__get( 'post_title' );
	}

	/**
	 * Returns course summary.
	 *
	 * @param int $length
	 *
	 * @return bool|null|string
	 */
	public function get_summary( $length = 140 ) {
		$summary = $this->__get( 'post_excerpt' );
		$length++;
		if ( mb_strlen( $summary ) > $length ) {
			$summary = wp_strip_all_tags( $summary );
			$sub = mb_substr( $summary, 0, $length - 5 );
			$words = explode( ' ', $sub );
			$cut = ( mb_strlen( $words[ count( $words ) - 1 ] ) );
			if ( $cut < 0 ) {
				return mb_substr( $sub, 0, $cut );
			} else {
				return $sub;
			}
		}
		return $summary;
	}

	public function get_feature_image_url() {
		return $this->__get( 'listing_image' );
	}

	/**
	 * Get the course feature image.
	 *
	 * @param string|array (Optional) Image size to use. Accepts any valid
	 * image size, or an array of width and height values in pixels (in that
	 * order).
	 *
	 * @return null|string
	 */
	//public function get_feature_image( $width = 235, $height = 235, $full = false ) {
	public function get_feature_image( $size = null ) {
		$id = $this->__get( 'ID' );
		if ( empty( $size ) ) {
			$size = array(
				coursepress_get_setting( 'course/image_width', 235 ),
				coursepress_get_setting( 'course/image_height', 235 ),
			);
		}
		$classes = 'attachment-post-thumbnail size-post-thumbnail wp-post-image';
		$listing_image = $this->get_feature_image_url();
		// Try post-thumbnail
		if ( ! $listing_image ) {
			if ( has_post_thumbnail( $id ) ) {
				$listing_image = get_the_post_thumbnail( $id, $size, array( 'class' => $classes.' course-feature-image' ) ); }
		} else {
			$args = array(
				'src' => esc_url( $listing_image ),
				'class' => $classes.' course-listing-image',
			);
			if ( is_array( $size ) && 1 < count( $size ) ) {
				$args['width'] = $size[0];
				$args['height'] = $size[1];
			}
			// Add microdata to image.
			if ( apply_filters( 'coursepress_schema', false, 'image' ) ) {
				$args['itemprop'] = 'image';
			}
			$listing_image = $this->create_html( 'img', $args );
		}
		return $listing_image;
	}

	public function get_feature_video_url() {
		return $this->__get( 'featured_video' );
	}

	public function get_feature_video( $width = 235, $height = 235 ) {
		$feature_video = $this->get_feature_video_url();
		if ( ! $width ) {
			$width = coursepress_get_setting( 'course/image_width', 235 );
		}
		if ( ! $height ) {
			$height = coursepress_get_setting( 'course/image_height', 235 );
		}
		if ( ! empty( $feature_video ) ) {
			$attr = array(
				'src' => esc_url_raw( $feature_video ),
				'class' => 'video-js vjs-default-skin vjs-big-play-centered course-feature-video',
				'width' => $width,
				'height' => $height,
				'controls' => true,
				'data-setup' => $this->create_video_js_setup_data( $feature_video ),
			);
			return $this->create_html( 'video', $attr );
		}
		return null;
	}

	public function get_media( $width = 235, $height = 235 ) {
		$media_type = coursepress_get_setting( 'course/details_media_type', 'image' );
		$image = $this->get_feature_image( array( $width, $height ) );
		if ( ( 'image' == $media_type || 'default' == $media_type ) && ! empty( $image ) ) {
			return $image;
		}
		$video = $this->get_feature_video( $width, $height );
		return empty( $video )? $image : $video;
	}

	public function get_description() {
		$description = $this->__get( 'post_content' );
		// @todo: Fix HTML formatting issue here
		return $description;
	}

	public function get_course_start_date() {
		return $this->__get( 'course_start_date' );
	}

	public function get_course_end_date() {
		return $this->__get( 'course_end_date' );
	}

	public function get_course_dates( $separator = ' - ' ) {
		$course_type = $this->__get( 'course_type' );
		$open = 'auto-moderated' == $course_type;
		if ( ! $open ) {
			$open = $this->__get( 'course_open_ended' );
		}
		if ( $open ) {
			$start = $this->__get( 'course_start_date_timestamp' );
			$today = strtotime( date( 'Y-m-d 23:59:59', time() ) );
			if ( $start > $today ) {
				return $this->time2str( $start );
			}
			return __( 'Open Ended', 'cp' );
		}
		return implode( $separator, array( $this->get_course_start_date(), $this->get_course_end_date() ) );
	}

	public function get_enrollment_start_date() {
		$open_ended = $this->__get( 'enrollment_open_ended' );
		if ( $open_ended ) {
			return __( 'Anytime', 'cp' );
		}
		return $this->__get( 'enrollment_start_date' );
	}

	public function get_enrollment_end_date() {
		return $this->__get( 'enrollment_end_date' );
	}

	public function get_enrollment_dates( $separator = ' - ' ) {
		$open_ended = $this->__get( 'enrollment_open_ended' );
		if ( $open_ended ) {
			return __( 'Anytime', 'cp' );
		}
		$start = $this->get_enrollment_start_date();
		$end = $this->get_enrollment_end_date();
		if ( $start == $end ) {
			return sprintf( __( 'Only %s', 'cp' ), $start );
		}
		return implode( $separator, array( $start, $end ) );
	}

	public function get_prerequisites() {
		$courses = $this->__get( 'enrollment_prerequisite' );
		if ( empty( $courses ) ) {
			return array();
		}
		if ( ! is_array( $courses ) ) {
			$courses = array( $courses );
		}
		/**
		 * remove $course_id
		 */
		$courses = array_diff( $courses, array( $this->__get( 'ID' ) ) );
		/**
		 * return array of courses ids
		 */
		return $courses;
	}

	public function get_course_enrollment_type( $atts ) {
		$enrollment_text = '';
		$enrollment_type = $this->__get( 'enrollment_type' );

		extract( shortcode_atts( array(
			'anyone_text' => __( 'Anyone', 'cp' ),
			'manual_text' => __( 'Students are added by instructors.', 'cp' ),
			'passcode_text' => __( 'A passcode is required to enroll.', 'cp' ),
			'prerequisite_text' => __( 'Students need to complete %s first.', 'cp' ),
			'registered_text' => __( 'Registered users.', 'cp' ),
		), $atts, 'course_enrollment_type' ) );

		switch ( $enrollment_type ) {
			case 'anyone':
				$enrollment_text = $anyone_text;
			break;

			case 'registered':
				$enrollment_text = $registered_text;
			break;

			case 'passcode':
				$enrollment_text = $passcode_text;
			break;

			case 'prerequisite':
				$prereq = $this->get_prerequisites();
				$prereq_courses = array();
				foreach ( $prereq as $prereq_id ) {
					$prereq_courses[] = sprintf(
						'<a href="%s">%s</a>',
						esc_url( get_permalink( $prereq_id ) ),
						get_the_title( $prereq_id )
					);
				}
				$enrollment_text = sprintf( $prerequisite_text, implode( ', ', $prereq_courses ) );
			break;

			case 'manually':
				$enrollment_text = $manual_text;
			break;
		}
		$enrollment_text = apply_filters( 'coursepress_course_enrollment_type_text', $enrollment_text );
		return $enrollment_text;
	}

	public function get_course_language() {
		return $this->__get( 'course_language' );
	}

	public function get_course_cost() {
		$price_html = __( 'FREE', 'cp' );
		if ( $this->__get( 'payment_paid_course' ) ) {
			$price = $this->__get( 'mp_product_price' );
			/**
			 * Trigger to allow changes on course cost
			 */
			$price_html = apply_filters( 'coursepress_course_cost', $price_html, $price, $this );
		}
		return $price_html;
	}

	public function get_view_mode() {
		return $this->__get( 'course_view' );
	}

	public function get_product_id() {
		return $this->__get( 'mp_product_id' );
	}

	public function is_with_modules() {
		return $this->__get( 'with_modules' );
	}

	public function is_paid_course() {
		return $this->__get( 'payment_paid_course' );
	}

	/**
	 * Check if the course has already started.
	 *
	 * @return bool
	 */
	public function is_course_started() {
		$time_now = $this->date_time_now();
		$openEnded = $this->__get( 'course_open_ended' );
		$start_date = $this->__get( 'course_start_date_timestamp' );
		if ( empty( $openEnded )
			&& $start_date > 0
			&& $start_date > $time_now ) {
			return false;
		}
		if ( $time_now < $start_date ) {
			return false;
		}
		return true;
	}

	/**
	 * Check if the course is no longer open.
	 *
	 * @return bool
	 */
	public function has_course_ended() {
		$time_now = $this->date_time_now();
		$openEnded = $this->__get( 'course_open_ended' );
		$end_date = $this->__get( 'course_end_date_timestamp' );
		if ( empty( $openEnded )
			&& $end_date > 0
			&& $end_date < $time_now ) {
			return true;
		}
		return false;
	}

	/**
	 * Check if the course is available
	 *
	 * @return bool
	 */
	public function is_available() {
		$course_type = $this->__get( 'course_type' );
		if ( 'auto-moderated' == $course_type ) {
			// Auto-moderated courses are always available
			return true;
		}
		$is_available = $this->is_course_started();
		if ( $is_available ) {
			// Check if the course hasn't ended yet
			if ( $this->has_course_ended() ) {
				$is_available = false;
			}
		}
		return $is_available;
	}

	/**
	 * Check if enrollment is open.
	 *
	 * @return bool
	 */
	public function is_enrollment_started() {
		/**
		 * Check is enrollment always possible
		 */
		$enrollment_open = $this->__get( 'enrollment_open_ended' );
		if ( ! empty( $enrollment_open ) ) {
			return true;
		}
		/**
		 * check is enrollment open date erlier that Today
		 */
		$time_now = $this->date_time_now();
		$start_date = $this->__get( 'enrollment_start_date_timestamp' );
		return $start_date < $time_now;
	}

	/**
	 * Check if enrollment has closed.
	 *
	 * @return bool
	 */
	public function has_enrollment_ended() {
		/**
		 * Check is enrollment always possible
		 */
		$enrollment_open = $this->__get( 'enrollment_open_ended' );
		if ( ! empty( $enrollment_open ) ) {
			return true;
		}
		/**
		 * check is enrollment close date late that Today
		 */
		$time_now = $this->date_time_now( '23:59:59' );
		$end_date = $this->__get( 'enrollment_end_date_timestamp' );
		return $end_date >= $time_now;
	}

	/**
	 * Check if user can enroll to the course.
	 *
	 * @return bool
	 */
	public function user_can_enroll() {
		$available = $this->is_available();
		if ( $available ) {
			// Check if enrollment has started
			$available = $this->is_enrollment_started();
			if ( $available ) {
				$available = $this->has_enrollment_ended();
			}
			// Check if enrollment already ended
			if ( $available && $this->has_course_ended() ) {
				$available = false;
			}
		}
		return $available;
	}

	private function _get_instructors() {
		$id = $this->__get( 'ID' );
		$instructor_ids = get_post_meta( $id, 'instructor' );
		if ( is_array( $instructor_ids ) ) {
			$instructor_ids = array_filter( $instructor_ids );
		}
		if ( ! empty( $instructor_ids ) ) {
			return $instructor_ids;
		}
		// Legacy call
		// @todo: Delete this meta
		$instructor_ids = get_post_meta( $id, 'instructors', true );
		if ( ! empty( $instructor_ids ) ) {
			foreach ( $instructor_ids as $instructor_id ) {
				coursepress_add_course_instructor( $instructor_id, $id );
			}
		}
		return $instructor_ids;
	}

	/**
	 * Count total number of course instructors.
	 *
	 * @return int
	 */
	public function count_instructors() {
		return count( $this->_get_instructors() );
	}

	/**
	 * Get course instructors.
	 *
	 * @return WP_User[] An array of WP_User object on success.
	 */
	public function get_instructors() {
		$ids = $this->_get_instructors();
		return $this->_users( $ids );
	}

	/**
	 * Get instructors emails.
	 *
	 * @since 3.0.0
	 *
	 * @return array $emails Array of emails.
	 */
	public function get_instructors_emails() {
		$emails = array();
		$ids = $this->_get_instructors();
		foreach ( $ids as $id ) {
			$user_info = get_userdata( $id );
			if ( ! empty( $user_info->user_email ) ) {
				$emails[] = $user_info->user_email;
			}
		}
		return $emails;
	}

	/**
	 * get users data
	 */
	private function _users( $ids ) {
		$users = array();
		if ( empty( $ids ) ) {
			return $users;
		}
		foreach ( $ids as $id ) {
			$users[ $id ] = coursepress_get_user( $id );
		}
		return $users;
	}

	public function get_instructors_link() {
		$instructors = $this->get_instructors();
		$links = array();
		if ( ! empty( $instructors ) ) {
			foreach ( $instructors as $instructor ) {
				$links[] = $this->create_html(
					'a',
					array(
						'href' => esc_url( $instructor->get_instructor_profile_link() ),
					),
					$instructor->get_name()
				);
			}
		}
		return $links;
	}

	private function _get_facilitators() {
		$id = $this->__get( 'ID' );
		$facilitator_ids = get_post_meta( $id, 'facilitator' );
		if ( is_array( $facilitator_ids ) && ! empty( $facilitator_ids ) ) {
			return array_unique( array_filter( $facilitator_ids ) );
		}
		return array();
	}

	/**
	 * Get facilitators emails.
	 *
	 * @since 3.0.0
	 *
	 * @return array $emails Array of emails.
	 */
	public function get_facilitators_emails() {
		$emails = array();
		$ids = $this->_get_facilitators();
		foreach ( $ids as $id ) {
			$user_info = get_userdata( $id );
			if ( ! empty( $user_info->user_email ) ) {
				$emails[] = $user_info->user_email;
			}
		}
		return $emails;
	}

	/**
	 * Count the total number of course facilitators.
	 *
	 * @return int
	 */
	public function count_facilitators() {
		return count( $this->_get_facilitators() );
	}

	/**
	 * Get course facilitators.
	 *
	 * @return array of WP_User object
	 */
	public function get_facilitators() {
		$ids = $this->_get_facilitators();
		return $this->_users( $ids );
	}

	/**
	 * Returns IDs of all the students enrolled in the course
	 * @return int[] IDs of enrolled students
	 */
	public function get_student_ids() {
		global $wpdb;
		$sql = "SELECT student_id FROM {$this->student_table} WHERE course_id = %d";
		$course_id = $this->__get( 'ID' );
		return $wpdb->get_col(
			$wpdb->prepare( $sql, $course_id )
		);
	}

	/**
	 * Returns IDs of certified users.
	 *
	 * @return array IDs of all the users that are certified for this course.
	 */
	public function get_certified_student_ids() {
		global $wpdb;
		$sql = $wpdb->prepare( "select post_author from {$wpdb->posts} where post_type = %s and post_parent = %d", 'cp_certificate', $this->ID );
		return $wpdb->get_col( $sql );
	}

	/**
	 * Count total number of students in the course.
	 *
	 * @return int
	 */
	public function count_students() {
		return count( $this->get_student_ids() );
	}

	/**
	 * Check if allowed students limit reached.
	 *
	 * @return int
	 */
	public function is_students_full() {
		$course_id = $this->__get( 'ID' );
		$limit = coursepress_course_get_setting( $course_id, 'class_size' );
		if ( $limit ) {
			$students = $this->count_students();
			return $limit <= $students;
		}
		return false;
	}

	/**
	 * Count the total number of certified students in the course.
	 *
	 * @return int
	 */
	public function count_certified_students() {
		return count( $this->get_certified_student_ids() );
	}

	/**
	 * Get course students
	 *
	 * @return CoursePress_User[] array of CoursePress_User object
	 */
	public function get_students( $query_args = array() ) {
		$query = $this->build_students_query( $query_args );
		return $this->build_cp_user_objects( $query->get_results() );
	}

	/**
	 * Get certified course students
	 *
	 * @return CoursePress_User[] array of CoursePress_User object
	 */
	public function get_certified_students( $query_args ) {
		$query = $this->build_certified_students_query( $query_args );
		return $this->build_cp_user_objects( $query->get_results() );
	}

	/**
	 * Get non certified course students
	 *
	 * @return CoursePress_User[] array of CoursePress_User object
	 */
	public function get_non_certified_students( $query_args ) {
		$query = $this->build_non_certified_students_query( $query_args );
		return $this->build_cp_user_objects( $query->get_results() );
	}

	/**
	 * @return WP_User_Query
	 */
	private function build_students_query( $query_args ) {
		$student_ids = $this->get_student_ids();
		$query_args = wp_parse_args($query_args, array(
			'orderby' => 'user_login',
			'fields'  => 'ID',
			'include' => empty( $student_ids ) ? PHP_INT_MAX : $student_ids,
		));
		return new WP_User_Query( $query_args );
	}

	/**
	 * @return WP_User_Query
	 */
	private function build_certified_students_query( $query_args ) {
		$include = $this->get_certified_student_ids();
		$query_args['include'] = empty( $include ) ? PHP_INT_MAX : $include;
		return $this->build_students_query( $query_args );
	}

	/**
	 * @return WP_User_Query
	 */
	private function build_non_certified_students_query( $query_args ) {
		$include = array_diff(
			$this->get_student_ids(),
			$this->get_certified_student_ids()
		);
		$query_args['include'] = empty( $include ) ? PHP_INT_MAX : $include;
		return $this->build_students_query( $query_args );
	}

	private function build_cp_user_objects( $student_ids ) {
		$student_objects = array();
		if ( ! empty( $student_ids ) ) {
			foreach ( $student_ids as $student_id ) {
				$student_objects[ $student_id ] = new CoursePress_User( $student_id );
			}
		}
		return $student_objects;
	}

	/**
	 * Get invited students.
	 *
	 * @since 3.0.0
	 *
	 * @return object $invitee Invited students.
	 */
	public function get_invited_students() {
		$invitee = $this->__get( 'invited_students' );
		if ( ! empty( $invitee ) ) {
			foreach ( $invitee as $pos => $invite ) {
				if ( isset( $invite->timestamp ) && ! empty( $invite->timestamp ) ) {
					$invite->date = $this->date( $invite->timestamp );
				} elseif ( isset( $invite->date ) && ! empty( $invite->date ) ) {
					$invite->date = $this->date( $invite->date );
				} else {
					// Legacy:: Previous invitation has no date
					$invite->date = '-';
				}
				$invitee->{$pos} = $invite;
			}
		}
		return $invitee;
	}

	/**
	 * Get an array of categories of the course.
	 *
	 * @return array
	 */
	public function get_category() {
		$id = $this->__get( 'ID' );
		$course_category = wp_get_object_terms( $id, 'course_category' );
		$cats = array();
		if ( ! empty( $course_category ) ) {
			foreach ( $course_category as $term ) {
				$cats[ $term->term_id ] = $term->name; }
		}
		return $cats;
	}

	public function get_permalink() {
		$course_name = $this->__get( 'post_name' );
		return coursepress_get_main_courses_url() . trailingslashit( $course_name );
	}

	public function get_units_url() {
		$course_url = $this->get_permalink();
		$slug = coursepress_get_setting( 'slugs/units', 'units' );
		return $course_url . trailingslashit( $slug );
	}

	public function get_discussion_url() {
		$course_url = $this->get_permalink();
		$discussion_slug = coursepress_get_setting( 'slugs/discussions', 'discussions' );
		return $course_url . trailingslashit( $discussion_slug );
	}

	public function get_discussion_new_url() {
		$url = $this->get_discussion_url();
		$slug = coursepress_get_setting( 'slugs/discussions_new', 'add_new_discussion' );
		return $url . trailingslashit( $slug );
	}

	public function get_grades_url() {
		$course_url = $this->get_permalink();
		$grades_slug = coursepress_get_setting( 'slugs/grades', 'grades' );
		return $course_url . trailingslashit( $grades_slug );
	}

	public function get_unenroll_url( $redirect = '' ) {
		$url = array(
			'course_id' => $this->__get( 'ID' ),
			'action' => 'coursepress_unenroll',
			'_wpnonce' => wp_create_nonce( 'coursepress_nonce' ),
		);
		if ( ! empty( $redirect ) ) {
			$url['redirect'] = $redirect;
		}
		$url = add_query_arg( $url, admin_url( 'admin-ajax.php' ) );
		return $url;
	}

	public function get_workbook_url() {
		$course_url = $this->get_permalink();
		$workbook_slug = coursepress_get_setting( 'slugs/workbook', 'workbook' );
		return $course_url . trailingslashit( $workbook_slug );
	}

	/**
	 * Get notifications url for the course.
	 *
	 * @return string
	 */
	public function get_notifications_url() {
		$course_url = $this->get_permalink();
		$notifications_slug = coursepress_get_setting( 'slugs/notifications', 'notifications' );
		return $course_url . trailingslashit( $notifications_slug );
	}

	public function get_edit_url() {
		$url = add_query_arg( array(
			'page' => 'coursepress_course',
			'cid' => $this->__get( 'ID' ),
		), admin_url( 'admin.php' ) );
		return $url;
	}

	private function _get_units( $published = true, $ids = true ) {
		$args = array(
			'post_type'      => 'unit',
			'post_status'    => $published ? 'publish' : 'any',
			'post_parent'    => $this->__get( 'ID' ),
			'posts_per_page' => -1, // Units are often retrieve all at once
			'suppress_filters' => true,
			'orderby' => 'menu_order',
			'order' => 'ASC',
		);
		if ( $ids ) {
			$args['fields'] = 'ids';
		}
		$units = get_posts( $args );
		return $units;
	}

	public function count_units( $published = true ) {
		$units = $this->_get_units( $published );
		return count( $units );
	}

	public function get_units( $published = true ) {
		if ( $this->__get( 'current_units' ) ) {
			return $this->__get( 'current_units' );
		}
		$units = array();
		$results = $this->_get_units( $published, false );
		if ( ! empty( $results ) ) {
			foreach ( $results as $unit ) {
				$unitClass = new CoursePress_Unit( $unit, $this );
				$units[] = $unitClass;
			}
		}
		$this->__set( 'current_units', $units );
		return $units;
	}

	public function get_course_structure( $show_details = false ) {
		/**
		 * @var $user CoursePress_User
		 */
		$course_id = $this->__get( 'ID' );
		$user = coursepress_get_user();
		$has_access = $user->has_access_at( $course_id );
		$structure = '';
		$units = $this->get_units( ! $has_access );
		if ( $units ) {
			foreach ( $units as $unit ) {
				$unit_structure = $unit->get_unit_structure( false, $show_details );
				$structure .= $this->create_html( 'li', false, $unit_structure );
			}
			$structure = $this->create_html( 'ul', array( 'class' => 'units-archive-list tree unit-tree' ), $structure );
		} else {
			$structure = $this->create_html( 'p', array(), __( 'There is no Units yet.', 'cp' ) );
		}
		return $structure;
	}

	/**
	 * Duplicate current course.
	 *
	 * This class object is created based on a WP_Post object. So using the current
	 * course post data, create new post of type "course". If success, then copy the
	 * course metadata to newly created course post.
	 * If there are units set, duplicate those units also.
	 *
	 * @return bool Success?
	 */
	public function duplicate_course() {
		global $CoursePress_Core;

		// Course ID is set when this class is instantiated.
		$course_id = $this->__get( 'ID' );
		// If in case course post object is not and ID not found, bail.
		if ( empty( $course_id ) ) {
			/**
			 * Perform actions if the duplication was failed.
			 *
			 * Note: We don't have course ID here.
			 *
			 * @since 3.0
			 */
			do_action( 'coursepress_course_duplicate_failed', false );
			return false;
		}
		/**
		 * Allow course duplication to be cancelled when filter returns true.
		 *
		 * @since 1.2.1
		 */
		if ( apply_filters( 'coursepress_course_cancel_duplicate', false, $course_id ) ) {
			/**
			 * Perform actions if the duplication was cancelled.
			 *
			 * @since 1.2.1
			 */
			do_action( 'coursepress_course_duplicate_cancelled', $course_id );
			return false;
		}
		// Copy of current course object.
		$new_course = clone $this;
		// Unset old ID, otherwise it will update the existing course.
		unset( $new_course->ID );
		// Set basic details.
		$new_course->post_author = get_current_user_id();
		$new_course->post_status = 'draft';
		$new_course->post_type = 'course';
		$new_course->post_name = $new_course->post_name . '-copy';
		$new_course->post_title	= $new_course->post_title . ' (copy)';
		// Attempt to create new post of type "course".
		$new_course_id = wp_insert_post( $new_course );
		// If duplicate course was created.
		if ( ! empty( $new_course_id ) ) {
			// Copy the old course metadata to duplicated course.
			$course_metas = get_post_meta( $course_id );
			if ( ! empty( $course_metas ) ) {
				foreach ( $course_metas as $key => $value ) {
					$value = array_pop( $value );
					$value = maybe_unserialize( $value );
					update_post_meta( $new_course_id, $key, $value );
				}
			}
			// If units are available for course, duplicate them.
			$units = coursepress_get_units( $course_id );
			if ( ! empty( $units ) ) {
				foreach ( $units as $unit ) {
					$unit = new CoursePress_Unit( $unit );
					$unit->duplicate_unit( $new_course_id );
				}
			}

			$instructors = $this->get_instructors();
			if ( ! empty( $instructors ) ) {
				foreach ( $instructors as $instructor ) {
					coursepress_add_course_instructor( $instructor->__get( 'ID' ), $new_course_id );
				}
			}

			$category_type = $CoursePress_Core->__get( 'category_type' );
			$categories = $this->get_category();
			if ( empty( $categories ) ) {
				$categories = array();
			}
			wp_set_object_terms( $new_course_id, $categories, $category_type );

			/**
			 * save course number
			 */
			$this->save_course_number( $new_course_id, $new_course->post_title );
			/**
			 * Perform actions if the duplication was successful.
			 *
			 * @since 3.0
			 */
			do_action( 'coursepress_course_duplicated', $new_course_id );
			return true;
		}
		// This action is documented above.
		do_action( 'coursepress_course_duplicate_failed', $course_id );
		return false;
	}

	public function get_status() {
		$status = $this->is_available() ? 'active' : '';
		if ( $this->has_course_ended() ) {
			$status = 'ended';
		} elseif ( ! $this->is_course_started() ) {
			$status = 'future';
		}
		return $status;
	}

	/**
	 * Get couse author user object.
	 *
	 * @return mixed CoursePress_User object or false.
	 */
	public function get_author() {
		$author = false;
		// Get current course author id.
		$author_id = $this->__get( 'post_author' );
		if ( $author_id ) {
			// Get the coursepress user object.
			$author = coursepress_get_user( $author_id );
			// If not a valid user.
			if ( is_wp_error( $author ) ) {
				return false;
			}
		}
		return $author;
	}

	/**
	 * Add custom filed with counter for posts with indetical title
	 *
	 * @since 2.0.0
	 *
	 * @param integer $post_id Post ID
	 * @param string $post_title Post title.
	 * @param array $excludes Array of excluded Post IDs
	 */
	public function save_course_number( $post_id, $post_title, $excludes = array() ) {
		global $wpdb, $CoursePress_Core;
		if ( ! coursepress_is_course( $post_id ) ) {
			return;
		}
		$course_post_type = $CoursePress_Core->course_post_type;
		$sql = $wpdb->prepare(
			"select ID from {$wpdb->posts} where post_title = ( select a.post_title from {$wpdb->posts} a where id = %d ) and post_type = %s and post_status in ( 'publish', 'draft', 'pending', 'future' ) order by id asc",
			$post_id,
			$course_post_type
		);
		$posts = $wpdb->get_results( $sql );
		$limit = 2 + count( $excludes );
		if ( count( $posts ) < $limit ) {
			delete_post_meta( $post_id, $this->count_title_name );
			return;
		}
		$count = 1;
		foreach ( $posts as $post ) {
			if ( ! empty( $excludes ) && in_array( $post->ID, $excludes ) ) {
				continue;
			}
			/**
			 * we need it only once
			 */
			if ( ! add_post_meta( $post->ID, $this->count_title_name, $count, true ) ) {
				update_post_meta( $post->ID, $this->count_title_name, $count );
			}
			$count++;
		}
	}

	/**
	 * Function called by filter "the_title" to add number.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_title Post title.
	 * @param integer $post_id Post ID.
	 * @return string Post title.
	 */
	public function get_numeric_identifier_to_course_name( $post_id = 0, $before = ' (', $after = ')' ) {
		if ( ! empty( $post_id ) ) {
			$number = get_post_meta( $post_id, $this->count_title_name, true );
			if ( ! empty( $number ) ) {
				return $before.$number.$after;
			}
		}
		return '';
	}

	/**
	 * Function called on action "before_delete_post" to clear custom fileds
	 * with course number.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $post_id Post ID.
	 */
	public function delete_course_number( $post_id ) {
		global $wpdb, $CoursePress_Core;
		if ( ! coursepress_is_course( $post_id ) ) {
			return;
		}
		$course_post_type = $CoursePress_Core->course_post_type;
		$sql = $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_title = ( SELECT a.post_title FROM {$wpdb->posts} a WHERE a.ID = %d )",
			$post_id
		);
		$results = $wpdb->get_results( $sql );
		foreach ( $results as $post ) {
			delete_post_meta( $post->ID, $this->count_title_name );
		}
		$post_title = get_the_title( $post_id );
		$this->save_course_number( $post_id, $post_title, array( $post_id ) );
	}

	public function replace_placeholders( $content, $post_id, $user_id = 0 ) {
		if ( ! coursepress_is_course( $post_id ) ) {
			return $content;
		}
		$content = preg_replace( '/COURSE_NAME/', $this->post_title, $content );
		$content = preg_replace( '/COURSE_OVERVIEW/', $this->post_excerpt, $content );
		/**
		 * COURSE_UNIT_LIST
		 */
		$value = '';
		$units = $this->get_units();
		foreach ( $units as $unit ) {
			$value .= sprintf( '<li>%s</li>', $unit->post_title );
		}
		if ( ! empty( $value ) ) {
			$value = sprintf( '<ul>%s</ul>', $value );
		}
		$content = preg_replace( '/COURSE_UNIT_LIST/', $value, $content );
		/**
		 * certificate
		 */
		$certificate = new CoursePress_Certificate();
		$value = $certificate->get_pdf_file_url( $post_id, $user_id );
		$content = preg_replace( '/DOWNLOAD_CERTIFICATE_LINK/', $value, $content );
		$value = sprintf( '<a href="%s">%s</a>', esc_url( $value ), esc_html__( 'Download Certificate', 'cp' ) );
		$content = preg_replace( '/DOWNLOAD_CERTIFICATE_BUTTON/', $value, $content );
		/**
		 * Workbook
		 */
		$workbook = coursepress_get_student_workbook_data( $user_id, $post_id );
		$value = '';
		if ( ! empty( $workbook ) ) {
			foreach ( $workbook as $item ) {
				$value .= sprintf( '<li>%s - %d%%</li>',  $item['title'], $item['progress'] );
			}
		}
		if ( empty( $value ) ) {
			$value = __( 'Workbook is not available for this course.', 'cp' );
		} else {
			$value = sprintf( '<ul>%s</ul>', $value );
		}
		$content = preg_replace( '/STUDENT_WORKBOOK/', $value, $content );
		return $content;
	}

	/**
	 * check email - it is possible to invite?
	 */
	public function can_invite_email( $email ) {
		/**
		 * check email
		 */
		if ( ! is_email( $email ) ) {
			return new WP_Error(
				'error',
				__( 'Entered email is not valid.', 'cp' )
			);
		}
		/**
		 * check current user
		 */
		$id = get_current_user_id();
		$user_info = get_userdata( $id );
		if ( $user_info->user_email === $email ) {
			return new WP_Error(
				'error',
				__( 'The attempt to invite yourself is ridiculous and ineffective.', 'cp' )
			);
		}
		/**
		 * check course autor
		 */
		$user_info = get_userdata( $this->post_author );
		if ( $user_info->user_email === $email ) {
			sprintf(
				__( 'User with email %s is the course author this email and can not be invited as a student.', 'cp' ),
				esc_html( $email )
			);
		}
		/**
		 * check already invited
		 */
		$invitee = $this->get_invited_students();
		if ( isset( $invitee->$email ) ) {
			return new WP_Error(
				'error',
				sprintf(
					__( 'User with email %s is already invited.', 'cp' ),
					esc_html( $email )
				)
			);
		}
		/**
		 * check instructors
		 */
		$instructors = $this->get_instructors_emails();
		if ( in_array( $email, $instructors ) ) {
			return new WP_Error(
				'error',
				sprintf(
					__( 'User with email %s is the instructor of this course and this email can not be invited as a student.', 'cp' ),
					esc_html( $email )
				)
			);
		}
		/**
		 * check facilitators
		 */
		$facilitators = $this->get_facilitators_emails();
		if ( in_array( $email, $facilitators ) ) {
			return new WP_Error(
				'error',
				sprintf(
					__( 'User with email %s is the facilitator of this course and this email can not be invited as a student.', 'cp' ),
					esc_html( $email )
				)
			);
		}
		/**
		 * check students
		 */
		$user = get_user_by( 'email', $email );
		if ( is_a( $user, 'WP_User' ) ) {
			$user = new CoursePress_User( $user );
			if ( ! is_wp_error( $user ) ) {
				$is_enrolled = $user->is_enrolled_at( $this->ID );
				if ( $is_enrolled ) {
					return new WP_Error(
						'error',
						sprintf(
							__( 'User with email %s is already enrolled to this course and can not be invited again', 'cp' ),
							esc_html( $email )
						)
					);
				}
			}
		}
		return true;
	}
}
