<?php

class CoursePress_View_Admin_Course_Export {

	private static $slug = 'coursepress_export_import';
	private static $title = '';
	private static $menu_title = '';
	private static $courses = array();
	protected $cap = 'coursepress_settings_cap';

	/**
	 * Init.
	 *
	 * @since 2.0.0
	 */
	public static function init() {
		self::$title = __( 'Export', 'coursepress' );
		self::$menu_title = __( 'Export', 'coursepress' );

		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'render_page' ) );
		add_filter( 'coursepress_admin_valid_pages', array( __CLASS__, 'add_valid' ) );
		add_filter( 'coursepress_admin_pages', array( __CLASS__, 'add_page' ) );
		add_action( 'coursepress_settings_page_pre_render_' . self::$slug, array( __CLASS__, 'process_form' ) );

		add_filter( 'coursepress_admin_page_main', array( __CLASS__, 'add_admin_notice' ) );
	}

	/**
	 * Render content page.
	 *
	 * @since 2.0.0
	 */
	public static function render_page() {
		self::_show_message();
		echo '<div class="wrap">';
		echo CoursePress_Helper_UI::get_admin_page_title(
			self::$menu_title,
			self::$menu_title
		);
		echo '</div>';
	}

	/**
	 * Get page slug.
	 *
	 * @since 2.0.0
	 *
	 * @return string Value of private $slug.
	 */
	public static function get_slug() {
		return self::$slug;
	}

	/**
	 * Add page to admin.
	 *
	 * @since 2.0.0
	 *
	 * @param array $pages Pages already existing.
	 * @return array Pages with Export page.
	 */
	public static function add_page( $pages ) {
		$pages[ self::$slug ] = array(
			'title' => self::$title,
			'menu_title' => self::$menu_title,
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			'cap' => apply_filters( 'coursepress_capabilities', 'coursepress_courses_cap' ),
			'parent' => CoursePress_View_Admin_Setting::get_slug(),
		);
		return $pages;
	}

	/**
	 * Add Export page slug to valid CoursePress pages.
	 *
	 * @since 2.0.0
	 *
	 * @param array Array of CoursePress pages.
	 * @return array Array of CoursePress pages extende by Export page slug.
	 */
	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;
		return $valid_pages;
	}

	/**
	 * Process form on Export page.
	 *
	 * @since 2.0.0
	 */
	public static function process_form() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
			return;
		}
		$nonce = $_REQUEST['_wpnonce'];
		if (
			isset( $_REQUEST['courses'] )
			&& wp_verify_nonce( $nonce, 'bulk_action_nonce' )
		) {
			self::$courses = explode( ',', $_REQUEST['courses'] );
			self::_export();
			exit;
		} else if ( isset( $_REQUEST['id'] ) ) {
			$action = sprintf( 'course_export_%d', $_REQUEST['id'] );
			if ( wp_verify_nonce( $nonce, $action ) ) {
				self::$courses = array( $_REQUEST['id'] );
				self::_export();
				exit;
			}
		}
		/**
		 * todo - export all courses
		 */
	}

	/**
	 * Export courses.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private static function _export() {
		/**
		 * sanitize && check rights
		 */
		$user_id = get_current_user_id();
		$course_ids = array();
		foreach ( self::$courses as $course_id ) {
			$course_id = absint( $course_id );
			if ( empty( $course_id ) ) {
				continue;
			}
			if ( CoursePress_Data_Capabilities::can_update_course( $course_id, $user_id ) ) {
				$course_ids[] = $course_id;
			}
		}

		/**
		 * Reddirect with message if there is no courses.
		 */
		if ( empty( $course_ids ) ) {
			$slug = CoursePress_View_Admin_CoursePress::get_slug();
			$url = add_query_arg(
				array(
					'page' => $slug,
					'm' => 'no-courses',
				),
				admin_url( 'admin.php' )
			);
			wp_safe_redirect( $url );
			exit;
		}

		/** Load WordPress export API */
		require_once( ABSPATH . 'wp-admin/includes/export.php' );
		global $wpdb;

		/**
		 * filename
		 */
		$sitename = sanitize_key( get_bloginfo( 'name' ) );
		if ( ! empty( $sitename ) ) {
			$sitename .= '.';
		}
		$date = date( 'Y-m-d' );
		$wp_filename = $sitename . 'coursepress.' . $date;
		if ( 1 == count( $course_ids ) ) {
			$post = get_post( $course_ids[0] );
			if ( $post ) {
				$wp_filename .= '.'.$post->post_name;
			}
		}
		$wp_filename .= '.xml';

		/**
		 * Filter the export filename.
		 *
		 * @since 4.4.0
		 *
		 * @param string $wp_filename The name of the file for download.
		 * @param string $sitename	The site name.
		 * @param string $date		Today's date, formatted.
		 */
		$filename = apply_filters( 'export_wp_filename', $wp_filename, $sitename, $date );

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

		/**
		 * get course_category
		 */
		$name = CoursePress_Data_Course::get_post_category_name();
		$terms = get_terms(
			$name,
			array(
				'hide_empty' => false,
			)
		);
		$unit_ids = CoursePress_Data_Unit::get_unit_ids_by_course_ids( $course_ids );
		$module_ids = CoursePress_Data_Module::get_module_ids_by_unit_ids( $unit_ids );
		$post_ids = array_merge( $course_ids, $unit_ids, $module_ids );

		/**
		 * Wrap given string in XML CDATA tag.
		 *
		 * @since 2.1.0
		 *
		 * @param string $str String to wrap in XML CDATA tag.
		 * @return string
		 */
		function wxr_cdata( $str ) {
			if ( ! seems_utf8( $str ) ) {
				$str = utf8_encode( $str );
			}
			// $str = ent2ncr(esc_html($str));
			$str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';

			return $str;
		}

		/**
		 * Return the URL of the site
		 *
		 * @since 2.5.0
		 *
		 * @return string Site URL.
		 */
		function wxr_site_url() {
			// Multisite: the base URL.
			if ( is_multisite() ) {
				return network_home_url(); } // WordPress (single site): the blog URL.
			else { 				return get_bloginfo_rss( 'url' ); }
		}

		/**
		 * Output a term_name XML tag from a given term object
		 *
		 * @since 2.9.0
		 *
		 * @param object $term Term Object
		 */
		function wxr_term_name( $term ) {
			if ( empty( $term->name ) ) {
				return; }

			echo '<wp:term_name>' . wxr_cdata( $term->name ) . '</wp:term_name>';
		}

		/**
		 * Output a term_description XML tag from a given term object
		 *
		 * @since 2.9.0
		 *
		 * @param object $term Term Object
		 */
		function wxr_term_description( $term ) {
			if ( empty( $term->description ) ) {
				return; }

			echo '<wp:term_description>' . wxr_cdata( $term->description ) . '</wp:term_description>';
		}

		/**
		 * Output list of authors with posts
		 *
		 * @since 3.1.0
		 *
		 * @global wpdb $wpdb WordPress database abstraction object.
		 *
		 * @param array $post_ids Array of post IDs to filter the query by. Optional.
		 */
		function wxr_authors_list( array $post_ids = null ) {
			global $wpdb;

			if ( ! empty( $post_ids ) ) {
				$post_ids = array_map( 'absint', $post_ids );
				$and = 'AND ID IN ( ' . implode( ', ', $post_ids ) . ')';
			} else {
				$and = '';
			}

			$authors = array();
			$results = $wpdb->get_results( "SELECT DISTINCT post_author FROM $wpdb->posts WHERE post_status != 'auto-draft' $and" );
			foreach ( (array) $results as $result ) {
				$authors[] = get_userdata( $result->post_author ); }

			$authors = array_filter( $authors );

			foreach ( $authors as $author ) {
				echo "\t<wp:author>";
				echo '<wp:author_id>' . intval( $author->ID ) . '</wp:author_id>';
				echo '<wp:author_login>' . wxr_cdata( $author->user_login ) . '</wp:author_login>';
				echo '<wp:author_email>' . wxr_cdata( $author->user_email ) . '</wp:author_email>';
				echo '<wp:author_display_name>' . wxr_cdata( $author->display_name ) . '</wp:author_display_name>';
				echo '<wp:author_first_name>' . wxr_cdata( $author->first_name ) . '</wp:author_first_name>';
				echo '<wp:author_last_name>' . wxr_cdata( $author->last_name ) . '</wp:author_last_name>';
				echo "</wp:author>\n";
			}
		}

		/**
		 * Output list of taxonomy terms, in XML tag format, associated with a post
		 *
		 * @since 2.3.0
		 */
		function wxr_course_taxonomy( $post ) {
			$taxonomies = get_object_taxonomies( $post->post_type );
			if ( empty( $taxonomies ) ) {
				return; }
			$terms = wp_get_object_terms( $post->ID, $taxonomies );

			foreach ( (array) $terms as $term ) {
				echo "\t\t<category domain=\"{$term->taxonomy}\" nicename=\"{$term->slug}\">" . wxr_cdata( $term->name ) . "</category>\n";
			}
		}

		echo '<?xml version="1.0" encoding="' . get_bloginfo( 'charset' ) . "\" ?>\n";

