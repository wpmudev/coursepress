<?php

class CoursePress_Model_VirtualPage {

	var $slug = null;
	var $title = null;
	var $content = null;
	var $author = null;
	var $date = null;
	var $type = null;
	var $comment_status = 'closed';
	var $ID = '';

	public static $the_post_id;

	function __construct( $args ) {
		if ( ! isset( $args['slug'] ) ) {
			throw new Exception( 'No slug given for the virtual page' );
		}

		$this->show_title  = isset( $args['show_title'] ) ? $args['show_title'] : true;
		$this->slug        = $args['slug'];
		$this->title       = isset( $args['title'] ) ? $args['title'] : '';
		$this->content     = isset( $args['content'] ) ? $args['content'] : '';
		$this->author      = isset( $args['author'] ) ? $args['author'] : 1;
		$this->date        = isset( $args['date'] ) ? $args['date'] : current_time( 'mysql' );
		$this->dategmt     = isset( $args['date'] ) ? $args['date'] : current_time( 'mysql', 1 );
		$this->type        = isset( $args['type'] ) ? $args['type'] : 'page';
		$this->post_parent = isset( $args['post_parent'] ) ? $args['post_parent'] : '';
		$this->ID          = isset( $args['ID'] ) ? $args['ID'] : '';

		$this->is_page        = isset( $args['is_page'] ) ? $args['is_page'] : true;
		$this->is_singular    = isset( $args['is_singular'] ) ? $args['is_singular'] : true;
		$this->is_archive     = isset( $args['is_archive'] ) ? $args['is_archive'] : false;
		$this->comment_status = isset( $args['comment_status'] ) ? $args['comment_status'] : 'closed';
		$this->post_type      = 'public';

		add_filter( 'the_posts', array( &$this, 'virtualPage' ) );
		add_filter( 'the_title', array( &$this, 'hide_title' ), 10, 2 );
		remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

	}

	// filter to create virtual page content
	function virtualPage( $posts ) {
		global $wp, $wp_query, $comment;

		// This will be 0 if its a virtual page.
		if ( 0 < $wp_query->post_count ) {
			return $posts;
		}

		$virtual_post = '';

		// Try a real post first and then override it with args
		if( ! empty( $this->ID ) ) {
			CoursePress_Model_VirtualPage::$the_post_id = $this->ID;
			$virtual_post                               = get_post( $this->ID );
			$virtual_post->post_content                 = $this->content;
			$virtual_post->post_title                   = $this->title;
			$virtual_post->post_parent                  = $this->post_parent;
			$virtual_post->post_type                    = $this->type;
		}

		if( empty( $virtual_post ) ) {

			$virtual_post = new stdClass;

			$virtual_post->ID                    = $this->ID;
			$virtual_post->post_author           = $this->author;
			$virtual_post->post_date             = $this->date;
			$virtual_post->post_date_gmt         = $this->dategmt;
			$virtual_post->post_content          = $this->content;
			$virtual_post->post_title            = $this->title;
			$virtual_post->post_excerpt          = '';
			$virtual_post->post_status           = 'publish';
			$virtual_post->comment_status        = $this->comment_status;
			$virtual_post->ping_status           = 'closed';
			$virtual_post->post_password         = '';
			$virtual_post->post_name             = $this->slug;
			$virtual_post->to_ping               = '';
			$virtual_post->pinged                = '';
			$virtual_post->modified              = $virtual_post->post_date;
			$virtual_post->modified_gmt          = $virtual_post->post_date_gmt;
			$virtual_post->post_content_filtered = '';
			$virtual_post->post_parent           = 0;
			$virtual_post->guid                  = get_home_url( '/' . $this->slug );
			$virtual_post->menu_order            = 0;
			$virtual_post->post_type             = $this->type;
			$virtual_post->post_mime_type        = '';
			$virtual_post->post_parent           = $this->post_parent;

			// setting this to -1 lets wordpress load comment 1... it uses the absolute value.
			$virtual_post->comment_count = 0;
		}

		$posts = array( $virtual_post );

		$wp_query->is_page     = $this->is_page;
		$wp_query->is_singular = $this->is_singular;
		$wp_query->is_home     = false;
		$wp_query->is_archive  = false;
		$wp_query->is_category = false;
		unset( $wp_query->query['error'] );
		$wp_query->query_vars['error'] = '';
		$wp_query->is_404              = false;

		return ( $posts );
	}

	function hide_title( $title, $id ) {

		// Only for this post!
		if( $this->ID !== $id && ! empty( $id ) ) {
			return $title;
		}

		if ( $this->show_title ) {
			return $title;
		} else {
			return '';
		}
	}

}