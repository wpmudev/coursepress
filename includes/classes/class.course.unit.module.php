<?php
if (!class_exists('Unit_Module')) {

    class Unit_Module {

        var $data;
        var $name = 'none';
        var $label = 'None Set';
        var $description = '';
        var $front_save = false;
        var $response_type = '';

        function __construct() {
            $this->on_create();
            $this->check_for_modules_to_delete();
        }

        function Unit_Module() {
            $this->__construct();
        }

        function admin_sidebar($data) {
            ?>
            <li class='draggable-module' id='<?php echo $this->name; ?>' <?php if ($data === true) echo "style='display:none;'"; ?>>
                <div class='action action-draggable'>
                    <div class='action-top closed'>
                        <a href="#available-actions" class="action-button hide-if-no-js"></a>
                        <?php _e($this->label, 'cp'); ?>
                    </div>
                    <div class='action-body closed'>
                        <?php if (!empty($this->description)) { ?>
                            <p>
                                <?php _e($this->description, 'cp'); ?>
                            </p>
                        <?php } ?>

                    </div>
                </div>
            </li>
            <?php
        }

        function update_module($data) {
            global $user_id, $wpdb;

            $post = array(
                'post_author' => $user_id,
                'post_parent' => $data->unit_id,
                'post_excerpt' => (isset($data->excerpt) ? $data->excerpt : ''),
                'post_content' => (isset($data->content) ? $data->content : ''),
                'post_status' => 'publish',
                'post_title' => (isset($data->title) ? $data->title : ''),
                'post_type' => (isset($data->post_type) ? $data->post_type : 'module'),
            );

            if (isset($data->ID) && $data->ID != '' && $data->ID != 0) {
                $post['ID'] = $data->ID; //If ID is set, wp_insert_post will do the UPDATE instead of insert
            }

            $post_id = wp_insert_post($post);

            //Update post meta
            if ($post_id != 0) {
                if (isset($data->metas)) {
                    foreach ($data->metas as $key => $value) {
                        update_post_meta($post_id, $key, $value);
                    }
                }
            }

            return $post_id;
        }

        function check_for_modules_to_delete() {

            if (is_admin()) {
                if (isset($_POST['modules_to_execute'])) {
                    $modules_to_delete = $_POST['modules_to_execute'];
                    foreach ($modules_to_delete as $module_to_delete) {
                        //echo 'Module to delete:' . $module_to_delete . '<br />';
                        wp_delete_post($module_to_delete, true);
                    }
                }
            }
        }

        function did_student_responed($unit_module_id, $student_id) {
            //Check if response already exists (from the user. Only one response is allowed per persponse request / module per user)
            $already_respond_posts_args = array(
                'posts_per_page' => 1,
                'meta_key' => 'user_ID',
                'meta_value' => $student_id,
                'post_type' => 'module_response',
                'post_parent' => $unit_module_id,
                'post_status' => 'publish');

            $already_respond_posts = get_posts($already_respond_posts_args);

            if (count($already_respond_posts) > 0) {
                return true;
            } else {
                return false;
            }
        }

        function update_module_response($data) {
            global $user_id, $wpdb, $coursepress;

            $unit_id = get_post_ancestors($data->response_id);
            $course_id = get_post_meta($unit_id[0], 'course_id', true);

            $post = array(
                'post_author' => $user_id,
                'post_parent' => $data->response_id,
                'post_excerpt' => (isset($data->excerpt) ? $data->excerpt : ''),
                'post_content' => (isset($data->content) ? $data->content : ''),
                'post_status' => 'publish',
                'post_title' => (isset($data->title) ? $data->title : ''),
                'post_type' => (isset($data->post_type) ? $data->post_type : 'module_response'),
            );

            if (isset($data->ID) && $data->ID != '' && $data->ID != 0) {
                $post['ID'] = $data->ID; //If ID is set, wp_insert_post will do the UPDATE instead of insert
            }

            //Check if response already exists (from the user. Only one response is allowed per persponse request / module per user)
            $already_respond_posts_args = array(
                'posts_per_page' => 1,
                'meta_key' => 'user_ID',
                'meta_value' => get_current_user_id(),
                'post_type' => (isset($data->post_type) ? $data->post_type : 'module_response'),
                'post_parent' => $data->response_id,
                'post_status' => 'publish');

            $already_respond_posts = get_posts($already_respond_posts_args);

            if (count($already_respond_posts) == 0) {

                $post_id = wp_insert_post($post);

                //Update post meta
                $data->metas['course_id'] = $course_id;

                if ($post_id != 0) {
                    if (isset($data->metas)) {
                        foreach ($data->metas as $key => $value) {
                            update_post_meta($post_id, $key, $value);
                        }
                    }
                }

                //SET AUTO GRADE IF REQUESTED BY A MODULE
                if (isset($data->auto_grade) && is_numeric($data->auto_grade)) {
                    $this->save_response_grade($post_id, $data->auto_grade);
                }

                //$coursepress->set_latest_activity(get_current_user_id());
                return $post_id;
            } else {
                return false;
            }
        }

        function get_module($module_id) {
            $module = get_post($module_id);
            return $module;
        }

        function get_modules($unit_id) {

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

        function get_modules_admin_forms($unit_id = 0) {
            global $coursepress_modules;

            $modules = $this->get_modules($unit_id);

            foreach ($modules as $mod) {
                $class_name = $mod->module_type;
                if (class_exists($class_name)) {
                    $module = new $class_name();
                    $module->admin_main($mod);
                }
            }
        }

        function get_modules_front($unit_id = 0) {
            global $coursepress_modules;
            $front_save = false;

            $modules = $this->get_modules($unit_id);
            ?>
            <form name="modules_form" id="modules_form" enctype="multipart/form-data" method="post">
                <?php
                foreach ($modules as $mod) {
                    $class_name = $mod->module_type;
                    if (class_exists($class_name)) {
                        $module = new $class_name();
                        $module->front_main($mod);
                        if ($module->front_save) {
                            $front_save = true;
                        }
                    }
                }

                wp_nonce_field('modules_nonce');

                if ($front_save) {
                    ?>
                    <input type="hidden" name="unit_id" value="<?php echo $unit_id; ?>" />
                    <input type="submit" class="apply-button-enrolled" name="submit_modules_data" value="<?php _e('Submit', 'cp'); ?>">
                    <?php
                }
                ?>
            </form>
            <?php
        }

        function get_module_response_comment_form($post_id) {
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

        function additional_module_actions() {
            $this->save_response_comment();
            $this->save_response_grade();
        }

        function save_response_comment() {
            if (isset($_POST['response_id']) && isset($_POST['response_comment']) && is_admin()) {
                update_post_meta($_POST['response_id'], 'response_comment', $_POST['response_comment']);
            }
        }

        function save_response_grade($response_id = '', $response_grade = '') {
            if ((isset($_POST['response_id']) || $response_id !== '') && (isset($_POST['response_grade']) || $response_grade !== '')) {

                $grade_data = array(
                    'grade' => ($response_grade !== '' && is_numeric($response_grade) ? $response_grade : $_POST['response_grade']),
                    'instructor' => get_current_user_ID(),
                    'time' => current_time('timestamp')
                );

                update_post_meta(($response_id !== '' && is_numeric($response_id) ? $response_id : $_POST['response_id']), 'response_grade', $grade_data);

                return true;
            } else {
                return false;
            }
        }

        function get_response_grade($response_id, $data = '') {
            $grade_data = get_post_meta($response_id, 'response_grade');

            if ($grade_data) {
                if ($data !== '') {
                    return $grade_data[0][$data];
                } else {
                    return $grade_data[0];
                }
            } else {
                
            }
        }

        function get_ungraded_response_count($course_id = '') {

            if ($course_id == '') {

                $args = array(
                    'post_type' => array('module_response', 'attachment'),
                    'post_status' => array('publish', 'inherit'),
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

                return count($ungraded_responses);
            } else {

                $args = array(
                    'post_type' => array('module_response', 'attachment'),
                    'post_status' => array('publish', 'inherit'),
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

                return count($ungraded_responses);
            }
        }

        function get_module_delete_link($module_id) {
            ?>
            <a class="delete_module_link" onclick="if (deleteModule(<?php echo $module_id; ?>)) {
                                    jQuery(this).parent().parent().remove();
                                    update_sortable_module_indexes();
                                }
                                ;"><?php _e('Delete'); ?></a>
               <?php
           }

           function get_module_remove_link() {
               ?>
            <a class="remove_module_link" onclick="if (removeModule()) {
                                    jQuery(this).parent().parent().remove();
                                    update_sortable_module_indexes();
                                }"><?php _e('Remove') ?></a>
            <?php
        }

        function get_response_comment($response_id, $count = false) {
            return get_post_meta($response_id, 'response_comment', true);
        }

        function get_response_form() {
            //module does not overwrite this method message?
        }

        function get_response() {
            
        }

        function on_create() {

        }

        function save_module_data() {
            
        }

        function admin_main() {
            
        }

    }

}
?>