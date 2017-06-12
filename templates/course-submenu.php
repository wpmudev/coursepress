<?php
/**
 * Course submenu template.
 *
 * @since 3.0
 * @package CoursePress
 */
?>
<div class="submenu-main-container course-submenu-container">
	<ul id="submenu-main" class="submenu course-submenu">
		<?php foreach ( coursepress_get_course_submenu() as $menu_id => $menu_item ) : ?>

			<li class="menu-item menu-item-<?php echo $menu_id; ?>">
				<a href="<?php echo esc_url_raw( $menu_item['url'] ); ?>"><?php echo $menu_item['label']; ?></a>
			</li>
		
		<?php endforeach; ?>
	</ul>
</div>
