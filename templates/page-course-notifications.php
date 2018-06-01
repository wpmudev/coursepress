<?php
/**
 * The template use to show student's per course notifications.
 *
 * @since 3.0
 * @package CoursePress
 */
$course_id     = get_the_ID();
$notifications = CoursePress_Data_Notification::get_notifications( $course_id );
get_header(); ?>

    <div class="coursepress-wrap">
        <div class="container">
            <div class="content-area">
                <header class="page-header">
<?php
/**
 * To override course submenu template to your theme or a child-theme,
 * create a template `course-submenu.php` and it will be loaded instead.
 *
 * @since 3.0
 */
coursepress_get_template( 'course', 'submenu' );
coursepress_breadcrumb();
?>
                    <h1 class="page-title"><?php _e( 'Course Notifications', 'cp' ); ?></h1>
                </header>
<?php
if ( empty( $notifications ) ) {
	echo '<p>';
	_e( 'There is no notifications yet.', 'cp' );
	echo '</p>';
} else {
	$date = 0;
	$today_start = strtotime( date( 'Y-m-d',  time() ) );
	$format = get_option( 'date_format' );
	$time_format = get_option( 'time_format' );
					?>
                    <ul class="notification-archive-list">
<?php
foreach ( $notifications as $notification ) {
	$timestamp = strtotime( $notification->post_date );
	$d = strtotime( date( 'Y-m-d', $timestamp ) );
	$diff = $today_start - $timestamp;
	$show = date_i18n( $format, $d );
	if ( 2 * DAY_IN_SECONDS > $diff ) {
		$d = __( 'Yesterday', 'cp' );
		if ( $today_start < $timestamp ) {
			$d = __( 'Today', 'cp' );
		}
		$show = $d;
	}
	if ( $date !== $d ) {
		printf( '<li class="date"><span>%s</span></li>', $show );
		$date = $d;
	}
	$author = sprintf( '<strong>%s</strong>', sprintf( __( 'By <span>%s</span>', 'cp' ), CoursePress_Utility::get_user_name( $notification->post_author ) ) );

?>
<li>
<h3 class="notification-title"><?php echo esc_html( $notification->post_title ); ?></h3>
<div class="notification-meta"><?php echo $author; ?> | <?php echo date_i18n( $time_format, $timestamp ); ?></div>
<div class="notification-content"><?php echo CoursePress_Utility::filter_content( $notification->post_content ); ?></div>
</li>
<?php } ?>
                    </ul>
<?php } ?>
            </div>
        </div>
    </div>
<?php
get_footer();
