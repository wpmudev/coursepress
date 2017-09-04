/*! CoursePress - v3.0.0
 * https://premium.wpmudev.org/project/coursepress-pro/
 * Copyright (c) 2017; * Licensed GPLv2+ */
/* global jQuery, Backbone */

(function() {
    'use strict';

    window.CoursePress = (function ($, doc, win) {
        var self = {
            Events: Backbone.Events || {}
        };

        self.Define = function (name, callback) {

            if ( !self[name] ) {
                self[name] = callback.call(null, $, doc, win);
            }
        };

        self.Cookie = function( cookie_name ) {
            var cookies, name;

                cookies = {},
                name = cookie_name + '_' + win._coursepress.cookie.hash;

            return {
                get: function() {
                    // Get the list of available cookies
                    doc.cookie.split(';').map(this.trim).map(this.toObject);

                    return cookies[name] ? cookies[name] : null;
                },
                set: function( cookie_value, time ) {
                    var d, expires;
                    d = new Date();
                    expires = d.getTime() + parseInt(time);

                    doc.cookie = name + '=' + cookie_value + ';expires=' + expires + ';path=' + win._coursepress.cookie.path;
                },
                unset: function() {

                },
                trim: function(cookie) {
                    cookie = cookie.trim();
                    return cookie;
                },
                toObject: function(cookie) {
                    cookie = cookie.split('=');
                    cookies[cookie[0]] = cookie[1];
                }
            };
        };

        return self;
    }(jQuery, document, window));
})();

