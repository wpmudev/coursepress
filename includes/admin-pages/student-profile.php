<?php
if (isset($_GET['student_id']) && is_numeric($_GET['student_id'])) {
    $student = new Student($_GET['student_id']);
}
?>
<div class='wrap nocoursesub'>
    <form action='?page=<?php echo esc_attr($page); ?><?php echo ($course_id !== 0) ? '&course_id=' . $course_id : '' ?>' name='course-add' method='post'>

        <div class='course-liquid-left'>

            <div id='course-left'>

                <?php wp_nonce_field('student_profile_' . $student->id); ?>

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
                    <table cellspacing="0" class="widefat">
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
                                                <div><span class="info_caption"><?php _e('Start', 'cp'); ?>:</span><span class="info"><?php echo $course_object->course_start_date; ?></span></div>
                                                <div><span class="info_caption"><?php _e('End', 'cp'); ?>:</span><?php echo $course_object->course_end_date; ?></span></div>
                                                <div><span class="info_caption"><?php _e('Duration', 'cp'); ?>:</span><span class="info"><?php echo get_number_of_days_between_dates($course_object->course_start_date, $course_object->course_end_date); ?> <?php _e('Days', 'cp'); ?></span></div>
                                            </div>
                                        </td>
                                    </tr>
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

        <?php if (1 == 1) { ?>
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
                                                        <div><span class="info_caption"><?php _e('First Name', 'cp'); ?>:</span><span class="info"><?php echo $student->user_firstname; ?></span></div>
                                                        <div><span class="info_caption"><?php _e('Last Name', 'cp'); ?>:</span><?php echo $student->user_lastname; ?></div>
                                                        <div><span class="info_caption"><?php _e('E-mail', 'cp'); ?>:</span><span class="info"><a href="mailto:<?php echo $student->user_email; ?>"><?php echo $student->user_email; ?></a></span></div>
                                                        <div><span class="info_caption"><?php _e('Courses', 'cp'); ?>:</span><span class="info"><?php echo $student->get_courses_number(); ?></span></div>
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

                                <div class="edit-profile-link"><a href="user-edit.php?user_id=<?php echo $instructor->ID; ?>">Edit Profile</a></div>
                            </div>

                            <div class="clearfix"></div>

                        </div>
                    </div>
                </div> <!-- course-holder-wrap -->

            </div> <!-- course-liquid-right -->
        <?php } ?>

    </form>

</div> <!-- wrap -->