<?php

class multiple_choice_module extends Unit_Module {

    var $name = 'multiple_choice_module';
    var $label = 'Multiple Choice';
    var $description = 'Allows usage of the Multiple Choice module';

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

coursepress_register_module('multiple_choice_module', 'multiple_choice_module', 'modules');
 
?>