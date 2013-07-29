<?php
global $page, $user_id, $coursepress_admin_notice;

$course_id = '';

if (isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
}

if (isset($_GET['unit_id'])) {
    $unit = new Unit($_GET['unit_id']);
    $unit_details = $unit->get_unit();
    $unit_id = $_GET['unit_id'];
} else {
    $unit = new Unit();
    $unit_id = 0;
}

if (isset($_POST['action']) && ($_POST['action'] == 'add_unit' || $_POST['action'] == 'update_unit')) {

    if (wp_verify_nonce($_REQUEST['_wpnonce'], 'unit_details_overview_' . $user_id)) {

        $new_post_id = $unit->update_unit();

        if ($new_post_id != 0) {
            wp_redirect('?page=' . $page . '&tab=units&course_id=' . $course_id . '&action=edit&unit_id=' . $new_post_id);
        } else {
            //an error occured
        }
    }
}
?>

<div class='wrap nocoursesub'>
    <form action="?page=<?php echo esc_attr($page); ?>&tab=units&course_id=<?php echo $course_id; ?>&action=add_new_unit" name="unit-add" method="post">
        <input type="hidden" name="beingdragged" id="beingdragged" value="" />
        <div class='course-liquid-left'>

            <div id='course-left'>


                <?php wp_nonce_field('unit_details_overview_' . $user_id); ?>

                <?php if (isset($unit_id)) { ?>
                    <input type="hidden" name="course_id" value="<?php echo esc_attr($course_id); ?>" />
                    <input type="hidden" name="unit_id" value="<?php echo esc_attr($unit_id); ?>" />
                    <input type="hidden" name="action" value="update_unit" />
                    <input type="hidden" name="plugin_notice" value="<?php _e('Unit has been updated.', 'cp'); ?>" />
                <?php } else { ?>
                    <input type="hidden" name="action" value="add_unit" />
                    <input type="hidden" name="plugin_notice" value="<?php _e('New Unit has been created.', 'cp'); ?>" />
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

                        <div class="level-droppable-rules levels-sortable ui-droppable">
                            <?php _e('Drag & Drop unit modules here', 'cp'); ?>
                        </div>

                        <div id="modules_accordion" class="modules_accordion">
                            <!--modules will appear here-->
                        </div>

                        <div class="buttons">
                            <input type="submit" value="<?php ($unit_id == 0 ? _e('Create', 'cp') : _e('Update', 'cp')); ?>" class="button-primary" />
                        </div>

                    </div>
                </div>

            </div>
        </div> <!-- course-liquid-left -->
    </form>

</div> <!-- wrap -->