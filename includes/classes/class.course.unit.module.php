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
                //echo 'post ID (update): ' . $post['ID'];
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

        function update_module_response($data) {
            global $user_id, $wpdb;

            $post = array(
                'post_author' => $user_id,
                'post_parent' => $data->response_id,
                'post_excerpt' => (isset($data->excerpt) ? $data->excerpt : ''),
                'post_content' => (isset($data->content) ? $data->content : ''),
                'post_status' => 'publish',
                'post_title' => (isset($data->title) ? $data->title : ''),
                'post_type' => (isset($data->post_type) ? $data->post_type : 'module_reponse'),
            );

            if (isset($data->ID) && $data->ID != '' && $data->ID != 0) {
                $post['ID'] = $data->ID; //If ID is set, wp_insert_post will do the UPDATE instead of insert
            }

            //Check if response already exists (from the user. Only one response is allowed per persponse request / module per user)
            $already_respond_posts_args = array(
                'posts_per_page' => 1,
                'meta_key' => 'user_ID',
                'meta_value' => get_current_user_id(),
                'post_type' => (isset($data->post_type) ? $data->post_type : 'module_reponse'),
                'post_parent' => $data->response_id,
                'post_status' => 'publish');

            $already_respond_posts = get_posts($already_respond_posts_args);

            if (count($already_respond_posts) == 0) {

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
            }else{
                return false;
            }
        }

        function get_module($module_id){
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
                $module = new $class_name();
                $module->admin_main($mod);
            }
        }

        function get_modules_front($unit_id = 0) {
            global $coursepress_modules;
            $front_save = false;

            $modules = $this->get_modules($unit_id);
            ?>
            <form name="modules_form" enctype="multipart/form-data" method="post">
                <?php
                foreach ($modules as $mod) {
                    $class_name = $mod->module_type;
                    $module = new $class_name();
                    $module->front_main($mod);
                    if ($module->front_save) {
                        $front_save = true;
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

        function get_response_form(){
            //module does not overwrite this method message?
        }
        
        function get_response(){
            
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