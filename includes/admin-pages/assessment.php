<div class="wrap nosubsub">
    <div class="icon32 icon32-posts-page" id="icon-edit-pages"><br></div>
    <h2><?php _e('Assessment', 'cp'); ?></h2>

    <?php
    if (isset($_GET['page_num'])) {
        $page_num = $_GET['page_num'];
    } else {
        $page_num = 1;
    }

    $unit_module_main = new Unit_Module();

    if (isset($_GET['response_id'])) {
        $response_id = $_GET['response_id'];
        $module_id = $_GET['module_id'];
        $user_id = $_GET['user_id'];
        $course_id = $_GET['course_id'];
        $unit_id = $_GET['unit_id'];
        ?>
        <div class="assessment-response-wrap">
            <form action="" name="assessment-response" method="post">

                <?php wp_nonce_field('course_details_overview'); ?>
                <input type="hidden" name="response_id" value="<?php echo $response_id; ?>">
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

                <div id="edit-sub" class="assessment-holder-wrap">

                    <?php
                    $unit_module = $unit_module_main->get_module($module_id);
                    $student = get_userdata($user_id);
                    ?>

                    <div class="sidebar-name no-movecursor">
                        <h3><span class="response-response-name"><?php echo $unit_module->post_title; ?></span><span class="response-student-info"><a href="admin.php?page=students&action=view&student_id=<?php echo $user_id; ?>"><?php echo $student->display_name; ?></a></span></h3>
                    </div>

                    <div class="assessment-holder">
                        <div class="assesment-response-details">

                            <?php if (isset($unit_module->post_content) && !empty($unit_module->post_content)) { ?>
                                <div class="module_response_description">
                                    <label><?php _e('Description', 'cp'); ?></label>
                                    <?php echo $unit_module->post_content; ?>
                                </div>

                                <div class="full regular-border-devider"></div>
                            <?php } ?>

                            <?php
                            $mclass = $unit_module->module_type;
                            $response = new $mclass();

                            echo $response->get_response_form($user_id, $module_id);
                            echo $unit_module_main->get_module_response_comment_form($response_id);
                            ?>
                            <br clear="all">
                        </div>


                        <div class="buttons">

                            <div class="additional_grade_info">
                                <?php
                                $grade_data = $unit_module_main->get_response_grade($response_id);
                                $grade = $grade_data['grade'];
                                $instructor_id = $grade_data['instructor'];
                                $instructor_name = get_userdata($instructor_id);
                                $grade_time = date_i18n(get_option('date_format').' '.get_option('time_format'), $grade_data['time']);

                                if (!empty($grade)) {
                                    _e('Grade by ');
                                    ?>
                                    <a href="admin.php?page=instructors&action=view&instructor_id=<?php echo $instructor_id; ?>"><?php echo $instructor_name->display_name; ?></a>
                                    <?php
                                    _e(' on ' . $grade_time);
                                }
                                ?>

                            </div>

                            <?php submit_button('Save Changes', 'primary', 'save-response-changes'); ?>

                            <select name="response_grade" id="response_grade">
                                <option value=""><?php _e('Choose Grade', 'cp'); ?></option>
                                <?php
                                for ($i = 0; $i <= 100; $i++) {
                                    ?>
                                    <option value="<?php echo $i; ?>" <?php selected($grade, $i, true); ?>><?php echo $i; ?>%</option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>

                        <br clear="all" />

                    </div>
                </div>

            </form>

        </div>
        <?php
        /* ================================ARCHIVE============================== */
    } else {
        ?>
        <div class="tablenav">
            <form method="get" id="course-filter">
                <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>" />
                <input type="hidden" name="page_num" value="<?php echo esc_attr($page_num); ?>" />
                <div class="alignleft actions">
                    <select name="course_id" id="dynamic_courses">

                        <?php
                        $assessment_page = 1;

                        $args = array(
                            'post_type' => 'course',
                            'post_status' => 'any',
                            'posts_per_page' => -1
                        );

                        $courses = get_posts($args);
                        $courses_with_students = 0;
                        $course_num = 0;
                        $first_course_id = 0;
                        $count = 0;

                        foreach ($courses as $course) {
                            if ($course_num == 0) {
                                $first_course_id = $course->ID;
                            }

                            $count = $unit_module_main->get_ungraded_response_count($course->ID);

                            $course_obj = new Course($course->ID);
                            $course_object = $course_obj->get_course();
                            if ($course_obj->get_number_of_students() >= 1) {
                                $courses_with_students++;
                                ?>
                                <option value="<?php echo $course->ID; ?>" <?php echo ((isset($_GET['course_id']) && $_GET['course_id'] == $course->ID) ? 'selected="selected"' : ''); ?>><?php echo $course->post_title . ' (' . $count . ')'; ?></option>
                                <?php
                            }
                            $course_num++;
                            $count = 0;
                        }

                        if ($courses_with_students == 0) {
                            ?>
                            <option value=""><?php _e('0 courses with enrolled students.', 'cp'); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <?php
                    $current_course_id = 0;
                    if (isset($_GET['course_id'])) {
                        $current_course_id = $_GET['course_id'];
                    } else {
                        $current_course_id = $first_course_id;
                    }
                    ?>

                    <?php
                    if ($current_course_id !== 0) {//courses exists, at least one 
                        $course = new Course($current_course_id);
                        $course_units = $course->get_units();

                        if (count($course_units) >= 1) {

                            //search for students
                            if (isset($_GET['classes'])) {
                                $classes = $_GET['classes'];
                            } else {
                                $classes = 'all';
                            }
                            ?>
                            <select name="classes" id="dynamic_classes" name="dynamic_classes">
                                <option value="all" <?php selected($classes, 'all', true); ?>><?php _e('All Classes', 'cp'); ?></option>
                                <option value="" <?php selected($classes, '', true); ?>><?php _e('Default', 'cp'); ?></option>
                                <?php
                                $course_classes = get_post_meta($current_course_id, 'course_classes', true);
                                foreach ($course_classes as $course_class) {
                                    ?>
                                    <option value="<?php echo $course_class; ?>" <?php selected($classes, $course_class, true); ?>><?php echo $course_class; ?></option>
                                    <?php
                                }
                                ?>
                            </select>

                            <label class="ungraded"><?php _e('Ungraded Modules Only', 'cp'); ?>
                                <?php
                                if (isset($_GET['ungraded']) && $_GET['ungraded'] == 'yes') {
                                    $ungraded_filter = 'yes';
                                } else {
                                    $ungraded_filter = 'no';
                                }
                                ?>
                                <input type="checkbox" id="ungraded" name="ungraded" value="yes" <?php checked($ungraded_filter, 'yes', true); ?> />
                            </label>

                            <?php
                        }
                    }
                    ?>

                </div>
            </form>
        </div><!--tablenav-->

        <?php
        if ($current_course_id !== 0) {//courses exists, at least one is in place 
            if (count($course_units) >= 1) {
                ?>
                <div class="assessment">
                    <div id="tabs">

                        <ul class="sidebar-name">
                            <?php
                            for ($i = 1; $i <= count($course_units); $i++) {
                                $current_unit = $course_units[$i - 1];
                                ?>
                                <li><a href="#tabs-<?php echo $i; ?>" alt="<?php echo $current_unit->post_title; ?>" title="<?php echo $current_unit->post_title; ?>"><?php echo $i; ?></a></li>
                            <?php } ?>
                        </ul>

                        <?php
                        for ($i = 1; $i <= count($course_units); $i++) {
                            $current_unit = $course_units[$i - 1];
                            ?>

                            <?php
                            //search for students
                            if (isset($_GET['classes'])) {
                                $classes = $_GET['classes'];
                            } else {
                                $classes = 'all';
                            }

                            if ($classes !== 'all') {
                                $args = array(
                                    'meta_query' => array(
                                        array(
                                            'key' => 'enrolled_course_class_' . $current_course_id,
                                            'value' => $classes,
                                        ))
                                );
                            } else {
                                $args = array(
                                    'meta_query' => array(
                                        array(
                                            'key' => 'enrolled_course_class_' . $current_course_id
                                        ))
                                );
                            }

                            //$student_search = new WP_User_Query($args);

                            $additional_url_args = array();
                            $additional_url_args['course_id'] = $current_course_id;
                            $additional_url_args['classes'] = urlencode($classes);
                            $additional_url_args['ungraded'] = (isset($_GET['ungraded']) ? $_GET['ungraded'] : 'no');
                            
                            $student_search = new Student_Search('', $page_num, array(), $args, $additional_url_args);
                            ?>
                            <div id="tabs-<?php echo $i; ?>">
                                <h2><?php echo $current_unit->post_title; ?></h2>
                                <?php
                                $columns = array(
                                    "name" => __('Student Name', 'cp'),
                                    "module" => __('Module', 'cp'),
                                    "title" => __('Title', 'cp'),
                                    "submission_date" => __('Submitted', 'cp'),
                                    "response" => __('Response', 'cp'),
                                    "grade" => __('Grade', 'cp'),
                                    "comment" => __('Comment', 'cp'),
                                );


                                $col_sizes = array(
                                    '12', '12', '36', '15', '10', '10', '5'
                                );
                                ?>

                                <table cellspacing="0" class="widefat shadow-table assessment-archive-table">
                                    <thead>
                                        <tr>
                                            <?php
                                            $n = 0;
                                            foreach ($columns as $key => $col) {
                                                ?>
                                                <th style="" class="manage-column column-<?php echo $key; ?>" width="<?php echo $col_sizes[$n] . '%'; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
                                                <?php
                                                $n++;
                                            }
                                            ?>
                                        </tr>
                                    </thead>

                                    <?php
                                    foreach ($student_search->get_results() as $user) {
                                        $style = ( ' alternate' == $style ) ? '' : ' alternate';
                                        $user_object = new Student($user->ID);

                                        $module = new Unit_Module();
                                        $modules = $module->get_modules($current_unit->ID);

                                        $input_modules_count = 0;

                                        foreach ($modules as $mod) {
                                            $class_name = $mod->module_type;
                                            $module = new $class_name();

                                            if ($module->front_save) {

                                                $input_modules_count++;
                                            }
                                        }

                                        $current_row = 0;

                                        foreach ($modules as $mod) {
                                            $class_name = $mod->module_type;
                                            $module = new $class_name();

                                            if ($module->front_save) {
                                                $response = $module->get_response($user_object->ID, $mod->ID);
                                                $visibility_class = (count($response) >= 1 ? '' : 'less_visible_row');

                                                $grade_data = $unit_module_main->get_response_grade($response->ID);

                                                if (isset($_GET['ungraded']) && $_GET['ungraded'] == 'yes') {
                                                    if (count($response) >= 1 && !$grade_data) {
                                                        $general_col_visibility = true;
                                                    } else {
                                                        $general_col_visibility = false;
                                                    }
                                                } else {
                                                    $general_col_visibility = true;
                                                }
                                                ?>
                                                <tr id='user-<?php echo $user_object->ID; ?>' class="<?php echo $style; ?>">
                                                    <?php if ($current_row == 0) { ?>
                                                        <td class="<?php echo $style . ' first-right-border'; ?>" rowspan="<?php echo $input_modules_count; ?>">
                                                            <span class="uppercase block"><?php echo $user_object->last_name; ?></span>
                                                            <?php echo $user_object->first_name; ?>
                                                        </td>
                                                        <?php
                                                    }
                                                    ?>
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
                                                            <?php echo (count($response) >= 1 ? $response->post_date : __('Not submitted yet', 'cp')); ?>
                                                        </td>

                                                        <td class="<?php echo $style . ' ' . $visibility_class; ?>">
                                                            <?php
                                                            if (count($response) >= 1) {
                                                                ?>
                                                                <a class="assessment-view-response-link" href="admin.php?page=assessment&course_id=<?php echo $current_course_id; ?>&unit_id=<?php echo $current_unit->ID; ?>&user_id=<?php echo $user_object->ID; ?>&module_id=<?php echo $mod->ID; ?>&response_id=<?php echo $response->ID; ?>&assessment_page=<?php echo $assessment_page; ?>"><?php _e('View', 'cp'); ?></a>

                                                                <?php
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                        </td>

                                                        <td class="<?php echo $style . ' ' . $visibility_class; ?>">
                                                            <?php
                                                            $grade = $grade_data['grade'];
                                                            $instructor_id = $grade_data['instructor'];
                                                            $instructor_name = get_userdata($instructor_id);
                                                            $grade_time = date_i18n(get_option('date_format').' '.get_option('time_format'), $grade_data['time']);

                                                            if (count($response) >= 1) {
                                                                if ($grade_data) {
                                                                    ?>
                                                                    <a class="response_grade" alt="<?php
                                                                    _e('Grade by ');
                                                                    echo $instructor_name->display_name;
                                                                    _e(' on ' . $grade_time);
                                                                    ?>" title="<?php
                                                                       _e('Grade by ');
                                                                       echo $instructor_name->display_name;
                                                                       _e(' on ' . $grade_time);
                                                                       ?>"><?php echo $grade; ?>%</a>
                                                                       <?php
                                                                   } else {
                                                                       _e('Pending grade', 'cp');
                                                                   }
                                                               } else {
                                                                   echo '-';
                                                               }
                                                               ?>
                                                        </td>

                                                        <td class="<?php echo $style . ' ' . $visibility_class; ?>">
                                                            <?php
                                                            $comment = $unit_module_main->get_response_comment($response->ID);

                                                            if ($comment) {
                                                                ?>
                                                                <a class="response_comment" alt="<?php echo $comment; ?>" title="<?php echo $comment; ?>">✓</a>
                                                                <?php
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                        </td>
                                                    <?php }//general col visibility  ?>
                                                </tr>
                                                <?php
                                            }
                                            $current_row++;
                                        }
                                    }

                                    if ($input_modules_count == 0) {
                                        ?>
                                        <tr>
                                            <td colspan="7"><?php _e('0 input modules in the selected unit.', 'cp'); ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>

                                </table>

                                <div class="tablenav">
                                    <div class="tablenav-pages"><?php $student_search->page_links(); ?></div>
                                </div><!--/tablenav-->

                            </div><!--a tab-->
                        <?php } ?>
                    </div><!--tabs-->
                </div><!--assessment-->

                <?php
            } else {
                ?>
                <p><?php _e('0 Units within the selected course.'); ?></p>
                <?php
            }
        }//Course exists
        ?>

    </div>
    <?php
}//Regular (not view) ?>