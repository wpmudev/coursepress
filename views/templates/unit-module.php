<?php
/**
 * Private template use to render course unit modules.
 *
 * @since 3.0
 * @package CoursePress
 */
global $_module_id;

$unit = coursepress_get_unit();
$steps = $unit->get_steps( true, true, $_module_id );

if ( $unit->__get( 'use_description' ) ) : ?>
	<div class="unit-description">
		<?php $unit->get_description(); ?>
	</div>
<?php endif; ?>

<?php if ( $steps ) :
    foreach ( $steps as $step ) :
        echo $step->template();
    endforeach;
endif; ?>
