/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'CourseCompletion', function( $, doc, win ) {
        var iris, CertificatePreview;

        $(doc).on( 'click', function(ev) {
            var sender = $(ev.target);

            if ( iris && ( ! sender.is('.iris-input') && ! sender.is('.iris-picker') ) ) {
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
            template_id: 'coursepress-course-completion-tpl',
            el: $('#course-completion'),
            courseEditor: false,
            current: 'pre_completion',
            events: {
                'focus [name="meta_cert_text_color"]': 'showColorPicker',
                'change [name="meta_basic_certificate"]': 'toggleSetting',
                'change [name]': 'updateModel',
                'click .cp-select-list li': 'switchCompletionPage',
                'focus [name]': 'removeErrorMarker',
                'click .cp-preview-cert': 'previewCertificate'

            },
            initialize: function(model, EditCourse) {
                this.model = model;
                this.courseEditor = EditCourse;
                EditCourse.on('coursepress:validate-course-completion', this.validate, this);
                EditCourse.on('coursepress:before-next-step-course-completion', this.updateCourseModel, this);

                this.on( 'view_rendered', this.setUpUI, this );

                this.render();
            },
            toggleSetting: function(ev) {
                var sender = $(ev.currentTarget),
                    is_checked = sender.is(':checked'),
                    container = this.$('#custom-certificate-setting');

                container[ is_checked ? 'slideDown' : 'slideUp' ]();
            },
            setUpUI: function() {
                var self;

                self = this;
                this.background = new CoursePress.AddImage( this.$('[name="meta_certificate_background"]') );
	            this.logo = new CoursePress.AddImage( this.$('[name="meta_certificate_logo"]') );
                this.$('select').select2();

                this.the_title = this.$('#page-completion-title');
                this.the_content = this.$('#page-completion-content');
                this.color = this.$('[name="meta_cert_text_color"]');

                this.color.iris({
                    palettes: true,
                    hide: true,
                    width: 220,
                    change: function( ) {
                        self.model.set( 'meta_cert_text_color', self.color.iris('color') );
                    }
                });

                this.$('.cp-select-list li').first().trigger( 'click' );
            },
            showColorPicker: function() {
                if ( !iris && this.color ) {
                    this.color.iris('show');
                    iris = this.color;
                }
            },
            switchCompletionPage: function( ev ) {
                var sender, page, title, description, the_page, self;

                sender = this.$(ev.currentTarget);
                page = sender.data('page');
                title = this.$('#completion-title');
                description = this.$('#completion-description');
                sender.siblings().removeClass('active');
                sender.addClass('active');
                self = this;
                this.current = page;
                the_page = win._coursepress.completion_pages[page];

                if ( the_page ) {
                    title.html( the_page.title );
                    description.html( the_page.description );

                    this.the_title.val( this.model.get( page + '_title' ) );
                    this.the_title.attr( 'name', 'meta_' + page + '_title' );
                    this.the_content.val( this.model.get( page + '_content' ) );

                    this.visualEditor({
                        content: this.model.get( page + '_content' ),
                        container: this.$('.cp-completion-content'),
                        callback: function( content ) {
                            self.model.set( 'meta_' + page + '_content', content );
                        }
                    });
                }

	            this.visualEditor({
		            content: this.model.get('basic_certificate_layout'),
		            container: this.$('.cp-certificate-layout'),
		            callback: function (content) {
			            self.model.set('meta_basic_certificate_layout', content);
		            }
	            });
            },
            validate: function() {
                var proceed = true;

                if ( ! this.the_title.val() ) {
                    this.the_title.parent().addClass('cp-error');
                    proceed = false;
                }

                if ( ! proceed ) {
                    this.courseEditor.goToNext = false;
                    return false;
                }
            },
            updateCourseModel: function() {
                this.courseEditor.updateCourse();
            },
            updateModel: function( ev ) {
                var input, name, type, value, first, model;

                input = $(ev.currentTarget);
                name = input.attr('name');

                if ( ( type = input.attr('type') ) &&
                    _.contains(['checkbox', 'radio'], type ) ) {
                    value = input.is(':checked') ? input.val() : false;
                } else {
                    value = input.val();
                }

                name = name.split('.');
                first = name.shift();
                model = this.model.get( first );

                if ( name.length ) {
                    _.each(name, function (t) {
                        model[t] = value;
                    }, this);
                    this.model.set( first, model );
                } else {
                    this.model.set( first, value );
                }
            },
            previewCertificate: function (ev) {
                var previewButton, model;

                previewButton = this.$(ev.currentTarget);
                previewButton.prop('disabled', true);

                model = new CoursePress.Request(this.model.toJSON());
                model.set('action', 'preview_certificate');
                model.on('coursepress:success_preview_certificate', function (data) {
                    if (data.pdf) {
                        this.preview = new CertificatePreview(data);
                    } else {
                        // @todo: show friendly error
                    }
                    previewButton.prop('disabled', false);
                }, this);
                model.save();
            }
        });
    });
})();