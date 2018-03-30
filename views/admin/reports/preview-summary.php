<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course CoursePress_Course
 */
?>
<div class="wrap coursepress-wrap" id="coursepress-reports-list">
    <h1 class="wp-heading-inline"><?php _e( 'Report', 'cp' ); ?></h1>
    <h2><?php echo $course->post_title; ?></h2>
    <div class="coursepress-page coursepress-page-report">
        <table class="coursepress-table coursepress-table-summary">
<thead>
<tr>
<?php
		$style = sprintf(
			'font-size: 4mm; background-color:%s;color:%s;',
			$colors['footer_bg'],
			$colors['footer']
		);
		printf( '<th style="%s">%s</th>', esc_attr( $style ), __( 'Student', 'cp' ) );
		printf( '<th style="%s">%s</th>', esc_attr( $style ), __( 'Responses', 'cp' ) );
		printf( '<th style="%s">%s</th>', esc_attr( $style ), __( 'Average response grade', 'cp' ) );
		printf( '<th style="%s">%s</th>', esc_attr( $style ), __( 'Total Average', 'cp' ) );
?>
</tr>
</thead>
<tbody>
<?php echo $content; ?>
</tbody>
</table>
    </div>
</div>
