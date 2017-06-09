<?php
/**
 * Template for course instructor bio.
 *
 * @since 3.0
 * @package CoursePress
 *
 * @var $CoursePress_Instructor CoursePress_User
 */
global $CoursePress_Instructor;

$instructor = $CoursePress_Instructor;
?>

<div class="instructor-info">
	<h2 class="instructor-title"><?php echo $instructor->get_name(); ?></h2>

	<div class="instructor-bio">
		<div class="instructor-avatar">
			<?php echo $instructor->get_avatar( 72 ); ?>
		</div>

        <div class="instructor-description">
			<?php echo $instructor->get_description(); ?>
        </div>
	</div>
</div>