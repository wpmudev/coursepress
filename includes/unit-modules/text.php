<?php

class text_module extends Unit_Module {

    var $name = 'text_module';
    var $label = 'Text';
    var $description = 'Allows usage of the Text module';

    function __construct() {
        $this->on_create();
    }

    function text_module() {
        $this->__construct();
    }

    function admin_main($data) {
        if (!$data)
            $data = new stdClass();
        ?>

        <div class="module-holder-<?php echo $this->name; ?>" <?php if (empty($data)) echo "style='display:none;'"; ?>>
            <div class="module-title sidebar-name">
                <h3><?php echo $this->label; ?></h3>
            </div>

            <div class="module-content">
                <input type="hidden" name="module_type" value="<?php echo $this->name; ?>" />
                <input type="hidden" name="<?php echo $this->name; ?>_id[]" value="" />
                <label><?php _e('Title', 'cp'); ?>
                    <input type="text" name="<?php echo $this->name; ?>_title[]" value="" />
                </label>
                <?php
                $args = array("textarea_name" => $this->name . "_content[]", "textarea_rows" => 5);
                wp_editor(stripslashes(''), '', $args);
                ?>
            </div>

        </div>
        <?php
    }

    function on_create() {
            $this->save_module_data();
    }

    function save_module_data() {
        global $wpdb, $last_inserted_unit_id;
          
        if (isset($_POST['module_type']) && $_POST['module_type'] == $this->name) {
            $data = new stdClass();
            $data->ID = '';
            $data->unit_id = '';
            $data->title = '';
            $data->excerpt = '';
            $data->content = '';
            $data->metas = array();

            foreach ($_POST[$this->name . '_id'] as $key => $value) {
                $data->id = $_POST[$this->name . '_id'][$key];
                $data->unit_id = ((isset($_POST['unit_id']) and $_POST['unit'] != '') ? $_POST['unit_id'] : $last_inserted_unit_id);
                $data->title = $_POST[$this->name . '_title'][$key];
                $data->content = $_POST[$this->name . '_content'][$key];
                parent::update_module($data);
            }
            
        }
    }

}

coursepress_register_module('text_module', 'text_module', 'modules');
?>