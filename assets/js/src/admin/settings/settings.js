/* global CoursePress */

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
                this.settings.extensions = new CoursePress.ExtensionsSettings( this.model.get('extensions_available'), this );
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
