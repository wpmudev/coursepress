<?php

class text_input_module extends Unit_Module {

    var $order = 5;
    var $name = 'text_input_module';
    var $label = 'Answer Field';
    var $description = '';
    var $front_save = true;
    var $response_type = 'view';

    function __construct() {
        $this->on_create();
    }

    function text_input_module() {
        $this->__construct();
    }

    function get_response_form( $user_ID, $response_request_ID, $show_label = true ) {
        $response = $this->get_response( $user_ID, $response_request_ID );

        $answer_length = get_post_meta( $response_request_ID, 'answer_length', false );

        if ( count( ( array ) $response >= 1 ) ) {
            if ( ( isset( $answer_length ) && $answer_length == 'single' ) || !isset( $answer_length ) ) {
                ?>
                <div class="module_text_response_answer">
                    <?php if ( $show_label ) { ?>
                        <label><?php _e( 'Response', 'cp' ); ?></label>
                    <?php } ?>
                    <div class="front_response_content">
                        <?php echo nl2br( $response->post_content ); ?>
                    </div>               
                </div>

                <?php
            } else {
                ?>
                <div class="module_textarea_response_answer">
                    <?php if ( $show_label ) { ?>
                        <label><?php _e( 'Response', 'cp' ); ?></label>
                    <?php } ?>
                    <div class="front_response_content">
                        <?php echo nl2br( $response->post_content ); ?>
                    </div>
                </div>
                <?php
            }
        } else {
            _e( 'No answer / response', 'cp' );
        }
        ?>
        <div class="full regular-border-devider"></div>
        <?php
    }

    function get_response( $user_ID, $response_request_ID ) {
        $already_respond_posts_args = array(
            'posts_per_page' => 1,
            'meta_key' => 'user_ID',
            'meta_value' => $user_ID,
            'post_type' => 'module_response',
            'post_parent' => $response_request_ID,
            'post_status' => 'publish'
        );


        $already_respond_posts = get_posts( $already_respond_posts_args );

        if ( isset( $already_respond_posts[0] ) && is_object( $already_respond_posts[0] ) ) {
            $response = $already_respond_posts[0];
        } else {
            $response = $already_respond_posts;
        }

        return $response;
    }

