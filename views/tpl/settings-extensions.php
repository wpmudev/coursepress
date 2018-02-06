<script type="text/template" id="coursepress-extensions-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'Extensions', 'cp' ); ?></h2>
	</div>

    <div class="cp-box-content">
        <table class="coursepress-extension-table">
            <thead>
                <tr>
                    <th><?php _e( 'Extension', 'cp' ); ?></th>
                    <th><?php _e( 'Source', 'cp' ); ?></th>
                    <th><?php _e( 'Action', 'cp' ); ?></th>
                    <th><?php _e( 'Use to sell courses', 'cp' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $extensions as $id => $extension ) { ?>
                <tr id="extension-row-<?php echo esc_attr( $id ); ?>">
                    <td><?php echo $extension['name']; ?></td>
                    <td><?php echo $extension['source_info']; ?></td>
                    <td class="action"
data-extension="<?php echo esc_attr( $id ); ?>"
data-active="<?php echo esc_attr( $extension['is_active']? 'yes':'no' ); ?>"
data-installed="<?php echo esc_attr( $extension['is_installed']? 'yes':'no' ); ?>"
data-nonce="<?php echo esc_attr( $extension['nonce'] ); ?>"
>
	                    <?php if ( $extension['is_active'] ) : ?>
                            <a href="<?php echo admin_url( 'plugins.php' ); ?>" class="cp-btn cp-btn-active"><?php _e( 'Deactivate', 'cp' ); ?></a>
	                    <?php elseif ( $extension['is_installed'] ) : ?>
                            <a href="<?php echo admin_url( 'plugins.php' ); ?>" class="cp-btn cp-bordered-btn"><?php _e( 'Activate', 'cp' ); ?></a>
	                    <?php elseif ( ! $extension['is_installed'] && $id === 'marketpress' ) : ?>
                            <a href="<?php echo $extension['link']; ?>" class="cp-btn cp-bordered-btn"><?php _e( 'Install', 'cp' ); ?></a>
                        <?php else : ?>
                            <label>
                                <?php _e( 'Not installed', 'cp' ); ?>
                            </label>
                        <?php endif; ?>
                    </td>
                    <td>
                        <label>
                        <input type="checkbox" name="extensions" value="<?php echo $id; ?>" {{_.checked('<?php echo $id; ?>', extensions )}} class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                        </label>
                    </td<
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <?php foreach ( $extensions as $id => $extension ) : ?>
        <?php if ( $extension['is_active'] ) : ?>
            <div id="extension-<?php echo $id; ?>" class="cp-box-content"></div>
        <?php endif; ?>
    <?php endforeach; ?>
</script>
