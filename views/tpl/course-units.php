<script type="text/template" id="coursepress-unit-details">
    <div class="cp-box-heading">
        <h2 class="box-heading-title">
            <?php _e( 'Unit Details', 'cp' ); ?>
            <a href="" class="cp-btn cp-btn-xs cp-bordered-btn cp-unit-preview"><?php _e( 'Preview', 'cp' ); ?></a>
        </h2>
    </div>
    <div class="cp-odd">
        <div class="cp-box-content">
            <div class="box-label-area">
                <h3 class="label"><?php _e( 'Unit Information', 'cp' ); ?></h3>
                <p class="description"><?php _e( 'Specify some details about this unit. Unit title fields is compulsory.', 'cp' ); ?></p>
            </div>
            <div class="box-inner-content">
                <div class="cp-box">
                    <label class="label"><?php _e( 'Unit Title', 'cp' ); ?></label>
                    <input type="text" name="post_title" class="widefat" value="{{post_title}}" />
                </div>
                <div class="cp-box cp-toggle-box">
                    <label>
                        <input type="checkbox" name="meta_use_feature_image" value="1" class="cp-toggle-input" {{_.checked(true, meta_use_feature_image)}} /> <span class="cp-toggle-btn"></span>
                        <span class="label"><?php _e( 'Use feature image', 'cp' ); ?></span>
                    </label>
                </div>
                <div class="cp-box cp-unit-feature-image {{meta_use_feature_image? '':'hidden'}}">
                    <input type="text" class="cp-add-image-input" id="unit-feature-image" value="{{meta_unit_feature_image}}" name="meta_unit_feature_image" value="" data-thumbnail="20" data-size="medium" data-title="<?php _e( 'Select Feature Image', 'cp' ); ?>" />
                </div>

                <div class="cp-box cp-toggle-box">
                    <label>
                        <input type="checkbox" name="meta_use_description" value="1" class="cp-toggle-input" {{_.checked(true, meta_use_description)}} /> <span class="cp-toggle-btn"></span>
                        <span class="label"><?php _e( 'Use description', 'cp' ); ?></span>
                    </label>
                </div>

                <div class="cp-box cp-unit-description {{meta_use_description?'': 'hidden'}}"></div>
            </div>
        </div>

        <div class="cp-box-content">
            <div class="box-label-area">
                <h3 class="label"><?php _e( 'Unit Availability', 'cp' ); ?></h3>
            </div>
            <div class="box-inner-content">
                <div class="cp-box">
                    <select name="meta_unit_availability">
                        <option value="instant" {{_.selected('instant', meta_unit_availability)}}><?php _e( 'Instantly available', 'cp' ); ?></option>
                        <option value="on_date" {{_.selected('on_date', meta_unit_availability)}}><?php _e( 'Available on', 'cp' ); ?></option>
                        <option value="after_delay" {{_.selected('after_delay', meta_unit_availability)}}><?php _e( 'Available after', 'cp' ); ?></option>
                    </select>
                </div>
                <div class="cp-box cp-on_date {{'on_date' === meta_unit_availability?'':'inactive'}}">
                    <label class="label"><?php _e( 'Unit availability date', 'cp' ); ?></label>
                    <input type="text" name="meta_unit_availability_date" />
                </div>
                <div class="cp-box cp-after_delay {{'after_delay' === meta_unit_availability?'':'inactive'}}">
                    <label class="label"><?php _e( 'Number of days', 'cp' ); ?></label>
                    <input type="text" name="meta_unit_delay_days" />
                    <p class="description"><?php _e( 'Unit will be available on X days after the course becomes available.', 'cp' ); ?></p>
                </div>
            </div>
        </div>
        <div class="cp-box-content">
            <div class="box-label-area">
                <h3 class="label"><?php _e( 'Progress to next unit', 'cp' ); ?></h3>
            </div>
            <div class="box-inner-content">
                <div class="cp-box cp-toggle-box">
                    <label>
                        <input type="checkbox" name="meta_force_current_unit_completion" value="1" class="cp-toggle-input" {{_.checked(true, meta_force_current_unit_completion)}} /> <span class="cp-toggle-btn"></span>
                        <span class="label"><?php _e( 'Force unit completion', 'cp' ); ?></span>
                    </label>
                    <p class="description"><?php _e( 'User needs to answer all required assessments and view all steps in order to access the next unit', 'cp' ); ?></p>
                </div>
                <div class="cp-box cp-toggle-box">
                    <label>
                        <input type="checkbox" name="meta_force_current_unit_successful_completion" value="1" class="cp-toggle-input" {{_.checked(true, meta_force_current_unit_successful_completion)}} /> <span class="cp-toggle-btn"></span>
                        <span class="label"><?php _e( 'Force unit successful completion', 'cp' ); ?></span>
                    </label>
                    <p class="description"><?php _e( 'User needs to pass all required assessable steps in order to access the next unit.', 'cp' ); ?></p>
                </div>
            </div>
        </div>
    </div>
    <div id="unit-steps-container"></div>
    <link type="text/css" rel="stylesheet" media="all" href="<?php echo includes_url( '/css/editor.css' ); ?>" />
