/* jshint -W065 */
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
            cookies = {};
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

         self.progressIndicator = function() {
            var progress = $( '<span class="cp-progress-indicator"><i class="fa fa-spinner fa-spin"></i></span>' ),
               check = '<i class="fa fa-check"></i>',
               error = '<i class="fa fa-remove"></i>'
            ;

            return {
               icon: progress,
               success: function( message ) {
                  message = ! message ? '' : message;
                  progress.addClass( 'success' ).html( check + message );
                  progress.fadeOut( 3500, progress.remove );

                  win.CoursePress.Events.trigger( 'coursepress:progress:success' );
               },
               error: function( message ) {
                  message = ! message ? '' : message;
                  progress.addClass( 'error' ).html( error + message );
                  progress.fadeOut( 3500, progress.remove );

                  win.CoursePress.Events.trigger( 'coursepress:progress:error' );
               }
            };
         };

        return self;
    }(jQuery, document, window));
})();
