<?php

class video_front_page_module extends Front_Page_Module {

    var $order = 3;
    var $name = 'video_front_page_module';
    var $label = 'Video';
    var $description = '';

    function __construct() {
        $this->on_create();
    }

    function video_front_page_module() {
        $this->__construct();
    }

    function front_main($data) {
        ?>
        <div class="<?php echo $this->name; ?> front-single-module<?php echo ( $this->front_save == true ? '-save' : '' ); ?>">
            <?php if ($data->post_title != '' && $this->display_title_on_front($data)) { ?>
                <h2 class="module_title"><?php echo $data->post_title; ?></h2>
            <?php } ?>

            <?php if ($data->post_content != '') { ?>  
                <div class="module_description"><?php echo apply_filters('element_content_filter', $data->post_content); ?></div>
            <?php } ?>

            <?php if ($data->video_url != '') { ?>  
                <div class="video_player">
                    <?php
                    $video_extension = pathinfo($data->video_url, PATHINFO_EXTENSION);

                    if (!empty($video_extension)) {//it's file, most likely on the server
                        $attr = array(
                            'src' => $data->video_url,
                        );

                        echo wp_video_shortcode($attr);
                    } else {

                        echo wp_oembed_get($data->video_url, $embed_args);
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    function admin_main($data) {
        global $content_width;

        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');
        wp_enqueue_media();
        wp_enqueue_script('media-upload');

        $supported_video_extensions = implode(",", wp_get_video_extensions());
        ?>

        <div class="<?php if (empty($data)) { ?>draggable-<?php } ?>module-holder-<?php echo $this->name; ?> module-holder-title" <?php if (empty($data)) { ?>style="display:none;"<?php } ?>>

            <h3 class="module-title sidebar-name">
                <span class="h3-label">
                    <span class="h3-label-left"><?php echo ( isset($data->post_title) && $data->post_title !== '' ? $data->post_title : __('Untitled', 'cp') ); ?></span>
                    <span class="h3-label-center"><?php parent::get_module_width_controls(isset($data->ID) && $data->ID !== '' ? $data->ID : false );?></span>
                    <span class="h3-label-right"><?php echo $this->label; ?></span>
                    <?php
                    parent::get_module_move_link();
                    ?>
                </span>
            </h3>

            <div class="module-content">

                <input type="hidden" name="<?php echo $this->name; ?>_module_order[]" class="module_order" value="<?php echo ( isset($data->module_order) ? $data->module_order : 999 ); ?>" />
                <input type="hidden" name="module_type[]" value="<?php echo $this->name; ?>" />
                <input type="hidden" name="<?php echo $this->name; ?>_id[]" value="<?php echo ( isset($data->ID) ? $data->ID : '' ); ?>" />

                <label class="bold-label"><?php
                    _e('Element Title', 'cp');
                    ?></label>
                <input type="text" class="element_title" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr(isset($data->post_title) ? $data->post_title : '' ); ?>" />


                <label class="show_title_on_front"><?php _e('Show Title', 'cp'); ?>
                    <input type="checkbox" name="<?php echo $this->name; ?>_show_title_on_front[]" value="yes" <?php echo ( isset($data->show_title_on_front) && $data->show_title_on_front == 'yes' ? 'checked' : (!isset($data->show_title_on_front) ) ? 'checked' : '' ) ?> />
                    <a class="help-icon" href="javascript:;"></a>
                    <div class="tooltip">
                        <div class="tooltip-before"></div>
                        <div class="tooltip-button">&times;</div>
                        <div class="tooltip-content">
                            <?php _e('The title is used to identify this element – useful for assessment. If checked, the title is displayed as a heading for this element for the student as well.', 'cp'); ?>
                        </div>
                    </div>
                </label>

                <div class="video_url_holder">
                    <label><?php _e('Put a URL or Browse for a video file.', 'cp'); ?>
                        <a class="help-icon" href="javascript:;"></a>
                        <div class="tooltip">
                            <div class="tooltip-before"></div>
                            <div class="tooltip-button">&times;</div>
                            <div class="tooltip-content">
                                <?php printf(__('You can enter a Youtube or Vimeo link e.g. %s  ( oEmbed support is required ). Alternatively you can Browse for a file - supported video extensions ( %s )', 'cp'), 'https://www.youtube.com/watch?v=y_bIr1yAELw', $supported_video_extensions); ?> 
                            </div>
                        </div>
                        <input class="video_url" type="text" size="36" name="<?php echo $this->name; ?>_video_url[]" value="<?php echo esc_attr(( isset($data->video_url) ? $data->video_url : '')); ?>" />
                        <input class="video_url_button" type="button" value="<?php _e('Browse', 'ub'); ?>" />
                    </label>
                </div>

                <?php
                if (isset($data->ID)) {
                    parent::get_module_delete_link($data->ID);
                } else {
                    parent::get_module_remove_link();
                }
                ?>
            </div>

        </div>

        <?php
    }

    function on_create() {
        $this->description = __('Allows adding video files and video embeds to the unit', 'cp');
        $this->save_module_data();
    }

    function save_module_data() {
        global $wpdb, $save_elements;

        if (isset($_POST['module_type']) && ( $save_elements == true )) {

            foreach (array_keys($_POST['module_type']) as $module_type => $module_value) {

                if ($module_value == $this->name) {
                    $data = new stdClass();
                    $data->ID = '';
                    $data->unit_id = '';
                    $data->title = '';
                    $data->excerpt = '';
                    $data->content = '';
                    $data->metas = array();
                    $data->metas['module_type'] = $this->name;
                    $data->post_type = 'module';

                    if (isset($_POST[$this->name . '_id'])) {
                        foreach ($_POST[$this->name . '_id'] as $key => $value) {
                            $data->ID = $_POST[$this->name . '_id'][$key];
                            $data->title = $_POST[$this->name . '_title'][$key];
                            //$data->content = $_POST[$this->name . '_content'][$key];
                            $data->metas['module_order'] = $_POST[$this->name . '_module_order'][$key];
                            $data->metas['video_url'] = $_POST[$this->name . '_video_url'][$key];
                            //$data->metas['player_width'] = $_POST[$this->name . '_player_width'][$key];

                            if (isset($_POST[$this->name . '_show_title_on_front'][$key])) {
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

coursepress_register_front_page_module('video_front_page_module', 'video_front_page_module', 'output');
?>