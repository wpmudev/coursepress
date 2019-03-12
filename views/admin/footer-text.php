<?php
/**
 * WPMU footer text
 */
?>
<div class="cp-admin-footer">
    <div class="wpmu-footer-text">
        <?php _e( 'Made with ', 'cp' ); ?><i class="fa fa-heart"></i> <?php _e( 'by', 'cp' ); ?>
        <a href="https://premium.wpmudev.org/">WPMU DEV</a>
    </div>
<?php
$hide_footer = $is_member = false;
if ( function_exists( 'is_wpmudev_member' ) ) {
	$is_member = is_wpmudev_member();
}

if ( $is_member ) {
	$hide_footer = apply_filters( 'wpmudev_coursepress_change_footer', $hide_footer );
	if ( ! $hide_footer ) {
?>
                    <ul class="sui-footer-nav">
                        <li><a href="https://premium.wpmudev.org/hub/" target="_blank"><?php esc_html_e( 'The Hub', 'cp' ); ?></a></li>
                        <li><a href="https://premium.wpmudev.org/projects/category/plugins/" target="_blank"><?php esc_html_e( 'Plugins', 'cp' ); ?></a></li>
                        <li><a href="https://premium.wpmudev.org/roadmap/" target="_blank"><?php esc_html_e( 'Roadmap', 'cp' ); ?></a></li>
                        <li><a href="https://premium.wpmudev.org/hub/support/" target="_blank"><?php esc_html_e( 'Support', 'cp' ); ?></a></li>
                        <li><a href="https://premium.wpmudev.org/docs/" target="_blank"><?php esc_html_e( 'Docs', 'cp' ); ?></a></li>
                        <li><a href="https://premium.wpmudev.org/hub/community/" target="_blank"><?php esc_html_e( 'Community', 'cp' ); ?></a></li>
                        <li><a href="https://premium.wpmudev.org/terms-of-service/" target="_blank"><?php esc_html_e( 'Terms of Service', 'cp' ); ?></a></li>
                        <li><a href="https://incsub.com/privacy-policy/" target="_blank"><?php esc_html_e( 'Privacy Policy', 'cp' ); ?></a></li>
                    </ul>
<?php
	}
} else {
?>
                <ul class="sui-footer-nav">
                    <li><a href="https://profiles.wordpress.org/wpmudev#content-plugins" target="_blank"><?php esc_html_e( 'Free Plugins', 'cp' ); ?></a></li>
                    <li><a href="https://premium.wpmudev.org/features/" target="_blank"><?php esc_html_e( 'Membership', 'cp' ); ?></a></li>
                    <li><a href="https://premium.wpmudev.org/roadmap/" target="_blank"><?php esc_html_e( 'Roadmap', 'cp' ); ?></a></li>
                    <li><a href="https://wordpress.org/support/plugin/forminator" target="_blank"><?php esc_html_e( 'Support', 'cp' ); ?></a></li>
                    <li><a href="https://premium.wpmudev.org/docs/" target="_blank"><?php esc_html_e( 'Docs', 'cp' ); ?></a></li>
                    <li><a href="https://premium.wpmudev.org/hub/" target="_blank"><?php esc_html_e( 'The Hub', 'cp' ); ?></a></li>
                    <li><a href="https://premium.wpmudev.org/terms-of-service/" target="_blank"><?php esc_html_e( 'Terms of Service', 'cp' ); ?></a></li>
                    <li><a href="https://incsub.com/privacy-policy/" target="_blank"><?php esc_html_e( 'Privacy Policy', 'cp' ); ?></a></li>
                </ul>
<?php } ?>
            <?php if ( ! $hide_footer ) : ?>
                <ul class="sui-footer-social">
                    <li><a href="https://www.facebook.com/wpmudev" target="_blank">
                            <span class="dashicons dashicons-facebook-alt"></span>
                            <span class="sui-screen-reader-text">Facebook</span>
                        </a></li>
                    <li><a href="https://twitter.com/wpmudev" target="_blank">
                        <span class="dashicons dashicons-twitter"></span>
                        <span class="sui-screen-reader-text">Twitter</span>
                        </a></li>
                    <li><a href="https://www.instagram.com/wpmu_dev/" target="_blank">
                            <i class="sui-icon-instagram" aria-hidden="true"></i>
                            <span class="sui-screen-reader-text">Instagram</span>
                        </a></li>
                </ul>
            <?php endif; ?>
</div>
