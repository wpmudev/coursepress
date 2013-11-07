<?php

class file_module extends Unit_Module {

    var $name = 'file_module';
    var $label = 'File Download';
    var $description = 'Allows adding files ready for download';
    var $front_save = false;
    var $response_type = '';

    function __construct() {
        $this->on_create();
    }

    function file_module() {
        $this->__construct();
    }

    function front_main($data) {
        ?>
        <div class="<?php echo $this->name; ?>">
            <?php if ($data->post_title != '') { ?>
                <h2 class="module_title"><?php echo $data->post_title; ?></h2>
            <?php } ?>

            <?php if ($data->file_url != '') { ?>  
                <div class="file_holder">
                    <a href="<?php echo trailingslashit(site_url()).'?fdcpf='.$data->file_url;?>" /><?php echo $data->post_title; ?></a> 
                </div>
        <?php } ?>
        </div>
        <?php
    }

    function admin_main($data) {
        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');
        wp_enqueue_media();
        wp_enqueue_script('media-upload');
        ?>

        <div class="<?php if (empty($data)) { ?>draggable-<?php } ?>module-holder-<?php echo $this->name; ?> module-holder-title" <?php if (empty($data)) { ?>style="display:none;"<?php } ?>>

            <h3 class="module-title sidebar-name"><?php echo $this->label; ?><?php echo (isset($data->post_title) ? ' (' . $data->post_title . ')' : ''); ?></h3>

            <div class="module-content">
                <input type="hidden" name="module_order[]" class="module_order" value="<?php echo (isset($data->module_order) ? get_post_meta($data->ID, 'module_order', true) : 999); ?>" />
                <input type="hidden" name="module_type[]" value="<?php echo $this->name; ?>" />
                <input type="hidden" name="<?php echo $this->name; ?>_id[]" value="<?php echo (isset($data->ID) ? $data->ID : ''); ?>" />

                <label><?php _e('Title', 'cp'); ?>
                    <input type="text" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr(isset($data->post_title) ? $data->post_title : ''); ?>" />
                </label>

                <div class="file_url_holder">
                    <label><?php _e('Put a URL or Browse for a file.', 'cp'); ?>
                        <input class="file_url" type="text" size="36" name="<?php echo $this->name; ?>_file_url[]" value="<?php echo esc_attr($data->file_url); ?>" />
                        <input class="file_url_button" type="button" value="<?php _e('Browse', 'ub'); ?>" />
                    </label>
                </div>
   
            </div>

        </div>

        <?php
    }

    function on_create() {
        $this->save_module_data();
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

                    foreach ($_POST[$this->name . '_id'] as $key => $value) {
                        $data->ID = $_POST[$this->name . '_id'][$key];
                        $data->unit_id = ((isset($_POST['unit_id']) and $_POST['unit'] != '') ? $_POST['unit_id'] : $last_inserted_unit_id);
                        $data->title = $_POST[$this->name . '_title'][$key];
                        $data->metas['module_order'] = $_POST['module_order'][$key];
                        $data->metas['file_url'] = $_POST[$this->name . '_file_url'][$key];
                        parent::update_module($data);
                    }
                }
            }
        }
    }

}

coursepress_register_module('file_module', 'file_module', 'instructors');
?>