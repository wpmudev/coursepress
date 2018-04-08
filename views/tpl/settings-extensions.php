<script type="text/template" id="coursepress-extensions-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php esc_html_e( 'Extensions', 'cp' ); ?></h2>
	</div>

    <div class="cp-box-content">
        <table class="coursepress-extension-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Extension', 'cp' ); ?></th>
                    <th><?php esc_html_e( 'Source', 'cp' ); ?></th>
                    <th><?php esc_html_e( 'Action', 'cp' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $extensions as $id => $extension ) { ?>
                <tr id="extension-row-<?php echo esc_attr( $id ); ?>">
                    <td>
						<?php echo esc_html( $extension['name'] ); ?>
					</td>
    <td>
	<?php
	if ( isset( $extension['wpmu'] ) && $extension['wpmu'] && isset( $extension['soruce_link'] ) ) {
		printf(
			'<a href="%s" target="_blank">%s</a>',
			esc_url( $extension['soruce_link'] ),
			esc_html( $extension['source_info'] )
		);
	} else {
		echo esc_html(  $extension['source_info'] );
	}
	?>
	</td>
                    <td class="action"
data-extension="<?php echo esc_attr( $id ); ?>"
data-active="<?php echo esc_attr( $extension['is_active']? 'yes':'no' ); ?>"
data-installed="<?php echo esc_attr( $extension['is_installed']? 'yes':'no' ); ?>"
data-nonce="<?php echo esc_attr( $extension['nonce'] ); ?>"
>
                        <?php
						if ( $extension['is_active'] ) {
							$aria = sprintf(
								_x( 'Deactivate %s', 'deactivate extension (plugin name in placeholder)', 'cp' ),
								$extension['name']
							);
?>
                            <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="cp-btn cp-btn-active" aria-label="<?php echo esc_attr( $aria ); ?>"><?php esc_html_e( 'Deactivate', 'cp' ); ?></a>
                        <?php
						} elseif ( $extension['is_installed'] ) {
						$aria = sprintf(
							_x( 'Activate %s', 'activate extension (plugin name in placeholder)', 'cp' ),
							$extension['name']
						);
	?>
                            <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="cp-btn cp-bordered-btn" aria-label="<?php echo esc_attr( $aria ); ?>"><?php esc_html_e( 'Activate', 'cp' ); ?></a>
                        <?php
						} elseif ( ! $extension['is_installed'] && isset( $extension['wpmu'] ) && $extension['wpmu'] ) {
						$aria = sprintf(
							_x( 'Install %s', 'install extension (plugin name in placeholder)', 'cp' ),
							$extension['name']
						);
?>
                            <a href="<?php echo esc_attr( $extension['link'] ); ?>" class="cp-btn cp-bordered-btn" aria-label="<?php echo esc_attr( $aria ); ?>"><?php esc_html_e( 'Install', 'cp' ); ?></a>
                        <?php } else { ?>
                            <label><?php esc_html_e( 'Not installed', 'cp' ); ?></label>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <?php foreach ( $extensions as $id => $extension ) : ?>
        <div id="extension-<?php echo esc_attr( $id ); ?>" class="cp-box-content <?php echo esc_attr( $extension['is_active']? '':'hidden' ); ?>" data-loaded="no"></div>
    <?php endforeach; ?>
</script>
