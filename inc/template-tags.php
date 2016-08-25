<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package CoursePress
 */
if ( !function_exists( 'coursepress_paging_nav' ) ) :

	/**
	 * Display navigation to next/previous set of posts when applicable.
	 *
	 * @return void
	 */
	function coursepress_paging_nav() {
		// Don't print empty markup if there's only one page.
		if ( $GLOBALS[ 'wp_query' ]->max_num_pages < 2 ) {
			return;
		}
		?>
		<nav class="navigation paging-navigation" role="navigation">
			<h1 class="screen-reader-text"><?php _e( 'Posts navigation', 'cp' ); ?></h1>
			<div class="nav-links">

				<?php if ( get_next_posts_link() ) : ?>
					<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'cp' ) ); ?></div>
				<?php endif; ?>

				<?php if ( get_previous_posts_link() ) : ?>
					<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'cp' ) ); ?></div>
				<?php endif; ?>

			</div><!-- .nav-links -->

		</nav><!-- .navigation -->
		<?php
	}

endif;

if ( !function_exists( 'coursepress_post_nav' ) ) :

	/**
	 * Display navigation to next/previous post when applicable.
	 *
	 * @return void
	 */
	function coursepress_post_nav() {
		// Don't print empty markup if there's nowhere to navigate.
		$previous	 = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
		$next		 = get_adjacent_post( false, '', false );

		if ( !$next && !$previous ) {
			return;
		}
		?>
		<nav class="navigation post-navigation" role="navigation">
			<h1 class="screen-reader-text"><?php _e( 'Post navigation', 'cp' ); ?></h1>
			<div class="nav-links">

				<?php previous_post_link( '%link', _x( '<span class="meta-nav">&larr;</span> %title', 'Previous post link', 'cp' ) ); ?>
				<?php next_post_link( '%link', _x( '%title <span class="meta-nav">&rarr;</span>', 'Next post link', 'cp' ) ); ?>

			</div><!-- .nav-links -->
		</nav><!-- .navigation -->
		<?php
	}

endif;

