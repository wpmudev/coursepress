<?php
global $page, $user_id, $coursepress_admin_notice;
global $coursepress_modules, $coursepress_modules_labels, $coursepress_modules_descriptions, $coursepress_modules_ordered;

$course_id = '';

if (isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {
    $course_id = (int) $_GET['course_id'];
    $course = new Course($course_id);
}

if (!current_user_can('coursepress_view_all_units_cap') && $course->details->post_author != get_current_user_id()) {
    die(__('You do not have required persmissions to access this page.', 'cp'));
}

if (!isset($_POST['force_current_unit_completion'])) {
    $_POST['force_current_unit_completion'] = 'off';
}

if (isset($_GET['unit_id'])) {
    $unit = new Unit($_GET['unit_id']);
    $unit_details = $unit->get_unit();
    $unit_id = (int) $_GET['unit_id'];
    $force_current_unit_completion = $unit->details->force_current_unit_completion;
} else {
    $unit = new Unit();
    $unit_id = 0;
    $force_current_unit_completion = 'off';
}

if (isset($_POST['action']) && ($_POST['action'] == 'add_unit' || $_POST['action'] == 'update_unit')) {

    if (wp_verify_nonce($_REQUEST['_wpnonce'], 'unit_details_overview_' . $user_id)) {

        //if (($_POST['action'] == 'add_unit' && current_user_can('coursepress_create_course_unit_cap')) || ($_POST['action'] == 'update_unit' && current_user_can('coursepress_update_course_unit_cap')) || ($unit_id != 0 && current_user_can('coursepress_update_my_course_unit_cap') && $unit_details->post_author == get_current_user_id())) {

        $new_post_id = $unit->update_unit(isset($_POST['unit_id']) ? $_POST['unit_id'] : 0);

        if (isset($_POST['submit-unit-publish'])) {
            /* Save & Publish */
            $unit = new Unit($new_post_id);
            $unit->change_status('publish');
        }

        if (isset($_POST['submit-unit-unpublish'])) {
            /* Save & Unpublish */
            $unit = new Unit($new_post_id);
            $unit->change_status('private');
        }


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
        <ul id="sortable-units" class="mp-tabs" style="">
            <?php
            $units = $course->get_units();

            $list_order = 1;

            foreach ($units as $unit) {

                $unit_object = new Unit($unit->ID);
                $unit_object = $unit_object->get_unit();
                ?>
                <li class="mp-tab <?php echo (isset($_GET['unit_id']) && $unit->ID == $_GET['unit_id'] ? 'active' : ''); ?>">
                    <a class="mp-tab-link" href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&unit_id=<?php echo $unit_object->ID; ?>&action=edit"><?php echo $unit_object->post_title; ?></a>
                    <i class="fa fa-arrows-v cp-move-icon"></i>

                    <input type="hidden" class="unit_order" value="<?php echo $list_order; ?>" name="unit_order_<?php echo $unit_object->ID; ?>" />
                    <input type="hidden" name="unit_id" class="unit_id" value="<?php echo $unit_object->ID; ?>" />                                                                                         
                </li>
                <?php
                $list_order++;
            }
            ?>
            <?php if (current_user_can('coursepress_create_course_unit_cap')) { ?>
                <li class="mp-tab <?php echo (!isset($_GET['unit_id']) ? 'active' : ''); ?> static">
                    <a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&action=add_new_unit" class="<?php echo (!isset($_GET['unit_id']) ? 'mp-tab-link' : 'button-secondary'); ?>"><?php _e('Add new Unit', 'cp'); ?></a>                                                                    
                </li>
            <?php } ?>
        </ul>

        <?php if (current_user_can('coursepress_create_course_unit_cap')) { ?>
            <!--<div class="mp-tabs">
                <div class="mp-tab <?php echo (!isset($_GET['unit_id']) ? 'active' : ''); ?>">
                    <a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&action=add_new_unit" class="<?php echo (!isset($_GET['unit_id']) ? 'mp-tab-link' : 'button-secondary'); ?>"><?php _e('Add new Unit', 'cp'); ?></a>
                </div>
            </div>-->
        <?php } ?>

    </div>

    <div class='mp-settings'><!--course-liquid-left-->
        <form action="?page=<?php echo esc_attr($page); ?>&tab=units&course_id=<?php echo $course_id; ?>&action=add_new_unit<?php echo ($unit_id !== 0) ? '&ms=uu' : '&ms=ua'; ?>" name="unit-add" id="unit-add" class="unit-add" method="post">
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
                        <h3><?php _e('Unit Details', 'cp'); ?>
                            <?php if ($unit_id != 0) { ?>
                                <span class="delete_unit">
                                    <a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&unit_id=<?php echo $unit_id; ?>&action=delete_unit" onclick="return removeUnit();">
                                        <i class="fa fa-times-circle cp-move-icon remove-btn"></i>
                                    </a>
                                </span>
                            <?php } ?>
                        </h3>
                    </div>

                    <div class='course-holder'>
                        <div class='course-details'>
                            <label for='unit_name'><?php _e('Unit Title', 'cp'); ?></label>
                            <input class='wide' type='text' name='unit_name' id='unit_name' value='<?php echo esc_attr(stripslashes(isset($unit_details->post_title) ? $unit_details->post_title : '')); ?>' />

                            <div class="wide">
                                <label for='unit_availability'><?php _e('Unit Availability', 'cp'); ?></label>
                                <input type="text" class="dateinput" name="unit_availability" value="<?php echo esc_attr(stripslashes(isset($unit_details->unit_availability) ? $unit_details->unit_availability : (date('Y-m-d', current_time('timestamp', 0))))); ?>" />
                                <div class="force_unit_completion">
                                    <input type="checkbox" name="force_current_unit_completion" id="force_current_unit_completion" value="on" <?php echo ($force_current_unit_completion == 'on') ? 'checked' : ''; ?> /> <?php _e('User needs to complete current unit in order to access the next one', 'cp'); ?>
                                </div>
                            </div>

                            <?php
                            $unit = new Unit($unit_id);
                            $unit_object = $unit->get_unit();
                            ?>

                            <div class="unit-control-buttons">

                                <?php
                                if (($unit_id == 0 && current_user_can('coursepress_create_course_unit_cap'))) {//do not show anything
                                    ?>
                                    <input type="submit" name="submit-unit" class="button button-units save-unit-button" value="<?php _e('Save Draft', 'cp'); ?>">
                                    <input type="submit" name="submit-unit-publish" class="button button-units button-publish" value="<?php _e('Publish', 'cp'); ?>">

                                <?php } ?>

                                <?php
                                if (($unit_id != 0 && current_user_can('coursepress_update_course_unit_cap')) || ($unit_id != 0 && current_user_can('coursepress_update_my_course_unit_cap') && $unit_object->post_author == get_current_user_id())) {//do not show anything
                                    ?>
                                    <input type="submit" name="submit-unit" class="button button-units save-unit-button" value="<?php echo ($unit_object->post_status == 'unpublished') ? 'Save Draft' : 'Save'; ?>">
                                <?php } ?>

                                <?php
                                if (($unit_id != 0 && current_user_can('coursepress_update_course_unit_cap')) || ($unit_id != 0 && current_user_can('coursepress_update_my_course_unit_cap') && $unit_object->post_author == get_current_user_id())) {//do not show anything
                                    ?>
                                    <a class="button button-preview" href="<?php echo get_permalink($unit_id); ?>" target="_new">Preview</a>

                                    <?php if (current_user_can('coursepress_change_course_unit_status_cap') || (current_user_can('coursepress_change_my_course_unit_status_cap') && $unit_object->post_author == get_current_user_id())) { ?>
                                        <input type="submit" name="submit-unit-<?php echo ($unit_object->post_status == 'unpublished') ? 'publish' : 'unpublish'; ?>" class="button button-units button-<?php echo ($unit_object->post_status == 'unpublished') ? 'publish' : 'unpublish'; ?>" value="<?php echo ($unit_object->post_status == 'unpublished') ? 'Publish' : 'Unpublish'; ?>">
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

                        <!--<div class="mp-wrap mp-postbox mp-default-margin"></div>-->

                        <h3 class="unit-elements-message"><?php _e('Add Elements and Pages to this Unit bellow', 'cp'); ?></h3>

                        <div class="module-droppable levels-sortable ui-droppable" style='display: none;'>
                            <?php _e('Drag & Drop unit elements here', 'cp'); ?>
                        </div>


                        <?php
                        $module = new Unit_Module();
                        $modules = $module->get_modules(isset($_GET['unit_id']) ? $_GET['unit_id'] : '-1');

                        if (is_array($modules) && count($modules) >= 1) {
                            ?>
                            <div class="loading_elements"><?php _e('Loading Unit elements, please wait...', 'cp'); ?></div>
                        <?php } ?>


                        <div id="modules_accordion" class="modules_accordion">
                            <!--modules will appear here-->
                            <?php
                            if (isset($_GET['unit_id'])) {
                                $module->get_modules_admin_forms($_GET['unit_id']);
                            }
                            ?>

                        </div>

                        <div class='course-details new-unit-element-holder'>

                            <label><?php _e('New Unit Element', 'cp'); ?></label>

                            <select name='unit-module-list' id='unit-module-list'>
                                <?php
                                $sections = array("instructors" => __('Read-only elements', 'cp'), "students" => __('Student Input Elements', 'cp'));

                                ksort($coursepress_modules_ordered);

                                foreach ($coursepress_modules_ordered as $coursepress_module) {
                                    ?>
                                    <option value='<?php echo $coursepress_module; ?>' data-module-description="<?php echo $coursepress_modules_descriptions[$coursepress_module]; ?>"><?php echo $coursepress_modules_labels[$coursepress_module]; ?></option>
                                    <?php
                                }

                                /* foreach ($sections as $key => $section) {

                                  if (isset($coursepress_modules[$key])) {
                                  foreach ($coursepress_modules[$key] as $mmodule => $mclass) {
                                  ?>
                                  <option value='<?php echo $mmodule; ?>' data-module-description="<?php echo $coursepress_modules_descriptions[$mmodule]; ?>"><?php echo $coursepress_modules_labels[$mmodule]; ?></option>
                                  <?php
                                  }
                                  }
                                  } */
                                ?>
                            </select>

                            <input type='button' name='unit-module-add' id='unit-module-add' value='<?php _e('Add Selected Element', 'cp'); ?>' class="button-secondary" />

                            <span class="module_description" id="module_description"></span>

                        </div>

                        <div class="course-details">
                            <div class="unit-control-buttons">

                                <?php
                                if (($unit_id == 0 && current_user_can('coursepress_create_course_unit_cap'))) {//do not show anything
                                    ?>
                                    <input type="submit" name="submit-unit" class="button button-units save-unit-button" value="<?php _e('Save Draft', 'cp'); ?>">
                                    <input type="submit" name="submit-unit-publish" class="button button-units button-publish" value="<?php _e('Publish', 'cp'); ?>">

                                <?php } ?>

                                <?php
                                if (($unit_id != 0 && current_user_can('coursepress_update_course_unit_cap')) || ($unit_id != 0 && current_user_can('coursepress_update_my_course_unit_cap') && $unit_object->post_author == get_current_user_id())) {//do not show anything
                                    ?>
                                    <input type="submit" name="submit-unit" class="button button-units save-unit-button" value="<?php echo ($unit_object->post_status == 'unpublished') ? 'Save Draft' : 'Save'; ?>">
                                <?php } ?>

                                <?php
                                if (($unit_id != 0 && current_user_can('coursepress_update_course_unit_cap')) || ($unit_id != 0 && current_user_can('coursepress_update_my_course_unit_cap') && $unit_object->post_author == get_current_user_id())) {//do not show anything
                                    ?>
                                    <a class="button button-preview" href="<?php echo get_permalink($unit_id); ?>" target="_new">Preview</a>

                                    <?php if (current_user_can('coursepress_change_course_unit_status_cap') || (current_user_can('coursepress_change_my_course_unit_status_cap') && $unit_object->post_author == get_current_user_id())) { ?>
                                        <input type="submit" name="submit-unit-<?php echo ($unit_object->post_status == 'unpublished') ? 'publish' : 'unpublish'; ?>" class="button button-units button-<?php echo ($unit_object->post_status == 'unpublished') ? 'publish' : 'unpublish'; ?>" value="<?php echo ($unit_object->post_status == 'unpublished') ? 'Publish' : 'Unpublish'; ?>">
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
            $sections = array("instructors" => __('Read-only elements', 'cp'), "students" => __('Student Input Elements', 'cp'));

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