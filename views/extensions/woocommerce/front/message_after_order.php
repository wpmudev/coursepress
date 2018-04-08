<?php
/**
 * @var boolean $show_dashboard_link
 * @var string $dashboard_link
 */
?>
<h2 class="cp_woo_header"><?php esc_html_e( 'Course', 'cp' ); ?></h2>
<p class="cp_woo_thanks"><?php esc_html_e( 'Thank you for signing up for the course. We hope you enjoy your experience.', 'cp' ); ?></p>
<?php if ( $show_dashboard_link ) { ?>
<p class="cp_woo_dashboard_link"><?php
printf( esc_html__( 'You can find the course in your <a href="%s">Dashboard</a>', 'cp' ), esc_url( $dashboard_link ));
?>
</p><hr />
<?php } ?>
