<?php
/**
 * LEGACY, still needed for now.
 *
 * @todo: needs to be replaced and removed soon
 */

class CoursePress_Helper_Legacy {
	public static function init() {
		// SEt course meta key
		//add_action( 'shutdown', array( __CLASS__, 'reset_course_metas' ) );
	}

	/**
	 * Updates old courses meta
	 **/
	public static function reset_course_metas() {

		// Check marker
		$meta_updated = get_option( 'cp_courses_meta_updated' );

		if ( empty( $meta_updated ) ) {
			$courses = get_posts(
				array(
					'post_type' => CoursePress_Data_Course::get_post_type_name(),
					'meta_key' => 'cp_updated_meta1d',
					'meta_compare' => 'NOT EXISTS',
					'fields' => 'ids',
					'suppress_filtes' => true,
				)
			);

			if ( ! empty( $courses ) ) {
				foreach ( $courses as $course_id ) {
					$old_settings = CoursePress_Data_Course::get_setting( $course_id );
					CoursePress_Data_Course::update_setting( $course_id, true, $old_settings );
					update_post_meta( $course_id, 'cp_updated_meta1d', true );
				}
			} else {
				update_option( 'cp_courses_meta_updated', true );
			}
		}
	}

	/**
	 * Mark a function as deprecated and inform when it has been used.
	 *
	 * The current behavior is to trigger a user error if `WP_DEBUG` is true.
	 *
	 * This function is to be used in every function that is deprecated.
	 *
	 * @since 2.0.5
	 *
	 * @param string $function    The function that was called.
	 * @param string $version     The version of WordPress that deprecated the function.
	 * @param string $replacement Optional. The function that should have been called. Default null.
	 */
	public static function deprecated_function( $function, $version, $replacement = null ) {
		/**
		 * Filters whether to trigger an error for deprecated functions.
		 *
		 * @since 2.5.0
		 *
		 * @param bool $trigger Whether to trigger the error for deprecated functions. Default true.
		 */
		if ( WP_DEBUG && apply_filters( 'deprecated_function_trigger_error', true ) ) {
			if ( function_exists( '__' ) ) {
				if ( ! is_null( $replacement ) ) {
					/* translators: 1: PHP function name, 2: version number, 3: alternative function name */
					trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.' ), $function, $version, $replacement ) );
				} else {
					/* translators: 1: PHP function name, 2: version number */
					trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.' ), $function, $version ) );
				}
			} else {
				if ( ! is_null( $replacement ) ) {
					trigger_error( sprintf( '%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', $function, $version, $replacement ) );
				} else {
					trigger_error( sprintf( '%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.', $function, $version ) );
				}
			}
		}
	}
}

// ----- Deprecated, might be removed anytime ----------------------------------

