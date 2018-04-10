<?php
/**
 * The template use for course archive.
 *
 * @since 3.0
 * @package CoursePress
 */
get_header();
?>

	<div class="coursepress-wrap">
		<div class="container">
			<div class="content-area">
                <header class="page-header">
                    <h1 class="page-title cp-flex">
                        <span class="gravatar"><?php echo get_avatar( $CoursePress_Instructor->user_email, 64 ); ?></span>
                        <span class="display_name"><?php echo esc_html( $CoursePress_Instructor->display_name ); ?></span>
                    </h1>
<?php if ( isset( $CoursePress_Instructor->description ) && ! empty( $CoursePress_Instructor->description ) ) { ?>
<div class="description"><?php echo wpautop( $CoursePress_Instructor->description ); ?></div>
<?php } ?>
				</header>

<?php

$courses = $CoursePress_Instructor->get_instructed_courses();
if ( $courses ) {
	foreach ( $courses as $course ) {
		$post = get_post( $course->ID );
		setup_postdata( $post );
		/**
		 * To override this template in your theme or to a child-theme,
		 * create a template `content-course.php` and it will be loaded instead.
		 *
		 * @since 3.0
		 */
		coursepress_get_template( 'content', 'course' );
	}
}
				?>
			</div>
		</div>
	</div>
<?php
get_footer();
