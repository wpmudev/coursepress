<?php

class open_answer_question_module extends Unit_Module {

    var $name = 'open_answer_question_module';
    var $label = 'Open Answer Question';
    var $description = 'Allows usage of the Open Answer Question module';

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

coursepress_register_module('open_answer_question_module', 'open_answer_question_module', 'modules');
 
?>