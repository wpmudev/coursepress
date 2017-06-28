<script type="text/template" id="coursepress-certificate-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'Certificate', 'cp' ); ?></h2>
	</div>

	<div class="cp-content">
        <?php
        $config = array();
        $toggle_input = coursepress_create_html( 'span', array( 'class' => 'cp-toggle-btn' ) );
        /**
         * Certificate Options
         */
        $config['certificate-options'] = array(
            'title' => __( 'Certificate options', 'CoursePress' ),
            'fields' => array(
                'enabled' => array(
                    'type' => 'checkbox',
                    'title' => $toggle_input . __( 'Enable basic certificate', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'basic_certificate/enabled', true ),
                ),
                'use_cp_default' => array(
                    'type' => 'checkbox',
                    'title' => $toggle_input . __( 'Use custom CoursePress certificate', 'CoursePress' ),
                    'value' => ! coursepress_get_setting( 'basic_certificate/use_cp_default', false ),
                ),
            ),
        );
        /**
         * Custom Certificate
         */
        $config['custom-certificate'] = array(
            'title' => __( 'Custom Certificate', 'CoursePress' ),
            'fields' => array(
                'content' => array(
                    'type' => 'wp_editor',
                    'id' => 'coursepress_settings_basic_certificate_content',
                    'value' => '{{content}}',
                ),
            ),
        );
        /**
         * Background Image
         */
        $config['background_image'] = array(
            'title' => __( 'Background Image', 'CoursePress' ),
            'fields' => array(
                'background_image' => array(
                    'type' => 'text',
                    'class' => 'cp-add-image-input',
                    'id' => 'coursepress-cert-bg',
                    'data-thumbnail' => 20,
                    'data-title' => __( 'Select Certificate Background', 'cp' ),
                    'value' => '{{background_image}}',
                ),
            ),
        );

        $config['content_margin'] = array(
            'title' => __( 'Content Margin', 'CoursePress' ),
            'description' => __( '', 'CoursePress' ),
            'fields' => array(
                'margin.top' => array(
                    'type' => 'number',
                    'label' => __( 'Top', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'basic_certificate/margin/top' ),
                    'flex' => true,
                ),
                'margin.left' => array(
                    'type' => 'number',
                    'label' => __( 'Left', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'basic_certificate/margin/left' ),
                    'flex' => true,
                ),
                'margin.right' => array(
                    'type' => 'number',
                    'label' => __( 'Right', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'basic_certificate/margin/right' ),
                    'flex' => true,
                ),
            ),
        );
        /**
         * Page orientation
         */
        $config['page_orientation'] = array(
            'title' => __( 'Page orientation', 'CoursePress' ),
            'fields' => array(
                'orientation' => array(
                    'type' => 'radio',
                    'value' => coursepress_get_setting( 'basic_certificate/orientation', 'L' ),
                    'field_options' => array(
                        'L' => __( 'Landscape', 'CoursePress' ),
                        'P' => __( 'Portrait', 'CoursePress' ),
                    ),
                ),
            ),
        );
        /**
         * Text Color
         */
        $config['text_color'] = array(
            'title' => __( 'Text Color', 'CoursePress' ),
            'fields' => array(
                'coursepress_settings[basic_certificate][text_color]' => array(
                    'type' => 'wp_color_picker',
                    'value' => coursepress_get_setting( 'basic_certificate/text_color', '#000' ),
                ),
            ),
        );
        /**
         * Preview
         */
        $config['preview'] = array(
            'title' => __( 'Preview', 'CoursePress' ),
            'fields' => array(
                'coursepress_settings[basic_certificate][preview]' => array(
                    'type' => 'button',
                    'value' => coursepress_create_html( 'span', array( 'class' => 'dashicons dashicons-visibility' ), '' )
                        . __( 'Preview Certificate', 'cp' ),
                    'class' => 'cp-dashicons alignright',
                ),
            ),
        );

        /**
         * Fire to allow changing basic certificate options
         *
         * @since 3.0
         */
        $options = apply_filters( 'coursepress_basic_certificate', $config );

        if ( empty( $options ) ) {
            $options = array();
        }

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
                        '<h2 class="label">%s</h2>',
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
                    '<div class="option option-%s option-%s %s">',
                    esc_attr( sanitize_title( $key ) ),
                    esc_attr( $data['type'] ),
                    esc_attr( $class )
                );
                if ( isset( $data['label'] ) ) {
                    printf( '<h3>%s</h3>', $data['label'] );
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
