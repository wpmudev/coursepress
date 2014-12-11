<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package CoursePress
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php wp_title('|', true, 'right'); ?></title>
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

        <?php wp_head(); ?>
        <?php include( get_template_directory() . '/inc/custom-colors.php' ); ?>
    </head>

    <body <?php body_class('cp-wrap'); ?>>
        <div id="page" class="hfeed site">
            <?php do_action('before'); ?>
            <header id="masthead" class="site-header" role="banner">
                <div class='wrap'>
                    <div class="site-branding">
                        <?php
                        $logo_image_url = get_theme_mod('coursepress_logo'); //get_stylesheet_directory_uri() . '/images/logo-default.png'
                        ?>
                        <h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php if ( isset($logo_image_url) && $logo_image_url != '' ) { ?><img id="coursepress_logo" src='<?php echo $logo_image_url; ?>' alt='<?php bloginfo('name'); ?>' border='0' /><?php
                                } else {
                                    echo get_bloginfo('name');
                                }
                                ?></a></h1>
                        <!--<h2 class="site-description"><?php bloginfo('description'); ?></h2>-->
                    </div>

                    <nav id="site-navigation" class="main-navigation" role="navigation">

                        <?php
                        $theme_location = 'primary';
                        if ( !has_nav_menu($theme_location) ) {
                            $theme_locations = get_nav_menu_locations();
                            foreach ( ( array ) $theme_locations as $key => $location ) {
                                $theme_location = $key;
                                break;
                            }
                        }

                        if ( has_nav_menu('primary') ) {
                            wp_nav_menu(array(
                                'theme_location' => 'primary',
                                'menu_class' => 'mobile_menu',
                                'menu_id' => 'mobile_menu',
                                //'items_wrap' => '<select id="drop-mobile-nav"><option value="">' . __( 'Select a page...', 'coursepress' ) . '</option>%3$s</select>',
                                'walker' => new Walker_Nav_Menu_Dropdown() ));
                        }
                        ?>

                        <a class="skip-link screen-reader-text" href="#content"><?php _e('Skip to content', 'cp'); ?></a>
<?php wp_nav_menu(array( 'theme_location' => 'primary' )); ?>
                    </nav><!-- #site-navigation -->
                </div>
            </header><!-- #masthead -->

            <div id="content" class="site-content">
