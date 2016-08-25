<?php
/**
 * Add New Discussion template file
 * 
 * @package CoursePress
 */
global $coursepress;
$course_id = do_shortcode( '[get_parent_course_id]' );

$coursepress->check_access( $course_id );

get_header();

$form_message_class = '';
$form_message = '';

if ( isset( $_POST['new_question_submit'] ) ) {
    check_admin_referer( 'new_question' );

    if ( $_POST['question_title'] !== '' ) {
        if ( $_POST['question_description'] !== '' ) {
            $discussion = new Discussion();
            $discussion->update_discussion( $_POST['question_title'], $_POST['question_description'], $course_id );
			// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
            wp_redirect( get_permalink( $course_id ) . $coursepress->get_discussion_slug() );
            exit;
        } else {
            $form_message = __( 'Question description is required.' );
            $form_message_class = 'red';
        }
    } else {
        $form_message = __( 'Question title is required.' );
        $form_message_class = 'red';
    }
}
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <h1><?php echo do_shortcode( '[course_title course_id="' . $course_id . '"]' ); ?></h1>
        <div class="instructors-content">
            <?php echo do_shortcode( '[course_instructors style="list-flat" course_id="' . $course_id . '"]' ); ?>
        </div>
        
        <?php
        echo do_shortcode( '[course_unit_archive_submenu]' );
        ?>

        <div class="clearfix"></div>

        <p class="form-info-<?php echo $form_message_class; ?>"><?php echo $form_message; ?></p>

        <form id="new_question_form" name="new_question_form" method="post" class="new_question_form">
            <div class="add_new_discussion">
                <?php echo do_shortcode( '[units_dropdown course_id="' . $course_id . '" include_general="true" general_title="'.__('Course General', 'cp').'"]' ) ?>
                <div class="new_question">
                    <div class="rounded"><span>Q</span></div>
                    <input type="text" name="question_title" placeholder="<?php _e( 'Title of your question', 'cp' ); ?>" />
                    <textarea name="question_description" placeholder="<?php _e( 'Question description...', 'cp' ); ?>"></textarea>

                    <input type="submit" class="button_submit" name="new_question_submit" value="<?php _e( 'Ask this Question', 'cp' ); ?>">
                    <a href="<?php echo get_permalink( $course_id ) . $coursepress->get_discussion_slug(); ?>/" class="button_cancel"><?php _e( 'Cancel', 'cp' ); ?></a>

                    <?php wp_nonce_field( 'new_question' ); ?>
                </div>
            </div>
        </form>

    </main><!-- #main -->
</div><!-- #primary -->
<?php get_sidebar( 'footer' ); ?>
<?php get_footer(); ?>