/* global CoursePress */

(function() {
    'use strict';

    CoursePress.Define( 'CourseSettings', function($) {
        return CoursePress.View.extend({
            el: $('#course-settings'),
            template_id: 'coursepress-course-settings-tpl',
            courseEditor: false,
            events: {
                'click #cp-create-cat': 'createCategory',
                'keyup .cp-categories-selector .select2-search__field': 'updateSearchValue',
            },
            initialize: function(model, EditCourse) {
                this.model = model;
                this.request = new CoursePress.Request();
                this.courseEditor = EditCourse;

                EditCourse.on('coursepress:validate-course-settings', this.validate, this);

                this.on( 'view_rendered', this.setUpUI, this );

                this.request.on( 'coursepress:success_create_course_category', this.updateCatSelection, this );

                this.render();
            },
            validate: function() {
                // @todo: do course settings validataion
            },
            setUpUI: function() {
                // set feature image
                this.listing_image = new CoursePress.AddImage( this.$('#listing_image') );

                // set category
                var catSelect = this.$('#course-categories');
                catSelect.select2({
                    placeholder: catSelect.attr('placeholder'),
                    tags: true,
                });

                this.$('[name="meta_enrollment_type"]').select2();
            },

            /**
             * Create new course category.
             *
             * @param ev Current selector.
             */
            createCategory: function () {
                var name = this.$('#course-categories-search').val();
                if ('' !== name) {
                    this.request.set( {
                        'action': 'create_course_category',
                        'name': name
                    } );
                    this.request.save();
                }
            },

            /**
             * Update category selector.
             *
             * @param response Ajax response data.
             */
            updateCatSelection: function (response) {
                var selected = this.$('#course-categories').val();
                selected = '' === selected ? [] : selected;
                selected.push(response);
                this.$('#course-categories').val(selected).trigger('change');
            },

            /**
             * Update hidden field value for search.
             *
             * @param ev
             */
            updateSearchValue: function (ev) {
                var target = $(ev.currentTarget);
                this.$('#course-categories-search').val(target.val());
            }
        });
    });
})();