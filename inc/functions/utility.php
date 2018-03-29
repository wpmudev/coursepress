<?php
/**
 * CoursePress utility functions and definitions
 *
 * @since 3.0
 * @package CoursePress
 */

/**
 * Check if current page is CoursePress admin page.
 *
 * @return bool|string Returns CoursePress screen ID on success or false.
 */
function coursepress_is_admin() {
	global $CoursePress_Admin_Page;

	if ( ! $CoursePress_Admin_Page instanceof CoursePress_Admin_Page ) {
		return false;
	}

	$screen_id = get_current_screen()->id;

	$pattern = '%toplevel_page_|coursepress-pro_page_|coursepress-base_page_|coursepress_page%';
	$id = preg_replace( $pattern, '', $screen_id );

	if ( in_array( $screen_id, $CoursePress_Admin_Page->__get( 'screens' ) ) ) {
		return $id;
	}

	return false;
}

/**
 * Get list of enrollment types.
 *
 * @return array
 */
function coursepress_get_enrollment_types() {
	$enrollment_types = array(
		'manually' => __( 'Manually added', 'cp' ),
		'registered' => __( 'Any registered users', 'cp' ),
		'passcode' => __( 'Any registered users with a pass code', 'cp' ),
		'prerequisite' => __( 'Registered users who completed the prerequisite course(s).', 'cp' ),
	);

	/**
	 * Fire to allow additional enrollment types.
	 *
	 * @since 2.0
	 */
	$enrollment_types = apply_filters( 'coursepress_course_enrollment_types', $enrollment_types );

	return $enrollment_types;
}

function coursepress_get_default_enrollment_type() {
	$default = 'registered';
	/**
	 * Fire to allow default enrollment type to change.
	 *
	 * @since 2.0
	 */
	$default = apply_filters( 'coursepress_course_enrollment_type_default', $default );
	return $default;
}

/**
 * Get the list of course categories.
 *
 * @return array
 */
function coursepress_get_categories() {
	$terms = get_terms( array( 'taxonomy' => 'course_category', 'hide_empty' => false ) );
	$cats = array();
	if ( ! empty( $terms ) ) {
		foreach ( $terms as $term ) {
			$cats[ $term->term_id ] = $term->name;
		}
	}
	return $cats;
}

/**
 * Get coursepress global setting.
 *
 * @param bool|string $key
 * @param mixed $default
 * @return mixed
 */
