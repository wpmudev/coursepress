<?php
/**
 * @var int $user_id
 * @var int $course
 */
$data = coursepress_get_disscusions( $course );
if ( empty( $data ) ) :
	printf(
		'<p class="message">%s</p>',
		__( 'This course does not have any discussions.', 'cp' )
	);
else :
	echo '<ul class="discussion-archive-list">';
	foreach ( $data as $discussion ) :
		echo '<li>';
		$comments_count = wp_count_comments( $discussion->ID );
		echo '<div class="discussion-archive-single-meta">';
		echo '<div class="discussion-comment"><div class="comment">';
		echo $comments_count->approved;
		echo '</div></div>';
		echo '</div>';

		$author = coursepress_get_user_name( $discussion->post_author, false, false );

		if ( 'course' === $discussion->unit_id ) :
			$applies_to = get_post_field( 'post_title', $discussion->course_id );
		else :
			$applies_to = get_post_field( 'post_title', $discussion->unit_id );
		endif;

		$date = get_the_date( get_option( 'date_format' ), $discussion );

		echo '
					<div class="discussion-archive-single">
						<h3 class="discussion-title"><a href="' . esc_url_raw( $discussion->url ) . '">' . esc_html( $discussion->post_title ) . '</a></h3>
						<div class="discussion-content">
							' . $discussion->post_content . '
						</div>
						<hr />
						<div class="meta">' . esc_html( $author ) . ' | ' . esc_html( $date ) . ' | ' . esc_html__( 'Applies to:', 'cp' ) . ' ' . $applies_to . '</div>
					</div>
			';

		echo '
				</li>';
	endforeach;
	echo '</ul>';
endif;

