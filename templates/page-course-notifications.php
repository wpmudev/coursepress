<?php
/**
 * The template use to show student's per course notifications.
 *
 * @since 3.0
 * @package CoursePress
 */
$course = coursepress_get_course();
$notifications = CoursePress_Data_Notification::get_notifications( array( $course->ID, 1298 ) );
//echo '<pre>'; print_r($notifications); exit;
get_header(); ?>

	<div class="coursepress-wrap">
		<div class="container">
			<div class="content-area">
				<header class="page-header">
					<h1 class="page-title"><?php _e( 'Notifications', 'cp' ); ?></h1>
					<h2 class="entry-title"><?php echo coursepress_get_course_title(); ?></h2>
				</header>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  
				<?php if ( ! empty( $notifications ) ) : ?>
					<ul>
						<?php foreach ( $notifications as $notification ) : ?>
							<li>
								<div class="notification-archive-single-meta">
									<div class="notification-date">
										<span class="month"><?php echo get_the_date( 'M', $notification ); ?></span>
										<span class="day"><?php echo get_the_date( 'd', $notification ); ?></span>
										<span class="year"><?php echo get_the_date( 'Y', $notification ); ?></span>
									</div>
									<div class="notification-time"><?php echo get_the_time( 'h:ia', $notification ); ?></div>
								</div>
								<?php // $author = sprintf( __( 'by <span>%s</span>', 'cp' ), CoursePress_Helper_Utility::get_user_name( $notification->post_author ) ); ?>
								<?php $author = get_user_option( 'display_name', $notification->post_author ); ?>
								<div class="notification-archive-single">
									<h3 class="notification-title"><?php echo esc_html( $notification->post_title ); ?></h3>
									<div class="notification_author"><?php echo $author; ?></div>
									<div class="notification-content"><?php echo $notification->post_content; ?></div>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php get_footer();
