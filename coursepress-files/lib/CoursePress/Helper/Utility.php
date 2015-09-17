<?php

class CoursePress_Helper_Utility {

	// Used by the array uasort() callbacks
	private static $sort_key;
	private static $image_url; // used to get attachment ID
	public static $is_singular;
	public static $post_page = 1;
	public static $embed_args = array();

	public static function init() {

		add_action( 'wp_ajax_attachment_model', array( __CLASS__, 'attachment_model_ajax' ) );
		add_action( 'init', array( __CLASS__, 'force_download_file_request' ), 1 );
		add_action( 'init', array( __CLASS__, 'open_course_zip_object' ), 1 );
		//add_action( 'admin_init', array( __CLASS__, 'course_admin_filters' ), 1 );
		add_filter('upload_mimes', array( __CLASS__, 'enable_extended_upload') );
	}

	public static function enable_extended_upload ( $mime_types =array() ) {

		$add_the_filter = false;

		$valid_pages = array( 'coursepress_settings', 'coursepress_course', 'coursepress' );
		$matches = false;

		if ( ! isset( $_GET['page'] ) || ! in_array( $_GET['page'], $valid_pages ) ) {
			$add_the_filter = false;
		}

		preg_match('/(page=)(\w*)/', wp_get_referer(), $matches );
		if( isset( $matches ) && isset( $matches[2] ) ) {
			$page_ref = $matches[2];

			if( in_array( $page_ref, $valid_pages ) ) {
				$add_the_filter = true;
			}

		}

		if( ! $add_the_filter ) {
			return $mime_types;
		}

		// The MIME types listed here will be allowed in the media library.
		// You can add as many MIME types as you want.
		$mime_types['gz']  = 'application/x-gzip';
		$mime_types['zip']  = 'application/zip';

		return $mime_types;
	}

	// Sort multi-dimension arrays on 'order' value.
	public static function sort_on_key( $array, $sort_key, $sort_asc = true ) {
		self::$sort_key = $sort_key;

		if ( $sort_asc === false ) {
			uasort( $array, array( __CLASS__, 'sort_desc' ) );
		} else {
			uasort( $array, array( __CLASS__, 'sort_asc' ) );
		}

		return $array;
	}

	// uasort callback to sort ascending
	public static function sort_asc( $x, $y ) {
		if ( $x[ self::$sort_key ] == $y[ self::$sort_key ] ) {
			return 0;
		} else if ( $x[ self::$sort_key ] < $y[ self::$sort_key ] ) {
			return - 1;
		} else {
			return 1;
		}
	}

	// uasort callback to sort descending
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

		if ( $sort_asc === false ) {
			uasort( $array, array( __CLASS__, 'sort_obj_desc' ) );
		} else {
			uasort( $array, array( __CLASS__, 'sort_obj_asc' ) );
		}

