<?php
global $page, $user_id, $coursepress_admin_notice;

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

    if (!isset($_POST['meta_open_ended_course'])) {
        $_POST['meta_open_ended_course'] = 'off';
    }

    $new_post_id = $course->update_course();

    if ($new_post_id != 0) {
        if (isset($_GET['ms'])) {
            wp_redirect('?page=' . $page . '&course_id=' . $new_post_id . '&ms=' . $_GET['ms']);
        } else {
            wp_redirect('?page=' . $page . '&course_id=' . $new_post_id);
        }
    } else {
        //an error occured
    }
}

if (isset($_GET['course_id'])) {
//$course_marking_type = $course->details->course_marking_type; //get_post_meta($course_id, 'course_marking_type', true);
    $class_size = $course->details->class_size;
    $enroll_type = $course->details->enroll_type;
    $passcode = $course->details->passcode;
    $course_start_date = $course->details->course_start_date;
    $course_end_date = $course->details->course_end_date;
    $enrollment_start_date = $course->details->enrollment_start_date;
    $enrollment_end_date = $course->details->enrollment_end_date;
    $open_ended_course = $course->details->open_ended_course;
    //$allow_course_discussion = $course->details->allow_course_discussion;
} else {
    $class_size = 0;
    $enroll_type = '';
    $passcode = '';
    $course_start_date = '';
    $course_end_date = '';
    $enrollment_start_date = '';
    $enrollment_end_date = '';
    $open_ended_course = 'off';
    //$allow_course_discussion = 'off';
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

                <div id='edit-sub' class='course-holder-wrap'>

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
                            wp_editor(stripslashes((isset($_GET['course_id']) ? $course_details->post_excerpt : '')), "course_excerpt", $args);
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
                            wp_editor(stripslashes($course_details->post_content), "course_description", $args);
                            ?>
                            <br/>

                            <!--<div class="half">
                                <label for='meta_course_marking_type'><?php _e('Marking Type', 'cp'); ?></label>

                                <select class="wide" name="meta_course_marking_type" id="course_marking_type">
                                    <option value="percentages" <?php echo ($course_marking_type == 'percentages' ? 'selected=""' : '') ?>><?php _e('Percentages', 'cp'); ?></option>
                                    <option value="grade" <?php echo ($course_marking_type == 'grade' ? 'selected=""' : '') ?>><?php _e('Grade', 'cp'); ?></option>
                                </select>

                            </div>-->


                            <div class="half">
                                <label for='meta_class-size'><?php _e('Class size', 'cp'); ?></label>
                                <input class='spinners' name='meta_class_size' id='class_size' value='<?php echo esc_attr(stripslashes((is_numeric($class_size) ? $class_size : 0))); ?>' />
                                <p class="description"><?php _e('select 0 for infinite', 'cp'); ?></p>
                            </div>

                            <!--<br clear="all" />-->

                            <div class="half">
                                <label for='meta_enroll_type'><?php _e('How & Who can Enroll?', 'cp'); ?></label>

                                <select class="wide" name="meta_enroll_type" id="enroll_type">
                                    <option value="anyone" <?php echo ($enroll_type == 'anyone' ? 'selected=""' : '') ?>><?php _e(' Anyone ', 'cp'); ?></option>
                                    <option value="passcode" <?php echo ($enroll_type == 'passcode' ? 'selected=""' : '') ?>><?php _e('Anyone with a pass code', 'cp'); ?></option>
                                    <option value="manually" <?php echo ($enroll_type == 'manually' ? 'selected=""' : '') ?>><?php _e('Manually added only', 'cp'); ?></option>
                                </select>

                            </div>


                            <div class="half" id="enroll_type_holder" <?php echo ($enroll_type <> 'passcode' ? 'style="display:none"' : '') ?>>
                                <label for='meta_enroll_type'><?php _e('Pass Code', 'cp'); ?></label>
                                <input type="text" name="meta_passcode" value="<?php echo esc_attr(stripslashes($passcode)); ?>" />
                                <p class="description"><?php _e('Students will need to enter the pass code in order to enroll', 'cp'); ?></p>
                            </div>

                            <br clear="all" />

                            <div class="full border-devider">
                                <label><?php _e('Open-ended course:', 'cp'); ?>
                                    <input type="checkbox" name="meta_open_ended_course" id="open_ended_course" <?php echo ($open_ended_course == 'on') ? 'checked' : ''; ?> />
                                </label>

                                <p class="description"><?php _e('The first or last course or enrollment date having no upper or lower limit.', 'cp') ?></p>
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

                                <label><?php _e('Enrollment Dates:', 'cp'); ?></label>

                                <p class="description"><?php _e('Student may enroll only during selected date range', 'cp'); ?></p>


                                <div class="half"><?php _e('Start Date', 'cp'); ?>
                                    <input type="text" class="dateinput" name="meta_enrollment_start_date" value="<?php echo esc_attr($enrollment_start_date); ?>" />
                                </div>

                                <div class="half"><?php _e('End Date', 'cp'); ?>
                                    <input type="text" class="dateinput" name="meta_enrollment_end_date" value="<?php echo esc_attr($enrollment_end_date); ?>" />
                                </div>
                            </div><!--/all-course-dates-->
                            
                            <!--<div class="full border-devider">
                                <label><?php _e('Allow Course Discussion', 'cp'); ?>
                                    <input type="checkbox" name="meta_allow_course_discussion" id="allow_course_discussion" <?php echo ($allow_course_discussion == 'on') ? 'checked' : ''; ?> />
                                </label>

                                <p class="description"><?php _e('If checked, students can post comments and follow discussion within the course.', 'cp') ?></p>
                            </div>-->

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

                        <?php if ((current_user_can('coursepress_assign_and_assign_instructor_course_cap')) || (current_user_can('coursepress_assign_and_assign_instructor_my_course_cap') && $course->details->post_author == get_current_user_id())) { ?>
                            <?php coursepress_instructors_avatars_array(); ?>

                            <div class="clearfix"></div>
                            <?php coursepress_instructors_drop_down(); ?>
                            <?php if (coursepress_get_number_of_instructors() != 0) { ?>
                                <div class = "inner-right inner-link">
                                    <a href = "javascript:void(0)" id = "add-instructor-trigger"><?php _e('Add new Instructor', 'cp');
                                ?></a>
                                </div>
                                <?php
                            } else {
                                _e('You do not have any available instructors yet. <a href="user-new.php" target="_new">Create one user with the Instructor role</a> in order to assign it to the courses.', 'cp');
                            }
                            ?>

                        <?php } ?>

                    </div>
                </div>
            </div> <!-- course-holder-wrap -->

        </div> <!-- course-liquid-right -->
    </form>

</div> <!-- wrap -->