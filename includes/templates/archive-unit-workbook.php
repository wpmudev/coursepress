<?php
/**
 * The units archive / grades template file
 * 
 * @package CoursePress
 */
global $coursepress;
$course_id = do_shortcode('[get_parent_course_id]');

//redirect to the parent course page if not enrolled
$coursepress->check_access($course_id);

add_thickbox();
?>

<?php
do_shortcode('[course_unit_archive_submenu]');
?>

<div class="clearfix"></div>

<?php
if (have_posts()) {
    while (have_posts()) {
        the_post();
        ?>
        <div class="workbook_units">
            <div class="unit_title">
                <h3><?php the_title(); ?>
                    <span><?php echo do_shortcode('[course_unit_details field="student_unit_grade" unit_id="' . get_the_ID() . '"]');?>% completed</span>
                </h3>
            </div>
            <div class="accordion-inner">
                <?php
                $columns = array(
                    "module" => __('Element', 'cp'),
                    "title" => __('Title', 'cp'),
                    "submission_date" => __('Submitted', 'cp'),
                    "response" => __('Answer', 'cp'),
                    "grade" => __('Grade', 'cp'),
                    "comment" => __('Comment', 'cp'),
                );


                $col_sizes = array(
                    '15', '30', '15', '10', '13', '5'
                );

                $unit_module_main = new Unit_Module();
                ?>
                <table cellspacing="0" class="widefat shadow-table assessment-archive-table">
                    <thead>
                        <tr>
                            <?php
                            $n = 0;
                            foreach ($columns as $key => $col) {
                                ?>
                                <th class="manage-column column-<?php echo $key; ?>" width="<?php echo $col_sizes[$n] . '%'; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
                                <?php
                                $n++;
                            }
                            ?>
                        </tr>
                    </thead>

                    <?php
                    $user_object = new Student(get_current_user_ID());

                    $module = new Unit_Module();
                    $modules = $module->get_modules(get_the_ID());

                    $input_modules_count = 0;

                    foreach ($modules as $mod) {
                        $class_name = $mod->module_type;
                        if (class_exists($class_name)) {
                            $module = new $class_name();
                            if ($module->front_save) {
                                $input_modules_count++;
                            }
                        }
                    }

                    $current_row = 0;
                    $style = '';
                    foreach ($modules as $mod) {
                        $class_name = $mod->module_type;

                        if (class_exists($class_name)) {
                            $module = new $class_name();

                            if ($module->front_save) {
                                $response = $module->get_response($user_object->ID, $mod->ID);
                                $visibility_class = (count($response) >= 1 ? '' : 'less_visible_row');

                                if (count($response) >= 1) {
                                    $grade_data = $unit_module_main->get_response_grade($response->ID);
                                }

                                if (isset($_GET['ungraded']) && $_GET['ungraded'] == 'yes') {
                                    if (count($response) >= 1 && !$grade_data) {
                                        $general_col_visibility = true;
                                    } else {
                                        $general_col_visibility = false;
                                    }
                                } else {
                                    $general_col_visibility = true;
                                }

                                $style = ( isset($style) && 'alternate' == $style ) ? '' : ' alternate';
                                ?>
                                <tr id='user-<?php echo $user_object->ID; ?>' class="<?php
                                echo $style;
                                echo 'row-' . $current_row;
                                ?>">

                                    <?php
                                    if ($general_col_visibility) {
                                        ?>
                                        <td class = "<?php echo $style . ' ' . $visibility_class; ?>">
                                            <?php echo $module->label;
                                            ?>
                                        </td>

                                        <td class="<?php echo $style . ' ' . $visibility_class; ?>">
                                            <?php echo $mod->post_title; ?>
                                        </td>

                                        <td class="<?php echo $style . ' ' . $visibility_class; ?>">
                                            <?php echo (count($response) >= 1 ? date('M d, Y', strtotime($response->post_date)) : __('Not submitted', 'cp')); ?>
                                        </td>

                                        <td class="<?php echo $style . ' ' . $visibility_class; ?>">
                                            <?php
                                            if (count($response) >= 1) {
                                                ?>
                                                <div id="response_<?php echo $response->ID; ?>" style="display:none;">
                                                    <?php if (isset($mod->post_content) && $mod->post_content !== '') { ?>
                                                        <div class="module_response_description">
                                                            <label><?php _e('Description', 'cp'); ?></label>
                                                            <?php echo $mod->post_content; ?>
                                                        </div>
                                                    <?php } ?>
                                                    <?php echo $module->get_response_form(get_current_user_ID(), $mod->ID); ?>

                                                    <?php
                                                    if (is_object($response) && !empty($response)) {

                                                        $comment = $unit_module_main->get_response_comment($response->ID);
                                                        if (!empty($comment)) {
                                                            ?>
                                                            <label class="comment_label"><?php _e('Comment', 'coursepress'); ?></label>
                                                            <div class="response_comment_front"><?php echo $comment; ?></div>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <a class="assessment-view-response-link button button-units thickbox" href="#TB_inline?width=500&height=300&inlineId=response_<?php echo $response->ID; ?>"><?php _e('View', 'cp'); ?></a>

                                                <?php
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>

                                        <td class="<?php echo $style . ' ' . $visibility_class; ?>">
                                            <?php
                                            if (isset($grade_data)) {
                                                $grade = $grade_data['grade'];
                                                $instructor_id = $grade_data['instructor'];
                                                $instructor_name = get_userdata($instructor_id);
                                                $grade_time = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $grade_data['time']);
                                            }
                                            if (count($response) >= 1) {
                                                if (isset($grade_data)) {
                                                    ?>
                                                    <?php echo $grade; ?>%
                                                    <?php
                                                } else {
                                                    _e('Pending', 'cp');
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>

                                        <td class="<?php echo $style . ' ' . $visibility_class; ?> td-center">
                                            <?php
                                            if (count($response) >= 1) {
                                                $comment = $unit_module_main->get_response_comment($response->ID);
                                            }
                                            if (isset($comment) && $comment !== '') {
                                                ?>
                                                <a alt="<?php echo $comment; ?>" title="<?php echo $comment; ?>"><i class="fa fa-comment"></i></a>
                                                <?php
                                            } else {
                                                echo '<i class="fa fa-comment-o"></i>';
                                            }
                                            ?>
                                        </td>
                                    <?php }//general col visibility         ?>
                                </tr>
                                <?php
                                $current_row++;
                            }
                        }
                    }


                    if (!isset($input_modules_count) || isset($input_modules_count) && $input_modules_count == 0) {
                        ?>
                        <tr>
                            <td colspan="7">
                                <?php
                                $unit_grade = do_shortcode('[course_unit_details field="student_unit_grade" unit_id="' . get_the_ID() . '"]');
                                _e('0 input elements in the selected unit.', 'cp');
                                ?>
                                <?php
                                if ($unit_grade == 0) {
                                    _e('Unit unread', 'cp');
                                } else {
                                    _e('Unit read - grade 100%', 'cp');
                                }
                                ?>
                            </td>
                        </tr>
            <?php
        }
        ?>

                </table>
            </div>
        </div>
        <?php
    }
} else {
    ?>
    <div class="zero-courses"><?php _e('0 Units in the course', 'cp'); ?></div>
    <?php
}
?>