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
                    <th><?php _e( 'Active?', 'cp' ); ?></th>
                </tr>
            </thead>
            <tbody>

            <?php foreach ( $extensions as $id => $extension ) : ?>
                <?php if ( $extension['is_active'] ) : ?>
                    <tr>
                        <td><?php echo $extension['name']; ?></td>
                        <td><?php echo $extension['source_info']; ?></td>
                        <td>
                            <label>
                                <input type="checkbox" name="extensions" value="<?php echo $id; ?>" {{_.checked('<?php echo $id; ?>', extensions )}} class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                            </label>
                        </td>
                    </tr>
                <?php elseif ( $id === 'marketpress' && ! $extension['is_installed'] ) : ?>
                    <tr>
                        <td><?php echo $extension['name']; ?></td>
                        <td><?php echo $extension['source_info']; ?></td>
                        <td>
                            <a href="<?php echo $extension['link']; ?>" class="cp-btn cp-bordered-btn"><?php _e( 'Install', 'cp' ); ?></a>
                        </td>
                    </tr>
                <?php endif; ?>

            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php foreach ( $extensions as $id => $extension ) : ?>
        <?php if ( $extension['is_active'] ) : ?>
            <div id="extension-<?php echo $id; ?>" class="cp-box-content"></div>
        <?php endif; ?>
    <?php endforeach; ?>
</script>