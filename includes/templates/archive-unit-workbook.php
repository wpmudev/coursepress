<?php
/**
 * The units archive / grades template file
 *
 * @package CoursePress
 */
global $coursepress;
$course_id = do_shortcode( '[get_parent_course_id]' );
$course_id = (int) $course_id;
$progress  = do_shortcode( '[course_progress course_id="' . $course_id . '"]' );

//redirect to the parent course page if not enrolled
$coursepress->check_access( $course_id );

add_thickbox();
?>

<?php

if ( 100 == ( int ) $progress ) {
	$complete_message         = '<span class="unit-archive-course-complete cp-wrap"><i class="fa fa-check-circle"></i> ' . __( 'Course Complete', 'cp' ) . '</span>';
	$workbook_course_progress = '';
} else {
	$complete_message         = '';
	$workbook_course_progress = '<span class="workbook-course-progress">' . __( 'Course progress: ', 'cp' ) . esc_html( $progress ) . '%' . '</span>';
}

?>

<?php echo do_shortcode( '[course_unit_archive_submenu]' ); ?>

	<h2 class="workbook-title">
		<?php echo __( 'Workbook', 'cp' );
		echo $workbook_course_progress;
		echo $complete_message; ?>
	</h2>

	<div class="clearfix"></div>

<?php
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$criteria           = Unit::get_module_completion_data( get_the_ID() );
		$input_module_count = count( $criteria['all_input_ids'] );
		$has_assessable     = $input_module_count > 0 ? true : false;
		?>
		<div class="workbook_units cp-wrap">
			<div class="unit_title">
				<h3><?php the_title(); ?>
					<span><?php _e( 'Unit progress: ', 'cp' ); ?> <?php echo do_shortcode( '[course_unit_progress course_id="' . $course_id . '" unit_id="' . get_the_ID() . '"]' ); ?>
						%</span>
				</h3>
			</div>
			<?php if ( $has_assessable ) { ?>
				<div class="accordion-inner">
					<?php
					echo do_shortcode( '[student_workbook_table]' );
					?>
				</div>
			<?php } else { ?>
				<div class="accordion-inner">
					<div class="zero-inputs"><?php _e( 'There are no activities to complete in this unit.', 'cp' ); ?></div>
				</div>
			<?php } ?>
		</div>
	<?php
	} // While
} else {
	?>
	<div class="zero-courses"><?php _e( '0 Units in the course', 'cp' ); ?></div>
<?php
}
?>