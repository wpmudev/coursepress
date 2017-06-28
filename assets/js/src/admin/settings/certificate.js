/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'CertificateSettings', function( $ ) {
        return CoursePress.View.extend({
            template_id: 'coursepress-certificate-setting-tpl',
            el: $('#coursepress-setting-basic_certificate'),
            initialize: function( model ) {
                this.model = model;
window.console.log(model);
                this.on( 'view_rendered', this.setUpUI, this );
                this.render();
            },
            setUpUI: function() {
                this.$('.switch-tmce').trigger('click');
                this.certBG = new CoursePress.AddImage( this.$('#coursepress-cert-bg' ) );
            }
        });
    });
})();