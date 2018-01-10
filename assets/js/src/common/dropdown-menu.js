/* global CoursePress */

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
            closeMenu: function() {
                $('.cp-dropdown-menu.open').removeClass('open');
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

        $('body').on( 'click', DropDownMenu.closeMenu );

        return DropDownMenu;
    });
})();