function coursepress_get_setting( $key = true, $default = '' ) {
	global $CoursePress_Data_Users;

	$caps = coursepress_get_array_val( $CoursePress_Data_Users->__get( 'capabilities' ), 'instructor' );
	$settings = coursepress_get_option( 'coursepress_settings', array() );

	$defaults = array(
		'general' => array(
			'details_media_type' => 'default',
	        'details_media_priority' => 'default',
	        'listing_media_type' => 'default',
	        'listing_media_priority' => 'default',
	        'image_width' => 235,
	        'image_height' => 235,
		),
		'slugs' => array(
			'course' => 'courses',
	        'course_category' => 'course_category',
	        'units' => 'units',
	        'notifications' => 'notifications',
	        'discussions' => 'discussion',
	        'discussions_new' => 'add_new_discussion',
	        'grades' => 'grades',
	        'workbook' => 'workbook',
	        'login' => 'student-login',
	        'signup' => 'courses-signup',
	        'student_dashboard' => 'courses-dashboard',
	        'student_settings' => 'student-settings',
	        'instructor_profile' => 'instructor',
	        'pages' => array(
	        	'student_dashboard' => 0,
		        'student_settings' => 0,
		        'login' => 0,
	        ),
		),
		'emails' => array(),
		'capabilities' => array(
			'instructor' => $caps,
			'facilitator' => $caps,
		),
		'basic_certificate' => array(
			'enabled'                   => true,
			'use_cp_default'            => false,
			'content'                   => '',
			'orientation'               => 'L',
			'margin'                    => array(
				'top'   => 0,
				'left'  => 0,
				'right' => 0,
			),
			'certificate_logo_position' => array(
				'x' => 0,
				'y' => 0,
				'w' => 0,
			),
		),
		'extensions' => array(),
		'marketpress' => array(
			'enabled' => false,
			'redirect' => true,
			'unpaid' => 'change_status',
			'delete' => 'change_status',
			'type' => 'commerce',
		),
		'woocommerce' => array(
			'enabled' => false,
			'redirect' => true,
			'unpaid' => 'change_status',
			'delete' => 'change_status',
			'type' => 'commerce',
		),
	);

	// Add social sharing default values dynamically.
	$social_keys = CoursePress_Data_SocialMedia::get_social_sharing_keys();
	if ( ! empty( $social_keys ) && ! empty( $settings ) ) {
		foreach ( $social_keys as $social_key ) {
			$defaults['general']['social_sharing'][ $social_key ] = 1;
		}
	}

	if ( ! empty( $settings ) ) {
		// Legacy settings
		$defaults['general']['image_width'] = coursepress_get_array_val( $settings, 'course/image_width' );
		$defaults['general']['image_height'] = coursepress_get_array_val( $settings, 'course/image_height' );
		$defaults['general']['details_media_type'] = coursepress_get_array_val( $settings, 'course/details_media_type' );
		$defaults['general']['details_media_priority'] = coursepress_get_array_val( $settings, 'course/details_media_priority' );
		$defaults['general']['listing_media_type'] = coursepress_get_array_val( $settings, 'course/listing_media_type' );
		$defaults['general']['listing_media_priority'] = coursepress_get_array_val( $settings, 'course/listing_media_priority' );
		$defaults['general']['order_by'] = coursepress_get_array_val( $settings, 'course/order_by' );
		$defaults['general']['order_by_direction'] = coursepress_get_array_val( $settings, 'course/order_by_direction' );
		$defaults['general']['instructor_show_username'] = coursepress_get_array_val( $settings, 'instructor/show_username' );
		$defaults['general']['enrollment_type_default'] = coursepress_get_array_val( $settings, 'course/enrollment_type_default' );
		$defaults['general']['reports_font'] = coursepress_get_array_val( $settings, 'reports/font' );
	}

	/**
	 * Fire to allow setting the default settings.
	 *
	 * @since 3.0
	 */
	$defaults = apply_filters( 'coursepress_default_settings', $defaults );

	foreach ( $defaults as $k => $_default ) {
		$custom = isset( $settings[ $k ] ) ? $settings[ $k ] : array();
		$settings[ $k ] = wp_parse_args( $custom, $_default );
	}

	if ( is_bool( $key ) && true === $key ) {
		return $settings;
	}

	return coursepress_get_array_val( $settings, $key, $default );
}

/**
 * Helper function to update CP global settings.
 *
 * @param bool $key
 * @param mixed $value
 *
 * @return mixed
 */
function coursepress_update_setting( $key = true, $value ) {
	$settings = coursepress_get_setting( true );

	if ( ! is_bool( $key )  ) {
		$settings = coursepress_set_array_val( $settings, $key, $value );
	} else {
		$settings = $value;
	}

	coursepress_update_option( 'coursepress_settings', $settings );

	return $settings;
}

/**
 * Get or print the given filename.
 *
 * @param string $file The relative path of the file.
 * @param array $args Optional arguments to set as variable
 * @param bool $echo Whether to return the result in string or not.
 * @return mixed
 */
function coursepress_render( $file, $args = array(), $echo = true ) {
	global $CoursePress;
	$path = $CoursePress->plugin_path;
	$filename = $path . $file . '.php';
	if ( file_exists( $filename ) && is_readable( $filename ) ) {
		if ( ! empty( $args ) ) {
			$args = (array) $args;
			foreach ( $args as $key => $value ) {
				$$key = $value;
			}
		}
		if ( $echo ) {
			include $filename;
		} else {
			ob_start();
			include $filename;
			return ob_get_clean();
		}
		return true;
	}
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( sprintf( 'CoursePress, missing template: %s', $file ) );
	}
	return false;
}

/**
 * Get coursepress template or load current theme's custom coursepress template.
 *
 * @param string $name
 * @param string $slug
 *
 * @return mixed
 */
function coursepress_get_template( $name, $slug = '' ) {
	$template = implode( '-', array( $name, $slug ) );

	if ( ! locate_template( $template . '.php' ) ) {
		coursepress_render( 'templates/' . $template );
	}

	return null;
}

/**
 * Helper function to get the value of an dimensional array base on path.
 *
 * @param array $array
 * @param string $key
 * @param mixed $default
 *
 * @return mixed|null|string
 */
