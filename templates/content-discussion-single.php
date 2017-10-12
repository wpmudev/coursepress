
		<div class="course-discussion-wrapper">

		<div class="course-discussion-page course-discussion-content">
        <h3 class="title course-discussion-title"><?php esc_html_e( 'Discussion', 'cp' ); ?>: <?php esc_html_e( $course->post_title ); ?></h3>
		$content .= CoursePress_Helper_Utility::filter_content( $post_content );

		if ( get_current_user_id() == (int) $author ) {
			$edit_discussion_link = CoursePress_Core::get_slug( 'course/', true ) . get_post_field( 'post_name', $course_id ) . '/' . CoursePress_Core::get_slug( 'discussions/' ) . CoursePress_Core::get_slug( 'discussion_new' );
			$edit_discussion_link .= '?id=' . $discussion->ID;
			<div class="edit-link"><a href="' . esc_url( $edit_discussion_link ) . '">' . esc_html__( 'Edit', 'cp' ) . '</a>
		}
		</div>
<?php

if ( ! empty( $discussion ) ) {
    setup_postdata( $discussion );
    comments_template();
    wp_reset_postdata();
}
 ?>

		</div>
