<?php
class text_module extends Unit_Module {

    var $name = 'text_module';
    var $label = 'Text';
    var $description = 'Allows usage of the Text module';
}

function coursepress_setup_default_modules() {
    M_register_rule('text_module', 'text_module', 'modules');
}

add_action('plugins_loaded', 'coursepress_setup_default_modules', 99);
?>