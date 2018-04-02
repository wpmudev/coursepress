<script type="text/template" id="coursepress-certificate-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'Certificate', 'cp' ); ?></h2>
	</div>

	<div class="cp-content">
        <?php
		global $CoursePress, $editor_styles;

		$config = array();
		$toggle_input = coursepress_create_html( 'span', array( 'class' => 'cp-toggle-btn' ) );
		/**
		 * Certificate Options
		 */
		$config['certificate-options'] = array(
			'title' => __( 'Certificate options', 'cp' ),
			'fields' => array(
				'enabled' => array(
					'type' => 'checkbox',
					'title' => $toggle_input . __( 'Enable basic certificate', 'cp' ),
					'value' => coursepress_get_setting( 'basic_certificate/enabled', true ),
				),
				'use_cp_default' => array(
					'type' => 'checkbox',
					'title' => $toggle_input . __( 'Use default CoursePress certificate', 'cp' ),
					'value' => coursepress_get_setting( 'basic_certificate/use_cp_default', false ),
				),
			),
		);
		/**
		 * Custom Certificate
		 */
		$content = coursepress_get_setting( 'basic_certificate/content' );
		$certClass = $CoursePress->get_class( 'CoursePress_Certificate' );
		if ( empty( $content ) ) {
			$content = $certClass->default_certificate_content();
		}
		$tokens = $certClass->get_tokens();
		$token_info = sprintf( '<p>%s</p>', __( 'These codes will be replaced with actual data:', 'cp' ) );
		$token_info .= sprintf( '<p><strong>%s</strong></p>', implode( ', ', array_keys( $tokens ) ) );

		$config['custom-certificate'] = array(
			'title'  => __( 'Custom Certificate', 'cp' ),
			'description' => __( 'Use the editor below to create the layout of your certificate. These codes will be replaced with actual data: FIRST_NAME, LAST_NAME, COURSE_NAME, COMPLETION_DATE, CERTIFICATE_NUMBER.', 'cp' ),
			'fields' => array(
				'content' => array(
					'type'          => 'div',
					'wrapper_class' => 'content_certificate_editor',
				),
			),
		);
		/**
		 * Background Image
		 */
		$config['background_image'] = array(
			'title' => __( 'Background Image', 'cp' ),
			'fields' => array(
				'background_image' => array(
					'type' => 'text',
					'class' => 'cp-add-image-input',
					'id' => 'coursepress-cert-bg',
					'value' => coursepress_get_setting( 'basic_certificate/background_image' ),
					'data' => array(
						'title' => __( 'Select Certificate Background', 'cp' ),
						'thumbnail' => coursepress_get_setting( 'basic_certificate/background_image_thumbnail_id' ),
					),
				),
			),
		);

		$config['content_margin'] = array(
			'title' => __( 'Content Margin', 'cp' ),
			'description' => __( '', 'cp' ),
			'fields' => array(
				'margin.top' => array(
					'type' => 'number',
					'label' => __( 'Top', 'cp' ),
					'value' => coursepress_get_setting( 'basic_certificate/margin/top' ),
					'flex' => true,
				),
				'margin.left' => array(
					'type' => 'number',
					'label' => __( 'Left', 'cp' ),
					'value' => coursepress_get_setting( 'basic_certificate/margin/left' ),
					'flex' => true,
				),
				'margin.right' => array(
					'type' => 'number',
					'label' => __( 'Right', 'cp' ),
					'value' => coursepress_get_setting( 'basic_certificate/margin/right' ),
					'flex' => true,
				),
			),
		);
		/**
		 * Logo Image
		 */
		$config['certificate_logo'] = array(
			'title'  => __( 'Logo Image', 'cp' ),
			'fields' => array(
				'certificate_logo' => array(
					'type'  => 'text',
					'class' => 'cp-add-image-input',
					'id'    => 'coursepress-logo-img',
					'value' => coursepress_get_setting( 'basic_certificate/certificate_logo' ),
					'data'  => array(
						'title'     => __( 'Select Logo Image', 'cp' ),
						'thumbnail' => coursepress_get_setting( 'basic_certificate/certificate_logo_thumbnail_id' ),
					),
				),
			),
		);

		$config['certificate_logo_position'] = array(
			'title'       => __( 'Logo Position', 'cp' ),
			'description' => __( '', 'cp' ),
			'fields'      => array(
				'certificate_logo_position.x' => array(
					'type'  => 'number',
					'label' => __( 'X', 'cp' ),
					'value' => coursepress_get_setting( 'basic_certificate/certificate_logo_position/x', 0 ),
					'flex'  => true,
				),
				'certificate_logo_position.y' => array(
					'type'  => 'number',
					'label' => __( 'Y', 'cp' ),
					'value' => coursepress_get_setting( 'basic_certificate/certificate_logo_position/y', 0 ),
					'flex'  => true,
				),
				'certificate_logo_position.w' => array(
					'type'  => 'number',
					'label' => __( 'Width', 'cp' ),
					'value' => coursepress_get_setting( 'basic_certificate/certificate_logo_position/w', 0 ),
					'flex'  => true,
				),
			),
		);
		/**
		 * Page orientation
		 */
		$config['page_orientation'] = array(
			'title' => __( 'Page orientation', 'cp' ),
			'fields' => array(
				'orientation' => array(
					'type' => 'select',
					'value' => coursepress_get_setting( 'basic_certificate/orientation', 'L' ),
					'field_options' => array(
						'L' => $toggle_input . __( 'Landscape', 'cp' ),
						'P' => $toggle_input . __( 'Portrait', 'cp' ),
					),
				),
			),
		);
		/**
		 * Text Color
		 */
		$config['text_color'] = array(
			'title' => __( 'Text Color', 'cp' ),
			'fields' => array(
				'cert_text_color' => array(
					'type' => 'text',
					'value' => coursepress_get_setting( 'basic_certificate/cert_text_color', '#000' ),
				),
			),
		);
		/**
		 * Preview
		 */
		$config['preview'] = array(
			'title' => __( 'Preview', 'cp' ),
			'fields' => array(
				'preview_certificate' => array(
					'type' => 'button',
					'value' => coursepress_create_html( 'span', array( 'class' => 'dashicons dashicons-visibility' ), '' )
						. __( 'Preview Certificate', 'cp' ),
					'class' => 'cp-btn cp-btn-default cp-dashicons alignright',
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
			$option_class = $option_key;

			if ( 'certificate-options' != $option_key ) {
				$option_class .= ' box-cert-settings';
			}

			printf( '<div class="cp-box-content cp-box-%s">', esc_attr( $option_class ) );
			if ( ! empty( $option['title'] ) || ! empty( $option['description'] ) ) {
				echo '<div class="box-label-area">';
				if ( ! empty( $option['title'] ) ) {
					printf(
						'<h3 class="label">%s</h3>',
						$option['title']
					);
				}
				if ( isset( $option['description'] ) ) {
					printf( '<div class="cp-alert cp-alert-info"><p class="description">%s</p></div>', $option['description'] );
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
					printf( '<h3 class="label">%s</h3>', $data['label'] );
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
        <link type="text/css" rel="stylesheet" media="all" href="<?php echo includes_url( '/css/editor.css' ); ?>" />
	</div>
</script>

<script type="text/template" id="coursepress-cert-preview">
    <button type="button" class="cp-btn cp-btn-active"><?php _e( 'Close Preview', 'cp' ); ?></button>
    <h2><?php _e( 'Course Certificate Preview', 'cp' ); ?></h2>
    <iframe id="coursepress-cert-frame" src="{{pdf}}"></iframe>
</script>
