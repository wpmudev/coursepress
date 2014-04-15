<?php

class file_input_module extends Unit_Module {

    var $order = 11;
    var $name = 'file_input_module';
    var $label = 'File Upload';
    var $description = '';
    var $front_save = true;
    var $response_type = 'file';

    function __construct() {
        $this->on_create();
    }

    function text_input_module() {
        $this->__construct();
    }

    function get_response_form($user_ID, $response_request_ID, $show_label = true) {
        global $coursepress;

        $response = $this->get_response($user_ID, $response_request_ID);
        if (count((array) $response >= 1)) {
            require_once( $coursepress->plugin_dir . 'includes/classes/class.encryption.php' );
            $encryption = new CP_Encryption();

            $file_extension = strtoupper(pathinfo($response->guid, PATHINFO_EXTENSION));

            $response->guid = $encryption->encode($response->guid);
            ?>
            <div class="module_file_response_answer">
                <?php if ($show_label) { ?>
                    <label><?php _e('Uploaded File', 'cp'); ?></label>
                <?php } ?>
                <div class="front_response_content">
                    <a href="<?php echo trailingslashit(site_url()) . '?fdcpf=' . $response->guid; ?>"><?php
                        _e('Download file ', 'cp');
                        echo ' (' . $file_extension . ')';
                        ?></a>
                </div>
            </div>

            <?php
        } else {
            _e('File not uploaded yet.', 'cp');
        }
        ?>
        <div class="full regular-border-devider"></div>
        <?php
    }

    function get_response($user_ID, $response_request_ID) {
        $already_respond_posts_args = array(
            'posts_per_page' => 1,
            'meta_key' => 'user_ID',
            'meta_value' => $user_ID,
            'post_type' => 'attachment',
            'post_parent' => $response_request_ID,
            'post_status' => 'inherit'
        );

        $already_respond_posts = get_posts($already_respond_posts_args);

        if (isset($already_respond_posts[0]) && is_object($already_respond_posts[0])) {
            $response = $already_respond_posts[0];
        } else {
            $response = $already_respond_posts;
        }

        return $response;
    }

    function front_main($data) {

        $response = $this->get_response(get_current_user_id(), $data->ID);

        if (count($response) == 0) {
            $enabled = 'enabled';
        } else {
            $enabled = 'disabled';
        }
        ?>
        <div class="<?php echo $this->name; ?> front-single-module<?php echo ($this->front_save == true ? '-save' : ''); ?>">
            <?php if ($data->post_title != '' && $this->display_title_on_front($data)) { ?>
                <h2 class="module_title"><?php echo $data->post_title; ?></h2>
            <?php } ?>

            <?php if ($data->post_content != '') { ?>  
                <div class="module_description"><?php echo apply_filters('element_content_filter', $data->post_content); ?></div>
            <?php } ?>

            <div class="module_file_input">     
                <input type="file" name="<?php echo $this->name . '_front_' . $data->ID; ?>" id="<?php echo $this->name . '_front_' . $data->ID; ?>" <?php echo $enabled; ?> />
            </div>
        </div>

        <?php
        $unit_module_main = new Unit_Module();

        if (is_object($response) && !empty($response)) {

            $comment = $unit_module_main->get_response_comment($response->ID);
            if (!empty($comment)) {
                ?>
                <div class="response_comment_front"><?php echo $comment; ?></div>
                <?php
            }
        }
        ?>

        <?php
    }

