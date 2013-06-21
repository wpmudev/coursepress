<?php
global $page;
?>
<div class='wrap nocoursesub'>

    <div class='course-liquid-left'>

        <div id='course-left'>
            <form action='?page=<?php echo $page; ?>' name='course-add' method='post'>

                <div id='edit-sub' class='course-holder-wrap'>

                    <div class='sidebar-name no-movecursor'>
                        <h3><?php _e('New Course Details', 'cp'); ?></h3>
                    </div>

                    <div class='course-holder'>
                        <div class='course-details'>
                            <label for='course_name'><?php _e('Course Name', 'cp'); ?></label>
                            <input class='wide' type='text' name='course_name' id='course_name' value='<?php echo esc_attr(stripslashes($course->title)); ?>' />
                            <br/><br/>
                            <label for='course_name'><?php _e('Course Description', 'cp'); ?></label>
                            <?php
                            $args = array("textarea_name" => "course_description", "textarea_rows" => 5);

                            if (!isset($course->course_description)) {
                                $course->course_description = '';
                            }

                            $desc = '';
                            wp_editor(stripslashes($course->course_description), "course_description", $args);
                            ?>
                            <br/>

                            <div class="half">
                                <label for='course_unit_number'><?php _e('Number of units', 'cp'); ?></label>
                                <input class='wide' type='text' name='course_unit_number' id='course_unit_number' value='<?php echo esc_attr(stripslashes($course->course_pricetext)); ?>' />
                            </div>

                            <div class="half">
                                <label for='course_marking_type'><?php _e('Marking Type', 'cp'); ?></label>
                                <input class='wide' type='text' name='course_marking_type' id='course_marking_type' value='<?php echo esc_attr(stripslashes($course->course_pricetext)); ?>' />
                            </div>


                        </div>

                        <div class='buttons'>
                            <?php
                            if ($course->id > 0) {
                                wp_original_referer_field(true, 'previous');
                                wp_nonce_field('update-' . $course->id);
                                ?>
                                <a href='?page=<?php echo $page; ?>' class='cancellink' title='Cancel edit'><?php _e('Cancel', 'cp'); ?></a>
                                <input type='submit' value='<?php _e('Update', 'cp'); ?>' class='button-primary' />
                                <input type='hidden' name='action' value='updated' />
                                <?php
                            } else {
                                wp_original_referer_field(true, 'previous');
                                wp_nonce_field('add-' . $course->id);
                                ?>
                                <a href='?page=<?php echo $page; ?>' class='cancellink' title='Cancel add'><?php _e('Cancel', 'cp'); ?></a>
                                <input type='submit' value='<?php _e('Add', 'cp'); ?>' class='button-primary' />
                                <input type='hidden' name='action' value='added' />
                                <?php
                            }
                            ?>
                        </div>

                    </div>
                </div>
            </form>
        </div>


    </div> <!-- course-liquid-left -->

    <div class='course-liquid-right'>
        <div class="course-holder-wrap">

            <div class="sidebar-name no-movecursor">
                <h3><?php _e('Course Instructor(s)', 'cp'); ?></h3>
            </div>

            <div class="level-holder" id="sidebar-levels">
                <ul class='courses courses-draggable'>
                    <li class='level-draggable'>
                        <div class='action action-draggable'>
                            <div class='action-top closed'>
                                Instructor Name...drop down + ajax to be added here
                            </div>
                        </div>
                    </li>
                </ul>

            </div>
        </div> <!-- course-holder-wrap -->

    </div> <!-- course-liquid-right -->

</div> <!-- wrap -->