<div class="wrap coursepress_wrapper coursepress-export">
	<h2><?php esc_html_e( 'Export', 'coursepress' ); ?></h2>
	<p class="description page-tagline">
		<?php esc_html_e( 'Select courses to export to another site.', 'coursepress' ); ?>
	</p>
	<form method="post" class="has-disabled">
		<?php wp_nonce_field( 'coursepress_export', 'coursepress_export' ); ?>
		<div class="cp-left">
			<p>
				<label>
					<input type="checkbox" class="input-key" name="coursepress[all]" value="1" />
					<?php esc_html_e( 'All courses', 'coursepress' ); ?>
				</label>
			</p>
			<?php
			$per_page = 20;
			$paged = ! empty( $_REQUEST['paged'] ) ? (int) $_REQUEST['paged'] : 1;

			$args = array(
				'post_type' => CoursePress_Data_Course::get_post_type_name(),
				'post_status' => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => $per_page,
				'paged' => $paged,
				'suppress_filters' => true,
			);
			$courses = new WP_Query( $args );

			if ( $courses->have_posts() ):
				while( $courses->have_posts() ): $courses->the_post();
			?>
				<p>
					<label>
						<input type="checkbox" class="input-key" name="coursepress[courses][<?php the_ID(); ?>]" value="<?php the_ID(); ?>" />
						<?php the_title(); ?>
					</label>
				</p>
			<?php
				endwhile;
			endif;
			?>
		</div>
		<div>
			<h3><?php esc_html_e( 'Export Options', 'coursepress' ); ?></h3>
			<div>
				<label>
					<input type="checkbox" name="coursepress[students]" class="input-requiredby" value="1" />
					<?php esc_html_e( 'Include students', 'coursepress' ); ?>
				</label>
				<p class="description">
					<?php esc_html_e( 'Will include course students and their course submission progress.', 'coursepress' ); ?>
				</p>
			</div><br />
			<div>
				<label>
					<input type="checkbox" name="coursepress[comments]" data-required-imput="coursepress[students]" disabled="disabled" value="1" />
					<?php esc_html_e( 'Include thread/comments', 'coursepress' ); ?>
				</label>
				<p class="description">
					<?php esc_html_e( 'Will include comments from Course forum and discussion modules.', 'coursepress' ); ?>
				</p>
			</div>
		</div>
		<div class="cp-right">
		<?php
			// Show paginate
			echo CoursePress_Helper_UI::admin_paginate( $paged, $courses->found_posts, $per_page, '', __( 'Course', 'coursepress' ) );
		?>
		</div>
		<div class="clear cp-submit">
			<?php submit_button( __( 'Export Courses', 'coursepress' ), 'button-primary disabled' ); ?>
		</div>
	</form>
</div>
