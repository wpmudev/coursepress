<script type="text/template" id="coursepress-extensions-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'Extensions', 'cp' ); ?></h2>
	</div>

    <div class="cp-box-content">
        <table class="coursepress-table">
            <thead>
                <tr>
                    <th><?php _e( 'Extension', 'cp' ); ?></th>
                    <th><?php _e( 'Source', 'cp' ); ?></th>
                    <th><?php _e( 'Active?', 'cp' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
            /**
             * Fire to get all available extensions.
             *
             * @since 3.0
             * @param array $extensions
             */
            $extensions = apply_filters( 'coursepress_extensions', array() );

            if ( ! empty( $extensions ) ) :
                foreach ( $extensions as $id => $extension ) : ?>

            <tr>
                <td><?php echo $extension['name']; ?></td>
                <td><?php echo $extension['source_info']; ?></td>
                <td>
                    <label>
                        <input type="checkbox" name="extension[<?php echo $id; ?>]" class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                    </label>
                </td>
            </tr>

            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</script>