<?php
global $page, $user_id, $coursepress_admin_notice, $coursepress;

if (isset($_GET['course_id'])) {
    $course = new Course((int)$_GET['course_id']);
    $course_details = $course->get_course();
    $course_id = (int)$_GET['course_id'];
} else {
    $course = new course();
    $course_id = 0;
}

if (isset($_POST['action']) && ($_POST['action'] == 'add' || $_POST['action'] == 'update')) {

    check_admin_referer('course_details_overview');

    /* if ($_POST['meta_course_category'] != -1) {
      $term = get_term_by('id', $_POST['meta_course_category'], 'course_category');
      wp_set_object_terms($course_id, $term->slug, 'course_category', false);
      } */

    if (!isset($_POST['meta_open_ended_course'])) {
        $_POST['meta_open_ended_course'] = 'off';
    }

    if (!isset($_POST['meta_allow_course_discussion'])) {
        $_POST['meta_allow_course_discussion'] = 'off';
    }

    if (!isset($_POST['meta_allow_course_grades_page'])) {
        $_POST['meta_allow_course_grades_page'] = 'off';
    }

    if (!isset($_POST['meta_allow_workbook_page'])) {
        $_POST['meta_allow_workbook_page'] = 'off';
    }

    if (isset($_POST['submit-unit'])) {
        /* Save / Save Draft */
        $new_post_id = $course->update_course();
    }

    if (isset($_POST['submit-unit-publish'])) {
        /* Save & Publish */
        $new_post_id = $course->update_course();
        $course = new Course($new_post_id);
        $course->change_status('publish');
    }

    if (isset($_POST['submit-unit-unpublish'])) {
        /* Save & Unpublish */
        $new_post_id = $course->update_course();
        $course = new Course($new_post_id);
        $course->change_status('private');
    }


    if ($new_post_id != 0) {
        ob_start();
        if (isset($_GET['ms'])) {
            wp_redirect(admin_url('admin.php?page=' . $page . '&course_id=' . $new_post_id . '&ms=' . $_GET['ms']));
            exit;
        } else {
            wp_redirect(admin_url('admin.php?page=' . $page . '&course_id=' . $new_post_id));
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
    $allow_workbook_page = $course->details->allow_workbook_page;
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
    $allow_workbook_page = 'off';
    $course_category = 0;
    $language = __('English', 'cp');
    $course_video_url = '';
}
?>

<div class='wrap nocoursesub'>
    <form action='<?php esc_attr_e(admin_url('admin.php?page='.$page.(($course_id !== 0) ? '&course_id=' . $course_id : '') . ($course_id !== 0) ? '&ms=cu' : '&ms=ca'));?>' name='course-add' method='post'>

        <div class='course-liquid-left'>

            <div id='course'>

                <?php wp_nonce_field('course_details_overview'); ?>

                <?php if (isset($course_id)) { ?>
                    <input type="hidden" name="course_id" value="<?php echo esc_attr($course_id); ?>" />
                    <input type="hidden" name="action" value="update" />
                <?php } else { ?>
                    <input type="hidden" name="action" value="add" />
                <?php } ?>

                <div id='edit-sub' class='course-holder-wrap mp-wrap'>

                    <div class='sidebar-name no-movecursor'>
                        <h3><?php _e('Course Setup', 'cp'); ?></h3>
                    </div>

                    <div class='course-holder'>
						
						<!-- COURSE BUTTONS -->
                        <div class="unit-control-buttons course-control-buttons">

                            <?php
                            if (($course_id == 0 && current_user_can('coursepress_create_course_cap'))) {//do not show anything
                                ?>
                                <input type="submit" name="submit-unit" class="button button-units save-unit-button" value="<?php _e('Save Draft', 'cp'); ?>">
                                <input type="submit" name="submit-unit-publish" class="button button-units button-publish" value="<?php _e('Publish', 'cp'); ?>">

                            <?php } ?>

                            <?php
                            if (($course_id != 0 && current_user_can('coursepress_update_course_cap')) || ($course_id != 0 && current_user_can('coursepress_update_my_course_cap') && $course_details->post_author == get_current_user_id())) {//do not show anything
                                ?>
                                <input type="submit" name="submit-unit" class="button button-units save-unit-button" value="<?php echo ($course_details->post_status == 'unpublished') ? __('Save Draft', 'cp') : __('Publish', 'cp'); ?>">
                            <?php } ?>

                            <?php
                            if (($course_id != 0 && current_user_can('coursepress_update_course_cap')) || ($course_id != 0 && current_user_can('coursepress_update_my_course_cap') && $course_details->post_author == get_current_user_id())) {//do not show anything
                                ?>
                                <a class="button button-preview" href="<?php echo get_permalink($course_id); ?>" target="_new">Preview</a>

                                <?php if (current_user_can('coursepress_change_course_status_cap') || (current_user_can('coursepress_change_my_course_status_cap') && $course_details->post_author == get_current_user_id())) { ?>
                                    <input type="submit" name="submit-unit-<?php echo ($course_details->post_status == 'unpublished') ? 'publish' : 'unpublish'; ?>" class="button button-units button-<?php echo ($course_details->post_status == 'unpublished') ? 'publish' : 'unpublish'; ?>" value="<?php echo ($course_details->post_status == 'unpublished') ? __('Publish', 'cp') : __('Unpublish', 'cp'); ?>">
                                    <?php
                                }
                            }
                            ?>
                        </div>
						<!-- /COURSE BUTTONS -->						
						
						<!-- COURSE DETAILS -->
                        <div class='course-details'>
							
							
							<!-- Course Overview -->
							<div class='course-section step step-1 save-marker active'>
								<div class='course-section-title'>
									<div class="status"></div>									
									<h3><?php _e( 'Step 1 - Course Overview', 'cp' )?></h3>
								</div>
								<div class='course-form'>
									
									<div class="wide">
		                            <label for='course_name'>
		                                <?php _e('Course Name', 'cp'); ?>
		                            </label>
		                            <input class='wide' type='text' name='course_name' id='course_name' value='<?php
		                            if (isset($_GET['course_id'])) {
		                                echo esc_attr(stripslashes($course->details->post_title));
		                            }
		                            ?>' />
									</div>
									
									<div class="wide">
		                            <label for='course_excerpt'>
		                                <?php _e('Course Excerpt / Short Overview', 'cp'); ?>
										<?php CP_Helper_Tooltip::tooltip( __('Provide a few short sentences to describe the course', 'cp') ); ?>
		                            </label>
		                            <?php
		                            $args = array("textarea_name" => "course_excerpt", "textarea_rows" => 3, "media_buttons" => false, "quicktags" => false);

		                            if (!isset($course_excerpt->post_excerpt)) {
		                                $course_excerpt = new StdClass;
		                                $course_excerpt->post_excerpt = '';
		                            }

		                            $desc = '';
		                            wp_editor(htmlspecialchars_decode((isset($_GET['course_id']) ? $course_details->post_excerpt : '')), "course_excerpt", $args);
		                            ?>
									</div>
									
									<div class="narrow">
		                            <label for='featured_url'>
		                                <?php _e('Listing Image', 'cp'); ?><br />
										<span><?php _e('The image is used on the "Courses" listing (archive) page along with the course excerpt.') ?></span>
		                            </label>
	                                <div class="featured_url_holder">
	                                    <input class="featured_url" type="text" size="36" name="meta_featured_url" value="<?php
	                                    if ($course_id !== 0) {
	                                        echo esc_attr($course->details->featured_url);
	                                    }
	                                    ?>" placeholder="<?php _e('Add Image URL or Browse for Image', 'cp'); ?>" />
	                                    <input class="featured_url_button button-secondary" type="button" value="<?php _e('Browse', 'cp'); ?>" />
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
									
									<div class="narrow">
	                                <label>
										<?php _e('Course Category', 'cp'); ?>
										<a class="context-link" href="edit-tags.php?taxonomy=course_category&post_type=course"><?php _e('Manage Categories', 'cp'); ?></a>
									</label>
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
	                                									
									</div>
									
									<div class="narrow">
	                                <label for='meta_course_language'><?php _e('Course Language', 'cp'); ?></label>
	                                <input type="text" name="meta_course_language" value="<?php echo esc_attr(stripslashes($language)); ?>" />
									</div>
																		
									
									
									<div class="course-step-buttons">
										<input type="button" class="button button-units next" value="<?php _e( 'Next', 'cp' ); ?>" />
									</div>
								</div>
							</div>
							<!-- /Course Overview -->

							<!-- Course Description -->
							<div class='course-section step step-2'>
								<div class='course-section-title'>
									<div class="status saved"></div>
									<h3><?php _e( 'Step 2 - Course Description', 'cp' )?></h3>									
								</div>
								<div class='course-form'>
									This is more stuff...
									<div class="course-step-buttons">
										<input type="button" class="button button-units prev" value="<?php _e( 'Previous', 'cp' ); ?>" />
										<input type="button" class="button button-units next" value="<?php _e( 'Next', 'cp' ); ?>" />
									</div>
								</div>
							</div>
							<!-- /Course Description -->							

							<!-- Instructors -->
							<div class='course-section step step-3'>
								<div class='course-section-title'>
									<div class="status invalid"></div>									
									<h3><?php _e( 'Step 3 - Instructors', 'cp' )?></h3>									
								</div>
								<div class='course-form'>
									This is just stuff...
									
									<div class="course-step-buttons">
										<input type="button" class="button button-units prev" value="<?php _e( 'Previous', 'cp' ); ?>" />
										<input type="button" class="button button-units next" value="<?php _e( 'Next', 'cp' ); ?>" />
									</div>
								</div>
							</div>
							<!-- /Instructors -->
							
							<!-- Course Dates -->							
							<div class='course-section step step-4'>
								<div class='course-section-title'>
									<div class="status progress"></div>									
									<h3><?php _e( 'Step 4 - Course Dates', 'cp' )?></h3>									
								</div>
								<div class='course-form'>
									This is more stuff...
									<div class="course-step-buttons">
										<input type="button" class="button button-units prev" value="<?php _e( 'Previous', 'cp' ); ?>" />
										<input type="button" class="button button-units next" value="<?php _e( 'Next', 'cp' ); ?>" />
									</div>
								</div>
							</div>
							<!-- /Course Dates -->

							<!-- Classes, Discussions & Workbook -->
							<div class='course-section step step-5'>
								<div class='course-section-title'>
									<div class="status attention"></div>									
									<h3><?php _e( 'Step 5 - Classes, Discussion & Workbook', 'cp' )?></h3>						
								</div>
								<div class='course-form'>
									This is just stuff...
									
									<div class="course-step-buttons">
										<input type="button" class="button button-units prev" value="<?php _e( 'Previous', 'cp' ); ?>" />
										<input type="button" class="button button-units next" value="<?php _e( 'Next', 'cp' ); ?>" />
									</div>
								</div>
							</div>
							<!-- /Classes, Discussions & Workbook -->							

							<!-- Enrollment & Course Cost -->
							<div class='course-section step step-6'>
								<div class='course-section-title'>
									<div class="status"></div>									
									<h3><?php _e( 'Step 6 - Enrollment & Course Cost', 'cp' )?></h3>						
								</div>
								<div class='course-form'>
									This is more stuff...
									<div class="course-step-buttons">
										<input type="button" class="button button-units prev" value="<?php _e( 'Previous', 'cp' ); ?>" />
										<input type="button" class="button button-units done" value="<?php _e( 'Done', 'cp' ); ?>" />
									</div>
								</div>
							</div>							
							<!-- /Enrollment & Course Cost -->
							

                            <br/><br/>
                            <label for='course_name'>
                                <?php _e('Course Description', 'cp'); ?>
                                <a class="help-icon" href="javascript:;"></a>
                                <div class="tooltip">
                                    <div class="tooltip-before"></div>
                                    <div class="tooltip-button">&times;</div>
                                    <div class="tooltip-content">
                                        <?php _e('Provide a detailed description of the course', 'cp'); ?>
                                    </div>
                                </div>

                            </label>
                            <?php
                            $args = array("textarea_name" => "course_description", "textarea_rows" => 10);

                            if (!isset($course_details->post_content)) {
                                $course_details = new StdClass;
                                $course_details->post_content = '';
                            }

                            $desc = '';
                            wp_editor(htmlspecialchars_decode($course_details->post_content), "course_description", $args);
                            ?>
                            <br />

                            <div class="half">
                                <div class="course-holder-wrap">

                                    <h3><?php _e('Course Instructor(s)', 'cp'); ?></h3>

                                    <div>

                                        <?php if ((current_user_can('coursepress_assign_and_assign_instructor_course_cap')) || (current_user_can('coursepress_assign_and_assign_instructor_my_course_cap') && $course->details->post_author == get_current_user_id()) || (current_user_can('coursepress_assign_and_assign_instructor_my_course_cap') && !isset($_GET['course_id']))) { ?>
                                            <?php coursepress_instructors_avatars_array(); ?>

                                            <div class="clearfix"></div>
                                            <p><?php _e('Select one or more instructors to facilitate this course', 'cp'); ?></p>
                                            <?php coursepress_instructors_drop_down(); ?><input class="button-primary" id="add-instructor-trigger" type="button" value="<?php _e('Assign', 'cp'); ?>">
                                            <p><?php _e('NOTE: If you need to add an instructor that is not on the list, please finish creating your course and save it. To create a new instructor, you must go to Users to create a new user account which you can select in this list. Then come back to this course and you can then select the instructor.', 'cp'); ?></p>


                                            <?php
                                        } else {
                                            if (coursepress_get_number_of_instructors() == 0 || coursepress_instructors_avatars($course_id, false, true) == 0) {//just to fill in emtpy space if none of the instructors has been assigned to the course and in the same time instructor can't assign instructors to a course
                                                _e('You do not have required permissions to assign instructors to a course.', 'cp');
                                            }
                                        }
                                        ?>

                                    </div>
                                </div> <!-- course-holder-wrap -->
                            </div>

                            <div class="half">
                                <h3><?php _e('Assigned Instructor(s)', 'cp'); ?></h3>

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
                            </div>

                            <br clear="all" />
                            <div class="full border-devider"></div>

                            <div class="half">
                                <h3><?php _e('Listing Image', 'cp'); ?></h3>
                                <p><?php _e('The image is used on the "Courses" listing (archive) page along with the course excerpt.') ?></p>
                                <div class="featured_url_holder">
                                    <input class="featured_url" type="text" size="36" name="meta_featured_url" value="<?php
                                    if ($course_id !== 0) {
                                        echo esc_attr($course->details->featured_url);
                                    }
                                    ?>" placeholder="<?php _e('Add Image URL of Browse for Image', 'cp'); ?>" />
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

                            <div class="half">
                                <?php
                                global $content_width;

                                wp_enqueue_style('thickbox');
                                wp_enqueue_script('thickbox');
                                wp_enqueue_media();
                                wp_enqueue_script('media-upload');

                                $supported_video_extensions = implode(", ", wp_get_video_extensions());

                                if (!empty($data)) {
                                    if (!isset($data->player_width) or empty($data->player_width)) {
                                        $data->player_width = empty($content_width) ? 640 : $content_width;
                                    }
                                }
                                ?>

                                <div class="video_url_holder mp-wrap">
                                    <h3><?php _e('Featured Video', 'cp'); ?></h3>
                                    <p><?php _e('This is used on the Course Overview page and will be displayed with the course description.', 'cp'); ?></p>

                                    <input class="course_video_url" type="text" size="36" name="meta_course_video_url" value="<?php echo esc_attr($course_video_url); ?>" placeholder="<?php
                                    _e('Add URL or Browse', 'cp');
                                    echo ' (' . $supported_video_extensions . ')';
                                    ?>" />

                                    <input type="button" class="course_video_url_button button-secondary" value="<?php _e('Browse', 'cp'); ?>" />

                                </div>
                            </div>

                            <br clear="all" />
                            <div class="full border-devider"></div>

                            <div class="half">
                                <label for='meta_class-size'><?php _e('Class size', 'cp'); ?>
                                    <a class="help-icon" href="javascript:;"></a>
                                    <div class="tooltip">
                                        <div class="tooltip-before"></div>
                                        <div class="tooltip-button">&times;</div>
                                        <div class="tooltip-content">
                                            <?php _e('Select 0 for infinite', 'cp'); ?>
                                        </div>
                                    </div>

                                </label>
                                <input class='spinners' name='meta_class_size' id='class_size' value='<?php echo esc_attr(stripslashes((is_numeric($class_size) ? $class_size : 0))); ?>' />
                            </div>

                            <div class="half">
                                <label for='meta_enroll_type'><?php _e('How & Who can Enroll?', 'cp'); ?></label>

                                <select class="wide" name="meta_enroll_type" id="enroll_type">
                                    <option value="anyone" <?php echo ($enroll_type == 'anyone' ? 'selected=""' : '') ?>><?php _e(' Anyone ', 'cp'); ?></option>
                                    <option value="passcode" <?php echo ($enroll_type == 'passcode' ? 'selected=""' : '') ?>><?php _e('Anyone with a pass code', 'cp'); ?></option>
                                    <option value="prerequisite" <?php echo ($enroll_type == 'prerequisite' ? 'selected=""' : '') ?>><?php _e('Anyone who completed the prerequisite course', 'cp'); ?></option>
                                    <option value="manually" <?php echo ($enroll_type == 'manually' ? 'selected=""' : '') ?>><?php _e('Manually added only', 'cp'); ?></option>
                                </select>

                            </div>

                            <div class="half">
                                <label for='meta_course_language'><?php _e('Course Language', 'cp'); ?></label>
                                <input type="text" name="meta_course_language" value="<?php echo esc_attr(stripslashes($language)); ?>" />
                            </div>

                            <div class='half' id='manually_added_holder'>
                                <p><?php _e('NOTE: If you need to manually add a student, students must be registered on your site first. To do this for a student, you can do this yourself by going to Users in WordPress where you can add the students manually. You can then select them from this list.', 'cp'); ?></p>
                            </div>
                            <div class="half" id="enroll_type_prerequisite_holder" <?php echo ($enroll_type <> 'prerequisite' ? 'style="display:none"' : '') ?>>
                                <label for='meta_enroll_type'><?php _e('Prerequisite Course', 'cp'); ?>
                                    <a class="help-icon" href="javascript:;"></a>
                                    <div class="tooltip">
                                        <div class="tooltip-before"></div>
                                        <div class="tooltip-button">&times;</div>
                                        <div class="tooltip-content">
                                            <?php _e('Students will need to fulfil prerequisite in order to enroll', 'cp'); ?>
                                        </div>
                                    </div>

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
                                    <a class="help-icon" href="javascript:;"></a>
                                    <div class="tooltip">
                                        <div class="tooltip-before"></div>
                                        <div class="tooltip-button">&times;</div>
                                        <div class="tooltip-content">
                                            <?php _e('Students will need to enter the pass code in order to enroll', 'cp'); ?>
                                        </div>
                                    </div>

                                </label>
                                <input type="text" name="meta_passcode" value="<?php echo esc_attr(stripslashes($passcode)); ?>" />

                            </div>

                            <!--<div class="half">
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

                            </div>-->
                            <br clear="all" />

                            <div class="full border-devider">
                                <div class="half">
                                    <h3><?php _e('Cost to enroll in the course', 'cp'); ?></h3>
                                    
                                    <?php
                                    
                                    if ($coursepress->is_marketpress_active()) {
                                        ?>

                                        <?php _e('MarketPress product'); ?>

                                        <a class="help-icon" href="javascript:;"></a>
                                        <div class="tooltip">
                                            <div class="tooltip-before"></div>
                                            <div class="tooltip-button">&times;</div>
                                            <div class="tooltip-content">
                                                <?php _e('For students to pay for this course, you can set up a product in MarketPress and sell the course. Select this course when creating/editing a product.'); ?>
                                            </div>
                                        </div>
                                        
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
                                        
                                        <p><?php _e('NOTE: If you wish to sell a course and have not set up a product in MarketPress, please finish creating your course and save it. Once you have saved your course, you can create a product in MarketPress to sell this course, then come back to this "Course Overview" page and select the product you have created. Click <a href="post-new.php?post_type=product" target="_blank">here</a> to open a new window that takes you to the "MarketPress Product page"', 'cp');?></p>

                                    <?php } else { ?>
                                        <p><?php printf(__('%s integrates with the <a href="https://premium.wpmudev.org/project/e-commerce/?ref=wordpress.org">MarketPress</a> plugin. Install it it to sell this course online.', 'cp'), $coursepress->name); ?></p>
                                    <?php } ?>

                                </div>
                            </div>

                            <br clear="all" />

                            <div class="full border-devider">
                                <label><?php _e('Course can be done at any time', 'cp'); ?>
                                    <a class="help-icon" href="javascript:;"></a>
                                    <div class="tooltip">
                                        <div class="tooltip-before"></div>
                                        <div class="tooltip-button">&times;</div>
                                        <div class="tooltip-content">
                                            <?php _e('The first or last course or enrollment date having no upper or lower limit.', 'cp') ?>
                                        </div>
                                    </div>

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
                                    <a class="help-icon" href="javascript:;"></a>
                                    <div class="tooltip">
                                        <div class="tooltip-before"></div>
                                        <div class="tooltip-button">&times;</div>
                                        <div class="tooltip-content">
                                            <?php _e('Student may enroll only during selected date range', 'cp'); ?>
                                        </div>
                                    </div>

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

                                    <a class="help-icon" href="javascript:;"></a>
                                    <div class="tooltip">
                                        <div class="tooltip-before"></div>
                                        <div class="tooltip-button">&times;</div>
                                        <div class="tooltip-content">
                                            <?php _e('If checked, students can post questions and get answers.', 'cp') ?>
                                        </div>
                                    </div>

                                    <input type="checkbox" name="meta_allow_course_discussion" id="allow_course_discussion" <?php echo ($allow_course_discussion == 'on') ? 'checked' : ''; ?> />
                                </label>

                            </div>

                            <!--<div class="full border-devider">
                                <label><?php _e('Show Grades Page for Students', 'cp'); ?>
                                    <a class="help-icon" href="javascript:;"></a>
                                    <div class="tooltip">
                                        <div class="tooltip-before"></div>
                                        <div class="tooltip-button">&times;</div>
                                        <div class="tooltip-content">
                            <?php _e('If checked, students can see their course performance and grades by units.', 'cp') ?>
                                        </div>
                                    </div>

                                    <input type="checkbox" name="meta_allow_course_grades_page" id="allow_course_grades_page" <?php echo ($allow_course_grades_page == 'on') ? 'checked' : ''; ?> />
                                </label>
                            </div>-->

                            <div class="full border-devider">
                                <label><?php _e('Add Workbook Page for Students', 'cp'); ?>
                                    <a class="help-icon" href="javascript:;"></a>
                                    <div class="tooltip">
                                        <div class="tooltip-before"></div>
                                        <div class="tooltip-button">&times;</div>
                                        <div class="tooltip-content">
                                            <?php _e('This is a page where students can see their progress and grades.', 'cp') ?>
                                        </div>
                                    </div>

                                    <input type="checkbox" name="meta_allow_workbook_page" id="allow_workbook_page" <?php echo ($allow_workbook_page == 'on') ? 'checked' : ''; ?> />
                                </label>

                            </div>

                            <br clear="all" />
                        </div>

						<!-- /COURSE DETAILS -->

                        <div class="buttons course-add-units-button">
                            <?php
                            if ($course_id !== 0) {
                                ?>
                                <a href="<?php echo admin_url('admin.php?page='.(int)$_GET['page'].'&tab=units&course_id='.(int)$_GET['course_id']);?>" class="button-secondary"><?php _e('Add Units &raquo;', 'cp'); ?></a> 
                            <?php } ?>
                        </div>

                    </div>
                </div>

            </div>
        </div> <!-- course-liquid-left -->

    </form>

</div> <!-- wrap -->