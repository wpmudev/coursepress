<?php

class checkbox_input_module extends Unit_Module {

    var $order = 7;
    var $name = 'checkbox_input_module';
    var $label = 'Multiple Choice';
    var $description = '';
    var $front_save = true;
    var $response_type = 'view';

    function __construct() {
        $this->on_create();
    }

    function checkbox_input_module() {
        $this->__construct();
    }

    function get_response_form($user_ID, $response_request_ID, $show_label = true) {
        $response = $this->get_response($user_ID, $response_request_ID);
        if ($response) {
            $student_checked_answers = get_post_meta($response->ID, 'student_checked_answers', true);
            ?>
            <div class="module_text_response_answer">
                <?php if ($show_label) { ?>
                    <label><?php _e('Response', 'cp'); ?></label>
                <?php } ?>
                <div class="front_response_content radio_input_module">
                    <ul class='radio_answer_check_li'>
                        <?php
                        $answers = get_post_meta($response_request_ID, 'answers', true);
                        $checked_answers = get_post_meta($response_request_ID, 'checked_answers', true);

                        foreach ($answers as $answer) {
                            ?>
                            <li>
                                <input class="radio_answer_check" type="checkbox" value='<?php echo esc_attr($answer); ?>' disabled <?php echo (isset($student_checked_answers) && in_array($answer, $student_checked_answers) ? 'checked' : ''); ?> /><?php echo $answer; ?><?php
                                if (isset($student_checked_answers) && in_array($answer, $student_checked_answers)) {
                                    echo (in_array($answer, $checked_answers) ? '<span class="correct_answer">✓</span>' : '<span class="not_correct_answer">✘</span>');
                                };
                                ?>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
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

        if (is_object($response)) {
            $student_checked_answers = get_post_meta($response->ID, 'student_checked_answers', true);
        }

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

            <ul class='radio_answer_check_li checkbox_answer_group' <?php echo ($data->mandatory_answer == 'yes') ? 'data-mandatory="yes"' : 'data-mandatory="no"';?>>
                <?php
                if (isset($data->answers) && !empty($data->answers)) {
                    foreach ($data->answers as $answer) {
                        ?>
                        <li>
                            <input class="checkbox_answer_check" type="checkbox" name="<?php echo $this->name . '_front_' . $data->ID; ?>[]" value='<?php echo esc_attr($answer); ?>' <?php echo $enabled; ?> <?php echo (isset($student_checked_answers) && in_array($answer, (is_array($student_checked_answers) ? $student_checked_answers : array())) ? 'checked' : ''); ?> /><?php echo $answer; ?>
                        </li>
                        <?php
                    }
                }
                ?>
            </ul>

            <?php
            /* $unit_module_main = new Unit_Module();

              if (is_object($response) && !empty($response)) {

              $comment = $unit_module_main->get_response_comment($response->ID);
              if (!empty($comment)) {
              ?>
              <div class="response_comment_front"><?php echo $comment; ?></div>
              <?php
              }
              } */
            ?>
            <?php if ($data->mandatory_answer == 'yes') { ?>
                <span class="mandatory_answer"><?php _e('* Mandatory', 'cp'); ?></span>
            <?php } ?>
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
                <!--<input type="hidden" name="<?php echo $this->name; ?>_checked_index[]" class='checked_index' value="0" />-->

                <input type="hidden" name="<?php echo $this->name; ?>_module_order[]" class="module_order" value="<?php echo (isset($data->module_order) ? $data->module_order : 999); ?>" />
                <input type="hidden" name="module_type[]" value="<?php echo $this->name; ?>" />
                <input type="hidden" name="<?php echo $this->name; ?>_id[]" value="<?php echo (isset($data->ID) ? $data->ID : ''); ?>" />

                <label class="bold-label"><?php _e('Title', 'cp'); ?></label>
                <input type="text" class="element_title" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr(isset($data->post_title) ? $data->post_title : ''); ?>" />

                <div class="group-check">
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

                    <label class="mandatory_answer"><?php _e('Mandatory Answer', 'cp'); ?>
                        <input type="checkbox" name="<?php echo $this->name; ?>_mandatory_answer[]" value="yes" <?php echo (isset($data->mandatory_answer) && $data->mandatory_answer == 'yes' ? 'checked' : (!isset($data->mandatory_answer)) ? 'checked' : '') ?> />
                        <a class="help-icon" href="javascript:;"></a>
                        <div class="tooltip">
                            <div class="tooltip-before"></div>
                            <div class="tooltip-button">&times;</div>
                            <div class="tooltip-content">
                                <?php _e('Student will need to provide a response on this question in order to continue the unit.', 'cp'); ?>
                            </div>
                        </div>
                    </label>

                    <label class="mandatory_answer"><?php _e('Assessable', 'cp'); ?>
                        <input type="checkbox" name="<?php echo $this->name; ?>_gradable_answer[]" value="yes" <?php echo (isset($data->gradable_answer) && $data->gradable_answer == 'yes' ? 'checked' : (!isset($data->gradable_answer)) ? 'checked' : '') ?> />
                        <a class="help-icon" href="javascript:;"></a>
                        <div class="tooltip">
                            <div class="tooltip-before"></div>
                            <div class="tooltip-button">&times;</div>
                            <div class="tooltip-content">
                                <?php _e('If checked, this question will be graded. If not checked, the response can still be viewed within the Assessment section but listed as Non-assessable.', 'cp'); ?>
                            </div>
                        </div>
                    </label>
                </div>

                <label class="bold-label"><?php _e('Question', 'cp'); ?></label>

                <div class="editor_in_place">

                    <?php
                    $args = array(
                        "textarea_name" => $this->name . "_content[]",
                        "textarea_rows" => 5,
                        "quicktags" => false,
                        "teeny" => true,
                    );

                    $editor_id = (esc_attr(isset($data->ID) ? 'editor_' . $data->ID : rand(1, 9999)));
                    wp_editor(htmlspecialchars_decode((isset($data->post_content) ? $data->post_content : '')), $editor_id, $args);
                    ?>
                </div>

                <div class="checkbox-editor">
                    <table class="form-table">
                        <tbody class="ci_items">
                            <tr>

                                <th width="90%">
                        <div class="checkbox_answer_check"><?php _e('Answers'); ?></div>
                        <div class="checkbox_answer"></div>
                        </th>

                        <th width="10%">
                            <!--<a class="checkbox_new_link"><?php _e('Add New', 'cp'); ?></a>-->
                        </th>

                        </tr>

                        <tr>
                            <td class="label" colspan="2"><?php _e('Set the correct answer', 'cp'); ?></td>
                        </tr>

                        <?php
                        $i = 1;
                        ?>

                        <?php
                        if (isset($data->ID)) {

                            $answer_cnt = 0;

                            if (isset($data->answers)) {
                                foreach ($data->answers as $answer) {
                                    ?>
                                    <tr>
                                        <td width="90%">
                                            <input class="checkbox_answer_check" type="checkbox" name="<?php echo $this->name . '_checkbox_check[' . (isset($data->module_order) ? $data->module_order : 999) . '][]'; ?>" value='<?php echo esc_attr((isset($answer) ? $answer : '')); ?>' <?php
                                            if (is_array($data->checked_answers) && in_array($answer, $data->checked_answers)) {
                                                echo 'checked';
                                            }
                                            ?> />
                                            <input class="checkbox_answer" type="text" name="<?php echo $this->name . '_checkbox_answers[' . (isset($data->module_order) ? $data->module_order : 999) . '][]'; ?>" value='<?php echo esc_attr((isset($answer) ? $answer : '')); ?>' />
                                        </td>
                                        <?php if ($answer_cnt >= 2) { ?>
                                            <td width="10%">    
                                                <a class="checkbox_remove" onclick="jQuery(this).parent().parent().remove();">Remove</a>
                                            </td>
                                        <?php } else { ?>
                                            <td width="10%">&nbsp;</td>
                                        <?php } ?>
                                    </tr>
                                    <?php
                                    $answer_cnt++;
                                }
                            }
                        } else {
                            ?>
                            <tr>
                                <td width="90%">
                                    <input class="checkbox_answer_check" type="checkbox" name="<?php echo $this->name . '_checkbox_check[' . (isset($data->module_order) ? $data->module_order : 999) . '][]'; ?>" checked />
                                    <input class="checkbox_answer" type="text" name="<?php echo $this->name . '_checkbox_answers[' . (isset($data->module_order) ? $data->module_order : 999) . '][]'; ?>" />
                                </td>
                                <td width="10%">&nbsp;</td>  
                            </tr>

                            <tr>
                                <td width="90%">
                                    <input class="checkbox_answer_check" type="checkbox" name="<?php echo $this->name . '_checkbox_check[' . (isset($data->module_order) ? $data->module_order : 999) . '][]'; ?>" />
                                    <input class="checkbox_answer" type="text" name="<?php echo $this->name . '_checkbox_answers[' . (isset($data->module_order) ? $data->module_order : 999) . '][]'; ?>" />
                                </td>
                                <td width="10%">&nbsp;</td>  
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>

                    <a class="checkbox_new_link button-secondary">Add New</a>

                </div>

            </div>


        </div>

        <?php
    }

    function on_create() {
        $this->description = __('Multiple choice question where multiple options can be selected', 'cp');
        $this->save_module_data();
        parent::additional_module_actions();
    }

    function save_module_data() {
        global $wpdb, $last_inserted_unit_id;

        if (isset($_POST['module_type'])) {

            $answers = array();
            $checked_answers = array();

            if (isset($_POST[$this->name . '_checkbox_answers'])) {

                foreach ($_POST[$this->name . '_checkbox_answers'] as $post_answers) {
                    $answers[] = $post_answers;
                }

                foreach ($_POST[$this->name . '_checkbox_check'] as $post_checked_answers) {
                    $checked_answers[] = $post_checked_answers;
                }



                //cp_write_log($checked_answers);

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
                            $data->unit_id = ((isset($_POST['unit_id']) and (isset($_POST['unit']) && $_POST['unit'] != '')) ? $_POST['unit_id'] : $last_inserted_unit_id);
                            $data->title = $_POST[$this->name . '_title'][$key];
                            $data->content = $_POST[$this->name . '_content'][$key];
                            $data->metas['module_order'] = $_POST[$this->name . '_module_order'][$key];

                            if (isset($_POST[$this->name . '_show_title_on_front'][$key])) {
                                $data->metas['show_title_on_front'] = $_POST[$this->name . '_show_title_on_front'][$key];
                            } else {
                                $data->metas['show_title_on_front'] = 'no';
                            }

                            if (isset($_POST[$this->name . '_mandatory_answer'][$key])) {
                                $data->metas['mandatory_answer'] = $_POST[$this->name . '_mandatory_answer'][$key];
                            } else {
                                $data->metas['mandatory_answer'] = 'no';
                            }

                            if (isset($_POST[$this->name . '_gradable_answer'][$key])) {
                                $data->metas['gradable_answer'] = $_POST[$this->name . '_gradable_answer'][$key];
                            } else {
                                $data->metas['gradable_answer'] = 'no';
                            }

                            $data->metas['answers'] = $answers[$key];
                            $data->metas['checked_answers'] = $checked_answers[$key];

                            parent::update_module($data);
                        }
                    }
                }
            }
        }

        if (isset($_POST['submit_modules_data_save']) || isset($_POST['submit_modules_data_done'])) {

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
                        $data->content = '';
                        $data->metas['student_checked_answers'] = $response_value;

                        /* CHECK AND SET THE GRADE AUTOMATICALLY */

                        $chosen_answers = array();

                        foreach ($response_value as $post_response_val) {
                            $chosen_answers[] = $post_response_val;
                        }


                        if (count($chosen_answers) !== 0) {
                            $right_answers = get_post_meta($response_id, 'checked_answers', true);
                            $response_grade = 0;

                            foreach ($chosen_answers as $chosen_answer) {
                                if (in_array($chosen_answer, $right_answers)) {
                                    $response_grade = $response_grade + 100;
                                } else {
                                    //$response_grade = $response_grade + 0;//this line can be empty as well :)
                                }
                            }

                            if (count($chosen_answers) >= count($right_answers)) {
                                $grade_cnt = count($chosen_answers);
                            } else {
                                $grade_cnt = count($right_answers);
                            }

                            $response_grade = round(($response_grade / $grade_cnt), 0);
                            $data->auto_grade = $response_grade;
                        }

                        parent::update_module_response($data);
                    }
                }
            }
        }
    }

}

coursepress_register_module('checkbox_input_module', 'checkbox_input_module', 'input');
?>