if ( !function_exists( 'coursepress_comment' ) ) :

	/**
	 * Template for comments and pingbacks.
	 *
	 * Used as a callback by wp_list_comments() for displaying the comments.
	 */
	function coursepress_comment( $comment, $args, $depth ) {
		$GLOBALS[ 'comment' ] = $comment;

		if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) :
			?>

			<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
				<div class="comment-body">
					<?php _e( 'Pingback:', 'cp' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( 'Edit', 'cp' ), '<span class="edit-link">', '</span>' ); ?>
				</div>

			<?php else : ?>

			<li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args[ 'has_children' ] ) ? '' : 'parent'  ); ?>>
				<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
					<footer class="comment-meta">
						<div class="comment-author vcard">
							<?php
							if ( 0 != $args[ 'avatar_size' ] ) {
								echo get_avatar( $comment, $args[ 'avatar_size' ] );
							}
							?>
							<?php printf( __( '%s <span class="says">says:</span>', 'cp' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
						</div><!-- .comment-author -->

						<div class="comment-metadata">
							<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
								<time datetime="<?php comment_time( 'c' ); ?>">
									<?php printf( _x( '%1$s at %2$s', '1: date, 2: time', 'cp' ), get_comment_date(), get_comment_time() ); ?>
								</time>
							</a>
							<?php edit_comment_link( __( 'Edit', 'cp' ), '<span class="edit-link">', '</span>' ); ?>
						</div><!-- .comment-metadata -->

						<?php if ( '0' == $comment->comment_approved ) : ?>
							<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'cp' ); ?></p>
						<?php endif; ?>
					</footer><!-- .comment-meta -->

					<div class="comment-content">
						<?php comment_text(); ?>
					</div><!-- .comment-content -->

					<?php
					comment_reply_link( array_merge( $args, array(
						'add_below'	 => 'div-comment',
						'depth'		 => $depth,
						'max_depth'	 => $args[ 'max_depth' ],
						'before'	 => '<div class="reply">',
						'after'		 => '</div>',
					) ) );
					?>
				</article><!-- .comment-body -->

			<?php
			endif;
		}

	endif; // ends check for coursepress_comment()

	if ( !function_exists( 'coursepress_discussion_comment' ) ) :

		/**
		 * Template for comments and pingbacks.
		 *
		 * Used as a callback by wp_list_comments() for displaying the comments.
		 */
		function coursepress_discussion_comment( $comment, $args, $depth ) {
			$GLOBALS[ 'comment' ] = $comment;

			if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) :
				?>

			<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
				<div class="comment-body">
					<?php _e( 'Pingback:', 'cp' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( 'Edit', 'cp' ), '<span class="edit-link">', '</span>' ); ?>
				</div>

			<?php else : ?>

			<li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args[ 'has_children' ] ) ? '' : 'parent'  ); ?>>
				<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
					<footer class="comment-meta">
						<div class="discussion-archive-single-meta">
							<div class="discussion-answer-circle"><span class="comments-count"><?php _e( 'A', 'cp' ) ?></span></div>
						</div>

						<?php if ( '0' == $comment->comment_approved ) : ?>
							<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'cp' ); ?></p>
						<?php endif; ?>
					</footer><!-- .comment-meta -->

					<div class="comment-content">
						<?php comment_text(); ?>
						<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
							<time>
								<?php echo comment_author() . ', '; ?>
								<?php echo human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) . ' ago'; ?>
							</time>
						</a>
						<?php edit_comment_link( __( 'Edit', 'cp' ), '<span class="edit-link">', '</span>' ); ?>
					</div><!-- .comment-content -->



					<?php
					comment_reply_link( array_merge( $args, array(
						'add_below'	 => 'div-comment',
						'depth'		 => $depth,
						'max_depth'	 => $args[ 'max_depth' ],
						'before'	 => '<div class="reply">',
						'after'		 => '</div>',
					) ) );
					?>
				</article><!-- .comment-body -->

			<?php
			endif;
		}

	endif; // ends check for coursepress_comment()

	if ( !function_exists( 'coursepress_posted_on' ) ) :

		/**
		 * Prints HTML with meta information for the current post-date/time and author.
		 */
		function coursepress_posted_on() {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
			if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
				$time_string .= '<time class="updated" datetime="%3$s">%4$s</time>';
			}

			$time_string = sprintf( $time_string, esc_attr( get_the_date( 'c' ) ), esc_html( get_the_date() ), esc_attr( get_the_modified_date( 'c' ) ), esc_html( get_the_modified_date() )
			);

			printf( __( '<span class="posted-on">Posted on %1$s</span><span class="byline"> by %2$s</span>', 'cp' ), sprintf( '<a href="%1$s" rel="bookmark">%2$s</a>', esc_url( get_permalink() ), $time_string
			), sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s">%2$s</a></span>', esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ), esc_html( get_the_author() )
			)
			);
		}

	endif;

	/**
	 * Returns true if a blog has more than 1 category.
	 */
	function coursepress_categorized_blog() {
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		$all_the_cool_cats = count( $all_the_cool_cats );

		if ( '1' != $all_the_cool_cats ) {
			// This blog has more than 1 category so coursepress_categorized_blog should return true.
			return true;
		} else {
			// This blog has only 1 category so coursepress_categorized_blog should return false.
			return false;
		}
	}

	/**
	 * Flush out the transients used in coursepress_categorized_blog.
	 */
	function coursepress_category_transient_flusher() {
		// Like, beat it. Dig?
		delete_transient( 'all_the_cool_cats' );
	}

	add_action( 'edit_category', 'coursepress_category_transient_flusher' );
	add_action( 'save_post', 'coursepress_category_transient_flusher' );
	