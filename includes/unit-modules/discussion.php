<?php

class discussion_module extends Unit_Module {

    var $name = 'discussion_module';
    var $label = 'Discussion';
    var $description = 'Allows usage of the Discussion module';

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

coursepress_register_module('discussion_module', 'discussion_module', 'modules');
 
?>