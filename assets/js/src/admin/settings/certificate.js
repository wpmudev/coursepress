/* global CoursePress, _, tinyMCE */

(function() {
    'use strict';

    CoursePress.Define( 'CertificateSettings', function( $, doc ) {
        var iris, CertificatePreview;

        $(doc).on( 'click', function(ev) {
           var sender = $(ev.currentTarget);

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

                if ( ( this.model.enabled !== undefined && ! this.model.enabled ) || this.model.use_cp_default ) {
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
            toggleCertificateSettings: function() {
                var boxes = this.$('.box-cert-settings');
                var enable = this.$('input[name=enabled]', this.$('.cp-box-certificate-options' )).is(':checked');
                var use_cp_default = this.$('input[name=use_cp_default]', this.$('.cp-box-certificate-options' )).is(':checked');
                boxes[ ( enable && ! use_cp_default ) ? 'slideDown' : 'slideUp' ]();
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
