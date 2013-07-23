<?php

class file_module extends Unit_Module {

    var $name = 'file_module';
    var $label = 'File';
    var $description = 'Allows usage of the File module';

    function admin_main($data) {
        if (!$data)
            $data = array();
        ?>

        <div class="module-holder-<?php echo $this->name; ?>" style="display:none;">
            <div class="module-title sidebar-name">
                 <h3><?php echo $this->label; ?></h3>
            </div>
            <div class="module-content">
                TO DO...
            </div>
        </div>
        <?php
    }

}

coursepress_register_module('file_module', 'file_module', 'modules');
 
?>