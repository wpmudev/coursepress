<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Course_Search' ) ) {

	class Course_Search {

		var $courses_per_page = 10;
		var $args = array();
		var $is_light = true;
		var $post_type = 'course';
		var $posts = false;

		function __construct( $search_term = '', $page_num = '', $courses_per_page = 10, $category = 0 ) {
			$this->is_light = CoursePress_Capabilities::is_pro() ? false : true;

			if ( $this->is_light ) {
				$page_num               = 1;
				$this->courses_per_page = 2;
			} else {
				if ( $this->courses_per_page !== $courses_per_page ) {
					$this->courses_per_page = $courses_per_page;
				}
			}

			$this->search_term = $search_term;
			$this->raw_page    = ( '' == $page_num ) ? false : (int) $page_num;
			$this->page_num    = (int) ( '' == $page_num ) ? 1 : $page_num;

			$selected_course_order_by_type = get_option( 'course_order_by_type', 'DESC' );
			$selected_course_order_by      = get_option( 'course_order_by', 'post_date' );

			$args = array(
				'posts_per_page' => $this->courses_per_page,
				'offset'         => ( $this->page_num - 1 ) * $this->courses_per_page,
				'post_type'      => $this->post_type,
				'post_status'    => 'any',
			);

			if( ! current_user_can( 'manage_options' ) ) {
				$instructor = new Instructor( get_current_user_id() );
				$instructor_courses = $instructor->get_accessable_courses();

				$args['post__in'] = $instructor_courses;
			}

			if ( $category !== 0 ) {
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'course_category',
						'field'    => 'term_id',
						'terms'    => array( $category ),
					)
				);
			}

			if ( $selected_course_order_by == 'course_order' ) {
				/* FIX FOR 4.1 */
				$args['meta_query'] = array(
					'relation' => 'OR',
					array(
						'key'     => 'course_order',
						'compare' => 'NOT EXISTS'
					),
					array(
						'key'     => 'course_order',
						'compare' => 'EXISTS'
					),
				);
				$args['orderby']    = 'meta_value';
				$args['order']      = $selected_course_order_by_type;
			} else {
				$args['orderby'] = $selected_course_order_by;
				$args['order']   = $selected_course_order_by_type;
			}

			$this->args = $args;
		}

		function Course( $search_term = '', $page_num = '' ) {
			$this->__construct( $search_term, $page_num );
		}

		function get_args() {
			return $this->args;
		}

		function get_results( $count = false ) {
			global $wpdb;

			$offset = ( $this->page_num - 1 ) * $this->courses_per_page;

			if ( $this->search_term !== '' ) {
				$search_args      = $this->args;
				$search_args['s'] = $this->search_term;
				$results          = get_posts( $search_args );
				if ( $count ) {
					return count( $results );
				} else {
					$this->posts = $results;
					return $this->posts;
				}
			} else {
				$this->posts = get_posts( $this->args );
				return $this->posts;
			}
		}

		function unset_course( $key ) {
			if( isset( $this->posts[ $key ] ) ) {
				unset( $this->posts[ $key ] );
			}
		}

		function get_count_of_all_courses( $category = 0 ) {
			$args = array(
				'posts_per_page' => - 1,
				'category'       => '',
				'orderby'        => 'post_date',
				'order'          => 'DESC',
				'post_type'      => $this->post_type,
				'post_status'    => 'any'
			);

			if ( $category !== 0 ) {
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'course_category',
						'field'    => 'term_id',
						'terms'    => array( $category ),
					)
				);
			}

			return count( get_posts( $args, OBJECT ) );
		}

		function page_links( $show_courses_per_page = 10, $course_category = 0 ) {
			$pagination = new CoursePress_Pagination();
			$pagination->Items( $this->get_count_of_all_courses( $course_category ) );
			$pagination->limit( $this->courses_per_page );
			$pagination->parameterName = 'page_num';
			$pagination->nextT         = __( 'Next', 'cp' );
			$pagination->prevT         = __( 'Previous', 'cp' );
			if ( $this->search_term != '' ) {
				$pagination->target( esc_url( "admin.php?page=courses&s=" . $this->search_term ) );
			} else {
				$pagination->target( "admin.php?page=courses&courses_per_page=" . $show_courses_per_page . "&course_category_filter=" . $course_category );
			}
			$pagination->currentPage( $this->page_num );
			$pagination->nextIcon( '&#9658;' );
			$pagination->prevIcon( '&#9668;' );
			$pagination->items_title = __( 'courses', 'cp' );
			$pagination->show();
		}

	}

}
?>