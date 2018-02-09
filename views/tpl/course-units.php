<script type="text/template" id="coursepress-unit-details">
    <div class="cp-box-heading">
        <h2 class="box-heading-title">
            <?php _e( 'Unit Details', 'cp' ); ?>
            <a href="<?php echo esc_url( $course->get_permalink() ); ?>" target="_blank" class="cp-btn cp-btn-xs cp-bordered-btn cp-unit-preview"><?php _e( 'Preview', 'cp' ); ?></a>
        </h2>
    </div>
    <div class="cp-odd {{with_modules?'with-modules': ''}}">
        <div class="cp-box-content">
            <div class="box-label-area">
                <h3 class="label"><?php _e( 'Unit Information', 'cp' ); ?></h3>
                <p class="description"><?php _e( 'Specify some details about this unit. Unit title fields is compulsory.', 'cp' ); ?></p>
            </div>
            <div class="box-inner-content">
                <div class="cp-toggle-box cp-unit-status">
                    <label>
                        <input type="checkbox" name="post_status" value="publish" class="cp-toggle-input" {{_.checked('publish', post_status)}} /> <span class="cp-toggle-btn"></span>
                        <span class="label"><?php _e( 'Publish', 'cp' ); ?></span>
                    </label>
                </div>
                <div class="cp-box cp-unit-title-box">
                    <label class="label"><?php _e( 'Unit Title', 'cp' ); ?></label>
                    <input type="text" name="post_title" class="widefat unit-title-input" value="{{post_title}}" />
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
                    <input type="text" name="meta_unit_availability_date" value="{{typeof meta_unit_availability_date !== 'undefined' && meta_unit_availability_date ? meta_unit_availability_date : ''}}" class="datepicker" />
                    <i class="fa fa-calendar"></i>
                </div>
                <div class="cp-box cp-after_delay {{'after_delay' === meta_unit_availability?'':'inactive'}}">
                    <label class="label"><?php _e( 'Number of days', 'cp' ); ?></label>
                    <input type="number" min="1" name="meta_unit_delay_days" value="{{typeof meta_unit_delay_days !== 'undefined' && meta_unit_delay_days ? meta_unit_delay_days : ''}}" />
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
        <button type="button" class="cp-btn cp-btn-xs cp-btn-active add-module">
            <?php _e( 'Add Module', 'cp' ); ?>
        </button>

        <div id="unit-module-list"></div>
    </div>
    <div class="cp-content-box" id="cp-module-steps"></div>
</script>

<script type="text/template" id="coursepress-move-to-module-popup-tpl">
    <div class="coursepress-popup-body popup-warning">
        <div class="coursepress-popup-heading">
            <h3><?php esc_html_e( 'Select a Module', 'cp' ); ?></h3>
        </div>
        <div class="coursepress-popup-content">
            <# if(_.size(modules)) { #>
                <?php esc_html_e( 'Please select the module where you would like this step to be moved.', 'cp' ); ?>
                <p>
                    <label>
                        <select name="target_module">
                            <option></option>
                            <# _.each( modules, function( module ) { #>
                                <option value="{{module.id}}">{{ module.title }}</option>
                            <# }) #>
                        </select>
                    </label>
                </p>
            <# } else { #>
                <?php esc_html_e( 'There are no other modules. Please add a new module and try again.', 'cp' ); ?>
            <# } #>
        </div>
        <div class="coursepress-popup-footer">
            <button type="button" class="cp-btn cp-btn-default cp-btn-cancel"><?php _e( 'Cancel', 'cp' ); ?></button>
            <button type="button" class="cp-btn cp-btn-active btn-ok">{{window._coursepress.text.ok}}</button>
        </div>
    </div>
</script>

<script type="text/template" id="coursepress-unit-module-list-tpl">
    <ul class="cp-input-group cp-select-list">
        <# _.each( modules, function( module, pos ) { #>
            <# if ( ! module.deleted ) { #>
            <li class="module-item" data-order="{{pos}}" data-id="{{module.id}}">
                <div class="icon-move cp-flex">
                    <div class="module-title"><# if ( '' == module.title ) { #><?php _ex( '[Untitled]', 'module title', 'cp' ); ?><# } else { #>{{module.title}}<# } #></div>
                    <div class="module-description">{{module.mini_desc}}</div>
                    <div class="step-icon-container"></div>
                </div>
            </li>
            <# }#>
        <# }) #>
    </ul>
</script>

<script type="text/template" id="coursepress-step-icons">
    <# _.each( icons, function( icon ) { #>
        <span class="step-icon step-{{icon}}"></span>
        <# }) #>
</script>

