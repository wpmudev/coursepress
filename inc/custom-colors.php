<style>
    body{
        color: <?php echo get_option( 'body_text_color' );?>
    }
    
    p, 
    .post-type-archive-course article.type-course .entry-content, .post-type-archive-course article.type-course .entry-content p{ 
        color: <?php echo get_option( 'content_text_color' ); ?>; 
    }
    
    h1, h2, h3, h4, h5, h6 { 
        color: <?php echo get_option( 'content_header_color' ); ?>; 
    }

    a, a:visited{ 
        color: <?php echo get_option( 'content_link_color' ); ?>; 
    }

    a:hover { 
        color: <?php echo get_option( 'content_link_hover_color' ); ?>; 
    }

    .main-navigation a, .main-navigation a:visited{ 
        color: <?php echo get_option( 'main_navigation_link_color' ); ?>; 
    }

    .main-navigation a:hover {
        color: <?php echo get_option( 'main_navigation_link_hover_color' ); ?>; 
    }

    h1.widget-title span.yellow{
        color: <?php echo get_option( 'widget_title_color' ); ?>; 
    }
    
    .site-footer{
        background-color: <?php echo get_option( 'footer_background_color' ); ?>;
    }
    
    .site-footer a{
        color: <?php echo get_option( 'footer_link_color' ); ?>;
    }
    
    .site-footer a:hover{
        color: <?php echo get_option( 'footer_link_hover_color' ); ?>;
    }


</style>