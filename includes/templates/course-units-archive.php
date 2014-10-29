<?php
$course_id			 = do_shortcode( '[get_parent_course_id]' );
$course_id			 = (int) $course_id;
$progress			 = do_shortcode( '[course_progress course_id="' . $course_id . '"]' );
do_shortcode( '[course_units_loop]' ); //required for getting unit results
?>

<?php
echo do_shortcode( '[course_unit_archive_submenu]' );
$complete_message	 = '';
if ( 100 == (int) $progress ) {
	$complete_message = '<div class="unit-archive-course-complete cp-wrap"><i class="fa fa-check-circle"></i> ' . __( 'Course Complete', 'cp' ) . '</div>';
}

?>
<h2><?php _e( 'Course Units', 'cp' );
echo ' ' . $complete_message; ?></h2>
<div class="units-archive">
    <ul class="units-archive-list">
		<?php if ( have_posts() ) { ?>
			<?php
			while ( have_posts() ) : the_post();
				$additional_class	 = '';
				$additional_li_class = '';

				if ( do_shortcode( '[course_unit_details field="is_unit_available"]' ) == false ) {
					$additional_class	 = 'locked-unit';
					$additional_li_class = 'li-locked-unit';
				}
				?>
				<li class="<?php echo esc_attr( $additional_li_class ); ?>">
					<div class='<?php echo esc_attr( $additional_class ); ?>'></div>
					<a href="<?php echo esc_url( do_shortcode( '[course_unit_details field="permalink" last_visited="true" unit_id="' . get_the_ID() . '"]' ) ); ?>" rel="bookmark"><?php the_title().' '.(get_post_status() !== 'publish' && cp_can_see_unit_draft() ? _e(' [DRAFT]', 'cp') :  ''); ?></a><?php echo do_shortcode( '[course_unit_details field="percent" format="true" style="flat"]' ); ?>
				<?php echo do_shortcode( '[module_status format="true" course_id="' . $course_id . '" unit_id="' . get_the_ID() . '"]' ); ?>
				</li>
				<?php
			endwhile;
		} else {
			?>
			<p class="zero-course-units"><?php _e( "0 units in the course currently. Please check back later.", "cp" ); ?></p>
			<?php
		}
		wp_reset_postdata();
        ?>
    </ul>
</div>