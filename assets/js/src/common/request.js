/* global CoursePress */

(function() {
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

            parse: function (response) {
                var action = this.get('action');

                if (response.success)
                    this.trigger('coursepress:success_' + action, response.data);
                else
                    this.trigger('coursepress:error_' + action, response.data);
            },

            serverError: function () {
                // @todo: Show friendly error here
            }
        })
    });
})();