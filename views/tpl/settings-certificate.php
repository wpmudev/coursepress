<script type="text/template" id="coursepress-certificate-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'Certificate', 'cp' ); ?></h2>
	</div>
	<div class="cp-content">
<?php
$option_name = sprintf( 'coursepress_%s', basename( __FILE__, '.php' ) );
$options = apply_filters( $option_name, array() );
/**
 * print options
 */
foreach ( $options as $option_key => $option ) {
	$classes = 'box-inner-content';
	printf( '<div class="cp-box-content cp-box-%s">', esc_attr( $option_key ) );
	if ( ! empty( $option['title'] ) || ! empty( $option['description'] ) ) {
		echo '<div class="box-label-area">';
		if ( ! empty( $option['title'] ) ) {
			printf(
				'<h2 class="label">%s</h2>',
				$option['title']
			);
		}
		if ( isset( $option['description'] ) ) {
			printf( '<p class="description">%s</p>', $option['description'] );
		}
		echo '</div>';
	} else {
		$classes .= ' box-inner-full';
	}
	printf( '<div class="%s">', esc_attr( $classes ) );
	/**
	 * flex wrapper: semaphore
	 */
	$is_flex = false;
	foreach ( $option['fields'] as $key => $data ) {
		/**
		 * flex wrapper: open & close
		 */
		if ( isset( $data['flex'] ) && true === $data['flex'] ) {
			if ( ! $is_flex ) {
				echo '<div class="flex">';
			}
			$is_flex = true;
		} else if ( true === $is_flex ) {
			echo '</div>';
			$is_flex = false;
		}
		$class = isset( $data['wrapper_class'] )? $data['wrapper_class']:'';
		printf(
			'<div class="option option-%s option-%s %s">',
			esc_attr( sanitize_title( $key ) ),
			esc_attr( $data['type'] ),
			esc_attr( $class )
		);
		if ( isset( $data['label'] ) ) {
			printf( '<h3>%s</h3>', $data['label'] );
		}
		$data['name'] = $key;
		lib3()->html->element( $data );
		echo '</div>';
	}
	/**
	 * flex wrapper: close
	 */
	if ( $is_flex ) {
		echo '</div>';
	}
	echo '</div>';
	echo '</div>';
}
?>
	</div>
</script>
