<script type="text/template" id="coursepress-capabilities-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'Instructor Capabilities', 'cp' ); ?></h2>
	</div>

	<div class="cp-box-content cp-shortcode-list">
		<div class="box-label-area">
			<h2 class="label"><?php _e( 'Browse capabilities', 'cp' ); ?></h2>
		</div>
		<div class="cp-box">
			<div class="cp-flex">
				<div class="cp-div-flex cp-pad-right">
					<ul class="cp-input-group cp-select-list cp-capabilities">
						<li data-id="cp-cap-general" class="active"><?php _e( 'General', 'cp' ); ?></li>
						<li data-id="cp-cap-courses"><?php _e( 'Courses', 'cp' ); ?></li>
						<li data-id="cp-cap-units"><?php _e( 'Units', 'cp' ); ?></li>
						<li data-id="cp-cap-instructors"><?php _e( 'Instructors', 'cp' ); ?></li>
						<li data-id="cp-cap-students"><?php _e( 'Students', 'cp' ); ?></li>
						<li data-id="cp-cap-notifications"><?php _e( 'Notifications', 'cp' ); ?></li>
						<li data-id="cp-cap-discussions"><?php _e( 'Discussions', 'cp' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<?php $options = apply_filters( 'coursepress_settings-capabilities', array() ); ?>
	<?php $i = 0; ?>
	<?php foreach ( $options as $option ) : ?>
		<div class="cp-box-content cp-caps-fields <?php echo $i > 0 ? 'inactive' : ''; ?>" id="<?php echo $option['id']; ?>">
            <div class="cp-box cp-sep">
                <h2 class="label"><?php echo $option['title']; ?></h2>
                <?php if ( isset( $option['description'] ) ) : ?>
                    <?php printf( '<p class="description">%s</p>', $option['description'] ); ?>
                <?php endif; ?>
            </div>

			<div class="box-inner-content">
				<?php foreach ( $option['fields'] as $key => $data ) : ?>
					<div class="flex-half option option-<?php esc_attr_e( $key ); ?>">
						<?php if ( isset( $data['label'] ) ) : ?>
							<?php printf( '<h3>%s</h3>', $data['label'] ); ?>
						<?php endif; ?>
						<?php $data['name'] = $key; ?>
						<?php lib3()->html->element( $data ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php $i++; ?>
	<?php endforeach; ?>
</script>