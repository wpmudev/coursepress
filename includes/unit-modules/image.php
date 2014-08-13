<?php

class image_module extends Unit_Module {

    var $order = 2;
    var $name = 'image_module';
    var $label = 'Image';
    var $description = '';
    var $front_save = false;
    var $response_type = '';

    function __construct() {
        $this->on_create();
    }

    function image_module() {
        $this->__construct();
    }

    function front_main( $data ) {
        ?>
        <div class="<?php echo $this->name; ?> front-single-module<?php echo ( $this->front_save == true ? '-save' : '' ); ?>">
            <?php if ( $data->post_title != '' && $this->display_title_on_front($data) ) { ?>
                <h2 class="module_title"><?php echo $data->post_title; ?></h2>
            <?php } ?>

            <?php
            if ( $data->image_url != '' ) {
                ?>  
                <div class="image_holder">
                    <img src="<?php echo $data->image_url; ?>" />
                </div>
            <?php } ?>
        </div>
        <?php
    }

    function admin_main( $data ) {
        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');
        wp_enqueue_media();
        wp_enqueue_script('media-upload');
        $supported_image_extensions = implode(", ", wp_get_image_extensions());
        ?>

        <div class="<?php if ( empty($data) ) { ?>draggable-<?php } ?>module-holder-<?php echo $this->name; ?> module-holder-title" <?php if ( empty($data) ) { ?>style="display:none;"<?php } ?>>

            <h3 class="module-title sidebar-name <?php echo!empty($data->active_module) ? 'is_active_module' : ''; ?>" data-panel="<?php echo!empty($data->panel) ? $data->panel : ''; ?>" data-id="<?php echo!empty($data->ID) ? $data->ID : ''; ?>">
                <span class="h3-label">
                    <span class="h3-label-left"><?php echo ( isset($data->post_title) && $data->post_title !== '' ? $data->post_title : __('Untitled', 'cp') ); ?></span>
                    <span class="h3-label-right"><?php echo $this->label; ?></span>
                    <?php
                    parent::get_module_move_link();
                    ?>
                </span>
            </h3>

            <div class="module-content">
                <input type="hidden" name="<?php echo $this->name; ?>_module_order[]" class="module_order" value="<?php echo ( isset($data->module_order) ? $data->module_order : 999 ); ?>" />
                <input type="hidden" name="module_type[]" value="<?php echo $this->name; ?>" />
                <input type="hidden" name="<?php echo $this->name; ?>_id[]" class="unit_element_id" value="<?php echo esc_attr(isset($data->ID) ? $data->ID : '' ); ?>" />

                <input type="hidden" class="element_id" value="<?php echo esc_attr(isset($data->ID) ? $data->ID : '' ); ?>" />

                <label class="bold-label"><?php
                    _e('Element Title', 'cp');
                    $this->time_estimation($data);
                    ?></label>
                <?php echo $this->element_title_description(); ?>
                <input type="text" class="element_title" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr(isset($data->post_title) ? $data->post_title : '' ); ?>" />

                <?php echo $this->show_title_on_front_element($data); ?>

                <div class="editor_in_place" style="display:none;">

                    <?php
                    $args = array(
                        "textarea_name" => $this->name . "_content[]",
                        "textarea_rows" => 5,
                        "quicktags" => false,
                        "teeny" => false
                    );

                    $editor_id = ( esc_attr(isset($data->ID) ? 'editor_' . $data->ID : rand(1, 9999) ) );
                    wp_editor(htmlspecialchars_decode(( isset($data->post_content) ? $data->post_content : '')), $editor_id, $args);
                    ?>
                </div>

                <div class="file_url_holder">
                    <label><?php _e('Enter a URL or Browse for an image.', 'cp'); ?>
                        <input class="image_url" type="text" size="36" name="<?php echo $this->name; ?>_image_url[]" value="<?php echo esc_attr(( isset($data->image_url) ? $data->image_url : '')); ?>" />
                        <input class="image_url_button" type="button" value="<?php _e('Browse', 'ub'); ?>" />
                        <div class="invalid_extension_message"><?php echo sprintf(__('Extension of the file is not valid. Please use one of the following: %s', 'cp'), $supported_image_extensions); ?></div>
                    </label>
                </div>
                <?php
                parent::get_module_delete_link();
                ?>
            </div>

        </div>

        <?php
    }

    function on_create() {
        $this->order = apply_filters($this->name . '_order', $this->order);
        $this->description = __('Image, 100% width', 'cp');
        $this->save_module_data();
        parent::additional_module_actions();
    }

    function save_module_data() {
        global $wpdb, $last_inserted_unit_id, $save_elements;

        if ( isset($_POST['module_type']) && ( $save_elements == true ) ) {

            foreach ( array_keys($_POST['module_type']) as $module_type => $module_value ) {

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

                    if ( isset($_POST[$this->name . '_id']) ) {
                        foreach ( $_POST[$this->name . '_id'] as $key => $value ) {
                            $data->ID = $_POST[$this->name . '_id'][$key];
                            $data->unit_id = ( ( isset($_POST['unit_id']) and ( isset($_POST['unit']) && $_POST['unit'] != '' ) ) ? $_POST['unit_id'] : $last_inserted_unit_id );
                            $data->title = $_POST[$this->name . '_title'][$key];
                            $data->metas['module_order'] = $_POST[$this->name . '_module_order'][$key];
                            $data->metas['image_url'] = $_POST[$this->name . '_image_url'][$key];
                            $data->metas['time_estimation'] = $_POST[$this->name . '_time_estimation'][$key];

                            if ( isset($_POST[$this->name . '_show_title_on_front'][$key]) ) {
                                $data->metas['show_title_on_front'] = $_POST[$this->name . '_show_title_on_front'][$key];
                            } else {
                                $data->metas['show_title_on_front'] = 'no';
                            }
                            parent::update_module($data);
                        }
                    }
                }
            }
        }
    }

}

coursepress_register_module('image_module', 'image_module', 'output');
?>