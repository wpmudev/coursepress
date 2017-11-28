/* global ajaxurl */
(function( $ ) {
	$( document ).ready( function() {
        function coursepressFormatCourse (course) {
            if (course.loading) {
                return course.text;
            }
            var markup = '<div class="select2-result-course clearfix">' +
                '<div class="select2-result-course__meta">' +
                '<div class="select2-result-course__title">' + course.post_title + '</div>';
            if (course.description) {
                markup += '<div class="select2-result-course__description">' + course.description + '</div>';
            }
            markup += '</div></div>';
            return markup;
        }
        function coursepressFormatCourseSelection (course) {
            var data = {
                'action': 'coursepress_get_course_units',
                'course_id': course.id,
                '_wpnonce': $('#coursepress-notifications .option-unit_id select').data('nonce')
            };
            jQuery.get(ajaxurl, data, function(response) {
                var units = $('[name=unit_id]');
                var options = '<option value="'+units.data('all-value')+'">'+units.data('all-label')+'</option>';
                var v = parseInt( units.data('value') );
                units.empty();
                $.each( response.data, function( key, value ) {
                    options += '<option value="'+value.ID+'" data-key="'+key+'"';
                    if ( v === parseInt( value.ID ) ) {
                        options += ' selected="selected"';
                    }
                    options += '>'+value.post_title+'</option>';
                });
                units.html(options);
            });
            return course.post_title || course.text;
        }
        $('#coursepress-notifications .option-course_id .select2').select2({
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        action: 'coursepress_courses_search',
                        _wpnonce: $(this).data('nonce')
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) { return markup; },
            minimumInputLength: 1,
            templateResult: coursepressFormatCourse,
            templateSelection: coursepressFormatCourseSelection
        });
	});
})( jQuery );