<script type="text/template" id="coursepress-unit-module-steps-tpl">
    <div class="cp-box-content cp-module-steps">
        <div class="cp-box">
            <label class="label"><?php _e( 'Module Name', 'cp' ); ?></label>
            <input type="text" name="title" class="widefat module-title" value="{{title}}" />
        </div>
        <button type="button" class="cp-btn cp-btn-xs cp-delete-module"><?php _e( 'Delete', 'cp' ); ?></button>
        <div class="cp-box cp-toggle-box">
            <label>
                <input type="checkbox" name="show_description" value="1" class="cp-toggle-input" {{_.checked(true, show_description)}} /> <span class="cp-toggle-btn"></span>
                <span class="label"><?php _e( 'Show description', 'cp' ); ?></span>
            </label>
        </div>
        <div class="cp-module-description {{show_description?'':'inactive'}}"></div>
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
                <th class="column-unit"><?php _e( 'Units & Modules', 'cp' ); ?></th>
                <th class="column-step"><?php _e( 'Steps', 'cp' ); ?></th>
                <th class="column-preview"><?php _e( 'Free Preview', 'cp' ); ?></th>
                <th class="column-time"><?php _e( 'Time', 'cp' ); ?></th>
            </tr>
            </thead>
        </table>
        <div id="units-container"></div>
    </div>
</script>
<script type="text/template" id="coursepress-unit-list-steps-tpl">
    <div class="cp-box-heading">
        <h2 class="box-heading-title"><?php _e( 'Units', 'cp' ); ?></h2>
    </div>

    <div class="cp-box-content units-content">
        <table class="cp-units-table">
            <thead>
            <tr>
                <th class="column-unit"><?php _e( 'Units & Steps', 'cp' ); ?></th>
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
    <ul class="units-list"></ul>
    <button type="button" class="cp-btn cp-btn-default cp-btn-xs new-unit"><?php _e( 'Add Unit', 'cp' ); ?></button>
</script>

<script type="text/template" id="coursepress-unit-item-tpl">
    <span class="cp-count" data-count="{{count}}">{{count}}</span>
    <span class="unit-title">{{post_title}}</span>
</script>

<script type="text/template" id="coursepress-unit-tpl">
    <div class="cp-unit-heading">
        <label>{{post_title}}</label>
        <button type="button" class="cp-btn cp-bordered-btn cp-btn-xs preview-unit" data-url="{{unit_permalink}}"><?php _e( 'Preview', 'cp' ); ?></button>
        <button type="button" class="cp-btn cp-bordered-btn cp-btn-xs edit-unit" data-unit="{{cid}}"><?php _e( 'Edit Unit', 'cp' ); ?></button>
        <button type="button" class="cp-btn cp-bordered-btn cp-btn-xs delete-unit" data-unit="{{cid}}"><?php _e( 'Delete', 'cp' ); ?></button>
    </div>
    <div class="cp-unit-content cp-unit-steps">
        <table class="unit-table-list">
            <# if ( modules ) { #>
                <# _.each( modules, function( module, module_id ) { #>
                    <tr>
                        <td class="column-unit" data-module="{{module_id}}" data-unit="{{cid}}"><# if ( '' == module.title ) { #><?php _ex( '[Untitled]', 'module title', 'cp' ); ?><# } else { #>{{module.title}}<# } #></td>
                        <td class="column-step" data-module="{{module_id}}" data-unit="{{cid}}">
                            <# if ( module.steps ) { #>
                                <# _.each( module.steps, function( step ) { #>
                                    <span class="step-icon step-{{step.module_type}}"></span>
                                <#})#>
                            <#}#>
                        </td>
                        <td class="column-preview" data-module="{{module_id}}">
                            <label class="cp-checkbox cp-ignore-update-model">
                                <input type="checkbox" name="preview" class="cp-checkbox-input" value="1" {{_.checked(true, module.preview)}} />
                                <span class="cp-checkbox-icon"></span>
                            </label>
                        </td>
                        <td class="column-time">-</td>
                    </tr>
                    <# })#>
            <# } else { #>
                <# _.each( steps, function( step, step_id ) { #>
                    <tr>
                        <td class="column-unit">{{step.post_title}}</td>
                        <td class="column-step">
                        </td>
                        <td class="column-preview" data-step="{{step_id}}">
                            <label class="cp-checkbox cp-ignore-update-model">
                                <input type="checkbox" name="meta_preview" class="cp-checkbox-input" value="1" {{_.checked(true, step.meta_preview)}} />
                                <span class="cp-checkbox-icon"></span>
                            </label>
                        </td>
                        <td class="column-time">-</td>
                    </tr>
                <# }) #>
            <# } #>
        </table>
    </div>
</script>

<script type="text/template" id="coursepress-unit-help-1-tpl">
    <div class="coursepress-popup-body">
        <div class="coursepress-popup-heading">
            <div class="coursepress-popup-title">
                <h3><?php _e( 'Modules or no modules, that is the question', 'cp' ); ?></h3>
            </div>
            <span class="cp-modal-close cp-close"></span>
        </div>
        <div class="coursepress-popup-content">
            <img src="{{window._coursepress.plugin_url}}/assets/images/unit-help.png" />
        </div>
        <div class="coursepress-popup-footer">
            <p><?php printf( __( 'Image on the left is a course structure for a course with modules. You can see various steps group e.g. %1$sMaking groceries list%2$s module consists of 2 steps. Courses with modules allow you to add more than 1 step to the screen so both steps inside %1$sMaking groceries list%2$s will be shown on the same screen.', 'cp' ), '<strong>', '</strong>' ); ?></p>
        </div>
    </div>
