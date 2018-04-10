<?php
/**
 * The template use for coursepress student settings.
 *
 * @since 3.0
 * @package CoursePress
 */
get_header(); ?>

    <div class="coursepress-wrap">
        <div class="container">
            <div class="content-area">
                <header class="page-header">
                    <h1 class="page-title"><?php _e( 'My Profile', 'cp' ); ?></h1>
                </header>
            </div>

            <?php coursepress_get_template( 'registration', 'form' ); ?>
        </div>
    </div>
<?php
get_footer();
