<?php
global $action, $page;
wp_reset_vars(array('action', 'page'));

if (wp_verify_nonce($_REQUEST['_wpnonce'], 'update-coursepress-options')) {
    if(isset($_POST['course_slug']) && !empty($_POST['course_slug'])){
        $this->set_course_slug($_POST['course_slug']);
    }
}
?>

<div class="wrap nosubsub">
    <div class="icon32 icon32-posts-page" id="icon-options-general"><br></div>
    <h2><?php _e('Settings', 'cp'); ?></h2>

    <?php
    $tab = (isset($_GET['tab'])) ? $_GET['tab'] : '';
    if (empty($tab)) {
        $tab = 'general';
    }
    ?>

    <?php
    $menus = array();
    $menus['general'] = __('General', 'cp');
    $menus['groups'] = __('Class Groups', 'cp');
    $menus['payment'] = __('Payment Settings', 'cp');
    $menus['email'] = __('E-mail Settings', 'cp');
    $menus['shortcodes'] = __('Shortcodes', 'cp');
    $menus = apply_filters('coursepress_settings_new_menus', $menus);
    ?>

    <h3 class="nav-tab-wrapper">
        <?php
        foreach ($menus as $key => $menu) {
            ?>
            <a class="nav-tab<?php if ($tab == $key)
            echo ' nav-tab-active'; ?>" href="admin.php?page=<?php echo $page; ?>&amp;tab=<?php echo $key; ?>"><?php echo $menu; ?></a>
               <?php
           }
           ?>
    </h3>

    <?php
    switch ($tab) {

        case 'general': $this->show_settings_general();
            break;
        
        case 'groups': $this->show_settings_groups();
            break;

        case 'payment': $this->show_settings_payment();
            break;

        case 'shortcodes': $this->show_settings_shortcodes();
            break;

        case 'email': $this->show_settings_email();
            break;
        
        default: do_action('coursepress_settings_menu_' . $tab);
            break;
    }
    ?>

</div>
