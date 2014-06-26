<?php
/**
 * @package CoursePress
 */
?>
<?php
$course = new Course( get_the_ID() );
$course_thumbnail = Course::get_course_id_by_name( 'asdas' );
//$course_category_id = $course->details->course_category;
//$course_category = get_term_by( 'ID', $course_category_id, 'course_category' );

$course_language = $course->details->course_language;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php
    if ( $course->get_course_thumbnail() ) {
        ?>
        <figure>
            <?php /*if ( isset( $course_category->name ) ) { ?>
                <figcaption><?php echo $course_category->name; ?></figcaption>
            <?php }*/ ?>

            <img src="<?php echo $course->get_course_thumbnail(); ?>">

            <?php edit_post_link( __( 'Edit Course', 'coursepress' ), '<span class="edit-link">', '</span>' ); ?>
        </figure>
    <?php }else{
        $extended_class = 'quick-course-info-extended';
    } ?>

    <section class='article-content-right'>
        <header class="entry-header">
            <h1 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
        </header><!-- .entry-header -->

        <?php if ( is_search() ) : // Only display Excerpts for Search   ?>
            <div class="entry-summary">
                <?php the_excerpt(); ?>
            </div><!-- .entry-summary -->
        <?php else : ?>
            <div class="entry-content">
                <div class="instructors-content">
                    <?php echo do_shortcode( '[course_instructors list="true" link="true"]' ); ?>
                </div>
                <?php the_excerpt(); ?>
                <?php
                wp_link_pages( array(
                    'before' => '<div class="page-links">' . __( 'Pages:', 'coursepress' ),
                    'after' => '</div>',
                ) );
                ?>
                <div class="quick-course-info <?php echo ( isset( $extended_class ) ? $extended_class : '' );?>">
                    <span class="course-time"><?php echo do_shortcode( '[course_details field="course_start_date"]' ); ?></span>
                    <?php if ( isset( $course_language ) && $course_language !== '' ) { ?>
                        <span class="course-lang"><?php echo $course_language; ?></span>
                    <?php } ?>
                    <a class="go-to-course-button" href="<?php the_permalink(); ?>"><?php _e( 'Go to Course', 'coursepress' ); ?></a>
                </div>
            </div><!-- .entry-content -->

        <?php endif; ?>

        <footer class="entry-meta">
            <?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search  ?>
                <?php
                /* translators: used between list items, there is a space after the comma */
                $categories_list = get_the_category_list( __( ', ', 'coursepress' ) );
                if ( $categories_list && coursepress_categorized_blog() ) :
                    ?>
                    <span class="cat-links">
                        <?php printf( __( 'Courses in %1$s', 'coursepress' ), $categories_list ); ?>
                    </span>
                <?php endif; // End if categories   ?>

                <?php
                /* translators: used between list items, there is a space after the comma */
                $tags_list = get_the_tag_list( '', __( ', ', 'coursepress' ) );
                if ( $tags_list ) :
                    ?>
                    <span class="tags-links">
                        <?php printf( __( 'Tagged %1$s', 'coursepress' ), $tags_list ); ?>
                    </span>
                <?php endif; // End if $tags_list  ?>
            <?php endif; // End if 'post' == get_post_type()  ?>

            <?php /* if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
              <span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'coursepress' ), __( '1 Comment', 'coursepress' ), __( '% Comments', 'coursepress' ) ); ?></span>
              <?php endif; */ ?>
        </footer><!-- .entry-meta -->
    </section>
</article><!-- #post-## -->
<br style="clear: both;" />