function coursepress_get_array_val( $array, $key, $default = '' ) {

	if ( ! is_array( $array ) ) {
		return null;
	}

	$keys = explode( '/', $key );
	$last_key = array_pop( $keys );

	foreach ( $keys as $k ) {
		if ( isset( $array[ $k ] ) ) {
			$array = $array[ $k ];
		}
	}

	if ( isset( $array[ $last_key ] ) ) {
		return $array[ $last_key ];
	}

	return $default;
}

/**
 * Helper function to set an array value base on path.
 *
 * @param $array
 * @param $path
 * @param $value
 *
 * @return array
 */
function coursepress_set_array_val( $array, $path, $value ) {

	if ( ! is_array( $path ) ) {
		$path = explode( '/', $path );
	}

	if ( ! is_array( $array ) ) {
		$array = array();
	}

	$key = array_shift( $path );

	if ( count( $path ) > 0 ) {
		if ( ! isset( $array[ $key ] ) ) {
			$array[ $key ] = array();
		}

		$array[ $key ] = coursepress_set_array_val( $array[ $key ], $path, $value );
	} else {
		$array[ $key ] = $value;
	}

	return $array;
}

/**
 * Helper function to get global option in either single or multi site.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function coursepress_get_option( $key, $default = '' ) {
	if ( is_multisite() ) {
		$value = get_site_option( $key, $default );
	} else {
	    $value = get_option( $key, $default );
	}

	return $value;
}

/**
 * Helper function to update global option in either single or multi site.
 *
 * @param $key
 * @param $value
 */
function coursepress_update_option( $key, $value ) {
	if ( is_multisite() ) {
		update_site_option( $key, $value );
	} else {
		update_option( $key, $value );
	}
}

/**
 * Get CoursePress courses url.
 *
 * @return string
 */
function coursepress_get_url() {
	$slug = coursepress_get_setting( 'slugs/course', 'courses' );

	return trailingslashit( home_url( '/' . $slug ) );
}

/**
 * Check if the given user have comments on the given course, unit or step ID.
 *
 * @param int $student_id
 * @param int $post_id
 *
 * @return bool
 */
function coursepress_user_have_comments( $student_id, $post_id ) {
	$args = array(
		'post_id' => $post_id,
		'user_id' => $student_id,
		'order' => 'ASC',
		'offset' => 0,
		'number' => 1, // We only need one to verify if current user posted a comment.
		'fields' => 'ids',
		'status' => 'all',
	);
	$comments = get_comments( $args );

	return count( $comments ) > 0;
}

/**
 * Get HTML progress wheel.
 *
 * @param array $attr
 *
 * @return string
 */
function coursepress_progress_wheel( $attr = array() ) {
	global $CoursePress_Core;

	$core = $CoursePress_Core;
	$defaults = array(
		'class'                     => '',
		'data-value'                 => 100,
		'data-start-angle'           => '4.7',
		'data-size'                  => 36,
		'data-knob-data-height'      => 40,
		'data-empty-fill'            => 'rgba(0, 0, 0, 0.2)',
		'data-fill-color'            => '#24bde6',
		'data-bg-color'              => '#e0e6eb',
		'data-thickness'             => '6',
		'data-format'                => true,
		'data-style'                 => 'extended',
		'data-animation-start-value' => '1.0',
		'data-knob-data-thickness'   => 0.18,
		'data-knob-text-show'        => true,
		'data-knob-text-color'       => '#222222',
		'data-knob-text-align'       => 'center',
		'data-knob-text-denominator' => '4.5',
	);

	$attr = wp_parse_args( $attr, $defaults );
	$class = array( 'course-progress-disc' );

	if ( ! empty( $attr['class'] ) ) {
		$class[] = $attr['class'];
	}
	$value = $attr['data-value'];

	$value = intval( $value ) / 100;
	$attr['data-value'] = $value;

	$attr['class'] = implode( ' ', $class );

	return $core->create_html( 'div', $attr );
}

/**
 * Returns breadcrumb.
 *
 * @return null
 */
