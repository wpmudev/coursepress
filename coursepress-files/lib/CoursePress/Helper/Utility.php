<?php

class CoursePress_Helper_Utility {

	// Used by the array uasort() callbacks
	private static $sort_key;
	private static $image_url; // used to get attachment ID
	public static $is_singular;
	public static $post_page = 1;

	public static function init() {

		add_action( 'wp_ajax_attachment_model', array( __CLASS__, 'attachment_model_ajax' ) );

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
			if ( ! isset( $a[ $k ] ) ) {
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
		$truncate .= $ending;
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




}