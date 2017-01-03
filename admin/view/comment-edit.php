<?php
wp_enqueue_script( 'comment' );
require_once( ABSPATH . 'wp-admin/admin-header.php' );

$comment_id = absint( $_GET['c'] );

if ( ! $comment = get_comment( $comment_id ) ) {
    comment_footer_die( __( 'Invalid comment ID.' ) . sprintf( ' <a href="%s">' . __( 'Go back' ) . '</a>.', 'javascript:history.go(-1)' ) ); }

if ( !CoursePress_Data_Capabilities::can_edit_comment( $comment_id ) )
    comment_footer_die( __('Sorry, you are not allowed to edit this comment.') );

if ( 'trash' == $comment->comment_approved ) {
    comment_footer_die( __( 'This comment is in the Trash. Please move it out of the Trash if you want to edit it.' ) ); }

$comment = get_comment_to_edit( $comment_id );

?>
<form name="post" action="edit.php" method="post" id="post">
<?php wp_nonce_field( 'update-comment_' . $comment->comment_ID ) ?>
<div class="wrap">
<h1><?php _e( 'Edit Comment' ); ?></h1>

<div id="poststuff">
<input type="hidden" name="post_type" value="<?php echo CoursePress_Data_Course::get_post_type_name(); ?>" />
<input type="hidden" name="page" value="coursepress_comments" />
<input type="hidden" name="action" value="editedcomment" />
<input type="hidden" name="comment_ID" value="<?php echo esc_attr( $comment->comment_ID ); ?>" />
<input type="hidden" name="comment_post_ID" value="<?php echo esc_attr( $comment->comment_post_ID ); ?>" />

<div id="post-body" class="metabox-holder columns-2">
<div id="post-body-content" class="edit-form-section edit-comment-section">

<div id="postdiv" class="postarea">
<?php
echo '<label for="content" class="screen-reader-text">' . __( 'Comment' ) . '</label>';
$quicktags_settings = array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close' );
wp_editor( $comment->comment_content, 'content', array( 'media_buttons' => false, 'tinymce' => false, 'quicktags' => $quicktags_settings ) );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
</div>
</div><!-- /post-body-content -->

<div id="postbox-container-1" class="postbox-container">
<div id="submitdiv" class="stuffbox" >
<h2><?php _e( 'Status' ) ?></h2>
<div class="inside">
<div class="submitbox" id="submitcomment">
<div id="minor-publishing">

<div id="misc-publishing-actions">

<fieldset class="misc-pub-section misc-pub-comment-status" id="comment-status-radio">
<legend class="screen-reader-text"><?php _e( 'Comment status' ); ?></legend>
<label><input type="radio"<?php checked( $comment->comment_approved, '1' ); ?> name="comment_status" value="1" /><?php _ex( 'Approved', 'comment status' ); ?></label><br />
<label><input type="radio"<?php checked( $comment->comment_approved, '0' ); ?> name="comment_status" value="0" /><?php _ex( 'Pending', 'comment status' ); ?></label><br />
<label><input type="radio"<?php checked( $comment->comment_approved, 'spam' ); ?> name="comment_status" value="spam" /><?php _ex( 'Spam', 'comment status' ); ?></label>
</fieldset>

<div class="misc-pub-section curtime misc-pub-curtime">
<?php
/* translators: Publish box date format, see https://secure.php.net/date */
$datef = __( 'M j, Y @ H:i' );
?>
    <span id="timestamp"><?php
printf(
    /* translators: %s: comment date */
    __( 'Submitted on: %s' ),
    '<b>' . date_i18n( $datef, strtotime( $comment->comment_date ) ) . '</b>'
);
?></span>
<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js"><span aria-hidden="true"><?php _e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php _e( 'Edit date and time' ); ?></span></a>
<fieldset id='timestampdiv' class='hide-if-js'>
<legend class="screen-reader-text"><?php _e( 'Date and time' ); ?></legend>
</fieldset>
</div>

<?php
$post_id = $comment->comment_post_ID;
if ( current_user_can( 'edit_post', $post_id ) ) {
    $post_link = "<a href='" . esc_url( get_edit_post_link( $post_id ) ) . "'>";
    $post_link .= esc_html( get_the_title( $post_id ) ) . '</a>';
} else {
    $post_link = esc_html( get_the_title( $post_id ) );
}
?>

<div class="misc-pub-section misc-pub-response-to">
<?php printf(
    /* translators: %s: post link */
    __( 'In response to: %s' ),
    '<b>' . $post_link . '</b>'
); ?>
</div>

<?php
if ( $comment->comment_parent ) :
    $parent      = get_comment( $comment->comment_parent );
if ( $parent ) :
    $parent_link = esc_url( get_comment_link( $parent ) );
$name        = get_comment_author( $parent );
?>
    <div class="misc-pub-section misc-pub-reply-to">
<?php printf(
    /* translators: %s: comment link */
    __( 'In reply to: %s' ),
    '<b><a href="' . $parent_link . '">' . $name . '</a></b>'
); ?>
    </div>
<?php endif;
endif; ?>

<?php
/**
 * Filters miscellaneous actions for the edit comment form sidebar.
 *
 * @since 4.3.0
 *
 * @param string $html    Output HTML to display miscellaneous action.
 * @param object $comment Current comment object.
 */
echo apply_filters( 'edit_comment_misc_actions', '', $comment );
?>

</div> <!-- misc actions -->
<div class="clear"></div>
</div>

<div id="major-publishing-actions">
<div id="delete-action"><!--
<?php echo "<a class='submitdelete deletion' href='" . wp_nonce_url( 'comment.php?action=' . ( ! EMPTY_TRASH_DAYS ? 'deletecomment' : 'trashcomment' ) . "&amp;c=$comment->comment_ID&amp;_wp_original_http_referer=" . urlencode( wp_get_referer() ), 'delete-comment_' . $comment->comment_ID ) . "'>" . ( ! EMPTY_TRASH_DAYS ? __( 'Delete Permanently' ) : __( 'Move to Trash' ) ) . "</a>\n"; ?>
--></div>
<div id="publishing-action">
<?php submit_button( __( 'Update' ), 'primary', 'save', false ); ?>
</div>
<div class="clear"></div>
</div>
</div>
</div>
</div><!-- /submitdiv -->
</div>

<div id="postbox-container-2" class="postbox-container">
<?php
/** This action is documented in wp-admin/edit-form-advanced.php */
do_action( 'add_meta_boxes', 'comment', $comment );

/**
 * Fires when comment-specific meta boxes are added.
 *
 * @since 3.0.0
 *
 * @param WP_Comment $comment Comment object.
 */
do_action( 'add_meta_boxes_comment', $comment );

do_meta_boxes( null, 'normal', $comment );

$referer = wp_get_referer();
?>
</div>

<input type="hidden" name="c" value="<?php echo esc_attr( $comment->comment_ID ) ?>" />
<input type="hidden" name="p" value="<?php echo esc_attr( $comment->comment_post_ID ) ?>" />
<input name="referredby" type="hidden" id="referredby" value="<?php echo $referer ? esc_url( $referer ) : ''; ?>" />
<?php wp_original_referer_field( true, 'previous' ); ?>
<input type="hidden" name="noredir" value="1" />

</div><!-- /post-body -->
</div>
</div>
</form>

<?php if ( ! wp_is_mobile() ) : ?>
<script type="text/javascript">
try{document.post.name.focus();}catch(e){}
</script>
<?php endif;