if ( ! function_exists( 'cp_deep_unserialize' ) ) {
	function cp_deep_unserialize( $serialized_object ) {
		_doing_it_wrong(
			'cp_deep_unserialize',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		$new_array = maybe_unserialize( $serialized_object );

		if ( is_serialized( $new_array ) ) {
			$new_array = cp_deep_unserialize( $new_array );
		}

		return $new_array;
	}
}

if ( ! function_exists( 'cp_search_array' ) ) {
	function cp_search_array( $array, $key, $value ) {
		_doing_it_wrong(
			'cp_search_array',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		$results = array();

		if ( is_array( $array ) ) {
			if ( isset( $array[ $key ] ) && $array[ $key ] == $value ) {
				$results[] = $array;
			}

			foreach ( $array as $subarray ) {
				$results = array_merge( $results, cp_search_array( $subarray, $key, $value ) );
			}
		}

		return $results;
	}
}

if ( ! function_exists( 'cp_minify_output' ) ) {
	function cp_minify_output( $buffer ) {
		_doing_it_wrong(
			'cp_minify_output',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		$search = array(
			'/\>[^\S ]+/s', // Strip whitespaces after tags, except space.
			'/[^\S ]+\</s', // Strip whitespaces before tags, except space.
			'/(\s)+/s',  // Shorten multiple whitespace sequences.
		);
		$replace = array(
			'>',
			'<',
			'\\1',
		);
		$buffer = preg_replace( $search, $replace, $buffer );

		return $buffer;
	}
}

if ( ! function_exists( 'cp_default_args' ) ) {
	function cp_default_args( $pairs, $atts, $shortcode = '' ) {
		_doing_it_wrong(
			'cp_default_args',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		$atts = (array) $atts;
		$out = array();
		foreach ( $pairs as $name => $default ) {
			if ( array_key_exists( $name, $atts ) ) {
				$out[ $name ] = $atts[ $name ];
			} else {
				$out[ $name ] = $default;
			}
		}

		return $out;
	}
}

if ( ! function_exists( 'cp_replace_img_src' ) ) {
	function cp_replace_img_src( $original_img_tag, $new_src_url ) {
		_doing_it_wrong(
			'cp_replace_img_src',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		$doc = new DOMDocument();
		$doc->loadHTML( $original_img_tag );

		$tags = $doc->getElementsByTagName( 'img' );
		if ( count( $tags ) > 0 ) {
			$tag = $tags->item( 0 );
			$tag->setAttribute( 'src', $new_src_url );

			return $doc->saveXML( $tag );
		}

		return false;
	}
}

if ( ! function_exists( 'cp_callback_img' ) ) {
	function cp_callback_img( $match ) {
		_doing_it_wrong(
			'cp_callback_img',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		list(, $img, $src ) = $match;
		$new_src = str_replace( '../wp-content', WP_CONTENT_URL, $src );

		return "$img=\"$new_src\" ";
	}
}

if ( ! function_exists( 'cp_in_array_r' ) ) {
	function cp_in_array_r( $needle, $haystack, $strict = false ) {
		_doing_it_wrong(
			'cp_in_array_r',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		foreach ( $haystack as $item ) {
			if ( $strict ) {
				if ( $item === $needle ) { return true; }
			} else {
				if ( $item == $needle ) { return true; }
			}

			if ( is_array( $item ) ) {
				if ( cp_in_array_r( $needle, $item, $strict ) ) { return true; }
			}
		}

		return false;
	}
}

if ( ! function_exists( 'cp_get_terms_dropdown' ) ) {
	function cp_get_terms_dropdown( $taxonomies, $args ) {
		_doing_it_wrong(
			'cp_get_terms_dropdown',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		$myterms = get_terms( $taxonomies, $args );
		$output = '<select>';
		foreach ( $myterms as $term ) {
			$root_url = get_bloginfo( 'url' );
			$term_taxonomy = $term->taxonomy;
			$term_slug = $term->slug;
			$term_name = $term->name;
			$link = $root_url . '/' . $term_taxonomy . '/' . $term_slug;
			$output .= '<option value="' . $link . '">' . $term_name . '</option>';
		}
		$output .= '</select>';

		return $output;
	}
}

if ( ! function_exists( 'cp_natkrsort' ) ) {
	function cp_natkrsort( $array ) {
		_doing_it_wrong(
			'cp_natkrsort',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		$keys = array_keys( $array );
		natsort( $keys );

		foreach ( $keys as $k ) {
			$new_array[ $k ] = $array[ $k ];
		}

		$new_array = array_reverse( $new_array, true );

		return $new_array;
	}
}

if ( ! function_exists( 'cp_get_count_of_users' ) ) {
	function cp_get_count_of_users( $role = '' ) {
		_doing_it_wrong(
			'cp_get_count_of_users',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		$result = count_users();
		if ( ! $role ) {
			return $result['total_users'];
		} else {
			foreach ( $result['avail_roles'] as $roles => $count ) {
				if ( $roles == $role ) {
					return $count;
				}
			}
		}

		return 0;
	}
}

if ( ! function_exists( 'cp_sp2nbsp' ) ) {
	function cp_sp2nbsp( $string ) {
		_doing_it_wrong(
			'cp_sp2nbsp',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		return str_replace( ' ', '&nbsp;', $string );
	}
}

if ( ! function_exists( 'cp_object_encode' ) ) {
	function cp_object_encode( $object ) {
		_doing_it_wrong(
			'cp_object_encode',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		if ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 && defined( 'JSON_FORCE_OBJECT' ) ) {
			$encoded = json_encode( $object, JSON_FORCE_OBJECT | JSON_HEX_QUOT | JSON_HEX_APOS );
		} else {
			$encoded = json_encode( $object );
		}

		$encoded = str_replace( '"', '&quot;', $encoded );
		$encoded = str_replace( "'", '&apos;', $encoded );

		return $encoded;
	}
}

if ( ! function_exists( 'cp_object_decode' ) ) {
	function cp_object_decode( $string, $class = 'stdClass' ) {
		_doing_it_wrong(
			'cp_object_decode',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		$object = str_replace( '&quot;', '"', $string );
		$object = str_replace( '&apos;', "'", $object );
		$object = json_decode( $object );

		// Convert to correct Class.
		return unserialize(
			sprintf(
				'O:%d:"%s"%s',
				strlen( $class ),
				$class,
				strstr( strstr( serialize( $object ), '"' ), ':' )
			)
		);
	}
}

if ( ! function_exists( 'cp_get_number_of_days_between_dates' ) ) {
	function cp_get_number_of_days_between_dates( $start_date, $end_date ) {
		_doing_it_wrong(
			'cp_get_number_of_days_between_dates',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		$time_start = strtotime( $start_date );
		$time_end = strtotime( $end_date );

		$time_diff = abs( $time_end - $time_start );

		$day_num = $time_diff / 86400;  // 86400 seconds in one day
		$day_num = intval( $day_num );

		return $day_num;
	}
}

if ( ! function_exists( 'cp_instructors_drop_down' ) ) {
	function cp_instructors_drop_down( $class = '' ) {
		_doing_it_wrong(
			'cp_instructors_drop_down',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		$content = '';
		$content .= '<select name="instructors" id="instructors" data-placeholder="' . __( 'Choose a Course Instructor...', 'coursepress' ) . '" class="' . $class . '">';

		$args = array(
			//'role' => 'instructor',
			'meta_key' => '',
			'meta_value' => '',
			'meta_compare' => '',
			'meta_query' => array(),
			'include' => array(),
			'exclude' => array(),
			'orderby' => 'display_name',
			'order' => 'ASC',
			'offset' => '',
			'search' => '',
			'class' => $class,
			'number' => '',
			'count_total' => false,
			'fields' => array( 'display_name', 'ID' ),
			'who' => '',
		);

		if ( is_multisite() ) {
			$args['blog_id'] = get_current_blog_id();
		}

		$instructors = get_users( $args );

		$number = 0;
		foreach ( $instructors as $instructor ) {
			$number ++;
			$content .= '<option value="' . $instructor->ID . '">' . $instructor->display_name . '</option>';
		}
		$content .= '</select>';

		if ( ! $number ) {
			$content = '';
		}

		echo $content;
	}
}

if ( ! function_exists( 'cp_students_drop_down' ) ) {
	function cp_students_drop_down() {
		_doing_it_wrong(
			'cp_students_drop_down',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);
		$content = '';
		$content .= '<select name="students" data-placeholder="' . __( 'Choose a Student...', 'coursepress' ) . '" class="chosen-select">';

		$args = array(
			'role' => '',
			'meta_key' => '',
			'meta_value' => '',
			'meta_compare' => '',
			'meta_query' => array(),
			'include' => array(),
			'exclude' => array(),
			'orderby' => 'display_name',
			'order' => 'ASC',
			'offset' => '',
			'search' => '',
			'number' => '',
			'count_total' => false,
			'fields' => array( 'display_name', 'ID' ),
			'who' => '',
		);

		if ( is_multisite() ) {
			$args['blog_id'] = get_current_blog_id();
		}

		$students = get_users( $args );

		$number = 0;
		foreach ( $students as $student ) {
			$number ++;
			$content .= '<option value="' . $student->ID . '">' . $student->display_name . '</option>';
		}
		$content .= '</select>';

		if ( ! $number ) {
			$content = '';
		}

		echo $content;
	}
}

if ( ! function_exists( 'cp_instructors_pending' ) ) {
	function cp_instructors_pending( $course_id, $has_capability ) {
		_doing_it_wrong(
			'cp_instructors_pending',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);
		$content = '';
		$instructor_invites = get_post_meta( $course_id, 'instructor_invites', true );

		if ( empty( $instructor_invites ) ) {
			return;
		}

		foreach ( $instructor_invites as $instructor ) {

			$remove_button = $has_capability ? '<div class="instructor-remove"><a href="javascript:removePendingInstructor(\'' . $instructor['code'] . '\', ' . $course_id . ' );"><i class="fa fa-times-circle cp-move-icon remove-btn"></i></a></div>' : '';

			$content .=
				'<div class="instructor-avatar-holder pending" id="' . $instructor['code'] . '">' .
				'<div class="instructor-status">PENDING</div>' .
				$remove_button .
				get_avatar( $instructor['email'], 80 ) .
				'<span class="instructor-name">' . $instructor['first_name'] . ' ' . $instructor['last_name'] . '</span>' .
				'</div>';
		}

		echo $content;
	}
}

if ( ! function_exists( 'cp_instructors_avatars' ) ) {
	function cp_instructors_avatars( $course_id, $remove_buttons = true, $just_count = false ) {
		_doing_it_wrong(
			'cp_instructors_avatars',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);
		global $post_id, $wpdb;

		$content = '';

		$args = array(
			//'role' => 'instructor',
			'meta_key' => 'course_' . $course_id,
			'meta_value' => $course_id,
			'meta_compare' => '',
			'meta_query' => array(),
			'include' => array(),
			'exclude' => array(),
			'orderby' => 'display_name',
			'order' => 'ASC',
			'offset' => '',
			'search' => '',
			'number' => '',
			'count_total' => false,
			'fields' => array( 'display_name', 'ID' ),
			'who' => '',
		);

		if ( is_multisite() ) {
			$args['blog_id'] = get_current_blog_id();
			$args['meta_key'] = $wpdb->prefix . 'course_' . $course_id;
		}

		$instructors = get_users( $args );

		if ( $just_count ) {
			return count( $instructors );
		} else {
			foreach ( $instructors as $instructor ) {
				if ( $remove_buttons ) {
					$content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-status"></div><div class="instructor-remove"><a href="javascript:removeInstructor( ' . $instructor->ID . ' );"><i class="fa fa-times-circle cp-move-icon remove-btn"></i></a></div>' . get_avatar( $instructor->ID, 80 ) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
				} else {
					$content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-status"></div><div class="instructor-remove"></div>' . get_avatar( $instructor->ID, 80 ) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
				}
			}

			echo $content;
		}
	}
}

if ( ! function_exists( 'cp_get_number_of_instructors' ) ) {
	function cp_get_number_of_instructors() {
		_doing_it_wrong(
			'cp_get_number_of_instructors',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);
		$args = array(
			//'role' => 'instructor',
			'count_total' => false,
			'fields' => array( 'display_name', 'ID' ),
			'who' => '',
		);

		if ( is_multisite() ) {
			$args['blog_id'] = get_current_blog_id();
		}

		$instructors = get_users( $args );

		return count( $instructors );
	}
}

if ( ! function_exists( 'coursepress_send_email' ) ) {
	function coursepress_send_email( $email_args = array() ) {
		_doing_it_wrong(
			'coursepress_send_email',
			'Deprecated CoursePress function. Use CoursePress_Helper_Email::send_mail() instead!',
			'2.0'
		);

		if ( 'student_registration' == $email_args['email_type'] ) {
			throw new Exception( 'Deprecated: Use CoursePress_Data_Student::send_registration() instead.' );
		}

		if ( 'enrollment_confirmation' == $email_args['email_type'] ) {
			throw new Exception( 'Deprecated: Use CoursePress_Data_Course::enroll_student() instead.' );
		}

		if ( 'student_invitation' == $email_args['email_type'] ) {
			throw new Exception( 'Deprecated: Use CoursePress_Data_Course::send_invitation() instead.' );
		}

		if ( 'instructor_invitation' == $email_args['email_type'] ) {
			throw new Exception( 'Deprecated: Use CoursePress_Data_Instructor::send_invitation() instead.' );
		}
	}
}

if ( ! function_exists( 'coursepress_unit_pages' ) ) {
	function coursepress_unit_pages( $unit_id, $unit_pagination = false ) {
		_doing_it_wrong(
			'coursepress_unit_pages',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		if ( $unit_pagination ) {
			$args = array(
				'post_type' => 'module',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'post_parent' => $unit_id,
				'meta_key' => 'module_page',
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
			);

			$modules = get_posts( $args );
			$module_id = isset( $modules[0] ) ? $modules[0]->ID : 0;

			if ( $module_id > 0 ) {
				$pages_num = count( get_post_meta( $unit_id, 'page_title', true ) );
				//$pages_num = get_post_meta( $module_id, 'module_page', true );
			} else {
				$pages_num = 1;
			}
		} else {
			$pages_num = 1;

			$modules = Unit_Module::get_modules( $unit_id );

			foreach ( $modules as $mod ) {
				if ( Unit_Module::get_module_type( $mod->ID ) == 'page_break_module' ) {
					$pages_num ++;
				}
			}
		}

		return $pages_num;
	}
}

if ( ! function_exists( 'coursepress_unit_module_pagination_ellipsis' ) ) {
	function coursepress_unit_module_pagination_ellipsis( $unit_id, $pages_num ) {
		_doing_it_wrong(
			'coursepress_unit_module_pagination_ellipsis',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		global $wp, $wp_query, $paged, $coursepress_modules;

		if ( ! isset( $unit_id ) || ! is_singular() ) { return; }

		$paged = $wp->query_vars['paged'] ? absint( $wp->query_vars['paged'] ) : 1;
		$max = intval( $pages_num ); //number of page-break modules + 1
		$wp_query->max_num_pages = $max;

		if ( $wp_query->max_num_pages <= 1 ) { return; }

		//	Add current page to the array
		if ( $paged >= 1 ) {
			$links[] = $paged;
		}

		//	Add the pages around the current page to the array
		if ( $paged >= 3 ) {
			$links[] = $paged - 1;
			$links[] = $paged - 2;
		}

		if ( ( $paged + 2 ) <= $max ) {
			$links[] = $paged + 2;
			$links[] = $paged + 1;
		}

		echo '<br clear="all"><div class="navigation"><ul>' . "\n";

		//	Previous Post Link
		if ( get_previous_posts_link() ) {
			printf( '<li>%s</li>' . "\n", get_previous_posts_link( '<span class="meta-nav">&larr;</span>' ) );
		}

		//	Link to first page, plus ellipses if necessary
		if ( ! in_array( 1, $links ) ) {
			$class = 1 == $paged ? ' class="active"' : '';

			printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );

			if ( ! in_array( 2, $links ) ) {
				echo '<li>…</li>';
			}
		}

		//	Link to current page, plus 2 pages in either direction if necessary
		sort( $links );

		foreach ( (array) $links as $link ) {
			$class = $paged == $link ? ' class="active"' : '';
			printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
		}

		//	Link to last page, plus ellipses if necessary
		if ( ! in_array( $max, $links ) ) {
			if ( ! in_array( $max - 1, $links ) ) {
				echo '<li>…</li>' . "\n";
			}

			$class = $paged == $max ? ' class="active"' : '';
			printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
		}

		$nextpage = intval( $paged ) + 1;
		//	Next Post Link
		if ( $nextpage <= $pages_num ) {
			$attr = apply_filters( 'next_posts_link_attributes', '' );

			printf( '<li>%s</li>' . "\n", get_next_posts_link( '<span class="meta-nav">&rarr;</span>' ) );
		}

		echo '</ul></div>' . "\n";
	}
}

if ( ! function_exists( 'coursepress_unit_module_pagination' ) ) {
	function coursepress_unit_module_pagination( $unit_id, $pages_num, $check_is_last_page = false ) {
		_doing_it_wrong(
			'coursepress_unit_module_pagination',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		global $wp, $wp_query, $paged, $coursepress_modules, $coursepress;

		if ( ! isset( $unit_id ) ) {// || !is_singular()
			//<br clear="all">
			echo '<div class="navigation module-pagination" id="navigation-pagination"></div>';

			return;
		}

		$paged = isset( $wp->query_vars['paged'] ) ? absint( $wp->query_vars['paged'] ) : 1;

		$max = intval( $pages_num ); //number of page-break modules + 1

		$wp_query->max_num_pages = $max;

		if ( $check_is_last_page ) {
			if ( $max <= 1 || ( $max == $paged ) ) {
				return true;
			} else {
				return false;
			}
		}
		//<br clear="all">
		if ( $wp_query->max_num_pages <= 1 ) {
			echo '<div class="navigation module-pagination" id="navigation-pagination"></div>';

			return;
		}
		//<br clear="all">
		echo '<div class="navigation module-pagination" id="navigation-pagination"><ul>' . "\n";

		for ( $link_num = 1; $link_num <= $max; $link_num ++ ) {
			$enabled = '';
			if ( $coursepress->is_preview( $unit_id, $link_num ) ) {
				$enabled = 'enabled-link';
			} else {
				if ( isset( $_GET['try'] ) ) {
					$enabled = 'disabled-link';
				}
			}

			$class = ( $paged == $link_num ? ' class="active ' . $enabled . '"' : ' class="' . $enabled . '"' );

			printf( '<li%1$s><a href="%2$s">%3$s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link_num ) ), $link_num );
		}

		echo '</ul></div>' . "\n";
	}
}

if ( ! function_exists( 'cp_get_id_by_post_name' ) ) {
	function cp_get_id_by_post_name( $post_name, $post_parent = 0, $type = 'unit' ) {
		_doing_it_wrong(
			'cp_get_id_by_post_name',
			'Deprecated CoursePress function. Use CoursePress_Data_Course::by_name() or CoursePress_Data_Unit::by_name()',
			'2.0'
		);

		global $wpdb;

		$sql = "
		SELECT ID
		FROM {$wpdb->posts}
		WHERE post_name = '%s' AND post_type='%s' AND post_parent=%d
		";
		$id = $wpdb->get_var(
			$wpdb->prepare( $sql, $post_name, $type, $post_parent )
		);

		return $id;
	}
}

if ( ! function_exists( 'cp_unit_uses_new_pagination' ) ) {
	function cp_unit_uses_new_pagination( $unit_id = false ) {
		_doing_it_wrong(
			'coursepress_unit_module_pagination',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		$unit_pagination_meta = get_post_meta( $unit_id, 'unit_pagination', true );

		if ( empty( $unit_pagination_meta ) ) {
			return false;
		} else {
			return true;
		}
	}
}

if ( ! function_exists( 'is_mac' ) ) {
	function is_mac() {
		_doing_it_wrong(
			'is_mac',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);

		$user_agent = getenv( 'HTTP_USER_AGENT' );
		return ( false !== strpos( $user_agent, 'Mac' ) );
	}
}

if ( ! function_exists( 'cp_is_chat_plugin_active' ) ) {
	function cp_is_chat_plugin_active() {
		/*
		 * @note: Keep hidden until otherwise confirmed it is deprecated!!!
		_doing_it_wrong(
			'cp_is_chat_plugin_active',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);
		*/

		$plugins = get_option( 'active_plugins' );

		if ( is_multisite() ) {
			$active_sitewide_plugins = get_site_option( 'active_sitewide_plugins' );
		} else {
			$active_sitewide_plugins = array();
		}

		$required_plugin = 'wordpress-chat/wordpress-chat.php';

		if ( in_array( $required_plugin, $plugins ) || cp_is_plugin_network_active( $required_plugin ) || preg_grep( '/^wordpress-chat.*/', $plugins ) || cp_preg_array_key_exists( '/^wordpress-chat.*/', $active_sitewide_plugins ) ) {
			return true;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'cp_preg_array_key_exists' ) ) {
	function cp_preg_array_key_exists( $pattern, $array ) {
		/*
		_doing_it_wrong(
			'cp_preg_array_key_exists',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);*/

		$keys = array_keys( $array );

		return (int) preg_grep( $pattern, $keys );
	}
}

if ( ! function_exists( 'cp_is_plugin_network_active' ) ) {
	function cp_is_plugin_network_active( $plugin_file ) {
		/*
		_doing_it_wrong(
			'cp_is_plugin_network_active',
			'Deprecated CoursePress function (without replacement)',
			'2.0'
		);*/

		if ( is_multisite() ) {
			$exists = array_key_exists(
				$plugin_file,
				get_site_option( 'active_sitewide_plugins' )
			);

			return $exists;
		}
	}
}

// ----- Functions moved to a class --------------------------------------------

if ( ! function_exists( 'cp_filter_content' ) ) {
	function cp_filter_content( $content, $none_allowed = false ) {
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::filter_content() instead!' );
	}
}

if ( ! function_exists( 'cp_user_can_register' ) ) {
	function cp_user_can_register() {
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::user_can_register() instead!' );
	}
}

if ( ! function_exists( 'cp_get_user_option' ) ) {
	function cp_get_user_option( $option, $user_id = false ) {
		throw new Exception( 'Deprecated: Use WP core function get_user_option() instead!' );
	}
}

if ( ! function_exists( 'cp_get_file_size' ) ) {
	function cp_get_file_size( $url, $human = true ) {
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::get_file_size() instead!' );
	}
}

if ( ! function_exists( 'cp_format_file_size' ) ) {
	function cp_format_file_size( $bytes ) {
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::format_file_size() instead!' );
	}
}

if ( ! function_exists( 'cp_remove_related_videos' ) ) {
	function cp_remove_related_videos( $html, $url, $args ) {
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::remove_related_videos() instead!' );
	}
}

if ( ! function_exists( 'cp_do_attachment_caption' ) ) {
	function cp_do_attachment_caption( $data ) {
		throw new Exception( 'Removed: Internal function that was removed without replacement!' );
	}
}

if ( ! function_exists( 'truncateHtml' ) ) {
	// @codingStandardsIgnoreStart Ignore CamelCase here...
	function truncateHtml( $text, $length = 100, $ending = '...', $exact = false, $consider_html = true ) {
	// @codingStandardsIgnoreEnd
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::truncate_html() instead!' );
	}
}

if ( ! function_exists( 'cp_length' ) ) {
	function cp_length( $text, $excerpt_length ) {
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::truncate_html() instead!' );
	}
}

if ( ! function_exists( 'cp_user_has_role' ) ) {
	function cp_user_has_role( $check_role, $user_id = null ) {
		throw new Exception( 'Deprecated: Use WP core function user_can() instead!' );
	}
}

if ( ! function_exists( 'cp_wp_get_image_extensions' ) ) {
	function cp_wp_get_image_extensions() {
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::get_image_extensions() instead!' );
	}
}

if ( ! function_exists( 'cp_curPageURL' ) ) {
	// @codingStandardsIgnoreStart Ignore CamelCase here...
	function cp_curPageURL() {
	// @codingStandardsIgnoreEnd
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::get_current_url() instead!' );
	}
}

if ( ! function_exists( 'cp_get_userdatabynicename' ) ) {
	function cp_get_userdatabynicename( $user_nicename ) {
		throw new Exception( 'Deprecated: Use WP core function get_user_by() instead!' );
	}
}

if ( ! function_exists( 'cp_cp_get_the_course_excerpt' ) ) {
	function cp_cp_get_the_course_excerpt( $id = false, $length = 55 ) {
		throw new Exception( 'Deprecated: Use the field WP_Post->post_excerpt instead!' );
	}
}

if ( ! function_exists( 'cp_get_the_course_excerpt' ) ) {
	function cp_get_the_course_excerpt( $id = false, $length = 55 ) {
		throw new Exception( 'Deprecated: Use the field WP_Post->post_excerpt instead!' );
	}
}

if ( ! function_exists( 'cp_delete_user_meta_by_key' ) ) {
	function cp_delete_user_meta_by_key( $meta_key ) {
		throw new Exception( 'Deprecated: Use WP core function delete_user_option() instead!' );
	}
}

if ( ! function_exists( 'cp_instructors_avatars_array' ) ) {
	function cp_instructors_avatars_array( $args = array() ) {
		throw new Exception( 'Deprecated: Use CoursePress_Helper_UI::get_user_avatar_array() instead!' );
	}
}

if ( ! function_exists( 'cp_admin_notice' ) ) {
	function cp_admin_notice( $notice, $type = 'updated' ) {
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::get_current_url() instead!' );
	}
}

if ( ! function_exists( 'cp_full_url' ) ) {
	function cp_full_url( $s, $use_forwarded_host = false ) {
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::get_current_url() instead!' );
	}
}

if ( ! function_exists( 'cp_url_origin' ) ) {
	function cp_url_origin( $s, $use_forwarded_host = false ) {
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::get_current_url() instead!' );
	}
}

if ( ! function_exists( 'cp_admin_ajax_url' ) ) {
	function cp_admin_ajax_url() {
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::get_ajax_url() instead!' );
	}
}

// ----- Need to be moved to a class and throw Exception here ------------------

if ( ! function_exists( 'cp_messaging_get_unread_messages_count' ) ) {
	function cp_messaging_get_unread_messages_count() {
		global $wpdb;

		$sql = '
		SELECT COUNT(1)
		FROM ' . $wpdb->base_prefix . 'messages
		WHERE message_to_user_ID = %d AND message_status = %s
		';

		$tmp_unread_message_count = $wpdb->get_var(
			$wpdb->prepare(
				$sql,
				get_current_user_id(),
				'unread'
			)
		);

		return $tmp_unread_message_count;
	}
}

if ( ! function_exists( 'cp_can_see_unit_draft' ) ) {
	/**
	 * Check if the current user can see Course Drafts. By default only admin
	 * users can see drafts.
	 *
	 * @todo Move this function into the Capability class and deprecate this function!
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	function cp_can_see_unit_draft() {
		if ( ! is_user_logged_in() ) { return false; }
		if ( current_user_can( 'manage_options' ) ) { return true; }
		if ( current_user_can( 'coursepress_create_course_unit_cap' ) ) { return true; }

		return false;
	}
}

if ( ! function_exists( 'cp_set_last_visited_unit_page' ) ) {

	/**
	 * Save the given page-ID as "last visited unit-page" of the specified user.
	 *
	 * @todo  Migrate and use deprecated class.student.completion.php!
	 *        This function/logic is already in that file...
	 *
	 * @since  1.0.0
	 * @param  int $unit_id Unit ID.
	 * @param  int $page_id The page ID.
	 * @param  int $student_id WP User ID.
	 */
	function cp_set_last_visited_unit_page( $unit_id, $page_id, $student_id = 0 ) {
		$unit_id = (int) $unit_id;
		if ( ! $unit_id ) { return false; }

		if ( ! $student_id ) { $student_id = get_current_user_ID(); }

		$global_option = ! is_multisite();
		update_user_option(
			$student_id,
			'last_visited_unit_' . $unit_id . '_page',
			$page_id,
			$global_option
		);
	}
}

if ( ! function_exists( 'cp_get_last_visited_unit_page' ) ) {
	/**
	 * Return page ID of the last unit page that was viewed by given student.
	 *
	 * @todo  Migrate and use deprecated class.student.completion.php!
	 *        This function/logic is already in that file...
	 *
	 * @since  1.0.0
	 * @param  int  $unit_id Unit ID.
	 * @param  bool $student_id WP User ID.
	 * @return int Unit page ID.
	 */
	function cp_get_last_visited_unit_page( $unit_id, $student_id = 0 ) {
		$unit_id = (int) $unit_id;
		if ( ! $unit_id ) { return false; }

		if ( ! $student_id ) { $student_id = get_current_user_ID(); }

		$global_option = ! is_multisite();
		$last_visited_unit_page = get_user_option(
			'last_visited_unit_' . $unit_id . '_page',
			$student_id
		);

		if ( $last_visited_unit_page ) {
			return (int) $last_visited_unit_page;
		} else {
			return 1;
		}
	}
}

if ( ! function_exists( 'cp_get_number_of_unit_pages_visited' ) ) {

	/**
	 * Return page ID of the last unit page that was viewed by given student.
	 *
	 * @todo  Migrate and use deprecated class.student.completion.php!
	 *        This function/logic is already in that file...
	 *
	 * @since  1.0.0
	 * @param  int  $unit_id unknown.
	 * @param  bool $student_id Student ID.
	 * @return int Number of visited unit-pages.
	 */
	function cp_get_number_of_unit_pages_visited( $unit_id, $student_id = 0 ) {
		$unit_id = (int) $unit_id;
		if ( ! $unit_id ) { return false; }

		if ( ! $student_id ) { $student_id = get_current_user_ID(); }

		$visited_pages = get_user_option(
			'visited_unit_pages_' . $unit_id . '_page',
			$student_id
		);

		if ( $visited_pages ) {
			return count( explode( ',', $visited_pages ) ) - 1;
		} else {
			return 0;
		}
	}
}

if ( ! function_exists( 'cp_is_course_visited' ) ) {

	/**
	 * Check if a certain course was visited by the specified student.
	 *
	 * @todo  Migrate to the same class as deprecated class.student.completion.php!
	 *
	 * @since  1.0.0
	 * @param  int $course_id The course ID.
	 * @param  int $student_id WP User ID.
	 */
	function cp_is_course_visited( $course_id, $student_id = false ) {
		if ( ! $student_id ) {
			$student_id = get_current_user_ID();
		}

		$visited_courses = get_user_option(
			'visited_course_units_' . $course_id,
			$student_id
		);

		if ( $visited_courses ) {
			$visited_courses = ( explode( ',', $visited_courses ) );
			if ( is_array( $visited_courses ) ) {
				if ( in_array( $course_id, $visited_courses ) ) {
					return true;
				} else {
					return false;
				}
			} else {
				if ( $visited_courses == $course_id ) {
					return true;
				} else {
					return false;
				}
			}
		}
		return false;
	}
}

if ( ! function_exists( 'cp_set_visited_course' ) ) {

	/**
	 * Mark the given course as "visited" for the specified user.
	 *
	 * @todo  Migrate and use deprecated class.student.completion.php!
	 *        This function/logic is already in that file...
	 *
	 * @since  1.0.0
	 * @param  int $unit_id A unit-ID of the course.
	 * @param  int $student_id WP User ID.
	 */
	function cp_set_visited_course( $unit_id, $student_id = 0 ) {
		$unit_id = (int) $unit_id;
		if ( ! $unit_id ) { return false; }

		if ( ! $student_id ) {  $student_id = get_current_user_ID(); }

		$course_id = wp_get_post_parent_id( (int) $unit_id );
		$visited_courses = get_user_option(
			'visited_course_units_' . $course_id,
			$student_id
		);

		if ( ! is_string( $visited_courses ) ) {
			$visited_courses = '';
		}

		$visited_courses = explode( ',', $visited_courses );

		if ( ! in_array( $course_id, $visited_courses ) ) {
			$visited_courses[] = $course_id;
			$visited_courses = implode( ',', $visited_courses );

			$global_option = ! is_multisite();
			update_user_option(
				$student_id,
				'visited_course_units_' . $course_id,
				$visited_courses,
				$global_option
			);
		}
	}
}

if ( ! function_exists( 'cp_set_visited_unit_page' ) ) {

	/**
	 * Mark a single unit-page as visited by the specified user.
	 *
	 * @todo  Migrate and use deprecated class.student.completion.php!
	 *        This function/logic is already in that file...
	 *
	 * @since  1.0.0
	 * @param  int $unit_id Unit ID.
	 * @param  int $page_num The page ID.
	 * @param  int $student_id WP User ID.
	 */
	function cp_set_visited_unit_page( $unit_id, $page_num, $student_id = 0 ) {
		$unit_id = (int) $unit_id;
		if ( ! $unit_id ) { return false; }

		if ( ! $student_id ) { $student_id = get_current_user_ID(); }

		$course_id = wp_get_post_parent_id( (int) $unit_id );

		CoursePress_Data_Student::visited_page( $student_id, $course_id, $unit_id, $page_num );

		// Legacy but still needed.

		$visited_pages = get_user_option(
			'visited_unit_pages_' . $unit_id . '_page',
			$student_id
		);

		if ( ! is_string( $visited_pages ) ) {
			$visited_pages = '';
		}

		$visited_pages = explode( ',', $visited_pages );

		if ( ! in_array( $page_num, $visited_pages ) ) {
			$visited_pages[] = $page_num;
			$visited_pages = implode( ',', $visited_pages );

			$global_option = ! is_multisite();
			update_user_option(
				$student_id,
				'visited_unit_pages_' . $unit_id . '_page',
				$visited_pages,
				$global_option
			);
			cp_set_visited_course(
				$unit_id,
				$student_id
			);
			cp_set_last_visited_unit_page(
				$unit_id,
				$page_num,
				$student_id
			);
		}
	}
}

