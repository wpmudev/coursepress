<?php
/**
 * Class CoursePress_Admin_Ajax
 *
 * Handles ajax requests both front and backend.
 *
 * @since 3.0
 * @package CoursePress
 */

class CoursePress_Admin_Ajax extends CoursePress_Utility {
    public function __construct() {
        // Hook to `wp_ajax` action hook to process common ajax request
        add_action( 'wp_ajax_coursepress_request', array( $this, 'process_ajax_request' ) );
        // Hook to get course units for editing
        add_action( 'wp_ajax_coursepress_get_course_units', array( $this, 'get_course_units' ) );
        // Hook to handle file uploads
        add_action( 'wp_ajax_coursepress_upload', array( $this, 'upload_file' ) );
    }

    /**
     * Callback method to process ajax request.
     * There's only 1 ajax request, each request differs and process base on the `action` param set.
     * So if the request is `update_course` it's corresponding method will be `update_course`.
     */
    function process_ajax_request() {
        $request = json_decode( file_get_contents( 'php://input' ) );
        $error = array( 'code' => 'cannot_process', 'message' => __( 'Something went wrong. Please try again.', 'cp' ) );

        if ( isset( $request->_wpnonce ) && wp_verify_nonce( $request->_wpnonce, 'coursepress_nonce' ) ) {
            $action = $request->action;

            // Remove commonly used params
            unset( $request->action, $request->_wpnonce );

            if ( method_exists( $this, $action ) ) {
                $response = call_user_func( array( $this, $action ), $request );

                if ( ! empty( $response['success'] ) )
                    wp_send_json_success( $response );
                else
                    $error = wp_parse_args( $response, $error );
            }
        }

        wp_send_json_error( $error );
    }

    /**
     * Get the course units for editing
     */
    function get_course_units() {
        $course_id = filter_input( INPUT_GET, 'course_id', FILTER_VALIDATE_INT );
        $wpnonce = filter_input( INPUT_GET, '_wpnonce' );
        $error = array( 'error_code' => 'cannot_get_units', 'message' => __( 'Something went wrong. Please try again.', 'cp' ) );

        if ( ! wp_verify_nonce( $wpnonce, 'coursepress_nonce' ) ) {
            wp_send_json_error($error);
        }

        $course = new CoursePress_Course( $course_id );
        $units = $course->get_units( false );

        if ( ! empty( $units ) ) {
            foreach ( $units as $pos => $unit ) {
                if ( ! empty( $course->with_modules ) ) {
                    $modules = $unit->get_modules_with_steps( false );
                    $unit->__set( 'modules', $modules );
                } else {
                    $steps = $unit->get_steps( false );
                    $unit->__set( 'steps', $steps );
                }
                $units[ $pos ] = $unit;
            }
        }

        wp_send_json_success( $units );
    }

