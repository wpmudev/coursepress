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
Check if user is an administrator.

###is_instructor()
Check if user is an instructor of any course.

###is_facilitator()
Check if user is a facilitator of any course.

###is_student()
Check if user is a student of any course.

###is_enrolled_at( int `$course_id` )
Check if user is enrolled at the given course ID.

###is_instructor_at( int `$course_id` )
Check if user is an instructor at the given course ID.

###is_facilitator_at( int `$course_id` )
Check if user is a facilitator of the given course ID.

###get_instructor_profile_link()
Returns instructor profile link if user is an instructor of any course. Otherwise false.

###get_name()
###get_avatar()

###get_accessable_courses( `string` $course_status, `bool` $ids_only, `bool` $all )
Returns the list of courses where user have access at. Each **course** is an instance of **CoursePress_Course** class object.

###has_access_at( `int` $course_id )
Check if user has administrative, instructor or facilitator access to the given course ID.

###get_response( `int` $course_id, `int` $unit_id, `int` $step_id )
Returns user response of the given step ID.

###is_course_completed( `int` $course_id )
Check if user had completed the given course ID.
The completion status return here only provide status according to user interaction and course requisite. It does not tell if the user have pass nor failed the course.

###get_course_grade( `int` $course_id )
Returns user's acquired course grade.

###get_course_progress( `int` $course_id )
Returns user's course progress percentage.

###get_course_completion_status( `int` $course_id )
Returns user's course completion status. Statuses are `ongoing`|`passed`|`failed`.
User is automatically mark as failed if the course had already ended.

###get_unit_grade( `int` $course_id, `int` $unit_id )
Returns users's grade of the given unit ID.

###get_unit_progress( `int` $course_id, `int` $unit_id )
Returns user's progress of the given unit ID.

###is_unit_seen( `int` $course_id, `int` $unit_id )
Check if user have already seen the unit.

###is_unit_completed( `int` $course_id, `int` $unit_id )
Check if user has completed the unit.

###has_course_unit_pass( `int` $course_id, `int` $unit_id )
Check if user have pass the unit.

###get_module_progress( `int` $course_id, `int` $unit_id, `int` $module_id )
Returns progress percentage of the given module ID.

###is_module_seen( `int` $course_id, `int` $unit_id, `int` $module_id )
Check if user has seen the given module ID.

###is_module_completed( `int` $course_id, `int` $unit_id, `int` $module_id )
Check if user have completed the given module ID.

###get_step_grade( `int` $course_id, `int` $unit_id, `int` $step_id )
Returns users' grade of the given step ID.

CoursePress_Shortcode
-

CoursePress_VirtualPage
-
