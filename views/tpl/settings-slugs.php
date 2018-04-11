<script type="text/template" id="coursepress-slugs-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'URL Slugs', 'cp' ); ?></h2>
    </div>

	<div class="cp-content">
        <div class="cp-box-content">
            <p class="description"><?php _e( 'A slug is a few words that describe a post or a page. Slugs are usually a URL friendly version of the post title ( which has been automatically generated by WordPress ), but a slug can be anything you like. Slugs are meant to be used with permalinks as they help describe what the content at the URL is. Post slug substitutes the "<b>%posttitle%</b>" placeholder in a custom permalink structure.', 'cp' ); ?></p>
            <div class="cp-alert cp-alert-info">
                <b>SITEROOT</b> = <?php echo home_url(); ?>/
            </div>
        </div>
        <?php
		$config = array();
		$slugs = array(
			'courses' => coursepress_get_setting( 'slugs/course', 'courses' ),
			'course_category' => coursepress_get_setting( 'slugs/category', 'course_category' ),
			'units' => coursepress_get_setting( 'slugs/units', 'units' ),
			'step' => coursepress_get_setting( 'slugs/step', 'step' ),
			'discussions_new' => coursepress_get_setting( 'slugs/discussions_new', 'add_new_discussion' ),
			'discussions' => coursepress_get_setting( 'slugs/discussions', 'discussion' ),
			'notifications' => coursepress_get_setting( 'slugs/notifications', 'notifications' ),
			'workbook' => coursepress_get_setting( 'slugs/workbook', 'workbook' ),
			'enrollment' => coursepress_get_setting( 'slugs/enrollment', 'enrollment_process' ),
			'instructor_profile' => coursepress_get_setting( 'slugs/instructor_profile', 'instructor' ),
			'login' => coursepress_get_setting( 'slugs/login', 'student-login' ),
			'student_dashboard' => coursepress_get_setting( 'slugs/student_dashboard', 'student_dashboard' ),
			'student_settings' => coursepress_get_setting( 'slugs/student_settings', 'student_settings' ),
		);
		/**
		 * Pages
		 */
		$pages = array(
			'enrollment' => coursepress_get_setting( 'slugs/pages/enrollment', 0 ),
			'login' => coursepress_get_setting( 'slugs/pages/login', 0 ),
			'signup' => coursepress_get_setting( 'slugs/pages/signup', 0 ),
			'student_dashboard' => coursepress_get_setting( 'slugs/pages/student_dashboard', 0 ),
			'student_settings' => coursepress_get_setting( 'slugs/pages/student_settings', 0 ),
		);

		/**
		 * Course details page
		 */
		$config['course'] = array(
			'title' => __( 'Courses', 'cp' ),
			'fields' => array(
				'course' => array(
					'type' => 'text',
					'value' => $slugs['courses'],
					'class' => 'large-text',
					'title' => '<strong>SITEROOT/</strong>',
					'after' => sprintf(
						__( 'URL Preview: %1$s/%2$s', 'cp' ),
						home_url(),
						$slugs['courses']
					),
				),
			),
		);
		$config['course_category'] = array(
			'title' => __( 'Course category', 'cp' ),
			'fields' => array(
				'category' => array(
					'type' => 'text',
					'value' => $slugs['course_category'],
					'class' => 'large-text',
					'title' => sprintf(
						'<strong>SITEROOT/</strong>%s/',
						$slugs['courses']
					),
					'after' => sprintf(
						__( 'URL Preview: %1$s/%2$s/%3$s', 'cp' ),
						home_url(),
						$slugs['courses'],
						$slugs['course_category']
					),
				),
			),
		);
		$config['units'] = array(
			'title' => __( 'Units', 'cp' ),
			'fields' => array(
				'units' => array(
					'type' => 'text',
					'value' => $slugs['units'],
					'class' => 'large-text',
					'title' => sprintf(
						'<strong>SITEROOT/</strong>%s/%s/%s/%s',
						$slugs['courses'],
						'%coursetitle%',
						$slugs['units'],
						'%unittitle%'
					),
				),
			),
		);
		$config['step'] = array(
			'title' => __( 'Step', 'cp' ),
			'fields' => array(
				'step' => array(
					'type' => 'text',
					'value' => $slugs['step'],
					'class' => 'large-text',
					'title' => sprintf(
						'<strong>SITEROOT/</strong>%s/%s/%s/%s/<span class="target">%s</span>/%s',
						$slugs['courses'],
						'%coursetitle%',
						$slugs['units'],
						'%unittitle%',
						$slugs['step'],
						'%steptitle%'
					),
				),
				'desc' => array(
					'type' => 'html_text',
					'value' => __( 'This string will be use only for no module courses', 'cp' ),
					'class' => 'description',
				),
			),
		);
		$config['notifications'] = array(
			'title' => __( 'Course notifications', 'cp' ),
			'fields' => array(
				'notifications' => array(
					'type' => 'text',
					'value' => $slugs['notifications'],
					'class' => 'large-text',
					'title' => sprintf(
						'<strong>SITEROOT/</strong>%s/%s/',
						$slugs['courses'],
						'%posttitle%'
					),
				),
			),
		);
		$config['discussions'] = array(
			'title' => __( 'Course discussions', 'cp' ),
			'fields' => array(
				'discussions' => array(
					'type' => 'text',
					'value' => $slugs['discussions'],
					'class' => 'large-text',
					'title' => sprintf(
						'<strong>SITEROOT/</strong>%s/%s/',
						$slugs['courses'],
						'%posttitle%'
					),
				),
			),
		);
		$config['discussions_new'] = array(
			'title' => __( 'Course new discussion', 'cp' ),
			'fields' => array(
				'discussions_new' => array(
					'type' => 'text',
					'value' => $slugs['discussions_new'],
					'class' => 'large-text',
					'title' => sprintf(
						'<strong>SITEROOT/</strong>%s/%s/%s/',
						$slugs['courses'],
						'%posttitle%',
						$slugs['discussions']
					),
				),
			),
		);
		$config['workbook'] = array(
			'title' => __( 'Course workbook', 'cp' ),
			'fields' => array(
				'workbook' => array(
					'type' => 'text',
					'value' => $slugs['workbook'],
					'class' => 'large-text',
					'title' => sprintf(
						'<strong>SITEROOT/</strong>%s/%s/',
						$slugs['courses'],
						'%posttitle%'
					),
				),
			),
		);

		$config['instructor_profile'] = array(
			'title' => __( 'Instructor profile', 'cp' ),
			'fields' => array(
				'instructor_profile' => array(
					'type' => 'text',
					'value' => $slugs['instructor_profile'],
					'class' => 'large-text',
					'title' => '<strong>SITEROOT/</strong>',
				),
			),
		);

		$pages_options = array( __( 'use virtual page', 'cp' ) );
		$pages_list = get_pages( array( 'hierarchical' => false ) );

		if ( count( $pages_list ) > 0 ) {
			foreach ( $pages_list as $page_item ) {
				$pages_options[ $page_item->ID ] = apply_filters( 'the_title', $page_item->post_title );
			}
		}

		$config['login'] = array(
			'title' => __( 'Login', 'cp' ),
			'fields' => array(
				'login' => array(
					'type' => 'text',
					'value' => $slugs['login'],
					'class' => 'large-text',
					'title' => '<strong>SITEROOT/</strong>',
					'wrapper_class' => 'half',
				),
				'pages.login' => array(
					'type' => 'select',
					'value' => $pages['login'],
					'field_options' => $pages_options,
					'title' => '&nbsp;',
					'wrapper_class' => 'half half-last',
				),
				'desc' => array(
					'type' => 'html_text',
					'value' => sprintf(
						__( 'Select page where you have %s shortcode or any other set of shortcodes. Please note that slug for the page set above will not be used if "Use virtual page" is not selected.', 'cp' ),
						'<b>[cp_pages page="student_login"]</b>'
					),
					'class' => 'description',
				),
			),
		);
		$config['dashboard'] = array(
			'title' => __( 'Student dashboard', 'cp' ),
			'fields' => array(
				'student_dashboard' => array(
					'type' => 'text',
					'value' => $slugs['student_dashboard'],
					'class' => 'large-text',
					'title' => '<strong>SITEROOT/</strong>',
					'wrapper_class' => 'half',
				),
				'pages.student_dashboard' => array(
					'type' => 'select',
					'value' => $pages['student_dashboard'],
					'field_options' => $pages_options,
					'title' => '&nbsp;',
					'wrapper_class' => 'half half-last',
				),
				'desc' => array(
					'type' => 'html_text',
					'value' => sprintf(
						__( 'Select page where you have %s shortcode or any other set of shortcodes. Please note that slug for the page set above will not be used if "Use virtual page" is not selected.', 'cp' ),
						'<b>[cp_pages page="student_dashboard"]</b>'
					),
					'class' => 'description',
				),
			),
		);
		$config['settings'] = array(
			'title' => __( 'Student settings', 'cp' ),
			'fields' => array(
				'student_settings' => array(
					'type' => 'text',
					'value' => $slugs['student_settings'],
					'class' => 'large-text',
					'title' => '<strong>SITEROOT/</strong>',
					'wrapper_class' => 'half',
				),
				'pages.student_settings' => array(
					'type' => 'select',
					'value' => $pages['student_settings'],
					'field_options' => $pages_options,
					'title' => '&nbsp;',
					'wrapper_class' => 'half half-last',
				),
				'desc' => array(
					'type' => 'html_text',
					'value' => sprintf(
						__( 'Select page where you have %s shortcode or any other set of shortcodes. Please note that slug for the page set above will not be used if "Use virtual page" is not selected.', 'cp' ),
						'<b>[cp_pages page="student_settings"]</b>'
					),
					'class' => 'description',
				),
			),
		);

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
				} elseif ( true === $is_flex ) {
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
