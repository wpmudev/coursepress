<?php

class CoursePress_Helper_Utility {

	// Used by the array uasort() callbacks
	private static $sort_key;
	private static $image_url; // used to get attachment ID.
	public static $is_singular;
	public static $embed_args = array();

	/**
	 * Stores the pagination position of current request.
	 *
	 * @var int
	 */
	protected static $cp_pagination = null;

	/**
	 * Stores the currently displayed post-ID.
	 *
	 * @var int
	 */
	protected static $cp_post_id = null;

	public static function init() {
		add_action( 'wp_ajax_attachment_model', array( __CLASS__, 'attachment_model_ajax' ) );
		add_action( 'init', array( __CLASS__, 'force_download_file_request' ), 1 );
		add_action( 'init', array( __CLASS__, 'open_course_zip_object' ), 1 );
		//add_action( 'admin_init', array( __CLASS__, 'course_admin_filters' ), 1 );
		add_filter( 'upload_mimes', array( __CLASS__, 'enable_extended_upload' ) );
		add_action( 'parse_request', array( __CLASS__, 'course_signup' ), 1 );
	}

	public static function enable_extended_upload( $mime_types = array() ) {
		$add_the_filter = false;

		$valid_pages = array( 'coursepress_settings', 'coursepress_course', 'coursepress' );
		$matches = false;

		if ( ! isset( $_GET['page'] ) || ! in_array( $_GET['page'], $valid_pages ) ) {
			$add_the_filter = false;
		}

		preg_match( '/(page=)(\w*)/', wp_get_referer(), $matches );
		if ( isset( $matches ) && isset( $matches[2] ) ) {
			$page_ref = $matches[2];

			if ( in_array( $page_ref, $valid_pages ) ) {
				$add_the_filter = true;
			}
		}

		if ( ! $add_the_filter ) {
			return $mime_types;
		}

		// The MIME types listed here will be allowed in the media library.
		// You can add as many MIME types as you want.
		$mime_types['gz'] = 'application/x-gzip';
		$mime_types['zip'] = 'application/zip';

		return $mime_types;
	}

	// Sort multi-dimension arrays on 'order' value.
	public static function sort_on_key( $array, $sort_key, $sort_asc = true ) {
		self::$sort_key = $sort_key;

		if ( ! $sort_asc ) {
			uasort( $array, array( __CLASS__, 'sort_desc' ) );
		} else {
			uasort( $array, array( __CLASS__, 'sort_asc' ) );
		}

		return $array;
	}

	// uasort callback to sort ascending.
	public static function sort_asc( $x, $y ) {
		if ( $x[ self::$sort_key ] == $y[ self::$sort_key ] ) {
			return 0;
		} else if ( $x[ self::$sort_key ] < $y[ self::$sort_key ] ) {
			return - 1;
		} else {
			return 1;
		}
	}

	// uasort callback to sort descending.
	public static function sort_desc( $x, $y ) {
		if ( $x[ self::$sort_key ] == $y[ self::$sort_key ] ) {
			return 0;
		} else if ( $x[ self::$sort_key ] > $y[ self::$sort_key ] ) {
			return - 1;
		} else {
			return 1;
		}
	}

	// Sort multi-dimension arrays on 'order' value.
	public static function sort_on_object_key( $array, $sort_key, $sort_asc = true ) {
		self::$sort_key = $sort_key;

		if ( ! $sort_asc ) {
			uasort( $array, array( __CLASS__, 'sort_obj_desc' ) );
		} else {
			uasort( $array, array( __CLASS__, 'sort_obj_asc' ) );
		}

		return $array;
	}

	// uasort callback to sort ascending.
	public static function sort_obj_asc( $x, $y ) {
		if ( $x->{self::$sort_key} == $y->{self::$sort_key} ) {
			return 0;
		} else if ( $x->{self::$sort_key} < $y->{self::$sort_key} ) {
			return - 1;
		} else {
			return 1;
		}
	}

	// uasort callback to sort descending.
	public static function sort_obj_desc( $x, $y ) {
		if ( $x->{self::$sort_key} == $y->{self::$sort_key} ) {
			return 0;
		} else if ( $x->{self::$sort_key} > $y->{self::$sort_key} ) {
			return - 1;
		} else {
			return 1;
		}
	}

	/**
	 * Set array value based on path.
	 *
	 * @since 2.0.5
	 *
	 * @param mixed $a Array or nothing - current values set.
	 * @param string/array $path Path as a string or an array.
	 * @param mixed $value Value to set.
	 *
	 * @return array Settings array.
	 */
	public static function set_array_value( $a, $path_input, $value ) {
		$path = array();
		if ( is_array( $path_input ) ) {
			$path = $path_input;
		} else {
			$path = explode( '/', $path_input );
		}
		$key = array_shift( $path );
		if ( empty( $path ) ) {
			if ( empty( $key ) ) {
				$key = count( $a );
			}
			$a[ $key ] = $value;
			return $a;
		}
		if ( ! isset( $a[ $key ] ) || ! is_array( $a[ $key ] ) ) {
			$a[ $key ] = array();
		}
		$a[ $key ] = self::set_array_value( $a[ $key ], $path, $value );
		return $a;
	}

	// get array value based on path.
	public static function get_array_val( $a, $path_input ) {
		$path = array();
		if ( is_array( $path_input ) ) {
			$path = $path_input;
		} else {
			$path = explode( '/', $path_input );
		}
		foreach ( $path as $k ) {
			if ( isset( $a[ $k ] ) ) {
				$a = $a[ $k ];
			} else {
				return null;
			}
		}
		return $a;
	}

