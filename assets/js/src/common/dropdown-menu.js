/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'DropDownMenu', function() {
        var DropDownMenu, findDropDown;

        DropDownMenu = CoursePress.View.extend({
            events: {
                'click .cp-dropdown-btn': 'toggleMenu'
            },
            render: function() {
                this.menuList = this.$('.cp-dropdown-menu');
            },
            toggleMenu: function() {
                var isOpen = this.menuList.is('.open');

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