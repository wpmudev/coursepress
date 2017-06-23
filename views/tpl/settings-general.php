<script type="text/template" id="coursepress-general-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'General', 'cp' ); ?></h2>
	</div>

    <?php
    /**
     * Fire to get all options.
     *
     * @since 3.0
     * @param array $extensions
     */
    $option_name = sprintf( 'coursepress_%s', basename( __FILE__, '.php' ) );
    $options = apply_filters( $option_name, array() );
    foreach ( $options as $option ) : ?>

    <div class="cp-box-content cp-sep">
        <div class="box-label-area">
            <label class="label"><?php echo $option['title']; ?></label>
            <?php
                if ( isset( $option['description'] ) ) :
                    printf( '<p class="description">%s</p>', $option['description'] );
                endif;
            ?>
        </div>
        <div class="box-inner-content">
        <?php foreach ( $option['fields'] as $key => $data ) : ?>
	        <div class="cp-box option option-<?php esc_attr_e( $key ); ?>">
            <?php
                if ( isset( $data['label'] ) ) :
                    printf( '<label class="label">%s</label>', $data['label'] );
                endif;

                $data['name'] = $key;
                lib3()->html->element( $data );
            ?>
	        </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</script>
