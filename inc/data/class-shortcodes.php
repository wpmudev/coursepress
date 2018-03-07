<?php

/**
 * Class CoursePress_Data_Shortcodes
 *
 * @since 3.0
 * @package CoursePress
 */
final class CoursePress_Data_Shortcodes extends CoursePress_Utility {

	/**
	 * Load the individual shortcode modules.
	 * For better maintenance and performance the shortcodes are split into
	 * multiple files instead of having one huge file.
	 *
	 * @since  2.0.0
	 */
	public function init() {
		$class_object = new CoursePress_Data_Shortcode_Course();
		$class_object->init();
		$class_object = new CoursePress_Data_Shortcode_CourseTemplate();
		$class_object->init();
		$class_object = new CoursePress_Data_Shortcode_Instructor();
		$class_object->init();
		$class_object = new CoursePress_Data_Shortcode_Student();
		$class_object->init();
		$class_object = new CoursePress_Data_Shortcode_Template();
		$class_object->init();
		$class_object = new CoursePress_Data_Shortcode_Unit();
		$class_object->init();
	}

	/**
	 * Get shortcode types array.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_shortcode_types() {

		$types = array(
			'course'      => __( 'Course', 'cp' ),
			'instructors' => __( 'Instructors', 'cp' ),
			'students'    => __( 'Students', 'cp' ),
		);

		return apply_filters( 'coursepress_shortcodes_types', $types );
	}

	/**
	 * Get shortcode sub types array.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_shortcode_sub_types() {

		$sub_types = array(
			'course' => array(
				'course_details'         => __( 'Details', 'cp' ),
				'course_title'           => __( 'Title', 'cp' ),
				'course_summary'         => __( 'Summary', 'cp' ),
				'course_desc'            => __( 'Description', 'cp' ),
				'course_start_date'      => __( 'Start Date', 'cp' ),
				'course_end_date'        => __( 'End Date', 'cp' ),
				'course_dates'           => __( 'Dates', 'cp' ),
				'course_enr_start'       => __( 'Enrollment Start', 'cp' ),
				'course_enr_end'         => __( 'Enrollment End', 'cp' ),
				'course_enr_dates'       => __( 'Enrollment Dates', 'cp' ),
				'course_enr_type'        => __( 'Enrollment Type', 'cp' ),
				'course_time_estimation' => __( 'Time Estimation', 'cp' ),
				'course_class_size'      => __( 'Class Size', 'cp' ),
				'course_cost'            => __( 'Cost', 'cp' ),
				'course_lang'            => __( 'Language', 'cp' ),
				'course_list_img'        => __( 'List Image', 'cp' ),
				'course_feat_video'      => __( 'Featured Video', 'cp' ),
				'course_thumb'           => __( 'Thumbnail', 'cp' ),
				'course_media'           => __( 'Media', 'cp' ),
				'course_join_btn'        => __( 'Join Button', 'cp' ),
				'course_action_links'    => __( 'Action Links', 'cp' ),
				'course_cal'             => __( 'Calendar', 'cp' ),
				'course_list'            => __( 'List', 'cp' ),
				'course_featured'        => __( 'Featured Course', 'cp' ),
				'course_structure'       => __( 'Structure', 'cp' ),
				'course_signup_page'     => __( 'Signup/Login Page', 'cp' ),
				'course_social_links'    => __( 'Social Links', 'cp' ),
			),
			'instructors' => array(
				'instructors_list'     => __( 'Instructors List', 'cp' ),
				'instructors_avatar'   => __( 'Instructor Avatar', 'cp' ),
				'instructors_prof_url' => __( 'Instructor Profile URL', 'cp' ),
			),
			'students'    => array(
				'student_dash_temp'     => __( 'Student Dashboard Template', 'cp' ),
				'student_settings_temp' => __( 'Student Settings Template', 'cp' ),
			),
		);

		/**
		 * sort
		 */
		foreach ( $sub_types as $key => $data ) {
			asort( $data );
			$sub_types[ $key ] = $data;
		}