	/**
	 * Unset array value based on path.
	 *
	 * @since 2.0.5
	 *
	 * @param mixed $a Array or nothing - current values set.
	 * @param string/array $path Path as a string or an array.
	 *
	 * @return array Settings array.
	 */
	public static function unset_array_value( $a, $path_input ) {
		$path = array();
		if ( is_array( $path_input ) ) {
			$path = $path_input;
		} else {
			$path = explode( '/', $path_input );
		}
		$key = array_shift( $path );
		if ( empty( $path ) ) {
			if ( empty( $key ) ) {
				$key = count( $a );
			}
			unset( $a[ $key ] );
			return $a;
		}
		if ( ! isset( $a[ $key ] ) || ! is_array( $a[ $key ] ) ) {
			$a[ $key ] = array();
		}
		$a[ $key ] = self::unset_array_value( $a[ $key ], $path );
		return $a;
	}

	/**
	 * set array value based on path.
	 *
	 * @deprecated 2.0.5 Use set_array_value()
	 * @see set_array_value()
	 */
	public static function set_array_val( &$a, $path_input, $value ) {
		CoursePress_Helper_Legacy::deprecated_function( __CLASS__.'::'.__FUNCTION__, '2.0.5', 'CoursePress_Helper_Utility::set_array_value()' );
		$path = array();
		if ( is_array( $path_input ) ) {
			$path = $path_input;
		} else {
			$path = explode( '/', $path_input );
		}
		$key = array_pop( $path );
		foreach ( $path as $k ) {
			if ( ! isset( $a[ $k ] ) || ! is_array( $a[ $k ] ) ) {
				$a[ $k ] = array();
			}
			$a = &$a[ $k ];
		}
		$a[ $key ? $key : count( $a ) ] = $value;
	}

	/**
	 * unset array value based on path.
	 *
	 * @deprecated 2.0.5 Use unset_array_value()
	 * @see unset_array_value()
	 */
	public static function unset_array_val( &$a, $path_input ) {
		CoursePress_Helper_Legacy::deprecated_function( __CLASS__.'::'.__FUNCTION__, '2.0.5', 'CoursePress_Helper_Utility::unset_array_value()' );
		$path = array();
		if ( is_array( $path_input ) ) {
			$path = $path_input;
		} else {
			$path = explode( '/', $path_input );
		}
		$key = array_pop( $path );
		foreach ( $path as $k ) {
			if ( ! isset( $a[ $k ] ) ) {
				$a[ $k ] = array();
			}
			$a = &$a[ $k ];
		}
		unset( $a[ $key ? $key : count( $a ) ] );
	}

	public static function object_to_array( $object ) {
		if ( is_object( $object ) ) {
			$object = get_object_vars( $object );
		}
		if ( is_array( $object ) ) {
			return array_map( array( __CLASS__, 'object_to_array' ), $object );
		} else {
			return $object;
		}
		return array();
	}

	public static function array_to_object( $array ) {
		if ( is_array( $array ) ) {
			return (object) array_map( array( __CLASS__, 'array_to_object' ), $array );
		} else {
			return $array;
		}
	}


