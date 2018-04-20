<div class="wrap coursepress_wrapper coursepress-import">
	<h2><?php esc_html_e( 'Import', 'coursepress' ); ?></h2>
	<p class="description page-tagline">
		<?php esc_html_e( 'Upload your exported courses to import here.', 'coursepress' ); ?>
	</p>

	<form method="post" enctype="multipart/form-data" class="has-disabled">
		<?php wp_nonce_field( 'coursepress_import', 'coursepress_import' ); ?>
		<p>
			<input type="file" name="import" class="input-key" />
		</p>
		<h3><?php esc_html_e( 'Import Options', 'coursepress' ); ?></h3>
		<div>
			<label>
				<input type="checkbox" name="coursepress[replace]" value="1" />
				<?php esc_html_e( 'Replace course if exists.', 'coursepress' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Courses with the same title will be automatically replaced by the new one.', 'coursepress' ); ?>
			</p>
		</div><br />
		<div>
			<label>
				<input type="checkbox" name="coursepress[students]" class="input-requiredby" value="1" />
				<?php esc_html_e( 'Include course students', 'coursepress' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Students listing must also included in your export for this to work.', 'coursepress' ); ?>
			</p>
		</div><br />
		<div>
			<label>
				<input type="checkbox" name="coursepress[comments]" data-required-imput="coursepress[students]" disabled="disabled" value="1" />
				<?php esc_html_e( 'Include course thread/comments', 'coursepress' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Comments listing must also included in your export for this to work.', 'coursepress' ); ?>
			</p>
		</div>		
		<div class="cp-submit">
			<?php submit_button( __( 'Upload file and import', 'coursepress' ), 'button-primary disabled' ); ?>
		</div>
	</form>
</div>
