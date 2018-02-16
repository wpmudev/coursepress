<div class="wrap coursepress-wrap coursepress-upgrade" id="coursepress-upgrade">
	<h1 class="wp-heading-inline"><?php _e( 'Upgrade', 'cp' ); ?></h1>
    <div class="coursepress-page">
        <div id="progress"></div>
        <a class="button button-default" data-nonce="<?php echo esc_attr( $nonce ); ?>"><?php esc_html_e( 'Upgrade courses', 'cp' ); ?></a>

        <h2><?php esc_html_e( 'Courses to upgrade', 'cp' ); ?></h2>
<ol>
<?php



foreach ( $courses as $course ) {
	$status = $status_class = '';
	if ( isset( $course->cp3_upgraded ) && 'done' === $course->cp3_upgraded ) {
		$status_class = 'done';
		$status = esc_html__( 'upgreded', 'cp' );
	}
	printf( '<li id="course-id-%d" class="status-%s">', esc_attr( $course->ID ), esc_attr( $status_class ) );
	printf( '<span class="status">%s</span> ', $status );
	printf( '<span class="title">%s</span>', esc_html( $course->post_title ) );
	echo '</li>';
}
?>
</ol>
</div>
