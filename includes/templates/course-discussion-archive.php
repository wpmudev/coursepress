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
?>
<?php
do_shortcode('[course_unit_archive_submenu]');
?>

<div class="discussion-controls">
    <button data-link="<?php echo get_permalink($course_id); ?><?php echo $coursepress->get_discussion_slug() . '/' . $coursepress->get_discussion_slug_new(); ?>/"><?php _e('Ask a Question', 'coursepress'); ?></button>
</div>

<ul class="discussion-archive-list">
    <?php
                
    $page = ( isset($wp->query_vars['paged']) ) ? $wp->query_vars['paged'] : 1;
    do_shortcode('[course_discussion_loop]');

    if ( have_posts() ) {
        ?>
        <?php
        while ( have_posts() ) : the_post();
        //foreach ( $myposts as $post ) : setup_postdata($post);
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
                    ?>"><span class="comments-count"><?php echo get_comments_number(); ?> <?php _e('Comments', 'cp'); ?></span></div>
                </div>
                <div class="discussion-archive-single">
                    <h1 class="discussion-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                    <div class="discussion-meta">
                        <?php
                        if ( $discussion->details->unit_id == '' ) {
                            $discussion_unit = $discussion->get_unit_name();
                        } else {
                            $unit = new Unit($discussion->details->unit_id);
                            $discussion_unit = '<a href="' . $unit->get_permalink() . '">' . $discussion->get_unit_name() . '</a>';
                        }
                        ?>
                        <span><?php echo get_the_date(); ?></span> | <span><?php the_author(); ?></span> | <span><?php echo $discussion_unit; ?></span> | <span><?php echo get_comments_number(); ?> <?php _e('Comments', 'coursepress'); ?></span>
                    </div>
                    <div class="clearfix"></div>
                </div>

            </li>
        <?php endwhile;
        } else {
        ?>
        <h1 class="zero-course-units"><?php _e("0 discussions. Start one, ask a question."); ?></h1>
        <?php
    }
    ?>
</ul>
<br clear="all" />
<?php coursepress_numeric_posts_nav('navigation-pagination'); ?>