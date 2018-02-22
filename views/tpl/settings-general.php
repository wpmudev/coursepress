<script type="text/template" id="coursepress-general-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'General', 'cp' ); ?></h2>
	</div>

	<div class="cp-content">
        <?php
        $config = array();

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
                    'value' => coursepress_get_setting( 'general/details_media_type', 'default' ),
                ),
                'details_media_priority' => array(
                    'type' => 'select',
                    'label' => __( 'Priority', 'CoursePress' ),
                    'field_options' => array(
                        'default' => __( 'Default', 'CP_TD' ),
                        'video' => __( 'Featured Video (image fallback)', 'CP_TD' ),
                        'image' => __( 'List Image (video fallback)', 'CP_TD' ),
                    ),
                    'value' => coursepress_get_setting( 'general/details_media_priority', 'default' ),
                ),
            ),
        );

        // Course listings
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
                    'value' => coursepress_get_setting( 'general/listing_media_type', 'default' ),
                ),
                'listing_media_priority' => array(
                    'type' => 'select',
                    'label' => __( 'Priority', 'CoursePress' ),
                    'field_options' => array(
                        'default' => __( 'Default', 'CP_TD' ),
                        'video' => __( 'Featured Video (image fallback)', 'CP_TD' ),
                        'image' => __( 'List Image (video fallback)', 'CP_TD' ),
                    ),
                    'value' => coursepress_get_setting( 'general/listing_media_priority', 'default' ),
                ),
            ),
        );

        // Course images
        $config['course-images'] = array(
            'title' => __( 'Course Images', 'CoursePress' ),
            'description' => __( 'Size for (newly uploaded) course images.', 'CoursePress' ),
            'fields' => array(
                'image_width' => array(
                    'type' => 'number',
                    'label' => __( 'Image Width', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/image_width', '235' ),
                    'config' => array(
                        'min' => 0,
                    ),
                ),
                'image_height' => array(
                    'type' => 'number',
                    'label' => __( 'Image Height', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/image_height', '225' ),
                    'config' => array(
                        'min' => 0,
                    ),
                ),
            ),
        );

        // Course order
        $config['course-order'] = array(
            'title' => __( 'Course Order', 'CoursePress' ),
            'description' => __( 'Order of courses in admin and on front.', 'CoursePress' ),
            'fields' => array(
                'order_by' => array(
                    'type' => 'select',
                    'desc' => __( '', 'CoursePress' ),
                    'label' => __( 'Order by', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/order_by', 'course_start_date' ),
                    'field_options' => array(
                        'post_date' => __( 'Post Date', 'CP_TD' ),
                        'start_date' => __( 'Course start date', 'CP_TD' ),
                        'enrollment_start_date' => __( 'Course enrollment start date', 'CP_TD' ),
                    ),
                ),
                'order_by_direction' => array(
                    'type' => 'select',
                    'label' => __( 'Direction', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/order_by_direction', 'DESC' ),
                    'field_options' => array(
                        'DESC' => __( 'Descending', 'CoursePress' ),
                        'ASC' => __( 'Ascending', 'CoursePress' ),
                    ),
                ),
            ),
        );

        $toggle_input = coursepress_create_html( 'span', array( 'class' => 'cp-toggle-btn' ) );

        // Menu items
        $config['theme-menu-items'] = array(
            'title' => __( 'Theme Menu Items', 'CoursePress' ),
            'fields' => array(
                'show_coursepress_menu' => array(
                    'type' => 'checkbox',
                    'title' => $toggle_input . __( 'Show menu items', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/show_coursepress_menu', 1 ),
                    'desc' => __( 'Attach default CoursePress menu items ( Courses, Student Dashboard, Log Out ) to the <strong>Primary Menu</strong>.<br />Items can also be added from Appearance &gt; Menus and the CoursePress panel.', 'CoursePress' ),
                ),
            ),

        );
        /**
         * Login Form
         */
        $config['general/login-form'] = array(
            'title' => __( 'Login form', 'CoursePress' ),
            'fields' => array(
                'use_custom_login' => array(
                    'type' => 'checkbox',
                    'title' => $toggle_input . __( 'Use Custom Login Form', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/use_custom_login', 1 ),
                    'desc' => __( 'Uses a custom Login Form to keep students on the front-end of your site.', 'CoursePress' ),
                ),
            ),
        );
        /**
         * Privacy
         */
        $config['instructor/show_username'] = array(
            'title' => __( 'Privacy', 'CoursePress' ),
            'fields' => array(
                'instructor_show_username' => array(
                    'type' => 'checkbox',
                    'title' => $toggle_input . __( 'Show instructor username in URL', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'instructor_show_username', 1 ),
                    'desc' => __( 'If checked, instructors username will be shown in the url. Otherwise, hashed (MD5) version will be shown.', 'CoursePress' ),
                ),
            ),
        );
        /**
         * schema
         */
        $config['general/add_structure_data'] = array(
            'title' => __( 'schema.org', 'CoursePress' ),
            'fields' => array(
                'add_structure_data' => array(
                    'type' => 'checkbox',
                    'desc' => __( 'Add structure data to courses.', 'CoursePress' ),
                    'title' => $toggle_input . __( 'Add microdata syntax', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/add_structure_data', 1 ),
                ),
            ),
        );

        /**
         * WordPress Login Redirect
         */
        $config['general/redirect_after_login'] = array(
            'title' => __( 'WP Login Redirect', 'CoursePress' ),
            'fields' => array(
                'redirect_after_login' => array(
                    'type' => 'checkbox',
                    'desc' => __( 'Redirect students to their Dashboard upon login via wp-login form.', 'CoursePress' ),
                    'title' => $toggle_input . __( 'Redirect After Login', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/redirect_after_login', 1 ),
                ),
            ),
        );
        /**
         * Enrollment Restrictions
         */

        $default_enrollment_type = coursepress_get_default_enrollment_type();
        $default_enrollment_type = coursepress_get_setting( 'general/enrollment_type_default', $default_enrollment_type );
        $config['course/enrollment_type_default'] = array(
            'title' => __( 'Enrollment restrictions', 'CoursePress' ),
            'description' => __( 'Select the default limitations on accessing and enrolling in this course.', 'CoursePress' ),
            'fields' => array(
                'enrollment_type_default' => array(
                    'type' => 'select',
                    'title' => __( 'Who can enroll', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/enrollment_type_default', $default_enrollment_type ),
                    'field_options' => coursepress_get_enrollment_types(),
                ),
            ),
        );
        /**
         * Reports
         */
        $pdf = $CoursePress->get_class( 'CoursePress_PDF' );
        $fonts = array();

        if ( $pdf ) {
            $fonts = $pdf->fonts();
        }

        /**
         * Fire to allow additional font to to be use when generating
         * user's PDF report.
         */
        $fonts = apply_filters( 'coursepress_pdf_fonts', $fonts );

        $config['reports/font'] = array(
            'title' => __( 'Reports', 'CoursePress' ),
            'description' => __( 'Select font which will be used in the PDF reports.', 'CoursePress' ),
            'fields' => array(
                'reports_font' => array(
                    'type' => 'select',
                    'title' => __( 'Use this font', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/reports_font', 'helvetica' ),
                    'field_options' => $fonts,
                ),
            ),
        );

        // Social sharing options.
        $social_options = CoursePress_Data_SocialMedia::get_social_sharing_array();
        if ( ! empty( $social_options ) ) {
	        foreach ( $social_options as $social_key => $title ) {
		        $fields[ 'social_sharing.' . $social_key ] = array(
		        	'type'  => 'checkbox',
			        'title' => $toggle_input . $title,
			        'value' => coursepress_get_setting( 'general/social_sharing/' . $social_key, 1 ),
		        );
	        }

	        // Add social sharing options.
	        $config['social_sharing'] = array(
		        'title'  => __( 'Social Sharing', 'CoursePress' ),
		        'fields' => $fields,
	        );
        }

        $option_name = sprintf( 'coursepress_%s', basename( __FILE__, '.php' ) );
        $options = apply_filters( $option_name, $config );
        /**
         * print options
         */
        foreach ( $options as $option_key => $option ) {
            $classes = 'box-inner-content';
            printf( '<div class="cp-box-content cp-box-%s">', esc_attr( $option_key ) );
            if ( ! empty( $option['title'] ) || ! empty( $option['description'] ) ) {
                echo '<div class="box-label-area">';
                if ( ! empty( $option['title'] ) ) {
                    printf(
                        '<h3 class="label">%s</h3>',
                        $option['title']
                    );
                }
                if ( isset( $option['description'] ) ) {
                    printf( '<p class="description">%s</p>', $option['description'] );
                }
                echo '</div>';
            } else {
                $classes .= ' box-inner-full';
            }
            printf( '<div class="%s">', esc_attr( $classes ) );
            /**
             * flex wrapper: semaphore
             */
            $is_flex = false;
            foreach ( $option['fields'] as $key => $data ) {
                /**
                 * flex wrapper: open & close
                 */
                if ( isset( $data['flex'] ) && true === $data['flex'] ) {
                    if ( ! $is_flex ) {
                        echo '<div class="flex">';
                    }
                    $is_flex = true;
                } else if ( true === $is_flex ) {
                    echo '</div>';
                    $is_flex = false;
                }
                $class = isset( $data['wrapper_class'] )? $data['wrapper_class']:'';
                printf(
                    '<div class="cp-box option option-%s option-%s %s">',
                    esc_attr( sanitize_title( $key ) ),
                    esc_attr( $data['type'] ),
                    esc_attr( $class )
                );
                if ( isset( $data['label'] ) ) {
                    printf( '<label class="label">%s</label>', $data['label'] );
                }
                $data['name'] = $key;
                lib3()->html->element( $data );
                echo '</div>';
            }
            /**
             * flex wrapper: close
             */
            if ( $is_flex ) {
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
        }
        ?>
	</div>
</script>
