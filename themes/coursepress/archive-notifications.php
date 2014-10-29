<?php
/**
 * The notifications archive template file
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
        //do_shortcode( '[course_notifications_loop]' ); //required for getting notifications results
        ?>

        <?php
        echo do_shortcode('[course_unit_archive_submenu]');
        ?>

        <div class="clearfix"></div>

        <ul class="notification-archive-list">
            <?php
            $page = ( isset($wp->query_vars['paged']) ) ? $wp->query_vars['paged'] : 1;

            $query_args = array(
                'category' => '',
                'order' => 'DESC',
                'post_type' => 'notifications',
                'post_mime_type' => '',
                'post_parent' => '',
                'post_status' => 'publish',
                'orderby' => 'meta_value_num',
                'paged' => $page,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => 'course_id',
                        'value' => $course_id
                    ),
                    array(
                        'key' => 'course_id',
                        'value' => ''
                    ),
                )
            );

            query_posts($query_args);
            ?>
            <?php if ( have_posts() ) { ?>
                <?php
                while ( have_posts() ) {
                    the_post();
                    ?>
                    <li>
                        <div class="notification-archive-single-meta">
                            <div class="notification-date"><span class="date-part-one"><?php echo get_the_date('M'); ?></span><span class="date-part-two"><?php echo get_the_date('j'); ?></span></div>
                            <span class="notification-meta-divider"></span>
                            <div class="notification-time"><?php the_time(); ?></div>
                        </div>
                        <div class="notification-archive-single">
                            <h1 class="notification-title"><?php the_title(); ?></h1>
                            <div class="notification_author"><?php the_author(); ?></div>
                            <div class="notification-content"><?php the_content(); ?></div>
                        </div>
                        <div class="clearfix"></div>
                    </li>
                    <?php
                }
            } else {
                ?>
                <h1 class="zero-course-units"><?php _e("0 notifications. Please check back later.", "cp"); ?></h1>
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