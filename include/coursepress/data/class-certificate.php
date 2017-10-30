<?php
/**
 * Data access module.
 *
 * @package CoursePress
 */

/**
 * Handles access to the Certificate details.
 */
class CoursePress_Data_Certificate {

	/**
	 * The post-type slug for certificates.
	 *
	 * @var string
	 */
	private static $post_type = 'cp_certificate';
	private static $custom_field_name_for_pdf_file = 'certificate_file';

	/**
	 * If the certificate module is enabled or not.
	 *
	 * @var bool
	 */
	private static $is_enabled = null;

	/**
	 * Certificate ID currently generated.
	 *
	 * @var int
	 **/
	public static $certificate_id = 0;

	/**
	 * Returns details about the custom post-type.
	 *
	 * @since  2.0.0
	 * @return array Details needed to register the post-type.
	 */
	public static function get_format() {
		if ( ! self::is_enabled() ) { return false; }

		return array(
			'post_type' => self::get_post_type_name(),
			'post_args' => array(
				'labels' => array(
					'name' => __( 'Certificates', 'CP_TD' ),
					'singular_name' => __( 'Certificate', 'CP_TD' ),
					'add_new' => __( 'Create New', 'CP_TD' ),
					'add_new_item' => __( 'Create New Certificate', 'CP_TD' ),
					'edit_item' => __( 'Edit Certificate', 'CP_TD' ),
					'edit' => __( 'Edit', 'CP_TD' ),
					'new_item' => __( 'New Certificate', 'CP_TD' ),
					'view_item' => __( 'View Certificate', 'CP_TD' ),
					'search_items' => __( 'Search Certificates', 'CP_TD' ),
					'not_found' => __( 'No Certificates Found', 'CP_TD' ),
					'not_found_in_trash' => __( 'No Certificates found in Trash', 'CP_TD' ),
					'view' => __( 'View Certificate', 'CP_TD' ),
				),
				'public' => false,
				'show_ui' => false,
				'show_in_menu' => false,
				'publicly_queryable' => false,
				'capability_type' => 'certificate',
				'map_meta_cap' => true,
				'query_var' => true,
			),
		);
	}

	/**
	 * Return the post-type slug for certificates.
	 *
	 * @since  2.0.0
	 * @return string The prefixed post-type slug.
	 */
	public static function get_post_type_name() {
		return CoursePress_Data_PostFormat::prefix( self::$post_type );
	}

	/**
	 * Checks if the Basic Certificate module is enabled or not.
	 *
	 * @since  2.0.0
	 * @return bool False means that all functions here are disabled.
	 */
	public static function is_enabled() {
		if ( null === self::$is_enabled ) {
			$flag = CoursePress_Core::get_setting(
				'basic_certificate/enabled',
				true
			);

			self::$is_enabled = cp_is_true( $flag );
		}

		return self::$is_enabled;
	}

	/**
	 * get certificate id
	 *
	 * @since  2.0.0
	 *
	 * @param  int $student_id The WP user-ID.
	 * @param  int $course_id The course-ID that was completed.
	 * @return integer/boolean Returns certificate id or false.
	 */
	public static function get_certificate_id( $student_id, $course_id ) {
		// First check, if the student is already certified for the course.
		$params = array(
			'author' => $student_id,
			'post_parent' => $course_id,
			'post_type' => self::get_post_type_name(),
			'post_status' => 'any',
		);
		$res = get_posts( $params );
		if ( is_array( $res ) && count( $res ) ) {
			return $res[0]->ID;
		}
		return false;
	}

	/**
	 * Generate the certificate, store it in DB and send email to the student.
	 *
	 * @since  2.0.0
	 * @param  int $student_id The WP user-ID.
	 * @param  int $course_id The course-ID that was completed.
	 */
	public static function generate_certificate( $student_id, $course_id ) {
		if ( ! self::is_enabled() ) { return false; }
		$certificate_id = self::get_certificate_id( $student_id, $course_id );
		if ( empty( $certificate_id ) ) {
			$certificate_id = self::create_certificate( $student_id, $course_id );
		}
		// And finally: Send that email :)
		self::send_certificate( $certificate_id );
	}

