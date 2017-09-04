/*! CoursePress - v3.0.0
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2017; * Licensed GPLv2+ */
(function() {
    'use strict';

    CoursePress.Define( 'GeneralSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-general-setting-tpl',
            el: $('#coursepress-setting-general'),

            initialize: function( model ) {
                this.model = model;

                this.on( 'view_rendered', this.setUpUI, this );
                this.render();
            },

            setUpUI: function() {
                this.$('select').select2();
            },

            getModel: function() {
                window.console.log(this.model);
                return this.model;
            }
        });
    });
})();
(function() {
    'use strict';

    CoursePress.Define( 'SlugsSettings', function($) {
        return CoursePress.View.extend({
            template_id: 'coursepress-slugs-setting-tpl',
            el: $('#coursepress-setting-slugs'),
            events: {
                'change [name]': 'updateModel'
            },
            initialize: function( model, settingView ) {
                this.model = ! model ? {} : model;
                this.settingView = settingView;

                this.on( 'view_rendered', this.setUpUI, this );
                this.render();
            },
            setUpUI: function() {
                this.$('select').select2();
            },
            updateModel: function(ev) {
                var sender = $(ev.currentTarget),
                    name = sender.attr('name'),
                    value = sender.val(),
                    first, model;

                if ( sender.is('[type="checkbox"],[type="radio"]') ) {
                    value = sender.is(':checked') ? value : false;
                }

                name = name.split('.');
                first = name.shift();

                if ( this.model[ first ] ) {
                    model = this.model[ first ];
                } else {
                    model = {};
                }

                if ( name.length ) {
                    _.each(name, function (t) {
                        model[t] = value;
                    }, this);
                    this.model[first] = model;
                } else {
                    this.model[first] = value;
                }
            },
            getModel: function() {
                window.console.log('slugs');
                window.console.log(this.model);
                return this.model;
            }
        });
    });
})();
(function() {
    'use strict';

    CoursePress.Define( 'EmailSettings', function( $, doc, win ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-emails-setting-tpl',
            el: $( '#coursepress-setting-email' ),
            events: {
                'change [name]': 'updateModel',
                'click .cp-input-group li': 'toggleBox'
            },
            rootModel: false,
            editor: false,
            current: 'registration',
            model: {
                enabled: 1,
                from: '',
                email: '',
                subject: '',
                content: '',
                auto_email: false
            },
            initialize: function( model ) {
                this.rootModel = model;
                this.on( 'view_rendered', this.setUpUI, this );
                this.render();
            },
            setUpUI: function() {
                // Select the first item as active
                this.$('.cp-input-group li').first().trigger( 'click' );
            },
            toggleBox: function(ev) {
                var target = $(ev.currentTarget),
                    key = target.data( 'key' );

                this.current = key;
                if ( this.rootModel[key] ) {
                    this.model = this.rootModel[key];
                    this.setValues(this.model);
                }

                target.siblings().removeClass('active');
                target.addClass('active');
            },
            setValues: function( model ) {
                var names, self;

                names = this.$( '[name]' );
                self = this;

                this.visualEditor({
                    content: this.rootModel[this.current].content,
                    container: this.$('.coursepress-email-content').empty(),
                    callback: function( content ) {
                        self.rootModel[self.current].content = content;
                    }
                });

                _.each( names, function( n ) {
                    var field = $(n),
                        name = field.attr( 'name' );

                    if ( model[name] ) {
                        field.val( model[name] );
                    }
                }, this );

                if ( win._coursepress.email_sections[ this.current ] ) {
                    var section = win._coursepress.email_sections[ this.current ];
                    this.$( '#course-email-heading' ).html( section.title );
                    this.$( '#course-email-desc' ).html( section.description );
                    this.$( '.cp-alert-info' ).html( section.content_help_text );
                    this.$( '[name="enabled"]' ).prop( 'checked', !!this.rootModel[this.current].enabled );
                }
            },
            getModel: function() {
                return this.rootModel;
            },
            updateModel: function( ev ) {
                var sender = this.$( ev.currentTarget ),
                    value = sender.val(),
                    name = sender.attr( 'name' );

                if ( 'checkbox' === sender.attr( 'type' ) ) {
                    value = sender.is( ':checked' ) ? value : false;
                }

                this.model[ name ] = value;
                this.rootModel[ this.current ] = this.model;
            }
        });
    });
})();
(function() {
    'use strict';

    CoursePress.Define( 'CapabilitiesSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-capabilities-setting-tpl',
            el: $( '#coursepress-setting-capabilities' ),
            events: {
                'click .cp-select-list.cp-capabilities li': 'showHideCaps',
                'change [name]': 'updateModel'
            },
            current: 'instructor',
            initialize: function( model ) {
                this.model = model;
                this.render();
            },

            updateModel: function(ev) {
                var sender = $(ev.currentTarget),
                    name = sender.attr('name'),
                    value = sender.val();

                if ( sender.is('[type="checkbox"],[type="radio"]') ) {
                    value = sender.is(':checked') ? value : false;
                }

                if ( ! this.model[this.current] ) {
                    this.model[this.current] = {};
                }
                this.model[this.current][name] = value;
            },

            getModel: function() {
                window.console.log(this.model);
                return this.model;
            },

            showHideCaps: function(ev) {
                var target = $(ev.currentTarget);
                var capsDiv = target.data( 'id' );
                this.setActiveItem( target );
                this.$( '.cp-caps-fields' ).addClass( 'inactive' );
                this.$('#' + capsDiv ).removeClass( 'inactive' );
            },

            setActiveItem: function(target) {
                target.siblings().removeClass('active');
                target.addClass( 'active' );
            }
        });
    });
})();
(function() {
    'use strict';

    CoursePress.Define( 'CertificateSettings', function( $, doc ) {
        var iris, CertificatePreview;

        $(doc).on( 'click', function(ev) {
           var sender = $(ev.currentTarget);

           if ( iris && ( ! sender.is(iris) || ! sender.is('.iris-picker') ) ) {
               iris.iris('hide');
               iris = false;
           }
        });

        CertificatePreview = CoursePress.View.extend({
            template_id: 'coursepress-cert-preview',
            className: 'coursepress-popup-preview',
            events: {
                'click .cp-btn': 'remove'
            },
            render: function() {
                CoursePress.View.prototype.render.apply( this );
                this.$el.appendTo( 'body' );
            }
        });

        return CoursePress.View.extend({
            template_id: 'coursepress-certificate-setting-tpl',
            el: $('#coursepress-setting-basic_certificate'),
            events: {
                'focus [name="cert_text_color"]': 'showColorPicker',
                'change [name]': 'updateModel',
                'change [name="use_cp_default"]': 'toggleCertificateSettings',
                'change [name="enabled"]': 'toggleCertificateSettings',
                'click [name="preview_certificate"]': 'previewCertificate'
            },
            initialize: function( model ) {
                this.model = model;

                this.on( 'view_rendered', this.setUpUI, this );
                this.render();
            },
            setUpUI: function() {
                var self = this;

                this.$('select').select2();
                this.certBG = new CoursePress.AddImage( this.$('#coursepress-cert-bg' ) );
                this.color = this.$('[name="cert_text_color"]');

                this.color.iris({
                    palettes: true,
                    hide: true,
                    width: 220,
                    change: function( ) {
                        self.model.cert_text_color = self.color.iris('color');
                    }
                });

                this.$('#content_certificate').val( this.model.content );
                this.$('.switch-tmce').trigger('click');

                if ( tinyMCE.get( 'content_certificate' ) ) {
                    this.contentEditor = tinyMCE.get( 'content_certificate' );
                    this.contentEditor.on( 'change', function() {
                        self.updateCertificateContent();
                    }, this );
                }

                this.certBox = this.$('.box-cert-settings' );

                if ( ! this.model.enabled || this.model.use_cp_default ) {
                    this.certBox.hide();
                }
            },
            updateCertificateContent: function() {
                this.model.content = this.contentEditor.getContent();
            },
            showColorPicker: function() {
                if ( this.color ) {
                    this.color.iris('show');
                    iris = this.color;
                }
            },
            updateModel: function(ev) {
                var sender = $(ev.currentTarget),
                    name = sender.attr('name'),
                    value = sender.val(),
                    first, model;

                if ( sender.is('[type="checkbox"],[type="radio"]') ) {
                    value = sender.is(':checked') ? value : false;
                }

                name = name.split('.');
                first = name.shift();
                model = this.model[first];

                if ( name.length ) {
                    _.each(name, function (t) {
                        model[t] = value;
                    }, this);
                    this.model[first] = model;
                } else {
                    this.model[first] = value;
                }
            },
            getModel: function() {
                return this.model;
            },
            toggleCertificateSettings: function(ev) {
                var boxes = this.$('.box-cert-settings'),
                    sender = $(ev.currentTarget),
                    is_checked = sender.is(':checked');

                if ( 'use_cp_default' === sender.attr('name') ) {
                    boxes[ is_checked ? 'slideUp' : 'slideDown']();
                } else {
                    boxes[ is_checked ? 'slideDown' : 'slideUp']();
                }
            },
            previewCertificate: function() {
                var model = new CoursePress.Request( this.getModel() );
                model.set( 'action', 'preview_certificate' );
                model.on( 'coursepress:success_preview_certificate', this.openPreview, this );
                model.save();
            },
            openPreview: function( data ) {
                if ( data.pdf ) {
                    this.preview = new CertificatePreview(data);
                } else {
                    // @todo: show friendly error
                }
            }
        });
    });
})();
(function() {
    'use strict';

    CoursePress.Define( 'ShortcodesSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-shortcodes-setting-tpl',
            el: $('#coursepress-setting-shortcodes'),
            events: {
                'click .cp-select-list.cp-type li': 'typeSelect',
                'click .cp-select-list.cp-sub-type li': 'subTypeSelect'
            },

            typeSelect: function(ev) {
                var target = $(ev.currentTarget);
                var subtype = target.data( 'id' );
                //this.setActiveItem( target );
                this.$('.cp-sub-type').addClass( 'inactive' );
                this.$('.cp-sub-type li').removeClass('active');
                this.$('#' + subtype ).removeClass( 'inactive' );
                this.$( '.cp-shortcode-details' ).removeClass( 'active' ).addClass( 'inactive' );
                this.$('#' + subtype + ' li').first().trigger( 'click' );
                target.siblings().removeClass('active');
                target.addClass( 'active' );
            },

            subTypeSelect: function(ev) {
                var target = $(ev.currentTarget);
                this.setActiveItem( target );
                this.$( '.cp-shortcode-details' ).removeClass( 'active' ).addClass( 'inactive' );
                this.$( '#' + target.data( 'id' ) ).removeClass( 'inactive' ).addClass( 'active' );
            },

            setActiveItem: function(target) {
                target.siblings().removeClass('active');
                target.addClass( 'active' );
            }
        });
    });
})();
(function() {
    'use strict';

    CoursePress.Define( 'ExtensionsSettings', function( $, doc, win ) {
        var Extension, Post;

        Extension = CoursePress.View.extend({
            type: false,
            initialize: function(model, options) {
                _.extend( this, options );
                this.model[this.type] = ! _.isObject( model ) ? {} : model;
                this.render();
            },
            render: function() {
                CoursePress.View.prototype.render.apply( this );

                this.$el.appendTo( '#extension-' + this.type );

                return this;
            },
            updateModel: function( ev ) {
                var input, name, type, value;

                input = $(ev.currentTarget);
                name = input.attr('name');

                if ( ( type = input.attr('type') ) &&
                    _.contains(['checkbox', 'radio'], type ) ) {
                    value = input.is(':checked') ? input.val() : false;
                } else {
                    value = input.val();
                }

                this.model[this.type][name] = value;
                var c = this.controller.setting.model.get(this.type);
                window.console.log(c);
                this.controller.setting.model.set( this.type, this.model[this.type] );
            }
        });

        Post = new CoursePress.Request();

        return CoursePress.View.extend({
            template_id: 'coursepress-extensions-setting-tpl',
            el: $( '#coursepress-setting-extensions' ),
            extensions: {},
            setting: false,
            initialize: function( extensions, settingObject ) {
                window.console.log(extensions);
                this.model = {extensions: extensions};
                this.setting = settingObject;

                this.render();
            },
            render: function() {
                CoursePress.View.prototype.render.apply( this );

                _.each( this.model.extensions, function( ext ) {
                    this.showExtension(ext);
                }, this );
            },
            updateModel: function(ev) {
                var target = this.$(ev.currentTarget),
                    value = target.val(),
                    is_checked = target.is(':checked'),
                    extensions = this.model.extensions;

                if ( is_checked ) {
                    if ( 'woocommerce' === value && _.contains( extensions, 'marketpress') ||
                        'marketpress' === value && _.contains( extensions, 'woocommerce' ) ) {
                        this.popup = new CoursePress.PopUp({
                            type: 'error',
                            message: win._coursepress.messages.no_mp_woo
                        });

                        target.prop( 'checked', false );
                        return false;
                    }
                }

                this.model.extensions = _.without( this.model.extensions, value );

                if ( is_checked ) {
                    this.model.extensions.push( value );
                    this.showExtension(value);
                } else {
                    this.hideExtension(value);
                }
            },
            showExtension: function( value ) {
                if ( ! this.extensions[value] ) {
                    var tpl = $('#coursepress-' + value + '-tpl' );

                    if ( ! tpl.length ) {
                        return;
                    }

                    // Initialize extension settings
                    this.extensions[value] = new Extension( this.setting.model.get(value), {
                        template_id: 'coursepress-' + value + '-tpl',
                        type: value,
                        controller: this
                    });
                }
            },
            hideExtension: function( value ) {
                if ( this.extensions[value] ) {
                    this.extensions[value].remove();
                    delete this.extensions[value];
                }
            },
            getModel: function() {
                var extensions = this.model.extensions;

                // MP and woo should not be activated at the same time
                if ( _.contains( extensions, 'marketpress') &&
                    _.contains( extensions, 'woocommerce' ) ) {
                    this.popup = new CoursePress.PopUp({
                        type: 'error',
                        message: win._coursepress.messages.no_mp_woo
                    });
                    return false;

                } else if ( _.contains( extensions, 'marketpress' ) ) {
                    // Extract and activate MP
                    Post.set( 'action', 'activate_marketpress' );
                    Post.off( 'coursepress:success_activate_marketpress' );
                    Post.on( 'coursepress:success_activate_marketpress', this.MPActivated, this );
                    Post.save();
                } else if ( _.contains( extensions, 'woocommerce' ) ) {
                    // Check WooCommerce and activae woo
                }

                return extensions;
            },
            MPActivated: function() {}
        });
    });

})();
(function() {
    'use strict';

    CoursePress.Define( 'ImportExportSettings', function( $, doc, win ) {
        var CourseImport;

        CourseImport = CoursePress.View.extend({
            events: {
                'submit': 'uploadFile',
                'change [name="import"]': 'validateFile',
                'change [name]': 'updateModel'
            },
            initialize: function() {
                this.uploadModel = new CoursePress.Upload();
                this.model = new CoursePress.Request();
                this.render();
            },
            render: function() {
                this.errorContainer = this.$('.cp-alert-error');
            },
            uploadFile: function() {
                var valid = this.validateFile();

                if ( valid ) {
                    this.uploadModel.set( 'type', 'import_file' );
                    this.uploadModel.off( 'coursepress:success_import_file' );
                    this.uploadModel.on( 'coursepress:success_import_file', this.uploadCourse, this );
                    this.uploadModel.upload();
                }

                return false;
            },

            uploadCourse: function( data ) {
                this.model.set( 'action', 'import_course' );
                this.model.set( data );
                this.model.off( 'coursepress:success_import_course' );
                this.model.on( 'coursepress:successs_import_course', this.maybeContinue, this );
                this.model.save();
            },

            maybeContinue: function() {
            },

            validateFile: function() {
                var file = this.$('[name="import"]'),
                    value = file.val(),
                    file_type = value.substring( value.lastIndexOf('.') +1 );

                if ( 'json' !== file_type ) {
                    this.errorContainer.html( win._coursepress.text.invalid_file_type ).show();
                    this.$el.addClass('active');
                    return false;
                } else {
                    this.errorContainer.hide();
                    this.$el.removeClass('active');
                    return true;
                }
            }
        });

        return CoursePress.View.extend({
            template_id: 'coursepress-import-export-setting-tpl',
            el: $('#coursepress-setting-import-export'),
            initialize: function() {
                this.on( 'view_rendered', this.setUpForms, this );
                this.render();
            },
            setUpForms: function() {
                this.importForm = CourseImport.extend({el: this.$('#form-import') });
                this.importForm = new this.importForm();
                //this.exportForm = this.$('#form-export');
            }
        });
    });
})();

