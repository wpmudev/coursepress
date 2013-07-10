<?php
class text_module extends Unit_Module {

    var $name = 'text_module';
    var $label = 'Text';
    var $description = 'Allows usage of the Text module';
    var $rulearea = 'core';

    function admin_main($data) {
        if (!$data)
            $data = array();
        ?>
        <div class='level-operation' id='main-<?php echo $name;?>'>
            <h2 class='sidebar-name'><?php _e('Text Module', 'membership'); ?><span><a href='#remove' id='remove-blogcreation' class='removelink' title='<?php _e("Remove Blog Creation from this rules area.", 'membership'); ?>'><?php _e('Remove', 'membership'); ?></a></span></h2>
            <div class='inner-operation'>
                <?php
                if (!isset($data['number'])) {
                    $data['number'] = '';
                }
                ?>
                <p><strong><?php _e('Positive : ', 'membership'); ?></strong><?php _e('User can create ', 'membership'); ?><input type='text' name='blogcreation[number]' value='<?php echo esc_attr($data['number']); ?>' /><?php _e(' blogs.', 'membership'); ?><br/><em><?php _e('Leave blank for unlimited blogs.', 'membership'); ?></em></p>
                <p><strong><?php _e('Negative : ', 'membership'); ?></strong><?php _e('User is unable to create any blogs.', 'membership'); ?></p>
                <input type='hidden' name='blogcreation[]' value='yes' />
            </div>
        </div>
        <?php
    }

    function on_creation() {
        
    }

    function on_positive($data) {

        $this->data = $data;

        add_filter('site_option_registration', array(&$this, 'pos_blog_creation'));
        add_filter('wpmu_active_signup', array(&$this, 'pos_blog_creation'));
    }

    function on_negative($data) {

        $this->data = $data;

        add_filter('site_option_registration', array(&$this, 'neg_blog_creation'));
        add_filter('wpmu_active_signup', array(&$this, 'neg_blog_creation'));
    }

    function neg_blog_creation($active = 'all') {

        if ($active == 'user' || $active == 'none') {
            return $active;
        } else {
            return 'none';
        }
    }

    function pos_blog_creation($active = 'all') {

        if ($active == 'user' || $active == 'none') {
            return $active;
        } else {
            // Check our count
            if (empty($this->data['number'])) {
                //  unlimited
                return $active;
            } else {
                $thelimit = (int) $this->data['number'];

                if ($thelimit > (int) $this->current_blog_count()) {
                    return $active;
                } else {
                    return $this->neg_blog_creation($active);
                }
            }
        }
    }

    function current_blog_count() {

        global $member, $wpdb;

        if (!empty($member) && method_exists($member, 'has_cap')) {
            // We have a member and it is a correct object
            $count = 0;
            $blogs = get_blogs_of_user($member->ID);
            foreach ($blogs as $blog) {
                if ($this->is_user_blog_admin($member->ID, $blog->userblog_id)) {
                    $count++;
                }
            }

            return (int) $count;
        } else {
            return 0;
        }
    }

    function is_user_blog_admin($user_id, $blog_id) {
        global $wpdb;

        $meta_key = $wpdb->base_prefix . $blog_id . "_capabilities";

        $role_sql = $wpdb->prepare("SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s", $meta_key);

        $role = $wpdb->get_results($role_sql);

        //clean the role
        foreach ($role as $key => $r) {
            $role[$key]->meta_value = maybe_unserialize($r->meta_value);
        }

        foreach ($role as $key => $r) {
            if ($r->meta_value['administrator'] == 1 && $r->user_id == $user_id) {
                return true;
            }
        }

        return false;
    }

}

/*
function M_AddAdminSection($sections) {
    $sections['admin'] = array("title" => __('Administration', 'membership'));

    return $sections;
}*/

function coursepress_setup_default_modules() {
    M_register_rule('text_module', 'text_module', 'modules');
}

add_action('plugins_loaded', 'coursepress_setup_default_modules', 99);
?>