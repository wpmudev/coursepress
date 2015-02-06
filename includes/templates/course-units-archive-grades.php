<?php
//echo do_shortcode( '[course_breadcrumbs type="unit_archive"]' );
do_shortcode( '[course_units_loop]' ); //required for getting unit results
?>

<?php
echo do_shortcode( '[course_unit_archive_submenu]' );
?>
<h2><?php _e( 'Course Grades', 'cp' ); ?></h2>

<div class="units-archive">
	<ul class="units-archive-list">
		<?php if ( have_posts() ) { ?>
			<?php
			$grades = 0;
			$units  = 0;
			while ( have_posts() ) : the_post();
				$grades = $grades + do_shortcode( '[course_unit_details field="student_unit_grade" unit_id="' . get_the_ID() . '"]' );
				?>
				<li>
					<span class="percentage"><?php echo do_shortcode( '[course_unit_details field="student_unit_grade" unit_id="' . get_the_ID() . '" format="true"]' ); ?></span><a href="<?php echo do_shortcode( '[course_unit_details field="permalink" unit_id="' . get_the_ID() . '"]' ); ?>" rel="bookmark"><?php the_title(); ?></a>
					<?php if ( do_shortcode( '[course_unit_details field="input_modules_count"]' ) > 0 ) { ?>
						<span class="unit-archive-single-module-status"><?php echo do_shortcode( '[course_unit_details field="student_module_responses"]' ); ?> <?php _e( 'of', 'cp' ); ?> <?php echo do_shortcode( '[course_unit_details field="input_modules_count"]' ); ?> <?php _e( 'elements completed', 'cp' ); ?></span>
					<?php } else { ?>
						<span class="unit-archive-single-module-status read-only-module"><?php _e( 'Read-only', 'cp' ); ?></span>
					<?php } ?>
				</li>
				<?php
				$units ++;
			endwhile;
		} else {
			?>
			<h1 class="zero-course-units"><?php _e( "0 units in the course currently. Please check back later.", "cp" ); ?></h1>
		<?php
		}
		?>
	</ul>

	<div class="total_grade"><?php echo apply_filters( 'coursepress_grade_caption', __( 'TOTAL:', 'cp' ) ); ?> <?php echo esc_html( apply_filters( 'coursepress_grade_total', ( $grades > 0 ? ( round( $grades / $units, 0 ) ) : 0 ) . '%' ) ); ?></div>

</div>