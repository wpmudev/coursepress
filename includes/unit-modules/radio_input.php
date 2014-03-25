<?php

class radio_input_module extends Unit_Module {

    var $name = 'radio_input_module';
    var $label = 'Radio Box Input';
    var $description = 'Allows adding radio boxes to the unit';
    var $front_save = true;
    var $response_type = 'view';

    function __construct() {
        $this->on_create();
    }

    function radio_input_module() {
        $this->__construct();
    }

    function get_response_form($user_ID, $response_request_ID, $show_label = true) {
        $response = $this->get_response($user_ID, $response_request_ID);
        if (count($response >= 1)) {
            ?>
            <div class="module_text_response_answer">
                <?php if ($show_label) { ?>
                    <label><?php _e('Response', 'cp'); ?></label>
                <?php } ?>
                <div class="front_response_content radio_input_module">
                    <ul class='radio_answer_check_li'>
                        <?php
                        $answers = get_post_meta($response_request_ID, 'answers', true);
                        $checked_answer = get_post_meta($response_request_ID, 'checked_answer', true);

                        foreach ($answers as $answer) {
                            ?>
                            <li>
                                <input class="radio_answer_check" type="radio" value='<?php echo esc_attr($answer); ?>' disabled <?php echo (isset($response->post_content) && trim($response->post_content) == $answer ? 'checked' : ''); ?> /><?php echo $answer; ?><?php
                                if (isset($response->post_content) && trim($response->post_content) == $answer) {
                                    echo ($checked_answer == $answer ? '<span class="correct_answer">✓</span>' : '<span class="not_correct_answer">✘</span>');
                                };
                                ?>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                    <?php //echo nl2br($response->post_content);    ?>
                </div>
            </div>

            <?php
        } else {
            _e('No answer / response', 'cp');
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
            'post_type' => 'module_response',
            'post_parent' => $response_request_ID,
            'post_status' => 'publish'
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
            <h2 class="module_title"><?php echo $data->post_title; ?></h2>
            <div class="module_description"><?php echo apply_filters('element_content_filter', $data->post_content); ?></div>

            <ul class='radio_answer_check_li'>
                <?php
                foreach ($data->answers as $answer) {
                    ?>
                    <li>
                        <input class="radio_answer_check" type="radio" name="<?php echo $this->name . '_front_' . $data->ID; ?>" value='<?php echo esc_attr($answer); ?>' <?php echo $enabled; ?> <?php echo (isset($response->post_content) && trim($response->post_content) == $answer ? 'checked' : ''); ?> /><?php echo $answer; ?>
                    </li>
                    <?php
                }
                ?>
            </ul>

        </div>
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
                <input type="hidden" name="<?php echo $this->name; ?>_checked_index[]" class='checked_index' value="0" />

                <input type="hidden" name="<?php echo $this->name; ?>_module_order[]" class="module_order" value="<?php echo (isset($data->module_order) ? $data->module_order : 999); ?>" />
                <input type="hidden" name="module_type[]" value="<?php echo $this->name; ?>" />
                <input type="hidden" name="<?php echo $this->name; ?>_id[]" value="<?php echo (isset($data->ID) ? $data->ID : ''); ?>" />
                <label><?php _e('Title', 'cp'); ?>
                    <input type="text" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr(isset($data->post_title) ? $data->post_title : ''); ?>" />
                </label>

                <div class="editor_in_place">
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

                <div class="radio-editor">
                    <table class="form-table">
                        <tbody class="ri_items">
                            <tr>
                                <th width="90%">
                        <div class="radio_answer_check"><?php _e('Answer'); ?></div>
                        <div class="radio_answer"><?php //_e('Answers', 'cp');  ?></div>
                        </th>
                        <th width="10%">
                            <a class="radio_new_link"><?php _e('Add New', 'cp'); ?></a>
                        </th>
                        </tr>

                        <?php
                        $i = 1;
                        ?>

                        <?php
                        if (isset($data->ID)) {
                            $answer_cnt = 0;

                            foreach ($data->answers as $answer) {
                                ?>
                                <tr>
                                    <td width="90%">
                                        <input class="radio_answer_check" type="radio" name="<?php echo $this->name . '_radio_check[' . (isset($data->module_order) ? $data->module_order : 999) . '][]'; ?>" <?php
                                        if ($data->checked_answer == $answer) {
                                            echo 'checked';
                                        }
                                        ?> />
                                        <input class="radio_answer" type="text" name="<?php echo $this->name . '_radio_answers[' . (isset($data->module_order) ? $data->module_order : 999) . '][]'; ?>" value='<?php echo esc_attr((isset($answer) ? $answer : '')); ?>' />

                                    </td>
                                    <?php if ($answer_cnt >= 2) { ?>
                                        <td width="10%">    
                                            <a class="radio_remove" onclick="jQuery(this).parent().parent().remove();">Remove</a>
                                        </td>
                                    <?php } else { ?>
                                        <td width="10%">&nbsp;</td>
                                    <?php } ?>
                                </tr>
                                <?php
                                $answer_cnt++;
                            }
                        } else {
                            ?>
                            <tr>
                                <td width="90%">
                                    <input class="radio_answer_check" type="radio" name="<?php echo $this->name . '_radio_check[' . (isset($data->module_order) ? $data->module_order : 999) . '][]'; ?>" checked />
                                    <input class="radio_answer" type="text" name="<?php echo $this->name . '_radio_answers[' . (isset($data->module_order) ? $data->module_order : 999) . '][]'; ?>" />
                                </td>
                                <td width="10%">&nbsp;</td>  
                            </tr>

                            <tr>
                                <td width="90%">
                                    <input class="radio_answer_check" type="radio" name="<?php echo $this->name . '_radio_check[' . (isset($data->module_order) ? $data->module_order : 999) . '][]'; ?>" />
                                    <input class="radio_answer" type="text" name="<?php echo $this->name . '_radio_answers[' . (isset($data->module_order) ? $data->module_order : 999) . '][]'; ?>" />
                                </td>
                                <td width="10%">&nbsp;</td>  
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>

                </div>

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

            $answers = array();

            foreach ($_POST[$this->name . '_radio_answers'] as $post_answers) {
                $answers[] = $post_answers;
            }

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
                        //cp_write_log($key);
                        $data->ID = $_POST[$this->name . '_id'][$key];
                        $data->unit_id = ((isset($_POST['unit_id']) and isset($_POST['unit']) and $_POST['unit'] != '') ? $_POST['unit_id'] : $last_inserted_unit_id);
                        $data->title = $_POST[$this->name . '_title'][$key];
                        $data->content = $_POST[$this->name . '_content'][$key];
                        $data->metas['module_order'] = $_POST[$this->name . '_module_order'][$key];
                        $data->metas['checked_answer'] = $_POST[$this->name . '_checked_index'][$key];
                        $data->metas['answers'] = $answers[$key];

                        parent::update_module($data);
                    }
                }
            }
        }

        if (isset($_POST['submit_modules_data'])) {

            foreach ($_POST as $response_name => $response_value) {

                if (preg_match('/' . $this->name . '_front_/', $response_name)) {

                    $response_id = intval(str_replace($this->name . '_front_', '', $response_name));

                    if ($response_value != '') {
                        $data = new stdClass();
                        $data->ID = '';
                        $data->title = '';
                        $data->excerpt = '';
                        $data->content = '';
                        $data->metas = array();
                        $data->metas['user_ID'] = get_current_user_id();
                        $data->post_type = 'module_response';
                        $data->response_id = $response_id;
                        $data->title = ''; //__('Response to '.$response_id.' module (Unit '.$_POST['unit_id'].')');
                        $data->content = $response_value;

                        /* CHECK AND SET THE GRADE AUTOMATICALLY */

                        $checked_value = get_post_meta($response_id, 'checked_answer', true);

                        if ($response_value == $checked_value) {
                            $response_grade = 100;
                        } else {
                            $response_grade = 0;
                        }

                        $data->auto_grade = $response_grade;

                        parent::update_module_response($data);
                    }
                }
            }
        }
    }

}

coursepress_register_module('radio_input_module', 'radio_input_module', 'students');
?>