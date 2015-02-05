<?php
/**
 * The discussion archive template file
 * 
 * @package CoursePress
 */
global $coursepress, $wp;
$course_id = do_shortcode('[get_parent_course_id]');
//redirect to the parent course page if not enrolled
$coursepress->check_access($course_id);
get_header();
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <h1><?php echo do_shortcode('[course_title course_id="' . $course_id . '"]'); ?></h1>
        <div class="instructors-content">
            <?php
            // Flat hyperlinked list of instructors
            echo do_shortcode('[course_instructors style="list-flat" link="true" course_id="' . $course_id . '"]');
            ?>
        </div>

        <?php
        echo do_shortcode('[course_unit_archive_submenu]');
        ?>

        <div class="discussion-controls">
            <a class="button_submit" href="<?php echo get_permalink($course_id); ?><?php echo $coursepress->get_discussion_slug() . '/' . $coursepress->get_discussion_slug_new(); ?>/"><?php _e('Ask a Question', 'cp'); ?></a>
        </div>

        <div class="clearfix"></div>

        <ul class="discussion-archive-list">
            <?php
            //do_shortcode( '[course_discussion_loop]' ); //required to get good results

            $page = ( isset($wp->query_vars['paged']) ) ? $wp->query_vars['paged'] : 1;
            $query_args = array(
                'order' => 'DESC',
                'post_type' => 'discussions',
                'post_status' => 'publish',
                'meta_key' => 'course_id',
                'meta_value' => $course_id,
                'paged' => $page,
            );

            query_posts($query_args);

            if ( have_posts() ) {
                ?>
                <?php
                while ( have_posts() ) : the_post();
                    $discussion = new Discussion(get_the_ID());
                    ?>
                    <li>
                        <div class="discussion-archive-single-meta">
                            <div class="<?php
            if ( get_comments_number() > 0 ) {
                echo 'discussion-answer-circle';
            } else {
                echo 'discussion-comments-circle';
            }
                    ?>"><span class="comments-count"><?php echo get_comments_number(); ?></span></div>
                        </div>
                        <div class="discussion-archive-single">
                            <h1 class="discussion-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                            <div class="discussion-meta">
                                <?php
                                if ( $discussion->details->unit_id == '' ) {
                                    $discussion_unit = $discussion->get_unit_name();
                                } else {
                                    $discussion_unit = '<a href="' . Unit::get_permalink( $discussion->details->unit_id ) . '">' . $discussion->get_unit_name() . '</a>';
                                }
                                ?>
                                <span><?php echo get_the_date(); ?></span> | <span><?php the_author(); ?></span> | <span><?php echo $discussion_unit; ?></span> | <span><?php echo get_comments_number(); ?> <?php _e('Comments', 'cp'); ?></span>
                            </div>
                            <div class="clearfix"></div>
                        </div>

                    </li>
                    <?php
                endwhile;
            } else {
                ?>
                <h1 class="zero-course-units"><?php _e("0 discussions. Start one, ask a question.", "cp"); ?></h1>
                <?php
            }
            ?>
        </ul>
        <br clear="all" />
        <?php cp_numeric_posts_nav('navigation-pagination'); ?>
    </main><!-- #main -->
</div><!-- #primary -->
<?php get_sidebar('footer'); ?>
<?php get_footer(); ?>