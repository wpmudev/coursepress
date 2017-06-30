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
                'click .cp-box-content.cp-box-index a': 'toggleBox'
            },
            initialize: function() {
                this.once( 'coursepress:admin_setting_general', this.getGeneralSettingView, this );
                this.once( 'coursepress:admin_setting_slugs', this.getSlugsSettingView, this );
                this.once( 'coursepress:admin_setting_emails', this.getEmailSettingView, this );
                this.once( 'coursepress:admin_setting_capabilities', this.getCapabilitiesView, this );
                this.once( 'coursepress:admin_setting_certificate', this.getCertificateView, this );
                this.once( 'coursepress:admin_setting_shortcodes', this.getShortCodesView, this );
                this.once( 'coursepress:admin_setting_extensions', this.getExtensionsView, this );
                this.once( 'coursepress:admin_setting_import-export', this.getImportExportView, this );

                CoursePress.View.prototype.initialize.apply( this, arguments );
            },

            render: function() {
                this.settingPages = this.$('.cp-menu-item');
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
                this.settings.emails = new CoursePress.EmailSettings( this.model.get( 'emails' ) );
            },

            getCapabilitiesView: function() {
                this.settings.capabilities = new CoursePress.CapabilitiesSettings( this.model.get('capabilities') );
            },

            getCertificateView: function() {
                this.settings.certificate = new CoursePress.CertificateSettings( this.model.get('certificate') );
            },

            getShortCodesView: function() {
                this.settings.shortcodes = new CoursePress.ShortcodesSettings();
            },

            getExtensionsView: function() {
                this.settings.extensions = new CoursePress.ExtensionsSettings( this.model.get('extensions'));
            },

            getImportExportView: function() {
                this.settings['import-export'] = new CoursePress.ImportExportSettings();
            },

            saveSetting: function() {
                var settingModel = this.settings[ this.currentPage ];
                this.model.set( this.currentPage, settingModel.model );

                this.model.set( 'action', 'update_settings' );
                this.model.save();
            },

            toggleBox: function(ev) {
                $('.cp-box-content.cp-box-emails').addClass('hidden');
                $('.cp-box-content.cp-box-index a').removeClass('selected');
                $(ev.currentTarget).toggleClass('selected');
                $('.cp-box-content.cp-box-'+$(ev.currentTarget).data('key')).toggleClass( 'hidden' );
                return false;
            }
        });

        Settings = new Settings( win._coursepress.settings );

        return Settings;
    });
})();
