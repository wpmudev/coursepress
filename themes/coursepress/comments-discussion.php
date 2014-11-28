<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form. The actual display of comments is
 * handled by a callback to coursepress_comment() which is
 * located in the inc/template-tags.php file.
 *
 * @package CoursePress
 */
/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */

if ( post_password_required() ) {
    return;
}
?>

<div id="comments" class="comments-area discussion-comments">

    <?php // You can start editing here -- including this comment!  ?>

    <?php if ( have_comments() ) : ?>
        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
            <nav id="comment-nav-above" class="comment-navigation" role="navigation">
                <h1 class="screen-reader-text"><?php _e( 'Comment navigation', 'cp' ); ?></h1>
                <div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'cp' ) ); ?></div>
                <div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'cp' ) ); ?></div>
            </nav><!-- #comment-nav-above -->
        <?php endif; // check for comment navigation  ?>

        <ul class="comment-list">
            <?php
            /* Loop through and list the comments. Tell wp_list_comments()
             * to use coursepress_comment() to format the comments.
             * If you want to override this in a child theme, then you can
             * define coursepress_comment() and that will be used instead.
             * See coursepress_comment() in inc/template-tags.php for more.
             */
            wp_list_comments( array( 'callback' => 'coursepress_discussion_comment' ) );
            ?>
        </ul><!-- .comment-list -->

        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through  ?>
            <nav id="comment-nav-below" class="comment-navigation" role="navigation">
                <h1 class="screen-reader-text"><?php _e( 'Comment navigation', 'cp' ); ?></h1>
                <div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'cp' ) ); ?></div>
                <div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'cp' ) ); ?></div>
            </nav><!-- #comment-nav-below -->
        <?php endif; // check for comment navigation  ?>

    <?php endif; // have_comments()  ?>

    <?php
    // If comments are closed and there are comments, let's leave a little note, shall we?
    if ( !comments_open() && '0' != get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
        ?>
        <p class="no-comments"><?php _e( 'Comments are closed.', 'cp' ); ?></p>
    <?php endif; ?>

    <?php
    $comments_args = array(
        'id_form' => 'discussion_comment_form',
        'id_submit' => 'answer_the_question_button',
        'title_reply' => __( 'Answer the Question', 'cp' ),
        'title_reply_to' => __( 'Leave a Reply to %s' ),
        'cancel_reply_link' => __( 'Cancel Reply' ),
        'label_submit' => __( 'Answer the Question', 'cp' ),
        'comment_field' => '<p class="comment-form-comment"><label for="comment">' .
        '</label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true" palceholder="' . __( 'Write a response', 'cp' ) . '">' .
        '</textarea></p>',
        'must_log_in' => '',
        'logged_in_as' => '',
        'comment_notes_before' => '',
        'comment_notes_after' => '',
    );
    comment_form( $comments_args );
    ?>
    

</div><!-- #comments -->
