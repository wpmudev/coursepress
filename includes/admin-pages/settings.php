<?php
global $action, $page;
wp_reset_vars(array('action', 'page'));

$page = $_GET['page'];

$tab = (isset($_GET['tab'])) ? $_GET['tab'] : '';
if (empty($tab)) {
    if (current_user_can('administrator')) {
        $tab = 'general';
    } else if (current_user_can('coursepress_settings_groups_page_cap')) {
        $tab = 'groups';
    } else if (current_user_can('coursepress_settings_shortcode_page_cap')) {
        $tab = 'shortcodes';
    } else {
        die(__('You do not have required permissions to access Settings.', 'cp'));
    }
}

if (isset($_POST['_wpnonce'])) {
    if (wp_verify_nonce($_REQUEST['_wpnonce'], 'update-coursepress-options')) {
        foreach ($_POST as $key => $value) {
            if (preg_match("/option_/i", $key)) {//every field name with prefix "option_" will be saved as an option
                if ($_POST[$key] != '') {
                    update_option(str_replace('option_', '', $key), $value);
                }
            }
        }
    }

    if ($tab == 'general') {
        if (isset($_POST['display_menu_items'])) {
            update_option('display_menu_items', 1);
        } else {
            update_option('display_menu_items', 0);
        }
        flush_rewrite_rules();
    }
}
?>

<div class="wrap nosubsub">
    <div class="icon32 icon32-posts-page" id="icon-options-general"><br></div>
    <h2><?php _e('Settings', 'cp'); ?></h2>

    <?php
    if (isset($_POST['submit'])) {
        ?>
        <div id="message" class="updated fade"><p><?php _e('Settings saved successfully.', 'cp'); ?></p></div>
        <?php
    }
    ?>


    <?php
    $menus = array();
    if (current_user_can('administrator')) {
        $menus['general'] = __('General', 'cp');
    }

    if (current_user_can('coursepress_settings_groups_page_cap')) {
        $menus['groups'] = __('Class Groups', 'cp');
    }

    if (current_user_can('administrator')) {
        /* $menus['payment'] = __('Payment Settings', 'cp'); */
        $menus['email'] = __('E-mail Settings', 'cp');
    }

    if (current_user_can('administrator')) {
        $menus['instructor_capabilities'] = __('Instructor Capabilities', 'cp');
    }

    if (current_user_can('coursepress_settings_shortcode_page_cap')) {
        $menus['shortcodes'] = __('Shortcodes', 'cp');
    }

    $menus = apply_filters('coursepress_settings_new_menus', $menus);
    ?>

    <h3 class="nav-tab-wrapper">
        <?php
        foreach ($menus as $key => $menu) {
            ?>
            <a class="nav-tab<?php
               if ($tab == $key)
                   echo ' nav-tab-active';
               ?>" href="admin.php?page=<?php echo $page; ?>&amp;tab=<?php echo $key; ?>"><?php echo $menu; ?></a>
               <?php
           }
           ?>
    </h3>

    <?php
    switch ($tab) {


        case 'general':
            if (current_user_can('administrator')) {
                $this->show_settings_general();
            }
            break;


        case 'groups':
            if (current_user_can('coursepress_settings_groups_page_cap')) {
                $this->show_settings_groups();
            }
            break;

        /* case 'payment':
          if (current_user_can('administrator')) {
          $this->show_settings_payment();
          }
          break; */

        case 'shortcodes':
            if (current_user_can('coursepress_settings_shortcode_page_cap')) {
                $this->show_settings_shortcodes();
            }
            break;

        case 'instructor_capabilities':
            if (current_user_can('administrator')) {
                //$this->add_user_roles_and_caps();
                $this->show_settings_instructor_capabilities();
            }
            break;

        case 'email':
            if (current_user_can('administrator')) {
                $this->show_settings_email();
            }
            break;

        default: do_action('coursepress_settings_menu_' . $tab);
            break;
    }
    ?>

</div>
