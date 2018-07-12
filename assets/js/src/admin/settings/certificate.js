/* global CoursePress, _ */

(function() {
    'use strict';

    CoursePress.Define( 'CertificateSettings', function( $, doc ) {
        var iris, CertificatePreview;

        $(doc).on( 'click', function(ev) {
           var sender = $(ev.target);
           if ( iris && ( ! sender.is(iris) && ! sender.is('.iris-picker') ) ) {
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
                this.model = ! model ? {} : model;
                this.on( 'view_rendered', this.setUpUI, this );
                this.render();
            },
            setUpUI: function() {
                var self = this;
                this.$('select').select2();
                new CoursePress.AddImage( this.$('#coursepress-cert-bg' ) );
                new CoursePress.AddImage( this.$('#coursepress-logo-img' ) );
                this.color = this.$('[name="cert_text_color"]');
                this.color.iris({
                    palettes: true,
                    hide: true,
                    width: 220,
                    change: function( ) {
                        self.model.cert_text_color = self.color.iris('color');
                    }
                });
                self.model.cert_text_color = this.color.iris('color');
                this.visualEditor({
                    container: this.$('.content_certificate_editor'),
                    content: this.model.content,
                    callback: function (content) {
                        self.model.content = content;
                    }
                });
                this.certBox = this.$('.box-cert-settings' );
                if ( ( this.model.enabled !== undefined && ! this.model.enabled ) || this.model.use_cp_default ) {
                    this.certBox.hide();
                }
                /**
                 * Hide "Use default CoursePress certificate" option.
                 */
                if ( this.model.enabled !== undefined && ! this.model.enabled ) {
                    this.$('.option-use_cp_default').hide();
                }
            },
            updateCertificateContent: function() {
                this.model.content = this.contentEditor.getContent();
            },
            showColorPicker: function() {
                if ( !iris && this.color ) {
                    this.color.iris('show');
                    iris = this.color;
                }
            },
            updateModel: function(ev) {
                var sender = $(ev.currentTarget),
                    name = sender.data('name'),
                    value = sender.val(),
                    first, model;
                if ( sender.is('[type="checkbox"],[type="radio"]') ) {
                    value = sender.is(':checked') ? value : false;
                }
                if ( ! name ) {
                    name = sender.attr('name');
                }
                if ( undefined === typeof name ) {
                    name = sender.attr('name');
                }
                if ( undefined === typeof name || 'undefined' === name ) {
                    return;
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
            toggleCertificateSettings: function() {
                var boxes = this.$('.box-cert-settings');
                var enable = this.$('input[name=enabled]', this.$('.cp-box-certificate-options' )).is(':checked');
                var use_cp_default = this.$('input[name=use_cp_default]', this.$('.cp-box-certificate-options' )).is(':checked');
                boxes[ ( enable && ! use_cp_default ) ? 'slideDown' : 'slideUp' ]();
                /**
                 * Hide "Use default CoursePress certificate" option.
                 */
                this.$('.option-use_cp_default')[ enable ? 'slideDown':'slideUp']();
            },
            previewCertificate: function(ev) {
                var previewButton, model = new CoursePress.Request( this.getModel() );
                previewButton = this.$(ev.currentTarget);
                previewButton.prop('disabled', true);
                model.set( 'action', 'preview_certificate' );
                model.on('coursepress:success_preview_certificate', function (data) {
                    this.preview = new CertificatePreview(data);
                    previewButton.prop('disabled', false);
                }, this);
                model.on('coursepress:error_preview_certificate', function (data) {
                    new CoursePress.PopUp({
                        type: 'error',
                        message: data.message
                    });
                    previewButton.prop('disabled', false);
                });
                model.save();
            }
        });
    });
})();
