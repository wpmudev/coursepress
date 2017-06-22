<script type="text/template" id="coursepress-marketpress-tpl">
    <div class="box-label-area">
        <label class="label">MarketPress</label>
    </div>

    <div class="box-inner-content">
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="checkbox" name="enabled" class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( sprintf( 'Use %s to sell courses.', 'MarketPress' ), 'cp' ); ?></span>
            </label>
            <p class="description"><?php _e( sprintf('When checked, %s will be use to sell courses.', 'MarketPress' ), 'cp' ); ?></p>
        </div>

        <div class="cp-box cp-toggle-box cp-sep">
            <label>
                <input type="checkbox" name="redirect" class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( sprintf( 'Redirect %s product to course product.', 'MarketPress' ), 'cp' ); ?></span>
            </label>
            <p class="description"><?php _e( sprintf( 'Visitors who will try to access %s single product will be redirected to course overview.', 'MarketPress' ), 'cp' ); ?></p>
        </div>
    </div>

    <div class="box-label-area">
            <label class="label"><?php _e( 'When the course becomes unpaid, then:', 'cp' ); ?></label>
    </div>

    <div class="box-inner-content">
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="radio" name="unpaid" class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Change the status into draft.', 'cp' ); ?></span>
            </label>
        </div>
        <div class="cp-box cp-toggle-box cp-sep">
            <label>
                <input type="radio" name="meta_allow_discussion" class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( sprintf( 'Remove %s related product.', 'MarketPress' ), 'cp' ); ?></span>
            </label>
        </div>
    </div>

    <div class="box-label-area">
        <label class="label"><?php _e( 'When a course is deleted, then:', 'cp' ); ?></label>
    </div>

    <div class="box-inner-content">
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="radio" name="meta_allow_discussion" class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Change the status into draft.', 'cp' ); ?></span>
            </label>
        </div>
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="radio" name="meta_allow_discussion" class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( sprintf( 'Remove %s related product.', 'MarketPress' ), 'cp' ); ?></span>
            </label>
        </div>
    </div>
</script>