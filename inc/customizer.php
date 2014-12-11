<?php

/**
 * CoursePress Theme Customizer
 *
 * @package CoursePress
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function coursepress_customize_register( $wp_customize ) {
    $wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
    $wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
    $wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

    $colors = array();

    $colors[] = array(
        'slug' => 'body_text_color',
        'default' => '#878786',
        'label' => __( 'Body Text Color', 'cp' )
    );

    $colors[] = array(
        'slug' => 'content_text_color',
        'default' => '#666666',
        'label' => __( 'Content Text Color', 'cp' )
    );

    $colors[] = array(
        'slug' => 'content_header_color',
        'default' => '#878786',
        'label' => __( 'Content Header Color', 'cp' )
    );

    $colors[] = array(
        'slug' => 'content_link_color',
        'default' => '#1cb8ea',
        'label' => __( 'Content Links Color', 'cp' )
    );

    $colors[] = array(
        'slug' => 'content_link_hover_color',
        'default' => '#1cb8ea',
        'label' => __( 'Content Links Hover Color', 'cp' )
    );

    $colors[] = array(
        'slug' => 'main_navigation_link_color',
        'default' => '#666',
        'label' => __( 'Main Navigation Links Color', 'cp' )
    );

    $colors[] = array(
        'slug' => 'main_navigation_link_hover_color',
        'default' => '#74d1d4',
        'label' => __( 'Main Navigation Links Hover Color', 'cp' )
    );

    $colors[] = array(
        'slug' => 'footer_background_color',
        'default' => '#f2f6f8',
        'label' => __( 'Footer Background Color', 'cp' )
    );

    $colors[] = array(
        'slug' => 'footer_link_color',
        'default' => '#83abb6',
        'label' => __( 'Footer Links Color', 'cp' )
    );

    $colors[] = array(
        'slug' => 'footer_link_hover_color',
        'default' => '#74d1d4',
        'label' => __( 'Footer Links Hover Color', 'cp' )
    );

    $colors[] = array(
        'slug' => 'widget_title_color',
        'default' => '#c0c21e',
        'label' => __( 'Widgets Title Color', 'cp' )
    );

    sort( $colors );

    foreach ( $colors as $color ) {
        // SETTINGS
        $wp_customize->add_setting(
                $color['slug'], array(
            'default' => $color['default'],
            'type' => 'option',
            'capability' =>
            'edit_theme_options'
                )
        );
        // CONTROLS
        $wp_customize->add_control(
                new WP_Customize_Color_Control(
                $wp_customize, $color['slug'], array(
            'label' => $color['label'],
            'section' => 'colors',
            'settings' => $color['slug']
                )
                )
        );
    }

    $wp_customize->add_setting( 'coursepress_logo', array(
        'default' => get_template_directory_uri() . '/images/logo-default.png',
        'type' => 'theme_mod',
        'capability' => 'edit_theme_options',
    ) );

    $wp_customize->add_section( 'cp_logo_section', array(
        'title' => __( 'Logo', 'cp' ),
        'priority' => 1,
    ) );

    $wp_customize->add_control(
            new WP_Customize_Image_Control(
            $wp_customize, 'logo', array(
        'label' => __( 'Upload a logo', 'cp' ),
        'section' => 'cp_logo_section',
        'settings' => 'coursepress_logo',
            )
    ) );

    if ( $wp_customize->is_preview() && !is_admin() ) {
        add_action( 'wp_footer', 'coursepress_customize_preview', 21 );
    }
}

function coursepress_customize_preview() {
    ?>
    <script type="text/javascript">
        ( function( $ ) {
            wp.customize( 'coursepress_logo', function( value ) {
                value.bind( function( to ) {
                    $( "#coursepress_logo" ).attr( "src", to ? to : '' );
                } );
            } );
        } )( jQuery )
    </script>
    <?php

}

add_action( 'customize_register', 'coursepress_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function coursepress_customize_preview_js() {
    wp_enqueue_script( 'coursepress_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20130508', true );
}

add_action( 'customize_preview_init', 'coursepress_customize_preview_js' );