	/**
	 * Send certificate to student.
	 *
	 * @since 2.0.0
	 * @param  int $student_id The WP user-ID.
	 * @param  int $course_id The course-ID that was completed.
	 * @return bool True on success.
	 */
	public static function send_certificate( $certificate_id ) {
		if ( ! self::is_enabled() ) { return false; }

		$email_args = self::fetch_params( $certificate_id );

		// Hooked to `wp_mail' filter to attached PDF Certificate as attachment.
		self::$certificate_id = $certificate_id;
		add_filter( 'wp_mail', array( __CLASS__, 'attached_pdf_certificate' ) );

		return CoursePress_Helper_Email::send_email(
			CoursePress_Helper_Email::BASIC_CERTIFICATE,
			$email_args
		);
	}

	/**
	 * Get pdf file name - this function is depracated, we should do not use
	 * it - it is easy to get all certificates. It must stay as legacy for
	 * already generated certificates.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Course ID.
	 * @param integer $student_id student ID.
	 * @return full path
	 */
	public static function deprecated_get_pdf_file_name( $course_id, $student_id, $add_extension = true ) {
		$filename = 'certificate-' . $course_id . '-' . $student_id;
		$pdf_file = CoursePress_Helper_PDF::cache_path() . $filename.'.pdf';
		return $pdf_file;
	}

	/**
	 * get pdf file name.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Course ID.
	 * @param integer $student_id student ID.
	 * @return full path
	 */
	public static function get_pdf_file_name( $course_id, $student_id, $basedir = 'include' ) {
		$filename = (defined( 'NONCE_KEY' ) && NONCE_KEY ) ? NONCE_KEY : rand();
		$filename .= $course_id . $student_id;
		$filename = md5( $filename );
		$filename .= '.pdf';
		/**
		 * subdirectory to avoid mass file storage in one directory
		 */
		$dir = substr( $filename, 0, 2 );
		$subdirectory = sprintf( '%s/', $dir );
		CoursePress_Helper_PDF::check_dir( $subdirectory );
		$dir = substr( $filename, 2, 2 );
		$subdirectory .= sprintf( '%s/', $dir );
		CoursePress_Helper_PDF::check_dir( $subdirectory );
		$filename = substr( $filename, 4 );
		/**
		 * add basedir or not?
		 */
		if ( 'no-base-dir' == $basedir ) {
			$pdf_file = $subdirectory . $filename;
		} else {
			$pdf_file = CoursePress_Helper_PDF::cache_path( $subdirectory ) . $filename;
		}
		return $pdf_file;
	}

	public static function attached_pdf_certificate( $mail_atts ) {
		$certificate = get_post( self::$certificate_id );

		if ( is_object( $certificate ) ) {
			$course_id = $certificate->post_parent;
			$student_id = $certificate->post_author;
			$pdf_file = self::get_pdf_file_name( $course_id, $student_id );

			if ( ! is_readable( $pdf_file ) ) {
				if ( self::generate_pdf_certificate( $course_id, $student_id, false ) ) {
					$mail_atts['attachments'] = array( $pdf_file );
				}
			} else {
				$mail_atts['attachments'] = array( $pdf_file );
			}
		}

		// Remove this hook immediately!
		remove_filter( 'wp_mail', array( __CLASS__, 'attached_pdf_certificate' ), 100 );

		return $mail_atts;
	}

