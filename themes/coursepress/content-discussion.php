<?php
/**
 * @package CoursePress
 */
$discussion = new Discussion( get_the_ID() );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">

        <div class="discussion-archive-single-meta">
            <div class="discussion-comments-circle"><span class="comments-count"><?php _e( 'Q', 'cp' ); ?></span></div>
        </div>

        <div class="discussion-archive-single">
            <h1 class="discussion-title"><?php the_title(); ?></h1>

            <div class="entry-content">
                <?php the_content(); ?>
            </div><!-- .entry-content -->
            <div class="discussion-meta">
                <span><?php echo get_the_date(); ?></span> | <span><?php the_author(); ?></span> | <span><?php echo $discussion->get_unit_name(); ?></span> | <span><?php echo get_comments_number(); ?> <?php _e( 'Comments', 'cp' );?></span>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="discussion-responses">
            <?php
            // If comments are open or we have at least one comment, load up the comment template
            if ( comments_open() || '0' != get_comments_number() ) {
                comments_template( '/comments-discussion.php' );
            }else{
                _e( 'Comments are disabled', 'cp' );
            }
            ?>
        </div>

    </header><!-- .entry-header -->

    <footer class="entry-meta">
        <?php edit_post_link( __( 'Edit', 'cp' ), '<span class="edit-link">', '</span>' ); ?>
    </footer><!-- .entry-meta -->
</article><!-- #post-## -->