(function() {
    'use strict';

    CoursePress.Define( 'Settings', function( $, doc, win ) {
        var Settings;

        Settings = CoursePress.View.extend({
            el: $('#coursepress-settings'),
            settings: {},
            currentPage: 'general', // Start with general settings
            currentView: false,
            events: {
                'click .cp-menu-item': 'setSettingPage',
                'click .save-coursepress-setting': 'saveSetting',
                'click .step-cancel': 'goToGeneral',
                'change [name]': 'updateModel'
            },

            initialize: function() {
                this.once( 'coursepress:admin_setting_general', this.getGeneralSettingView, this );
                this.once( 'coursepress:admin_setting_slugs', this.getSlugsSettingView, this );
                this.once( 'coursepress:admin_setting_email', this.getEmailSettingView, this );
                this.once( 'coursepress:admin_setting_capabilities', this.getCapabilitiesView, this );
                this.once( 'coursepress:admin_setting_basic_certificate', this.getCertificateView, this );
                this.once( 'coursepress:admin_setting_shortcodes', this.getShortCodesView, this );
                this.once( 'coursepress:admin_setting_extensions', this.getExtensionsView, this );
                this.once( 'coursepress:admin_setting_import-export', this.getImportExportView, this );

                CoursePress.View.prototype.initialize.apply( this, arguments );
            },

            render: function() {
                this.settingPages = this.$('.cp-menu-item');
                this.cancelButton = this.$('.step-cancel');
                this.saveButton = this.$('.save-coursepress-setting' );
                this.on( 'coursepress:admin_setting', this.setCurrentPage, this );
                this.setPage( this.currentPage );
            },

            setCurrentPage: function() {
                this.currentMenu = this.$('.cp-menu-item.setting-' + this.currentPage );
                this.currentView = this.$( '#coursepress-setting-' + this.currentPage );

                this.currentMenu.addClass('active');
                this.currentMenu.siblings().removeClass('active');
                this.currentView.addClass( 'tab-active' );
                this.currentView.siblings().removeClass('tab-active');

                if ( 'general' === this.currentPage ) {
                    // Disable cancel button
                    this.cancelButton.attr('disabled', 'disabled');
                } else {
                    this.cancelButton.removeAttr('disabled');
                }

                if ( 'import-export' === this.currentPage ) {
                    this.cancelButton.hide();
                    this.saveButton.hide();
                } else {
                    this.cancelButton.show();
                    this.saveButton.show();
                }
            },

            setPage: function( setting ) {
                this.currentPage = setting;

                this.trigger( 'coursepress:admin_setting', setting );
                this.trigger( 'coursepress:admin_setting_' + setting );
            },

            setSettingPage: function(ev) {
                var target = $(ev.currentTarget),
                    setting = target.data('setting');

                this.setPage( setting );
            },

            getGeneralSettingView: function() {
                this.settings.general = new CoursePress.GeneralSettings( this.model.get('general') );
            },

            getSlugsSettingView: function() {
                this.settings.slugs = new CoursePress.SlugsSettings( this.model.get( 'slugs' ) );
            },

            getEmailSettingView: function() {
                this.settings.email = new CoursePress.EmailSettings( this.model.get( 'email' ) );
            },

            getCapabilitiesView: function() {
                this.settings.capabilities = new CoursePress.CapabilitiesSettings( this.model.get('capabilities') );
            },

            getCertificateView: function() {
                this.settings.basic_certificate = new CoursePress.CertificateSettings( this.model.get('basic_certificate') );
            },

            getShortCodesView: function() {
                this.settings.shortcodes = new CoursePress.ShortcodesSettings();
            },

            getExtensionsView: function() {
                this.settings.extensions = new CoursePress.ExtensionsSettings( this.model.get('extensions'), this );
            },

            getImportExportView: function() {
                this.settings['import-export'] = new CoursePress.ImportExportSettings();
            },

            saveSetting: function(ev) {
                var settingModel = this.settings[ this.currentPage ],
                    button = this.$(ev.currentTarget),
                    model = settingModel.getModel();

                if ( model ) {
                    button.addClass('cp-progress');
                    this.model.set(this.currentPage, model);
                    this.model.set('action', 'update_settings');
                    this.model.off( 'coursepress:success_update_settings' );
                    this.model.on( 'coursepress:success_update_settings', this.after_update, this );
                    this.model.off( 'coursepress:error_update_settings' );
                    this.model.on( 'coursepress:error_update_settings', this.after_update, this );
                    this.model.save();
                }
            },

            goToGeneral: function() {
                this.$( '.cp-menu-item.setting-general' ).trigger( 'click' );
                $(win).scrollTop(0);
            },

            after_update: function() {
                var button = this.$('.save-coursepress-setting');
                button.removeClass('cp-progress');
            }
        });

        Settings = new Settings( win._coursepress.settings );

        return Settings;
    });
})();