	// Does a recursive array merge without creating 'mini' arrays as array_merge_recursive() does.
	public static function merge_distinct( array &$array1, array &$array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) && isset( $merged [ $key ] ) && is_array( $merged [ $key ] ) ) {
				$merged [ $key ] = self::merge_distinct( $merged [ $key ], $value );
			} else {
				$merged [ $key ] = $value;
			}
		}

		return $merged;
	}

	public static function delete_user_meta_by_key( $meta_key ) {
		global $wpdb;

		$legacy = delete_metadata( 'user', 0, $meta_key, '', true );

		$meta_key = $wpdb->prefix . $meta_key;

		if ( $legacy || delete_metadata( 'user', 0, $meta_key, '', true ) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function get_id( $user ) {
		if ( ! is_object( $user ) ) {
			return $user;
		} else {
			return $user->ID;
		}
	}

	public static function sanitize_recursive( $mixed ) {
		if ( is_array( $mixed ) ) {
			foreach ( $mixed as $key => $value ) {
				$mixed[ $key ] = self::sanitize_recursive( $value );
			}
		} else {
			if ( is_string( $mixed ) ) {
				return self::filter_content( $mixed );
			}
		}

		return $mixed;
	}

	// Deals with legacy 'on' / 'off' values for checkboxes
	public static function checked( $value, $compare = true, $echo = false ) {
		$checked = false;
		if ( true === $compare ) {
			$checked = cp_is_true( $value ) ? 'checked="checked"' : '';
		} else {
			$checked = $compare === $value ? 'checked="checked"' : '';
		}

		if ( $echo ) {
			echo $checked;
		} else {
			return $checked;
		}
	}

	// Get appropriate AJAX URL
	public static function get_ajax_url() {
		return set_url_scheme( admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Return the URL of current request.
	 *
	 * @since  2.0.0
	 * @param  bool $host_only If set to true only protocol + host is returned.
	 * @return string URL.
	 */
	public static function get_current_url( $host_only = false ) {
		static $_cur_url = null;

		if ( null === $_cur_url ) {
			$_cur_url = 'http';
			if ( isset( $_SERVER['HTTPS'] ) && 'on' == $_SERVER['HTTPS'] ) {
				$_cur_url .= 's';
			}
			$_cur_url .= '://';
			if ( isset( $_SERVER['SERVER_PORT'] ) && '80' != $_SERVER['SERVER_PORT'] ) {
				$_cur_url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
			} else {
				$_cur_url .= $_SERVER['SERVER_NAME'];
			}
		}

		if ( $host_only ) {
			return $_cur_url;
		} else {
			return $_cur_url . $_SERVER['REQUEST_URI'];
		}
	}

	// Allowed image extensions
	public static function get_image_extensions() {
		return apply_filters(
			'coursepress_allowed_image_extensions',
			array(
				'jpg',
				'jpeg',
				'jpe',
				'gif',
				'png',
				'bmp',
				'tif',
				'tiff',
				'ico',
			)
		);
	}

	/**
	 * Filter HTML string and remove forbidden tags and attributes.
	 * This function uses the wp_kses() function to sanitize the content.
	 *
	 * @since  2.0.0
	 * @param  string $content Raw HTML code.
	 * @param  bool   $no_html Return sanitized HTML (false) or plain text (true)?
	 * @return string Sanitized content.
	 */
	public static function filter_content( $content, $no_html = false ) {
		$kses_rules = apply_filters(
			'coursepress_allowed_post_tags',
			wp_kses_allowed_html( 'post' )
		);

		if ( $no_html ) {
			if ( is_array( $content ) ) {
				foreach ( $content as $content_key => $content_value ) {
					$content[ $content_key ] = wp_filter_nohtml_kses( $content_value );
				}
			} else {
				$content = wp_filter_nohtml_kses( $content );
			}
		} else {
			if ( current_user_can( 'unfiltered_html' ) ) {
				$content = $content;
			} else {
				if ( is_array( $content ) ) {
					foreach ( $content as $content_key => $content_value ) {
						$content[ $content_key ] = wp_kses( $content_value,  $kses_rules );
					}
				} else {
					$content = wp_kses( $content, $kses_rules );
				}
			}
		}

		return $content;
	}

	/**
	 * Returns true if the WP installation allows user registration.
	 *
	 * @since  1.0.0
	 * @return bool If CoursePress allows user signup.
	 */
	public static function users_can_register() {
		static $_allow_register = null;

		if ( null === $_allow_register ) {
			if ( is_multisite() ) {
				$_allow_register = users_can_register_signup_filter();
			} else {
				$_allow_register = get_option( 'users_can_register' );
			}

			/**
			 * Filter the return value to allow users to manually enable
			 * CoursePress registration only.
			 *
			 * @since 2.0.0
			 * @var bool $_allow_register
			 */
			$_allow_register = apply_filters(
				'coursepress_users_can_register',
				$_allow_register
			);
		}

		return $_allow_register;
	}

	public static function is_payment_supported() {
		// Hook for payment plugins to turn to 'true'.
		// Attempt to give Course ID to allow per course filtering.
		return apply_filters(
			'coursepress_payment_supported',
			false,
			CoursePress_Data_Course::last_course_id()
		);
	}

	public static function send_bb_json( $response ) {
		if ( ! headers_sent() ) {
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		}

		echo json_encode( $response );
		exit;
	}

	public static function attachment_model_ajax() {
		$json_data = array();

		switch ( $_REQUEST['task'] ) {
			case 'get':
				$json_data[] = self::attachment_from_url(
					sanitize_text_field( $_REQUEST['url'] )
				);
				break;
		}

		if ( ! empty( $json_data ) ) {
			CoursePress_Helper_Utility::send_bb_json( $json_data );
		}
	}

	public static function attachment_from_url( $url ) {
		$attachment = false;

		// TODO: Use a custom SQL instead of this filter-workaround...
		add_filter(
			'posts_where',
			array( __CLASS__, 'where_attachment_guid' )
		);

		self::$image_url = preg_replace( '/http:\/\/(\w|\.)*\//', '', $url );

		$args = array(
			'post_status' => 'any',
			'post_type' => 'attachment',
		);
		$query = new WP_Query( $args );

		if ( ! empty( $query ) ) {
			$attachment = $query->posts;
			$attachment = ! empty( $attachment ) ? $attachment[0] : false;
		}

		remove_filter(
			'posts_where',
			array( __CLASS__, 'where_attachment_guid' )
		);

		return $attachment;
	}

	public static function where_attachment_guid( $sql ) {
		$sql = ' AND guid LIKE "%' . self::$image_url . '"';

		return $sql;
	}

	public static function safe_b64encode( $string ) {
		$data = base64_encode( $string );
		$data = str_replace( array( '+', '/', '=' ), array( '-', '_', '' ), $data );

		return $data;
	}

	public static function safe_b64decode( $string ) {
		$data = str_replace( array( '-', '_' ), array( '+', '/' ), $string );
		$mod4 = strlen( $data ) % 4;
		if ( $mod4 ) {
			$data .= substr( '====', $mod4 );
		}

		return base64_decode( $data );
	}

	public static function encode( $value ) {
		if ( ! $value ) { return false; }
		if ( ! extension_loaded( 'mcrypt' ) ) { return $value; }
		if ( ! function_exists( 'mcrypt_module_open' ) ) { return $value; }
		$security_key = self::get_security_key();
		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
		$crypttext = mcrypt_encrypt(
			MCRYPT_RIJNDAEL_256,
			$security_key,
			$value,
			MCRYPT_MODE_ECB,
			$iv
		);

		return trim( self::safe_b64encode( $crypttext ) );
	}

	public static function decode( $value ) {
		if ( ! $value ) { return false; }
		if ( ! extension_loaded( 'mcrypt' ) ) { return $value; }
		if ( ! function_exists( 'mcrypt_module_open' ) ) { return $value; }

		$security_key = self::get_security_key();
		$crypttext = self::safe_b64decode( $value );

		if ( ! $crypttext ) { return false; }

		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
		$decrypttext = mcrypt_decrypt(
			MCRYPT_RIJNDAEL_256,
			$security_key,
			$crypttext,
			MCRYPT_MODE_ECB,
			$iv
		);

		return trim( $decrypttext );
	}

	public static function get_file_size( $url, $human = true ) {
		$bytes = 0;

		// If its not a path... its probably a URL
		if ( ! preg_match( '/^\//', $url ) ) {
			$header = wp_remote_head( $url );
			if ( ! is_wp_error( $header ) ) {
				$bytes = $header['headers']['content-length'];
			} else {
				$bytes = 0;
			}
		} else {
			try {
				$bytes = filesize( $url );
				$bytes = ! empty( $bytes ) ? $bytes : 0;
			} catch ( Exception $e ) {
				$bytes = 0;
			}
		}

		if ( 0 == $bytes ) {
			$human = false;
		}

		return $human ? self::format_file_size( $bytes ) : $bytes;
	}

	public static function format_file_size( $bytes ) {
		$bytes = (int) $bytes;

		if ( $bytes >= 1073741824 ) {
			$bytes = number_format( $bytes / 1073741824, 2 ) . ' GB';
		} elseif ( $bytes >= 1048576 ) {
			$bytes = number_format( $bytes / 1048576, 2 ) . ' MB';
		} elseif ( $bytes >= 1024 ) {
			$bytes = number_format( $bytes / 1024, 2 ) . ' KB';
		} elseif ( $bytes > 1 ) {
			$bytes = $bytes . ' bytes';
		} elseif ( 1 == $bytes ) {
			$bytes = $bytes . ' byte';
		} else {
			$bytes = '0 bytes';
		}

		return $bytes;
	}

	public static function force_download_file_request() {
		if ( isset( $_GET['fdcpf'] ) ) {
			// Regenerate certficate
			if ( ! empty( $_GET['c'] ) && ! empty( $_GET['u'] ) ) {
				$course_id = (int) $_GET['c'];
				$student_id = (int) $_GET['u'];
				CoursePress_Data_Certificate::generate_pdf_certificate( $course_id, $student_id );
			}

			$requested_file = self::decode( $_GET['fdcpf'] );
			self::download_file_request( $requested_file );
		}
	}

	public static function download_file_request( $requested_file ) {
		ob_start();

		$requested_file_obj = wp_check_filetype( $requested_file );
		$filename = basename( $requested_file );

		/**
		 * Filter used to alter header params. E.g. removing 'timeout'.
		 */
		$force_download_parameters = apply_filters(
			'coursepress_force_download_parameters',
			array(
				'timeout' => 60,
				'user-agent' => CoursePress::$name . ' / ' . CoursePress::$version . ';',
			)
		);

		$body = wp_remote_retrieve_body( wp_remote_get( $requested_file ), $force_download_parameters );
		if ( empty( $body ) && preg_match( '/^https/', $requested_file ) ) {
			$requested_file = preg_replace( '/^https/', 'http', $requested_file );
			$body = wp_remote_retrieve_body( wp_remote_get( $requested_file ), $force_download_parameters );
		}
		if ( ! empty( $body ) ) {
			header( 'Pragma: public' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Cache-Control: private', false );
			header( 'Content-Type: ' . $requested_file_obj['type'] );
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Connection: close' );
			echo $body;
		} else {
			_e( 'Something went wrong.', 'coursepress' );
		}
		exit();
	}

	public static function open_course_zip_object() {
		if ( isset( $_GET['oacpf'] ) ) {
			ob_start();

			$requested_file = self::decode( $_GET['oacpf'] );

			$module_id = isset( $_GET['module'] ) ? (int) $_GET['module'] : false;
			$append_url = ! empty( $module_id ) ? '#module-' . $module_id : '';

			// Unzipping the magic
			$upload_dir = wp_upload_dir();

			$path = explode( '.', $requested_file );
			$extension = array_pop( $path );
			$path = implode( '.', $path );

			if ( 'zip' !== strtolower( $extension ) ) {
				exit();
			}

			// Get access to zip functions
			require_once ABSPATH .'/wp-admin/includes/file.php'; //the cheat
			WP_Filesystem();

			$subdir = str_replace( $upload_dir['baseurl'], '', $path );
			$subdir = explode( '/', $subdir );
			$filename = array_pop( $subdir );
			$subdir = implode( '/', $subdir );

			$src_path = untrailingslashit( $upload_dir['basedir'] ) . trailingslashit( $subdir ) . $filename . '.' . $extension;
			$object_dir = trailingslashit( untrailingslashit( $upload_dir['basedir'] ) . trailingslashit( $subdir ) . 'objects/' . $filename );
			$file = $_GET['file'];
			$file_path = $object_dir . $file;
			$file_url_base = trailingslashit( str_replace( $filename, '', $path ) ) . trailingslashit( 'objects' ) . trailingslashit( $filename );
			$file_url = $file_url_base . $file;

			// Presume that its not unzipped yet.
			if ( ! file_exists( $object_dir ) || ! file_exists( $file_path ) ) {
				// Unzip it
				$unzipfile = unzip_file( $src_path, $object_dir );
			}

			echo '<a href="' . esc_url_raw( wp_get_referer() ) . $append_url . '" style="padding: 5px; font-size: 12px; text-decoration: none; opacity: 0.3; background: #3C3C3C; color: #fff; font-family: helvetica, sans-serif; position: absolute; top: 2; left: 2;"> &laquo; ' . esc_html__( 'Back to Course', 'coursepress' ) . '</a>';

			if ( file_exists( $file_path ) ) {
				echo '<iframe style="margin:0; padding:0; border:none; width: 100%; height: 100vh;" src="' .$file_url . '"></iframe>';
			} else {
				// file not there? try redirect and should go to 404
				wp_safe_redirect( $file_url );
			}
			exit();
		}
	}

	public static function truncate_html(
		$text, $length = 100, $ending = '...', $exact = false, $consider_html = true
	) {
		if ( $consider_html ) {
			// if the plain text is shorter than the maximum length, return the whole text
			if ( strlen( preg_replace( '/<.*?>/', '', $text ) ) <= $length ) {
				return $text;
			}

			/**
			 * add space before HTML end line, to avoid caoncatantion of two
			 * sentences without space between.
			 */
			$text = preg_replace( '@(<\/p>|<br)@', ' $1', $text );

			// splits all html-tags to scanable lines.
			preg_match_all( '/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER );
			$total_length = strlen( $ending );
			$open_tags = array();
			$truncate = '';

			foreach ( $lines as $line_matchings ) {
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if ( ! empty( $line_matchings[1] ) ) {
					// if it's an "empty element" with or without xhtml-conform closing slash
					if ( preg_match( '/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1] ) ) {
						// do nothing
						// if tag is a closing tag
					} else if ( preg_match( '/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings ) ) {
						// delete tag from $open_tags list
						$pos = array_search( $tag_matchings[1], $open_tags );
						if ( false !== $pos ) {
							unset( $open_tags[ $pos ] );
						}
						// if tag is an opening tag
					} else if ( preg_match( '/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings ) ) {
						// add tag to the beginning of $open_tags list
						array_unshift( $open_tags, strtolower( $tag_matchings[1] ) );
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings[1];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen( preg_replace( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2] ) );

				if ( $total_length + $content_length > $length ) {
					// the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if ( preg_match_all( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE ) ) {
						// calculate the real length of all entities in the legal range
						foreach ( $entities[0] as $entity ) {
							if ( $entity[1] + 1 - $entities_length <= $left ) {
								$left --;
								$entities_length += strlen( $entity[0] );
							} else {
								// no more characters left
								break;
							}
						}
					}
					$truncate .= substr( $line_matchings[2], 0, $left + $entities_length );
					// maximum length is reached, so get off the loop
					break;
				} else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}
				// if the maximum length is reached, get off the loop
				if ( $total_length >= $length ) {
					break;
				}
			}
		} else {
			if ( strlen( $text ) <= $length ) {
				return $text;
			} else {
				$truncate = substr( $text, 0, $length - strlen( $ending ) );
			}
		}

		// if the words shouldn't be cut in the middle...
		if ( ! $exact ) {
			// ...search the last occurance of a space...
			$spacepos = strrpos( $truncate, ' ' );
			if ( isset( $spacepos ) ) {
				// ...and cut the text in this position
				$truncate = substr( $truncate, 0, $spacepos );
			}
		}

		// add the defined ending to the text
		$truncate .= ' ' . $ending;
		if ( $consider_html ) {
			// close all unclosed html-tags
			foreach ( $open_tags as $tag ) {
				$truncate .= '</' . $tag . '>';
			}
		}

		return $truncate;
	}

	public static function author_description_excerpt( $user = false, $length = 100 ) {
		if ( ! $user ) {
			$user = get_current_user();
		}

		if ( ! is_object( $user ) && 0 < (int) $user ) {
			$user = get_userdata( $user );
		}

		$excerpt = get_user_option( 'description', $user->ID );

		$excerpt = strip_shortcodes( $excerpt );
		$excerpt = str_replace( ']]>', ']]&gt;', $excerpt );
		$excerpt = strip_tags( $excerpt );
		$excerpt_length = apply_filters( 'excerpt_length', $length );
		$excerpt_more = ' ' . '...';

		$words = preg_split( "/[\n\r\t ]+/", $excerpt, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY );
		if ( count( $words ) > $excerpt_length ) {
			array_pop( $words );
			$excerpt = implode( ' ', $words );
			$excerpt = $excerpt . $excerpt_more;
		} else {
			$excerpt = implode( ' ', $words );
		}

		return $excerpt;
	}

	/**
	 * Stores the post-ID for later usage via self::the_post()
	 *
	 * @since 2.0.0
	 * @param int $post The post-ID
	 */
	public static function set_the_post( $post ) {
		self::$cp_post_id = (int) $post;
	}

	/**
	 * Returns the post-ID (or WP_Post object) that was previously stored via
	 * self::set_the_post()
	 *
	 * @since  2.0.0
	 * @param  string $type Either 'id' or 'full'.
	 * @return int|WP_Post The post-ID or post object.
	 */
	public static function the_post( $type = 'full' ) {
		$id = self::$cp_post_id;

		switch ( $type ) {
			case 'id':
				return $id;

			case 'full':
			default:
				return get_post( $id );
		}
	}

	/**
	 * Store the pagination offset for current archive list.
	 *
	 * @since 2.0.0
	 * @param int $page The pagination offset (i.e. number of current page).
	 */
	public static function set_the_pagination( $page ) {
		self::$cp_pagination = (int) $page;
	}

	/**
	 * Returns the number of the currently displayed pagination page.
	 *
	 * @since  2.0.0
	 * @return int The pagination offset (1 is first page).
	 */
	public static function the_pagination() {
		global $wp;

		if ( null === self::$cp_pagination ) {
			if ( isset( $wp->query_vars['paged'] ) ) {
				self::$cp_pagination = (int) $wp->query_vars['paged'];
			} else {
				self::$cp_pagination = 1;
			}
		}

		return self::$cp_pagination;
	}

	public static function the_course( $id_only = false ) {
		$id = CoursePress_Data_Course::last_course_id();

		if ( empty( $id ) ) { return ''; }

		if ( $id_only ) {
			return $id;
		} else {
			return get_post( $id );
		}
	}

	public static function the_course_category() {
		return CoursePress_Data_Course::$last_course_category;
	}

	public static function the_course_subpage() {
		return CoursePress_Data_Course::$last_course_subpage;
	}

	public static function set_the_course( $post ) {
		if ( is_object( $post ) ) {
			CoursePress_Data_Course::set_last_course_id( (int) $post->ID );
		} else {
			CoursePress_Data_Course::set_last_course_id( (int) $post );
		}
	}

	public static function set_the_course_category( $category ) {
		CoursePress_Data_Course::$last_course_category = sanitize_text_field( $category );
	}

	public static function set_the_course_subpage( $page ) {
		CoursePress_Data_Course::$last_course_subpage = sanitize_text_field( $page );
	}

	//public static function course_admin_filters() {
	//
	//	$valid_pages = array( 'coursepress_settings', 'coursepress_course', 'coursepress' );
	//
	//	if ( ! isset( $_GET['page'] ) || ! in_array( $_GET['page'], $valid_pages ) ) {
	//		return;
	//	}
	//
	//	add_filter( 'upload_mimes', array( __CLASS__, 'add_zip_mimes') );
	//
	//}
	//
	//public static function add_zip_mimes ( $existing_mimes = array() ) {
	//	// add your extension to the mimes array as below
	//	$existing_mimes['zip'] = 'application/zip';
	//	$existing_mimes['gz'] = 'application/x-gzip';
	//	return $existing_mimes;
	//}

	public static function allowed_student_mimes() {
		return apply_filters(
			'coursepress_allowed_student_mimes',
			array(
				'txt' => 'text/plain',
				'pdf' => 'application/pdf',
				'zip' => 'application/zip',
			)
		);
	}

	public static function remove_youtube_controls( $code ) {
		if ( false !== strpos( $code, 'youtu.be' ) || false !== strpos( $code, 'youtube.com' ) ) {
			$parameters = http_build_query( self::$embed_args );

			$code = preg_replace(
				"@src=(['\"])?([^'\">s]*)@",
				'src=$1$2&' . $parameters,
				$code
			);
		}

		return $code;
	}

	public static function remove_related_videos( $html, $url, $args ) {
		self::$embed_args = $args;
		self::$embed_args['color'] = 'white';
		self::$embed_args['rel'] = 0;
		self::$embed_args['modestbranding'] = 1;
		self::$embed_args['showinfo'] = 0;

		self::$embed_args = apply_filters(
			'coursepress_video_embed_args',
			self::$embed_args,
			$html,
			$url,
			$args
		);

		// build the query url.
		$parameters = http_build_query( self::$embed_args );

		// Another attempt to remove Youtube features.
		add_filter(
			'embed_handler_html',
			array( __CLASS__, 'remove_youtube_controls' )
		);
		add_filter(
			'embed_oembed_html',
			array( __CLASS__, 'remove_youtube_controls' )
		);

		// YouTube
		$html = str_replace(
			'feature=oembed',
			'feature=oembed&' . $parameters,
			$html
		);

		return $html;
	}

	/**
	 * Check if the website has access to a certain website.
	 * This function is used to check if the public internet is accessible by
	 * the current WP installation.
	 *
	 * @since  1.0.0
	 * @param  string $test_domain Website to check. Default is google.com.
	 * @return bool True if the website can be reached
	 */
	public static function has_connection( $test_domain = 'www.google.com' ) {
		static $_connected = null;

		if ( null === $_connected ) {
			$cn = fsockopen( $test_domain, 80, $err_num, $err, 5 );
			$_connected = (bool) $cn;
			if ( $_connected ) {
				fclose( $cn );
			}
		}

		return $_connected;
	}

	/**
	 * Returns the full name of the specified user.
	 *
	 * Depending on param $last_first the result will be either of those
	 * "First Last (displayname)"
	 * "Last, First (displayname)"
	 *
	 * @since  1.0.0
	 * @param  int  $user_id The user ID.
	 * @param  bool $last_first Which format to use. Default: "First Last"
	 * @param  bool $show_username Append displayname in brackets. Default: yes.
	 * @return string Full name of the user.
	 */
	public static function get_user_name( $user_id, $last_first = false, $show_username = true ) {
		$user_id = (int) $user_id;
		$display_name = (string) get_user_option( 'display_name', $user_id );
		$last = (string) get_user_option( 'last_name', $user_id );
		$first = (string) get_user_option( 'first_name', $user_id );
		$result = '';

		if ( $last_first ) {
			if ( $last ) { $result .= $last; }
			if ( $first && $result ) { $result .= ', '; }
			if ( $first ) { $result .= $first; }
		} else {
			if ( $first ) { $result .= $first; }
			if ( $last && $result ) { $result .= ' '; }
			if ( $last ) { $result .= $last; }
		}

		if ( $display_name ) {
			if ( $result && $show_username ) {
				$result .= ' (' . $display_name . ')';
			} elseif ( ! $result ) {
				$result = $display_name;
			}
		}

		return $result;
	}

	/**
	 * Convert a duration string in extended ISO 8601 format "HH:MM:SS" into a
	 * second value (integer).
	 *
	 * @since  1.0.0
	 * @param  string $duration Duration in ISO format HH:MM:SS.
	 * @return int Duration in seconds
	 */
	public static function duration_to_seconds( $duration ) {
		$seconds = 0;

		$parts = explode( ':', $duration );

		if ( ! empty( $parts ) ) {
			$seconds = (int) array_pop( $parts );
		}

		if ( ! empty( $parts ) ) {
			$seconds += 60 * ( (int) array_pop( $parts ) );
		}

		if ( ! empty( $parts ) ) {
			$seconds += 3600 * ( (int) array_pop( $parts ) );
		}

		return $seconds;
	}

	/**
	 * Converts a time value (in seconds) into extended ISO 8601 format string.
	 *
	 * @since  1.0.0
	 * @param  int $seconds Duration in seconds.
	 * @return string Duration in ISO format HH:MM:SS.
	 */
	public static function seconds_to_duration( $seconds ) {
		$hh = (int) ( $seconds / 3600 );
		$mm = (int) ( ( $seconds - ( $hh * 3600 ) ) / 60 );
		$ss = $seconds - ( $hh * 3600 ) - ( $mm * 60 );

		return sprintf( '%02d:%02d:%02d', $hh, $mm, $ss );
	}

	public static function hashcode( $string ) {
		$hash = 0;
		if ( ! strlen( $string ) ) { return $hash; }

		for ( $i = 0; $i < strlen( $string ); $i++ ) {
			$char = substr( $string, $i, 1 );
			$hash = ( ( $hash << 5 ) - $hash ) + ord( $char );
			$hash = $hash & $hash; // Convert to 32bit integer.
		}

		return $hash;
	}

	/**
	 * Replaces the defined placeholders in the content with specified values.
	 *
	 * @since  2.0.0
	 * @param  string $content The full content, with placeholders.
	 * @param  array  $vars List of placeholder => value.
	 * @return string The content but with all placeholders replaced.
	 */
	public static function replace_vars( $content, $vars ) {
		$keys = array();
		$values = array();

		foreach ( $vars as $key => $value ) {
			$keys[] = $key;
			$values[] = $value;
		}

		return str_replace( $keys, $values, $content );
	}

	public static function serialize_key( $key, $value ) {
		$meta = maybe_serialize( array( $key => $value ) );
		$start = strpos( $meta, '{' ) + 1;
		$end = strpos( $meta, ';}' ) -4;

		return substr( $meta, $start, $end );
	}

	/**
	 * Add post meta as unique field.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $post_id Post ID.
	 * @param string $meta_key Meta field key.
	 * @param any $meta_value Meta field value.
	 */
	public static function add_meta_unique( $post_id, $meta_key, $meta_value ) {
		$success = add_post_meta( $post_id, $meta_key, $meta_value, true );
		if ( ! $success ) {
			update_post_meta( $post_id, $meta_key, $meta_value );
		}
	}

	/**
	 * get post or empty post type object
	 */
	public static function get_post_by_post_type( $post_type, $post_id = 0 ) {
		/**
		 * Create empty post object.
		 */
		if ( empty( $post_id ) ) {
			$post = new stdClass();
			$post = new WP_Post( $post );
			$post->post_type = $post_type;
			$post->post_status = 'draft';
			return $post;
		}
		/**
		 * try to get post and sanitize post_type
		 */
		$post = get_post( $post_id );
		if ( $post_type !== $post->post_type ) {
			wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );
		}
		return $post;
	}

	/**
	 * Login user - we need do it in parse_request action, because when
	 * shortcode is parsed, then it is too late to set auth cookie.
	 *
	 * @since 2.0.3
	 */
	public static function course_signup() {
		if ( ! isset( $_POST['log'] ) || ! isset( $_POST['pwd'] ) ) {
			return;
		}
		if ( is_user_logged_in() ) {
			return;
		}
		// Attempt a login if submitted.
		$user = $_POST['log'];
		if ( preg_match( '/@/', $user ) ) {
			$userdata = get_user_by( 'email', $user );
			$user = $userdata->user_login;
		}
		$credentials = array(
			'user_login' => $user,
			'user_password' => $_POST['pwd'],
		);
		$auth = wp_signon( $credentials );
		if ( ! is_wp_error( $auth ) ) {
			/**
			 * redirect contributors+ to dashboard
			 */
			$userdata = get_user_by( 'login', $user );
			if ( user_can( $userdata, 'edit_posts' ) ) {
				wp_safe_redirect( admin_url() );
				exit;
			}
			if ( isset( $_POST['redirect_url'] ) ) {
				wp_safe_redirect( urldecode( esc_url_raw( $_POST['redirect_url'] ) ) );
			} else if ( isset( $_POST['redirect_to'] ) ) {
				wp_safe_redirect( urldecode( esc_url_raw( $_POST['redirect_to'] ) ) );
			} else {
				wp_redirect( esc_url_raw( CoursePress_Core::get_slug( 'student_dashboard', true ) ) );
			}
			exit;
		}
		add_filter( 'cp_course_signup_form_show_messages', '__return_true' );
	}

	/**
	 * get $security_key - get key as substring of NONCE_KEY, but check
	 * length.
	 *
	 * @since 2.0.3
	 */
	private static function get_security_key() {
		$security_key = NONCE_KEY;
		$available_lengths = array( 32, 24, 16 );
		$security_key = NONCE_KEY;
		foreach ( $available_lengths as $key_length ) {
			if ( function_exists( 'mb_substr' ) ) {
				$security_key = mb_substr( $security_key, 0, $key_length );
			} else {
				$security_key = substr( $security_key, 0, $key_length );
			}
			if ( $key_length == strlen( $security_key ) ) {
				return $security_key;
			}
		}
		/**
		 * md5 has always 16 characters length.
		 */
		$security_key = md5( NONCE_KEY );
		return $security_key;
	}

	/**
	 * Get total secounds.
	 *
	 * @since 2.0.4
	 *
	 * @param integer $secounds Number of secounds.
	 * @param integer $hours Number of hours.
	 * @param integer $minutes Number of minutes.
	 * @return array {
	 *      @type string Time in format hh:mm:ss
	 *      @type integer Number of hours.
	 *      @type integer Number of minutes.
	 *      @type integer Number of seconds.
	 * }
	 * internet Total number of seconds.
	 */
	public static function get_time( $seconds = 0, $minutes = 0, $hours = 0 ) {
		$time = (int) $seconds + (int) $minutes * MINUTE_IN_SECONDS + (int) $hours * HOUR_IN_SECONDS;
		return array(
			'total_seconds' => $time,
			'time' => date( 'H:i:s', $time ),
			'hours' => date( 'H', $time ),
			'minutes' => date( 'i', $time ),
			'seconds' => date( 's', $time ),
		);
	}

	/*
	 * Convert an associative array to html params.
	 *
	 * @since 2.0.4
	 */
	public static function convert_array_to_params( $array ) {
		$content = '';
		if ( is_array( $array ) ) {
			foreach ( $array as $key => $value ) {
				if ( preg_match( '/^\d+$/', $key ) ) {
					continue;
				}
				$content .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
			}
		}
		return $content;
	}

	/**
	 * Add custom nav meta box.
	 *
	 * Adapted from http://www.johnmorrisonline.com/how-to-add-a-fully-functional-custom-meta-box-to-wordpress-navigation-menus/.
	 */
	public static function add_nav_menu_meta_boxes() {
		add_meta_box( 'coursepress_endpoints_nav_link', __( 'CoursePress Menu', 'coursepress' ), array( __CLASS__, 'nav_menu_links' ), 'nav-menus', 'side', 'low' );
	}

	/**
	 * Output menu links.
	 */
	public static function nav_menu_links() {
		$end_points = array(
			'courses' => array(
				'label' => __( 'Courses', 'coursepress' ),
				'value' => '#coursepress-endpoints-courses',
			),
			'login' => array(
				'label' => __( 'Log In/Out', 'coursepress' ),
				'value' => '#coursepress-endpoints-login',
			),
			'dashboard' => array(
				'label' => __( 'Dashboard', 'coursepress' ),
				'value' => '#coursepress-endpoints-dashboard',
			),
			'profile' => array(
				'label' => __( 'Profile', 'coursepress' ),
				'value' => '#coursepress-endpoints-profile',
			),
		);
		?>
		<div id="posttype-coursepress-endpoints" class="posttypediv">
			<div id="tabs-panel-coursepress-endpoints" class="tabs-panel tabs-panel-active">
				<ul id="coursepress-endpoints-checklist" class="categorychecklist form-no-clear">
					<?php
					$i = -1;
					foreach ( $end_points as $one ) {
						?>
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-object-id]" value="<?php echo esc_attr( $i ); ?>" /> <?php echo esc_html( $one['label'] ); ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-type]" value="custom" />
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-title]" value="<?php echo esc_html( $one['label'] ); ?>" />
							<input type="hidden" class="menu-item-url" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-url]" value="<?php echo esc_url( $one['value'] ); ?>" />
							<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-classes]" />
						</li>
						<?php
						$i --;
					}
					?>
				</ul>
			</div>
			<p class="button-controls">
				<span class="list-controls">
					<a href="<?php echo admin_url( 'nav-menus.php?page-tab=all&selectall=1#posttype-coursepress-endpoints' ); ?>" class="select-all"><?php _e( 'Select All', 'coursepress' ); ?></a>
				</span>
				<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'coursepress' ); ?>" name="add-post-type-menu-item" id="submit-posttype-coursepress-endpoints">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Site vars.
	 *
	 * @since 2.0.7
	 *
	 * @param array $vars Array of site vars.
	 * @return array Array of site vars.
	 */
	public static function add_site_vars( $vars = array() ) {
		/**
		 * get login url
		 */
		$login_url = wp_login_url();
		if ( CoursePress_Core::get_setting( 'general/use_custom_login', true ) ) {
			$login_url = CoursePress_Core::get_slug( 'login', true );
		}
		$vars['BLOG_ADDRESS'] = site_url();
		$vars['BLOG_NAME'] = $vars['WEBSITE_NAME'] =  get_bloginfo( 'name' );
		$vars['LOGIN_ADDRESS'] = $login_url;
		$vars['WEBSITE_ADDRESS'] = home_url();
		/**
		 * Allow to change site vars.
		 *
		 * @since 2.0.6
		 *
		 * @param array $vars Array of site vars.
		 */
		return apply_filters( 'coursepress_site_vars', $vars );
	}

	/**
	 * Converts a hex color into an RGB array.
	 *
	 * @param $hex_color string The color in format #FFFFFF
	 * @param $default string The value to return if the color to convert turns out to be invalid.
	 * @return array An array containing RGB values.
	 */
	public static function convert_hex_color_to_rgb($hex_color, $default = array())
	{
		$color_valid = (boolean) preg_match('/^#[a-f0-9]{6}$/i', $hex_color);
		if($color_valid)
		{
			$values = CP_TCPDF_COLORS::convertHTMLColorToDec($hex_color, CP_TCPDF_COLORS::$spotcolor);
			return array_values($values);
		}

		return $default;
	}

	/**
	 * If the strength meter is enabled, this method checks a hidden field to make sure that the password is strong enough.
	 *
	 * If the strength meter is disabled then this method makes sure that the password meets the minimum length requirement and has the required characters.
	 */
	public static function is_password_strong()
	{
		$confirm_weak_password = isset($_POST['confirm_weak_password']) ? (boolean)$_POST['confirm_weak_password'] : false;
		$min_password_length = self::get_minimum_password_length();

		if (self::is_password_strength_meter_enabled()) {
			$password_strength = isset($_POST['password_strength_level']) ? intval($_POST['password_strength_level']) : 0;

			return $confirm_weak_password || $password_strength >= 3;
		} else {
			$password = isset($_POST['password']) ? $_POST['password'] : '';
			$password_strong = strlen($password) >= $min_password_length && preg_match('#[0-9a-z]+#i', $password);

			return $confirm_weak_password || $password_strong;
		}
	}

	/**
	 * Checks if password strength meter is enabled.
	 * @return bool
	 */
	public static function is_password_strength_meter_enabled()
	{
		return (boolean) apply_filters('coursepress_display_password_strength_meter', true);
	}

	/**
	 * Returns the minimum password length to use for validation when the strength meter is disabled.
	 */
	public static function get_minimum_password_length()
	{
		return apply_filters('coursepress_min_password_length', 6);
	}

	public static function is_youtube_url($url)
	{
		$host = parse_url($url, PHP_URL_HOST);
		return $host && (strpos($host, 'youtube') !== false || strpos($host, 'youtu.be') !== false);
	}

	public static function is_vimeo_url($url)
	{
		$host = parse_url($url, PHP_URL_HOST);
		return $host && strpos($host, 'vimeo') !== false;
	}

	public static function create_video_js_setup_data($url, $data)
	{
		$src = null;
		$extra_data = array();
		if(self::is_youtube_url($url))
		{
			$src = 'youtube';

			$show_related_media = !cp_is_true(self::get_array_val($data, 'hide_related_media'));
			$extra_data['youtube'] = array(
				'rel' => intval($show_related_media)
			);
		}
		else if(self::is_vimeo_url($url))
		{
			$src = 'vimeo';
		}

		$setup_data = array();
		$player_width = CoursePress_Helper_Utility::get_array_val( $data, 'video_player_width' );
		if(!$player_width)
		{
			$setup_data['fluid'] = true;
		}
		if($src)
		{
			$setup_data['techOrder'] = array($src);
			$setup_data['sources'] = array(
				array(
					'type' => 'video/' . $src,
					'src' => $url
				)
			);
		}

		$setup_data = array_merge($setup_data, $extra_data);

		return json_encode($setup_data);
	}
}
