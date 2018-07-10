/* global CoursePress, _coursepress */

(function() {
    'use strict';
    CoursePress.Define( 'FoldCourseUnits', function( $, doc ) {
        var foldMenu,foldButton,target, toggleButton, toggleMenu, foldWorkbookButton, foldWorkbook;
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
        $(doc).on( 'click', '.course-structure .fold', foldMenu );
        /**
         * fold workbook
         */
        foldWorkbookButton = $( '<span class="fold"></span>' ).insertBefore( $('tr.row-unit td span') );
        foldWorkbook = function() {
            target = $(this).closest( 'tbody' );
            target = $('tr.row-module, tr.row-step', target );
            $(this).toggleClass('folded');
            if ($(this).is( '.folded' )) {
                target.slideUp();
            } else {
                target.slideDown();
            }
        };
        $(doc).on( 'click', '.workbook-table .fold', foldWorkbook );

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