	/**
	 * Inserts a new certificate into the DB and returns the created post_id.
	 *
	 * Note that we need to save this twice:
	 * First time the post_content is empty/dummy, then on the second pass we
	 * populate the content, as we need to know the post_id to generate it.
	 *
	 * @since  2.0.0
	 * @return int Post-ID
	 */
	protected static function create_certificate( $student_id, $course_id ) {
		$post = array(
			'post_author' => $student_id,
			'post_parent' => $course_id,
			'post_status' => 'private', // Post is only visible for post_author.
			'post_type' => self::get_post_type_name(),
			'post_content' => 'Processing...', // Intentional value.
			'post_title' => 'Basic Certificate',
			'ping_status' => 'closed',
			'meta_input' => array(
				self::$custom_field_name_for_pdf_file => self::get_pdf_file_name( $course_id, $student_id ),
			),
		);

		// Stage 1: Save data to get post_id!
		$certificate_id = wp_insert_post( $post );

		$post['ID'] = $certificate_id;
		$post['post_content'] = self::get_certificate_content( $certificate_id );

		// Stage 2: Save final certificate data!
		wp_update_post(
			apply_filters( 'coursepress_pre_insert_post', $post )
		);

		/**
		 * generate pdf file here!
		 */
		self::generate_pdf_certificate( $course_id, $student_id, false, $post );

		return $certificate_id;
	}

	/**
	 * Returns an array with all certificate details needed fo send the email
	 * and to process the certificate contents.
	 *
	 * @since  2.0.0
	 * @param  int $certificate_id The post-ID of the certificate.
	 * @return array Array with certificate details.
	 */
	protected static function fetch_params( $certificate_id ) {
		if ( ! self::is_enabled() ) { return array(); }

		$student_id = (int) get_post_field( 'post_author', $certificate_id );
		$course_id = (int) get_post_field( 'post_parent', $certificate_id );
		$completion_date = get_post_field( 'post_date', $certificate_id );

		if ( ! empty( $completion_date ) ) {
			$date_format = get_option( 'date_format' );
			$completion_date = date_i18n( $date_format, strtotime( $completion_date ) );
		}

		if ( empty( $student_id ) ) { return false; }
		if ( empty( $course_id ) ) { return false; }

		$student = get_userdata( $student_id );
		if ( empty( $student ) ) { return false; }

		$course = get_post( $course_id );

		$course_name = $course->post_title;
		$valid_stati = array( 'draft', 'pending', 'auto-draft' );

		if ( in_array( $course->post_status, $valid_stati ) ) {
			$course_address = CoursePress_Core::get_slug( 'course/', true ) . $course->post_name . '/';
		} else {
			$course_address = get_permalink( $course_id );
		}

		$params = array();
		$params['student_id'] = $student_id;
		$params['course_id'] = $course_id;
		$params['email'] = sanitize_email( $student->user_email );
		$params['first_name'] = empty( $student->first_name ) && empty( $student->last_name ) ? $student->display_name : $student->first_name;
		$params['last_name'] = $student->last_name;
		$params['completion_date'] = $completion_date;
		$params['certificate_id'] = $certificate_id;
		$params['course_name'] = $course_name;
		$params['course_address'] = $course_address;
		$params['unit_list'] = CoursePress_Data_Course::get_units_html_list( $course_id );

		return $params;
	}

	/**
	 * Parse the Certificate template and return HTML code to render the
	 * certificate.
	 *
	 * @since  2.0.0
	 * @param  int $certificate_id The post-ID of the certificate.
	 * @return string HTML code to display the certificate.
	 */
	protected static function get_certificate_content( $certificate_id ) {
		$data = self::fetch_params( $certificate_id );

		$content = CoursePress_Core::get_setting(
			'basic_certificate/content'
		);

		// Check if custom certificate is enable
		$course_id = $data['course_id'];
		$is_override = CoursePress_Data_Course::get_setting( $course_id, 'basic_certificate' );

		if ( cp_is_true( $is_override ) ) {
			$content = CoursePress_Data_Course::get_setting( $course_id, 'basic_certificate_layout', $content );
		} else {
			$use_cp_default = CoursePress_Core::get_setting( 'basic_certificate/use_cp_default', false );
			$use_cp_default = cp_is_true( $use_cp_default );
			if ( $use_cp_default ) {
				$content = CoursePress_View_Admin_Setting_BasicCertificate::default_certificate_content();
			}
		}

		$vars = array(
			'FIRST_NAME' => sanitize_text_field( $data['first_name'] ),
			'LAST_NAME' => sanitize_text_field( $data['last_name'] ),
			'COURSE_NAME' => sanitize_text_field( $data['course_name'] ),
			'COMPLETION_DATE' => sanitize_text_field( $data['completion_date'] ),
			'CERTIFICATE_NUMBER' => (int) $data['certificate_id'],
			'UNIT_LIST' => $data['unit_list'],
		);

		/**
		 * Filter variables before applying changes.
		 *
		 * @param (array) $vars.
		 **/
		$vars = apply_filters( 'coursepress_basic_certificate_vars', $vars );

		return CoursePress_Helper_Utility::replace_vars( $content, $vars );
	}