    function update_course( $request ) {
        $course_object = array(
            'post_type' => 'course',
            'post_status' => 'pending',
            'post_title' => __( 'Untitled', 'cp' ),
            'post_excerpt' => '',
            'post_name' => '',
            'post_content' => '',
            'ID' => 0,
            'menu_order' => 0,
            'comment_status' => 'closed', // Alway closed comment status
        );

        // Fill course object
        foreach ( $course_object as $key => $value ) {
            if ( isset( $request->{$key} ) ) {
                $course_object[ $key ] = $request->{$key};
            }
        }

        if ( 'auto-draft' == $course_object['post_status'] ) {
            $course_object['post_status'] = 'draft';
        }

        $course_id = wp_update_post( $course_object );

        if ( is_wp_error( $course_id ) ) {
            // Bail early if an error occur
            return array();
        }

        $course_meta = array(
            'course_type' => 'auto-moderated',
            'course_language' => __( 'English', 'cp' ),
            'allow_discussion' => false,
            'allow_workbook' => false,
            'payment_paid_course' => false,
            'listing_image' => '',
            'listing_image_thumbnail_id' => 0,
            'featured_video' => '',
            'enrollment_type' => 'registered',
            'enrollment_passcode' => '',

            'course_view' => 'normal',
            'structure_level' => 'unit',
            'structure_show_empty_units' => false,
            'structure_visible_units' => array(),
            'structure_preview_units' => array(),
            'structure_visible_pages' => array(),
            'structure_preview_pages' => array(),
            'structure_visible_modules' => array(),
            'structure_preview_modules' => array(),
            'course_open_ended' => true,
            'course_start_date' => 0,
            'course_end_date' => '',
            'enrollment_open_ended' => false,
            'enrollment_start_date' => '',
            'enrollment_end_date' => '',
            'class_limited' => '',
            'class_size' => '',

            'pre_completion_title' => __( 'Almost there!', 'CP_TD' ),
            'pre_completion_content' => '',
            'minimum_grade_required' => 100,
            'course_completion_title' => __( 'Congratulations, You Passed!', 'CP_TD' ),
            'course_completion_content' => '',
            'course_failed_title' => __( 'Sorry, you did not pass this course!', 'CP_TD' ),
            'course_failed_content' => '',
            'basic_certificate_layout' => '',
            'basic_certificate' => false,
            'certificate_background' => '',
            'cert_margin' => array(
                'top' => 0,
                'left' => 0,
                'right' => 0,
            ),
            'page_orientation' => 'L',
            'cert_text_color' => '#5a5a5a'
        );

        // Now fill the course meta
        $date_types = array( 'course_start_date', 'course_end_date', 'enrollment_start_date', 'enrollment_end_date' );
        $time_now = current_time( 'timestamp' );

        foreach ( $course_meta as $meta_key => $meta_value ) {
            // The request meta_key is prefix by `meta_`, let find them
            $_meta_key = 'meta_' . $meta_key;

            if ( isset( $request->{$_meta_key} ) )
                $meta_value = $request->{$_meta_key};

            // If the value is an object, make it an array
            if ( is_object( $meta_value ) )
                $value = get_object_vars( $meta_value );

            // We store date_types in microseconds format
            if ( in_array( $meta_key, $date_types ) ) {
                $meta_value = ! empty( $meta_value ) ? strtotime( $meta_value, $time_now ) : 0;

                // We need date types in most queries, store them as seperate meta key
                update_post_meta( $course_id, $meta_key, $meta_value );
            }

            $course_meta[ $meta_key ] = $meta_value;
        }

        // Set post thumbnail ID if not empty
        if ( ! empty( $course_meta['meta_listing_image_thumbnail_id'] ) ) {
            set_post_thumbnail($course_id, $course_meta['meta_listing_image_thumbnail_id']);
        }
        error_log(print_r($course_meta,true));

        // Check course category
        if ( isset( $request->course_category ) ) {
            $category = is_object( $request->course_category ) ? get_object_vars( $request->course_category ) : $request->course_category;
            wp_set_object_terms( $course_id, $category, 'course_category', false );
        }

        update_post_meta( $course_id, 'course_settings', $course_meta );

        /**
         * Fire whenever a course is created or updated.
         *
         * @param int $course_id
         * @param array $course_meta
         */
        do_action( 'coursepress_course_updated', $course_id, $course_meta );

        $course = get_post( $course_id );

        return array( 'success' => true, 'course' => $course );
    }

    function delete_course( $request ) {
        // @todo: Do
    }

    /**
     * Update global settings.
     *
     * @param $request
     * @return array
     */
    function update_settings( $request ) {
        if ( $request ) {
            $request = get_object_vars( $request );
            $request = array_map( array( $this, 'to_array' ), $request );
        }

        //error_log(print_r($request,true));
        coursepress_update_setting( true, $request );

        return array( 'success' => true );
    }

    function activate_marketpress() {
        global $CoursePress_Extension;

        if ( ! $CoursePress_Extension->is_plugin_installed( 'marketpress' ) ) {
            // Install MP then activate
        } elseif ( ! $CoursePress_Extension->is_plugin_active( 'marketpress/marketpress.php' ) ) {
            // Activate plugin
        }
    }

