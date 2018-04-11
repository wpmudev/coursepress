<script type="text/template" id="coursepress-marketpress-tpl">
    <hr />
    <div class="box-label-area">
        <h3 class="label">MarketPress E-Commerce</h3>
    </div>

    <div class="box-inner-content">
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="checkbox" name="enabled" value="1" class="cp-toggle-input extension-commerce-enable" autocomplete="off" {{_.checked(true, marketpress.enabled)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php printf( __( 'Use %s to sell courses.', 'MarketPress' ), 'cp' ); ?></span>
            </label>
            <p class="description"><?php printf( __('When checked, %s will be use to sell courses.', 'MarketPress' ), 'cp' ); ?></p>
        </div>

        <div class="cp-box cp-toggle-box cp-sep">
            <label>
                <input type="checkbox" name="redirect" value="1" class="cp-toggle-input" autocomplete="off" {{_.checked(true, marketpress.redirect)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php printf( __( 'Redirect %s product to course product.', 'MarketPress' ), 'cp' ); ?></span>
            </label>
            <p class="description"><?php printf( __( 'Visitors who will try to access %s single product will be redirected to course overview.', 'MarketPress' ), 'cp' ); ?></p>
        </div>
    </div>

    <div class="box-label-area">
            <label class="label"><?php _e( 'When the course becomes unpaid, then:', 'cp' ); ?></label>
    </div>

    <div class="box-inner-content">
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="radio" name="unpaid" class="cp-toggle-input" value="change_status" autocomplete="off" {{_.checked('change_status', marketpress.unpaid)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Change the status into draft.', 'cp' ); ?></span>
            </label>
        </div>
        <div class="cp-box cp-toggle-box cp-sep">
            <label>
                <input type="radio" name="unpaid" class="cp-toggle-input" value="delete" autocomplete="off" {{_.checked( 'delete', marketpress.unpaid)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php printf( __( 'Remove %s related product.', 'MarketPress' ), 'cp' ); ?></span>
            </label>
        </div>
    </div>

    <div class="box-label-area">
        <label class="label"><?php _e( 'When a course is deleted, then:', 'cp' ); ?></label>
    </div>

    <div class="box-inner-content">
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="radio" name="delete" class="cp-toggle-input" value="change_status" autocomplete="off" {{_.checked('change_status', marketpress.delete)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Change the status into draft.', 'cp' ); ?></span>
            </label>
        </div>
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="radio" name="delete" class="cp-toggle-input" value="delete" autocomplete="off" {{_.checked('delete', marketpress.delete)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php printf( __( 'Remove %s related product.', 'MarketPress' ), 'cp' ); ?></span>
            </label>
        </div>
    </div>
</script>
