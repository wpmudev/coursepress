<script type="text/template" id="coursepress-marketpress-tpl">
    <hr />
    <div class="box-label-area">
        <h3 class="label">MarketPress E-Commerce</h3>
    </div>

    <div class="box-inner-content">
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="checkbox" name="enabled" value="1" class="cp-toggle-input extension-commerce-enable" autocomplete="off" {{_.checked(true, marketpress.enabled)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php esc_html_e( sprintf( 'Use %s to sell courses.', 'MarketPress' ), 'cp' ); ?></span>
            </label>
            <p class="description"><?php esc_html_e( sprintf('When checked, %s will be use to sell courses.', 'MarketPress' ), 'cp' ); ?></p>
        </div>

        <div class="cp-box cp-toggle-box cp-sep">
            <label>
                <input type="checkbox" name="redirect" value="1" class="cp-toggle-input" autocomplete="off" {{_.checked(true, marketpress.redirect)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php esc_html_e( sprintf( 'Redirect %s product to course product.', 'MarketPress' ), 'cp' ); ?></span>
            </label>
            <p class="description"><?php esc_html_e( sprintf( 'Visitors who will try to access %s single product will be redirected to course overview.', 'MarketPress' ), 'cp' ); ?></p>
        </div>
    </div>

    <div class="box-label-area">
            <label class="label"><?php esc_html_e( 'When the course becomes unpaid, then:', 'cp' ); ?></label>
    </div>

    <div class="box-inner-content">
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="radio" name="unpaid" class="cp-toggle-input" value="change_status" autocomplete="off" {{_.checked('change_status', marketpress.unpaid)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php esc_html_e( 'Change the status into draft.', 'cp' ); ?></span>
            </label>
        </div>
        <div class="cp-box cp-toggle-box cp-sep">
            <label>
                <input type="radio" name="unpaid" class="cp-toggle-input" value="delete" autocomplete="off" {{_.checked( 'delete', marketpress.unpaid)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php esc_html_e( sprintf( 'Remove %s related product.', 'MarketPress' ), 'cp' ); ?></span>
            </label>
        </div>
    </div>

    <div class="box-label-area">
        <label class="label"><?php esc_html_e( 'When a course is deleted, then:', 'cp' ); ?></label>
    </div>

    <div class="box-inner-content">
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="radio" name="delete" class="cp-toggle-input" value="change_status" autocomplete="off" {{_.checked('change_status', marketpress.delete)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php esc_html_e( 'Change the status into draft.', 'cp' ); ?></span>
            </label>
        </div>
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="radio" name="delete" class="cp-toggle-input" value="delete" autocomplete="off" {{_.checked('delete', marketpress.delete)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php esc_html_e( sprintf( 'Remove %s related product.', 'MarketPress' ), 'cp' ); ?></span>
            </label>
        </div>
    </div>
</script>
