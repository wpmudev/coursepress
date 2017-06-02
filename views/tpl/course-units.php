<script type="text/template" id="coursepress-course-units-tpl">
    <div class="cp-box-heading">
        <h2 class="box-heading-title"><?php _e( 'Units', 'cp' ); ?></h2>
    </div>

    <div class="cp-box-content units-content">
        <table class="widefat cp-units-table">
            <thead>
            <tr>
                <th><?php _e( 'Units & Steps', 'cp' ); ?></th>
                <th><?php _e( 'Steps', 'cp' ); ?></th>
                <th><?php _e( 'Free Preview', 'cp' ); ?></th>
                <th><?php _e( 'Time', 'cp' ); ?></th>
            </tr>
            </thead>
            <tbody id="units-container"></tbody>
        </table>
    </div>
</script>
<script type="text/template" id="coursepress-course-units-with-modules-tpl">
    <div class="cp-box-heading">
        <h2 class="box-heading-title"><?php _e( 'Units', 'cp' ); ?></h2>
    </div>

    <div class="cp-box-content units-content">
        <table class="widefat cp-units-table">
            <thead>
            <tr>
                <th><?php _e( 'Units & Modules', 'cp' ); ?></th>
                <th><?php _e( 'Steps', 'cp' ); ?></th>
                <th><?php _e( 'Free Preview', 'cp' ); ?></th>
                <th><?php _e( 'Time', 'cp' ); ?></th>
            </tr>
            </thead>
        </table>
        <div id="units-container"></div>
    </div>
</script>

<script type="text/template" id="coursepress-unit-list-tpl">
    <ul class="units-list">
        <# _.each( units, function( data, id ) { #>
            <li class="unit-item" data-item="{{id}}">
                <span class="cp-count">{{data.count}}</span>
                {{data.title}}
            </li>
            <# })#>
    </ul>
    <button type="button" class="cp-btn cp-btn-default cp-btn-xs"><?php _e( 'Add Unit', 'cp' ); ?></button>
</script>

<script type="text/template" id="coursepress-unit-tpl">
    <div class="cp-unit-heading">
        <label>{{post_title}}</label>
        <button type="button" class="cp-btn cp-bordered-btn cp-btn-xs"><?php _e( 'Preview', 'cp' ); ?></button>
        <button type="button" class="cp-btn cp-bordered-btn cp-btn-xs"><?php _e( 'Edit Unit', 'cp' ); ?></button>
    </div>
    <div class="cp-unit-content cp-unit-steps">
        <table class="unit-table-list">
        <# _.each( modules, function( module, module_id ) { #>
            <tr>
                <td>{{module.title}}</td>
                <td></td>
                <td>
                    <input type="checkbox" {{_.checked(true, module.preview)}} />
                </td>
                <td>-</td>
            </tr>
            <# })#>
        </table>
    </div>
</script>