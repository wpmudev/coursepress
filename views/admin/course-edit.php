<?php
/**
 * @var string $page_title
 * @var int $course_id
 * @var array $menu_list
 */
?>
<div class="wrap coursepress-wrap coursepress-edit">
	<h1 class="wp-heading-inline">
		<?php if ( ! empty( $course_id ) ) : ?>
			<span class="course-tag"><?php esc_html_e( 'Course Name', 'cp' ); ?></span>
		<?php endif; ?>

		<?php echo $page_title; ?>
	</h1>

	<div id="course-edit-template" class="coursepress-page course-steps-page">
		<div class="cp-menu-items course-menu-list">
			<ul class="course-menu">
				<?php foreach ( $menu_list as $key => $label ) : ?>
					<li class="cp-menu-item step step-<?php echo $key; ?>" data-step="<?php echo esc_attr( $key ); ?>">
						<span class="menu-label"><?php echo esc_html( $label ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="course-content">
			<?php  foreach ( $menu_list as $key => $label ) : ?>
				<div id="<?php echo esc_attr( $key ); ?>" class="course-content-tab"></div>
			<?php endforeach; ?>

			<div class="course-footer">
				<button type="button" class="cp-btn cp-btn-default step-back">
					<?php esc_html_e( 'Back', 'cp' ); ?>
				</button>

                <button type="button" class="cp-btn cp-btn-active step-save">
                    <i class="fa fa-circle-o-notch fa-spin"></i>
					<?php esc_html_e( 'Save', 'cp' ); ?>
                </button>

                <button type="button" class="cp-btn cp-btn-active step-next">
                    <i class="fa fa-circle-o-notch fa-spin"></i>
					<?php esc_html_e( 'Save and Continue', 'cp' ); ?>
                </button>
			</div>
		</div>
	</div>
</div>