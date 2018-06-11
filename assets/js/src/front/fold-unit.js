/* global CoursePress, _coursepress */

(function() {
    'use strict';
    CoursePress.Define( 'FoldCourseUnits', function( $, doc ) {
        var foldMenu,foldButton,target, toggleButton, toggleMenu;
        /**
         * fold
         */
        foldButton = $( '<span class="fold"></span>' ).insertBefore( $('.unit') );
        foldMenu = function() {
            target = $(this).next().find('.module-tree');
            $(this).toggleClass('folded');
            if ($(this).is( '.folded' )) {
                target.slideUp();
            } else {
                target.slideDown();
            }
        };
        $(doc).on( 'click', '.fold', foldMenu );
        /**
         * toggle
         */
        toggleButton = $( '<span class="toggle">'+_coursepress.text.shortcodes.unit_archive_list.unfold+'</span>' ).insertBefore( $('.course-structure') );
        toggleMenu = function() {
            if ( $(this).hasClass('open') ) {
                $('.course-structure').hide();
                $(this).removeClass('open').html( _coursepress.text.shortcodes.unit_archive_list.unfold);
            } else {
                $('.course-structure').show();
                $(this).addClass('open').html( _coursepress.text.shortcodes.unit_archive_list.fold);
            }
        };
        $(doc).on( 'click', '.toggle', toggleMenu);
    });
})();
