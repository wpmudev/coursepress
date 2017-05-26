var CoursePress = (function ($, doc, win) {
    var self = {};

    self.Define = function( name, callback ) {

        if ( ! self[name] )
            self[name] = callback.call(null, $, doc, win);
    };

    return self;
}(jQuery, document, window));