    function front_main( $data ) {

        $response = $this->get_response( get_current_user_id(), $data->ID );

        if ( count( $response ) == 0 ) {
            $enabled = 'enabled';
        } else {
            $enabled = 'disabled';
        }
        ?>

        <?php if ( ( isset( $data->answer_length ) && $data->answer_length == 'single' ) || ( !isset( $data->answer_length ) ) ) { ?>

            <div class="<?php echo $this->name; ?> front-single-module<?php echo ( $this->front_save == true ? '-save' : '' ); ?>">
                <?php if ( $data->post_title != '' && $this->display_title_on_front( $data ) ) { ?>
                    <h2 class="module_title"><?php echo $data->post_title; ?></h2>
                <?php } ?>

                <?php if ( $data->post_content != '' ) { ?>  
                    <div class="module_description"><?php echo apply_filters( 'element_content_filter', $data->post_content ); ?></div>
                <?php } ?>

                <?php if ( is_object( $response ) && count( $response ) >= 1 && trim( $response->post_content ) !== '' ) { ?>
                    <div class="front_response_content">
                        <?php echo $response->post_content; ?>
                    </div>
                <?php } else { ?>
                    <div class="module_textarea_input"><input <?php echo ( $data->mandatory_answer == 'yes' ) ? 'data-mandatory="yes"' : 'data-mandatory="no"'; ?> type="text" name="<?php echo $this->name . '_front_' . $data->ID; ?>" id="<?php echo $this->name . '_front_' . $data->ID; ?>" placeholder="<?php esc_attr_e( isset( $data->placeholder_text ) && $data->placeholder_text !== '' ? $data->placeholder_text : '' ); ?>" value="<?php echo ( is_object( $response ) && count( $response >= 1 ) ? esc_attr( $response->post_content ) : '' ); ?>" <?php echo $enabled; ?> /></div>
                <?php } ?>

                <?php if ( $data->mandatory_answer == 'yes' ) { ?>
                    <span class="mandatory_answer"><?php _e( '* Mandatory', 'cp' ); ?></span>
                <?php } ?>
            </div>
            <?php
        } else {
            ?>
            <div class="<?php echo $this->name; ?> front-single-module<?php echo ( $this->front_save == true ? '-save' : '' ); ?>">
                <?php if ( $data->post_title != '' && $this->display_title_on_front( $data ) ) { ?>
                    <h2 class="module_title"><?php echo $data->post_title; ?></h2>
                <?php } ?>

                <?php if ( $data->post_content != '' ) { ?>  
                    <div class="module_description"><?php echo apply_filters( 'element_content_filter', $data->post_content ); ?></div>
                <?php } ?>

                <div class="module_textarea_input">
                    <?php if ( count( $response ) >= 1 && trim( $response->post_content ) !== '' ) { ?>
                        <div class="front_response_content">
                            <?php echo $response->post_content; ?>
                        </div>
                    <?php } else { ?>
                        <textarea <?php echo ( $data->mandatory_answer == 'yes' ) ? 'data-mandatory="yes"' : 'data-mandatory="no"'; ?> class="<?php echo $this->name . '_front'; ?>" name="<?php echo $this->name . '_front_' . $data->ID; ?>" id="<?php echo $this->name . '_front_' . $data->ID; ?>" placeholder="<?php esc_attr_e( isset( $data->placeholder_text ) && $data->placeholder_text !== '' ? $data->placeholder_text : '' ); ?>"></textarea>
                    <?php } ?>
                </div>

                <?php if ( $data->mandatory_answer == 'yes' ) { ?>
                    <span class="mandatory_answer"><?php _e( '* Mandatory', 'cp' ); ?></span>
                <?php } ?>
            </div>
            <?php
        }
    }

