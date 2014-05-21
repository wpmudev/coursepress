<?php
/**
 * @package CoursePress
 */
?>
<?php
$course = new Course(get_the_ID());

$course_category_id = $course->details->course_category;
$course_category = get_term_by('ID', $course_category_id, 'course_category');

$course_language = $course->details->course_language;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <h1 class="entry-title"><?php the_title(); ?></h1>
        <div class="instructors-content">
            <?php echo do_shortcode('[course_instructors list="true" link="true"]'); ?>
        </div>
    </header><!-- .entry-header -->

    <section id="course-summary">
        <?php if ($course->details->course_video_url != '') { ?>  
                <div class="course-video">
                    <?php
                    $video_extension = pathinfo($course->details->course_video_url, PATHINFO_EXTENSION);

                    if (!empty($video_extension)) {//it's file, most likely on the server
                        $attr = array(
                            'src' => $course->details->course_video_url,
                            /*'width' => 500,
                            'height' => 300*/
                        );

                        echo wp_video_shortcode($attr);
                    } else {

                        $embed_args = array(
                            /*'width' => $course->details->course_video_url,
                            'height' => 900*/
                        );

                        echo wp_oembed_get($course->details->course_video_url, $embed_args);
                    }
                    ?>
                </div>
            <?php } ?>

        <div class="entry-content-excerpt <?php echo (!isset($course->details->course_video_url) || $course->details->course_video_url == '' ? 'entry-content-excerpt-right' : '');?>">
            <?php //the_excerpt(); ?>
            <div class="course-box">
                <span class="strong"><?php _e('Course Dates: ', 'cp'); ?></span><?php
                if (do_shortcode('[course_details field="course_start_date"]') == 'Open-ended') {
                    _e('Open-ended', 'cp');
                } else {
                    echo do_shortcode('[course_details field="course_start_date"]') . ' - ' . do_shortcode('[course_details field="course_end_date"]');
                }
                ?><br />
                <span class="strong"><?php _e('Enrollment Dates: ', 'cp'); ?></span><?php
                if (do_shortcode('[course_details field="enrollment_start_date"]') == 'Open-ended') {
                    _e('Open-ended', 'cp');
                } else {
                    echo do_shortcode('[course_details field="enrollment_start_date"]') . ' - ' . do_shortcode('[course_details field="enrollment_end_date"]');
                }
                ?><br />
                <span class="strong"><?php _e('Class Size: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="class_size"]'); ?><br />
                <span class="strong"><?php _e('Who can Enroll: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="enroll_type"]'); ?><br />
                <?php if (isset($course_language) && $course_language !== '') { ?>
                    <span class="strong"><?php _e('Language: ', 'cp'); ?></span><span><?php echo $course_language; ?></span><br />
                <?php } ?>
                <span class="strong"><?php _e('Price: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="price"]'); ?>
            </div><!--course-box-->
            <div class="quick-course-info">
                <!--<span class="course-time"><?php echo do_shortcode('[course_details field="course_start_date"]'); ?></span>-->
                <?php /* if (isset($course_language) && $course_language !== '') { ?>
                  <span class="course-lang"><?php echo $course_language; ?></span>
                  <?php } */ ?>
                <?php echo do_shortcode('[course_details field="button"]'); ?>
            </div>
        </div>
    </section>

    <section id="additional-summary">
        <div class="social-shares">
            <span><?php _e('SHARE', 'coursepress'); ?></span>
            <a href="http://www.facebook.com/sharer/sharer.php?s=100&p[url]=<?php the_permalink(); ?>&p[images][0]=&p[title]=<?php the_title(); ?>&p[summary]=<?php echo urlencode(strip_tags(get_the_excerpt())); ?>" class="facebook-share" target="_blank"></a>
            <a href="http://twitter.com/home?status=<?php the_title(); ?> <?php the_permalink(); ?>" class="twitter-share" target="_blank"></a>
            <a href="https://plus.google.com/share?url=<?php the_permalink(); ?>" class="google-share" target="_blank"></a>
            <a href="mailto:?subject=<?php the_title(); ?>&body=<?php echo strip_tags(get_the_excerpt()); ?>" target="_top" class="email-share"></a>
        </div><!--social shares-->
    </section>

    <br clear="all" />

    <?php
    $instructors = $course->get_course_instructors();
    ?>
    <div class="entry-content <?php echo(count($instructors) > 0 ? 'left-content' : ''); ?>">
        <h1 class="h1-about-course"><?php _e('About the Course', 'coursepress'); ?></h1>
        <?php the_content(); ?>
        <?php
        wp_link_pages(array(
            'before' => '<div class="page-links">' . __('Pages:', 'coursepress'),
            'after' => '</div>',
        ));
        ?>
    </div><!-- .entry-content -->

    <?php if (count($instructors) > 0) { ?>
        <div class="course-instructors right-content">
            <h1 class="h1-instructors"><?php _e('Instructors', 'coursepress'); ?></h1>
            <script>
                jQuery(function() {
                    jQuery("#instructor-profiles").accordion({
                        heightStyle: "content"
                    });
                });


            </script>
            <div id="instructor-profiles">
                <?php
                foreach ($instructors as $instructor) {
                    ?>

                    <h3><?php echo $instructor->display_name; ?></h3>

                    <?php
                    $doc = new DOMDocument();
                    $doc->loadHTML(get_avatar($instructor->ID, 235));
                    $imageTags = $doc->getElementsByTagName('img');

                    foreach ($imageTags as $tag) {
                        $avatar_url = $tag->getAttribute('src');
                    }
                    ?>

                    <?php
                    /* $content .= '<div class="instructor"><a href="' . trailingslashit(site_url()) . trailingslashit($instructor_profile_slug) . trailingslashit($instructor->user_login) . '">';
                      $content .= '<div class="small-circle-profile-image" style="background: url(' . $avatar_url . ');"></div>';
                      $content .= '<div class="instructor-name">' . $instructor->display_name . '</div>';
                      $content .= '</a></div>';
                      $instructors_count++; */
                    ?>

                    <div>
                        <img src="<?php echo $avatar_url; ?>" />
                        <p>
                            <?php echo author_description_excerpt($instructor->ID, 50); ?>
                        </p>
                        <a href="<?php echo do_shortcode('[instructor_profile_url instructor_id="' . $instructor->ID . '"]'); ?>" class="full-instructor-profile"><?php _e('View Full Profile'); ?></a>
                    </div>
                <?php } ?>
            </div>

        </div><!--course-instructors right-content-->
    <?php } ?>
    <br clear="all" />

    <footer class="entry-meta">
        <?php
        /* translators: used between list items, there is a space after the comma */
        $category_list = get_the_category_list(__(', ', 'coursepress'));

        /* translators: used between list items, there is a space after the comma */
        $tag_list = get_the_tag_list('', __(', ', 'coursepress'));

        if (!coursepress_categorized_blog()) {
            // This blog only has 1 category so we just need to worry about tags in the meta text
            if ('' != $tag_list) {
                $meta_text = __('This entry was tagged %2$s. Bookmark the <a href="%3$s" rel="bookmark">permalink</a>.', 'coursepress');
            } else {
                //$meta_text = __('Bookmark the <a href="%3$s" rel="bookmark">permalink</a>.', 'coursepress');
                $meta_text = '';
            }
        } else {
            // But this blog has loads of categories so we should probably display them here
            if ('' != $tag_list) {
                $meta_text = __('This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" rel="bookmark">permalink</a>.', 'coursepress');
            } else {
                $meta_text = __('This entry was posted in %1$s. Bookmark the <a href="%3$s" rel="bookmark">permalink</a>.', 'coursepress');
            }
        } // end check for categories on this blog

        printf(
                $meta_text, $category_list, $tag_list, get_permalink()
        );
        ?>

        <?php edit_post_link(__('Edit', 'coursepress'), '<span class="edit-link">', '</span>'); ?>
    </footer><!-- .entry-meta -->
</article><!-- #post-## -->
