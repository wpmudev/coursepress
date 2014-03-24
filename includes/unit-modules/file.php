<?php

class file_module extends Unit_Module {

    var $name = 'file_module';
    var $label = 'File Download';
    var $description = 'Allows adding encrypted links to files (URLs) and force download in browser.';
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
        <div class="<?php echo $this->name; ?> front-single-module<?php echo ($this->front_save == true ? '-save' : ''); ?>">
            <?php if ($data->post_title != '') { ?>
                <h2 class="module_title"><?php echo $data->post_title; ?></h2>
            <?php } ?>

            <?php
            if ($data->file_url != '') {
                global $coursepress;

                require_once( $coursepress->plugin_dir . 'includes/classes/class.encryption.php' );
                $encryption = new CP_Encryption();

                $data->file_url = $encryption->encode($data->file_url);
                ?>  
                <div class="file_holder">
                    <a href="<?php echo trailingslashit(site_url()) . '?fdcpf=' . $data->file_url; ?>" /><?php echo $data->post_title; ?></a> 
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

                <label><?php _e('Title', 'cp'); ?>
                    <input type="text" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr(isset($data->post_title) ? $data->post_title : ''); ?>" />
                </label>

                <div class="file_url_holder">
                    <label><?php _e('Put a URL or Browse for a file.', 'cp'); ?>
                        <input class="file_url" type="text" size="36" name="<?php echo $this->name; ?>_file_url[]" value="<?php echo esc_attr((isset($data->file_url) ? $data->file_url : '')); ?>" />
                        <input class="file_url_button" type="button" value="<?php _e('Browse', 'ub'); ?>" />
                    </label>
                </div>

            </div>

        </div>

        <?php
    }

    function on_create() {
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
                            $data->unit_id = ((isset($_POST['unit_id']) and $_POST['unit'] != '') ? $_POST['unit_id'] : $last_inserted_unit_id);
                            $data->title = $_POST[$this->name . '_title'][$key];
                            $data->metas['module_order'] = $_POST[$this->name . '_module_order'][$key];
                            $data->metas['file_url'] = $_POST[$this->name . '_file_url'][$key];
                            parent::update_module($data);
                        }
                    }
                }
            }
        }
    }

}

coursepress_register_module('file_module', 'file_module', 'instructors');
?>