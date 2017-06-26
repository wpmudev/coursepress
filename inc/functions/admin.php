<?php
function coursepress_visual_editor( $content, $id, $settings = array() ) {
    if ( empty( $settings['editor_height'] ) ) {
        $settings['editor_height'] = 300;
    }
    wp_editor( $content, $id, $settings );
}

function coursepress_teeny_editor( $content, $id, $settings = array() ) {
    $settings['teeny'] = true;
    $settings['media_buttons'] = false;

    coursepress_visual_editor( $content, $id, $settings );
}