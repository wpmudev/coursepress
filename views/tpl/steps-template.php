<script type="text/template" id="coursepress-step-tpl">
    <div class="cp-step-header">
        <span class="step-type">{{type}}</span>
        <input type="text" name="post_title" />
        <button type="button" class="step-toggle-button"></button>
    </div>
    <div class="cp-step-content"></div>
    <div class="cp-step-footer">
        <label>
            <input type="checkbox" name="meta_show_title" value="1" class="cp-toggle-input" /> <span class="cp-toggle-btn"></span>
            <span class="label"><?php _e( 'Show module title in unit view', 'cp' ); ?></span>
        </label>
        <a href="" class="cp-btn cp-btn-xs cp-bordered-btn cp-preview"><?php _e( 'Preview', 'cp' ); ?></a>
    </div>
</script>

<script type="text/template" id="coursepress-step-text">
    <?php coursepress_visual_editor( 'WHAT THE HELL', 'post_content1' ); ?>
</script>