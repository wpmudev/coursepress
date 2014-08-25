<?php
if ( !class_exists('Unit_Module') ) {

    class Unit_Module {

        var $data;
        var $name = 'none';
        var $label = 'None Set';
        var $description = '';
        var $front_save = false;
        var $response_type = '';
        var $details;
        var $parent_unit = '';

        function __construct() {
            $this->on_create();
            $this->check_for_modules_to_delete();
        }

        function Unit_Module() {
            $this->__construct();
        }

        function admin_sidebar( $data ) {
            ?>
            <li class='draggable-module' id='<?php echo $this->name; ?>' <?php if ( $data === true ) echo "style='display:none;'"; ?>>
                <div class='action action-draggable'>
                    <div class='action-top closed'>
                        <a href="#available-actions" class="action-button hide-if-no-js"></a>
                        <?php _e($this->label, 'cp'); ?>
                    </div>
                    <div class='action-body closed'>
                        <?php if ( !empty($this->description) ) { ?>
                            <p>
                                <?php _e($this->description, 'cp'); ?>
                            </p>
                        <?php } ?>

                    </div>
                </div>
            </li>
            <?php
        }

        function update_module( $data ) {
            global $user_id, $wpdb; //$last_inserted_module_id

            $post = array(
                'post_author' => $user_id,
                'post_parent' => $data->unit_id,
                'post_excerpt' => ( isset($data->excerpt) ? $data->excerpt : '' ),
                'post_content' => ( isset($data->content) ? $data->content : '' ),
                'post_status' => 'publish',
                'post_title' => ( isset($data->title) ? $data->title : '' ),
                'post_type' => ( isset($data->post_type) ? $data->post_type : 'module' ),
            );

            if ( isset($data->ID) && $data->ID != '' && $data->ID != 0 ) {
                $post['ID'] = $data->ID; //If ID is set, wp_insert_post will do the UPDATE instead of insert
                //$update = true;
            } else {
                //$update = false;
            }

            //require( ABSPATH . WPINC . '/pluggable.php' );
            $post_id = wp_insert_post($post);

            //Update post meta
            if ( $post_id != 0 ) {
                /* if( !$update ) {
                  $last_inserted_module_id = $post_id;
                  } */
                if ( isset($data->metas) ) {
                    foreach ( $data->metas as $key => $value ) {
                        update_post_meta($post_id, $key, $value);
                    }
                }
            }

            return $post_id;
        }

        function delete_module( $id, $force_delete = true ) {
            global $wpdb;
            wp_delete_post($id, $force_delete); //Whether to bypass trash and force deletion
            //Delete unit module responses

            $args = array(
                'posts_per_page' => -1,
                'post_parent' => $id,
                'post_type' => array( 'module_response', 'attachment' ),
                'post_status' => 'any',
            );

            $units_module_responses = get_posts($args);

            foreach ( $units_module_responses as $units_module_response ) {
                wp_delete_post($units_module_response->ID, true);
            }
        }

        function check_for_modules_to_delete() {

            if ( is_admin() ) {
                if ( isset($_POST['modules_to_execute']) ) {
                    $modules_to_delete = $_POST['modules_to_execute'];
                    foreach ( $modules_to_delete as $module_to_delete ) {
                        //echo 'Module to delete:' . $module_to_delete . '<br />';
                        $this->delete_module($module_to_delete, true);
                        //wp_delete_post( $module_to_delete, true );
                    }
                }
            }
        }

        function did_student_responed( $unit_module_id, $student_id ) {
            //Check if response already exists ( from the user. Only one response is allowed per persponse request / module per user )
            $already_respond_posts_args = array(
                'posts_per_page' => 1,
                'meta_key' => 'user_ID',
                'meta_value' => $student_id,
                'post_type' => array( 'module_response', 'attachment' ),
                'post_parent' => $unit_module_id,
                'post_status' => array( 'publish', 'inherit' )
            );

            $already_respond_posts = get_posts($already_respond_posts_args);

            if ( count($already_respond_posts) > 0 ) {
                return true;
            } else {
                return false;
            }
        }

        function update_module_response( $data ) {
            global $user_id, $wpdb, $coursepress;

            $unit_id = get_post_ancestors($data->response_id);
            $course_id = get_post_meta($unit_id[0], 'course_id', true);

            $post = array(
                'post_author' => $user_id,
                'post_parent' => $data->response_id,
                'post_excerpt' => ( isset($data->excerpt) ? $data->excerpt : '' ),
                'post_content' => ( isset($data->content) ? $data->content : '' ),
                'post_status' => 'publish',
                'post_title' => ( isset($data->title) ? $data->title : '' ),
                'post_type' => ( isset($data->post_type) ? $data->post_type : 'module_response' ),
            );

            if ( isset($data->ID) && $data->ID != '' && $data->ID != 0 ) {
                $post['ID'] = $data->ID; //If ID is set, wp_insert_post will do the UPDATE instead of insert
            }

            //Check if response already exists ( from the user. Only one response is allowed per persponse request / module per user )
            $already_respond_posts_args = array(
                'posts_per_page' => 1,
                'meta_key' => 'user_ID',
                'meta_value' => get_current_user_id(),
                'post_type' => ( isset($data->post_type) ? $data->post_type : 'module_response' ),
                'post_parent' => $data->response_id,
                'post_status' => 'publish' );

            $already_respond_posts = get_posts($already_respond_posts_args);

            if ( count($already_respond_posts) == 0 ) {

                $post_id = wp_insert_post($post);

                //Update post meta
                $data->metas['course_id'] = $course_id;

                if ( $post_id != 0 ) {
                    if ( isset($data->metas) ) {
                        foreach ( $data->metas as $key => $value ) {
                            update_post_meta($post_id, $key, $value);
                        }
                    }
                }

                //SET AUTO GRADE IF REQUESTED BY A MODULE
                if ( isset($data->auto_grade) && is_numeric($data->auto_grade) ) {
                    $this->save_response_grade($post_id, $data->auto_grade);
                }

                //$coursepress->set_latest_activity( get_current_user_id() );
                return $post_id;
            } else {
                return false;
            }
        }

        function get_module( $module_id ) {
            $module = get_post($module_id);
            return $module;
        }

        function get_module_unit_id( $module_id ) {
            $parents = get_post_ancestors($module_id);
            $id = ($parents) ? $parents[0] : $post->ID;
            return $id;
        }

        function order_modules( $modules ) {
            $ordered_modules = array();

            foreach ( $modules as $module ) {
                $order = get_post_meta($module->ID, 'module_order', true);
                $ordered_modules[$order] = $module;
            }

            return $ordered_modules;
        }

        function get_modules( $unit_id ) {

            $args = array(
                'post_type' => 'module',
                'post_status' => 'any',
                'posts_per_page' => -1,
                'post_parent' => $unit_id,
                'meta_key' => 'module_order',
                'orderby' => 'meta_value_num',
                'order' => 'ASC',
            );

            $modules = get_posts($args);

            return $modules;
        }

        function get_modules_admin_forms( $unit_id = 0 ) {
            global $coursepress_modules;

            $modules = $this->get_modules($unit_id);

            foreach ( $modules as $mod ) {
                $class_name = $mod->module_type;
                if ( class_exists($class_name) ) {
                    $module = new $class_name();
                    $module->admin_main($mod);
                }
            }
        }

        function get_modules_front( $unit_id = 0 ) {
            global $coursepress, $coursepress_modules, $wp, $paged, $_POST;

            if ( isset($_GET['resubmit_nonce']) || wp_verify_nonce($_GET['resubmit_nonce'], 'resubmit_answer') ) {
                if ( isset($_GET['resubmit_answer']) ) {
                    $response = get_post(( int ) $_GET['resubmit_answer']);
                    if ( isset($response) && isset($response->post_author) && $response->post_author == get_current_user_ID() ) {
                        $resubmitted_response = array(
                            'ID' => $response->ID,
                            'post_status' => 'private'
                        );
                        wp_update_post($resubmitted_response);
                    }
                    wp_redirect($_GET['resubmit_redirect_to']);
                    exit;
                }
            }


            $front_save = false;
            $responses = 0;
            $input_modules = 0;

            $paged = isset($wp->query_vars['paged']) ? absint($wp->query_vars['paged']) : 1;

            cp_set_last_visited_unit_page($unit_id, $paged, get_current_user_ID());
            cp_set_visited_unit_page($unit_id, $paged, get_current_user_ID());

            $modules = $this->get_modules($unit_id);

            $course_id = do_shortcode('[get_parent_course_id]');

            //$unit_module_page_number = isset( $_GET['to_elements_page'] ) ? $_GET['to_elements_page'] : 1;

            if ( isset($_POST['submit_modules_data_done']) || isset($_POST['submit_modules_data_no_save_done']) ) {
                // if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
                if ( isset($_POST['submit_modules_data_done']) ) {
                    //wp_redirect( full_url( $_SERVER ). '?saved=ok' );
                    wp_redirect(get_permalink($course_id) . trailingslashit($coursepress->get_units_slug()) . '?saved=ok');
                } else {
                    if ( $paged != 1 ) {
                        //wp_redirect( full_url( $_SERVER ) );
                        wp_redirect(get_permalink($course_id) . trailingslashit($coursepress->get_units_slug()));
                    } else {
                        wp_redirect(full_url($_SERVER));
                    }
                }

                exit;
            }

            if ( isset($_POST['submit_modules_data_save']) || isset($_POST['submit_modules_data_no_save_save']) ) {
                // if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
                if ( isset($_POST['submit_modules_data_save']) ) {
                    //wp_redirect( $_SERVER['REQUEST_URI'] . '?saved=ok' );
                    wp_redirect(full_url($_SERVER) . '?saved=ok');
                    //exit;
                } else {
                    //wp_redirect( get_permalink( $unit_id ) . trailingslashit( 'page' ) . trailingslashit( $unit_module_page_number ) );
                }
            }

            if ( isset($_POST['save_student_progress_indication']) ) {
                wp_redirect(get_permalink($course_id) . trailingslashit($coursepress->get_units_slug()) . '?saved=progress_ok');
                exit;
            }
            ?>
            <form name="modules_form" id="modules_form" enctype="multipart/form-data" method="post" action="<?php echo trailingslashit(get_permalink($unit_id)); //strtok( $_SERVER["REQUEST_URI"], '?' );                         ?>" onSubmit="return check_for_mandatory_answers();"><!--#submit_bottom-->
                <input type="hidden" id="go_to_page" value="" />

                <?php
                $pages_num = 1;
                foreach ( $modules as $mod ) {
                    $class_name = $mod->module_type;
                    if ( class_exists($class_name) ) {
                        $module = new $class_name();
                        if ( $module->name == 'page_break_module' ) {
                            $pages_num++;
                        } else {
                            if ( $pages_num == $paged ) {
                                $module->front_main($mod);
                                if ( $module->front_save ) {
                                    $front_save = true;
                                    if ( method_exists($module, 'get_response') ) {
                                        $response = $module->get_response(get_current_user_id(), $mod->ID);
                                        if ( count($response) > 0 ) {
                                            $responses++;
                                        }$input_modules++;
                                    }
                                }
                            }
                        }
                    }
                }
                wp_nonce_field('modules_nonce');
                $is_last_page = coursepress_unit_module_pagination($unit_id, $pages_num, true); //check if current unit page is last page
                if ( !$coursepress->is_preview($unit_id) ) {
                    if ( $front_save ) {
                        if ( $input_modules !== $responses ) {
                            ?>
                            <div class="mandatory_message"><?php _e('All questions marked with "* Mandatory" require your input.', 'cp'); ?></div><div class="clearf"></div>
                            <input type="hidden" name="unit_id" value="<?php echo $unit_id; ?>" />
                            <a id="submit_bottom"></a>
                            <?php
                            if ( isset($_POST['submit_modules_data']) ) {
                                $form_message = __('The module data has been submitted successfully.', 'coursepress');
                            }
                            if ( isset($form_message) ) {
                                ?><p class="form-info-regular"><?php echo $form_message; ?></p>
                            <?php } ?><input type="submit" class="apply-button-enrolled submit-elements-data-button" name="submit_modules_data_<?php echo ( $is_last_page ? 'done' : 'save' ); ?>" value="<?php echo ( $is_last_page ? __('Done', 'cp') : __('Next', 'cp') ); ?>">

                            <?php
                        } else {
                            ?><input type="submit" class = "apply-button-enrolled submit-elements-data-button" name = "submit_modules_data_no_save_<?php echo ( $is_last_page ? 'done' : 'save' ); ?>" value = "<?php echo ( $is_last_page ? __('Done', 'cp') : __('Next', 'cp') ); ?>">
                            <?php
                        }
                    } else {
                        ?><input type="submit" class="apply-button-enrolled submit-elements-data-button" name="submit_modules_data_no_save_<?php echo ( $is_last_page ? 'done' : 'save' ); ?>" value="<?php echo ( $is_last_page ? __('Done', 'cp') : __('Next', 'cp') ); ?>">
                        <?php
                    }
                }
                ?>
                <?php
                coursepress_unit_module_pagination($unit_id, $pages_num);
                ?>
                <div class="fullbox"></div>
                <a href="" id="save_student_progress" class="save_progress"><?php _e('Save Progress & Exit', 'tc'); ?></a>
            </form>

            <?php
        }

        function get_module_response_comment_form( $post_id ) {
            $post = get_post($post_id);
            $settings = array(
                'media_buttons' => false,
                'textarea_rows' => 2,
                'editor_class' => 'response_comment'
            );
            ?>
            <label><?php _e('Comment', 'cp'); ?></label>
            <?php
            return wp_editor($post->response_comment, 'response_comment', $settings);
        }

        function get_module_type( $post_id ) {
            return get_post_meta($post_id, 'module_type', true);
        }

        function additional_module_actions() {
            $this->save_response_comment();
            $this->save_response_grade();
        }

        function save_response_comment() {
            if ( isset($_POST['response_id']) && isset($_POST['response_comment']) && is_admin() ) {
                update_post_meta($_POST['response_id'], 'response_comment', $_POST['response_comment']);
            }
        }

        function save_response_grade( $response_id = '', $response_grade = '' ) {
            if ( ( isset($_POST['response_id']) || $response_id !== '' ) && ( isset($_POST['response_grade']) || $response_grade !== '' ) ) {

                $grade_data = array(
                    'grade' => ( $response_grade !== '' && is_numeric($response_grade) ? $response_grade : $_POST['response_grade'] ),
                    'instructor' => get_current_user_ID(),
                    'time' => current_time('timestamp')
                );

                update_post_meta(( $response_id !== '' && is_numeric($response_id) ? $response_id : $_POST['response_id']), 'response_grade', $grade_data);

                return true;
            } else {
                return false;
            }
        }

        function get_response_grade( $response_id, $data = '' ) {
            $grade_data = get_post_meta($response_id, 'response_grade');

            if ( $grade_data ) {
                if ( $data !== '' ) {
                    return $grade_data[0][$data];
                } else {
                    return $grade_data[0];
                }
            } else {
                
            }
        }

        function get_ungraded_response_count( $course_id = '' ) {

            if ( $course_id == '' ) {

                $args = array(
                    'post_type' => array( 'module_response', 'attachment' ),
                    'post_status' => array( 'publish', 'inherit' ),
                    'posts_per_page' => -1,
                    'meta_key' => 'course_id',
                    'meta_value' => $course_id,
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'response_grade',
                            'compare' => 'NOT EXISTS',
                            'value' => ''
                        )
                    )
                );

                $ungraded_responses = get_posts($args);

                $array_order_num = 0;

                //Count only ungraded responses from STUDENTS!
                foreach ( $ungraded_responses as $ungraded_response ) {

                    if ( get_post_meta($ungraded_response->post_parent, 'gradable_answer', true) == 'no' ) {
                        unset($ungraded_responses[$array_order_num]);
                    }

                    if ( get_user_meta($ungraded_response->post_author, 'role', true) !== 'student' ) {
                        unset($ungraded_responses[$array_order_num]);
                    }
                    $array_order_num++;
                }

                /* $admins_responses = 0;

                  foreach ( $ungraded_responses as $ungraded_responses ) {
                  if( user_can( $ungraded_responses->post_author, 'administrator' ) ) {
                  $admins_responses++;
                  }
                  } */

                return count($ungraded_responses); // - $admins_responses;
            } else {

                $args = array(
                    'post_type' => array( 'module_response', 'attachment' ),
                    'post_status' => array( 'publish', 'inherit' ),
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'response_grade',
                            'compare' => 'NOT EXISTS',
                            'value' => ''
                        ),
                        array(
                            'key' => 'course_id',
                            'value' => $course_id
                        )
                    )
                );

                $ungraded_responses = get_posts($args);


                $array_order_num = 0;

                //Count only ungraded responses from STUDENTS!
                foreach ( $ungraded_responses as $ungraded_response ) {

                    if ( get_post_meta($ungraded_response->post_parent, 'gradable_answer', true) == 'no' ) {
                        unset($ungraded_responses[$array_order_num]);
                    }

                    if ( get_user_meta($ungraded_response->post_author, 'role', true) !== 'student' ) {
                        unset($ungraded_responses[$array_order_num]);
                    }

                    $array_order_num++;
                }

                return count($ungraded_responses);
            }
        }

        function element_title_description() {
            ?>
            <span class="element_title_description"><?php _e('The title is used to identify this module element and is useful for assessment.', 'cp'); ?></span>
            <?php
        }

        function mandatory_answer_element( $data ) {
            ?>
            <label class="mandatory_answer">
                <input type="checkbox" name="<?php echo $this->name; ?>_mandatory_answer[]" value="yes" <?php echo ( isset($data->mandatory_answer) && $data->mandatory_answer == 'yes' ? 'checked' : (!isset($data->mandatory_answer) ) ? 'checked' : '' ) ?> />
                <input type="hidden" name="<?php echo $this->name; ?>_mandatory_answer_field[]" value="<?php echo ( isset($data->mandatory_answer) && $data->mandatory_answer == 'yes' ? 'yes' : 'no' ) ?>" />
                <?php _e('Mandatory Answer', 'cp'); ?><br />
                <span class="element_title_description"><?php _e('A response is required to continue', 'cp'); ?></span>
            </label>
            <?php
        }

        function assessable_answer_element( $data ) {
            ?>
            <label class="mandatory_answer">
                <input type="checkbox" class="assessable_checkbox" name="<?php echo $this->name; ?>_gradable_answer[]" value="yes" <?php echo ( isset($data->gradable_answer) && $data->gradable_answer == 'yes' ? 'checked' : (!isset($data->gradable_answer) ) ? 'checked' : '' ) ?> />
                <input type="hidden" name="<?php echo $this->name; ?>_gradable_answer_field[]" value="<?php echo ( isset($data->gradable_answer) && $data->gradable_answer == 'yes' ? 'yes' : 'no' ) ?>" />				
                <?php _e('Assessable', 'cp'); ?><br />
                <span class="element_title_description"><?php _e('The answer will be graded', 'cp'); ?></span>
            </label>
            <?php
        }

        function placeholder_element( $data ) {
            ?>
            <div class="placeholder_holder">
                <label><?php _e('Placeholder Text') ?><br />
                    <span class="element_title_description"><?php _e('Additional instructions visible in the input field as a placeholder', 'cp'); ?></span>
                </label>
                <input type="text" class="placeholder_text" name="<?php echo $this->name; ?>_placeholder_text[]" value="<?php echo esc_attr(isset($data->placeholder_text) ? $data->placeholder_text : '' ); ?>" />
            </div>
            <?php
        }

        function show_title_on_front_element( $data ) {
            ?>
            <label class="show_title_on_front">
                <input type="checkbox" name="<?php echo $this->name; ?>_show_title_on_front[]" value="yes" <?php echo ( isset($data->show_title_on_front) && $data->show_title_on_front == 'yes' ? 'checked' : (!isset($data->show_title_on_front) ) ? 'checked' : '' ) ?> />
                <input type="hidden" name="<?php echo $this->name; ?>_show_title_field[]" value="<?php echo ( isset($data->show_title_on_front) && $data->show_title_on_front == 'yes' ? 'yes' : 'no' ) ?>" />
                <?php _e('Show Title', 'cp'); ?><br />
                <span class="element_title_description"><?php _e('The title is displayed as a heading', 'cp'); ?></span>
            </label>
            <?php
        }

        function minimum_grade_element( $data ) {
            ?>
            <label class="minimum_grade_required_label">
                <?php _e('Minimum grade required', 'cp'); ?><input type="text" class="grade_spinner" name="<?php echo $this->name; ?>_minimum_grade_required[]" value="<?php echo ( isset($data->minimum_grade_required) ? $data->minimum_grade_required : 100 ); ?>" /><br />
                <span class="element_title_description"><?php _e('Set the minimum grade (%) required to pass the task', 'cp'); ?></span>
            </label>
            <?php
        }

        function limit_attempts_element( $data ) {
            ?>
            <label class="limit_attampts_label">
                <input type="checkbox" class="limit_attempts_checkbox" name="<?php echo $this->name; ?>_limit_attempts[]" value="yes" <?php echo ( isset($data->limit_attempts) && $data->limit_attempts == 'yes' ? 'checked' : (!isset($data->limit_attempts) ) ? 'checked' : '' ) ?> />
                <input type="hidden" name="<?php echo $this->name; ?>_limit_attempts_field[]" value="<?php echo ( isset($data->limit_attempts) && $data->limit_attempts == 'yes' ? 'yes' : 'no' ) ?>" />								
                <?php _e('Limit Attempts', 'cp'); ?><input type="text" class="attempts_spinner" name="<?php echo $this->name; ?>_limit_attempts_value[]" value="<?php echo ( isset($data->limit_attempts_value) ? $data->limit_attempts_value : 1 ); ?>" /><br>
                <span class="element_title_description"><?php _e('Limit attempts of this task', 'cp'); ?></span>
            </label>
            <?php
        }

        function grade_status_and_resubmit( $data, $grade, $responses, $last_public_response = false ) {
            $number_of_answers = ( int ) count($responses) + ( int ) count($last_public_response);

            $limit_attempts = $data->limit_attempts; //yes or no
            $limit_attempts_value = $data->limit_attempts_value;
            $attempts_remaining = $limit_attempts_value - $number_of_answers;

            if ( isset($limit_attempts) && $limit_attempts == 'yes' ) {
                $limit_attempts_value = $limit_attempts_value;
            } else {
                $limit_attempts_value = -1; //unlimited
            }

            if ( $grade && $data->gradable_answer ) {
                ?>
                <div class="module_grade">
                    <div class="module_grade_left">
                        <?php
                        if ( $grade['grade'] < 100 ) {
                            if ( ($number_of_answers < $limit_attempts_value) || $limit_attempts_value == -1 ) {
                                $response = $this->get_response(get_current_user_id(), $data->ID);
                                $unit_id = wp_get_post_parent_id($data->ID);
                                $paged = isset($wp->query_vars['paged']) ? absint($wp->query_vars['paged']) : 1;
                                $permalink = trailingslashit(trailingslashit(get_permalink($unit_id)) . 'page/' . trailingslashit($paged));
                                $resubmit_url = $permalink . '?resubmit_answer=' . $last_public_response->ID . '&resubmit_redirect_to=' . $permalink;
                                ?>
                                <a href="<?php echo wp_nonce_url($resubmit_url, 'resubmit_answer', 'resubmit_nonce'); ?>" class="resubmit_response"><?php _e('Resubmit', 'cp'); ?></a>
                                <?php
                                if ( $attempts_remaining > 0 ) {
                                    if ( $attempts_remaining == 1 ) {
                                        _e('(1 attempt remaining)', 'cp');
                                    } else {
                                        printf(__('(%d attempts remaining)', 'cp'), $attempts_remaining);
                                    }
                                }
                            }
                        }
                        ?>
                    </div>
                    <div class="module_grade_right">
                        <?php echo __('Graded: ') . $grade['grade'] . '%'; ?> 
                        <?php
                        if ( isset($data->minimum_grade_required) && is_numeric($data->minimum_grade_required) ) {
                            if ( $grade['grade'] >= $data->minimum_grade_required ) {
                                ?>
                                <span class="passed_element">(<?php _e('Passed', 'cp'); ?>)</span>
                                <?php
                            } else {
                                ?>
                                <span class="failed_element">(<?php _e('Failed', 'cp'); ?>)</span>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php
            } else {
                if ( $data->gradable_answer && 'enabled' != $enabled ) {
                    if ( ( int ) count($responses) > 1 ) {
                        ?>
                        <div class="module_grade"><?php echo __('Grade Pending.'); ?></div>
                        <?php
                    }
                }
            }
        }

        function time_estimation( $data ) {
            // var_dump($data->time_estimation);
            ?>
            <div class="module_time_estimation"><?php _e('Time Estimation (mins)', 'cp'); ?> <input type="text" name="<?php echo $this->name; ?>_time_estimation[]" value="<?php echo esc_attr(isset($data->time_estimation) ? $data->time_estimation : '1:00'); ?>" /></div>
            <?php
        }

        function get_module_move_link() {
            ?>
            <span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
            <?php
        }

        function create_auto_draft( $unit_id ) {
            global $user_id;

            $post = array(
                'post_author' => $user_id,
                'post_content' => '',
                'post_status' => 'auto-draft',
                'post_type' => 'module',
                'post_parent' => $unit_id
            );

            $post_id = wp_insert_post($post);

            return $post_id;
        }

        function get_module_delete_link() {
            ?>
            <a class="delete_module_link" onclick="if (deleteModule(jQuery(this).parent().find('.element_id').val())) {
                                    jQuery(this).parent().parent().remove();
                                    update_sortable_module_indexes();
                                }
                                ;"><i class="fa fa-trash-o"></i> <?php _e('Delete'); ?></a>
            <?php
        }

        function display_title_on_front( $data ) {
            $to_display = isset($data->show_title_on_front) && $data->show_title_on_front == 'yes' ? true : (!isset($data->show_title_on_front) ) ? true : false;
            return $to_display;
        }

        function get_response_comment( $response_id, $count = false ) {
            return get_post_meta($response_id, 'response_comment', true);
        }

        function get_response_form( $user_ID, $response_request_ID, $show_label = true ) {
            //module does not overwrite this method message?
        }

        function get_response( $user_ID, $response_request_ID ) {
            
        }

        function on_create() {
            
        }

        function save_module_data() {
            
        }

        function admin_main( $data ) {
            
        }

    }

}
?>