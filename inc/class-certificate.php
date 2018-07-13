<?php
/**
 * Class CoursePress_Certificate
 *
 * @since 2.0
 */
class CoursePress_Certificate extends CoursePress_Utility {
	const CUSTOM_FIELD_NAME_FOR_PDF_FILE = 'certificate_file';

	protected $post_type = 'cp_certificate';
	static $certificate_id = 0;

	public function __construct() {
		// Set default certificate content
		add_filter( 'coursepress_default_settings', array( $this, 'default_certificate_settings' ) );
	}

	/**
	 * delete certificate file
	 *
	 * @since 3.0.0
	 *
	 * @param integer $student_id The WP user-ID.
	 * @param integer $course_id The course-ID that was completed.
	 */
	public function delete_certificate( $student_id, $course_id ) {
		/**
		 * delete certificate file
		 */
		$filename = $this->get_pdf_file_name( $course_id, $student_id );
		if ( file_exists( $filename ) ) {
			unlink( $filename );
		}
		/**
		 * delete certificate entry
		 */
		$filename = basename( $filename );
		$post = $this->get_certificate_by_filename( $filename );
		if ( ! is_wp_error( $post ) ) {
			wp_delete_post( $post->ID, true );
		}
	}

	public function default_certificate_settings( $settings ) {
		$settings['basic_certificate'] = array(
			'enabled' => true,
			'use_cp_default' => false,
			'content' => $this->default_certificate_content(),
			'margin' => array(
				'top' => 0,
				'left' => 0,
				'right' => 0,
			),
			'certificate_logo_position' => array(
				'x' => 0,
				'y' => 0,
				'w' => 0,
			),
			'background_image' => '',
			'certificate_logo' => '',
			'orientation' => 'L',
		);
		return $settings;
	}

	public function is_enabled() {
		return coursepress_get_setting( 'basic_certificate/enabled', true );
	}

	public function get_tokens() {
		$tokens = array(
			'FIRST_NAME' => '',
			'LAST_NAME' => '',
			'COURSE_NAME' => '',
			'COMPLETION_DATE' => '',
			'CERTIFICATE_NUMBER' => '',
			'UNIT_LIST' => '',
		);
		/**
		 * Fire to allow changes on certificate tokens.
		 *
		 * @since 2.0
		 */
		$tokens = apply_filters( 'coursepress_basic_certificate_vars', $tokens );
		return $tokens;
	}

