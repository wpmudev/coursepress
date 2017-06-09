<?php
/**
 * The template use for instructor profile.
 *
 * @since 3.0
 * @package CoursePress
 *
 * @var $CoursePress_Instructor CoursePress_Instructor
 */
global $CoursePress_Instructor, $post;

$instructor = $CoursePress_Instructor;
$courses = $instructor->get_instructed_courses( true, false );

get_header(); ?>

	<div class="wrap coursepress-wrap">
		<div class="container">
			<main class="site-main" role="main">
				<header class="page-header">
					<h1 class="page-title"><?php _e( 'Instructor', 'cp' ); ?></h1>

                    <?php
                        get_template_part( 'template-parts/course/instructor-bio' );
                    ?>
				</header>

                <div class="courses">
                    <h2 class="sub-title coursepress-sub-title"><?php printf( __( '%s\'s Courses', 'cp' ), $instructor->get_name() ); ?></h2>

                    <?php if ( empty( $courses ) ) : ?>
                        <p class="description"><?php _e( 'No courses found.', 'cp' ); ?></p>
                    <?php else : ?>

                        <?php
                            foreach ( $courses as $post ) :
                                setup_postdata( $post );

                                get_template_part( 'template-parts/course/content' );
                            endforeach;

                            wp_reset_postdata();
                        ?>

                    <?php endif; ?>
                </div>
			</main>
		</div>
	</div>

<?php get_footer();
