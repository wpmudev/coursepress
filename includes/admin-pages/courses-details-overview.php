<?php
global $page, $user_id, $coursepress_admin_notice;

if (isset($_GET['course_id'])) {
    $course = new Course($_GET['course_id']);
    $course_details = $course->get_course();
    $course_id = $_GET['course_id'];
} else {
    $course = new Course();
    $course_id = 0;
}

$course_marking_type = $course->details->course_marking_type;//get_post_meta($course_id, 'course_marking_type', true);
$class_size = $course->details->class_size;
$enroll_type = $course->details->enroll_type;
$passcode = $course->details->passcode;
$course_start_date = $course->details->course_start_date;
$course_end_date = $course->details->course_end_date;
$enrollment_start_date = $course->details->enrollment_start_date;
$enrollment_end_date = $course->details->enrollment_end_date;

if (isset($_POST['action']) && ($_POST['action'] == 'add' || $_POST['action'] == 'update')) {

    if (wp_verify_nonce($_REQUEST['_wpnonce'], 'course_details_overview_' . $user_id)) {

        $new_post_id = $course->update_course();

        if ($new_post_id != 0) {
            wp_redirect('?page=' . $page . '&course_id=' . $new_post_id);
        } else {
            //an error occured
        }
    }
}
?>

<div class='wrap nocoursesub'>
    <form action='?page=<?php echo esc_attr($page); ?><?php echo ($course_id !== 0) ? '&course_id='.$course_id : '' ?>' name='course-add' method='post'>
        
        <div class='course-liquid-left'>

            <div id='course-left'>


                <?php wp_nonce_field('course_details_overview_' . $user_id); ?>

                <?php if (isset($course_id)) { ?>
                    <input type="hidden" name="course_id" value="<?php echo esc_attr($course_id); ?>" />
                    <input type="hidden" name="action" value="update" />
                    <input type="hidden" name="plugin_notice" value="<?php _e('Course has been updated.', 'cp'); ?>" />
                <?php } else { ?>
                    <input type="hidden" name="action" value="add" />
                    <input type="hidden" name="plugin_notice" value="<?php _e('New Course has been created.', 'cp'); ?>" />
                <?php } ?>

                <div id='edit-sub' class='course-holder-wrap'>

                    <div class='sidebar-name no-movecursor'>
                        <h3><?php _e('Course Details', 'cp'); ?></h3>
                    </div>

                    <div class='course-holder'>
                        <div class='course-details'>
                            <label for='course_name'><?php _e('Course Name', 'cp'); ?></label>
                            <input class='wide' type='text' name='course_name' id='course_name' value='<?php echo esc_attr(stripslashes($course_details->post_title)); ?>' />
                            
                            <br/><br/>
                            <label for='course_excerpt'><?php _e('Course Excerpt', 'cp'); ?></label>
                            <?php
                            $args = array("textarea_name" => "course_excerpt", "textarea_rows" => 3);

                            if (!isset($course_excerpt->post_excerpt)) {
                                $course_excerpt = new StdClass;
                                $course_excerpt->post_excerpt = '';
                            }

                            $desc = '';
                            wp_editor(stripslashes($course_details->post_excerpt), "course_excerpt", $args);
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

                            <div class="half">
                                <label for='meta_course_marking_type'><?php _e('Marking Type', 'cp'); ?></label>

                                <select class="wide" name="meta_course_marking_type" id="course_marking_type">
                                    <option value="percentages" <?php echo ($course_marking_type == 'percentages' ? 'selected=""' : '') ?>><?php _e('Percentages', 'cp'); ?></option>
                                    <option value="grade" <?php echo ($course_marking_type == 'grade' ? 'selected=""' : '') ?>><?php _e('Grade', 'cp'); ?></option>
                                </select>

                            </div>



                            <div class="fullwidth">
                                <label for='meta_class-size'><?php _e('Class size', 'cp'); ?></label>
                                <input class='spinners' name='meta_class_size' id='class_size' value='<?php echo esc_attr(stripslashes((is_numeric($class_size) ? $class_size : 0))); ?>' />
                                <p class="description"><?php _e('select 0 for infinite', 'cp'); ?></p>
                            </div>

                            <br clear="all" />

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

                            <br clear="all" />

                        </div>

                        <div class="buttons">
                            <input type="submit" value="<?php ($course_id == 0 ? _e('Create', 'cp') : _e('Update', 'cp')); ?>" class="button-primary" />
                            <?php if ($course_id !== 0) { ?>
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
                            <?php coursepress_instructors_avatars($course_id); ?>
                        </div>

                        
                        <?php coursepress_instructors_avatars_array(); ?>
                        <div class="clearfix"></div>
                        <?php coursepress_instructors_drop_down(); ?>

                        <div class="inner-right inner-link">
                            <a href="javascript:void(0)" id="add-instructor-trigger"><?php _e('Add new Instructor', 'cp'); ?></a>
                        </div>
                    </div>
                </div>
            </div> <!-- course-holder-wrap -->

        </div> <!-- course-liquid-right -->
    </form>

</div> <!-- wrap -->