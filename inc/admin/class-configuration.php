<?php
/**
 * Contol configuration pages
 *
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Configuration {

	public function __construct() {

		add_filter( 'coursepress_settings-general', array( $this, 'general' ) );
	}

	public function general( $config ) {
		/**
		 * Course details page
		 */
		$config['course-details-page'] = array(
			'title' => __( 'Course details page', 'CoursePress' ),
			'description' => __( 'Specify Media to use when viewing course details.', 'CoursePress' ),
			'fields' => array(
				'details_media_type' => array(
					'type' => 'select',
					'label' => __( 'Media Type', 'CoursePress' ),
					'field_options' => array(
						'default' => __( 'Priority Mode (default)', 'CP_TD' ),
						'video' => __( 'Featured Video', 'CP_TD' ),
						'image' => __( 'List Image', 'CP_TD' ),
					),
					'value' => coursepress_get_setting( 'course/details_media_type', 'default' ),
				),
				'details_media_priority' => array(
					'type' => 'select',
					'label' => __( 'Priority', 'CoursePress' ),
					'field_options' => array(
						'default' => __( 'Default', 'CP_TD' ),
						'video' => __( 'Featured Video (image fallback)', 'CP_TD' ),
						'image' => __( 'List Image (video fallback)', 'CP_TD' ),
					),
					'value' => coursepress_get_setting( 'course/details_media_priority', 'default' ),
				),
			),
		);
		/**
		 * Course Listings
		 */
		$config['course-listings'] = array(
			'title' => __( 'Course Listings', 'CoursePress' ),
			'description' => __( 'Media to use when viewing course listings (e.g. Courses page or Instructor page).', 'CoursePress' ),
			'fields' => array(
				'listing_media_type' => array(
					'type' => 'select',
					'label' => __( 'Media Type', 'CoursePress' ),
					'field_options' => array(
						'default' => __( 'Priority Mode (default)', 'CP_TD' ),
						'video' => __( 'Featured Video', 'CP_TD' ),
						'image' => __( 'List Image', 'CP_TD' ),
					),
					'value' => coursepress_get_setting( 'course/listing_media_type', 'default' ),
				),
				'listing_media_priority' => array(
					'type' => 'select',
					'label' => __( 'Priority', 'CoursePress' ),
					'field_options' => array(
						'default' => __( 'Default', 'CP_TD' ),
						'video' => __( 'Featured Video (image fallback)', 'CP_TD' ),
						'image' => __( 'List Image (video fallback)', 'CP_TD' ),
					),
					'value' => coursepress_get_setting( 'course/listing_media_priority', 'default' ),
				),
			),
		);
		/**
		 * Course Images
		 */
		$config['course-images'] = array(
			'title' => __( 'Course Images', 'CoursePress' ),
			'description' => __( 'Size for (newly uploaded) course images.', 'CoursePress' ),
			'fields' => array(
				'image_width' => array(
					'type' => 'number',
					'label' => __( 'Image Width', 'CoursePress' ),
					'value' => coursepress_get_setting( 'course/image_width', '235' ),
					'config' => array(
						'min' => 0,
					),
				),
				'image_height' => array(
					'type' => 'number',
					'label' => __( 'Image Height', 'CoursePress' ),
					'value' => coursepress_get_setting( 'course/image_height', '225' ),
					'config' => array(
						'min' => 0,
					),
				),
			),
		);
		/*
            'title' => __( '', 'CoursePress' ),
            'description' => __( '', 'CoursePress' ),
            'fields' => array(
                '' => array(
                    'type' => '',
                    'label' => __( '', 'CoursePress' ),
                    'value' => coursepress_get_setting( '', '' ),
                ),
            ),
		*/
		return $config;
	}
}
