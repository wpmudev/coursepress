<?php $discussion = coursepress_get_discussion( $course ); ?>
<div class="course-discussion-wrapper">
	<div class="course-discussion-page course-discussion-content">
<?php
if ( empty( $discussion ) ) {
    printf(
        '<h3 class="title course-discussion-title">%s</h3>',
        esc_html__( 'Missing discussion', 'cp' )
    );
    printf(
        '<p class="message error">%s</p>',
        esc_html__( 'Something went wrong, please back to previous page.', 'cp' )
    );
} else {
?>
        <h3 class="title course-discussion-title"><?php echo esc_html( 'Discussion', 'cp' ); ?>: <?php echo esc_html( $discussion->post_title ); ?></h3>
<?php echo esc_html( $discussion->post_content ); ?>
<?php
}
?>
    </div>
<?php
if ( ! empty( $discussion ) ) {
	global $post;
	$post = $discussion;
	setup_postdata( $post );
	comments_template();
	wp_reset_postdata();
}
?>
</div>
