# PHP Classes

`CoursePress_User`
-
**Paramter:**
* $user_id or WP_User object.

**Methods:**

####get_name()
####get_avatar( `int` $size = 42 )
####get_description()

####is_super_admin()

####is_instructor()

####is_instructor_at( `int` $course_id )

####is_facilitator()

####is_facilitator_at( `int` $course_id )

####is_student()

####is_enrolled_at( `int` $course_id )

####has_access_at( `int` $course_id )

####get_accessible_courses( `bool` $published = true, `bool` $returlAll = true )

####get_user_enrolled_at( `bool` $published = true, `bool` $returnAll = false )

####get_completion_data( `int` $course_id )

####get_response( `int` $course_id, `int` $unit_id, `int` $step_id, `array` $progress = false )

####is_course_completed( `int` $course_id )

####get_course_grade( `int` $course_id )

####get_course_progress( `int` $course_id )

####get_course_completion_status( `int` $course_id )

####get_unit_grade( `int` $course_id, `int` $unit_id )

####get_unit_progress( `int` $course_id, `int` $unit_id )

####is_unit_seen( `int` $course_id, `int` $unit_id )

####is_unit_completed( `int` $course_id, `int` $unit_id )

####has_pass_course_unit( `int` $course_id, `int` $unit_id )

####get_module_progress( `int` $course_id, `int` $unit_id, `int` $module_id )

####is_module_seen( `int` $course_id, `int` $unit_id, `int` $module_id )

####is_module_completed( `int` $course_id, `int` $unit_id, `int` $module_id )

####get_step_grade( `int` $course_id, `int` $unit_id, `int` $step_id )





