<script type="text/template" id="modal-template">
	<div class="enrollment-modal-container"></div>
</script>
<script type="text/template" id="modal-next-step-is-mandatory" data-type="modal-step" data-modal-action="mandatory">
	<div class="bbm-modal__topbar">
		<h3 class="bbm-modal__title"><?php _e( 'This is a REQUIRED module.', 'cp' ); ?></h3>
	</div>
	<div class="bbm-modal__section">
		<?php echo empty( $message_required_modules ) ? '' : $message_required_modules; ?>
	</div>
	<div class="bbm-modal__bottombar"><a class="cancel-link"><?php _e( 'Close', 'cp' ); ?></a></div>
</script>