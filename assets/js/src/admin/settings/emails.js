/* global CoursePress, _, tinyMCE */

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
                var self = this;

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