# PHP Functions and Definitions
###coursepress_get_setting( string `$key`, mixed `$default` )

Use to retrieve global coursepress setting.

**Parameters:**
* $key - The setting key to get the value at.
* $default - Default value to set when there's no setting found.

###coursepress_get_courses( array `$args` )
**Parameter:**
* An array of arguments to compose the query. Arguments are similar to **WP_Query**

Returns an array of courses where each course is an intance of **CoursePress_Course** class object.

###coursepress_get_course( int `$course_id` )
**Parameter:**
* $course_id - The ID of the course to retrieve at.

Returns **CoursePress_Course** class object.


###coursepress_get_course_url( int `$course_id` )
**Parameter:**
* The ID of the course to get the URL at.

Returns the course permalink base on the course slug setting.


###coursepress_is_admin()
Check if the current page is of coursepress admin page.


###coursepress_get_accessable_courses( int `$user_id`, string `$status`, bool `$ids_only`, bool `$returnAll` )


###coursepress_get_enrollment_types()


###coursepress_get_categories()


# PHP Classes
CoursePress_Course
-
**Parameter:**
* Course ID or WP_Post object

**Methods**
###get_settings()
Returns all settings of the course

###is_course_started()
Check if the course has already started

###has_course_ended()
###is_available()

###is_enrollment_started()
###has_enrollment_ended()
###user_can_enroll()

###course_instructors()
###get_instructors()
###count_facilitators()
###get_facilitators()
###count_students()
###get_students()
###count_certified_students()
###get_category()
###get_units( string `$unit_status`, bool `$ids_only` )
###count_units( string `$unit_status` )


CoursePress_User
-
**Parameter:**
* User ID or WP_User object

**Methods:**
###is_super_admin()
###is_instructor()
###is_facilitator()
###is_student()
###is_enrolled_at( int `$course_id` )
###is_instructor_at( int `$course_id` )
###is_facilitator_at( int `$course_id` )
###get_instructor_profile_link()
###get_name()
###get_avatar()
###get_accessable_courses( string `$course_status`, bool `$ids_only`, bool `$all` )


CoursePress_Shortcode
-

CoursePress_VirtualPage
-
