<?php
global $page, $user_id, $coursepress_admin_notice;
global $coursepress;

if (isset($_GET['course_id'])) {
    $course = new Course($_GET['course_id']);
    $course_details = $course->get_course();
    $course_id = $_GET['course_id'];
} else {
    $course = new course();
    $course_id = 0;
}

if (isset($_POST['action']) && ($_POST['action'] == 'add' || $_POST['action'] == 'update')) {

    check_admin_referer('course_details_overview');

    if ($_POST['meta_course_category'] != -1) {
        $term = get_term_by('id', $_POST['meta_course_category'], 'course_category');
        wp_set_object_terms($course_id, $term->slug, 'course_category', false);
    }

    if (!isset($_POST['meta_open_ended_course'])) {
        $_POST['meta_open_ended_course'] = 'off';
    }

    if (!isset($_POST['meta_allow_course_discussion'])) {
        $_POST['meta_allow_course_discussion'] = 'off';
    }

    if (!isset($_POST['meta_allow_course_grades_page'])) {
        $_POST['meta_allow_course_grades_page'] = 'off';
    }

    $new_post_id = $course->update_course();

    if ($new_post_id != 0) {
        ob_start();
        if (isset($_GET['ms'])) {
            wp_redirect('?page=' . $page . '&course_id=' . $new_post_id . '&ms=' . $_GET['ms']);
            exit;
        } else {
            wp_redirect('?page=' . $page . '&course_id=' . $new_post_id);
            exit;
        }
    } else {
        //an error occured
    }
}

if (isset($_GET['course_id'])) {
    $class_size = $course->details->class_size;
    $enroll_type = $course->details->enroll_type;
    $passcode = $course->details->passcode;
    $prerequisite = $course->details->prerequisite;
    $course_start_date = $course->details->course_start_date;
    $course_end_date = $course->details->course_end_date;
    $enrollment_start_date = $course->details->enrollment_start_date;
    $enrollment_end_date = $course->details->enrollment_end_date;
    $open_ended_course = $course->details->open_ended_course;
    $marketpress_product = $course->details->marketpress_product;
    $allow_course_discussion = $course->details->allow_course_discussion;
    $allow_course_grades_page = $course->details->allow_course_grades_page;
    $course_category = $course->details->course_category;
    $language = $course->details->course_language;
    $course_video_url = $course->details->course_video_url;
} else {
    $class_size = 0;
    $enroll_type = '';
    $passcode = '';
    $prerequisite = '';
    $course_start_date = '';
    $course_end_date = '';
    $enrollment_start_date = '';
    $enrollment_end_date = '';
    $open_ended_course = 'off';
    $marketpress_product = '';
    $allow_course_discussion = 'off';
    $allow_course_grades_page = 'off';
    $course_category = 0;
    $language = __('English', 'cp');
    $course_video_url = '';
}
?>

