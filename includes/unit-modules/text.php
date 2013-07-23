<?php

class text_module extends Unit_Module {

    var $name = 'text_module';
    var $label = 'Text';
    var $description = 'Allows usage of the Text module';

    function admin_main($data) {
        if (!$data)
            $data = array();
        ?>

        <div class="module-holder-<?php echo $this->name; ?>" style="display:none;">
            <div class="module-title sidebar-name">
                 <h3><?php echo $this->label; ?></h3>
            </div>
            <div class="module-content">
                <?php
                $args = array("textarea_name" => $this->name, "textarea_rows" => 5);
                wp_editor(stripslashes(''), '', $args);
                ?>
            </div>
        </div>
        <?php
    }

}

coursepress_register_module('text_module', 'text_module', 'modules');
 
?>