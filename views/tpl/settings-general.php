<script type="text/template" id="coursepress-general-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php esc_html_e( 'General', 'cp' ); ?></h2>
	</div>

	<div class="cp-content">
        <?php
        $config = array();

        $config['course-details-page'] = array(
            'title' => esc_html__( 'Course details page', 'cp' ),
            'description' => esc_html__( 'Specify Media to use when viewing course details.', 'cp' ),
            'fields' => array(
                'details_media_type' => array(
                    'type' => 'select',
                    'label' => esc_html__( 'Media Type', 'cp' ),
                    'field_options' => array(
                        'default' => esc_html__( 'Priority Mode (default)', 'cp' ),
                        'video' => esc_html__( 'Featured Video', 'cp' ),
                        'image' => esc_html__( 'List Image', 'cp' ),
                    ),
                    'value' => coursepress_get_setting( 'general/details_media_type', 'default' ),
                ),
                'details_media_priority' => array(
                    'type' => 'select',
                    'label' => esc_html__( 'Priority', 'cp' ),
                    'field_options' => array(
                        'default' => esc_html__( 'Default', 'cp' ),
                        'video' => esc_html__( 'Featured Video (image fallback)', 'cp' ),
                        'image' => esc_html__( 'List Image (video fallback)', 'cp' ),
                    ),
                    'value' => coursepress_get_setting( 'general/details_media_priority', 'default' ),
                ),
            ),
        );

        // Course listings
        $config['course-listings'] = array(
            'title' => esc_html__( 'Course Listings', 'cp' ),
            'description' => esc_html__( 'Media to use when viewing course listings (e.g. Courses page or Instructor page).', 'cp' ),
            'fields' => array(
                'listing_media_type' => array(
                    'type' => 'select',
                    'label' => esc_html__( 'Media Type', 'cp' ),
                    'field_options' => array(
                        'default' => esc_html__( 'Priority Mode (default)', 'cp' ),
                        'video' => esc_html__( 'Featured Video', 'cp' ),
                        'image' => esc_html__( 'List Image', 'cp' ),
                    ),
                    'value' => coursepress_get_setting( 'general/listing_media_type', 'default' ),
                ),
                'listing_media_priority' => array(
                    'type' => 'select',
                    'label' => esc_html__( 'Priority', 'cp' ),
                    'field_options' => array(
                        'default' => esc_html__( 'Default', 'cp' ),
                        'video' => esc_html__( 'Featured Video (image fallback)', 'cp' ),
                        'image' => esc_html__( 'List Image (video fallback)', 'cp' ),
                    ),
                    'value' => coursepress_get_setting( 'general/listing_media_priority', 'default' ),
                ),
            ),
        );

        // Course images
        $config['course-images'] = array(
            'title' => esc_html__( 'Course Images', 'cp' ),
            'description' => esc_html__( 'Size for (newly uploaded) course images.', 'cp' ),
            'fields' => array(
                'image_width' => array(
                    'type' => 'number',
                    'label' => esc_html__( 'Image Width', 'cp' ),
                    'value' => coursepress_get_setting( 'general/image_width', '235' ),
                    'config' => array(
                        'min' => 0,
                    ),
                ),
                'image_height' => array(
                    'type' => 'number',
                    'label' => esc_html__( 'Image Height', 'cp' ),
                    'value' => coursepress_get_setting( 'general/image_height', '225' ),
                    'config' => array(
                        'min' => 0,
                    ),
                ),
            ),
        );

        // Course order
        $config['course-order'] = array(
            'title' => esc_html__( 'Course Order', 'cp' ),
            'description' => esc_html__( 'Order of courses in admin and on front.', 'cp' ),
            'fields' => array(
                'order_by' => array(
                    'type' => 'select',
                    'desc' => esc_html__( ' ', 'cp' ),
                    'label' => esc_html__( 'Order by', 'cp' ),
                    'value' => coursepress_get_setting( 'general/order_by', 'course_start_date' ),
                    'field_options' => array(
                        'post_date' => esc_html__( 'Post Date', 'cp' ),
                        'start_date' => esc_html__( 'Course start date', 'cp' ),
                        'enrollment_start_date' => esc_html__( 'Course enrollment start date', 'cp' ),
                    ),
                ),
                'order_by_direction' => array(
                    'type' => 'select',
                    'label' => esc_html__( 'Direction', 'cp' ),
                    'value' => coursepress_get_setting( 'general/order_by_direction', 'DESC' ),
                    'field_options' => array(
                        'DESC' => esc_html__( 'Descending', 'cp' ),
                        'ASC' => esc_html__( 'Ascending', 'cp' ),
                    ),
                ),
            ),
        );

        $toggle_input = coursepress_create_html( 'span', array( 'class' => 'cp-toggle-btn' ) );

        // Menu items
        $config['theme-menu-items'] = array(
            'title' => esc_html__( 'Theme Menu Items', 'cp' ),
            'fields' => array(
                'show_coursepress_menu' => array(
                    'type' => 'checkbox',
                    'title' => $toggle_input . esc_html__( 'Show menu items', 'cp' ),
                    'value' => coursepress_get_setting( 'general/show_coursepress_menu', 1 ),
                    'desc' => esc_html__( 'Attach default CoursePress menu items ( Courses, Student Dashboard, Log Out ) to the <strong>Primary Menu</strong>.<br />Items can also be added from Appearance &gt; Menus and the CoursePress panel.', 'cp' ),
                ),
            ),

        );
        /**
         * Login Form
         */
        $config['general/login-form'] = array(
            'title' => esc_html__( 'Login form', 'cp' ),
            'fields' => array(
                'use_custom_login' => array(
                    'type' => 'checkbox',
                    'title' => $toggle_input . esc_html__( 'Use Custom Login Form', 'cp' ),
                    'value' => coursepress_get_setting( 'general/use_custom_login', 1 ),
                    'desc' => esc_html__( 'Uses a custom Login Form to keep students on the front-end of your site.', 'cp' ),
                ),
            ),
        );
        /**
         * Privacy
         */
        $config['instructor/show_username'] = array(
            'title' => esc_html__( 'Privacy', 'cp' ),
            'fields' => array(
                'instructor_show_username' => array(
                    'type' => 'checkbox',
                    'title' => $toggle_input . esc_html__( 'Show instructor username in URL', 'cp' ),
                    'value' => coursepress_get_setting( 'instructor_show_username', 1 ),
                    'desc' => esc_html__( 'If checked, instructors username will be shown in the url. Otherwise, hashed (MD5) version will be shown.', 'cp' ),
                ),
            ),
        );
        /**
         * schema
         */
        $config['general/add_structure_data'] = array(
            'title' => esc_html__( 'schema.org', 'cp' ),
            'fields' => array(
                'add_structure_data' => array(
                    'type' => 'checkbox',
                    'desc' => esc_html__( 'Add structure data to courses.', 'cp' ),
                    'title' => $toggle_input . esc_html__( 'Add microdata syntax', 'cp' ),
                    'value' => coursepress_get_setting( 'general/add_structure_data', 1 ),
                ),
            ),
        );

        /**
         * WordPress Login Redirect
         */
        $config['general/redirect_after_login'] = array(
            'title' => esc_html__( 'WP Login Redirect', 'cp' ),
            'fields' => array(
                'redirect_after_login' => array(
                    'type' => 'checkbox',
                    'desc' => esc_html__( 'Redirect students to their Dashboard upon login via wp-login form.', 'cp' ),
                    'title' => $toggle_input . esc_html__( 'Redirect After Login', 'cp' ),
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
            'title' => esc_html__( 'Enrollment restrictions', 'cp' ),
            'description' => esc_html__( 'Select the default limitations on accessing and enrolling in this course.', 'cp' ),
            'fields' => array(
                'enrollment_type_default' => array(
                    'type' => 'select',
                    'title' => esc_html__( 'Who can enroll', 'cp' ),
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
            'title' => esc_html__( 'Reports', 'cp' ),
            'description' => esc_html__( 'Select font which will be used in the PDF reports.', 'cp' ),
            'fields' => array(
                'reports_font' => array(
                    'type' => 'select',
                    'title' => esc_html__( 'Use this font', 'cp' ),
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
		        'title'  => esc_html__( 'Social Sharing', 'cp' ),
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
                        esc_html( $option['title'] )
                    );
                }
                if ( isset( $option['description'] ) ) {
                    printf( '<p class="description">%s</p>', esc_html( $option['description'] ) );
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
                } elseif ( true === $is_flex ) {
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
                    printf( '<label class="label">%s</label>', esc_html( $data['label'] ) );
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