?>
	<!-- This is a WordPress eXtended RSS file generated by WordPress as an export of your site. -->
	<!-- It contains information about your site's posts, pages, comments, categories, and other content. -->
	<!-- You may use this file to transfer that content from one site to another. -->
	<!-- This file is not intended to serve as a complete backup of your site. -->

	<!-- To import this information into a WordPress site follow these steps: -->
	<!-- 1. Log in to that site as an administrator. -->
	<!-- 2. Go to Tools: Import in the WordPress admin panel. -->
	<!-- 3. Install the "WordPress" importer from the list. -->
	<!-- 4. Activate & Run Importer. -->
	<!-- 5. Upload this file using the form provided on that page. -->
	<!-- 6. You will first be asked to map the authors in this export file to users -->
	<!--	on the site. For each author, you may choose to map to an -->
	<!--	existing user on the site or to create a new user. -->
	<!-- 7. WordPress will then import each of the posts, pages, comments, categories, etc. -->
	<!--	contained in this file into your site. -->

	<?php the_generator( 'export' ); ?>
		<rss version="2.0"
			xmlns:excerpt="http://wordpress.org/export/<?php echo WXR_VERSION; ?>/excerpt/"
			xmlns:content="http://purl.org/rss/1.0/modules/content/"
			xmlns:wfw="http://wellformedweb.org/CommentAPI/"
			xmlns:dc="http://purl.org/dc/elements/1.1/"
			xmlns:wp="http://wordpress.org/export/<?php echo WXR_VERSION; ?>/"
			>

			<channel>
			<title><?php bloginfo_rss( 'name' ); ?></title>
			<link><?php bloginfo_rss( 'url' ); ?></link>
			<description><?php bloginfo_rss( 'description' ); ?></description>
			<pubDate><?php echo date( 'D, d M Y H:i:s +0000' ); ?></pubDate>
			<language><?php bloginfo_rss( 'language' ); ?></language>
			<wp:wxr_version><?php echo WXR_VERSION; ?></wp:wxr_version>
			<wp:base_site_url><?php echo wxr_site_url(); ?></wp:base_site_url>
			<wp:base_blog_url><?php bloginfo_rss( 'url' ); ?></wp:base_blog_url>

			<?php wxr_authors_list( $post_ids ); ?>

		<?php foreach ( $terms as $t ) : ?>
			<wp:term><wp:term_id><?php echo wxr_cdata( $t->term_id ); ?></wp:term_id><wp:term_taxonomy><?php echo wxr_cdata( $t->taxonomy ); ?></wp:term_taxonomy><wp:term_slug><?php echo wxr_cdata( $t->slug ); ?></wp:term_slug><wp:term_parent><?php echo wxr_cdata( $t->parent ? $terms[ $t->parent ]->slug : '' ); ?></wp:term_parent><?php wxr_term_name( $t ); ?><?php wxr_term_description( $t ); ?></wp:term>
			<?php endforeach; ?>
