# PHP Functions and Definitions
inc/functions/utility.php
-

####coursepress_is_admin()
Check if the current page is one of CP admin pages.

####coursepress_get_enrollment_types()
Returns the list of enrollment types use for enrollment restriction option.

####coursepress_get_categories()
Returns the list of categories CP have.

####coursepress_get_setting( `string` $key, `mixed` $default )
Get CP global setting.

####coursepress_render( `string` $filename, `array` $args, `bool` $echo = true )
Get or include CP file.

**Parameters:**
* $filename - The absolute path location.
* $args - An array of optional arguments to set as variables.
* $echo - Whether to include the file or return as string.

####coursepress_get_template( `string` $name, `string` $slug )
Get coursepress template or load current theme's custom coursepress template.

**Parameters:**
* $name - The key template name. Example: `course`-overview.php
* $slug - The slug portion of the template part. Example: course-`overview`.php

####coursepress_get_array_val( `array` $array, `string` $key, `mixed` $default )
Helper function to get the value of an dimensional array base on key/path.

####coursepress_set_array_val( `array` $array, `string` $key, `mixed` $value)
Helper function to set an array value base on path.

####coursepress_get_option( `string` $key, `mixed` $default )
Helper function to get global option in either single or multi site.

####coursepress_get_url()
Get CoursePress courses main url.

####coursepress_user_have_comments( `int` $user_id, `int` $post_id )
Check if the given user have comments on the given course, unit or step ID.

####coursepress_progress_wheel( `array` $args )
Get HTML progress wheel block.

####coursepress_breadcrumb()
Returns CP breadcrumb HTML block

####coursepress_create_html( `string` $tag, `array` $attributes, `string` $content = '' )
Helper function to generate HTML block.


`inc/functions/user.php`
-

####coursepress_get_user_option( `int` $user_id, `string` $key )
Helper function to get user option.

####coursepress_get_user( `int` $user_id = 0 )
Returns an instance of CoursePress_User object on success of null.

**Parameter:**
* $user_id - Optional. If omitted, will use current user ID.

####coursepress_add_course_instructor( `int` $user_id, `int` $course_id )
Add user as instructor to a course.

####coursepress_delete_course_instructor( `int` $user_id, `int` $course_id )
Remove user as instructor from a course.

####coursepress_get_user_instructed_courses( `int` $user_id )
Returns an array of courses where user is an instructor at.

####coursepress_get_user_instructor_profile_url( `int` $user_id )
Returns user instructor profile link if user is an instructor of any course, otherwise return's false.

####coursepress_add_student( `int` $user_id, `int` $course_id )
Add user as student to a course.

####coursepress_delete_student( `int` $user_id, `int` $course_id )
Remove user as student from a course.

####coursepress_get_enrolled_courses( `int` $user_id )
Returns an array of courses where user is enrolled at.

####coursepress_add_course_facilitator( `int` $user_id, `int` $course_id )
Add user as facilitator to a course.

####coursepress_delete_course_facilitator( `int` $user_id, `int` $course_id )
Remove user as facilitator from the course.

####coursepress_get_user_facilitated_courses( `int` $user_id )
Returns an array of courses where user is a facilitator.

####coursepress_get_accessible_courses( `int` $user_id )
Returns an array of courses where user have access. User must be either an instructor or facilitator of the course.


`inc/functions/course.php`
-

####coursepress_get_course( `int` $course_id = 0 )
Returns an instance of CoursePress_Course object on success or WP_Error.

**Parameter:**
* $course_id - Optional. If omitted, will assume current $post ID.

####coursepress_get_courses( `array` $args )
Returns an array of courses base on the given `$args`. Arguments pattern is similar to `get_posts` arguments.

####coursepress_get_the_course_title( `int` $course_id = 0 )
Helper function to get course or current course title.

####coursepress_get_course_summary( `int` $course_id = 0, `int` $length = 140 )
Returns the course summary.

####coursepress_get_course_description( `int` $course_id = 0 )
Returns the course description.

####coursepress_get_course_media( `int` $course_id, `int` $width = 235, `int` $height = 235 )
Return's course media base on set settings.

####coursepress_get_course_availability_dates( `int` $course_id, `string` $separator = ' - ' )
Returns course start and end date, separated by the given separator.

####coursepress_get_course_enrollment_dates( `int` $course_id, `string` $separator = ' - ' )
Returns the course enrollment start and end date, separated by the given separator.

####coursepress_get_course_enrollment_button( `int` $course_id )
Returns course enrollment button, filtered by course status and current user accessibility.

####coursepress_get_course_instructors_link( `int` $course_id, `string` $separator )
Returns instructors links.

####coursepress_get_course_structure( `int` $course_id, `bool` $show_details = false )
Returns course structure.

####coursepress_get_course_permalink( `int` $course_id )

####coursepress_get_course_submenu( `int` $course_id )

####coursepress_get_course_units_archive_url( `int` $course_id )

####coursepress_get_current_course_cycle()
Returns unit, module, step, or iterated contents base on the current serve course.

####coursepress_get_previous_course_cycle_link( `string` $label = 'Previous' )

####coursepress_get_next_course_cycle_link( `string` $label = 'Next' )


`inc/functions/unit.php`
-

####coursepress_get_unit( `int` $unit_id = 0 )
Returns an instance of CoursePress_Unit on success or WP_Error.

**Parameter:**
* $unit_id - Optional. If omitted, will use the current course unit serve.

####coursepress_get_unit_title( `int` $unit_id )

####coursepress_get_unit_description( `int` $unit_id )

####coursepress_get_unit_structure( `int` $course_id, `int` $unit_id, `bool` $items_only = true, `bool` $show_details = false )