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
        $config['course-details-page'] = array(
            'title' => __( 'Course details page', 'CoursePress' ),
            'description' => __( 'Specify Media to use when viewing course details.', 'CoursePress' ),
            'fields' => array(
                'details_media_type' =>  array (
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
        return $config;
    }

}
