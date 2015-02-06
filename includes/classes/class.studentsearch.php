<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Student_Search' ) ) {

	class Student_Search extends WP_User_Query {

		var $users_per_page = 25;
		var $search_errors = false;
		var $additional_url_args = array();

		function __construct(
			$search_term = '', $page_num = '', $search_args = array(), $meta_args = array(),
			$additional_url_args = array()
		) {
			global $wpdb;

			$this->additional_url_args = $additional_url_args;

			if ( ! empty( $search_args['users_per_page'] ) && is_numeric( $search_args['users_per_page'] ) ) {
				$this->users_per_page = $search_args['users_per_page'];
			}

			$this->search_term = $search_term;
			$this->raw_page    = ( '' == $page_num ) ? false : (int) $page_num;
			$this->page_num    = (int) ( '' == $page_num ) ? 1 : $page_num;

			$args = array(
				'search' => $this->search_term,
				'number' => $this->users_per_page,
				'offset' => ( $this->page_num - 1 ) * $this->users_per_page,
				/* 'fields' => 'all_with_meta' */
			);

			$search_args['meta_key']   = 'role'; //( isset( $search_args['meta_key'] ) ? $search_args['meta_key'] : '' );
			$search_args['meta_value'] = 'student'; //( isset( $search_args['meta_value'] ) ? $search_args['meta_value'] : '' );

			if ( ! empty( $meta_args ) ) {
				$meta_args['number'] = $this->users_per_page;
				$meta_args['offset'] = ( $this->page_num - 1 ) * $this->users_per_page;
				$args                = $meta_args;
			}

			if ( is_multisite() ) {
				$args['meta_key'] = $wpdb->prefix . 'role';
			}

			$args['blog_id'] = get_current_blog_id();

			$this->query_vars = wp_parse_args( $args, array(
				//'role' => 'student',
				'meta_value'     => $search_args['meta_value'],
				'meta_compare'   => '',
				'include'        => array(),
				'exclude'        => array(),
				'search'         => '',
				'search_columns' => array(),
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'offset'         => ( $this->page_num - 1 ) * $this->users_per_page,
				'number'         => '',
				'count_total'    => true,
				'fields'         => 'all_with_meta',
				'who'            => ''
			) );


			add_action( 'pre_user_query', array( &$this, 'add_first_and_last' ) );

			parent::prepare_query();
			$this->query();
			$this->do_paging();
		}

		function Student_Search( $search_term = '', $page_num = '' ) {
			$this->__construct( $search_term, $page_num );
		}

		function do_paging() {

			$this->total_users_for_query = $this->get_total();

			if ( $this->total_users_for_query > $this->users_per_page ) { // pagination required
				if ( ! empty( $this->search_term ) ) {
					$args['s'] = urlencode( $this->search_term );
				}

				$this->paging_text = paginate_links( array(
					'total'    => ceil( $this->total_users_for_query / $this->users_per_page ),
					'current'  => $this->page_num,
					'base'     => 'admin.php?page=students&%_%',
					'format'   => 'userspage=%#%',
					'add_args' => isset( $args ) ? $args : ''
				) );

				if ( $this->paging_text ) {
					$this->paging_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', 'cp' ) . '</span>%s', number_format_i18n( ( $this->page_num - 1 ) * $this->users_per_page + 1 ), number_format_i18n( min( $this->page_num * $this->users_per_page, $this->total_users_for_query ) ), number_format_i18n( $this->total_users_for_query ), $this->paging_text
					);
				}
			}
		}

		function page_links() {
			$pagination = new CoursePress_Pagination();
			$pagination->Items( $this->get_total() );
			$pagination->limit( $this->users_per_page );
			$pagination->parameterName = 'page_num';
			$pagination->nextT         = __( 'Next', 'cp' );
			$pagination->prevT         = __( 'Previous', 'cp' );
			$pagination->target( esc_url( "admin.php?page=" . ( isset( $_GET['page'] ) ? $_GET['page'] : 'students' ) . '&' . http_build_query( $this->additional_url_args ) ) );
			$pagination->currentPage( $this->page_num );
			$pagination->nextIcon( '&#9658;' );
			$pagination->prevIcon( '&#9668;' );
			$pagination->items_title = __( 'students', 'cp' );
			$pagination->show();
		}

		function add_first_and_last( $user_search ) {
			global $wpdb;
			$vars = $user_search->query_vars;

			if ( ! is_null( $vars['search'] ) && ! empty( $vars['search'] ) ) {
				$search = preg_replace( '/^\*/', '', $vars['search'] );
				$search = preg_replace( '/\*$/', '', $search );

				$user_search->query_from .= " INNER JOIN {$wpdb->usermeta} m1 ON " .
				                            "{$wpdb->users}.ID=m1.user_id AND (m1.meta_key='first_name')";
				$user_search->query_from .= " INNER JOIN {$wpdb->usermeta} m2 ON " .
				                            "{$wpdb->users}.ID=m2.user_id AND (m2.meta_key='last_name')";

				$names_where = $wpdb->prepare( "m1.meta_value LIKE '%s' OR m2.meta_value LIKE '%s'", "%{$search}%", "%{$search}%" );

				$user_search->query_where = str_replace( 'WHERE 1=1 AND (', "WHERE 1=1 AND ({$names_where} OR ", $user_search->query_where );
			}
		}

	}

}
?>