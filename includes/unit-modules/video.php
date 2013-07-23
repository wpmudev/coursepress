<?php

class video_module extends Unit_Module {

    var $name = 'video_module';
    var $label = 'Video';
    var $description = 'Allows usage of the Video module';

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

coursepress_register_module('video_module', 'video_module', 'modules');
 
?>