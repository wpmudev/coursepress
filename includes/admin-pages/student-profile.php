<?php
if (isset($_GET['student_id']) && is_numeric($_GET['student_id'])) {
    $student = new Student($_GET['student_id']);
}

if (isset($_POST['course_id'])) {
    if (wp_verify_nonce($_POST['save_class_and_group_changes'], 'save_class_and_group_changes')) {
        $course = new Course($_POST['course_id']);
        if ((current_user_can('coursepress_change_students_group_class_cap')) || (current_user_can('coursepress_change_my_students_group_class_cap') && $course->details->post_author == get_current_user_id())) {
            $student->update_student_group($_POST['course_id'], $_POST['course_group']);
            $student->update_student_class($_POST['course_id'], $_POST['course_class']);
            $message = __('Group and Class for the student has been updated successfully.', 'cp');
        } else {
            $message = __('You do not have required permissions to change course group and/or class for the student.', 'cp');
        }
    }
}
?>
<div class='wrap nocoursesub'>


    <div class='course-liquid-left'>


        <div id='course-left'>
            <?php
            if (isset($message)) {
                ?>
                <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
                <?php
            }
            ?>
            <div id='edit-sub' class='course-holder-wrap'>

                <?php
                $columns = array(
                    "course" => __('', 'cp'),
                    "additional_info" => __('', 'cp'),
                );
                ?>

            </div>

            <div id='edit-sub' class='course-holder-wrap'>

                <div class='sidebar-name no-movecursor'>
                    <h3><?php _e('Courses', 'cp'); ?></h3>

                </div>

                <?php
                $columns = array(
                    "course" => __('', 'cp'),
                    "additional_info" => __('', 'cp'),
                );
                ?>
                <!--COURSES START-->
                <table cellspacing="0" class="widefat shadow-table">
                    <tbody>
                        <?php
                        $style = '';

                        $enrolled_courses = $student->get_enrolled_courses_ids();

                        if (count($enrolled_courses) == 0) {
                            ?>
                            <tr>
                                <td><div class="zero-row"><?php _e('Student did not enroll in any course yet.', 'cp'); ?></div></td>
                                <td></td>
                            </tr>
                            <?php
                        }

                        foreach ($enrolled_courses as $course_id) {

                            $course_object = new Course($course_id);
                            $course_object = $course_object->get_course();

                            if ($course_object) {

                                $style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
                                ?>
                                <tr id='user-<?php echo $course_object->ID; ?>' <?php echo $style; ?>>
                                    <td <?php echo $style; ?>><a href="?page=course_details&course_id=<?php echo $course_object->ID; ?>" width="75%">
                                            <div class="course_title"><?php echo $course_object->post_title; ?></a></div>
                                        <div class="course_excerpt"><?php echo get_the_course_excerpt($course_object->ID); ?></div>
                                    </td>

                                    <td <?php echo $style; ?> width="25%">
                                        <div class="course_additional_info">
                                                <div><span class="info_caption"><?php _e('Start', 'cp'); ?>:</span><span class="info"><?php if($course_object->open_ended_course == 'on'){ _e('Open-ended', 'cp'); }else{ echo $course_object->course_start_date;} ?></span></div>
                                                <div><span class="info_caption"><?php _e('End', 'cp'); ?>:</span><?php if($course_object->open_ended_course == 'on'){ _e('Open-ended', 'cp'); }else{ echo $course_object->course_end_date;} ?></span></div>
                                                <div><span class="info_caption"><?php _e('Duration', 'cp'); ?>:</span><span class="info"><?php if($course_object->open_ended_course == 'on'){ echo '&infin;';} else{ echo get_number_of_days_between_dates($course_object->course_start_date, $course_object->course_end_date); } ?> <?php _e('Days', 'cp'); ?></span></div>
                                            </div>
                                    </td>
                                </tr>
                                <?php if ((current_user_can('coursepress_change_students_group_class_cap')) || (current_user_can('coursepress_change_my_students_group_class_cap') && $course_object->post_author == get_current_user_id())) { ?>
                                <tr>
                                    <td <?php echo $style; ?> colspan="2">
                                        
                                        <form name="form_student_<?php echo $course_object->ID; ?>" id="form_student_<?php echo $course_object->ID; ?>" method="post" action="?page=students&action=view&student_id=<?php echo $student->ID; ?>">
                                            <?php wp_nonce_field('save_class_and_group_changes', 'save_class_and_group_changes'); ?>
                                            
                                                <input type="hidden" name="course_id" value="<?php echo $course_object->ID; ?>" />
                                                <input type="hidden" name="student_id" value="<?php echo $student->ID; ?>" />

                                                <div class="changable">
                                                    <label class="class-label">
                                                        <?php _e('Class', 'cp'); ?>

                                                        <select name="course_class" data-placeholder="'.__('Choose a Class...', 'cp').'" class="chosen-select chosen-select-student" id="course_class_<?php echo $course_object->ID; ?>">

                                                            <option value=""<?php echo ($student->{'enrolled_course_class_' . $course_object->ID} == '' ? ' selected="selected"' : ''); ?>><?php _e('Default', 'cp'); ?></option>
                                                            <?php
                                                            $course_classes = get_post_meta($course_object->ID, 'course_classes', true);
                                                            if (!empty($course_classes)) {
                                                                foreach ($course_classes as $class) {
                                                                    ?>
                                                                    <option value="<?php echo $class; ?>"<?php echo ($student->{'enrolled_course_class_' . $course_object->ID} == $class ? ' selected="selected"' : ''); ?>><?php echo $class; ?></option>
                                                                    <?php
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </label>

                                                    <label class="group-label">
                                                        <?php _e('Group', 'cp'); ?>
                                                        <select name="course_group" id="course_group_<?php echo $course_object->ID; ?>" data-placeholder="'.__('Choose a Group...', 'cp').'" class="chosen-select chosen-select-student">
                                                            <option value=""<?php echo ($student->{'enrolled_course_group_' . $course_object->ID} == '' ? ' selected="selected"' : ''); ?>><?php _e('Default', 'cp'); ?></option>
                                                            <?php
                                                            $groups = get_option('course_groups');
                                                            if (count($groups) >= 1 && $groups != '') {
                                                                foreach ($groups as $group) {
                                                                    ?>
                                                                    <option value="<?php echo $group; ?>"<?php echo ($student->{'enrolled_course_group_' . $course_object->ID} == $group ? ' selected="selected"' : ''); ?>><?php echo $group; ?></option>
                                                                    <?php
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </label>

                                                    <?php submit_button('Save Changes', 'secondary', 'save-group-class-changes', '') ?>

                                                </div>
                                            
                                        </form>
                                        
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
                <!--COURSES END-->

            </div>

        </div>
    </div> <!-- course-liquid-left -->

    <div class='course-liquid-right'>

        <div class="course-holder-wrap">

            <div class="sidebar-name no-movecursor">
                <h3><?php _e('Profile', 'cp'); ?></h3>
            </div>

            <div class="instructor-profile-holder" id="sidebar-levels">
                <div class='sidebar-inner'>

                    <div class="instructors-info" id="instructors-info">
                        <!--PROFILE START-->
                        <table cellspacing="0" class="widefat instructor-profile">
                            <tbody>
                                <tr>
                                    <?php if (isset($_GET['action']) && $_GET['action'] == 'view') { ?>
                                        <td><?php echo get_avatar($student->ID, '80'); ?></td>
                                        <td>
                                            <div class="instructor_additional_info">
                                                <div><span class="info_caption"><?php _e('First Name', 'cp'); ?>:</span> <span class="info"><?php echo $student->user_firstname; ?></span></div>
                                                <div><span class="info_caption"><?php _e('Last Name', 'cp'); ?>:</span> <span class="info"><?php echo $student->user_lastname; ?></span></div>
                                                <div><span class="info_caption"><?php _e('Email', 'cp'); ?>:</span> <span class="info"><a href="mailto:<?php echo $student->user_email; ?>"><?php echo $student->user_email; ?></a></span></div>
                                                <div><span class="info_caption"><?php _e('Courses', 'cp'); ?>:</span> <span class="info"><?php echo $student->get_courses_number(); ?></span></div>
                                            </div>
                                        </td>
                                    <?php } else { ?>
                                        <td>
                                            <label class="ins-box"><?php _e('First Name', 'cp'); ?>
                                                <input type="user_firstname" value="<?php echo esc_attr($student->user_firstname); ?>" />
                                            </label>

                                            <label class="ins-box"><?php _e('Last Name', 'cp'); ?>
                                                <input type="user_lastname" value="<?php echo esc_attr($student->user_lastname); ?>" />
                                            </label>

                                            <label class="ins-box"><?php _e('E-mail', 'cp'); ?>
                                                <input type="user_email" value="<?php echo esc_attr($student->user_email); ?>" />
                                            </label>
                                        </td>
                                        <td>

                                        </td>
                                    <?php } ?>

                                </tr>

                            </tbody>
                        </table>
                        <!--PROFILE END-->

                        <div class="edit-profile-link"><a href="user-edit.php?user_id=<?php echo $student->ID; ?>">Edit Profile</a></div>
                    </div>

                    <div class="clearfix"></div>

                </div>
            </div>
        </div> <!-- course-holder-wrap -->

    </div> <!-- course-liquid-right -->


</div> <!-- wrap -->