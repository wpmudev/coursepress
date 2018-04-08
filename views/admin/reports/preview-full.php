<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course CoursePress_Course
 */
?>
<div class="wrap coursepress-wrap" id="coursepress-reports-list">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Report', 'cp' ); ?></h1>
    <h2><?php echo esc_html( $course->post_title ); ?></h2>
    <div class="coursepress-page coursepress-page-report">
    <h4><?php esc_html_e( 'Units list', 'cp' );?></h4>
    <ul>
<?php
foreach ( $units as $unit ) {
	printf( '<li>%s</li>', esc_html( $unit->post_title ) );
}
?>
    </ul>
    <h4><?php esc_html_e( 'Students list', 'cp' );?></h4>
    <ul>
<?php
foreach ( $students as $student ) {
	printf( '<li>%s</li>', esc_html( $student->display_name ) );
}
?>
    </ul>

<?php echo $content; ?>
    </div>
</div>
