<script type="text/template" id="coursepress-unit-list-tpl">
	<div class="tab tabs units-builder-tabs">
		<div class="sticky-wrapper">
			<ul class="sticky-tabs">
				<# _.each( units, function( unit ) { #>
					<li class="coursepress-uv-tab">
						<span class="unit-title">{{unit.post_title}}</span>
					</li>
				<# }) #>
			</ul>
			<div class="sticky-buttons">
				<div class="button button-add-new-unit">
					<i class="fa fa-plus-square"></i>
					<?php _e( 'Add New Unit', 'cp' ); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="tab-content unit-builder-content"></div>
</script>

<script type="text/template" id="coursepress-unit-edit-tpl">
	<div class="unit-header">
		<h3><i class="fa fa-cog"></i> {{unit_title}} <input type="checkbox" data-off="<?php _e( 'Draft', 'cp' ); ?>" data-on="<?php _e( 'Live', 'cp' ); ?>" name="post_status" value="1" /></h3>
	</div>
</script>