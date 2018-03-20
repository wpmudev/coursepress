/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'FoldCourseUnits', function( $, doc ) {

      var foldMenu,foldButton,target;

      foldButton = $( '<span class="fold"></span>' ).insertBefore( $('.unit') );

      target = $('.fold').next().find('.module-tree');

      foldMenu = function() {

        $('.fold').toggleClass('folded');

        if ($('.fold').is( '.folded' )) {
          target.slideUp();
        } else {
          target.slideDown();
        }

      };

      $(doc).on( 'click', '.fold', foldMenu );

    });
})();