function coursepress_breadcrumb() {
	global $CoursePress_VirtualPage;

	if ( ! $CoursePress_VirtualPage instanceof CoursePress_VirtualPage ) {
		return null;
	}

	$vp = $CoursePress_VirtualPage;
	$items = $CoursePress_VirtualPage->__get( 'breadcrumb' );

	if ( ! empty( $items ) ) {
		$breadcrumb = '';

		// Make the last item non-clickable
		$last_item = array_pop( $items );

		foreach ( $items as $item ) {
			$attr = array( 'class' => 'course-item' );
			$breadcrumb .= $vp->create_html( 'li', $attr, $item );
		}

		$breadcrumb .= $vp->create_html( 'li', array( 'class' => 'current' ), wp_strip_all_tags( $last_item ) );

		echo $vp->create_html( 'ul', array( 'class' => 'course-breadcrumb' ), $breadcrumb );
	}

	return '';
}

/**
 * Generate HTML block.
 *
 * @param $tag
 * @param array $attributes
 * @param string $content
 *
 * @return null|string
 */
function coursepress_create_html( $tag, $attributes = array(), $content = '' ) {
	global $CoursePress_Core;

	if ( ! $CoursePress_Core instanceof CoursePress_Core ) {
		return null;
	}

	return $CoursePress_Core->create_html( $tag, $attributes, $content );
}

/**
 * Returns true if the WP installation allows user registration.
 *
 * @since  1.0.0
 * @return bool If CoursePress allows user signup.
 */
function coursepress_users_can_register() {
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
		$_allow_register = apply_filters( 'coursepress_users_can_register', $_allow_register );
	}

	return $_allow_register;
}

/**
 * Generate alert message.
 *
 * @param string $content Message content.
 * @param string $type    Alert type.
 *
 * @since 3.0.0
 *
 * @return string
 */
function coursepress_alert_message( $content = '', $type = 'info' ) {

	$html = '<div class="cp-alert cp-alert-' . $type . '">';
	$html .= $content;
	$html .= '</div>';

	return $html;
}

function coursepress_convert_hex_color_to_rgb( $hex_color, $default ) {
	if ( is_string( $hex_color ) ) {
		$color_valid = (boolean) preg_match( '/^#[a-f0-9]{6}$/i', $hex_color );
		if ( $color_valid ) {
			$values = CP_TCPDF_COLORS::convertHTMLColorToDec( $hex_color, CP_TCPDF_COLORS::$spotcolor );
			return array_values( $values );
		}
	}
	return $default;
}

function coursepress_download_file( $requested_file ) {
	global $CoursePress;

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
			'user-agent' => $CoursePress->name . ' / ' . $CoursePress->version . ';',
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
		_e( 'Something went wrong.', 'cp' );
	}
	exit();
}

function coursepress_set_cookie( $key, $value, $time ) {
	$key = $key . '_' . COOKIEHASH;
	setcookie( $key, $value, $time, COOKIEPATH, COOKIE_DOMAIN );
}

function coursepress_delete_cookie( $key ) {
	$key = $key . '_' . COOKIEHASH;

	ob_start();
	$content = ob_get_clean();
	setcookie( $key, false, -1, COOKIEPATH, COOKIE_DOMAIN );
}

function coursepress_get_cookie( $key ) {
	$key = $key . '_' . COOKIEHASH;

	if ( isset( $_COOKIE[ $key ] ) ) {
		return $_COOKIE[ $key ];
	}

	return false;
}

function coursepress_get_dashboard_url() {
	$page_dashboard = coursepress_get_setting( 'slugs/pages/student_dashboard', false );

	if ( ! $page_dashboard ) {
		$dashboard = coursepress_get_setting( 'slugs/student_dashboard', 'courses-dashboard' );
		$dashboard_url = site_url( '/' ) . trailingslashit( $dashboard );
	} else {
		$dashboard_url = get_permalink( $page_dashboard );
	}

	return $dashboard_url;
}

function coursepress_get_student_login_url() {
	$login_page = coursepress_get_setting( 'slugs/pages/login' );
	if ( (int) $login_page > 0 ) {
		$login_url = get_permalink( (int) $login_page );
	} else {
		$login_slug = coursepress_get_setting( 'slugs/login', 'student-login' );
		$login_url = site_url( '/' ) . trailingslashit( $login_slug );
	}
	return $login_url;
}

function coursepress_get_student_settings_url() {
	$student_page = coursepress_get_setting( 'slugs/pages/student_settings', false );
	if ( ! $student_page ) {
		$student_settings = coursepress_get_setting( 'slugs/student_settings', 'student-settings' );
		$student_url = site_url( '/' ) . trailingslashit( $student_settings, 0 );
	} else {
		$student_url = get_permalink( $student_page );
	}

	return $student_url;
}