(function() {
    'use strict';

    CoursePress.Define('Request', function ($, doc, win) {
        return Backbone.Model.extend({
            url: win._coursepress.ajaxurl + '?action=coursepress_request',
            defaults: {
                _wpnonce: win._coursepress._wpnonce
            },

            initialize: function () {
                this.on('error', this.serverError, this);

                Backbone.Model.prototype.initialize.apply(this, arguments);
            },

            parse: function ( response ) {
                var action = this.get('action');

                if ( response.success ) {
                    this.trigger('coursepress:success_' + action, response.data);
                } else {
                    this.trigger('coursepress:error_' + action, response.data);
                }
            },

            serverError: function () {
                // @todo: Show friendly error here
            }
        });
    });
})();
(function(){
    'use strict';

    CoursePress.Define('View', function ( $, doc, win ) {
        _.mixin({
            isTrue: function (value, selected) {
                if (_.isArray(selected) ) {
                    return _.contains(selected, value);
                } else if (_.isObject(selected ) ) {
                    return !!selected[value];
                } else {
                    if ( _.isBoolean( value ) && ! _.isBoolean(selected) ) {
                        selected = parseInt(selected, 10) > 0 ? true : false;
                    }
                    return value === selected;
                }
            },
            checked: function (value, selected) {
                return _.isTrue(value, selected) ? 'checked=checked' : '';
            },
            selected: function (value, selected) {
                return _.isTrue(value, selected) ? 'selected="selected"' : '';
            },
            disabled: function( value, selected ) {
                return _.isTrue( value, selected ) ? 'disabled=disabled': '';
            },
            _getTemplate: function (template_id, data) {
                var settings = {
                        evaluate: /<#([\s\S]+?)#>/g,
                        interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                        escape: /\{\{([^\}]+?)\}\}(?!\})/g
                    },
                    tpl = $('#' + template_id);

                if ( tpl.length ) {
                    tpl = _.template( tpl.html(), null, settings);
                }

                return tpl(data);
            },
            focus: function( selector ) {
                var el = $( selector ), top;

                if ( 0 < el.length ) {
                    top = el.offset().top;
                    top -= 100;

                    $(window).scrollTop( top );
                }

                return false;
            }
        });

        return Backbone.View.extend({
            template_id: '',
            model: {},
            events: {
                'change [name]': 'updateModel',
                'focus [name]': 'removeErrorMarker'
            },
            time: 10,
            initialize: function () {
                if (arguments && arguments[0]) {
                    this.model = new CoursePress.Request(arguments[0]);
                }
                this.render();
            },
            render: function () {
                if ( ! _.isEmpty(this.template_id) ) {
                    var data = !!this.model.get ? this.model.toJSON() : this.model;
                    this.$el.html(_._getTemplate(this.template_id, data));
                }

                this.trigger( 'view_rendered' );

                /**
                 * Trigger whenever the view template is loaded
                 */
                CoursePress.Events.trigger('coursepress:view_rendered', this);

                return this;
            },
            updateModel: function(ev) {
                var input, name, type, value;

                input = $(ev.currentTarget);
                name = input.attr('name');

                if ( ( type = input.attr('type') ) &&
                    _.contains(['checkbox', 'radio'], type ) ) {
                    value = input.is(':checked') ? input.val() : false;
                } else {
                    value = input.val();
                }

                if ( !!this.model.get ) {
                    this.model.set(name, value);
                } else {
                    this.model[name] = value;
                }

                /**
                 * Trigger whenever the model is updated
                 */
                this.trigger( 'coursepress:model_updated', this.model, this );

                ev.stopImmediatePropagation();
            },
            removeErrorMarker: function( ev ) {
                var sender = this.$(ev.currentTarget),
                    error = sender.parents('.cp-error');

                if ( error.length ) {
                    error.removeClass('cp-error');
                }
            },
            visualEditor: function( options ) {
               // var self = this;

                this.time += 200;

                _.delay(function() {

                    var id, container, tpl, tpl_id, settings, mceinit, qtinit, editor,
                        content, date, is_mce;

                    date = new Date();

                    id = 'post_editor_' + date.getTime();
                    container = options.container;
                    content = options.content;

                    if (win.tinyMCEPreInit) {
                        mceinit = win.tinyMCEPreInit.mceInit['coursepress_editor'];
                        qtinit = win.tinyMCEPreInit.qtInit['coursepress_editor'];
                    }

                    tpl_id = 'coursepress-visual-editor';

                    tpl = $('#' + tpl_id).html();
                    tpl = tpl.replace(/coursepress_editor/g, id);
                    settings = {
                        evaluate: /<#([\s\S]+?)#>/g,
                        interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                        escape: /\{\{([^\}]+?)\}\}(?!\})/g
                    };
                    tpl = _.template(tpl, null, settings);
                    container.html(tpl);
                    container.find('textarea#' + id).val(content);
                    is_mce = container.find('.wp-editor-wrap').is('.tmce-active');

                    mceinit.selector = '#' + id;
                    qtinit.id = id;
                    win.tinyMCEPreInit.mceInit[id] = mceinit;
                    win.tinyMCEPreInit.qtInit[id] = qtinit;

                    win.tinymce.init(mceinit);
                    win.quicktags(qtinit);
                    editor = win.tinymce.get(id);

                    _.delay(function() {
                        if (is_mce) {
                            container.find('.switch-tmce').trigger('click');
                        } else {
                            container.find('.switch-html').trigger('click');
                        }
                    }, 100 );

                    if (editor) {
                        // Add on change callback
                        editor.on('change', function () {
                            content = editor.getContent();

                            if (options.callback) {
                                options.callback.call(null, content);
                            }
                        });
                    }
                    container.find('textarea#' + id).val(content).on('change', function () {
                        content = $(this).val();

                        if (options.callback) {
                            options.callback.call(null, content);
                        }
                    });

                }, this.time );
            }
        });
    });
})();
(function() {
    'use strict';

    CoursePress.Define( 'AddImage', function($, doc, win) {
       var frame, in_frame;

       // Determine whether or not the selected is from the frame
       in_frame = false;

       return CoursePress.View.extend({
           template_id: 'coursepress-add-image-tpl',
           input: false,
           events: {
               'change .cp-image-url': 'updateInput',
               'click .cp-btn-browse': 'selectImage',
               'click .cp-btn-clear': 'clearSelection',
               'focus .cp-image-url': 'removeErrorMarker'
           },
           data: {
               size: 'thumbnail',
               title: win._coursepress.text.media.select_image
           },
           initialize: function(input) {
               this.input = input.hide();

               if ( this.input.data('title') ) {
                   this.data.title = this.input.data('title');
               }
               if ( this.input.data('size') ) {
                   this.data.size = this.input.data('size');
               }

               this.thumbnail_id = this.input.attr('thumbnail');
               this.render();
           },
           render: function() {
               var html, data, thumbnail_id, value, src;

               thumbnail_id = this.input.data('thumbnail');
               value = src = this.input.val();

               if ( ! value ) {
                   value = '';
               }

               data = {name: this.input.attr('name'), thumbnail_id: thumbnail_id, value: value};
               html = _._getTemplate(this.template_id, data);

               this.setElement(html);
               this.$el.insertAfter(this.input);
               this.thumbnail_box = this.$('.cp-thumbnail');

               this.image_id_input = this.$('.cp-thumbnail-id');
               this.image_id_input.off('change'); // Disable hooked change event
               this.image_id_input.on('change', this.input.prop('change'));
               this.image_url_input = this.$('.cp-image-url');

               if ( thumbnail_id ) {
                   this.image_id_input.val(thumbnail_id);
               }
               if ( src ) {
                   this.setThumbnail(src);
               }
           },
           updateInput: function(ev) {
               var input = $(ev.currentTarget);
               this.input.val(input.val());

               this.input.trigger('change');

               if ( ! in_frame ) {
                   this.image_id_input.val(0);
               }
               this.image_id_input.trigger('change');
           },
           selectImage: function() {

               if ( ! win.wp || ! win.wp.media ) {
                   return; // @todo: show graceful error
               }

               if ( ! frame ) {
                   var settings = {
                       frame: 'select',
                       title: this.data.title,
                       library: ['image']
                   };

                   frame = new wp.media(settings);

                   frame.on('open', this.openMediaFrame, this);
                   frame.on('select', this.setSelectedImage, this);
               }
               frame.open();
           },
           openMediaFrame: function() {
           },
           setSelectedImage: function() {
               var selected, thumbnail, id, url;

               selected = frame.state().get('selection').first();
               id = selected.get('id');

               in_frame = true;

               if ( !!selected.attributes.sizes.thumbnail ) {
                   thumbnail = selected.attributes.sizes.thumbnail.url;
                   this.setThumbnail(thumbnail);
               }

               url = selected.attributes.url;

               // Set correct url value
               this.input.val(url);

               this.image_url_input.val(url);
               this.image_url_input.trigger('change');
               this.image_id_input.val(id);
               this.image_id_input.trigger('change');
               this.input.trigger('change');

               // Restore before closing wpmedia
               in_frame = false;
           },
           setThumbnail: function(src) {
               this.thumbnail_box.css('background-image', 'url(' + src + ')');
           },
           clearSelection: function() {
               this.image_id_input.val('');
               this.image_url_input.val('');
               this.input.val('');
               this.thumbnail_box.css('background-image', '');
           }
       });
    });
})();
(function() {
    'use strict';

    CoursePress.Define( 'AddVideo', function($, doc, win) {
       var frame, in_frame;

       // Determine whether or not the selected is from the frame
       in_frame = false;

       return CoursePress.View.extend({
           template_id: 'coursepress-add-video-tpl',
           input: false,
           events: {
               'change .cp-video-url': 'updateInput',
               'click .cp-btn-browse': 'selectVideo',
               'click .cp-btn-clear': 'clearSelection'
           },
           data: {
               title: win._coursepress.text.media.select_video
           },
           initialize: function(input) {
               this.input = input.hide();

               if ( this.input.data('title') ) {
                   this.data.title = this.input.data('title');
               }
               if ( this.input.data('size') ) {
                   this.data.size = this.input.data('size');
               }

               this.render();
           },
           render: function() {
               var html, data, value;
               value = this.input.val();

               data = {name: this.input.attr('name'), value: value};
               html = _._getTemplate(this.template_id, data);

               this.setElement(html);
               this.$el.insertAfter(this.input);

               this.video_url_input = this.$('.cp-video-url');
           },
           updateInput: function(ev) {
               var input = $(ev.currentTarget);
               this.input.val(input.val());

               this.input.trigger('change');
           },
           selectVideo: function() {

               if ( ! win.wp || ! win.wp.media ) {
                   return; // @todo: show graceful error
               }

               if ( ! frame ) {
                   var settings = {
                       frame: 'select',
                       title: this.data.title,
                       library: {
                           type: [ 'video' ]
                       }
                   };

                   frame = new wp.media(settings);

                   frame.on('open', this.openMediaFrame, this);
                   frame.on('select', this.setSelectedVideo, this);
               }
               frame.open();
           },
           openMediaFrame: function() {},
           setSelectedVideo: function() {
               var selected, id, url;

               selected = frame.state().get('selection').first();
               id = selected.get('id');

               in_frame = true;

               // We need only videos.
               if ( typeof selected.attributes.type !== 'undefined' && selected.attributes.type === 'video' ) {
                   url = selected.attributes.url;

                   // Set correct url value
                   this.input.val( url );

                   this.video_url_input.val( url );
                   this.video_url_input.trigger( 'change' );
                   this.input.trigger( 'change' );
               } else {
                   window.console.log(selected);
               }

               // Restore before closing wpmedia
               in_frame = false;
           },
           clearSelection: function() {
               this.video_url_input.val('');
               this.input.val('');
           }
       });
    });
})();
(function() {
    'use strict';

    CoursePress.Define( 'DropDownMenu', function($) {
        var DropDownMenu, findDropDown;

        DropDownMenu = CoursePress.View.extend({
            events: {
                'click .cp-dropdown-btn': 'toggleMenu'
            },
            render: function() {
                this.menuList = this.$('.cp-dropdown-menu');
            },
            toggleMenu: function() {
                var isOpen = this.menuList.is('.open'),
                    others = $('.cp-dropdown-menu').not(this.menuList);

                // Closed all other dropdowns
                others.removeClass('open');
                if ( ! isOpen ) {
                    this.menuList.addClass('open');
                } else {
                    this.menuList.removeClass('open');
                }
            }
        });

        // Find dropdown menus
        findDropDown = function(view) {
            var dropdown = view.$('.cp-dropdown');

            if ( dropdown.length ) {
                _.each(dropdown, function( menu ) {
                    var Menu = DropDownMenu.extend({el: menu});
                    new Menu();
                });
            }
        };

        CoursePress.Events.on('coursepress:view_rendered', findDropDown);

        return DropDownMenu;
    });
})();
(function() {
    'use strict';

    CoursePress.Define( 'PopUp', function() {
        return CoursePress.View.extend({
            template_id: 'coursepress-popup-tpl',
            className: 'coursepress-popup',
            events: {
                'click .btn-ok': 'Ok',
                'click .cp-btn-cancel': 'remove'
            },
            render: function() {
                CoursePress.View.prototype.render.apply( this );

                this.$el.appendTo( 'body' );
            },
            Ok: function() {
                /**
                 * Trigger whenever OK button is click
                 */
                this.trigger( 'coursepress:popup_ok', this );
                this.remove();
            }
        });
    });
})();
(function() {
    'use strict';

    CoursePress.Define( 'Upload', function( $, doc, win ) {
        return CoursePress.Request.extend({
            url: win._coursepress.ajaxurl + '?action=coursepress_upload',
            parse: function ( response ) {
                var action = this.get('type');

                if ( response.success ) {
                    this.trigger('coursepress:success_' + action, response.data);
                } else {
                    this.trigger('coursepress:error_' + action, response.data);
                }
            },
            upload: function() {
                var data = this.toJSON();

                this.save(data, {
                    iframe: true,
                    files: $(':file'),
                    data: data
                });
            }
        });
    });
})();
(function() {
    'use strict';

    CoursePress.Define( 'AddMedia', function( $, doc, win ) {
        var frame;

        return CoursePress.View.extend({
            template_id: 'coursepress-add-media-tpl',
            className: 'cp-add-media-box',
            type: 'video',
            events: {
                'click .cp-browse-btn': 'toggleFrame'
            },

            initialize: function(element) {
                this.element = element;
                this.data = element.data();
                this.model = {
                    placeholder: element.data('placeholder')
                };
                this.on( 'view_rendered', this.setUI, this );
                this.render();
            },

            setUI: function() {
                this.element.hide();
                this.$el.insertAfter( this.element );
                this.input = this.$('.cp-add-media-input');
                this.input.val(this.element.val());
            },

            toggleFrame: function() {
                if ( ! win.wp || ! win.wp.media ) {
                    return; // @todo: show graceful error
                }

                if ( ! frame ) {
                    var settings = {
                        frame: 'select',
                        title: this.data.title,
                        library: {type: [this.data.type]}
                    };

                    frame = new wp.media(settings);
                    frame.on('select', this.setSelected, this);
                }

                frame.open();
            },

            setSelected: function() {
                var selected, id;

                selected = frame.state().get('selection').first();
                id = selected.get('id');

                this.input.val( selected.attributes.url );
                this.element.val( selected.attributes.url );
                this.element.trigger( 'change' );
            }
        });
    });
})();