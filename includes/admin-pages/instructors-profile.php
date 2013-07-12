<?php
if (isset($_GET['instructor_id']) && is_numeric($_GET['instructor_id'])) {
    $instructor = new Instructor($_GET['instructor_id']);
}
?>
<div class='wrap nocoursesub'>
    <form action='?page=<?php echo esc_attr($page); ?><?php echo ($course_id !== 0) ? '&course_id=' . $course_id : '' ?>' name='course-add' method='post'>

        <div class='course-liquid-left'>

            <div id='course-left'>

                <?php wp_nonce_field('instructor_profile_' . $instructor->id); ?>

                <div id='edit-sub' class='course-holder-wrap'>

                    <div class='sidebar-name no-movecursor'>
                        <h3><?php _e('Profile', 'cp'); ?></h3>

                    </div>

                    <?php
                    $columns = array(
                        "course" => __('', 'cp'),
                        "additional_info" => __('', 'cp'),
                    );
                    ?>
                    <!--PROFILE START-->
                    <table cellspacing="0" class="widefat instructor-profile">
                        <tbody>
                            <tr>
                                <td><?php echo get_avatar($instructor->ID, '80'); ?></td>
                                <td>
                                    <div class="course_additional_info">
                                        <div><span class="info_caption"><?php _e('First Name', 'cp'); ?>:</span><span class="info"><?php echo $instructor->user_firstname; ?></span></div>
                                        <div><span class="info_caption"><?php _e('Last Name', 'cp'); ?>:</span><?php echo $instructor->user_lastname; ?></div>
                                        <div><span class="info_caption"><?php _e('E-mail', 'cp'); ?>:</span><span class="info"><a href="mailto:<?php echo $instructor->user_email; ?>"><?php echo $instructor->user_email; ?></a></span></div>
                                        <div><span class="info_caption"><?php _e('Courses', 'cp'); ?>:</span><span class="info"><?php echo $instructor->get_courses_number(); ?></span></div>
                                    </div>
                                </td>

                            </tr>

                        </tbody>
                    </table>
                    <!--PROFILE END-->

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

                            $assigned_courses = $instructor->get_assigned_courses_ids();

                            if (count($assigned_courses) == 0) {
                                ?>
                                <tr>
                                    <td><div class="zero-row"><?php _e('0 courses assigned to the instructor', 'cp'); ?></div></td>
                                    <td></td>
                                </tr>
                                <?php
                            }

                            foreach ($assigned_courses as $course_id) {

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


        <div class='course-liquid-right'>

            <div class="course-holder-wrap">

                <div class="sidebar-name no-movecursor">
                    <h3><?php _e('Edit Profile', 'cp'); ?></h3>
                </div>

                <div class="level-holder" id="sidebar-levels">
                    <div class='sidebar-inner'>
                        
                        <div class="instructors-info" id="instructors-info">
                            <label class="ins-box"><?php _e('First Name', 'cp'); ?>
                                <input type="user_firstname" value="<?php echo esc_attr($instructor->user_firstname); ?>" />
                            </label>

                            <label class="ins-box"><?php _e('Last Name', 'cp'); ?>
                                <input type="user_lastname" value="<?php echo esc_attr($instructor->user_lastname); ?>" />
                            </label>

                            <label class="ins-box"><?php _e('E-mail', 'cp'); ?>
                                <input type="user_email" value="<?php echo esc_attr($instructor->user_email); ?>" />
                            </label>

                            <label><?php _e('Bio', 'cp'); ?>
                                <?php
                                /*$args = array(
                                    "textarea_name" => "bio",
                                    "textarea_rows" => 3,
                                   
                                    "tinymce" => array(
                                        "theme_advanced_buttons1" => "bold,italic,underline,bullist,numlist",
                                        "theme_advanced_buttons2" => '',
                                        "theme_advanced_buttons3" => ''
                                    )
                                );*/

                                if (!isset($instructor->bio)) {
                                    $bio = new StdClass;
                                    $instructor->bio = '';
                                }

                                $args = array("textarea_name" => "course_excerpt", "textarea_rows" => 3);

                            if (!isset($course_excerpt->post_excerpt)) {
                                $course_excerpt = new StdClass;
                                $course_excerpt->post_excerpt = '';
                            }

                            $desc = '';
                            wp_editor(stripslashes($course_details->post_excerpt), "course_excerpt", $args);
                            
                                //wp_editor(esc_attr($instructor->bio), "bio", $args);
                                ?>
                            </label>
                        </div>

                        <div class="clearfix"></div>

                    </div>
                </div>
            </div> <!-- course-holder-wrap -->

        </div> <!-- course-liquid-right -->

    </form>

</div> <!-- wrap -->