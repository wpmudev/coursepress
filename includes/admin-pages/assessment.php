<div class="wrap nosubsub">
    <div class="icon32 icon32-posts-page" id="icon-edit-pages"><br></div>
    <h2><?php _e('Assessment', 'cp'); ?></h2>

    <div class="tablenav">
        <form method="post" action="?page=<?php echo esc_attr($page); ?>" id="posts-filter">
            <div class="alignleft actions">
                <select name="courses" id="dynamic_courses">

                    <?php
                    $args = array(
                        'post_type' => 'course',
                        'post_status' => 'any',
                        'posts_per_page' => -1
                    );

                    $courses = get_posts($args);
                    $courses_with_students = 0;
                    $course_num = 0;
                    $first_course_id = 0;

                    foreach ($courses as $course) {
                        if ($course_num == 0) {
                            $first_course_id = $course->ID;
                        }

                        $course_obj = new Course($course->ID);
                        $course_object = $course_obj->get_course();
                        if ($course_obj->get_number_of_students() >= 1) {
                            $courses_with_students++;
                            ?>
                            <option value="admin.php?page=assessment&course_id=<?php echo $course->ID; ?>" <?php echo ((isset($_GET['course_id']) && $_GET['course_id'] == $course->ID) ? 'selected="selected"' : ''); ?>><?php echo $course->post_title . ' (' . $course_obj->get_number_of_students() . ')'; ?></option>
                            <?php
                        }
                        $course_num++;
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
                        ?>
                        <select name="classes" id="dynamic_classes">
                            <option value="all"><?php _e('All Classes', 'cp'); ?></option>
                            <option value=""><?php _e('Default', 'cp'); ?></option>
                            <?php
                            $course_classes = get_post_meta($current_course_id, 'course_classes', true);
                            foreach ($course_classes as $course_class) {
                                ?>
                                <option value="<?php echo $course_class; ?>"><?php echo $course_class; ?></option>
                                <?php
                            }
                            ?>
                        </select>
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

                        $student_search = new WP_User_Query($args);
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

                            <table cellspacing="0" class="widefat shadow-table">
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

                                                <td class="<?php echo $style; ?>">
                                                    <?php echo $module->label; ?>
                                                </td>

                                                <td class="<?php echo $style; ?>">
                                                    <?php echo $mod->post_title; ?>
                                                </td>

                                                <td class="<?php echo $style; ?>">
                                                    <?php
                                                    $response = $module->get_response($user_object->ID, $mod->ID);
                                                    ?>

                                                    <?php echo (count($response) >= 1 ? $response->post_date : __('Not submitted yet', 'cp')); ?>
                                                </td>

                                                <td class="<?php echo $style; ?>">
                                                    <?php
                                                    if (count($response) >= 1) {
                                                        /*if ($module->response_type == 'file') {
                                                            ?>
                                                            <a href="<?php echo $response->guid; ?>"><?php echo ucfirst($module->response_type).' ('.strtoupper(pathinfo($response->guid, PATHINFO_EXTENSION)).')'; ?></a>
                                                            <?php
                                                        } else {
                                                            echo ucfirst($module->response_type);
                                                        }*/
                                                        
                                                        ?>
                                                    <a href=""><?php _e('View', 'cp');?></a>

                                                    <?php
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>

                                                <td class="<?php echo $style; ?>">
                        <?php echo 'grade'; ?>
                                                </td>

                                                <td class="<?php echo $style; ?>">
                        <?php echo 'comment'; ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        $current_row++;
                                    }
                                    ?>


                                    <?php
                                    /*
                                      $style = '';

                                      foreach ($student_search->get_results() as $user) {
                                      $style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
                                      $user_object = new Student($user->ID);

                                      $module = new Unit_Module();
                                      $modules = $module->get_modules($current_unit->ID);
                                      ?>
                                      <tr id='user-<?php echo $user_object->ID; ?>' <?php echo $style; ?>>
                                      <td <?php echo $style; ?>>
                                      <span class="uppercase block"><?php echo $user_object->last_name; ?></span>
                                      <?php echo $user_object->first_name; ?>
                                      </td>

                                      <td <?php echo $style; ?>>
                                      <?php
                                      foreach ($modules as $mod) {

                                      $class_name = $mod->module_type;
                                      $module = new $class_name();
                                      if ($module->front_save) {
                                      ?>
                                      <?php echo $module->label; ?>
                                      <?php
                                      }
                                      }
                                      ?>
                                      </td>
                                      <td <?php echo $style; ?>>
                                      <?php
                                      foreach ($modules as $mod) {

                                      $class_name = $mod->module_type;
                                      $module = new $class_name();
                                      if ($module->front_save) {
                                      ?>
                                      <div class="assessment-column"><?php echo $mod->post_title; ?></div>
                                      <?php
                                      }
                                      }
                                      ?>
                                      </td>

                                      <td <?php echo $style; ?>>
                                      <?php
                                      foreach ($modules as $mod) {
                                      $already_respond_posts_args = array(
                                      'posts_per_page' => 1,
                                      'meta_key' => 'user_ID',
                                      'meta_value' => $user->ID,
                                      'post_type' => 'module_reponse',
                                      'post_parent' => $mod->ID,
                                      'post_status' => 'publish'
                                      );

                                      $already_respond_posts = get_posts($already_respond_posts_args);
                                      $response = $already_respond_posts[0];

                                      $class_name = $mod->module_type;
                                      $module = new $class_name();
                                      if ($module->front_save) {
                                      ?>
                                      <div class="assessment-column"><?php echo (count($response) >= 1 ? $response->post_date : __('Not submitted yet', 'cp')); ?></div>
                                      <?php
                                      }
                                      }
                                      ?>
                                      </td>
                                      <td <?php echo $style; ?>>-</td>
                                      <td <?php echo $style; ?>>-</td>
                                      <td <?php echo $style; ?>>-</td>

                                      </tr> */
                                }
                                ?>
                            </table>
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