<?php
global $page, $user_id, $coursepress_admin_notice;
global $coursepress_modules, $coursepress_modules_labels;

$course_id = '';

if (isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {
    $course_id = (int) $_GET['course_id'];
    $course = new Course($course_id);
}

if (!current_user_can('coursepress_view_all_units_cap') && $course->details->post_author != get_current_user_id()) {
    die(__('You do not have required persmissions to access this page.', 'cp'));
}

if (isset($_GET['unit_id'])) {
    $unit = new Unit($_GET['unit_id']);
    $unit_details = $unit->get_unit();
    $unit_id = (int) $_GET['unit_id'];
} else {
    $unit = new Unit();
    $unit_id = 0;
}

if (isset($_POST['action']) && ($_POST['action'] == 'add_unit' || $_POST['action'] == 'update_unit')) {

    if (wp_verify_nonce($_REQUEST['_wpnonce'], 'unit_details_overview_' . $user_id)) {

        //if (($_POST['action'] == 'add_unit' && current_user_can('coursepress_create_course_unit_cap')) || ($_POST['action'] == 'update_unit' && current_user_can('coursepress_update_course_unit_cap')) || ($unit_id != 0 && current_user_can('coursepress_update_my_course_unit_cap') && $unit_details->post_author == get_current_user_id())) {

        $new_post_id = $unit->update_unit(isset($_POST['unit_id']) ? $_POST['unit_id'] : 0);

        if ($new_post_id != 0) {
            ob_start();
            if (isset($_GET['ms'])) {
                wp_redirect('?page=' . $page . '&tab=units&course_id=' . $course_id . '&action=edit&unit_id=' . $new_post_id . '&ms=' . $_GET['ms']);
                //exit;
            } else {
                wp_redirect('?page=' . $page . '&tab=units&course_id=' . $course_id . '&action=edit&unit_id=' . $new_post_id);
                //exit;
            }
        } else {
            //an error occured
        }

        /* }else{
          die(__('You don\'t have right permissions for the requested action', 'cp'));
          } */
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['new_status']) && isset($_GET['unit_id']) && is_numeric($_GET['unit_id'])) {
    $unit = new Unit($_GET['unit_id']);
    $unit_object = $unit->get_unit();
    if ((current_user_can('coursepress_change_course_unit_status_cap')) || (current_user_can('coursepress_change_my_course_unit_status_cap') && $unit_object->post_author == get_current_user_id())) {
        $unit->change_status($_GET['new_status']);
    }
}
?>

<div class='wrap mp-wrap nocoursesub'>

    <div id="undefined-sticky-wrapper" class="sticky-wrapper">
        <ul class="mp-tabs" style="">
            <?php
            $units = $course->get_units();

            $list_order = 1;

            foreach ($units as $unit) {

                $unit_object = new Unit($unit->ID);
                $unit_object = $unit_object->get_unit();
                ?>
                <li class="mp-tab <?php echo (isset($_GET['unit_id']) && $unit->ID == $_GET['unit_id'] ? 'active' : ''); ?>"><!--postbox ui-state-default clearfix-->
                    <a class="mp-tab-link" href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&unit_id=<?php echo $unit_object->ID; ?>&action=edit"><?php echo $unit_object->post_title; ?></a>

                    <?php /* if ((current_user_can('coursepress_delete_course_units_cap')) || (current_user_can('coursepress_delete_my_course_units_cap') && $unit_object->post_author == get_current_user_id())) { ?>
                      <div class="unit-remove"><a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&unit_id=<?php echo $unit_object->ID; ?>&action=delete_unit" onClick="return removeUnit();" class="remove-button"></a></div>
                      <?php } */ ?>

                                                                                                <!--<div class="unit-buttons"><a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&unit_id=<?php echo $unit_object->ID; ?>&action=edit" class="button button-settings">Settings</a>
                    <?php /* if ((current_user_can('coursepress_change_course_unit_status_cap')) || (current_user_can('coursepress_change_my_course_unit_status_cap') && $unit_object->post_author == get_current_user_id())) { ?>
                      <a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&unit_id=<?php echo $unit_object->ID; ?>&action=change_status&new_status=<?php echo ($unit_object->post_status == 'unpublished') ? 'publish' : 'private'; ?>" class="button button-<?php echo ($unit_object->post_status == 'unpublished') ? 'unpublish' : 'publish'; ?>"><?php ($unit_object->post_status == 'unpublished') ? _e('Publish', 'cp') : _e('Unpublish', 'cp'); ?></a>
                      <?php } */ ?>
                                                                                                </div>-->
                </li>
                <?php
                $list_order++;
            }
            ?>
            <?php if (current_user_can('coursepress_create_course_unit_cap')) { ?>
                <li class="<?php echo (!isset($_GET['unit_id']) ? 'mp-tab active' : ''); ?>">
                    <a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&action=add_new_unit" class="<?php echo (!isset($_GET['unit_id']) ? 'mp-tab-link' : 'button-secondary'); ?>"><?php _e('Add new Unit', 'cp'); ?></a>
                </li>
            <?php } ?>
        </ul>

    </div>

    <div class='mp-settings'><!--course-liquid-left-->
        <form action="?page=<?php echo esc_attr($page); ?>&tab=units&course_id=<?php echo $course_id; ?>&action=add_new_unit<?php echo ($unit_id !== 0) ? '&ms=uu' : '&ms=ua'; ?>" name="unit-add" id="unit-add" method="post">
            <input type="hidden" name="beingdragged" id="beingdragged" value="" />
            <div id='course'>

                <?php wp_nonce_field('unit_details_overview_' . $user_id); ?>

                <?php if (isset($unit_id)) { ?>
                    <input type="hidden" name="course_id" value="<?php echo esc_attr($course_id); ?>" />
                    <input type="hidden" name="unit_id" value="<?php echo esc_attr($unit_id); ?>" />
                    <input type="hidden" name="action" value="update_unit" />
                <?php } else { ?>
                    <input type="hidden" name="action" value="add_unit" />
                <?php } ?>

                <div id='edit-sub' class='course-holder-wrap'>

                    <div class='sidebar-name no-movecursor'>
                        <h3><?php _e('Unit Details', 'cp'); ?></h3>
                    </div>

                    <div class='course-holder'>
                        <div class='course-details'>
                            <label for='unit_name'><?php _e('Unit Title', 'cp'); ?></label>
                            <input class='wide' type='text' name='unit_name' id='unit_name' value='<?php echo esc_attr(stripslashes(isset($unit_details->post_title) ? $unit_details->post_title : '')); ?>' />

                            <div class="wide">
                                <label for='unit_availability'><?php _e('Unit Availability', 'cp'); ?></label>
                                <input type="text" class="dateinput" name="unit_availability" value="<?php echo esc_attr(trim($course_start_date) !== '' ? $course_start_date : (date( 'Y-m-d', current_time( 'timestamp', 0 ) )) ); ?>" />
                            </div>

                            <div class="unit-control-buttons">
                                <?php if (($unit_id == 0 && current_user_can('coursepress_create_course_unit_cap')) || ($unit_id != 0 && current_user_can('coursepress_update_course_unit_cap')) || ($unit_id != 0 && current_user_can('coursepress_update_my_course_unit_cap') && $unit_details->post_author == get_current_user_id())) {//do not show anything
                                    ?>
                                    <a class="button button-units save-unit-button"><?php ($unit_id == 0 ? _e('Save', 'cp') : _e('Save', 'cp')); ?></a>
                                <?php } ?>

                                <?php
                                if ($unit_id != '') {
                                    $unit = new Unit($unit_id);
                                    if ($unit->can_show_permalink()) {
                                        ?>
                                        <a class="button button-preview" href="<?php echo get_permalink($unit_id); ?>" target="_new"><?php _e('Preview', 'cp'); ?></a>
                                        <?php
                                    }
                                    ?>

                                    <?php $unit_object = $unit->get_unit(); ?>

                                    <?php if (current_user_can('coursepress_change_course_unit_status_cap') || (current_user_can('coursepress_change_my_course_unit_status_cap') && $unit_object->post_author == get_current_user_id())) { ?>
                                        <a class="button button-<?php echo ($unit_object->post_status == 'unpublished') ? 'publish' : 'unpublish'; ?>" href="?page=<?php echo $page; ?>&tab=units&course_id=<?php echo $course_id; ?>&unit_id=<?php echo $unit_object->ID; ?>&action=edit&new_status=<?php echo ($unit_object->post_status == 'unpublished') ? 'publish' : 'private'; ?>"><?php ($unit_object->post_status == 'unpublished') ? _e('Publish', 'cp') : _e('Unpublish', 'cp'); ?></a>
                                        <?php
                                    }
                                }
                                ?>

                            </div>


                            <label for='unit_description'><?php _e('Introduction to this Unit', 'cp'); ?></label>
                            <?php
                            $args = array("textarea_name" => "unit_description", "textarea_rows" => 10);

                            if (!isset($unit_details->post_content)) {
                                $unit_details = new StdClass;
                                $unit_details->post_content = '';
                            }

                            $desc = '';
                            wp_editor(htmlspecialchars_decode($unit_details->post_content), "unit_description", $args);
                            ?>
                            <br/>

                        </div>

                        <div class="mp-wrap mp-postbox mp-default-margin"></div>

                        <div class="module-droppable levels-sortable ui-droppable" style='display: none;'>
                            <?php _e('Drag & Drop unit modules here', 'cp'); ?>
                        </div>

                        <div id="modules_accordion" class="modules_accordion">
                            <!--modules will appear here-->
                            <?php
                            if (isset($_GET['unit_id'])) {
                                $module = new Unit_Module();
                                $module->get_modules_admin_forms($_GET['unit_id']);
                            }
                            ?>

                        </div>

                        <div class='course-details'>
                            <select name='unit-module-list' id='unit-module-list'>
                                <?php
                                $sections = array("instructors" => __('Read-only modules', 'cp'), "students" => __('Student Input Modules', 'cp'));

                                foreach ($sections as $key => $section) {

                                    if (isset($coursepress_modules[$key])) {
                                        foreach ($coursepress_modules[$key] as $mmodule => $mclass) {
                                            ?>
                                            <option value='<?php echo $mmodule; ?>'><?php echo $coursepress_modules_labels[$mmodule]; ?></option>
                                            <?php
                                        }
                                    }
                                }
                                ?>
                            </select>

                            <!--<select name="unit-module-list" id="unit-module-list">
                                <option value="audio_module">Audio</option>
                                <option value="file_module">File Download</option>
                                <option value="text_module">Text</option>
                                <option value="video_module">Video</option>
                                <option value="checkbox_input_module">Check Box Input</option>
                                <option value="file_input_module">File Upload</option>
                                <option value="radio_input_module">Radio Box Input</option>
                                <option value="text_input_module">Text Input</option>
                                <option value="textarea_input_module">Text Area Input</option>
                            </select>-->

                            <input type='button' name='unit-module-add' id='unit-module-add' value='Add' />
                        </div>

                        <div class="course-details">
                            <div class="unit-control-buttons">
                                <?php if (($unit_id == 0 && current_user_can('coursepress_create_course_unit_cap')) || ($unit_id != 0 && current_user_can('coursepress_update_course_unit_cap')) || ($unit_id != 0 && current_user_can('coursepress_update_my_course_unit_cap') && $unit_details->post_author == get_current_user_id())) {//do not show anything
                                    ?>
                                    <a class="button button-units save-unit-button"><?php ($unit_id == 0 ? _e('Save', 'cp') : _e('Save', 'cp')); ?></a>
                                <?php } ?>

                                <?php
                                if ($unit_id != '') {
                                    $unit = new Unit($unit_id);
                                    if ($unit->can_show_permalink()) {
                                        ?>
                                        <a class="button button-preview" href="<?php echo get_permalink($unit_id); ?>" target="_new"><?php _e('Preview', 'cp'); ?></a>
                                        <?php
                                    }
                                    ?>

                                    <?php $unit_object = $unit->get_unit(); ?>

                                    <?php if (current_user_can('coursepress_change_course_unit_status_cap') || (current_user_can('coursepress_change_my_course_unit_status_cap') && $unit_object->post_author == get_current_user_id())) { ?>
                                        <a class="button button-<?php echo ($unit_object->post_status == 'unpublished') ? 'publish' : 'unpublish'; ?>" href="?page=<?php echo $page; ?>&tab=units&course_id=<?php echo $course_id; ?>&unit_id=<?php echo $unit_object->ID; ?>&action=edit&new_status=<?php echo ($unit_object->post_status == 'unpublished') ? 'publish' : 'private'; ?>"><?php ($unit_object->post_status == 'unpublished') ? _e('Publish', 'cp') : _e('Unpublish', 'cp'); ?></a>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>

                    </div><!--/course-holder-->
                </div><!--/course-holder-wrap-->
            </div><!--/course-->
        </form>
    </div> <!-- course-liquid-left -->

    <div class='level-liquid-right' style="display:none;">
        <div class="level-holder-wrap">
            <?php
            $sections = array("instructors" => __('Read-only modules', 'cp'), "students" => __('Student Input Modules', 'cp'));

            foreach ($sections as $key => $section) {
                ?>

                <div class="sidebar-name no-movecursor">
                    <h3><?php echo $section; ?></h3>
                </div>

                <div class="section-holder" id="sidebar-<?php echo $key; ?>" style="min-height: 98px;">
                    <ul class='modules'>
                        <?php
                        if (isset($coursepress_modules[$key])) {
                            foreach ($coursepress_modules[$key] as $mmodule => $mclass) {
                                $module = new $mclass();
                                if (!array_key_exists($mmodule, $module)) {
                                    $module->admin_sidebar(false);
                                } else {
                                    $module->admin_sidebar(true);
                                }

                                $module->admin_main(array());
                            }
                        }
                        ?>
                    </ul>
                </div>
                <?php
            }
            ?>
        </div> <!-- level-holder-wrap -->

    </div> <!-- level-liquid-right -->


    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('#modules_accordion .switch-tmce').each(function() {
                jQuery(this).trigger('click');
            });
        });
    </script>
</div> <!-- wrap -->