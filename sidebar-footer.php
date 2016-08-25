<?php
/**
 * The Sidebar containing the footer widget areas.
 *
 * @package CoursePress
 */
?>
<div id="third" class="widget-area footer-widget-area clearf" role="complementary">
    <?php do_action( 'before_sidebar' ); ?>
    <?php if ( !dynamic_sidebar( 'sidebar-2' ) ) : ?>
    <?php endif; // end sidebar widget area ?>
</div><!-- #secondary -->