    function admin_main( $data ) {
        ?>

        <div class="<?php if ( empty( $data ) ) { ?>draggable-<?php } ?>module-holder-<?php echo $this->name; ?> module-holder-title" <?php if ( empty( $data ) ) { ?>style="display:none;"<?php } ?>>

            <h3 class="module-title sidebar-name">
                <span class="h3-label">
                    <span class="h3-label-left"><?php echo ( isset( $data->post_title ) && $data->post_title !== '' ? $data->post_title : __( 'Untitled', 'cp' ) ); ?></span>
                    <span class="h3-label-right"><?php echo $this->label; ?></span>
                    <?php
                    if ( isset( $data->ID ) ) {
                        parent::get_module_delete_link( $data->ID );
                    } else {
                        parent::get_module_remove_link();
                    }
                    ?>
                </span>
            </h3>

            <div class="module-content">
                <input type="hidden" name="<?php echo $this->name; ?>_module_order[]" class="module_order" value="<?php echo ( isset( $data->module_order ) ? $data->module_order : 999 ); ?>" />
                <input type="hidden" name="module_type[]" value="<?php echo $this->name; ?>" />
                <input type="hidden" name="<?php echo $this->name; ?>_id[]" value="<?php echo ( isset( $data->ID ) ? $data->ID : '' ); ?>" />

                <label class="bold-label"><?php _e( 'Title', 'cp' ); ?></label>
                <input type="text" class="element_title" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr( isset( $data->post_title ) ? $data->post_title : '' ); ?>" />

                <div class="group-check">
                    <label class="show_title_on_front"><?php _e( 'Show Title', 'cp' ); ?>
                        <input type="checkbox" name="<?php echo $this->name; ?>_show_title_on_front[]" value="yes" <?php echo ( isset( $data->show_title_on_front ) && $data->show_title_on_front == 'yes' ? 'checked' : ( !isset( $data->show_title_on_front ) ) ? 'checked' : '' ) ?> />
                        <a class="help-icon" href="javascript:;"></a>
                        <div class="tooltip">
                            <div class="tooltip-before"></div>
                            <div class="tooltip-button">&times;</div>
                            <div class="tooltip-content">
                                <?php _e( 'The title is used to identify this element – useful for assessment. If checked, the title is displayed as a heading for this element for the student as well.', 'cp' ); ?>
                            </div>
                        </div>
                    </label>

                    <label class="mandatory_answer"><?php _e( 'Mandatory Answer', 'cp' ); ?>
                        <input type="checkbox" name="<?php echo $this->name; ?>_mandatory_answer[]" value="yes" <?php echo ( isset( $data->mandatory_answer ) && $data->mandatory_answer == 'yes' ? 'checked' : ( !isset( $data->mandatory_answer ) ) ? 'checked' : '' ) ?> />
                        <a class="help-icon" href="javascript:;"></a>
                        <div class="tooltip">
                            <div class="tooltip-before"></div>
                            <div class="tooltip-button">&times;</div>
                            <div class="tooltip-content">
                                <?php _e( 'Student will need to provide a response on this question in order to continue the unit.', 'cp' ); ?>
                            </div>
                        </div>
                    </label>

                    <label class="mandatory_answer"><?php _e( 'Assessable', 'cp' ); ?>
                        <input type="checkbox" name="<?php echo $this->name; ?>_gradable_answer[]" value="yes" <?php echo ( isset( $data->gradable_answer ) && $data->gradable_answer == 'yes' ? 'checked' : ( !isset( $data->gradable_answer ) ) ? 'checked' : '' ) ?> />
                        <a class="help-icon" href="javascript:;"></a>
                        <div class="tooltip">
                            <div class="tooltip-before"></div>
                            <div class="tooltip-button">&times;</div>
                            <div class="tooltip-content">
                                <?php _e( 'If checked, this question will be graded. If not checked, the response can still be viewed within the Assessment section but listed as Non-assessable.', 'cp' ); ?>
                            </div>
                        </div>
                    </label>
                </div>

                <label class="bold-label"><?php _e( 'Content', 'cp' ); ?></label>

                <div class="editor_in_place">
                    <?php
                    $args = array(
                        "textarea_name" => $this->name . "_content[]",
                        "textarea_rows" => 5,
                        "quicktags" => false,
                        "teeny" => true,
                    );

                    $editor_id = ( esc_attr( isset( $data->ID ) ? 'editor_' . $data->ID : rand( 1, 9999 ) ) );
                    wp_editor( htmlspecialchars_decode( ( isset( $data->post_content ) ? $data->post_content : '' ) ), $editor_id, $args );
                    ?>
                </div>

                <div class="answer_length">  
                    <label class="bold-label"><?php _e( 'Answer Length', 'cp' ); ?></label>
                    <input type="radio" name="<?php echo $this->name; ?>_answer_length[]" value="single" <?php ?> <?php echo ( isset( $data->answer_length ) && $data->answer_length == 'single' ? 'checked' : ( !isset( $data->answer_length ) ) ? 'checked' : '' ) ?> /> <?php _e( 'Single Line', 'tc' ); ?><br /><br />
                    <input type="radio" name="<?php echo $this->name; ?>_answer_length[]" value="multi" <?php echo ( isset( $data->answer_length ) && $data->answer_length == 'multi' ? 'checked' : '' ); ?> /> <?php _e( 'Multiple Lines', 'tc' ); ?>
                </div>

                <div class="placeholder_holder">
                    <label><?php _e( 'Placeholder Text' ) ?>
                        <a class="help-icon" href="javascript:;"></a>
                        <div class="tooltip">
                            <div class="tooltip-before"></div>
                            <div class="tooltip-button">&times;</div>
                            <div class="tooltip-content">
                                <?php _e( 'Additional instructions visible in the input field as a placeholder', 'cp' ); ?>
                            </div>
                        </div>
                    </label>
                    <input type="text" class="placeholder_text" name="<?php echo $this->name; ?>_placeholder_text[]" value="<?php echo esc_attr( isset( $data->placeholder_text ) ? $data->placeholder_text : '' ); ?>" />
                </div>

            </div>

        </div>

        <?php
    }

