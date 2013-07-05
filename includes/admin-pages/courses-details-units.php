<?php
$course_id = '';
if (isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
}

if(isset($_GET['unit_id'])){
    $unit =  new Unit($_GET['unit_id']);
}

if(isset($_GET['action']) && $_GET['action'] == 'delete_unit' && isset($_GET['unit_id']) && is_numeric($_GET['unit_id'])){
    $unit->delete_unit($force_delete = true);
}

if(isset($_GET['action']) && $_GET['action'] == 'change_status' && isset($_GET['unit_id']) && is_numeric($_GET['unit_id'])){
    $unit->change_status($_GET['new_status']);
}

if (isset($_GET['action']) && $_GET['action'] == 'add_new_unit' || (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['unit_id']))) {
    $this->show_unit_details();
} else {
    ?>

    <?php
    $args = array(
        'category' => '',
        'order' => 'ASC',
        'post_type' => 'unit',
        'post_mime_type' => '',
        'post_parent' => '',
        'post_status' => 'any',
        'meta_key' => 'unit_order',
        'orderby' => 'meta_value_num',
        'meta_query' => array(
            array(
                'key' => 'course_id',
                'value' => $course_id
            ),
        )
    );

    $units = get_posts($args);
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
                <div class="unit-title"><a href="?page=course_details&tab=units&course_id=<?php echo $course_id;?>&unit_id=<?php echo $unit_object->ID; ?>&action=edit"><?php echo $unit_object->post_title; ?></a></div>
                <div class="unit-description"><?php echo get_the_course_excerpt($unit_object->ID, 28); ?></div>
                <div class="unit-remove"><a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&unit_id=<?php echo $unit_object->ID; ?>&action=delete_unit" onClick="return removeUnit();" class="remove-button"></a></div>
                <div class="unit-buttons"><a href="?page=course_details&tab=units&course_id=<?php echo $course_id;?>&unit_id=<?php echo $unit_object->ID; ?>&action=edit" class="button button-settings">Settings</a>
                <a href="?page=course_details&tab=units&course_id=<?php echo $course_id;?>&unit_id=<?php echo $unit_object->ID; ?>&action=change_status&new_status=<?php echo ($unit_object->post_status == 'unpublished') ? 'publish' : 'private'; ?>" class="button button-<?php echo ($unit_object->post_status == 'unpublished') ? 'unpublish' : 'publish'; ?>"><?php ($unit_object->post_status == 'unpublished') ? _e('Publish', 'cp') : _e('Unpublish', 'cp'); ?></a></div>
                <input type="hidden" class="unit_order" value="<?php echo $list_order; ?>" name="unit_order_<?php echo $unit_object->ID; ?>" />
                <input type="hidden" name="unit_id" class="unit_id" value="<?php echo $unit_object->ID;?>" />
            </li>
            <?php
            $list_order++;
        }
        ?>
    </ul>
    <ul>
        <li class="postbox ui-state-fixed ui-state-highlight add-new-unit-box">
            <div class="add-new-unit-title">
                <span class="plusTitle"><a href="?page=course_details&tab=units&course_id=<?php echo $course_id; ?>&action=add_new_unit"><?php _e('Add new Unit', 'cp'); ?></a></span>
            </div>
        </li>
    </ul>

<?php } ?>