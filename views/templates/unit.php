<?php
/**
 * Private template use to render course unit.
 *
 * @since 3.0
 * @package CoursePress
 */
$unit = coursepress_get_unit();

if ( $unit->__get( 'use_description' ) ) : ?>
	<div class="unit-description">
		<?php $unit->get_description(); ?>
	</div>
<?php endif; ?>