    function admin_main($data) {
        ?>

        <div class="<?php if (empty($data)) { ?>draggable-<?php } ?>module-holder-<?php echo $this->name; ?> module-holder-title" <?php if (empty($data)) { ?>style="display:none;"<?php } ?>>

            <h3 class="module-title sidebar-name">
                <span class="h3-label">
                    <span class="h3-label-left"><?php echo (isset($data->post_title) && $data->post_title !== '' ? $data->post_title : __('Untitled', 'cp')); ?></span>
                    <span class="h3-label-right"><?php echo $this->label; ?></span>
                    <?php
                    if (isset($data->ID)) {
                        parent::get_module_delete_link($data->ID);
                    } else {
                        parent::get_module_remove_link();
                    }
                    ?>
                </span>
            </h3>

            <div class="module-content">
                <input type="hidden" name="<?php echo $this->name; ?>_module_order[]" class="module_order" value="<?php echo (isset($data->module_order) ? $data->module_order : 999); ?>" />
                <input type="hidden" name="module_type[]" value="<?php echo $this->name; ?>" />
                <input type="hidden" name="<?php echo $this->name; ?>_id[]" value="<?php echo (isset($data->ID) ? $data->ID : ''); ?>" />

                <label><?php _e('Title', 'cp'); ?>
                    <input type="text" class="element_title" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr(isset($data->post_title) ? $data->post_title : ''); ?>" />
                </label>

                <label class="show_title_on_front"><?php _e('Show Title', 'cp'); ?>
                    <input type="checkbox" name="<?php echo $this->name; ?>_show_title_on_front[]" value="yes" <?php echo (isset($data->show_title_on_front) && $data->show_title_on_front == 'yes' ? 'checked' : (!isset($data->show_title_on_front)) ? 'checked' : '') ?> />
                    <a class="help-icon" href="javascript:;"></a>
                    <div class="tooltip">
                        <div class="tooltip-before"></div>
                        <div class="tooltip-button">&times;</div>
                        <div class="tooltip-content">
                            <?php _e('The title is used to identify this element – useful for assessment. If checked, the title is displayed as a heading for this element for the student as well.', 'cp'); ?>
                        </div>
                    </div>
                </label>

                <div class="editor_in_place">
                    <label><?php _e('Content', 'cp'); ?></label>
                    <?php
                    $args = array("textarea_name" => $this->name . "_content[]", "textarea_rows" => 5, "teeny" => true, 'tinymce' =>
                        array(
                            'skin' => 'wp_theme',
                            'theme' => 'advanced',
                    ));
                    $editor_id = (esc_attr(isset($data->ID) ? 'editor_' . $data->ID : rand(1, 9999)));
                    wp_editor(htmlspecialchars_decode((isset($data->post_content) ? $data->post_content : '')), $editor_id, $args);
                    ?>
                </div>
            </div>

        </div>

        <?php
    }

    function on_create() {
        $this->description = __('Add file upload blocks to the unit. Useful if students need to send you various files like essay, homework etc.', 'cp');
        $this->save_module_data();
        parent::additional_module_actions();
    }

    function save_module_data() {
        global $wpdb, $last_inserted_unit_id;

        if (isset($_POST['module_type'])) {

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
                            $data->unit_id = ((isset($_POST['unit_id']) and $_POST['unit'] != '') ? $_POST['unit_id'] : $last_inserted_unit_id);
                            $data->title = $_POST[$this->name . '_title'][$key];
                            $data->content = $_POST[$this->name . '_content'][$key];
                            $data->metas['module_order'] = $_POST[$this->name . '_module_order'][$key];
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

        if (isset($_POST['submit_modules_data'])) {

            if ($_FILES) {
                foreach ($_FILES as $file => $array) {

                    $response_id = intval(str_replace($this->name . '_front_', '', $file));

                    if (!function_exists('wp_handle_upload')) {
                        require_once( ABSPATH . 'wp-includes/pluggable.php');
                        require_once( ABSPATH . 'wp-admin/includes/file.php' );
                    }

                    $uploadedfile = $_FILES[$file];
                    $upload_overrides = array('test_form' => false);

                    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

                    if ($movefile) {
                        //var_dump($movefile);

                        if (!isset($movefile['error'])) {

                            $filename = $movefile['file'];

                            $wp_upload_dir = wp_upload_dir();

                            $attachment = array(
                                'guid' => $movefile['url'],
                                'post_mime_type' => $movefile['type'],
                                'post_title' => basename($movefile['url']),
                                'post_content' => '',
                                'post_status' => 'inherit'
                            );

                            $attach_id = wp_insert_attachment($attachment, $filename, $response_id);

                            $unit_id = get_post_ancestors($response_id);
                            $course_id = get_post_meta($unit_id[0], 'course_id', true);

                            update_post_meta($attach_id, 'user_ID', get_current_user_ID());
                            update_post_meta($attach_id, 'course_id', $course_id);
                        } else {
                            ?>
                            <p class="form-info-red"><?php echo $movefile['error']; ?></p>
                            <?php
                        }
                    } else {
                        
                    }
                }
            }
        }
    }

}

coursepress_register_module('file_input_module', 'file_input_module', 'students');
?>