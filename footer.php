<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package CoursePress
 */
?>


</div><!-- #content -->

<div class="push"></div>
</div><!-- #page -->

<footer id="colophon" class="site-footer" role="contentinfo">
    <nav id="footer-navigation" class="footer-navigation wrap" role="navigation">
        <?php wp_nav_menu( array( 'theme_location' => 'secondary' ) ); ?>
    </nav><!-- #site-navigation -->
</footer><!-- #colophon -->

<?php wp_footer(); ?>

</body>
</html>