</script>

<script type="text/template" id="coursepress-unit-modules-tpl">
    <div class="cp-content-box">
        <h3><?php _e( 'Modules', 'cp' ); ?></h3>
    </div>
    <div class="cp-odd">
        <button type="button" class="cp-btn cp-btn-active">
            <?php _e( 'Add Module', 'cp' ); ?>
        </button>

        <div>
            <ul class="cp-input-group cp-select-list">
                <# _.each( modules, function( module, pos ) { #>
                    <li class="module-item" data-order="{{pos}}">
                        <span>{{module.title}}</span>
                    </li>
                    <# }) #>
            </ul>
        </div>
    </div>
    <div class="cp-content-box" id="cp-module-steps"></div>
</script>

<script type="text/template" id="coursepress-unit-module-steps-tpl">
    <div class="cp-box-content">
        <div class="cp-box">
            <h3 class="label"><?php _e( 'Module Name', 'cp' ); ?></h3>
            <input type="text" name="title" class="widefat" value="{{title}}" />
        </div>
        <div id="module-steps-container">
            <h3><?php _e( 'Steps', 'cp' ); ?></h3>
            <p class="description"><?php _e( 'Click steps below to add them to this unit', 'cp' ); ?></p>
            <div class="unit-steps-area">
                <div class="unit-steps-tools">
                    <ul>
                        <?php foreach ( $steps as $step => $label ) : ?>
                            <li class="unit-step unit-step-<?php echo $step; ?>" data-step="<?php echo $step; ?>">
                                <span><?php echo $label; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="unit-steps"></div>
            </div>
        </div>
    </div>
</script>

<script type="text/template" id="coursepress-unit-steps-tpl">
    <h3><?php _e( 'Steps', 'cp' ); ?></h3>
    <p class="description"><?php _e( 'Click steps below to add them to this unit', 'cp' ); ?></p>
    <div class="unit-steps-area">
        <div class="unit-steps-tools">
            <ul>
            <?php foreach ( $steps as $step => $label ) : ?>
                <li class="unit-step unit-step-<?php echo $step; ?>" data-step="<?php echo $step; ?>">
                    <span><?php echo $label; ?></span>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
        <div class="unit-steps"></div>
    </div>
</script>

<script type="text/template" id="coursepress-units-tpl">
    <div class="cp-box-heading">
        <h2 class="box-heading-title"><?php _e( 'Units', 'cp' ); ?></h2>
    </div>

    <div class="cp-box-content units-content">
        <table class="cp-units-table">
            <thead>
            <tr>
                <th class="column-unit">
                    <# if ( !!meta_with_modules ) { #>
                        <?php _e( 'Units & Modules', 'cp' ); ?>
                        <# } else { #>
                            <?php _e( 'Units & Steps', 'cp' ); ?>
                            <# } #>
                </th>
                <th class="column-step"><?php _e( 'Steps', 'cp' ); ?></th>
                <th class="column-preview"><?php _e( 'Free Preview', 'cp' ); ?></th>
                <th class="column-time"><?php _e( 'Time', 'cp' ); ?></th>
            </tr>
            </thead>
        </table>
        <div id="units-container"></div>
    </div>
</script>

<script type="text/template" id="coursepress-unit-list-tpl">
    <ul class="units-list">
        <# _.each( units, function( data, id ) { #>
            <li class="unit-item" data-unit="{{id}}">
                <span class="cp-count">{{data.count}}</span>
                <span class="unit-title">{{data.title}}</span>
            </li>
            <# })#>
    </ul>
    <button type="button" class="cp-btn cp-btn-default cp-btn-xs"><?php _e( 'Add Unit', 'cp' ); ?></button>
</script>

<script type="text/template" id="coursepress-unit-tpl">
    <div class="cp-unit-heading">
        <label>{{post_title}}</label>
        <button type="button" class="cp-btn cp-bordered-btn cp-btn-xs"><?php _e( 'Preview', 'cp' ); ?></button>
        <button type="button" class="cp-btn cp-bordered-btn cp-btn-xs" data-unit="{{ID}}"><?php _e( 'Edit Unit', 'cp' ); ?></button>
    </div>
    <div class="cp-unit-content cp-unit-steps">
        <table class="unit-table-list">
        <# _.each( modules, function( module, module_id ) { #>
            <tr>
                <td class="column-unit">{{module.title}}</td>
                <td class="column-step"></td>
                <td class="column-preview">
                    <label class="cp-checkbox">
                        <input type="checkbox" class="cp-checkbox-input" {{_.checked(true, module.preview)}} />
                        <span class="cp-checkbox-icon"></span>
                    </label>
                </td>
                <td class="column-time">-</td>
            </tr>
            <# })#>
        </table>
    </div>
</script>