	/**
	 * Inserts a new certificate into the DB and returns the created post_id.
	 *
	 * Note that we need to save this twice:
	 * First time the post_content is empty/dummy, then on the second pass we
	 * populate the content, as we need to know the post_id to generate it.
	 *
	 * @since  2.0.0
	 * @param int $student_id
	 * @param int $course_id
	 * @return int Post-ID
	 */
	protected function create_certificate( $student_id, $course_id ) {
		$post = array(
			'post_author' => $student_id,
			'post_parent' => $course_id,
			'post_status' => 'private', // Post is only visible for post_author.
			'post_type' => $this->post_type,
			'post_content' => 'Processing...', // Intentional value.
			'post_title' => 'Basic Certificate',
			'ping_status' => 'closed',
			'meta_input' => array(
				self::CUSTOM_FIELD_NAME_FOR_PDF_FILE => $this->get_pdf_file_name( $course_id, $student_id ),
			),
		);
		// Stage 1: Save data to get post_id!
		$certificate_id = wp_insert_post( $post );
		$post['ID'] = $certificate_id;
		$post['post_content'] = $this->get_certificate_content( $certificate_id );
		// Stage 2: Save final certificate data!
		wp_update_post(
			apply_filters( 'coursepress_pre_insert_post', $post )
		);
		/**
		 * generate pdf file here!
		 */
		$this->generate_pdf_certificate( $course_id, $student_id, false, $post );
		return $certificate_id;
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
	public function get_certificate_id( $student_id, $course_id ) {
		// First check, if the student is already certified for the course.
		$params = array(
			'author' => $student_id,
			'post_parent' => $course_id,
			'post_type' => $this->post_type,
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
	 *
	 * @return int|null
	 */
	public function generate_certificate( $student_id, $course_id ) {
		if ( ! $this->is_enabled() ) {
			return false;
		}
		$certificate_id = $this->get_certificate_id( $student_id, $course_id );
		if ( empty( $certificate_id ) ) {
			$certificate_id = $this->create_certificate( $student_id, $course_id );
		}
		// And finally: Send that email :)
		$this->send_certificate( $certificate_id );
		return true;
	}

	/**
	 * Send certificate to student.
	 *
	 * @since 2.0.0
	 * @param  int $student_id The WP user-ID.
	 * @param  int $course_id The course-ID that was completed.
	 * @return bool True on success.
	 */
	public function send_certificate( $certificate_id ) {
		if ( ! $this->is_enabled() ) {
			return false;
		}
		// Hooked to `wp_mail' filter to attached PDF Certificate as attachment.
		self::$certificate_id = $certificate_id;
		add_filter( 'wp_mail', array( __CLASS__, 'attached_pdf_certificate' ) );
		/**
		 * @TODO: Send email
		 *
		return CoursePress_Helper_Email::send_email(
			CoursePress_Helper_Email::BASIC_CERTIFICATE,
			$email_args
		);
		 **/
		return true;
	}

	/**
	 * Returns an array with all certificate details needed fo send the email
	 * and to process the certificate contents.
	 *
	 * @since  2.0.0
	 * @param  int $certificate_id The post-ID of the certificate.
	 * @return bool|array Array with certificate details.
	 */
	protected function fetch_params( $certificate_id ) {
		if ( ! $this->is_enabled() ) {
			return array();
		}
		$student_id = (int) get_post_field( 'post_author', $certificate_id );
		$course_id = (int) get_post_field( 'post_parent', $certificate_id );
		$completion_date = get_post_field( 'post_date', $certificate_id );
		if ( ! empty( $completion_date ) ) {
			$date_format = get_option( 'date_format' );
			$completion_date = date_i18n( $date_format, strtotime( $completion_date ) );
		}
		if ( empty( $student_id ) || empty( $course_id ) ) {
			return false;
		}
		$student = get_userdata( $student_id );
		if ( empty( $student ) ) {
			return false;
		}
		$course = get_post( $course_id );
		$course_name = $course->post_title;
		$valid_stati = array( 'draft', 'pending', 'auto-draft' );
		if ( in_array( $course->post_status, $valid_stati ) ) {
			$course_address = coursepress_get_course_permalink( $course_id );
		} else {
			$course_address = get_permalink( $course_id );
		}
		$params = array();
		$params['student_id'] = $student_id;
		$params['course_id'] = $course_id;
		$params['email'] = sanitize_email( $student->user_email );
		$params['display_name'] = $student->display_name;
		$params['first_name'] = $student->first_name;
		$params['last_name'] = $student->last_name;
		$params['completion_date'] = $completion_date;
		$params['certificate_id'] = $certificate_id;
		$params['course_name'] = $course_name;
		$params['course_address'] = $course_address;
		$params['unit_list'] = '';//CoursePress_Data_Course::get_units_html_list( $course_id );
		return $params;
	}

	public function default_certificate_content() {
		$msg = __(
			'<h2>%1$s %2$s</h2>
			has successfully completed the course

			<h3>%3$s</h3>

			<h4>Date: %4$s</h4>
			<small>Certificate no.: %5$s</small>', 'cp'
		);
		$default_certification_content = sprintf(
			$msg,
			'FIRST_NAME',
			'LAST_NAME',
			'COURSE_NAME',
			'COMPLETION_DATE',
			'CERTIFICATE_NUMBER',
			'UNIT_LIST'
		);
		return $default_certification_content;
	}

	/**
	 * Parse the Certificate template and return HTML code to render the
	 * certificate.
	 *
	 * @since  2.0.0
	 * @param  int $certificate_id The post-ID of the certificate.
	 * @return string HTML code to display the certificate.
	 */
	protected function get_certificate_content( $certificate_id ) {
		$data = $this->fetch_params( $certificate_id );
		$content = coursepress_get_setting( 'basic_certificate/content' );
		// Check if custom certificate is enable
		$course_id = $data['course_id'];
		$course = coursepress_get_course( $course_id );
		$is_override = $course->__get( 'basic_certificate' );
		if ( ! empty( $is_override ) ) {
			$content = $course->__get( 'basic_certificate_layout' );
		} else {
			$use_cp_default = coursepress_get_setting( 'basic_certificate/use_cp_default', false );
			if ( ! empty( $use_cp_default ) ) {
				$content = $this->default_certificate_content();
			}
		}
		$vars = array(
			'FIRST_NAME' => sanitize_text_field( ( $data['first_name'] || $data['last_name'] ) ? $data['first_name'] : $data['display_name'] ),
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
		return $this->replace_vars( $content, $vars );
	}

	/**
	 * get pdf file name.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Course ID.
	 * @param integer $student_id student ID.
	 * @return mixed
	 */
	public function get_pdf_file_name( $course_id, $student_id, $basedir = 'include' ) {
		global $cp_coursepress;
		$pdf = $cp_coursepress->get_class( 'CoursePress_PDF' );
		$filename = (defined( 'NONCE_KEY' ) && NONCE_KEY) ? NONCE_KEY : rand();
		$filename .= $course_id . $student_id;
		$filename = md5( $filename );
		$filename .= '.pdf';
		/**
		 * subdirectory to avoid mass file storage in one directory
		 */
		$dir = substr( $filename, 0, 2 );
		$subdirectory = sprintf( '%s/', $dir );
		$this->check_dir( $subdirectory );
		$dir = substr( $filename, 2, 2 );
		$subdirectory .= sprintf( '%s/', $dir );
	    $this->check_dir( $subdirectory );
		$filename = substr( $filename, 4 );
		/**
		 * add basedir or not?
		 */
		if ( 'no-base-dir' === $basedir ) {
			$pdf_file = $subdirectory . $filename;
		} else {
			$pdf_file = $pdf->cache_path( $subdirectory ) . $filename;
		}
		return $pdf_file;
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
	 *
	 * @return full path
	 */
	public function deprecated_get_pdf_file_name( $course_id, $student_id ) {

		global $cp_coursepress;
		$pdf = $cp_coursepress->get_class( 'CoursePress_PDF' );

		$filename = 'certificate-' . $course_id . '-' . $student_id;
		$pdf_file = $pdf->cache_path() . $filename.'.pdf';

		return $pdf_file;
	}

	public function get_pdf_file_url( $course_id, $student_id ) {
		$pdf_file = $this->get_pdf_file_name( $course_id, $student_id );
		return str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $pdf_file );
	}

	/**
	 * @param $course_id
	 * @param string $student_id
	 * @param bool $download
	 * @return array|bool|string
	 */
	public function generate_pdf_certificate( $course_id, $student_id = '', $download = false ) {
		global $cp_coursepress;
		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}
		$post_params = array(
			'post_type' => $this->post_type,
			'author' => $student_id,
			'post_parent' => $course_id,
			'post_status' => 'any',
		);
		$post = get_posts( $post_params );
		$course = coursepress_get_course( $course_id );
		if ( is_wp_error( $course ) ) {
			return false;
		}
		$is_override = $course->__get( 'basic_certificate' );
		$is_override = ! empty( $is_override );
		if ( count( $post ) > 0 || $is_override ) {
			$pdf = $cp_coursepress->get_class( 'CoursePress_PDF' );
			$post = $post[0];
			// We'll replace the existing content to a new one to apply settings changes when applicable.
			$certificate = $this->get_certificate_content( $post->ID );
			$settings = coursepress_get_setting( 'basic_certificate' );
			$background = coursepress_get_array_val( $settings, 'background_image', '' );
			$orientation = coursepress_get_array_val( $settings, 'orientation', 'L' );
			$margins = (array) coursepress_get_array_val( $settings, 'margin' );
			$filename = $this->get_pdf_file_name( $course_id, $student_id, 'no-base-dir' );
			/**
			 * certificate logo
			 */
			$logo = array(
				'file' => coursepress_get_setting( 'certificate_logo', false ),
				'w' => coursepress_get_setting( 'certificate_logo_position-w', 100 ),
				'x' => coursepress_get_setting( 'certificate_logo_position-x',  95 ),
				'y' => coursepress_get_setting( 'certificate_logo_position-y',  15 ),
			);
			/**
			 * text
			 */
			$text_color = coursepress_get_setting( 'basic_certificate/text_color', array() );
			$text_color = coursepress_convert_hex_color_to_rgb( $text_color, array() );
			/**
			 * Is certificate overrided?
			 */
			if ( $is_override ) {
				$margins = (array) $course->__get( 'cert_margin', array() );
				$orientation = $course->__get( 'page_orientation' );
				$background = $course->__get( 'certificate_background' );
				$logo_image = $course->__get( 'certificate_logo' );
				$logo_position = (array) $course->__get( 'certificate_logo_position' );
				$text_color = $course->__get( 'cert_text_color' );
				$text_color = coursepress_convert_hex_color_to_rgb( $text_color, array() );
				$logo = array_merge(
					array( 'file' => $logo_image ),
					$logo_position
				);
			} else {
				/**
				 * Use CP defaults?
				 */
				$use_cp_default = coursepress_get_setting( 'basic_certificate/use_cp_default', false );
				if ( ! empty( $use_cp_default ) ) {
					/**
					 * Default Background
					 */
					$background = $cp_coursepress->plugin_path .'assets/images/certificate/certificate-background-p.png';
					/**
					 * default orientation
					 */
					$orientation = 'P';
					/**
					 * CP Logo
					 */
					$logo = array(
						'file' => $cp_coursepress->plugin_path . 'assets/images/certificate/certificate-logo-coursepress.png',
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
			$html = coursepress_create_html( 'div', array( 'class' => 'basic_certificate' ), $certificate );
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
			$certificate_title = apply_filters( 'coursepress_certificate_title', __( 'Certificate of Completion', 'cp' ) );
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
			return $pdf->make_pdf( $html, $args );
		}
		return false;
	}

	public function pdf_notice() {
		global $cp_coursepress;
		$pdf = $cp_coursepress->get_class( 'CoursePress_PDF' );
		$cache_path = $pdf->cache_path();
		$message = coursepress_create_html(
			'p',
			array(),
			sprintf( __( 'CoursePress cannot generate PDF because directory is not writable: %s', 'cp' ), $cache_path )
		);
		echo coursepress_create_html( 'div', array( 'class' => 'notice notice-error' ), $message );
	}

	/**
	 * check and create subdirectory.
	 *
	 * @since 2.0.4
	 *
	 * @param string $subdirectory subdirectory
	 */
	public static function check_dir( $subdirectory ) {
		$uploads_dir = wp_upload_dir();
		$cache_path = apply_filters( 'coursepress_pdf_cache_path', trailingslashit( $uploads_dir['basedir'] ) . 'pdf-cache/' );
		$check_directory = $cache_path . $subdirectory;
		if ( ! is_dir( $check_directory ) ) {
			mkdir( $check_directory );
		}
	}

	/**
	 * Get encoded url.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $student_id The ID of the student the certification belongs to.
	 * @param integer $course_id The ID of the completed course.
	 *
	 * @return mixed A link to pdf certificate or false.
	 */
	public function get_encoded_url( $course_id, $student_id ) {

		// Get from certificate
		$certificate_id = $this->get_certificate_id( $student_id, $course_id );
		if ( ! empty( $certificate_id ) ) {
			$file = get_post_meta( $certificate_id, self::CUSTOM_FIELD_NAME_FOR_PDF_FILE, true );
			$url = $this->url_prepare( $file, $course_id, $student_id );
			if ( ! empty( $url ) ) {
				return $url;
			}
		}

		// Get by default
		$file = $this->get_pdf_file_name( $course_id, $student_id );
		$url = $this->url_prepare( $file, $course_id, $student_id );
		if ( ! empty( $url ) ) {
			return $url;
		}

		// legacy of not secure certificates.
		$file = $this->deprecated_get_pdf_file_name( $course_id, $student_id );
		$url = $this->url_prepare( $file, $course_id, $student_id );
		if ( ! empty( $url ) ) {
			return $url;
		}

		// No file.
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
	public function url_prepare( $file, $course_id, $student_id ) {
		if ( is_file( $file ) && is_readable( $file ) ) {
			$upload_dir = wp_upload_dir();
			$url = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $file );
			$url = $this->encode( $url );
			$url = add_query_arg( array( 'fdcpf' => $url, 'c' => $course_id, 'u' => $student_id ), home_url() );
			return $url;
		}
		return $this->get_pdf_file_url( $course_id, $student_id );
	}

	/**
	 * Get certificate post entry by file name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $filename Certificate File name.
	 */
	private function get_certificate_by_filename( $filename ) {
		$post = null;
		$args = array(
			'post_type' => $this->post_type,
			'post_status' => 'any',
			'meta_query' => array(
				'filename' => array(
					'key' => self::CUSTOM_FIELD_NAME_FOR_PDF_FILE,
					'value' => $filename,
					'compare' => 'LIKE',
				),
			),
		);
		$query = new WP_Query( $args );
		if ( isset( $query->posts ) ) {
			$length = count( $query->posts );
			if ( 1 === $length ) {
				return array_shift( $query->posts );
			}
		}
		return new WP_Error();
	}

	/**
	 * Try to regenerate missing certificate file
	 *
	 * @since 3.0.0
	 *
	 * @param string $filename Certificate file name.
	 */
	public function try_to_regenerate( $filename ) {
		$post = $this->get_certificate_by_filename( $filename );
		if ( is_wp_error( $post ) ) {
			return false;
		}
		$course_id = $post->post_parent;
		$student_id = $post->post_author;
		$filename = $this->get_pdf_file_name( $course_id, $student_id );
		if ( ! is_file( $filename ) ) {
			$this->generate_pdf_certificate( $course_id, $student_id, false );
		}
		return is_file( $filename );
	}
}
