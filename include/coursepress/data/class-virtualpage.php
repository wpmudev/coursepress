<?php
/**
 * Handles the virtual pages used by CoursePress.
 *
 * When the coursepress theme is used, these virtual pages are not used because
 * the theme already comes with special template files for CoursePress pages.
 *
 * @since  2.0.0
 *
 * @package WordPress
 * @subpackage CoursePress
 */
class CoursePress_Data_VirtualPage {
	protected $callback = false;
	protected $context = '';

	/**
	 * The slug of our virtual page.
	 *
	 * @var string
	 */
	protected $slug = '';

	/**
	 * The title of our virtual page.
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * The page contents of our virtual page.
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * The page-owner (user-ID) of the virtual page.
	 *
	 * @var int
	 */
	protected $author = 0;

	/**
	 * The parent page-ID of the virtual page.
	 *
	 * @var int
	 */
	protected $post_parent = 0;

	/**
	 * Creation date of the virtual page.
	 *
	 * @var string
	 */
	protected $date = '';

	/**
	 * The simulated post-type.
	 * Usually this is set to a virtual post-type, e.g. 'coursepress_instructor'.
	 *
	 * @var string
	 */
	protected $type = 'page';

	/**
	 * Comment status (closed or open)
	 *
	 * @var string
	 */
	protected $comment_status = 'closed';

	/**
	 * The internal ID of the virtual page.
	 *
	 * @var int
	 */
	protected $ID = 0;

	/**
	 * The original post-ID of the WP page.
	 *
	 * @var int
	 */
	protected $orig_id = 0;

	/**
	 * Flag if we should display the title.
	 *
	 * @var bool
	 */
	protected $show_title = true;

	/**
	 * Simulated `is_page` flag for the WP_Query object.
	 *
	 * @var bool
	 */
	protected $is_page = true;

	/**
	 * Simulated `is_singular` flag for the WP_Query object.
	 *
	 * @var bool
	 */
	protected $is_singular = true;

	/**
	 * Simulated `is_archive` flag for the WP_Query object.
	 *
	 * @var bool
	 */
	protected $is_archive = false;

	/**
	 * Initialize the Virtual page object.
	 *
	 * Note: There can be only one virtual page per request.
	 * TODO This should be a singleton or static method!
	 *
	 * @since 2.0.0
	 * @param array $args Constructor options.
	 */
	public function __construct( $args ) {
		global $wp_query, $post;

		if ( ! isset( $args['slug'] ) ) {
			throw new Exception( 'No slug given for the virtual page' );
		}

		$this->slug = $args['slug'];
		$this->date = current_time( 'mysql' );
		$this->orig_id = get_the_ID();

		if ( ! empty( $args ) ) {
			foreach ( $args as $key => $value ) {
				$this->$key = $value;
			}
		}

		if ( isset( $args['show_title'] ) ) {
			$this->show_title = $args['show_title'];
		}
		if ( isset( $args['title'] ) ) {
			$this->title = $args['title'];
		}
		if ( isset( $args['content'] ) ) {
			$this->content = $args['content'];
		}
		if ( isset( $args['author'] ) ) {
			$this->author = $args['author'];
		}
		if ( isset( $args['date'] ) ) {
			$this->date = $args['date'];
		}
		if ( isset( $args['type'] ) ) {
			$this->type = $args['type'];
		}
		if ( isset( $args['ID'] ) ) {
			$this->ID = $args['ID'];
		}

		if ( isset( $args['post_parent'] ) ) {
			$this->post_parent = $args['post_parent'];
		}
		if ( isset( $args['comment_status'] ) ) {
			$this->comment_status = $args['comment_status'];
		}

		if ( isset( $args['is_page'] ) ) {
			$this->is_page = $args['is_page'];
		}
		if ( isset( $args['is_singular'] ) ) {
			$this->is_singular = $args['is_singular'];
			$post = get_post( $args['ID'] );

			// Check status
			if ( 'publish' != $post->post_status && false === CoursePress_Data_Capabilities::can_update_course( $post->ID ) ) {
				return;
			}
		}
		if ( isset( $args['is_archive'] ) ) {
			//$this->is_archive = $args['is_archive'];
			$wp_query->is_single = true;
			$wp_query->is_singular = true;
		}

		// Hook up our virtual page with WP.
		add_filter(
			'the_posts',
			array( $this, 'virtual_page' )
		);
		add_filter(
			'the_title',
			array( $this, 'virtual_title' ),
			10, 2
		);
		add_filter(
			'the_content',
			array( $this, 'virtual_content' )
		);

		add_filter(
			'comments_template',
			array( $this, 'virtual_comments' )
		);

		// Unhook some WP hooks that are conflicting with virtual pages.
		remove_action(
			'wp_head',
			'start_post_rel_link',
			10, 0
		);
		remove_action(
			'wp_head',
			'adjacent_posts_rel_link_wp_head',
			10, 0
		);
	}

