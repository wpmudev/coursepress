<?php
if (!class_exists('Front_Page_Module')) {

    class Front_Page_Module {

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

        function Front_Page_Module() {
            $this->__construct();
        }

        function update_module($data) {
            global $user_id, $wpdb;

            $post = array(
                'post_author' => $user_id,
                'post_excerpt' => ( isset($data->excerpt) ? $data->excerpt : '' ),
                'post_content' => ( isset($data->content) ? $data->content : '' ),
                'post_status' => 'publish',
                'post_title' => ( isset($data->title) ? $data->title : '' ),
                'post_type' => ( isset($data->post_type) ? $data->post_type : 'front_page_module' ),
            );

            if (isset($data->ID) && $data->ID != '' && $data->ID != 0) {
                $post['ID'] = $data->ID; //If ID is set, wp_insert_post will do the UPDATE instead of insert
                //$update = true;
            } else {
                //$update = false;
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

        function delete_module($id, $force_delete = true) {
            global $wpdb;
            wp_delete_post($id, $force_delete); //Whether to bypass trash and force deletion
            //Delete unit module responses

            wp_delete_post($units_module_response->ID, true);
        }

        function check_for_modules_to_delete() {

            if (is_admin()) {
                if (isset($_POST['modules_to_execute'])) {
                    $modules_to_delete = $_POST['modules_to_execute'];
                    foreach ($modules_to_delete as $module_to_delete) {
                        $this->delete_module($module_to_delete, true);
                    }
                }
            }
        }

        function get_module_width_controls($module_id = false) {
            ?>
            <a class="column-icon column-one-icon" alt="<?php _e('Full Width', 'cp');?>" title="<?php _e('Full Width', 'cp');?>"></a>
            <a class="column-icon column-two-icon" alt="<?php _e('One Half', 'cp');?>" title="<?php _e('One Half', 'cp');?>"></a>
            <a class="column-icon column-tree-icon" alt="<?php _e('One Third', 'cp');?>" title="<?php _e('One Third', 'cp');?>"></a>
            <a class="column-icon column-two-thirds-icon" alt="<?php _e('Two Thirds', 'cp');?>" title="<?php _e('Two Thirds', 'cp');?>"></a>
            <?php
        }

        function get_module($module_id) {
            $module = get_post($module_id);
            return $module;
        }

        function order_modules($modules) {
            $ordered_modules = array();

            foreach ($modules as $module) {
                $order = get_post_meta($module->ID, 'module_order', true);
                $ordered_modules[$order] = $module;
            }

            return $ordered_modules;
        }

        function get_modules() {

            $args = array(
                'post_type' => 'front_page_module',
                'post_status' => 'any',
                'posts_per_page' => -1,
                'meta_key' => 'module_order',
                'orderby' => 'meta_value_num',
                'order' => 'ASC',
            );

            $modules = get_posts($args);

            return $modules;
        }

        function get_modules_admin_forms() {
            global $coursepress_modules;

            $modules = $this->get_modules();

            foreach ($modules as $mod) {
                $class_name = $mod->module_type;
                if (class_exists($class_name)) {
                    $module = new $class_name();
                    $module->admin_main($mod);
                }
            }
        }

        function get_modules_front() {

            global $coursepress, $coursepress_modules, $wp, $paged, $_POST;

            $front_save = false;
            $responses = 0;
            $input_modules = 0;

            $paged = isset($wp->query_vars['paged']) ? absint($wp->query_vars['paged']) : 1;

            $modules = $this->get_modules();

            $course_id = do_shortcode('[get_parent_course_id]');

            //$unit_module_page_number = isset( $_GET['to_elements_page'] ) ? $_GET['to_elements_page'] : 1;

            if (isset($_POST['submit_modules_data_done']) || isset($_POST['submit_modules_data_no_save_done'])) {

                if (isset($_POST['submit_modules_data_done'])) {
                    //wp_redirect( full_url( $_SERVER ). '?saved=ok' );
                    wp_redirect(get_permalink($course_id) . trailingslashit($coursepress->get_units_slug()) . '?saved=ok');
                } else {
                    //wp_redirect( full_url( $_SERVER ) );
                    wp_redirect(get_permalink($course_id) . trailingslashit($coursepress->get_units_slug()));
                }

                exit;
            }

            if (isset($_POST['submit_modules_data_save']) || isset($_POST['submit_modules_data_no_save_save'])) {
                if (isset($_POST['submit_modules_data_save'])) {
                    //wp_redirect( $_SERVER['REQUEST_URI'] . '?saved=ok' );
                    wp_redirect(full_url($_SERVER) . '?saved=ok');
                    //exit;
                } else {
                    //wp_redirect( get_permalink( $unit_id ) . trailingslashit( 'page' ) . trailingslashit( $unit_module_page_number ) );
                }
            }
            ?>
            <form name="modules_form" id="modules_form" enctype="multipart/form-data" method="post" action="<?php echo trailingslashit(get_permalink($unit_id)); //strtok( $_SERVER["REQUEST_URI"], '?' );              ?>" onSubmit="return check_for_mandatory_answers();"><!--#submit_bottom-->
                <input type="hidden" id="go_to_page" value="" />
                <?php
                $pages_num = 1;

                foreach ($modules as $mod) {
                    $class_name = $mod->module_type;

                    if (class_exists($class_name)) {
                        $module = new $class_name();

                        if ($module->name == 'page_break_module') {
                            $pages_num++;
                        } else {
                            if ($pages_num == $paged) {

                                $module->front_main($mod);

                                if ($module->front_save) {
                                    $front_save = true;

                                    if (method_exists($module, 'get_response')) {
                                        $response = $module->get_response(get_current_user_id(), $mod->ID);

                                        if (count($response) > 0) {
                                            $responses++;
                                        }
                                        $input_modules++;
                                    }
                                }
                            }
                        }
                    }
                }

                wp_nonce_field('modules_nonce');
                ?>
            </form>
            <?php
            //coursepress_unit_module_pagination($unit_id, $pages_num);
        }

        function get_module_type($post_id) {
            return get_post_meta($post_id, 'module_type', true);
        }

        function get_module_move_link() {
            ?>
            <span class="module_move"><i class="fa fa-th cp-move-icon"></i></span>
            <?php
        }

        function get_module_delete_link($module_id) {
            ?>
            <a class="delete_module_link" onclick="if (deleteModule(<?php echo $module_id; ?>)) {
                        //alert(jQuery(this).parent().parent().parent().attr('class'));
                        //alert(jQuery(this).parent().parent().attr('class'));

                        jQuery(this).parent().parent().remove();
                        update_sortable_module_indexes();
                        /* jQuery(this).parent().parent().remove();*/


                    }
                    ;"><i class="fa fa-trash-o"></i> <?php _e('Delete'); ?></a>
               <?php
           }

           function get_module_remove_link() {
               ?>
            <a class="remove_module_link" onclick="if (removeModule()) {

                        jQuery(this).parent().parent().remove();
                        update_sortable_module_indexes();
                        /* jQuery(this).parent().parent().remove();*/

                    }"><i class="fa fa-trash-o"></i> <?php _e('Remove') ?></a>
            <?php
        }

        function display_title_on_front($data) {
            $to_display = isset($data->show_title_on_front) && $data->show_title_on_front == 'yes' ? true : (!isset($data->show_title_on_front) ) ? true : false;
            return $to_display;
        }

        function on_create() {
            
        }

        function save_module_data() {
            
        }

        function admin_main($data) {
            
        }

    }

}
?>