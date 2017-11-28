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

<script type="text/template" id="coursepress-add-video-tpl">
    <div class="cp-flex cp-add-video-box">
        <div class="cp-div-url">
            <div class="cp-input-clear">
                <input type="text" class="cp-video-url" value="{{value}}" placeholder="<?php _e( 'Paste URL or browse uploaded files', 'cp' ); ?>" />
                <button type="button" class="cp-btn cp-btn-clear"><?php _e( 'Clear', 'cp' ); ?></button>
            </div>
        </div>
        <button type="button" class="cp-btn cp-btn-default cp-btn-browse"><?php _e( 'Browse', 'cp' ); ?></button>
    </div>
</script>

<script type="text/template" id="coursepress-popup-tpl">
    <div class="coursepress-popup-body popup-{{type}}">
        <div class="coursepress-popup-heading">
            <h3>{{window._coursepress.text[type]}}</h3>
        </div>
        <div class="coursepress-popup-content">{{message}}</div>
        <div class="coursepress-popup-footer">
            <button type="button" class="cp-btn cp-btn-default cp-btn-cancel"><?php _e( 'Cancel', 'cp' ); ?></button>
            <button type="button" class="cp-btn cp-btn-active btn-ok">{{window._coursepress.text.ok}}</button>
        </div>
    </div>
</script>

<script type="text/template" id="coursepress-popup-info-tpl">
    <div class="coursepress-popup-body popup-{{type}}">
        <div class="coursepress-popup-heading">
            <h3>{{window._coursepress.text[type]}}</h3>
        </div>
        <div class="coursepress-popup-content">{{message}}</div>
        <div class="coursepress-popup-footer">
            <button type="button" class="cp-btn cp-btn-default btn-ok">{{window._coursepress.text.close}}</button>
        </div>
    </div>
</script>

<script type="text/template" id="coursepress-visual-editor">
    <?php coursepress_visual_editor( '', 'coursepress_editor' ); ?>
</script>

<script type="text/template" id="coursepress-add-media-tpl">
    <div class="cp-flex-2">
        <input type="text" placeholder="{{placeholder}}" class="cp-add-media-input" />
        <button type="button" class="cp-btn cp-btn-default cp-browse-btn"><?php _e( 'Browse', 'cp' ); ?></button>
    </div>
</script>
