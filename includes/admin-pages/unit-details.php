<?php
global $page, $user_id, $coursepress_admin_notice;

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

            $new_post_id = $unit->update_unit();

            if ($new_post_id != 0) {
                if (isset($_GET['ms'])) {
                    wp_redirect('?page=' . $page . '&tab=units&course_id=' . $course_id . '&action=edit&unit_id=' . $new_post_id . '&ms=' . $_GET['ms']);
                } else {
                    wp_redirect('?page=' . $page . '&tab=units&course_id=' . $course_id . '&action=edit&unit_id=' . $new_post_id);
                }
            } else {
                //an error occured
            }
        
    /*}else{
        die(__('You don\'t have right permissions for the requested action', 'cp'));
    }*/
}
}
?>

<div class='wrap nocoursesub'>
    <form action="?page=<?php echo esc_attr($page); ?>&tab=units&course_id=<?php echo $course_id; ?>&action=add_new_unit<?php echo ($unit_id !== 0) ? '&ms=uu' : '&ms=ua'; ?>" name="unit-add" method="post">
        <div class='course'>

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
                            <label for='unit_name'><?php _e('Unit Name', 'cp'); ?></label>
                            <input class='wide' type='text' name='unit_name' id='unit_name' value='<?php echo esc_attr(stripslashes($unit_details->post_title)); ?>' />
                            <br/><br/>
                            <label for='unit_description'><?php _e('Unit Description', 'cp'); ?></label>
                            <?php
                            $args = array("textarea_name" => "unit_description", "textarea_rows" => 10);

                            if (!isset($unit_details->post_content)) {
                                $unit_details = new StdClass;
                                $unit_details->post_content = '';
                            }

                            $desc = '';
                            wp_editor(stripslashes($unit_details->post_content), "unit_description", $args);
                            ?>
                            <br/>

                        </div>

                        <div class="buttons">
                            <?php if (($unit_id == 0 && current_user_can('coursepress_create_course_unit_cap')) || ($unit_id != 0 && current_user_can('coursepress_update_course_unit_cap')) || ($unit_id != 0 && current_user_can('coursepress_update_my_course_unit_cap') && $unit_details->post_author == get_current_user_id())) {//do not show anything
                                ?>
                                <input type="submit" value="<?php ($unit_id == 0 ? _e('Create', 'cp') : _e('Update', 'cp')); ?>" class="button-primary" />
                            <?php } ?>
                        </div>

                    </div>
                </div>

            </div>
        </div> <!-- course-liquid-left -->
    </form>

</div> <!-- wrap -->