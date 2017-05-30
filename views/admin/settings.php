<div class="wrap coursepress-wrap coursepress-settings">
	<h1 class="wp-heading-inline">CoursePress <?php _e( 'Settings', 'cp' ); ?></h1>

	<div class="coursepress-page">
		<div class="step-list course-menu-list">
			<span class="cp-icon cp-icon-md step-icon-bars"><i class="fa fa-bars"></i></span>
			<ul class="course-menu">
				<?php
					$admin_menus = array(
						'general' => __( 'General', 'cp' ),
						'slugs' => __( 'URL Slugs', 'cp' ),
						'emails' => __( 'Emails', 'cp' ),
						'capabilities' => __( 'Capabilities', 'cp' ),
						'certificate' => __( 'Certificate', 'cp' ),
						'shortcodes' => __( 'Shortcodes', 'cp' ),
						'extensions' => __( 'Extensions', 'cp' ),
						'import-export' => __( 'Import/Export', 'cp' ),
					);

					/**
					 * Fire to allow re-populations of CoursePress settings menus.
					 *
					 * @since 3.0
					 * @param array $admin_menus
					 */
					$admin_menus = apply_filters( 'coursepress_admin_settings', $admin_menus );

					foreach ( $admin_menus as $menu_id => $menu_label ) :
				?>
					<li class="step" data-step="<?php echo $menu_id; ?>">
						<span class="menu-label"><?php echo $menu_label; ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="step-content course-content">
			<?php foreach ( $admin_menus as $menu_id => $menu ) : ?>
				<div class="content-tab" id="admin-<?php echo $menu_id; ?>"></div>
			<?php endforeach; ?>
		</div>
	</div>
</div>