/* global CoursePress, _ */

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
                // var c = this.controller.setting.model.get(this.type);
                this.controller.setting.model.set( this.type, this.model[this.type] );
            }
        });

        Post = new CoursePress.Request();

        return CoursePress.View.extend({
            template_id: 'coursepress-extensions-setting-tpl',
            el: $( '#coursepress-setting-extensions' ),
            extensions: {},
            events: {
                'click .coursepress-extension-table .action a': 'handleExtensionButton',
                'change #coursepress-setting-extensions input[name=enabled]': 'toggleExtension'
            },
            setting: false,
            initialize: function( extensions, settingObject ) {


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
window.console.log( 'updateModel' );
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
                    /**
                     * Add to model
                     */
                    if ( ! _.contains( this.model.extensions, value ) ) {
                        this.model.extensions.push( value );
                    }
                }
            },
            hideExtension: function( value ) {
                if ( this.extensions[value] ) {
                    this.extensions[value].remove();
                    delete this.extensions[value];
                }
                this.model.extensions = _.without( this.model.extensions, value );
            },
            getModel: function() {
                var enabled = $('input.extension-commerce-enable');
                // MP and woo should not be activated at the same time
                if ( 1 < enabled.length ) {
                    this.popup = new CoursePress.PopUp({
                        type: 'error',
                        message: win._coursepress.messages.no_mp_woo
                    });
                    return false;
                }
                return enabled;
            },
            handleExtensionButton: function( ev) {
                var button = $(ev.currentTarget);
                var extension = button.closest('td').data('extension');
                var installed = button.closest('td').data('installed');
                var active = button.closest('td').data('active');
                var nonce = button.closest('td').data('nonce');
                var model = new CoursePress.Request( this.getModel() );

window.console.log(button);


                if ( 'no' === installed ) {
                    var data = {
                        message: win._coursepress.text.extensions.not_instaled
                    };
                    this.showPopUo( data, 'info' );
                    return false;
                }
                model.set( 'nonce', nonce );
                model.set( 'extension', extension );
                model.on( 'coursepress:success_activate_plugin', this.activatedPluginSuccess, this);
                model.on( 'coursepress:error_activate_plugin', this.showPopUo, this, 'error' );
                model.on( 'coursepress:success_deactivate_plugin', this.deactivatedPluginSuccess, this );
                model.on( 'coursepress:error_deactivate_plugin', this.showPopUo, this, 'error' );
                if ( 'no' === active ) {
                    this.showPopUo( { message: win._coursepress.text.extensions.activating_plugin }, 'info' );
                    model.set( 'action', 'activate_plugin' );
                } else {
                    this.showPopUo( { message: win._coursepress.text.extensions.deactivating_plugin }, 'info' );
                    model.set( 'action', 'deactivate_plugin' );
                }
                model.save();
                return false;
            },
            activatedPluginSuccess: function( data ) {
                var button = $('#extension-row-'+data.extension+' .action a');
                this.showPopUo( data, 'info' );
                button.addClass( 'cp-btn-active').removeClass( 'cp-bordered-btn' ).html( win._coursepress.text.extensions.buttons.deactivate );
                button.closest('td').data('active', 'yes' );


window.console.log( 'activatedPluginSuccess', data );
                this.showExtension( data.extension );
            },
            deactivatedPluginSuccess: function( data ) {
                var button = $('#extension-row-'+data.extension+' .action a');
                this.showPopUo( data, 'info' );
                button.removeClass( 'cp-btn-active').addClass( 'cp-bordered-btn' ).html( win._coursepress.text.extensions.buttons.activate );
                button.closest('td').data('active', 'no' );
                this.hideExtension( data.extension );
            },
            showPopUo: function( data, type ) {
                $('.coursepress-popup').detach();
                /**
                 * Show popup
                 */
                new CoursePress.PopUp({
                    type: type,
                    message: data.message
                });
            },
            toggleExtension: function(ev) {
                var extension = $(ev.currentTarget);
window.console.log( 'toggleExtension', extension );                
            }
        });
    });

})();
