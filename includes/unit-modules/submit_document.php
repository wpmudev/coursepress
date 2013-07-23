<?php

class submit_document_module extends Unit_Module {

    var $name = 'submit_document_module';
    var $label = 'Submit a Document';
    var $description = 'Allows usage of the Submit a Document module';

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

coursepress_register_module('submit_document_module', 'submit_document_module', 'modules');
 
?>