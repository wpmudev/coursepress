<?php

class file_input_module extends Unit_Module {

    var $name = 'file_input_module';
    var $label = 'File Upload';
    var $description = 'Allows adding file upload blocks to the unit';
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
        if (count($response >= 1)) {
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
        $response = $already_respond_posts[0];
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
        <div class="<?php echo $this->name; ?>">
            <h2 class="module_title"><?php echo $data->post_title; ?></h2>
            <div class="module_description"><?php echo $data->post_content; ?></div>
            <div class="module_file_input">     
                <input type="file" name="<?php echo $this->name . '_front_' . $data->ID; ?>" id="<?php echo $this->name . '_front_' . $data->ID; ?>" <?php echo $enabled; ?> />
            </div>
        </div>
        <?php
    }

    function admin_main($data) {
        ?>

        <div class="<?php if (empty($data)) { ?>draggable-<?php } ?>module-holder-<?php echo $this->name; ?> module-holder-title" <?php if (empty($data)) { ?>style="display:none;"<?php } ?>>

            <h3 class="module-title sidebar-name">
                <span class="h3-label"><?php echo $this->label; ?><?php echo (isset($data->post_title) ? ' (' . $data->post_title . ')' : ''); ?></span>
            </h3>

            <div class="module-content">
                <?php if (isset($data->ID)) { parent::get_module_delete_link($data->ID); }else{ parent::get_module_remove_link();} ?>
                <input type="hidden" name="<?php echo $this->name; ?>_module_order[]" class="module_order" value="<?php echo (isset($data->module_order) ? $data->module_order : 999); ?>" />
                <input type="hidden" name="module_type[]" value="<?php echo $this->name; ?>" />
                <input type="hidden" name="<?php echo $this->name; ?>_id[]" value="<?php echo (isset($data->ID) ? $data->ID : ''); ?>" />
                <label><?php _e('Title', 'cp'); ?>
                    <input type="text" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr(isset($data->post_title) ? $data->post_title : ''); ?>" />
                </label>
                <?php // if (!empty($data)) {         ?>
                <div class="editor_in_place">
                    <?php
                    $args = array("textarea_name" => $this->name . "_content[]", "textarea_rows" => 5);
                    wp_editor(stripslashes(esc_attr(isset($data->post_content) ? $data->post_content : '')), (esc_attr(isset($data->ID) ? 'editor_' . $data->ID : '')), $args);
                    ?>
                </div>
                <?php //}else{         ?>
                <!--<div class="editor_to_place">Loading editor...</div>-->
                <?php //}         ?>
            </div>

        </div>

        <?php
    }

    function on_create() {
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

                    foreach ($_POST[$this->name . '_id'] as $key => $value) {
                        $data->ID = $_POST[$this->name . '_id'][$key];
                        $data->unit_id = ((isset($_POST['unit_id']) and $_POST['unit'] != '') ? $_POST['unit_id'] : $last_inserted_unit_id);
                        $data->title = $_POST[$this->name . '_title'][$key];
                        $data->content = $_POST[$this->name . '_content'][$key];
                        $data->metas['module_order'] = $_POST[$this->name . '_module_order'][$key];
                        parent::update_module($data);
                    }
                }
            }
        }

        if (isset($_POST['submit_modules_data'])) {

            if ($_FILES) {
                foreach ($_FILES as $file => $array) {

                    $response_id = intval(str_replace($this->name . '_front_', '', $file));

                    if (!function_exists('wp_handle_upload')) {
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