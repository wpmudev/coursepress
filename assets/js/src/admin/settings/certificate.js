/* global CoursePress */

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
                'focus [name="text_color"]': 'showColorPicker'
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
            },
            showColorPicker: function() {
                if ( this.color ) {
                    this.color.iris('show');
                    iris = this.color;
                }
            }
        });
    });
})();