</script>

<script type="text/template" id="coursepress-unit-help-2-tpl">
    <div class="coursepress-popup-body">
        <div class="coursepress-popup-heading">
            <div class="coursepress-popup-title">
                <h3><?php _e( 'Woo, time to add some content', 'cp' ); ?></h3>
            </div>
            <span class="cp-modal-close cp-close"></span>
        </div>
        <div class="coursepress-popup-content">
            <div class="cp-flex">
                <div class="cp-box">
                    <img src="{{window._coursepress.plugin_url}}/assets/images/unit-help-2.png" />
                </div>
                <div class="cp-box cp-box-2">
                    <p><?php _e( 'Typically, courses consist of Units and Steps, where each step is a shown individuality to visitor. To show more than one step on screen they must be placed inside a module.', 'cp' ); ?></p>
                </div>
            </div>
        </div>
        <div class="coursepress-popup-footer">
			<button type="button" class="cp-btn cp-btn-active cp-btn-got-it-1"><?php _e( 'Got it.', 'cp' ); ?></button>
        </div>
    </div>
</script>
<script type="text/template" id="coursepress-unit-help-3-tpl">
    <div class="coursepress-popup-body">
        <div class="coursepress-popup-heading">
            <div class="coursepress-popup-title">
                <h3><?php _e( 'Create your first content', 'cp' ); ?></h3>
            </div>
            <span class="cp-modal-close cp-close"><?php _e( 'Skip Help', 'cp' ); ?></span>
        </div>
        <div class="coursepress-popup-content">
            <p><?php printf( __( 'Welcome! This wizard will help you set up your course content. First up, let\'s create your first unit! Go ahead and click %1$sAdd Unit%2$s to get started.', 'cp' ), '<strong>', '</strong>' ); ?></p>
        </div>
    </div>
</script>
<script type="text/template" id="coursepress-help-overlay-tpl">
    <div class="coursepress-popup-body">
        <div class="coursepress-popup-heading">
            <div class="coursepress-popup-title">
                <h3>{{popup_title}}</h3>
            </div>
            <span class="cp-modal-close cp-close"><?php _e( 'Skip Help', 'cp' ); ?></span>
        </div>
        <div class="coursepress-popup-content">
            <p>{{popup_content}}</p>
        </div>
        <div class="coursepress-popup-footer">
            <button type="button" class="cp-btn cp-btn-active btn-ok">{{window._coursepress.text.ok}}</button>
        </div>
    </div> 
</script>

<script type="text/template" id="coursepress-unit-help-4-tpl">
    <div class="coursepress-popup-body">
        <div class="coursepress-popup-heading">
            <div class="coursepress-popup-title">
                <h3><?php _e( 'Type unit title', 'cp' ); ?></h3>
            </div>
            <span class="cp-modal-close cp-close"><?php _e( 'Skip Help', 'cp' ); ?></span>
        </div>
        <div class="coursepress-popup-content">
            <p><?php _e( 'Great start, you have now created your first unit. Give it a name above and hit return.', 'cp' ); ?></p>
        </div>
    </div>
</script>

<script type="text/template" id="coursepress-unit-help-5-tpl">
    <div class="coursepress-popup-body">
        <div class="coursepress-popup-heading">
            <div class="coursepress-popup-title">
                <h3><?php _e( 'Add step to unit', 'cp' ); ?></h3>
            </div>
            <span class="cp-modal-close cp-close"><?php _e( 'Skip Help', 'cp' ); ?></span>
        </div>
        <div class="coursepress-popup-content">
            <p><?php _e( 'Doing great, lets set up the first step in your unit. You can have as few or as many steps as you like, and you can change their order later on.', 'cp' ); ?></p>
        </div>
    </div>
</script>

<script type="text/template" id="coursepress-unit-help-6-tpl">
    <div class="coursepress-popup-body">
        <div class="coursepress-popup-heading">
            <div class="coursepress-popup-title">
                <h3><?php _e( 'Fantastic', 'cp' ); ?></h3>
            </div>
            <span class="cp-modal-close cp-close"><?php _e( 'Skip Help', 'cp' ); ?></span>
        </div>
        <div class="coursepress-popup-content">
            <p><?php _e( 'You now know everything you need to create a course and it\'s contents. If you ever want to go over this tutorial again, you can do so by clicking \'Help\' in the top-right corner. Have fun creating courses!', 'cp' ); ?></p>
            <button type="button" class="cp-btn cp-btn-active cp-btn-got-it-2"><?php _e( 'Got it', 'cp' ); ?></button>
        </div>
    </div>
</script>

