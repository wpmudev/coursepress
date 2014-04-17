<?php

class text_module extends Unit_Module {

    var $order = 4;
    var $name = 'text_module';
    var $label = 'Text';
    var $description = '';
    var $front_save = false;
    var $response_type = '';

    function __construct() {
        $this->on_create();
    }

    function text_module() {
        $this->__construct();
    }

    function front_main($data) {
        ?>
        <div class="<?php echo $this->name; ?> front-single-module<?php echo ($this->front_save == true ? '-save' : ''); ?>">
            <?php if ($data->post_title != '' && $this->display_title_on_front($data)) { ?>
                <h2 class="module_title"><?php echo $data->post_title; ?></h2>
            <?php } ?>

            <?php if ($data->post_content != '') { ?>  
                <div class="module_description">
                    <?php echo apply_filters('element_content_filter', $data->post_content); ?>
                </div>
            <?php } ?>

        </div>
        <?php
    }

    function admin_main($data) {
        ?>

        <div class="<?php if (empty($data)) { ?>draggable-<?php } ?>module-holder-<?php echo $this->name; ?> module-holder-title" <?php if (empty($data)) { ?>style="display:none;"<?php } ?>>

            <h3 class="module-title sidebar-name">
                <span class="h3-label">
                    <span class="h3-label-left"><?php echo (isset($data->post_title) && $data->post_title !== '' ? $data->post_title : __('Untitled', 'cp')); ?></span>
                    <span class="h3-label-right"><?php echo $this->label; ?></span>
                    <?php
                    if (isset($data->ID)) {
                        parent::get_module_delete_link($data->ID);
                    } else {
                        parent::get_module_remove_link();
                    }
                    ?>
                </span>
            </h3>

            <div class="module-content">

                <input type="hidden" name="<?php echo $this->name; ?>_module_order[]" class="module_order" value="<?php echo (isset($data->module_order) ? $data->module_order : 999); ?>" />
                <input type="hidden" name="module_type[]" value="<?php echo $this->name; ?>" />
                <input type="hidden" name="<?php echo $this->name; ?>_id[]" value="<?php echo (isset($data->ID) ? $data->ID : ''); ?>" />

                <label class="bold-label"><?php _e('Title', 'cp'); ?></label>
                    <input type="text" class="element_title" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr(isset($data->post_title) ? $data->post_title : ''); ?>" />
                

                <label class="show_title_on_front"><?php _e('Show Title', 'cp'); ?>
                    <input type="checkbox" name="<?php echo $this->name; ?>_show_title_on_front[]" value="yes" <?php echo (isset($data->show_title_on_front) && $data->show_title_on_front == 'yes' ? 'checked' : (!isset($data->show_title_on_front)) ? 'checked' : '') ?> />
                    <a class="help-icon" href="javascript:;"></a>
                    <div class="tooltip">
                        <div class="tooltip-before"></div>
                        <div class="tooltip-button">&times;</div>
                        <div class="tooltip-content">
                            <?php _e('The title is used to identify this element – useful for assessment. If checked, the title is displayed as a heading for this element for the student as well.', 'cp'); ?>
                        </div>
                    </div>
                </label>

                <label class="bold-label"><?php _e('Content', 'cp'); ?></label>
                
                <div class="editor_in_place">
                    <?php
                    $args = array("textarea_name" => $this->name . "_content[]", "textarea_rows" => 5, "teeny" => true, /*'tinymce' =>
                        array(
                            'skin' => 'wordpress',
                            'theme' => 'modern',
                    )*/);
                    $editor_id = (esc_attr(isset($data->ID) ? 'editor_' . $data->ID : rand(1, 9999)));
                    wp_editor(htmlspecialchars_decode((isset($data->post_content) ? $data->post_content : '')), $editor_id, $args);
                    ?>
                </div>

            </div>

        </div>

        <?php
    }

    function on_create() {
        $this->description = __('Add text block to the unit.', 'cp');
        $this->save_module_data();
        parent::additional_module_actions();
    }

    function save_module_data() {
        global $wpdb, $last_inserted_unit_id;

        if (isset($_POST['module_type'])) {

            foreach (array_keys($_POST['module_type']) as $module_type => $module_value) {

                if ($module_value == $this->name) {
                    $data = new stdClass();
                    $data->ID = '';
                    $data->unit_id = '';
                    $data->title = '';
                    $data->excerpt = '';
                    $data->content = '';
                    $data->metas = array();
                    $data->metas['module_type'] = $this->name;
                    $data->post_type = 'module';

                    if (isset($_POST[$this->name . '_id'])) {
                        foreach ($_POST[$this->name . '_id'] as $key => $value) {
                            $data->ID = $_POST[$this->name . '_id'][$key];
                            $data->unit_id = ((isset($_POST['unit_id']) && $_POST['unit_id'] != '') ? $_POST['unit_id'] : $last_inserted_unit_id);
                            $data->title = $_POST[$this->name . '_title'][$key];
                            $data->content = $_POST[$this->name . '_content'][$key];
                            $data->metas['module_order'] = $_POST[$this->name . '_module_order'][$key];
                            if (isset($_POST[$this->name . '_show_title_on_front'][$key])) {
                                $data->metas['show_title_on_front'] = $_POST[$this->name . '_show_title_on_front'][$key];
                            } else {
                                $data->metas['show_title_on_front'] = 'no';
                            }
                            parent::update_module($data);
                        }
                    }
                }
            }
        }
    }

}

coursepress_register_module('text_module', 'text_module', 'instructors');
?>