<div class='wrap nocoursesub'>
    <form action='?page=<?php echo esc_attr($page); ?><?php echo ($course_id !== 0) ? '&course_id=' . $course_id : '' ?><?php echo ($course_id !== 0) ? '&ms=cu' : '&ms=ca'; ?>' name='course-add' method='post'>

        <div class='course-liquid-left'>

            <div id='course-left'>

                <?php wp_nonce_field('course_details_overview'); ?>

                <?php if (isset($course_id)) { ?>
                    <input type="hidden" name="course_id" value="<?php echo esc_attr($course_id); ?>" />
                    <input type="hidden" name="action" value="update" />
                <?php } else { ?>
                    <input type="hidden" name="action" value="add" />
                <?php } ?>

                <div id='edit-sub' class='course-holder-wrap mp-wrap'>

                    <div class='sidebar-name no-movecursor'>
                        <h3><?php _e('Course Details', 'cp'); ?></h3>
                    </div>

                    <div class='course-holder'>
                        <div class='course-details'>
                            <label for='course_name'><?php _e('Course Name', 'cp'); ?></label>
                            <input class='wide' type='text' name='course_name' id='course_name' value='<?php
                            if (isset($_GET['course_id'])) {
                                echo esc_attr(stripslashes($course->details->post_title));
                            }
                            ?>' />

                            <br/><br/>
                            <label for='course_excerpt'><?php _e('Course Excerpt', 'cp'); ?></label>
                            <?php
                            $args = array("textarea_name" => "course_excerpt", "textarea_rows" => 3);

                            if (!isset($course_excerpt->post_excerpt)) {
                                $course_excerpt = new StdClass;
                                $course_excerpt->post_excerpt = '';
                            }

                            $desc = '';
                            wp_editor(htmlspecialchars_decode((isset($_GET['course_id']) ? $course_details->post_excerpt : '')), "course_excerpt", $args);
                            ?>

                            <br/><br/>
                            <label for='course_name'><?php _e('Course Description', 'cp'); ?></label>
                            <?php
                            $args = array("textarea_name" => "course_description", "textarea_rows" => 10);

                            if (!isset($course_details->post_content)) {
                                $course_details = new StdClass;
                                $course_details->post_content = '';
                            }

                            $desc = '';
                            wp_editor(htmlspecialchars_decode($course_details->post_content), "course_description", $args);
                            ?>
                            <br/>

                            <div class="half">
                                <label for='meta_class-size'><?php _e('Class size', 'cp'); ?>
                                    <a class="mp-help-icon" href="javascript:;"></a>
                                    <div class="mp-help-text"><?php _e('select 0 for infinite', 'cp'); ?></div>
                                </label>
                                <input class='spinners' name='meta_class_size' id='class_size' value='<?php echo esc_attr(stripslashes((is_numeric($class_size) ? $class_size : 0))); ?>' />
                            </div>

                            <!--<br clear="all" />-->

                            <div class="half">
                                <label for='meta_enroll_type'><?php _e('How & Who can Enroll?', 'cp'); ?></label>

                                <select class="wide" name="meta_enroll_type" id="enroll_type">
                                    <option value="anyone" <?php echo ($enroll_type == 'anyone' ? 'selected=""' : '') ?>><?php _e(' Anyone ', 'cp'); ?></option>
                                    <option value="passcode" <?php echo ($enroll_type == 'passcode' ? 'selected=""' : '') ?>><?php _e('Anyone with a pass code', 'cp'); ?></option>
                                    <option value="prerequisite" <?php echo ($enroll_type == 'prerequisite' ? 'selected=""' : '') ?>><?php _e('Anyone who fulfil prerequisite (course)', 'cp'); ?></option>
                                    <option value="manually" <?php echo ($enroll_type == 'manually' ? 'selected=""' : '') ?>><?php _e('Manually added only', 'cp'); ?></option>
                                </select>

                            </div>

                            <div class="half" id="enroll_type_prerequisite_holder" <?php echo ($enroll_type <> 'prerequisite' ? 'style="display:none"' : '') ?>>
                                <label for='meta_enroll_type'><?php _e('Prerequisite Course', 'cp'); ?>
                                    <a class="mp-help-icon" href="javascript:;"></a>
                                    <div class="mp-help-text"><?php _e('Students will need to fulfil prerequisite in order to enroll', 'cp'); ?></div
                                </label>
                                <select name="meta_prerequisite" class="chosen-select">
                                    <?php
                                    $args = array(
                                        'post_type' => 'course',
                                        'post_status' => 'any',
                                        'posts_per_page' => -1,
                                        'exclude' => $course_id
                                    );

                                    $pre_courses = get_posts($args);

                                    foreach ($pre_courses as $pre_course) {

                                        $pre_course_obj = new Course($pre_course->ID);
                                        $pre_course_object = $pre_course_obj->get_course();
                                        ?>
                                        <option value="<?php echo $pre_course->ID; ?>" <?php selected($prerequisite, $pre_course->ID, true); ?>><?php echo $pre_course->post_title; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>

                            </div>

                            <div class="half" id="enroll_type_holder" <?php echo ($enroll_type <> 'passcode' ? 'style="display:none"' : '') ?>>
                                <label for='meta_enroll_type'><?php _e('Pass Code', 'cp'); ?>
                                    <a class="mp-help-icon" href="javascript:;"></a>
                                    <div class="mp-help-text"><?php _e('Students will need to enter the pass code in order to enroll', 'cp'); ?></div
                                </label>
                                <input type="text" name="meta_passcode" value="<?php echo esc_attr(stripslashes($passcode)); ?>" />

                            </div>

                            <br clear="all" />
                            <br clear="all" />

                            <div class="half">
                                <label><?php _e('Course Category', 'cp'); ?></label>
                                <?php
                                $tax_args = array(
                                    'show_option_all' => '',
                                    'show_option_none' => __('-- None --', 'cp'),
                                    'orderby' => 'ID',
                                    'order' => 'ASC',
                                    'show_count' => 0,
                                    'hide_empty' => 0,
                                    'echo' => 1,
                                    'selected' => $course_category,
                                    'hierarchical' => 0,
                                    'name' => 'meta_course_category',
                                    'id' => '',
                                    'class' => 'postform chosen-select',
                                    'depth' => 0,
                                    'tab_index' => -1,
                                    'taxonomy' => 'course_category',
                                    'hide_if_empty' => false,
                                    'walker' => ''
                                );

                                $taxonomies = array('course_category');
                                wp_dropdown_categories($tax_args);
                                ?>
                                <a href="edit-tags.php?taxonomy=course_category&post_type=course"><?php _e('Manage Categories', 'cp'); ?></a>

                            </div>

                            <div class="half">
                                <label for='meta_course_language'><?php _e('Course Language', 'cp'); ?></label>
                                <input type="text" name="meta_course_language" value="<?php echo esc_attr(stripslashes($language)); ?>" />
                            </div>

                            <br clear="all" />

                            <div class="full border-devider">
                                <label><?php _e('Open-ended course:', 'cp'); ?>
                                    <a class="mp-help-icon" href="javascript:;"></a>
                                    <div class="mp-help-text"><?php _e('The first or last course or enrollment date having no upper or lower limit.', 'cp') ?></div>
                                    <input type="checkbox" name="meta_open_ended_course" id="open_ended_course" <?php echo ($open_ended_course == 'on') ? 'checked' : ''; ?> />
                                </label>
                            </div>

                            <div id="all_course_dates" class="border-devider" <?php echo ($open_ended_course == 'on') ? 'style="display:none;"' : ''; ?>>

                                <label><?php _e('Course Dates:', 'cp'); ?></label>

                                <p class="description"></p>

                                <div class="half"><?php _e('Start Date', 'cp'); ?>
                                    <input type="text" class="dateinput" name="meta_course_start_date" value="<?php echo esc_attr($course_start_date); ?>" />
                                </div>

                                <div class="half"><?php _e('End Date', 'cp'); ?> 
                                    <input type="text" class="dateinput" name="meta_course_end_date" value="<?php echo esc_attr($course_end_date); ?>" />
                                </div>

                                <br clear="all" />
                                <br clear="all" />

                                <label><?php _e('Enrollment Dates:', 'cp'); ?>
                                    <a class="mp-help-icon" href="javascript:;"></a>
                                    <div class="mp-help-text"><?php _e('Student may enroll only during selected date range', 'cp'); ?></div>
                                </label>

                                <div class="half"><?php _e('Start Date', 'cp'); ?>
                                    <input type="text" class="dateinput" name="meta_enrollment_start_date" value="<?php echo esc_attr($enrollment_start_date); ?>" />
                                </div>

                                <div class="half"><?php _e('End Date', 'cp'); ?>
                                    <input type="text" class="dateinput" name="meta_enrollment_end_date" value="<?php echo esc_attr($enrollment_end_date); ?>" />
                                </div>
                            </div><!--/all-course-dates-->

                            <br clear="all" />

                            <div class="full border-devider">
                                <label><?php _e('Allow Course Discussion', 'cp'); ?>
                                    <a class="mp-help-icon" href="javascript:;"></a>
                                    <div class="mp-help-text"><?php _e('If checked, students can post questions and get answers.', 'cp') ?></div>
                                    <input type="checkbox" name="meta_allow_course_discussion" id="allow_course_discussion" <?php echo ($allow_course_discussion == 'on') ? 'checked' : ''; ?> />
                                </label>

                            </div>

                            <div class="full border-devider">
                                <label><?php _e('Allow Grades Page', 'cp'); ?>
                                    <a class="mp-help-icon" href="javascript:;"></a>
                                    <div class="mp-help-text"><?php _e('If checked, students can see their course performance and grades by units.', 'cp') ?></div>

                                    <input type="checkbox" name="meta_allow_course_grades_page" id="allow_course_grades_page" <?php echo ($allow_course_grades_page == 'on') ? 'checked' : ''; ?> />
                                </label>

                            </div>

                            <br clear="all" />

                        </div>

                        <div class="buttons">
                            <?php
                            if (($course_id == 0 && current_user_can('coursepress_create_course_cap')) || ($course_id != 0 && current_user_can('coursepress_update_course_cap')) || ($course_id != 0 && current_user_can('coursepress_update_my_course_cap') && $course_details->post_author == get_current_user_id())) {//do not show anything
                                ?>
                                <input type = "submit" value = "<?php ($course_id == 0 ? _e('Create', 'cp') : _e('Update', 'cp')); ?>" class = "button-primary" />
                                <?php
                            } else {
                                ?>

                                <?php
                            }
                            if ($course_id !== 0) {
                                ?>
                                <a href="?page=<?php echo $page; ?>&tab=units&course_id=<?php echo $_GET['course_id']; ?>" class="button-secondary"><?php _e('Add Units »', 'cp'); ?></a> 
                            <?php } ?>
                        </div>

                    </div>
                </div>

            </div>
        </div> <!-- course-liquid-left -->

        <div class='course-liquid-right'>

            <div class="course-holder-wrap">

                <div class="sidebar-name no-movecursor">
                    <h3><?php _e('Course Instructor(s)', 'cp'); ?></h3>
                </div>

                <div class="level-holder" id="sidebar-levels">
                    <div class='sidebar-inner'>
                        <div class="instructors-info" id="instructors-info">
                            <?php
                            if ((current_user_can('coursepress_assign_and_assign_instructor_course_cap')) || (current_user_can('coursepress_assign_and_assign_instructor_my_course_cap') && $course->details->post_author == get_current_user_id())) {
                                $remove_button = true;
                            } else {
                                $remove_button = false;
                            }
                            ?>

                            <?php coursepress_instructors_avatars($course_id, $remove_button); ?>
                        </div>

                        <?php if ((current_user_can('coursepress_assign_and_assign_instructor_course_cap')) || (current_user_can('coursepress_assign_and_assign_instructor_my_course_cap') && $course->details->post_author == get_current_user_id()) || (current_user_can('coursepress_assign_and_assign_instructor_my_course_cap') && !isset($_GET['course_id']))) { ?>
                            <?php coursepress_instructors_avatars_array(); ?>

                            <div class="clearfix"></div>
                            <?php coursepress_instructors_drop_down(); ?>

                            <?php if (coursepress_get_number_of_instructors() != 0) { ?>
                                <div class="inner-right inner-link">
                                    <input class="button-secondary" id="add-instructor-trigger" type="button" value="<?php _e('Assign Selected Instructor', 'cp'); ?>">
                                </div>
                                <?php
                            } else {
                                _e('You do not have any available instructors yet. <a href="user-new.php" target="_new">Create one user with the Instructor role</a> in order to assign it to the courses.', 'cp');
                            }
                            ?>

                            <?php
                        } else {
                            if (coursepress_get_number_of_instructors() == 0 || coursepress_instructors_avatars($course_id, false, true) == 0) {//just to fill in emtpy space if none of the instructors has been assigned to the course and in the same time instructor can't assign instructors to a course
                                _e('You do not have required permissions to assign instructors to a course.', 'cp');
                            }
                        }
                        ?>

                    </div>
                </div>
            </div> <!-- course-holder-wrap -->

            <?php
            if ($coursepress->is_marketpress_active()) {
                ?>
                <div class="course-holder-wrap mp-wrap">

                    <div class="sidebar-name no-movecursor">
                        <h3><?php _e('MarketPress Integration', 'cp'); ?></h3>
                    </div>

                    <div class="level-holder" id="sidebar-levels">
                        <div class='sidebar-inner'>
                            <a class="mp-help-icon" href="javascript:;"></a>
                            <div class="mp-help-text"><?php _e('For students to pay for this course, you can set up a product in MarketPress and sell the course. Select this course when creating/editing a product.'); ?></div>
                            <?php _e('MarketPress product'); ?>
                            <select name="meta_marketpress_product" id="meta_marketpress_product" class="chosen-select">
                                <option value="" <?php selected($marketpress_product, '', true); ?>><?php _e('None, this course is free'); ?></option>
                                <?php
                                global $post;
                                $args = array(
                                    'numberposts' => -1,
                                    'post_type' => 'product'
                                );
                                $posts = get_posts($args);
                                foreach ($posts as $post) {
                                    setup_postdata($post);
                                    ?>
                                    <option value="<?php echo $post->ID; ?>" <?php selected($marketpress_product, $post->ID, true); ?>><?php the_title(); ?></option>
                                <?php } ?>
                            </select>


                        </div>
                    </div>
                </div> <!-- course-holder-wrap -->
            <?php } ?>


            <div class="course-holder-wrap">

                <div class="sidebar-name no-movecursor">
                    <h3><?php _e('Course Image', 'cp'); ?></h3>
                </div>

                <div class="level-holder" id="sidebar-levels">
                    <div class='sidebar-inner'>
                        <div class="featured_url_holder">
                            <?php _e('Browse for an image.', 'cp'); ?>
                            <input class="featured_url" type="text" size="36" name="meta_featured_url" value="<?php
                            if ($course_id !== 0) {
                                echo esc_attr($course->details->featured_url);
                            }
                            ?>" />
                            <input class="featured_url_button button-secondary" type="button" value="<?php _e('Browse', 'ub'); ?>" />
                            <input type="hidden" name="_thumbnail_id" id="thumbnail_id" value="<?php
                            if ($course_id !== 0) {
                                echo get_post_meta($course_id, '_thumbnail_id', true);
                            }
                            ?>" />
                                   <?php
                                   //get_the_post_thumbnail($course_id, 'course_thumb', array(100, 100));
                                   //echo wp_get_attachment_image(get_post_meta($course_id, '_thumbnail_id', true), array(100, 100));
                                   //echo 'asdads'.get_post_meta($course_id, '_thumbnail_id', true);
                                   ?>
                        </div>
                    </div>
                </div>
            </div> <!-- course-holder-wrap -->

            <?php
            global $content_width;

            wp_enqueue_style('thickbox');
            wp_enqueue_script('thickbox');
            wp_enqueue_media();
            wp_enqueue_script('media-upload');

            $supported_video_extensions = implode(",", wp_get_video_extensions());

            if (!empty($data)) {
                if (!isset($data->player_width) or empty($data->player_width)) {
                    $data->player_width = empty($content_width) ? 640 : $content_width;
                }
            }
            ?>

            <div class="course-holder-wrap">

                <div class="sidebar-name no-movecursor">
                    <h3><?php _e('Featured Course Video', 'cp'); ?></h3>
                </div>

                <div class="level-holder" id="sidebar-levels">
                    <div class='sidebar-inner'>

                        <div class="video_url_holder">
                            <?php _e('Put a URL (oEmbed support is required) or Browse for a video file.', 'cp'); ?>
                            <input class="course_video_url" type="text" size="36" name="meta_course_video_url" value="<?php echo esc_attr($course_video_url); ?>" />

                            <?php
//echo '(' . $supported_video_extensions . ')';
                            ?>

                            <input type="button" class="course_video_url_button button-secondary" value="<?php _e('Browse', 'cp'); ?>" />

                        </div>

                    </div>
                </div>
            </div>



        </div> <!-- course-liquid-right -->
    </form>

</div> <!-- wrap -->