function coursepress_replace_vars( $content, $vars ) {
	$login_url = wp_login_url();

	if ( coursepress_get_setting( 'general/use_custom_login', true ) ) {
		$login_url = coursepress_get_student_login_url();
	}
	$vars['COURSES_ADDRESS'] = coursepress_get_main_courses_url();
	$vars['BLOG_ADDRESS'] = site_url();
	$vars['BLOG_NAME'] = $vars['WEBSITE_NAME'] = get_bloginfo( 'name' );
	$vars['LOGIN_ADDRESS'] = $login_url;
	$vars['WEBSITE_ADDRESS'] = home_url();

	$keys   = array();
	$values = array();

	foreach ( $vars as $key => $value ) {
		$keys[]   = $key;
		$values[] = $value;
	}

	return str_replace( $keys, $values, $content );
}

function coursepress_html_select( $data, $echo = false ) {
	$content = sprintf(
		'<select name="%s" id="%s" class="%s">',
		isset( $data['name'] )? $data['name']:'',
		isset( $data['id'] )? $data['id']:'',
		isset( $data['class'] )? $data['class']:''
	);
	if ( isset( $data['options'] ) && is_array( $data['options'] ) ) {
		foreach ( $data['options'] as $one ) {
			$content .= sprintf(
				'<option value="%s" %s>%s</option>',
				isset( $one['value'] )? esc_attr( $one['value'] ) : '',
				isset( $data['value'] )? selected( $one['value'], $data['value'] ) : '',
				isset( $one['label'] )? esc_html( $one['label'] ) : ''
			);
		}
	} else {
		return;
	}
	$content .= '</select>';
	if ( ! $echo ) {
		return $content;
	}
	echo $content;
}

/**
 * Evaluate if the specified value translates to boolean TRUE.
 *
 * True:
 * - Boolean true
 * - Number other than 0
 * - Strings 'yes', 'on', 'true'
 *
 * @param  mixed $value Value to evaluate.
 *
 * @since  2.0.0
 *
 * @return bool
 */
function coursepress_is_true( $value ) {
	if ( ! $value ) {
		// Handles: null, 0, '0', false, ''.
		return false;
	} elseif ( true === $value ) {
		// Bool directly.
		return true;
	} elseif ( ! is_scalar( $value ) ) {
		// Arrays, objects, etc. always evaluate to false.
		return false;
	} elseif ( is_numeric( $value ) ) {
		// A number other than 0 is true.
		return true;
	}
	// Other strings for boolean.
	$value = strtolower( (string) $value );
	if ( 'on' == $value || 'yes' == $value || 'true' == $value ) {
		return true;
	}
	return false;
}

/**
 * Check user access to course.
 *
 * @since 2.0.0
 *
 * @param integer $course_id Course ID
 * @return boolean User can or can not.
 */
function coursepress_can_access_course( $course_id ) {

	if ( empty( $course_id ) ) {
		$course_id = coursepress_get_course_id();

		if ( empty( $course_id ) ) {
			return; // Simply return to avoid fatal error
		}
	}

	$course = coursepress_get_course();

	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( $course->get_permalink() );
		exit;
	}

	$user = coursepress_get_user();

	if ( current_user_can( 'manage_options' ) ) {
		return true;
	}

	/**
	 * check student
	 */
	if ( $user->is_enrolled_at( $course_id ) ) {
		return true;
	}

	/**
	 * check instructor
	 */
	if ( $user->is_instructor_at( $course_id ) ) {
		return true;
	}

	wp_safe_redirect( $course->get_permalink() );
	exit;

}

/**
 * Site vars.
 *
 * @since 2.0.7
 *
 * @param array $vars Array of site vars.
 * @return array Array of site vars.
 */
function coursepress_add_site_vars( $vars = array() ) {
	/**
	 * Get login url.
	 */
	$login_url = wp_login_url();
	if ( coursepress_get_setting( 'general/use_custom_login', true ) ) {
		$login_url = coursepress_get_setting( 'slugs/login', true );
	}
	$vars['BLOG_ADDRESS']    = site_url();
	$vars['BLOG_NAME']       = $vars['WEBSITE_NAME'] = get_bloginfo( 'name' );
	$vars['LOGIN_ADDRESS']   = $login_url;
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
