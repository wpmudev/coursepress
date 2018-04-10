<script type="text/template" id="coursepress-shortcodes-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'Shortcodes', 'cp' ); ?></h2>
	</div>

	<div class="cp-box-content cp-shortcode-list cp-odd">
        <h3><?php _e( 'Browse shortcodes', 'cp' ); ?></h3>
		<div class="cp-box">
			<div class="cp-flex">
				<div class="cp-div-flex cp-pad-right">
					<?php $data = new CoursePress_Data_Shortcodes(); ?>
					<?php $shortcode_types = $data->get_shortcode_types(); ?>
					<?php if ( ! empty( $shortcode_types ) ) : ?>
						<ul class="cp-input-group cp-select-list cp-type">
							<?php $i = 0; ?>
							<?php foreach ( $shortcode_types as $key => $type ) : ?>
								<li data-id="<?php echo $key; ?>" <?php echo $i === 0 ? 'class="active"' : ''; ?>><?php echo $type; ?></li>
								<?php $i++; ?>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
				<div class="cp-div-flex cp-pad-left">
					<?php $shortcode_sub_types = $data->get_shortcode_sub_types(); ?>
					<?php if ( ! empty( $shortcode_sub_types ) ) : ?>
						<?php $i = 0; ?>
						<?php foreach ( $shortcode_sub_types as $parent => $child ) : ?>
							<ul class="cp-input-group cp-select-list cp-sub-type <?php echo $i > 0 ? 'inactive' : ''; ?>" id="<?php echo $parent; ?>">
								<?php $j = 0; ?>
								<?php foreach ( $child as $key => $type ) : ?>
									<li data-id="<?php echo $key; ?>" <?php echo $j === 0 ? 'class="active"' : ''; ?>><?php echo $type; ?></li>
									<?php $j++; ?>
								<?php endforeach; ?>
							</ul>
							<?php $i++; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<?php $details = $data->get_shortcode_details(); ?>
	<?php $i = 0; ?>
	<?php foreach ( $details as $id => $values ) : ?>
		<div class="cp-box-content cp-shortcode-details <?php echo $i !== 0 ? 'inactive' : ''; ?>" id="<?php echo $id; ?>">
            <h3 class="cp-box-header"><?php echo $values['title']; ?></h3>
			<div class="cp-box cp-sep">
				<p class="description"><?php echo $values['description']; ?></p>
				<p class="cp-usage-label"><?php _e( 'USAGE EXAMPLE', 'cp' ); ?>:</p>
				<p class="cp-code"><?php echo $data->esc_shortcodes( $values['usage'] ); ?></p>
				<?php echo isset( $values['add_info'] ) ? $values['add_info'] : ''; ?>
			</div>
			<?php if ( ! empty( $values['required_attr'] ) ) : ?>
				<div class="cp-box cp-sep">
					<h3 class="label"><?php _e( 'Required attributes', 'cp' ); ?>:</h3>
					<table class="cp-shortcode-table">
						<tbody>
						<?php foreach ( $values['required_attr'] as $attr ) : ?>
							<tr>
								<td><span class="cp-code"><?php echo $attr['attr']; ?></span></td>
								<td>
									<?php echo isset( $attr['description'] ) ? $attr['description'] : ''; ?>
									<?php if ( ! empty( $attr['options'] ) ) : ?>
										<p class="cp-attr-sub-label"><?php _e( 'Options', 'cp' ); ?></p>
										<div class="cp-code-box">
											<p><?php echo esc_html( $attr['options'] ); ?></p>
										</div>
									<?php endif; ?>
									<?php if ( ! empty( $attr['default'] ) ) : ?>
										<p class="cp-attr-sub-label"><?php _e( 'Default', 'cp' ); ?></p>
										<div class="cp-code-box">
											<p><?php echo esc_html( $attr['default'] ); ?></p>
										</div>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php endif; ?>
				<?php if ( ! empty( $values['optional_attr'] ) ) : ?>
					<div class="cp-box">
						<h3 class="label"><?php _e( 'Optional Attributes', 'cp' ); ?>:</h3>
						<table class="cp-shortcode-table">
							<tbody>
							<?php foreach ( $values['optional_attr'] as $attr ) : ?>
								<tr>
									<td><span class="cp-code"><?php echo $attr['attr']; ?></span></td>
									<td>
										<?php echo isset( $attr['description'] ) ? $attr['description'] : ''; ?>
										<?php if ( ! empty( $attr['options'] ) ) : ?>
											<p class="cp-attr-sub-label"><?php _e( 'Options', 'cp' ); ?></p>
											<div class="cp-code-box">
												<p><?php esc_html( $attr['options'] ); ?></p>
											</div>
										<?php endif; ?>
										<?php if ( ! empty( $attr['default'] ) ) : ?>
											<p class="cp-attr-sub-label"><?php _e( 'Default', 'cp' ); ?></p>
											<div class="cp-code-box">
												<p><?php echo esc_html( $attr['default'] ); ?></p>
											</div>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>
		<?php $i++; ?>
	<?php endforeach; ?>

</script>