/* global CoursePress, _ */

(function() {
    'use strict';

    CoursePress.Define( 'CertificateSettings', function( $, doc ) {
        var iris;

        $(doc).on( 'click', function(ev) {
           var sender = $(ev.currentTarget);

           if ( iris && ( ! sender.is(iris) || ! sender.is('.iris-picker') ) ) {
               iris.iris('hide');
               iris = false;
           }
        });

        return CoursePress.View.extend({
            template_id: 'coursepress-certificate-setting-tpl',
            el: $('#coursepress-setting-basic_certificate'),
            events: {
                'focus [name="text_color"]': 'showColorPicker',
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
                this.$('.switch-tmce').trigger('click');
                this.certBG = new CoursePress.AddImage( this.$('#coursepress-cert-bg' ) );
                this.color = this.$('[name="text_color"]');

                this.color.iris({
                    palettes: true,
                    hide: true,
                    width: 220
                });

                // Toggle certificate settings on first load
                this.$('[name="enabled"]').trigger('change');
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

                if ( 'cp_use_default' === sender.attr('name') ) {
                    boxes[is_checked ? 'slideUp' : 'slideDown']();
                } else {
                    boxes[ is_checked ? 'slideDown' : 'slideUp']();
                }
            },
            previewCertificate: function() {
                var model = new CoursePress.Request(this.model);
                model.set( 'action', 'preview_certificate' );
                model.on( 'coursepress:success_preview_certificate', this.openPreview, this );
                model.save();
            },
            openPreview: function( data ) {
                if ( data.pdf ) {
                    window.location = data.pdf;
                } else {
                    // @todo: show friendly error
                }
            }
        });
    });
})();