	/**
	 * Create a dynamic WP_Post object to reflect the virtual page definition.
	 * Handles the WP filter `the_posts`.
	 *
	 * @since  2.0.0
	 * @param  array $posts Default list of WP_Posts to diwplay.
	 * @return array Modified list, only contains 1 item: Our virtual page.
	 */
	public function virtual_page( $posts ) {
		global $wp_query, $withcomments;
		$virtual_post = false;

		// This will be 0 if its a virtual page.
		if ( 0 < $wp_query->post_count ) {
			return $posts;
		}

		if ( 'closed' == $this->comment_status ) {
			$withcomments = false;
		}

		// Try a real post first and then override it with args
		if ( $this->ID ) {
			CoursePress_Helper_Utility::set_the_post( $this->ID );
			$virtual_post = get_post( $this->ID );
			$virtual_post->post_content = $this->content;
			$virtual_post->post_title = $this->title;
			$virtual_post->post_parent = $this->post_parent;
			$virtual_post->post_type = $this->type;
		}

		// Not using `else` since get_post can also return null...
		if ( ! $virtual_post ) {
			$virtual_post = new stdClass();

			$virtual_post->ID = $this->orig_id;
			$virtual_post->post_author = $this->author;
			$virtual_post->post_date = $this->date;
			$virtual_post->post_date_gmt = $this->date;
			$virtual_post->post_content = $this->content;
			$virtual_post->post_title = $this->title;
			$virtual_post->post_excerpt = '';
			$virtual_post->post_status = 'publish';
			$virtual_post->comment_status = $this->comment_status;
			$virtual_post->ping_status = 'closed';
			$virtual_post->post_password = '';
			$virtual_post->post_name = $this->slug;
			$virtual_post->to_ping = '';
			$virtual_post->pinged = '';
			$virtual_post->modified = $virtual_post->post_date;
			$virtual_post->modified_gmt = $virtual_post->post_date_gmt;
			$virtual_post->post_content_filtered = '';
			$virtual_post->guid = get_home_url( '/' . $this->slug );
			$virtual_post->menu_order = 0;
			$virtual_post->post_type = $this->type;
			$virtual_post->post_mime_type = '';
			$virtual_post->post_parent = $this->post_parent;
		}

		/*
		Set to 0 (not -1; WP uses absint() to sanitize the value).
		This prevents WordPress from displaying any comments.
		*/
		$virtual_post->comment_count = 0;

		$wp_query->is_page = $this->is_page;
		$wp_query->is_singular = $this->is_singular;
		$wp_query->is_archive = $this->is_archive;
		$wp_query->is_home = false;
		$wp_query->is_category = false;
		unset( $wp_query->query['error'] );
		$wp_query->query_vars['error'] = '';
		$wp_query->is_404 = false;

		return array( $virtual_post );
	}

	public function virtual_content( $content ) {
		if ( $this->callback ) {
			$content = call_user_func( $this->callback, $content );
			$content = apply_filters( 'coursepress_view_course', $content, $this->ID, $this->context );
		}

		return $content;
	}

	/**
	 * Set the comments template to an empty file.
	 *
	 * @since  2.0.0
	 * @param  string $template The default template filename from WordPress.
	 * @return string The new template filename.
	 */
	public function virtual_comments( $template ) {
		if ( 'closed' == $this->comment_status ) {
			$template = CoursePress::$path . 'lib/CoursePress/Template/no-comment.php';
		}

		return $template;
	}

	/**
	 * Returns the contents for filter `the_title`
	 *
	 * @since  2.0.0
	 * @param  string $title Default page title by WordPress.
	 * @param  int    $id Page that is processed.
	 * @return string Modified page title.
	 */
	public function virtual_title( $title, $id ) {
		// Only modify title of the virtual page!
		if ( $id && $this->ID != $id ) {
			return $title;
		}
		/**
		 * Prevent menu items
		 */
		if ( is_string( $id ) && preg_match( '/^cp\-/', $id ) ) {
			return $title;
		}
		if ( $this->show_title ) {
			return $title;
		} else {
			return '';
		}
	}
}