	public static function generate_pdf_certificate( $course_id, $student_id = '', $download = false, $post = array() ) {
		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}
		$post_params = array(
			'post_type' => self::get_post_type_name(),
			'author' => $student_id,
			'post_parent' => $course_id,
			'post_status' => 'any',
		);
		$post = get_posts( $post_params );
		$is_override = CoursePress_Data_Course::get_setting( $course_id, 'basic_certificate' );
		$is_override = cp_is_true( $is_override );

		if ( count( $post ) > 0 || $is_override ) {
			$post = $post[0];
			// We'll replace the existing content to a new one to apply settings changes when applicable.
			$certificate = self::get_certificate_content( $post->ID );
			$settings = CoursePress_Core::get_setting( 'basic_certificate' );
			$background = CoursePress_Helper_Utility::get_array_val( $settings, 'background_image' );
			$orientation = CoursePress_Helper_Utility::get_array_val( $settings, 'orientation' );
			$margins = (array) CoursePress_Helper_Utility::get_array_val( $settings, 'margin' );
			$filename = self::get_pdf_file_name( $course_id, $student_id, 'no-base-dir' );
			$text_color = $text_color = CoursePress_Helper_Utility::convert_hex_color_to_rgb( CoursePress_Core::get_setting( 'basic_certificate/text_color' ), array() );
			$logo = array();
			$logo_image = CoursePress_Helper_Utility::get_array_val( $settings, 'logo_image' );
			if ( ! empty( $logo_image  ) ) {
				$logo_positions = CoursePress_Helper_Utility::get_array_val( $settings, 'logo' );
				$logo  = array(
					'file' => $logo_image,
					'x'	=> $logo_positions['x'],
					'y'	=> $logo_positions['y'],
					'w'	=> $logo_positions['width'],
				);
			}
			/**
			 * Is certificate overrided?
			 */
			if ( $is_override ) {
				$margins = CoursePress_Data_Course::get_setting( $course_id, 'cert_margin', array() );
				$orientation = CoursePress_Data_Course::get_setting( $course_id, 'page_orientation', 'L' );
				$background = CoursePress_Data_Course::get_setting( $course_id, 'certificate_background', '' );
				$text_color = CoursePress_Helper_Utility::convert_hex_color_to_rgb( CoursePress_Data_Course::get_setting( $course_id, 'cert_text_color' ), $text_color );
				$certificate_logo = CoursePress_Data_Course::get_setting( $course_id, 'certificate_logo', '' );
				if ( ! empty( $certificate_logo ) ) {
					$logo_positions = CoursePress_Data_Course::get_setting( $course_id, 'logo_position', '' );
					$logo  = array(
						'file' => $certificate_logo,
						'x'    => $logo_positions['x'],
						'y'    => $logo_positions['y'],
						'w'    => $logo_positions['width'],
					);
				}
			} else {
				/**
				 * Use CP defaults?
				 */
				$use_cp_default = CoursePress_Core::get_setting( 'basic_certificate/use_cp_default', false );
				$use_cp_default = cp_is_true( $use_cp_default );
				if ( $use_cp_default ) {
					/**
					 * Default Background
					 */
					$background = CoursePress::$path.'/asset/img/certificate/certificate-background-p.png';
					/**
					 * default orientation
					 */
					$orientation = 'P';
					/**
					 * CP Logo
					 */
					$logo = array(
						'file' => CoursePress::$path.'/asset/img/certificate/certificate-logo-coursepress.png',
						'x' => 95,
						'y' => 15,
						'w' => 100,
					);
					/**
					 * Default margins
					 */
					$margins = array(
						'left' => 40,
						'right' => 40,
						'top' => 100,
					);
					/**
					 * default color
					 */
					$text_color = array( 90, 90, 90 );
				}
			}


			// Set the content
			$certificate = stripslashes( $certificate );
			$html = '<div class="basic_certificate">'. $certificate . '</div>';
			/**
			 * Allow others to modify the HTML layout.
			 *
			 * @since 2.0
			 *
			 * @param (string) $html			Current HTML layout.
			 * @param (int) $course_id			The course ID the certificate is generated from.
			 * @param (int) $student_id			The student ID the certificate is generated to.
			 **/
			$html = apply_filters( 'coursepress_basic_certificate_html', $html, $course_id, $student_id );
			$certificate_title = apply_filters( 'coursepress_certificate_title', __( 'Certificate of Completion', 'CP_TD' ) );
			$args = array(
				'title' => $certificate_title,
				'orientation' => $orientation,
				'image' => $background,
				'filename' => $filename,
				'format' => 'F',
				'uid' => $post->ID,
				'page_break' => 'no',
				'margins' => apply_filters( 'coursepress_basic_certificate_margins', $margins ),
				'logo' => apply_filters( 'coursepress_basic_certificate_logo', $logo ),
				'text_color' => apply_filters( 'coursepress_basic_certificate_text_color', $text_color ),
			);
			if ( $download ) {
				$args['format'] = 'FI';
				$args['force_download'] = true;
				$args['url'] = true;
			}
			return CoursePress_Helper_PDF::make_pdf( $html, $args );
		}
		return false;
	}

	/**
	 * Certificate link.
	 *
	 * @param (int) $student_id The ID of the student the certification belongs to.
	 * @param (int) $course_id The ID of the completed course.
	 * @return (mixed) A link to pdf certificate or null.
	 */
	public static function get_certificate_link( $student_id, $course_id, $link_title ) {
		$certificate_link = self::get_encoded_url( $course_id, $student_id );

		return sprintf( '<a href="%s">%s</a>', $certificate_link, $link_title );
	}

	public static function background_image( $image_url ) {
		$uploads_dir = wp_upload_dir();
		$basedir = trailingslashit( $uploads_dir['basedir'] );
		$uri = substr( $image_url, strrpos( $image_url, 'uploads/' ) + 8 );
		return $basedir . $uri;
	}

	/**
	 * Get encoded url.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $student_id The ID of the student the certification belongs to.
	 * @param integer $course_id The ID of the completed course.
	 *
	 * @return (mixed) A link to pdf certificate or false.
	 */
	public static function get_encoded_url( $course_id, $student_id ) {
		/**
		 * get from certificate
		 */
		$certificate_id = self::get_certificate_id( $student_id, $course_id );
		if ( ! empty( $certificate_id ) ) {
			$file = get_post_meta( $certificate_id, self::$custom_field_name_for_pdf_file, true );
			$url = self::url_prepare( $file, $course_id, $student_id );
			if ( ! empty( $url ) ) {
				return $url;
			}
		}
		/**
		 * get by default
		 */
		$file = CoursePress_Data_Certificate::get_pdf_file_name( $course_id, $student_id );
		$url = self::url_prepare( $file, $course_id, $student_id );
		if ( ! empty( $url ) ) {
			return $url;
		}
		/**
		 * legacy of not secure certificates.
		 *
		 */
		$file = CoursePress_Data_Certificate::deprecated_get_pdf_file_name( $course_id, $student_id );
		$url = self::url_prepare( $file, $course_id, $student_id );
		if ( ! empty( $url ) ) {
			return $url;
		}
		/**
		 * no file
		 */
		return false;
	}

	/**
	 * Check & prepare PDF url.
	 *
	 * @since 2.0.0
	 *
	 * @param string $file full path to certificate file.
	 * @return string/boolean Returns encoded URL or false if file do not * exists.
	 */
	public static function url_prepare( $file, $course_id, $student_id ) {
		if ( is_file( $file ) && is_readable( $file ) ) {
			$upload_dir = wp_upload_dir();
			$url = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $file );
			$url = CoursePress_Helper_Utility::encode( $url );
			//$url = trailingslashit( home_url() ) . '?fdcpf=' . $url;
			$url = add_query_arg( array( 'fdcpf' => $url, 'c' => $course_id, 'u' => $student_id ), home_url() );
			return $url;
		}
		return CoursePress_View_Front_Course::download_certificate_link( $course_id, $student_id );
	}

	/**
	 * Get course data and create substitutions array.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id course ID.
	 * @param integer $student_id student ID.
	 * @return array Array of substitutions.
	 */
	public static function get_vars( $course_id, $student_id ) {
		$certificate_id = self::get_certificate_id( $student_id, $course_id );
		$date_format = apply_filters( 'coursepress_basic_certificate_date_format', get_option( 'date_format' ) );
		$date = strtotime( get_post_field( 'post_date', $certificate_id ) );
		$vars = array(
			'CERTIFICATE_URL' => self::get_encoded_url( $course_id, $student_id ),
			'COMPLETION_DATE' => date_i18n( $date_format, $date ),
			'CERTIFICATE_NUMBER' => $certificate_id,
			'CERTIFICATE_BUTTON' => '',
		);
		/**
		 * add button
		 */
		if ( $vars['CERTIFICATE_URL'] ) {
			$vars['CERTIFICATE_BUTTON'] = sprintf(
				'<p class="buttons"><a href="%s" class="button blue-button light-blue-button">%s</a></p>',
				esc_url( $vars['CERTIFICATE_URL'] ),
				__( 'Download your certificate', 'CP_TD' )
			);
		}
		return $vars;
	}

	/**
	 * Count number of certified students.
	 *
	 * @since 2.0.0
	 *
	 * @global $wpdb wpdb class object
	 * @return array Array of certified counters.
	 */
	public static function get_certificates_count() {
		$counters = array();
		global $wpdb;
		$sql = $wpdb->prepare( "select post_parent as course_id, count(*) as size from {$wpdb->posts} where post_type = %s group by post_parent", self::get_post_type_name() );
		$results = $wpdb->get_results( $sql );
		foreach ( $results as $one ) {
			$counters[ $one->course_id ] = $one->size;
		}
		return $counters;
	}

	/**
	 * Get certified students by course ID.
	 *
	 * @since 2.0.0
	 *
	 * @global $wpdb wpdb class object
	 * @param integer $course_id Course ID.
	 * @return array Array of certified counters.
	 */
	public static function get_certificated_students_by_course_id( $course_id ) {
		global $wpdb;
		$sql = $wpdb->prepare( "select post_author from {$wpdb->posts} where post_type = %s and post_parent = %d", self::get_post_type_name(), $course_id );
		return $wpdb->get_col( $sql );
	}

	/**
	 * Delete certificate:
	 * - delete certificate PDF file,
	 * - delete custom post with certificate data.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $certificate_id certificate ID.
	 */
	public static function delete_certificate( $certificate_id ) {
		$file = get_post_meta( $certificate_id, self::$custom_field_name_for_pdf_file, true );
		if ( is_file( $file ) && is_writable( $file ) ) {
			unlink( $file );
		}
		wp_delete_post( $certificate_id, true );
	}
}
