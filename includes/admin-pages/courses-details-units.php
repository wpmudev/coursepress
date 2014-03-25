<?php
$course_id = '';

if (isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
    $course = new Course($course_id);
    $units = $course->get_units();
}

if (!current_user_can('coursepress_view_all_units_cap') && $course->details->post_author != get_current_user_id()) {
    die(__('You do not have required persmissions to access this page.', 'cp'));
}

if (isset($_GET['unit_id'])) {
    $unit = new Unit($_GET['unit_id']);
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_unit' && isset($_GET['unit_id']) && is_numeric($_GET['unit_id'])) {
    $unit = new Unit($_GET['unit_id']);
    $unit_object = $unit->get_unit();
    if ((current_user_can('coursepress_delete_course_units_cap')) || (current_user_can('coursepress_delete_my_course_units_cap') && $unit_object->post_author == get_current_user_id())) {
        $unit->delete_unit($force_delete = true);
    }
    $units = $course->get_units();
}

if (isset($_GET['action']) && $_GET['action'] == 'change_status' && isset($_GET['unit_id']) && is_numeric($_GET['unit_id'])) {
    $unit = new Unit($_GET['unit_id']);
    $unit_object = $unit->get_unit();
    if ((current_user_can('coursepress_change_course_unit_status_cap')) || (current_user_can('coursepress_change_my_course_unit_status_cap') && $unit_object->post_author == get_current_user_id())) {
        $unit->change_status($_GET['new_status']);
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'add_new_unit' || (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['unit_id']))) {
    $this->show_unit_details();
} else {
    
    wp_redirect("admin.php?page=course_details&tab=units&course_id=".$course_id."&action=add_new_unit");
    exit;

    ?>

    <ul id="sortable-units">
        <?php
        $list_order = 1;
        foreach ($units as $unit) {

            $unit_object = new Unit($unit->ID);
            $unit_object = $unit_object->get_unit();
            
            
            ?>
            <li class="postbox ui-state-default clearfix">
                <div class="unit-order-number"><div class="numberCircle"><?php echo $list_order; ?></div></div>
                <div class="unit-title"><a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&unit_id=<?php echo $unit_object->ID; ?>&action=edit"><?php echo $unit_object->post_title; ?></a></div>
                <div class="unit-description"><?php echo get_the_course_excerpt($unit_object->ID, 28); ?></div>

                <?php if ((current_user_can('coursepress_delete_course_units_cap')) || (current_user_can('coursepress_delete_my_course_units_cap') && $unit_object->post_author == get_current_user_id())) { ?>
                    <div class="unit-remove"><a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&unit_id=<?php echo $unit_object->ID; ?>&action=delete_unit" onClick="return removeUnit();">
                        <i class="fa fa-times-circle cp-move-icon remove-btn"></i>
                        </a></div>
                <?php } ?>
                    
                <div class="unit-buttons unit-control-buttons"><a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&unit_id=<?php echo $unit_object->ID; ?>&action=edit" class="button button-units save-unit-button">Settings</a>
                    <?php if ((current_user_can('coursepress_change_course_unit_status_cap')) || (current_user_can('coursepress_change_my_course_unit_status_cap') && $unit_object->post_author == get_current_user_id())) { ?>
                        <a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&unit_id=<?php echo $unit_object->ID; ?>&action=change_status&new_status=<?php echo ($unit_object->post_status == 'unpublished') ? 'publish' : 'private'; ?>" class="button button-<?php echo ($unit_object->post_status == 'unpublished') ? 'publish' : 'unpublish'; ?>"><?php ($unit_object->post_status == 'unpublished') ? _e('Publish', 'cp') : _e('Unpublish', 'cp'); ?></a>
                    <?php } ?>
                </div>

                <input type="hidden" class="unit_order" value="<?php echo $list_order; ?>" name="unit_order_<?php echo $unit_object->ID; ?>" />
                <input type="hidden" name="unit_id" class="unit_id" value="<?php echo $unit_object->ID; ?>" />
            </li>
            <?php
            $list_order++;
        }
        ?>
    </ul>
    <?php if (current_user_can('coursepress_create_course_unit_cap')) { ?>   
        <ul>
            <li class="postbox ui-state-fixed ui-state-highlight add-new-unit-box">
                <div class="add-new-unit-title">
                    <span class="plusTitle"><a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&action=add_new_unit"><?php _e('Add new Unit', 'cp'); ?></a></span>
                </div>
            </li>
        </ul>
    <?php } ?>

<?php } ?>