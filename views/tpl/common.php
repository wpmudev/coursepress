<script type="text/template" id="coursepress-add-image-tpl">
    <div class="cp-add-image-box">
        <div class="cp-thumbnail"></div>
        <div class="cp-div-url">
            <input type="hidden" name="{{name}}_thumbnail_id" class="cp-thumbnail-id" value="{{thumbnail_id}}" />
            <input type="text" class="cp-image-url" placeholder="<?php _e( 'Paste URL or browse uploaded images', 'cp' ); ?>" />
            <button type="button" class="cp-btn cp-btn-clear"><?php _e( 'Clear', 'cp' ); ?></button>
        </div>
        <button type="button" class="cp-btn cp-btn-default cp-btn-browse"><?php _e( 'Browse', 'cp' ); ?></button>
    </div>
</script>