    /**
     * Generate certificate for PREVIEW.
     *
     * @param $request
     * @return array
     */
    function preview_certificate( $request ) {
        global $CoursePress;

        $course_id = '';
        $pdf = $CoursePress->get_class( 'CoursePress_PDF' );

        if ( isset( $request->ID ) ) {
            $course_id = $request->course_id;
            $content = $request->meta_basic_certificate_layout;
            $background = $request->meta_certificate_background;
            $margins = get_object_vars( $request->meta_cert_margin );
            $orientation = $request->meta_page_orientation;
        } else {
            $content = $request->content;
            $background = $request->background_image;
            $margins = get_object_vars( $request->margin );
            $text_color = $request->cert_text_color;
            $orientation = $request->orientation;
        }

        $filename = 'cert-preview-' . $course_id . '.pdf';
        $date_format = apply_filters( 'coursepress_basic_certificate_date_format', get_option( 'date_format' ) );
        $content = apply_filters( 'coursepress_basic_certificate_html', $content, $course_id, get_current_user_id() );

        $vars = array(
            'FIRST_NAME' => __( 'Jon', 'CP_TD' ),
            'LAST_NAME' => __( 'Snow', 'CP_TD' ),
            'COURSE_NAME' => __( 'Example Course Title', 'CP_TD' ),
            'COMPLETION_DATE' => date_i18n( $date_format, $this->date_time_now() ),
            'CERTIFICATE_NUMBER' => uniqid( rand(), true ),
        );
        $content = $this->replace_vars( $content, $vars );
        $text_color = $this->convert_hex_color_to_rgb( $text_color, '#000000' );

        // Set PDF args
        $args = array(
            'title' => __( 'Course Completion Certificate', 'CP_TD' ),
            'orientation' => $orientation,
            'image' => $background,
            'filename' => $filename,
            'format' => 'F',
            'uid' => '12345',
            'margins' => apply_filters( 'coursepress_basic_certificate_margins', $margins ),
            'logo' => apply_filters( 'coursepress_basic_certificate_logo', '' ),
            'text_color' => apply_filters( 'coursepress_basic_certificate_text_color', $text_color ),
        );

        $pdf->make_pdf( $content, $args );

        return array(
            'success' => true,
            'pdf' => $pdf->cache_url() . $filename,
        );
    }

    function upload_file() {
        $request = $_POST;

        if ( ! empty( $_FILES ) && ! empty( $request['_wpnonce'] )
            && wp_verify_nonce( $request['_wpnonce'], 'coursepress_nonce' ) ) {
            $type = $request['type'];

            if ( method_exists( $this, $type ) ) {
                call_user_func( array( $this, $type ), $_FILES, $request );
            }
        }
        wp_send_json_error(true);
    }

    function import_file() {
        $import = wp_import_handle_upload();

        if ( ! empty( $import['id'] ) ) {
            $import_id = $import['id'];

            $filename = $import['file'];
            $courses = file_get_contents( $filename );
            $data = array();

            if ( preg_match( '%.json%', $filename ) ) {
                // Import file is json format!
                // Let's save imported file
                $option_id = 'coursepress_import_' . $import_id;
                $courses = json_decode( $courses );
                $courses = get_object_vars( $courses );
                coursepress_update_option( $option_id, $courses );

                $data['import_id'] = $option_id;
                $data['total_courses'] = count( $courses );

                wp_send_json_success( $data );
            }
        }

        wp_send_json_error();
    }

    function import_course( $request ) {

        $import_id = $request->import_id;
        $total_course = $request->total_courses;

        // Let's import the course one at a time to avoid over caps
        $courses = coursepress_get_option( $import_id );
        $courses = maybe_unserialize( $courses );
        $the_course = array_shift( $courses );

        $importClass = new CoursePress_Import( $the_course, $request );

    }

	/**
	 * Toggle course status.
	 *
	 * @param $request Request data.
	 */
	function course_status_toggle( $request ) {

		$toggled = false;

		// If course id and status is not empty, attempt to change status.
		if ( ! empty( $request->course_id ) && ! empty( $request->status ) ) {
			$toggled = coursepress_change_course_status( $request->course_id, $request->status );
		}

		// If status changed, return success response, else fail.
		if ( $toggled ) {
			$success = array( 'message' => __( 'Course status updated successfully.', 'cp' ) );
			wp_send_json_success( $success );
		} else {
			$error = array( 'error_code' => 'cannot_change_status', 'message' => __( 'Could not update course status.', 'cp' ) );
			wp_send_json_error( $error );
		}
	}
}