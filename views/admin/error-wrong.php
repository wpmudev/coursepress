<div class="wrap coursepress-wrap">
<?php
if ( isset( $title ) && ! empty( $title ) ) {
    printf( '<h1 class="wp-heading-inline">%s</h1>', $title );
}
?>
<div class="notice notice-error"><p><?php
esc_html_e( 'We\'re sorry, but something went wrong.', 'cp' );
?></p></div>