		/**
		 * Filter to alter shortcodes sub types array.
		 *
		 * @param array $data Sub types.
		 */
		return apply_filters( 'coursepress_shortcodes_sub_types', $sub_types );
	}

	/**
	 * Get shortcode details array.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_shortcode_details() {

		$details = array(
			'course_details'        => array(
				'title'         => __( 'COURSE DETAILS', 'cp' ),
				'description'   => __( 'This shortcode allows you to display details about your course.', 'cp' ),
				'usage'         => array( '[course show="title, summary, cost" course_id="5"]' ),
				'add_info'      => coursepress_alert_message( __( 'All the same information can be retrieved by using the specific course shortcodes following.', 'cp' ) ),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
					array(
						'attr'        => 'show',
						'description' => __( 'All the fields you would like to show.', 'cp' ),
						'options'     => 'title, summary, description, start, end, dates, enrollment_start, enrollment_end, enrollment_dates, enrollment_type, class_size, cost, language, instructors, image, video, media, button, action_links, calendar',
						'default'     => 'summary',
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'date_format',
						'description' => __( 'PHP style date format.', 'cp' ),
					),
					array(
						'attr'        => 'label_tag',
						'description' => __( 'HTML tag (without brackets) to use for the individual labels.', 'cp' ),
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to use after the label.', 'cp' ),
					),
				),
			),
			'course_title'          => array(
				'title'         => __( 'COURSE TITLE', 'cp' ),
				'description'   => __( 'This shortcode displays the course title.', 'cp' ),
				'usage'         => array( '[course_title course_id="4"]', '[course_title]' ),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'title_tag',
						'description' => __( 'The HTML tag (without brackets) to use for the title.', 'cp' ),
						'default'     => 'h3',
					),
					array(
						'attr'        => 'label_tag',
						'description' => __( 'HTML tag (without brackets) to use for the individual labels.', 'cp' ),
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to use after the label.', 'cp' ),
					),
				),
			),
			'course_summary'        => array(
				'title'         => __( 'COURSE SUMMARY', 'cp' ),
				'description'   => __( 'This shortcode displays the course summary/excerpt.', 'cp' ),
				'usage'         => array( '[course_summary course_id="4"]', '[course_summary]' ),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'length',
						'description' => __( 'Text length of the summary.', 'cp' ),
						'default'     => __( 'empty (uses WordPress excerpt length)', 'cp' ),
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_desc'           => array(
				'title'         => __( 'COURSE DESCRIPTION', 'cp' ),
				'description'   => __( 'This shortcode displays the longer course description (post content).', 'cp' ),
				'usage'         => array( '[course_description course_id="4"]', '[course_description]' ),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_start_date'     => array(
				'title'         => __( 'COURSE START DATE', 'cp' ),
				'description'   => __( 'This shortcode shows the course start date.', 'cp' ),
				'usage'         => array(
					'[course_start]',
					'[course_start label="Awesomeness begins on" label_tag="h3"]',
				),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'date_format',
						'description' => __( 'PHP style date format.', 'cp' ),
						'default'     => __( 'WordPress setting', 'cp' ),
					),
					array(
						'attr'        => 'label',
						'description' => __( 'Label to display for the output. Set label to "" to hide the label completely.', 'cp' ),
					),
					array(
						'attr'        => 'label_tag',
						'description' => __( 'HTML tag (without brackets) to use for the individual labels.', 'cp' ),
						'default'     => 'strong',
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to use after the label.', 'cp' ),
						'default'     => 'colon (:)',
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_end_date'       => array(
				'title'         => __( 'COURSE END DATE', 'cp' ),
				'description'   => __( 'This shortcode shows the course end date.', 'cp' ),
				'usage'         => array(
					'[course_end]',
					'[course_end label="The End." label_tag="h3" course_id="5"]',
				),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'date_format',
						'description' => __( 'PHP style date format.', 'cp' ),
						'default'     => __( 'WordPress setting', 'cp' ),
					),
					array(
						'attr'        => 'label',
						'description' => __( 'Label to display for the output. Set label to "" to hide the label completely.', 'cp' ),
					),
					array(
						'attr'        => 'label_tag',
						'description' => __( 'HTML tag (without brackets) to use for the individual labels.', 'cp' ),
						'default'     => 'strong',
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to use after the label.', 'cp' ),
						'default'     => 'colon (:)',
					),
					array(
						'attr'        => 'no_date_text',
						'description' => __( 'Text to display if the course has no end date.', 'cp' ),
						'default'     => __( 'No End Date', 'cp' ),
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_dates'          => array(
				'title'         => __( 'COURSE DATES', 'cp' ),
				'description'   => __( 'This shortcode displays the course start and end date range. Typically as [course_start] - [course_end].', 'cp' ),
				'usage'         => array(
					'[course_dates course_id="42"]',
					'[course_dates course_id="42" show_alt_display="yes" alt_display_text="Learn Anytime!"]',
				),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'date_format',
						'description' => __( 'PHP style date format.', 'cp' ),
						'default'     => __( 'WordPress setting', 'cp' ),
					),
					array(
						'attr'        => 'label',
						'description' => __( 'Label to display for the output. Set label to "" to hide the label completely.', 'cp' ),
					),
					array(
						'attr'        => 'label_tag',
						'description' => __( 'HTML tag (without brackets) to use for the individual labels.', 'cp' ),
						'default'     => 'strong',
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to use after the label.', 'cp' ),
						'default'     => 'colon (:)',
					),
					array(
						'attr'        => 'no_date_text',
						'description' => __( 'Text to display if the course has no end date.', 'cp' ),
						'default'     => __( 'No End Date', 'cp' ),
					),
					array(
						'attr'        => 'alt_display_text',
						'description' => __( 'Alternate display when there is no end date.', 'cp' ),
						'default'     => __( 'Open-ended', 'cp' ),
					),
					array(
						'attr'        => 'show_alt_display',
						'description' => __( 'If set to "yes" use the alt_display_text. If set to "no" use the "no_date_text".', 'cp' ),
						'default'     => 'no',
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_enr_start'      => array(
				'title'         => __( 'COURSE ENROLLMENT START', 'cp' ),
				'description'   => __( 'This shortcode displays the course enrollment start date.', 'cp' ),
				'usage'         => array(
					'[course_enrollment_start]',
					'[course_enrollment_start label="Signup from" label_tag="em"]',
				),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'date_format',
						'description' => __( 'PHP style date format.', 'cp' ),
						'default'     => __( 'WordPress setting', 'cp' ),
					),
					array(
						'attr'        => 'label',
						'description' => __( 'Label to display for the output. Set label to "" to hide the label completely.', 'cp' ),
					),
					array(
						'attr'        => 'label_tag',
						'description' => __( 'HTML tag (without brackets) to use for the individual labels.', 'cp' ),
						'default'     => 'strong',
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to use after the label.', 'cp' ),
						'default'     => 'colon (:)',
					),
					array(
						'attr'        => 'no_date_text',
						'description' => __( 'Text to display if the course has no end date.', 'cp' ),
						'default'     => __( 'Enroll Anytime', 'cp' ),
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_enr_end'        => array(
				'title'         => __( 'COURSE ENROLLMENT END', 'cp' ),
				'description'   => __( 'This shortcode displays the course enrollment end date.', 'cp' ),
				'usage'         => array(
					'[course_enrollment_end]',
					'[course_enrollment_end label="End" label_delimeter="-"]',
				),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'date_format',
						'description' => __( 'PHP style date format.', 'cp' ),
						'default'     => __( 'WordPress setting', 'cp' ),
					),
					array(
						'attr'        => 'label',
						'description' => __( 'Label to display for the output. Set label to "" to hide the label completely.', 'cp' ),
					),
					array(
						'attr'        => 'label_tag',
						'description' => __( 'HTML tag (without brackets) to use for the individual labels.', 'cp' ),
						'default'     => 'strong',
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to use after the label.', 'cp' ),
						'default'     => 'colon (:)',
					),
					array(
						'attr'        => 'no_date_text',
						'description' => __( 'Text to display if the course has no end date.', 'cp' ),
						'default'     => __( 'Enroll Anytime', 'cp' ),
					),
					array(
						'attr'        => 'show_all_dates',
						'description' => __( 'If "yes" it will display the no_date_text even if there is no date. If "no" then nothing will be displayed.', 'cp' ),
						'default'     => 'no',
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_enr_dates'      => array(
				'title'         => __( 'COURSE ENROLLMENT DATES', 'cp' ),
				'description'   => __( 'This shortcode displays the course enrollment start and end date range. Typically as [course_enrollment_start] - [course_enrollment_end].', 'cp' ),
				'usage'         => array(
					'[course_enrollment_dates]',
					'[course_enrollment_dates no_date_text="No better time than now!"]',
				),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'date_format',
						'description' => __( 'PHP style date format.', 'cp' ),
						'default'     => __( 'WordPress setting', 'cp' ),
					),
					array(
						'attr'        => 'label',
						'description' => __( 'Label to display for the output. Set label to "" to hide the label completely.', 'cp' ),
					),
					array(
						'attr'        => 'label_tag',
						'description' => __( 'HTML tag (without brackets) to use for the individual labels.', 'cp' ),
						'default'     => 'strong',
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to use after the label.', 'cp' ),
						'default'     => 'colon (:)',
					),
					array(
						'attr'        => 'no_date_text',
						'description' => __( 'Text to display if the course has no end date.', 'cp' ),
						'default'     => __( 'Enroll Anytime', 'cp' ),
					),
					array(
						'attr'        => 'alt_display_text',
						'description' => __( 'Alternate display when there is no enrollment start or end dates.', 'cp' ),
						'default'     => __( 'Open-ended', 'cp' ),
					),
					array(
						'attr'        => 'show_alt_display',
						'description' => __( 'If set to "yes" use the alt_display_text. If set to "no" use the "no_date_text".', 'cp' ),
						'default'     => 'no',
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_enr_type'       => array(
				'title'         => __( 'COURSE ENROLLMENT TYPE', 'cp' ),
				'description'   => __( 'This shortcode shows the type of enrollment (manual, prerequisite, passcode or anyone).', 'cp' ),
				'usage'         => array(
					'[course_enrollment_type]',
					'[course_enrollment_type course_id="42"]',
					'[course_enrollment_type passcode_text="Whats the magic word?"]',
				),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'label',
						'description' => __( 'Label to display for the output. Set label to "" to hide the label completely.', 'cp' ),
					),
					array(
						'attr'        => 'label_tag',
						'description' => __( 'HTML tag (without brackets) to use for the individual labels.', 'cp' ),
						'default'     => 'strong',
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to use after the label.', 'cp' ),
						'default'     => 'colon (:)',
					),
					array(
						'attr'        => 'manual_text',
						'description' => __( 'Text to display for manual enrollments.', 'cp' ),
						'default'     => __( 'Students are added by instructors', 'cp' ),
					),
					array(
						'attr'        => 'prerequisite_text',
						'description' => __( 'Text to display when there is a prerequisite. Use %s as placeholder for prerequisite course title.', 'cp' ),
						'default'     => __( 'Students need to complete "%s" first', 'cp' ),
					),
					array(
						'attr'        => 'passcode_text',
						'description' => __( 'Text to display when a passcode is required.', 'cp' ),
						'default'     => __( 'A passcode is required to enroll', 'cp' ),
					),
					array(
						'attr'        => 'anyone_text',
						'description' => __( 'Text to display when anyone can enroll.', 'cp' ),
						'default'     => __( 'Anyone', 'cp' ),
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_time_estimation' => array(
				'title'         => __( 'COURSE TIME ESTIMATION', 'cp' ),
				'description'   => __( 'This shortcode shows the total time estimation based on calculation of unit elements.', 'cp' ),
				'usage'         => array(
					'[course_time_estimation]',
					'[course_time_estimation wrapper="yes"]',
					'[course_time_estimation course_id="42"]',
					'[course_time_estimation course_id="42" wrapper="yes"]',
				),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes to use for further styling.', 'cp' ),
					),
					array(
						'attr'        => 'label',
						'description' => __( 'Label to display for the output. Set label to "" to hide the label completely.', 'cp' ),
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to use after the label.', 'cp' ),
						'default'     => 'colon (:)',
					),
					array(
						'attr'        => 'label_tag',
						'description' => __( 'HTML tag (without brackets) to use for the individual labels.', 'cp' ),
						'default'     => 'strong',
					),
					array(
						'attr'        => 'wrapper',
						'description' => __( 'Wrap inside a div tag (yes|no).', 'cp' ),
					),
				),
			),
			'course_class_size'     => array(
				'title'         => __( 'COURSE CLASS SIZE', 'cp' ),
				'description'   => __( 'This shortcode shows the course class size, limits and remaining seats.', 'cp' ),
				'usage'         => array(
					'[course_class_size]',
					'[course_class_size course_id="42" no_limit_text="The more the merrier"]',
					'[course_class_size remaining_text="Only %d places remaining!"]',
				),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'label',
						'description' => __( 'Label to display for the output. Set label to "" to hide the label completely.', 'cp' ),
					),
					array(
						'attr'        => 'label_tag',
						'description' => __( 'HTML tag (without brackets) to use for the individual labels.', 'cp' ),
						'default'     => 'strong',
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to use after the label.', 'cp' ),
						'default'     => 'colon (:)',
					),
					array(
						'attr'        => 'show_no_limit',
						'description' => __( 'If "yes" it will show the no_limit_text. If "no" then nothing will display for unlimited courses.', 'cp' ),
						'default'     => 'no',
					),
					array(
						'attr'        => 'show_remaining',
						'description' => __( 'If "yes" show remaining_text. If "no" don’t show remaining places.', 'cp' ),
						'default'     => 'yes',
					),
					array(
						'attr'        => 'no_limit_text',
						'description' => __( 'Text to display for unlimited class sizes.', 'cp' ),
						'default'     => __( 'Unlimited', 'cp' ),
					),
					array(
						'attr'        => 'remaining_text',
						'description' => __( 'Text to display for remaining places. Use %d for the remaining number.', 'cp' ),
						'default'     => '(%d places left)',
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_cost'           => array(
				'title'         => __( 'COURSE COST', 'cp' ),
				'description'   => __( 'This shortcode shows the pricing for the course or free for unpaid courses.', 'cp' ),
				'usage'         => array( '[course_cost]', '[course_cost no_cost_text="Free as in beer."]' ),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'label',
						'description' => __( 'Label to display for the output. Set label to "" to hide the label completely.', 'cp' ),
					),
					array(
						'attr'        => 'label_tag',
						'description' => __( 'HTML tag (without brackets) to use for the individual labels.', 'cp' ),
						'default'     => 'strong',
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to use after the label.', 'cp' ),
						'default'     => 'colon (:)',
					),
					array(
						'attr'        => 'no_cost_text',
						'description' => __( 'Text to display for unpaid courses.', 'cp' ),
						'default'     => __( 'FREE', 'cp' ),
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_lang'           => array(
				'title'         => __( 'COURSE LANGUAGE', 'cp' ),
				'description'   => __( 'This shortcode displays the language of the course (if set).', 'cp' ),
				'usage'         => array( '[course_language]', '[course_language label="Delivered in"]' ),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'label',
						'description' => __( 'Label to display for the output. Set label to "" to hide the label completely.', 'cp' ),
					),
					array(
						'attr'        => 'label_tag',
						'description' => __( 'HTML tag (without brackets) to use for the individual labels.', 'cp' ),
						'default'     => 'strong',
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to use after the label.', 'cp' ),
						'default'     => 'colon (:)',
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_list_img'       => array(
				'title'         => __( 'COURSE LIST IMAGE', 'cp' ),
				'description'   => __( 'This shortcode displays the course list image. (See [course_media]).', 'cp' ),
				'usage'         => array( '[course_list_image]', '[course_list_image width="100" height="100"]' ),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'width',
						'description' => __( 'Width of image.', 'cp' ),
						'default'     => __( 'Original width', 'cp' ),
					),
					array(
						'attr'        => 'height',
						'description' => __( 'Height of image.', 'cp' ),
						'default'     => __( 'Original height', 'cp' ),
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_feat_video'     => array(
				'title'         => __( 'COURSE FEATURED VIDEO', 'cp' ),
				'description'   => __( 'This shortcode embeds a video player with the course’s featured video. (See [course_media]).', 'cp' ),
				'usage'         => array(
					'[course_featured_video]',
					'[course_featured_video width="320" height="240"]',
				),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'width',
						'description' => __( 'Width of video player.', 'cp' ),
						'default'     => __( 'Default player width', 'cp' ),
					),
					array(
						'attr'        => 'height',
						'description' => __( 'Height of video player.', 'cp' ),
						'default'     => __( 'Default player height', 'cp' ),
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_thumb'          => array(
				'title'         => __( 'COURSE THUMBNAIL', 'cp' ),
				'description'   => __( 'This shortcode shows the course thumbnail image that is generated from list image. (See [course_media]).', 'cp' ),
				'usage'         => array( '[course_thumbnail]', '[course_thumbnail course_id="22" wrapper="div"]' ),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'wrapper',
						'description' => __( 'The HTML tag to wrap around the thumbnail.', 'cp' ),
						'default'     => 'figure',
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_media'          => array(
				'title'         => __( 'COURSE MEDIA', 'cp' ),
				'description'   => __( 'This shortcode either displays the list image or the featured video (with the other option as possible fallback).', 'cp' ),
				'usage'         => array(
					'[course_media]',
					'[course_media list_page="yes"]',
					'[course_media type="video"]',
					'[course_media priority="image"]',
					'[course_media type="thumbnail"]',
				),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'type',
						'description' => __( 'Use "image" to only display list image if available. Use "video" to only show the video if available. Use "thumbnail" to show the course thumbnail. Use "default" to enable priority mode (see priority attribute).', 'cp' ),
						'default'     => __( 'CoursePress Settings', 'cp' ),
					),
					array(
						'attr'        => 'priority',
						'description' => __( ' Use "image" to try to show the list image first. If not available, then try to use the featured video. Use "video" to try to show the featured video first. If not available, try to use the list image.', 'cp' ),
						'default'     => __( 'CoursePress Settings', 'cp' ),
					),
					array(
						'attr'        => 'list_page',
						'description' => __( 'Use "yes" to use the CoursePress Settings for "Course Listings". Use "no" to use the CoursePress Settings for "Course Details Page".', 'cp' ),
						'default'     => 'no',
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_join_btn'       => array(
				'title'         => __( 'COURSE JOIN BUTTON', 'cp' ),
				'description'   => __( 'This shortcode shows the Join/Signup/Enroll button for the course. What it displays is dependent on the course settings and the user’s status/enrollment. See the attributes for possible button labels.', 'cp' ),
				'usage'         => array(
					'[course_join_button]',
					'[course_join_button course_id="11" course_expired_text="You missed out big time!"]',
				),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'course_full_text',
						'description' => __( 'Text to display if the course is full', 'cp' ),
						'default'     => __( 'Course Full', 'cp' ),
					),
					array(
						'attr'        => 'course_expired_text',
						'description' => __( 'Text to display when the course has expired.', 'cp' ),
						'default'     => __( 'Not available', 'cp' ),
					),
					array(
						'attr'        => 'enrollment_finished_text',
						'description' => __( ' Text to display when enrollments are finished (expired).', 'cp' ),
						'default'     => __( 'Enrollments Finished', 'cp' ),
					),
					array(
						'attr'        => 'enrollment_closed_text',
						'description' => __( 'Text to display when enrollments haven’t started yet.', 'cp' ),
						'default'     => __( 'Enrollments Closed', 'cp' ),
					),
					array(
						'attr'        => 'enroll_text',
						'description' => __( 'Text to display when course is ready for enrollments.', 'cp' ),
						'default'     => __( 'Enroll Now', 'cp' ),
					),
					array(
						'attr'        => 'signup_text',
						'description' => __( 'Text to display when course is ready for enrollments, but the user is not logged in (visitor).', 'cp' ),
						'default'     => __( 'Signup!', 'cp' ),
					),
					array(
						'attr'        => 'details_text',
						'description' => __( 'Text for the button that takes you to the full course page.', 'cp' ),
						'default'     => __( 'Course Details', 'cp' ),
					),
					array(
						'attr'        => 'prerequisite_text',
						'description' => __( 'Text to display if the course has a prerequisite', 'cp' ),
						'default'     => __( 'Pre-requisite Required', 'cp' ),
					),
					array(
						'attr'        => 'passcode_text',
						'description' => __( 'Text to display if the course requires a password.', 'cp' ),
						'default'     => __( 'Passcode Required', 'cp' ),
					),
					array(
						'attr'        => 'not_started_text',
						'description' => __( 'Text to display when a student is enrolled, but the course hasn’t started yet.', 'cp' ),
						'default'     => __( 'Not available', 'cp' ),
					),
					array(
						'attr'        => 'access_text',
						'description' => __( 'Text to display when the user is enrolled and ready to learn.', 'cp' ),
						'default'     => __( 'Start Learning', 'cp' ),
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_action_links'   => array(
				'title'         => __( 'COURSE ACTION LINKS', 'cp' ),
				'description'   => __( 'This shortcode shows "Course Details" and "Withdraw" links to students.', 'cp' ),
				'usage'         => array( '[course_action_links]' ),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_cal'            => array(
				'title'         => __( 'COURSE CALENDAR', 'cp' ),
				'description'   => __( 'This shortcode shows the course calendar (bounds are restricted by course start and end dates). Will always attempt to show today’s date on a calendar first.', 'cp' ),
				'usage'         => array( '[course_calendar]', '[course_calendar pre="< Previous" next="Next >"]' ),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'month',
						'description' => __( 'Month to display as number (e.g. 03 for March).', 'cp' ),
						'default'     => __( 'Today\'s date', 'cp' ),
					),
					array(
						'attr'        => 'year',
						'description' => __( 'Year to display as 4-digit number (e.g. 2014).', 'cp' ),
						'default'     => __( 'Today\'s date', 'cp' ),
					),
					array(
						'attr'        => 'pre',
						'description' => __( 'Text to display for previous month link.', 'cp' ),
						'default'     => __( '« Previous', 'cp' ),
					),
					array(
						'attr'        => 'next',
						'description' => __( 'Text to display for next month link.', 'cp' ),
						'default'     => __( 'Next »', 'cp' ),
					),
				),
			),
			'course_list'           => array(
				'title'         => __( 'COURSE LIST', 'cp' ),
				'description'   => __( 'This shortcode displays a listing of courses. Can be for all courses or restricted by instructors or students (only one or the other, if both specified only students will be used).', 'cp' ),
				'usage'         => array(
					'[course_list]',
					'[course_list instructor="2"]',
					'[course_list student="3"]',
					'[course_list instructor="2,4,5"]',
					'[course_list show="dates,cost" limit="5"]',
				),
				'optional_attr' => array(
					array(
						'attr'        => 'status',
						'description' => __( 'The status of courses to show (uses WordPress status).', 'cp' ),
						'default'     => 'published',
					),
					array(
						'attr'        => 'instructor',
						'description' => __( 'The instructor id to list courses for a specific instructor. Can also specify multiple instructors using commas. (e.g. instructor="1,2,3").', 'cp' ),
						'default'     => __( 'empty', 'cp' ),
					),
					array(
						'attr'        => 'student',
						'description' => __( 'The student id to list courses for a specific student. Can also specify multiple students using commas. (e.g. student="1,2,3").', 'cp' ),
						'default'     => __( 'empty', 'cp' ),
					),
					array(
						'attr'        => 'limit',
						'description' => __( 'Limit the number of courses. Use -1 to show all.', 'cp' ),
						'default'     => '-1',
					),
					array(
						'attr'        => 'order',
						'description' => __( 'Order the courses by title. "ASC" for ascending order. "DESC" for descending order. Empty for WordPress default.', 'cp' ),
						'default'     => 'ASC',
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => 'empty',
					),
				),
			),
			'course_featured'       => array(
				'title'         => __( 'FEATURED COURSE', 'cp' ),
				'description'   => __( 'This shortcode shows a featured course.', 'cp' ),
				'usage'         => array(
					'[course_featured course_id="42"]',
					'[course_featured course_id="11" featured_title="The best we got!"]',
				),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'featured_title',
						'description' => __( 'The title to display for the featured course.', 'cp' ),
						'default'     => __( 'Featured Course', 'cp' ),
					),
					array(
						'attr'        => 'button_title',
						'description' => __( 'Text to display on the call to action button.', 'cp' ),
						'default'     => __( 'Find out more', 'cp' ),
					),
					array(
						'attr'        => 'media_type',
						'description' => __( 'Media type to use for featured course. See [course_media].', 'cp' ),
						'default'     => 'default',
					),
					array(
						'attr'        => 'media_priority',
						'description' => __( 'Media priority to use for featured course. See [course_media].', 'cp' ),
						'default'     => 'video',
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => __( 'empty', 'cp' ),
					),
				),
			),
			'course_structure'      => array(
				'title'         => __( 'COURSE STRUCURE', 'cp' ),
				'description'   => __( 'This shortcode displays a tree view of the course structure.', 'cp' ),
				'usage'         => array(
					'[course_structure]',
					'[course_structure course_id="42" free_text="Gratis!" show_title="no"]',
					'[course_structure show_title="no" label="Curriculum"]',
				),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'free_text',
						'description' => __( 'Text to show for FREE preview items..', 'cp' ),
						'default'     => __( 'Free', 'cp' ),
					),
					array(
						'attr'        => 'show_title',
						'description' => __( 'Show course title in structure, "yes" or "no".', 'cp' ),
						'default'     => 'no',
					),
					array(
						'attr'        => 'show_label',
						'description' => __( 'Show label text as tree heading, "yes" or "no".', 'cp' ),
						'default'     => 'no',
					),
					array(
						'attr'        => 'show_divider',
						'description' => __( 'Show divider between major items in the tree, "yes" or "no".', 'cp' ),
						'default'     => 'yes',
					),
					array(
						'attr'        => 'label',
						'description' => __( 'Label to display for the output. Set label to "" to hide the label completely.', 'cp' ),
					),
					array(
						'attr'        => 'label_tag',
						'description' => __( 'HTML tag (without brackets) to use for the individual labels.', 'cp' ),
						'default'     => 'strong',
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to use after the label.', 'cp' ),
						'default'     => 'colon (:)',
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes for styling.', 'cp' ),
						'default'     => __( 'empty', 'cp' ),
					),
				),
			),
			'course_social_links' => array(
				'title' => __( 'COURSE SOCIAL LINKS', 'cp' ),
				'description' => __( 'Shortcode show social icons for share.', 'cp' ),
				'usage' => array( '[course_social_links course_id="10"]' ),
				'required_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'If outside of the WordPress loop.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr' => 'services',
						'description' => __( 'Select services to show. Available: facebook, twitter, google, email.', 'cp' ),
						'default' => '',
					),
					array(
						'attr' => 'share_title',
						'description' => 'Share title',
						'default' => __( 'Share', 'cp' ),
					),
					array(
						'attr' => 'echo',
						'description' => 'Print shortcode?',
						'default' => 'false',
					),
				),
			),
			'course_signup_page'    => array(
				'title'         => __( 'COURSE SIGNUP/LOGIN PAGE', 'cp' ),
				'description'   => __( 'This shortcode shows a custom login or signup page for front-end user registration and login.', 'cp' ),
				'usage'         => array( '[course_signup][course_signup signup_title="<h1>Signup Now</h1>"]' ),
				'add_info'      => coursepress_alert_message( __( 'This is already part of CoursePress and can be set in CoursePress Settings. Links to default pages can be found in Appearance > Menus > CoursePress', 'cp' ) ),
				'optional_attr' => array(
					array(
						'attr'        => 'failed_login_text',
						'description' => __( 'Text to display when user doesn’t authenticate.', 'cp' ),
						'default'     => __( 'Invalid Login', 'cp' ),
					),
					array(
						'attr'        => 'failed_login_class',
						'description' => __( 'CSS class to use for invalid login.', 'cp' ),
						'default'     => 'red',
					),
					array(
						'attr'        => 'logout_url',
						'description' => __( 'URL to redirect to when user logs out.', 'cp' ),
						'default'     => __( 'Plugin defaults', 'cp' ),
					),
					array(
						'attr'        => 'signup_title',
						'description' => __( 'Title to use for Signup section.', 'cp' ),
						'default'     => '<h3>' . __( 'Signup', 'cp' ) . '</h3>',
					),
					array(
						'attr'        => 'login_title',
						'description' => __( 'Title to use for Login section.', 'cp' ),
						'default'     => '<h3>' . __( 'Login', 'cp' ) . '</h3>',
					),
					array(
						'attr'        => 'signup_url',
						'description' => __( 'URL to redirect to when clicking on "Don\'t have an account? Go to Signup!".', 'cp' ),
						'default'     => __( 'Plugin defaults', 'cp' ),
					),
					array(
						'attr'        => 'login_url',
						'description' => __( 'URL to redirect to when clicking on "Already have an Account?".', 'cp' ),
						'default'     => __( 'Plugin defaults', 'cp' ),
					),
				),
			),
			'instructors_list'      => array(
				'title'         => __( 'INSTRUCTORS LIST', 'cp' ),
				'description'   => __( 'This shortcode displays a list or count of Instructors ( gravatar, name and link to profile page ).', 'cp' ),
				'usage'         => array(
					'[course_instructors]',
					'[course_instructors course_id="5"]',
					'[course_instructors style="list"]',
				),
				'add_info'      => __( 'This is already part of CoursePress and can be set in CoursePress Settings. Links to default pages can be found in Appearance > Menus > CoursePress', 'cp' ),
				'optional_attr' => array(
					array(
						'attr'        => 'course_id',
						'description' => __( 'ID of the course instructors are assign to ( required if use it outside of a loop ).', 'cp' ),
					),
					array(
						'attr'        => 'style',
						'description' => __( 'How to display the instructors.', 'cp' ),
						'options'     => 'block, list, list-flat, count ' . __( '(counts instructors for the course)', 'cp' ),
						'default'     => 'block',
					),
					array(
						'attr'        => 'label',
						'description' => __( 'Label to display for the output. Set label to "" to hide the label completely.', 'cp' ),
					),
					array(
						'attr'        => 'label_plural',
						'description' => __( 'Plural if more than one instructor.', 'cp' ),
						'default'     => __( 'Instructors', 'cp' ),
					),
					array(
						'attr'        => 'label_delimeter',
						'description' => __( 'Symbol to put after label.', 'cp' ),
						'default'     => 'colon (:)',
					),
					array(
						'attr'        => 'label_element',
						'description' => __( 'HTML tag to wrap the label (without brackets, e.g. h3).', 'cp' ),
						'default'     => __( 'empty', 'cp' ),
					),
					array(
						'attr'        => 'link_text',
						'description' => __( 'Text to click to link to full profiles.', 'cp' ),
						'default'     => __( 'View Full Profile', 'cp' ),
					),
					array(
						'attr'        => 'show_label',
						'description' => __( 'Show the label.', 'cp' ),
						'options'     => 'yes, no',
					),
					array(
						'attr'        => 'summary_length',
						'description' => __( 'Length of instructor bio to show when style is "block".', 'cp' ),
						'default'     => 50,
					),
					array(
						'attr'        => 'list_separator',
						'description' => __( 'Symbol to use to separate instructors when styl is "list" or "list-flat".', 'cp' ),
						'default'     => 'comma (,)',
					),
					array(
						'attr'        => 'avatar_size',
						'description' => __( 'Pixel size of the avatars when viewing in block mode.', 'cp' ),
						'default'     => 80,
					),
					array(
						'attr'        => 'default_avatar',
						'description' => __( 'URL to a default image if the user avatar cannot be found.', 'cp' ),
					),
					array(
						'attr'        => 'show_divider',
						'description' => __( 'Put a divider between instructor profiles when style is "block".', 'cp' ),
					),
					array(
						'attr'        => 'link_all',
						'description' => __( 'Make the entire instructor profile a link to the full profile.', 'cp' ),
					),
					array(
						'attr'        => 'class',
						'description' => __( 'Additional CSS classes to use for further styling.', 'cp' ),
					),
				),
			),
			'instructors_avatar'    => array(
				'title'         => __( 'INSTRUCTOR AVATAR', 'cp' ),
				'description'   => __( 'This shortcode displays an instructor’s avatar.', 'cp' ),
				'usage'         => array( '[course_instructor_avatar instructor_id="1"]' ),
				'required_attr' => array(
					array(
						'attr'        => 'instructor_id',
						'description' => __( 'The user id of the instructor.', 'cp' ),
					),
				),
				'optional_attr' => array(
					array(
						'attr'        => 'thumb_size',
						'description' => __( 'Size of avatar thumbnail.', 'cp' ),
						'default'     => 80,
					),
					array(
						'attr'        => 'class',
						'description' => __( 'CSS class to use for the avatar.', 'cp' ),
						'default'     => 'small-circle-profile-image',
					),
				),
			),
			'instructors_prof_url'  => array(
				'title'         => __( 'INSTRUCTOR PROFILE URL', 'cp' ),
				'description'   => __( 'This shortcode returns the URL to the instructor profile.', 'cp' ),
				'usage'         => array( '[instructor_profile_url instructor_id="1"]' ),
				'required_attr' => array(
					array(
						'attr'        => 'instructor_id',
						'description' => __( 'The user id of the instructor.', 'cp' ),
					),
				),
			),
			'student_dash_temp'     => array(
				'title'       => __( 'STUDENT DASHBOARD TEMPLATE', 'cp' ),
				'description' => __( 'This shortcode loads the student dashboard template.', 'cp' ),
				'usage'       => array( '[courses_student_dashboard]' ),
			),
			'student_settings_temp' => array(
				'title'       => __( 'STUDENT SETTINGS TEMPLATE', 'cp' ),
				'description' => __( 'This shortcode loads the student settings template.', 'cp' ),
				'usage'       => array( '[courses_student_settings]' ),
			),
		);
		/**
		 * Sort Optional Attributes
		 */
		foreach ( $details as $key => $data ) {
			if ( isset( $data['optional_attr'] ) ) {
				$attributes = $data['optional_attr'];
				uasort( $attributes, array( $this, 'sort_optional_attr' ) );
				$details[ $key ]['optional_attr'] = $attributes;
			}
		}

		/**
		 * Filter to alter shortcode details array.
		 *
		 * @param array $details Shortcode details.
		 */
		return apply_filters( 'coursepress_shortcodes_details', $details );
	}

	private function sort_optional_attr( $a, $b ) {
		return strnatcmp( $a['attr'], $b['attr'] );
	}

	/**
	 * Format shortcode HTML.
	 *
	 * Escape html entities and add line break for each shortcodes.
	 *
	 * @param array $shortcodes
	 *
	 * @return string
	 */
	public function esc_shortcodes( $shortcodes = array() ) {

		$content = '';

		if ( ! empty( $shortcodes ) ) {
			// Make shortcodes a string.
			$content = implode( '', $shortcodes );
			$content = esc_html( $content );
			// Add line break for each shortcodes.
			$content = str_replace( '][', ']<br/>[', $content );
		}

		return $content;
	}
}
