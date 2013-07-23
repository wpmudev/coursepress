<?php

class live_chat_module extends Unit_Module {

    var $name = 'live_chat_module';
    var $label = 'Live Chat';
    var $description = 'Allows usage of the Live Chat module';

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

coursepress_register_module('live_chat_module', 'live_chat_module', 'modules');
 
?>