<?php
		/** This action is documented in wp-includes/feed-rss2.php */
		do_action( 'rss2_head' );
?>

<?php if ( $post_ids ) {
	/**
 * @global WP_Query $wp_query
 */
	global $wp_query;

	// Fake being in the loop.
	$wp_query->in_the_loop = true;

	// Fetch 20 posts at a time rather than loading the entire table into memory.
	while ( $next_posts = array_splice( $post_ids, 0, 20 ) ) {
		$where = 'WHERE ID IN (' . join( ',', $next_posts ) . ')';
		$posts = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} $where" );

		// Begin Loop.
		foreach ( $posts as $post ) {
			setup_postdata( $post );
			$is_sticky = is_sticky( $post->ID ) ? 1 : 0;
	?>
	<item>
	<title><?php
		/** This filter is documented in wp-includes/feed.php */
		echo apply_filters( 'the_title_rss', $post->post_title );
	?></title>
		<link><?php the_permalink_rss() ?></link>
		<pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); ?></pubDate>
		<dc:creator><?php echo wxr_cdata( get_the_author_meta( 'login' ) ); ?></dc:creator>
		<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<description></description>
		<content:encoded><?php
		/**
		 * Filter the post content used for WXR exports.
		 *
		 * @since 2.5.0
		 *
		 * @param string $post_content Content of the current post.
		 */
		echo wxr_cdata( apply_filters( 'the_content_export', $post->post_content ) );
	?></content:encoded>
	<excerpt:encoded><?php
		/**
		 * Filter the post excerpt used for WXR exports.
		 *
		 * @since 2.6.0
		 *
		 * @param string $post_excerpt Excerpt for the current post.
		 */
		echo wxr_cdata( apply_filters( 'the_excerpt_export', $post->post_excerpt ) );
	?></excerpt:encoded>
		<wp:post_id><?php echo intval( $post->ID ); ?></wp:post_id>
		<wp:post_date><?php echo wxr_cdata( $post->post_date ); ?></wp:post_date>
		<wp:post_date_gmt><?php echo wxr_cdata( $post->post_date_gmt ); ?></wp:post_date_gmt>
		<wp:comment_status><?php echo wxr_cdata( $post->comment_status ); ?></wp:comment_status>
		<wp:ping_status><?php echo wxr_cdata( $post->ping_status ); ?></wp:ping_status>
		<wp:post_name><?php echo wxr_cdata( $post->post_name ); ?></wp:post_name>
		<wp:status><?php echo wxr_cdata( $post->post_status ); ?></wp:status>
		<wp:post_parent><?php echo intval( $post->post_parent ); ?></wp:post_parent>
		<wp:menu_order><?php echo intval( $post->menu_order ); ?></wp:menu_order>
		<wp:post_type><?php echo wxr_cdata( $post->post_type ); ?></wp:post_type>
		<wp:post_password><?php echo wxr_cdata( $post->post_password ); ?></wp:post_password>
		<wp:is_sticky><?php echo intval( $is_sticky ); ?></wp:is_sticky>
<?php	if ( $post->post_type == 'attachment' ) : ?>
		<wp:attachment_url><?php echo wxr_cdata( wp_get_attachment_url( $post->ID ) ); ?></wp:attachment_url>
<?php 	endif; ?>
<?php 	wxr_course_taxonomy( $post ); ?>
<?php	$postmeta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d", $post->ID ) );
foreach ( $postmeta as $meta ) :
	/**
	 * Filter whether to selectively skip post meta used for WXR exports.
	 *
	 * Returning a truthy value to the filter will skip the current meta
	 * object from being exported.
	 *
	 * @since 3.3.0
	 *
	 * @param bool   $skip	 Whether to skip the current post meta. Default false.
	 * @param string $meta_key Current meta key.
	 * @param object $meta	 Current meta object.
	 */
	if ( apply_filters( 'wxr_export_skip_postmeta', false, $meta->meta_key, $meta ) ) {
		continue; }
?>
		<wp:postmeta>
			<wp:meta_key><?php echo wxr_cdata( $meta->meta_key ); ?></wp:meta_key>
			<wp:meta_value><?php echo wxr_cdata( $meta->meta_value ); ?></wp:meta_value>
		</wp:postmeta>
<?php	endforeach;

$_comments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved <> 'spam'", $post->ID ) );
$comments = array_map( 'get_comment', $_comments );
foreach ( $comments as $c ) : ?>
		<wp:comment>
			<wp:comment_id><?php echo intval( $c->comment_ID ); ?></wp:comment_id>
			<wp:comment_author><?php echo wxr_cdata( $c->comment_author ); ?></wp:comment_author>
			<wp:comment_author_email><?php echo wxr_cdata( $c->comment_author_email ); ?></wp:comment_author_email>
			<wp:comment_author_url><?php echo esc_url_raw( $c->comment_author_url ); ?></wp:comment_author_url>
			<wp:comment_author_IP><?php echo wxr_cdata( $c->comment_author_IP ); ?></wp:comment_author_IP>
			<wp:comment_date><?php echo wxr_cdata( $c->comment_date ); ?></wp:comment_date>
			<wp:comment_date_gmt><?php echo wxr_cdata( $c->comment_date_gmt ); ?></wp:comment_date_gmt>
			<wp:comment_content><?php echo wxr_cdata( $c->comment_content ) ?></wp:comment_content>
			<wp:comment_approved><?php echo wxr_cdata( $c->comment_approved ); ?></wp:comment_approved>
			<wp:comment_type><?php echo wxr_cdata( $c->comment_type ); ?></wp:comment_type>
			<wp:comment_parent><?php echo intval( $c->comment_parent ); ?></wp:comment_parent>
			<wp:comment_user_id><?php echo intval( $c->user_id ); ?></wp:comment_user_id>
<?php		$c_meta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->commentmeta WHERE comment_id = %d", $c->comment_ID ) );
foreach ( $c_meta as $meta ) :
	/**
	 * Filter whether to selectively skip comment meta used for WXR exports.
	 *
	 * Returning a truthy value to the filter will skip the current meta
	 * object from being exported.
	 *
	 * @since 4.0.0
	 *
	 * @param bool   $skip	 Whether to skip the current comment meta. Default false.
	 * @param string $meta_key Current meta key.
	 * @param object $meta	 Current meta object.
	 */
	if ( apply_filters( 'wxr_export_skip_commentmeta', false, $meta->meta_key, $meta ) ) {
		continue;
	}
?>
			<wp:commentmeta>
				<wp:meta_key><?php echo wxr_cdata( $meta->meta_key ); ?></wp:meta_key>
				<wp:meta_value><?php echo wxr_cdata( $meta->meta_value ); ?></wp:meta_value>
			</wp:commentmeta>
<?php		endforeach; ?>
		</wp:comment>
<?php	endforeach; ?>
	</item>
<?php
		}
	}
} ?>
</channel>
</rss>
<?php
	}

	/**
	 * Add admin notice message.
	 *
	 * @since 2.0.0
	 *
	 * @param string $content Key for message.
	 * @return string Admin notice message.
	 */
	public static function add_admin_notice( $content ) {
		if ( ! isset( $_REQUEST['m'] ) ) {
			return $content;
		}
		switch ( $_REQUEST['m'] ) {
			case 'no-courses':
				$content = CoursePress_Helper_UI::admin_notice(
					__( 'Export Courses: please select some courses first.', 'coursepress' ),
					'error'
				).$content;
			break;
		}
		return $content;
	}
}
