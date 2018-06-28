<?php
/**
 * Course submenu template.
 *
 * @since 3.0
 * @package CoursePress
 */
?>
<div class="submenu-main-container course-submenu-container">
<button class="course-submenu-toggle" aria-controls="submenu-main" aria-expanded="false"
data-toggle-off="<?php esc_attr_e( _x( '&#x25bc;', 'down arrow for course submenu', 'cp' ) ); ?>"
data-toggle-on="<?php esc_attr_e( _x( '&#x25b2;', 'up arrow for course submenu', 'cp' ) ); ?>"
><?php
_ex( '&#x25bc;', 'bottom arrow for course submenu', 'cp' );
?></button>
	<ul id="submenu-main" class="submenu course-submenu">
	<?php
	$menu = coursepress_get_course_submenu();
	if ( $menu ) {
		foreach ( $menu as $menu_id => $menu_item ) :
		?>
			<li class="<?php echo esc_attr( implode( ' ', $menu_item['classes'] ) ); ?>">
				<a href="<?php echo esc_url( $menu_item['url'] ); ?>"><?php echo esc_html( $menu_item['label'] ); ?></a>
			</li>
		<?php
		endforeach;
	}
	?>
	</ul>
</div>
