<div class="wrap coursepress-wrap">
<?php if ( isset( $title ) && ! empty( $title ) ) : ?>
	<?php printf( '<h1 class="wp-heading-inline">%s</h1>', $title ); ?>
<?php endif; ?>
<div class="notice notice-error">
	<p>
		<?php if ( ! empty( $message ) ) : ?>
			<?php echo $message; ?>
		<?php else : ?>
			<?php esc_html_e( 'We\'re sorry, but something went wrong.', 'cp' ); ?>
		<?php endif; ?>
	</p>
</div>