		return $array;
	}

	// uasort callback to sort ascending
	public static function sort_obj_asc( $x, $y ) {
		if ( $x->{self::$sort_key} == $y->{self::$sort_key} ) {
			return 0;
		} else if ( $x->{self::$sort_key} < $y->{self::$sort_key} ) {
			return - 1;
		} else {
			return 1;
		}
	}

	// uasort callback to sort descending
	public static function sort_obj_desc( $x, $y ) {
		if ( $x->{self::$sort_key} == $y->{self::$sort_key} ) {
			return 0;
		} else if ( $x->{self::$sort_key} > $y->{self::$sort_key} ) {
			return - 1;
		} else {
			return 1;
		}
	}


	// set array value based on path
	public static function set_array_val( &$a, $path, $value ) {
		if ( ! is_array( $path ) ) {
			$path = explode( '/', $path );
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

	// get array value based on path
	public static function get_array_val( $a, $path ) {
		if ( ! is_array( $path ) ) {
			$path = explode( '/', $path );
		}

		foreach ( $path as $k ) {
			if ( isset( $a[ $k ] ) ) {
				$a = &$a[ $k ];
			} else {
				return null;
			}
		}

		return $a;
	}

	public static function unset_array_val( &$a, $path ) {
		if ( ! is_array( $path ) ) {
			$path = explode( '/', $path );
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
	}

	public static function array_to_object( $array ) {
		if ( is_array( $array ) ) {
			return (object) array_map( array( __CLASS__, 'array_to_object' ), $array );
		} else {
			return $array;
		}
	}


	// Does a recursive array merge without creating 'mini' arrays as array_merge_recursive() does
	public static function merge_distinct( array &$array1, array &$array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) && isset ( $merged [ $key ] ) && is_array( $merged [ $key ] ) ) {
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

	public static function sanitize_recursive( $array ) {

		if ( ! is_array( $array ) ) {
			if ( is_string( $array ) ) {
				return self::filter_content( $array );
			} else {
				// Lets not mess with booleans
				return $array;
			}
		} else {

			foreach ( $array as $key => $value ) {
				$array[ $key ] = self::sanitize_recursive( $value );
			}

			return $array;
		}

	}

	// Deals with legacy 'on' / 'off' values for checkboxes
	public static function checked( $value, $compare = true, $echo = false ) {
		$checked = false;
		if ( $compare === true ) {
			$checked = ( ! empty( $value ) && 'off' !== $value ) || ( ! empty( $value ) && 'on' === $value ) ? 'checked="checked"' : '';
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
		$scheme = ( is_ssl() || force_ssl_admin() ? 'https' : 'http' );

		return admin_url( "admin-ajax.php", $scheme );
	}

	// Allowed image extensions
	public static function get_image_extensions() {
		return apply_filters( 'coursepress_allowed_image_extensions', array(
			'jpg',
			'jpeg',
			'jpe',
			'gif',
			'png',
			'bmp',
			'tif',
			'tiff',
			'ico'
		) );
	}

	// Filter HTML
	public static function filter_content( $content, $none_allowed = false ) {
		if ( $none_allowed ) {
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
						$content[ $content_key ] = wp_kses( $content_value, self::filter_content_rules() );
					}
				} else {
					$content = wp_kses( $content, self::filter_content_rules() );
				}
			}
		}

		return $content;
	}

	// Allowed tags
	public static function filter_content_rules() {
		$allowed_tags = wp_kses_allowed_html( 'post' );

		return apply_filters( 'coursepress_allowed_post_tags', $allowed_tags );
	}

	public static function send_email( $args ) {

		if ( ! isset( $args['email_type'] ) ) {
			return;
		}

		// Filtered fields
		$email = apply_filters( 'coursepress_email_fields', array(

			'email'   => apply_filters( 'coursepress_email_to_address', sanitize_email( $args['email'] ), $args ),
			'subject' => apply_filters( 'coursepress_email_subject', sanitize_text_field( $args['subject'] ) , $args ),
			'message' => apply_filters( 'coursepress_email_message', $args['message'], $args ),

		), $args );

		// Good one to hook if you want to hook WP specific filters (e.g. changing from address)
		do_action( 'coursepress_email_pre_send', $args );

		if ( apply_filters( 'coursepress_email_strip_slashed', true, $args ) ) {
			$email['subject'] = stripslashes( $email['subject'] );
			$email['message'] = stripslashes( nl2br( $email['message'] ) );
		}

		$headers = apply_filters( 'coursepress_email_headers', array(
			'Content-type' => 'text/html',
		), $args );

		$header_string = '';
		foreach ( $headers as $key => $value ) {
			$header_string .= $key . ': ' . $value . "\r\n";
		}

		$result = wp_mail( $email['email'], $email['subject'], CoursePress_Helper_Utility::filter_content( $email['message'] ), $header_string );

		do_action( 'coursepress_email_post_send', $args, $result );

		return apply_filters( 'coursepress_email_send_result', $result, $args );
	}

	public static function users_can_register() {
		if ( is_multisite() ) {
			return users_can_register_signup_filter();
		} else {
			return get_option( 'users_can_register' );
		}
	}

	public static function is_payment_supported() {
		// Hook for payment plugins to turn to 'true'.  Attempt to give Course ID to allow per course filtering.
		return apply_filters( 'coursepress_payment_supported', false, CoursePress_Model_Course::last_course_id() );
	}

	public static function send_bb_json( $response ) {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo json_encode( $response );
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_die();
		} else {
			die;
		}
	}

	public static function attachment_model_ajax() {
		$json_data = array();

		switch ( $_REQUEST['task'] ) {

			case 'get':
				$json_data[] = self::attachment_from_url( sanitize_text_field( $_REQUEST['url'] ) );

				break;

		}

		if ( ! empty( $json_data ) ) {
			CoursePress_Helper_Utility::send_bb_json( $json_data );
		}
	}

	public static function attachment_from_url( $url ) {
		$attachment = false;

		add_filter( 'posts_where', array( __CLASS__, 'where_attachment_guid' ) );

		self::$image_url = preg_replace( '/http:\/\/(\w|\.)*\//', '', $url );

		$args  = array(
			'post_status' => 'any',
			'post_type'   => 'attachment'
		);
		$query = new WP_Query( $args );

		if ( ! empty( $query ) ) {
			$attachment = $query->posts;
			$attachment = ! empty( $attachment ) ? $attachment[0] : false;
		}

		remove_filter( 'posts_where', array( __CLASS__, 'where_attachment_guid' ) );

		return $attachment;
	}

	public static function where_attachment_guid( $sql ) {
		global $wpdb;

		$sql = ' AND guid LIKE "%' . self::$image_url . '"';

		return $sql;
	}

	public static function fix_bool( $value ) {

		if( true !== $value && false !== $value ) {
			$value = '' . $value; // Convert number to string
			$value = strtolower( $value );
		}

		return 'on' === $value || 'yes' === $value || 1 === (int) $value || true === $value || 'true' === $value ? true : false;

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
		$security_key = NONCE_KEY;
		if ( extension_loaded( 'mcrypt' ) && function_exists( 'mcrypt_module_open' ) ) {
			if ( ! $value ) {
				return false;
			}

			$text      = $value;
			$iv_size   = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
			$iv        = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
			$crypttext = mcrypt_encrypt( MCRYPT_RIJNDAEL_256, mb_substr( $security_key, 0, 24 ), $text, MCRYPT_MODE_ECB, $iv );

			return trim( self::safe_b64encode( $crypttext ) );
		} else {
			return $value;
		}
	}

	public static function decode( $value ) {
		$security_key = NONCE_KEY;
		if ( extension_loaded( 'mcrypt' ) && function_exists( 'mcrypt_module_open' ) ) {
			if ( ! $value ) {
				return false;
			}

			$crypttext   = self::safe_b64decode( $value );
			$iv_size     = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
			$iv          = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
			$decrypttext = mcrypt_decrypt( MCRYPT_RIJNDAEL_256, mb_substr( $security_key, 0, 24 ), $crypttext, MCRYPT_MODE_ECB, $iv );

			return trim( $decrypttext );
		} else {
			return $value;
		}
	}

	public static function get_file_size( $url, $human = true ) {
		$bytes = 0;
		// If its not a path... its probably a URL
		if ( !preg_match( '/^\//', $url ) ) {
			$header = wp_remote_head( $url );
			if ( !is_wp_error( $header ) ) {
				$bytes = $header[ 'headers' ][ 'content-length' ];
			} else {
				$bytes = 0;
			}
		} else {
			try {
				$bytes	 = filesize( $url );
				$bytes	 = !empty( $bytes ) ? $bytes : 0;
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
		} elseif ( $bytes == 1 ) {
			$bytes = $bytes . ' byte';
		} else {
			$bytes = '0 bytes';
		}

		return $bytes;
	}

	public static function force_download_file_request() {

		if ( isset( $_GET[ 'fdcpf' ] ) ) {
			$requested_file	 = self::decode( $_GET[ 'fdcpf' ] );
			self::download_file_request( $requested_file );
		}

	}

	public static function download_file_request( $requested_file ) {

		ob_start();

		$requested_file_obj = wp_check_filetype( $requested_file );
		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check = 0, pre-check = 0' );
		header( 'Cache-Control: private', false );
		header( 'Content-Type: ' . $requested_file_obj[ "type" ] );
		header( 'Content-Disposition: attachment; filename ="' . basename( $requested_file ) . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Connection: close' );

		/**
		 * Filter used to alter header params. E.g. removing 'timeout'.
		 */
		$force_download_parameters = apply_filters( 'coursepress_force_download_parameters', array(
			'timeout'	 => 60,
			'user-agent' => CoursePress_Core::$name . ' / ' . CoursePress_Core::$version . ';'
		) );
		echo wp_remote_retrieve_body( wp_remote_get( $requested_file ), $force_download_parameters );
		exit();

	}

	public static function open_course_zip_object() {

		if ( isset( $_GET[ 'oacpf' ] ) ) {
			ob_start();

			$requested_file	 = self::decode( $_GET[ 'oacpf' ] );


			// Unzipping the magic
			$upload_dir = wp_upload_dir();

			$path = explode( '.', $requested_file );
			$extension = array_pop( $path );
			$path = implode( '.', $path );

			if( 'zip' !== strtolower( $extension ) ) {
				exit();
			}

			// Get access to zip functions
			require_once(ABSPATH .'/wp-admin/includes/file.php'); //the cheat
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
			if( ! file_exists( $object_dir ) || ! file_exists( $file_path ) ) {
				// Unzip it
				$unzipfile = unzip_file( $src_path, $object_dir );
			}

			echo '<a href="' . esc_url_raw( wp_get_referer() ) . '" style="padding: 5px; font-size: 12px; text-decoration: none; opacity: 0.3; background: #3C3C3C; color: #fff; font-family: helvetica, sans-serif; position: absolute; top: 2; left: 2;"> &laquo; ' . esc_html__( 'Back to Course', CoursePress::TD ) . '</a>';
			echo '<iframe style="margin:0; padding:0; border:none; width: 100%; height: 100vh;" src="' .$file_url . '"></iframe>';
			exit();
		}

	}

	public static function truncateHtml( $text, $length = 100, $ending = '...', $exact = false, $considerHtml = true ) {
		if ( $considerHtml ) {
			// if the plain text is shorter than the maximum length, return the whole text
			if ( strlen( preg_replace( '/<.*?>/', '', $text ) ) <= $length ) {
				return $text;
			}
			// splits all html-tags to scanable lines
			preg_match_all( '/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER );
			$total_length	 = strlen( $ending );
			$open_tags		 = array();
			$truncate		 = '';
			foreach ( $lines as $line_matchings ) {
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if ( !empty( $line_matchings[ 1 ] ) ) {
					// if it's an "empty element" with or without xhtml-conform closing slash
					if ( preg_match( '/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[ 1 ] ) ) {
						// do nothing
						// if tag is a closing tag
					} else if ( preg_match( '/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[ 1 ], $tag_matchings ) ) {
						// delete tag from $open_tags list
						$pos = array_search( $tag_matchings[ 1 ], $open_tags );
						if ( $pos !== false ) {
							unset( $open_tags[ $pos ] );
						}
						// if tag is an opening tag
					} else if ( preg_match( '/^<\s*([^\s>!]+).*?>$/s', $line_matchings[ 1 ], $tag_matchings ) ) {
						// add tag to the beginning of $open_tags list
						array_unshift( $open_tags, strtolower( $tag_matchings[ 1 ] ) );
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings[ 1 ];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen( preg_replace( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[ 2 ] ) );
				if ( $total_length + $content_length > $length ) {
					// the number of characters which are left
					$left			 = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if ( preg_match_all( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[ 2 ], $entities, PREG_OFFSET_CAPTURE ) ) {
						// calculate the real length of all entities in the legal range
						foreach ( $entities[ 0 ] as $entity ) {
							if ( $entity[ 1 ] + 1 - $entities_length <= $left ) {
								$left --;
								$entities_length += strlen( $entity[ 0 ] );
							} else {
								// no more characters left
								break;
							}
						}
					}
					$truncate .= substr( $line_matchings[ 2 ], 0, $left + $entities_length );
					// maximum lenght is reached, so get off the loop
					break;
				} else {
					$truncate .= $line_matchings[ 2 ];
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
		if ( !$exact ) {
			// ...search the last occurance of a space...
			$spacepos = strrpos( $truncate, ' ' );
			if ( isset( $spacepos ) ) {
				// ...and cut the text in this position
				$truncate = substr( $truncate, 0, $spacepos );
			}
		}
		// add the defined ending to the text
		$truncate .= ' ' . $ending;
		if ( $considerHtml ) {
			// close all unclosed html-tags
			foreach ( $open_tags as $tag ) {
				$truncate .= '</' . $tag . '>';
			}
		}

		return $truncate;
	}

	public static function author_description_excerpt( $user = false, $length = 100 ) {

		if( ! $user ) {
			$user = get_current_user();
		}

		if( ! is_object( $user ) && 0 < (int) $user ) {
			$user = get_userdata( $user );
		}

		$excerpt = get_user_option( 'description', $user->ID );

		$excerpt        = strip_shortcodes( $excerpt );
		$excerpt        = str_replace( ']]>', ']]&gt;', $excerpt );
		$excerpt        = strip_tags( $excerpt );
		$excerpt_length = apply_filters( 'excerpt_length', $length );
		$excerpt_more   = ' ' . '...';

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

	public static function the_post( $id_only = false ) {

		$id = CoursePress_Model_VirtualPage::$the_post_id;

		if( $id_only ) {
			return $id;
		} else {
			return get_post( $id );
		}

	}

	public static function the_post_page() {
		return self::$post_page;
	}

	public static function the_course( $id_only = false ) {

		//$id = in_the_loop() ? get_the_ID() : CoursePress_Model_Course::last_course_id();
		$id = CoursePress_Model_Course::last_course_id();

		if( empty( $id ) ) {
			return '';
		}

		if( $id_only ) {
			return $id;
		} else {
			return get_post( $id );
		}

	}

	public static function the_course_category() {
		return CoursePress_Model_Course::$last_course_category;
	}

	public static function the_course_subpage() {
		return CoursePress_Model_Course::$last_course_subpage;
	}

	public static function set_the_post( $post ) {

		if( is_object( $post ) ) {
			CoursePress_Model_VirtualPage::$the_post_id = (int) $post->ID;
		} else {
			CoursePress_Model_VirtualPage::$the_post_id = (int) $post;
		}

	}

	public static function set_the_post_page( $page ) {
		self::$post_page = (int) $page;
	}

	public static function set_the_course( $post ) {

		if( is_object( $post ) ) {
			CoursePress_Model_Course::set_last_course_id( (int) $post->ID );
		} else {
			CoursePress_Model_Course::set_last_course_id( (int) $post );
		}

	}

	public static function set_the_course_category( $category ) {
		CoursePress_Model_Course::$last_course_category = sanitize_text_field( $category );
	}

	public static function set_the_course_subpage( $page ) {
		CoursePress_Model_Course::$last_course_subpage = sanitize_text_field( $page );
	}

	//public static function course_admin_filters() {
	//
	//	$valid_pages = array( 'coursepress_settings', 'coursepress_course', 'coursepress' );
	//
	//	if ( ! isset( $_GET['page'] ) || ! in_array( $_GET['page'], $valid_pages ) ) {
	//		return;
	//	}
	//
	//	add_filter('upload_mimes', array( __CLASS__, 'add_zip_mimes') );
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
		return apply_filters( 'coursepress_allowed_student_mimes', array(
			'txt' => 'text/plain',
			'pdf' => 'application/pdf',
			'zip' => 'application/zip'
		) );
	}


	public static function remove_youtube_controls($code){
		if(strpos($code, 'youtu.be') !== false || strpos($code, 'youtube.com') !== false){

			$parameters = http_build_query( self::$embed_args );

			$return = preg_replace("@src=(['\"])?([^'\">s]*)@", "src=$1$2&" . $parameters, $code);
			error_log( $return );
			return $return;
		}
		return $code;
	}

	public static function remove_related_videos( $html, $url, $args ) {

		self::$embed_args                   = $args;
		self::$embed_args['color']          = 'white';
		self::$embed_args['rel']            = 0;
		self::$embed_args['modestbranding'] = 1;
		self::$embed_args['showinfo']       = 0;

		self::$embed_args = apply_filters( 'coursepress_video_embed_args', self::$embed_args, $html, $url, $args );

		// build the query url
		$parameters = http_build_query( self::$embed_args );

		// Another attempt to remove Youtube features
		add_filter('embed_handler_html', array( __CLASS__, 'remove_youtube_controls' ) );
		add_filter('embed_oembed_html', array( __CLASS__, 'remove_youtube_controls' ) );

		// YouTube
		$html = str_replace( 'feature=oembed', 'feature=oembed&' . $parameters, $html );

		return $html;
	}

	public static function has_connection( $test_domain = "www.google.com" ) {
		$cn = @fsockopen( $test_domain, 80, $err_num, $err, 5);
		$connected = (bool) $cn;
		if( $connected ) { fclose( $cn ); }
		return $connected;
	}

	public static function get_user_name( $user_id, $last_first = false, $username = true ) {
		$user_id = (int) $user_id;
		$display_name = get_user_option( 'display_name', $user_id );
		$last         = get_user_option( 'last_name', $user_id );
		$last         = ! empty( $last ) ? $last : '';
		$first        = get_user_option( 'first_name', $user_id );
		$first        = ! empty( $first ) ? $first : '';
		$return_name  = '';
		if( ! $last_first ) {
			$return_name = ! empty( $first ) ? $first : '';
			$return_name = ! empty( $last ) ? $return_name . ' ' . $last : $return_name;
			if( $username ) {
				$return_name = ! empty( $return_name ) ? $return_name . ' (' . $display_name . ')' : $display_name;
			}
			$return_name = ! empty( $return_name ) ? $return_name : $display_name;
		} else {
			$return_name = ! empty( $last ) ? $last : '';
			$return_name = ! empty( $first ) && ! empty( $last ) ? $last . ', ' . $first : $return_name;
			$return_name = empty( $return_name ) && ! empty ( $first ) && empty( $last ) ? $first : $return_name;
			if( $username ) {
				$return_name = ! empty( $return_name ) ? $return_name . ' (' . $display_name . ')' : $display_name;
			}
			$return_name = ! empty( $return_name ) ? $return_name : $display_name;
		}

		return $return_name;
	}

}