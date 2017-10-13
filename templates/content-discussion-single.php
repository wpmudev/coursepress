<?php
$discussion = coursepress_get_discussion( $course );
?>
<div class="course-discussion-wrapper">

	<div class="course-discussion-page course-discussion-content">
        <h3 class="title course-discussion-title"><?php esc_html_e( 'Discussion', 'cp' ); ?>: <?php esc_html_e( $discussion->post_title ); ?></h3>
<?php echo $discussion->post_content; ?>

	</div>
<?php

if ( ! empty( $discussion ) ) {
	setup_postdata( $discussion );
	comments_template();
	wp_reset_postdata();
}
	?>

</div>