    function on_create() {
        $this->description = __( 'Allow students to enter a single line of text', 'cp' );
        $this->save_module_data();
        parent::additional_module_actions();
    }

    function save_module_data() {
        global $wpdb, $last_inserted_unit_id, $save_elements;

        if ( isset( $_POST['module_type'] ) && ( $save_elements == true ) ) {
            
            foreach ( array_keys( $_POST['module_type'] ) as $module_type => $module_value ) {

                if ( $module_value == $this->name ) {
                    $data = new stdClass();
                    $data->ID = '';
                    $data->unit_id = '';
                    $data->title = '';
                    $data->excerpt = '';
                    $data->content = '';
                    $data->metas = array();
                    $data->metas['module_type'] = $this->name;
                    $data->post_type = 'module';

                    if ( isset( $_POST[$this->name . '_id'] ) ) {
                        foreach ( $_POST[$this->name . '_id'] as $key => $value ) {
                            $data->ID = $_POST[$this->name . '_id'][$key];
                            $data->unit_id = ( ( isset( $_POST['unit_id'] ) and ( isset( $_POST['unit'] ) && $_POST['unit'] != '' ) ) ? $_POST['unit_id'] : $last_inserted_unit_id );
                            $data->title = $_POST[$this->name . '_title'][$key];
                            $data->content = $_POST[$this->name . '_content'][$key];
                            $data->metas['module_order'] = $_POST[$this->name . '_module_order'][$key];
                            $data->metas['placeholder_text'] = $_POST[$this->name . '_placeholder_text'][$key];
                            $data->metas['answer_length'] = $_POST[$this->name . '_answer_length'][$key];

                            if ( isset( $_POST[$this->name . '_show_title_on_front'][$key] ) ) {
                                $data->metas['show_title_on_front'] = $_POST[$this->name . '_show_title_on_front'][$key];
                            } else {
                                $data->metas['show_title_on_front'] = 'no';
                            }

                            if ( isset( $_POST[$this->name . '_mandatory_answer'][$key] ) ) {
                                $data->metas['mandatory_answer'] = $_POST[$this->name . '_mandatory_answer'][$key];
                            } else {
                                $data->metas['mandatory_answer'] = 'no';
                            }

                            if ( isset( $_POST[$this->name . '_gradable_answer'][$key] ) ) {
                                $data->metas['gradable_answer'] = $_POST[$this->name . '_gradable_answer'][$key];
                            } else {
                                $data->metas['gradable_answer'] = 'no';
                            }

                            parent::update_module( $data );
                        }
                    }
                }
            }
        }

        if ( isset( $_POST['submit_modules_data_save'] ) || isset( $_POST['submit_modules_data_done'] ) ) {

            foreach ( $_POST as $response_name => $response_value ) {

                if ( preg_match( '/' . $this->name . '_front_/', $response_name ) ) {
                    //echo $response_name . ',' . $response_value . '<br />';

                    $response_id = intval( str_replace( $this->name . '_front_', '', $response_name ) );

                    if ( $response_value != '' ) {
                        $data = new stdClass();
                        $data->ID = '';
                        $data->title = '';
                        $data->excerpt = '';
                        $data->content = '';
                        $data->metas = array();
                        $data->metas['user_ID'] = get_current_user_id();
                        $data->post_type = 'module_response';
                        $data->response_id = $response_id;
                        $data->title = ''; //__( 'Response to '.$response_id.' module ( Unit '.$_POST['unit_id'].' )' );
                        $data->content = $response_value;

                        parent::update_module_response( $data );
                    }
                }
            }
        }
    }

}

coursepress_register_module( 'text_input_module', 'text_input_module', 'input' );
?>