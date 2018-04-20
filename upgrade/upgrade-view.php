<?php
/**
 * The template use to upgrade CoursePress from version 1.x to version 2.0.0
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
// Get all courses
$course_args = array(
	'post_type' => 'course',
	'post_status' => 'any',
	'posts_per_page' => -1,
	'suppress_filters' => true,
	'fields' => 'ids',
);
$courses = get_posts( $course_args );
$courses_to_upgrade = array();
foreach ( $courses as $course_id ) {
	$already_upgraded = (bool) get_post_meta( $course_id, '_cp_updated_to_version_2', true );
	if ( ! $already_upgraded ) {
		$courses_to_upgrade[] = $course_id;
	}
}
$count = count( $courses_to_upgrade );
?>
<div class="wrap coursepress-upgrade-view">
	<h2><?php _e( 'CoursePress 2.0 Upgrade', 'coursepress' ); ?></h2>

	<p class="description"><?php
		printf( _n( 'You have %d course to update.', 'You have %d courses to update.', $count, 'coursepress' ), $count );
	?></p>
	<div class="coursepress-update-holder">
		<form method="post" id="coursepress-update-form">
			<input type="hidden" name="user_id" value="<?php echo get_current_user_id(); ?>" />
			<?php foreach ( $courses_to_upgrade as $course_id ) : ?>
					<input type="hidden" name="course" value="<?php echo $course_id; ?>" data-type="course" data-done="" data-name="<?php echo esc_attr( get_the_title( $course_id ) ); ?>" />
			<?php endforeach; ?>

			<?php submit_button( __( 'Begin Update', 'coursepress' ) ); ?>
		</form>
	</div>
</div>
