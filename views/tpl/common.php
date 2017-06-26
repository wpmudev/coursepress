<script type="text/template" id="coursepress-add-image-tpl">
    <div class="cp-flex cp-add-image-box">
        <div class="cp-thumbnail"></div>
        <div class="cp-div-url">
            <input type="hidden" name="{{name}}_thumbnail_id" class="cp-thumbnail-id" value="{{thumbnail_id}}" />
            <div class="cp-input-clear">
                <input type="text" class="cp-image-url" value="{{value}}" placeholder="<?php _e( 'Paste URL or browse uploaded images', 'cp' ); ?>" />
                <button type="button" class="cp-btn cp-btn-clear"><?php _e( 'Clear', 'cp' ); ?></button>
            </div>
        </div>
        <button type="button" class="cp-btn cp-btn-default cp-btn-browse"><?php _e( 'Browse', 'cp' ); ?></button>
    </div>
</script>
<script type="text/template" id="coursepress-tokens">
    <div class="cp-alert cp-alert-info">
        <p><?php _e( 'These codes will be replaced with actual data:', 'cp' ); ?></p>
    </div>
</script>