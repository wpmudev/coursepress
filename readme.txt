=== CoursePress Learning Management System ===
Contributors: wpmudev
Plugin Name: CoursePress Learning Management System
Author: WPMU DEV
Author URI: http://premium.wpmudev.org/
Tags: LMS, learning management system, online course, education, e-learning, classes, courses, teach, assignments, lessons
Requires at least: 4.1
Tested up to: 4.9.6
Stable tag: 2.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The complete learning management system and online course builder for WordPress.

== Description ==

= e-Learning for WordPress =
CoursePress makes e-learning easy for WordPress. Share or sell tutorials, lectures and online courses with video training, quizzes, automated assessments, file sharing and completion certificates.

= Manage Courses Like a Pro =
Literally...just like the pros. CoursePress comes without any course limitations. Get unlimited course creation and everything you need for grading, marketing, assessments, reporting (including automatic grading and reporting), student & instructor management and everything else you'd expect from a complete Learning Management System.

[youtube https://www.youtube.com/watch?v=HXzOBRYVjDw]

= Post Your First Course Today =
Use the included CoursePress theme or widgets and shortcodes with dynamic styles to integrate CoursePress with your current WordPress theme.

Get your learning management system set up quickly and post your first course right out-of-the-box.

= Sell With WooCommerce or MarketPress =
Use any of the 100+ available MarketPress and WooCommerce payment options to start taking payments for your online courses.

Share classes for free or sell them using one of your favorite eCommerce solutions.

= More Quiz Options =
CoursePress has a bunch of options for assessing your students – multi-answer, single choice, selectable, short answer, long answer, true and false and file upload. Require students to complete each unit quiz with a qualifying grade before starting the next session.

= Online Course Moderation =
Make sure every question gets answered and assignments graded quickly – even for the big classes. CoursePress lets you add multiple instructors and course facilitators to stay on top of responses.

= Promote and Sell =
The CoursePress LMS includes everything you need to market your classes and keep students involved. Invite new users, share a course overview, embed a video preview, drip release units and send notifications when new material is available.

> = Level-up with [CoursePress](https://premium.wpmudev.org/project/coursepress-pro/) =
>
> Dreaming of building the next Udemy, Udacity, Coursera or Lynda? CoursePress can get you started for free!
>
> Use [CoursePress](https://premium.wpmudev.org/project/coursepress-pro/) to create an unlimited number of courses.
>
> #### Features available in CoursePress include:
>
> * Trusted LMS that powers the Academy on WPMU DEV
> * Unlimited Course creation
> * Tools for course promotion and marketing
> * Entice new students with a course ‘teaser’ – including previews of video, course elements and a description
> * Automated and manual assessment and reporting features – including automatic grading
> * Drip release units
> * Complete media integration
> * Included CoursePress theme and dynamically shortcode integration
> * Interactive discussion boards to help develop a more robust learning environment
> * Allow students to download and upload quiz files or any other file types
> * Genuinely assessed certification
> * Give every course a custom completion certificate
> * Assign multiple instructors and course facilitators
> * Works great with Multisite
>
> Add beautiful courses to your WordPress or Multisite network.

== Frequently Asked Questions ==

= Where do I find the included CoursePress theme? =

The CoursePress theme can be found in the plugin zip package. Download the file, unzip the package and navigate to coursepress > 2.0 > themes > coursepress.

= How do I install the CoursePress theme? =

To install the CoursePress theme, zip the “coursepress” folder and upload using “Appearance > Themes” click the “Add New” button.

Or move the "coursepress" folder from "/wp-content/plugins/coursepress/themes/" to "/wp-content/themes/" using SFTP or the cPanel's File Manager.

= Does the free version of CoursePress have any limitations? =

Nope! All of the course management features in the free and pro versions are exactly the same.

== Installation ==

= To Install =

* Download the CoursePress plugin file
* Unzip the file into a folder on your hard drive
* Upload the /coursepress/ folder to the /wp-content/plugins/ folder on your site
* Visit your Dashboard -> Plugins and Activate it there.

= To Set Up And Configure CoursePress =

You can find [in-depth setup and usage instructions with screenshots here »](http://premium.wpmudev.org/project/coursepress-pro/#usage "CoursePress plugin installation and usage help")

== Screenshots ==

1. All the elements you need to create a great course
2. Your courses have never looked so good
3. Real-time interaction with Chat
4. Will your course have a six figure launch?
5. Offer certification with your courses with grading, marking and assessment tools
6. Display and promote your courses however you like
7. Massively customizable

== Changelog ==

= 2.1.5 =
* Fixed: Added course categories information in the export file.
* Fixed: Check MarketPress integration to avoid warnings.
* Fixed: Mistakes in language strings.
* Fixed: Problem with the login form on small screens like mobile.
* Fixed: Show CoursePress Theme instructors box only when a course has at least one instructor.
* Fixed: Warnings on the "Grade" tab, when one of a module is a quiz.
* Fixed: Warnings when we use WooCommerce 3x.
* Improved: Added CoursePress version to export file.

= 2.1.4 =
* Fixed: Course certificate email settings were hidden for nonpremium users.
* Fixed: Discussion layout issue.
* Fixed: Missing new instructor on instructors list.
* Fixed: On a course structure show section without modules if "Show units without modules" is checked.
* Fixed: PHP notices when try to edit not exited unit.
* Fixed: Problem with display course sub-menu on the frontend.
* Fixed: Show unit progress on admin panel.
* Fixed: There was no ability to remove instructor.
* Fixed: Typo in notifications configuration.
* Fixed: Typo in MarketPress integration box.
* Improved: Added filters to get_time_estimation() functions: coursepress_course_get_time_estimation, coursepress_unit_get_time_estimation, coursepress_module_get_time_estimation.
* Improved: Prevent saves units before fetching all data.

= 2.1.3 =
* Fixed: Unit and Pages TinyMCE editor does not work after WordPress update to 4.9.

= 2.1.2 =
* Improved: Add ability to define certificate logo.
* Improved: Show instructor feedback on student workbook frontend.
* CoursePress Theme: Fixed display of HTML Bullet points.
* CoursePress Theme: Fixed expanded unit list view mode.
* CoursePress Theme: Improved bbPress compatibility.
* Fixed: Expanded unit list view mode not working for students.
* Fixed: Export problem on PHP 5.2, json_encode() have only one parameter.
* Fixed: Missing upgrade scripts.
* Fixed: Module access with URL.
* Fixed: Prevent access modules when previously required modules haven't been completed.
* Fixed: Problem loading translations.
* Fixed: Problem with TinyMCE.
* Fixed: Try Again button is not showing while Retry is set to 1.
* Fixed: Visual editor load problem when a user disables in the profile.

= 2.1.1 =
* Improved: Added `editor-rtl.css` file.
* Improved: Better display responsive video.
* Improved: HTML login & signup forms.
* Improved: Username sanitization.
* Fixed: Assessments feedback sending problem.
* Fixed: Compatibility with older PHP versions.
* Fixed: CoursePress shortcode links are not working.
* Fixed: Even the Discussion module is not "Required", you can't go to next section.
* Fixed: Instructors capabilities problem on multisite installation.
* Fixed: Instructor can't reply to comments on course unit.
* Fixed: Missing translation in steps 6 & 7.
* Fixed: Problems with missing passcode after login.
* Fixed: Problems with PHP 5.2.x.
* Fixed: Problem to go next unit when all answers are required but current unit do not have any assessable modules.
* Fixed: Problem with go  to next module.
* Fixed: Problem with non assessable modules and grade unit.
* Fixed: Problem with yimer on quiz module after we use "Try Again" button.
* Fixed: Sections on units page overview are not linked and not visible.
* Fixed: Show and Free Preview options in Course Structure settings.
* Fixed: Small issues with PHP 7.1.1
* Fixed: The title of "Course Structure" widget should not be visible, when it is not checked.
* Fixed: Unable to send feedback about assessments.
* Fixed: Units inside the course are available agains the settings options.
* Fixed: Visual editor is blank and missing data after module reorder.
* Updated: Vimeo javascript for version 3.0.0.

= 2.1.0.2 =
* Added: A filter 'coursepress_update_student_progress' to allow turn off student progress update during modules update.
* Fixed: Upgrade from 1x to 2x was broken.

= 2.1.0.1 =
* Fixed: Compatibility with older PHP versions.

= 2.1 =
* Added: Text color option for course certificate.
* Added: Categories argument to [course_list] shortcode to filter courses.
* Added: HTML rel attribute to next and previous buttons.
* Added: Password strength meter on student profile
* Added: The ability to disable the strength meter in the enrollment pup-up.
* Added: Option to disable emails individually
* Added: New filter: coursepress_comment_list_args to filter comments list arguments.
* Added: New filter: coursepress_comment_form_args to filter new comment form arguments.
* Added: Unit slug will be updated when you change unit title.
* Improved: Add individual nonce when withdraw a student from a course.
* Improved: Students list on assessment screen can be sorted by username and display name.
* Improved: Students list on assessment screen is more like users list.
* Improved: Students list on course edit screen can by sorted by: username, display name, first name and last name.
* Improved: Students list on course edit screen is more like users list.
* Improved: Added certificate filter on students list on course edit screen.
* Improved: Increase a course assessments page load time.
* Improved: Image display.
* Improved: Removed unused function CoursePress_Data_Course::is_course_preview()
* Improved: Better section title display - added information about no title.
* Improved: Automatic migration from users upgrading from version 1.x
* Improved: Smaller download size.
* Improved: Form module UI for better display.

* Fixed: Default unit title height to avoid collapse when empty.
* Fixed: Instructor profile course lists on multisite.
* Fixed: Empty dashboard page to non-enrolled user.
* Fixed: A minor microdata issue on the course details page.
* Fixed: In unit-builder, the checkbox options were not getting saved properly in some scenarios.
* Fixed: Multiple answers grade calculation.
* Fixed: Form grade calculation.

= 2.0.7 =
* Added: New "Paid" column on course list table.
* Added: Email validation on CoursePress signup form.
* Added: A sample course to fresh CoursePress installation.
* Added: No visible units message info.
* Added: Ability to sort by title for course_list shortcode.
* Added: Instructors notification for new enrolled students.
* Added: Text color option for basic certificate.

* Improved: Description on settings page.
* Improved: Compatibility with Avada theme.
* Improved: Disable WooCommerce guest checkout for courses checkout.
* Improved: Hide Install MarketPress message when WooCommerce Integration is active.
* Improved: Password strength for custom registration form.

* Updated: External library "select2" to version 4.0.2
* Updated: External library "jquery-chosen" to version 1.6.2
* Updated: External library "jquery-circle-progress" to version 1.2.1
* Updated: External font Awesome to version 4.7.0

* Fixed: Email placeholders.
* Fixed: "Continue Learning" link for required modules.
* Fixed: Warning messages on CoursePress configuration page when we use tab, which no longer exits.
* Fixed: Issue with tinymce edit unit page.
* Fixed: Dark screen when use "Save Progress & Exit" with discussion module.
* Fixed: Removed PHP warnings when adding units.
* Fixed: Next nav on IE.
* Fixed: Unable to progress to next module.

= 2.0.6.2 =
* Fixed: HTML and entities issue on unit editor.
* Fixed: Preview button.

= 2.0.6.1 =
* Fixed: Remove vulnerability to view any module.
* Fixed: Prerequisite Courses problem with access to next course.

= 2.0.6 =
* Added: Redirect to login page for guest users.
* Added: Number of failed attempts at student assessment.
* Added: Validation for user related functions to avoid processing when there is no logged user.

* Improved: Change "Duration" to "Time Limit" label.
* Improved: Unify all TinyMCE editors CP instance.
* Improved: Process of saving answers.
* Improved: Required unit message.
* Improved: Message box for paid course.
* Improved: Integration with WooCommerce - when student pay with PayPal, a student will be enrolled to the ordered courses.

* Updated: Bundled MarketPress to latest version.

* Fixed: Remove inclusion of CP assets on none CP pages.
* Fixed: WooCommerce "Out of stock" product status.
* Fixed: Deleting modules when section is deleted.
* Fixed: Missing untranslated texts.
* Fixed: Marketpress sale price.
* Fixed: Editor height.
* Fixed: Missing grade for manualy graded fields when using "Save & Exit".
* Fixed: Too many redirects error.
* Fixed: HTML tags not showing in Multiple/Single Choice field.
* Fixed: Ignoring "User also needs to pass all required assessments" unit option.
* Fixed: "Passing Grade" value one assessments page.
* Fixed: Link to student profile in "Number of Courses" column.
* Fixed: Form input with correct grade.

= 2.0.5 =
* Added HTML preview for Raports.
* Added ability to turn on/off social sharing icons.
* Added check is safe_mode before call set_time_limit() function.
* Added the action "coursepress_woocommerce_product_updated". This action has two variable $product_id and $course_id and is called after update product by CoursePress.
* Added check is user logged before we show comment form in discussion module.
* Added CoursePress Endpoints to Apperance -> Menu screen.
* Added ability to remember the last sorting chosen by user on the list of courses in admin area.
* Improved select2 field for prerequisites and remove current course from the list.
* Improved MarketPress product thumbnail and images integration.
* Improved link building to student profile in admin area.
* Improved HTML login & signup forms.
* FIxed on signup form double error messages.
* Fixed "Grades" screen.
* Fixed a wrong link to student profile from students tab on a course edit screen.
* Fixed a bug with prerequisites courses when we have only one prerequisite course.
* Fixed registration email tokens.
* Fixed registration form shortcode.
* Fixed a wrong icon on assessments page, it was always shown fail icon.
* Fixed missing html attributes on certificate preview.
* Fixed missing messages on login/signup form.

= 2.0.4 =
* Added ability to enroll, when we use course_join_button, but we are not logged.
* Added sanitization of course id in "course_join_button" shortcode.
* Added ability to save course state and exit.
* Added filter "coursepress_unit_add_open_date" to allow display open unit date.
* Added auto-creating missing directories for PDF certificates.
* Added ability to show "Withdraw" link on [cp_pages page="student_dashboard"].
* Added fields "Display Name", "Email" and "Registered" to "Student Profile" page.
* Added "Edit user" link on "Student Profile" page.
* Improved time count method for modules, pages and units.
* Improved login/signup popups for small screens.
* Improved integration with WooCommerce - when we change order status from completed to another status, a student will be withdrawn from the ordered courses.
* Limiting facilitator role, from now on it can only edit allowed courses.
* Improved the appearance of buttons on course frontend page.
* Improved "My Profile" screen.
* Delete related course data from usermeta table when deleting a course.
* Improve "Student Profile" screen by adding nonce check, to avoid any profile display.
* Improve login and signup screens for small screens.
* Fixed a problem with wrong courses on [cp_pages page="student_dashboard"].
* Fixed a "60 seconds" bug - when module uses a timer, there was no chance to show different amount of seconds then "60".
* Fixed a bug with the sum of time for pages and units on course edit page.
* Fixed a bug related to the inability to disable CoursePress login form.
* Fixed a bug with previewing units & modules.
* Fixed a problem with "Manage Course" button.
* Fixed a problem with access to CoursePress menu for instructors.
* Fixed a problem with enrolling students using MarketPress.
* Fixed a problem with sections pagination when course is in normal mode.
* Fixed untranslated texts.
* Fixed a bug with ability to approve comments to discussions.
* Fixed a bug when user can enroll in the course without paying to paid course.
* Fixed a bug with combination when we do not have required modules, but we check "User needs to answer all required[...]".

= 2.0.3.1 =
* Fixed: Fatal error thrown on old PHP version
* Fixed: a bug when user can enroll in the course without paying to paid course.

= 2.0.3 =
* Fixed a bug with deleting sections within modules.
* Fixed "button" in shortcodes when not on courses list.
* Fixed a bug with adding students to course in admin, when MarketPress or WooCommerce is used to sell courses.
* Fixed problem with custom CoursePress login page.
* Fixed a bug with pagination on course archive page.
* Fixed a bug with custom certificate background.
* Fixed a bug with available unit date for different languages then English.
* Fixed a bug with instructors permission to see CoursePress assessments.
* Fixed a bug with course fee with WooCommerce.
* Fixed a bug on workbook page - it show correct answers before student answered.
* Fixed a bug with post status on course edit page.
* Fixed shortcode course_list context attribute.
* Changed redirect after login, for editors it will be now WordPress Dashboard.
* Changed redirect for non-logged users who enter student dashboard URL to login page.
* Added parsing video URL to add autoplay parameter.
* Added ability to auto re-create certificate if file is missing.
* Added permission check when try to download certificate.
* Added ability to show unit description.
* Added ability to login by email too.
* Added key length check for mcrypt function to avoid wrong key size.

= 2.0.2 =
* Fixed a bug with class autoloder function.
* Added better default CoursePress certificate.
* Added "Settings" and "Support" links on WP plugins page.
* Fixed a bug with import students to course when MarketPress is active.
* Handle situation when student try to add comment to non existing module.
* Fixed "Domain Name" for proper translation.
* Added check to import json file.
* Added confirmation popup when student withdraw from the course.
* Fixed non-virtual student login page.
* Removed fakepath file location in file upload.
* Fixed student dashboard page.
* Fixed warning message when user is logged out.
* Fixed untranslated texts.
* Added allow weak password registration.

= 2.0.1 =
* Fixed: Chat module support for wordpress-chat plugin
* Fixed: Module's unlimited retry attempts
* Fixed: Language Internationalization
* Fixed: compatibility issue with Market Press CSS covering course edit
* Fixed: Student progress migration when Market Press is enabled
* Fixed: Treegrid js error
* Fixed: Units overview link
* Updated included Market Press version 3.1.2

= 2.0.0 =
* Overall optimisation through whole new codebase.
* Added 'Focus' course view mode where modules will be viewed individually.
* Added per course completion certificate option.
* Added course completion pages (pre-completion, successful completion, failed).
* Added course notification when the course starts.
* Added unit notification when a unit opens.
* Added instructor feedback notification.
* Added course facilitators.
* Added course import/exports.
* Added overview reports download option.
* Added enhanced units settings
* Added quiz, form, selectable, and discussion modules.
* Improved assessments page.
* Improved discussion management.
* Improved course/students lists.
* Removed section break module.
* Delete certificate when deleting student.

= 1.3.4.3 =
* Fixed: Problem with wrong units presentation on the reports page.
* Fixed: Issues with BBPress forums on topics page when CoursePress is active.
* Fixed: Problem with missing class in shortcode course_list.
* Fixed: Problem with unit availability if unit is available before course.
* Fixed: Wrong published units count.

= 1.3.4.2 =
* Fixed: Problem preview draft unit.
* Fixed: Copy feature image from course to WooCommerce product.
* Fixed: Problem with units on units list page.
* Fixed: Missing div bracket in cp_popup_success_message template.
* Fixed: Paid course flag when we change MarketPress to WooCommerce.
* Fixed: Missing translation domain in Woo integration.
* Fixed: Wrong instructor id used by course_list shortcode.
* Fixed: Redirect from WooCommerce shop main page.
* Fixed: Problem with Virtual Pages when a site uses SSL.
* Fixed: Problem with answers with HTML entities.

= 1.3.4.1 =
* Fixed: Skip search filter if query post types are bbpress types

= 1.3.4 =
* Fixed: Empty text field by default when resubmitting an answer

= 1.3.3 =
* Fixed: Compatibility issues with WP 4.5.

= 1.3.2 =
* Fixed: Session completion data outdated when instructor grades an answer.

= 1.3.1 =
* Fixed: AJAX url was not properly generated in some cases (if only admin  is SSL)

= 1.3 =
* Fixed: Console error when opening course categories.
* Fixed: Console error when opening New Course in page settings.
* Fixed: Issues with invite instructor.
* Fixed: Quizz assessment wrong answer count.
* Fixed: Mandatory wrong answer count.
* Fixed: Broken Unit Page Template If a unit or one of the unit set to be available in the future.
* Fixed: course_instructors shortcode do not print the list of instructors if no parameters set.
* Fixed: get instructor by hash query was not returning anything
* Enhance: Performance improvements
* Enhance: Improved Session class so it's completely overridable
* Other minor fixes
* Some code reorganization

= 1.2.6.7 =
* Fixed: Empty admin notification.
* Fixed: Assessments restricted for instructors from other courses.
* Fixed: Free preview not working for non default themes.
* Fixed: WooCommerce product visibility when course is created.
* Fixed: MarketPress product description not populated.
* Fixed: WooCommerce payment not processing course signup.
* Added: MarketPress product to course page redirection.
* Fixed: PHP warning for empty multiple choice module.
* Fixed: WooCommerce checkout causing blank pop-up.
* Added: Performance improvements.
* Fixed: Registration fields sanitization.

= 1.2.6.6 =
* Fixed: Issues in certificate admin screen.
* Fixed: Description creation in text mode.
* Fixed: Assessments not listed in multisite installs.
* Fixed: Issues with multiple paid courses.
* Fixed: Items quentity in Marketpress shopping cart.

= 1.2.6.5 =
* Updated: Notice for dynamic CoursePress editor and plugin compatibility.
* Fixed: MarketPress activation check on WordPress multisite.
* Fixed: Enrollment issues on paid courses.
* Fixed: Minor style issues.
* Fixed: Progress tracking issues.
* Fixed: Course access inconsistency.
* Fixed: PHP Notice when recording assessment grade.
* Fixed: Course description not saving when editor in text mode.
* Updated: Performance improvements on completion calculation.
* Updated: Enforced logic to prevent possible unit data loss.

= 1.2.6.4 =
* Fixed: Unit completion issues.
* Fixed: Certificate creation issues.

= 1.2.6.3 =
* Updated: Improvements for 'Submit different answer' workflow.
* Fixed: Handle cases when percentage is greater than 100.
* Fixed: Last visited page issue.
* Fixed: Fix mandatory answer not being recorded for File Input modules.
* Updated: Performance improvements on `Units` view and loading modules.
* Updated: Performance improvements with CoursePress Theme.
* Added: MarketPress 3.0.0.2 bundled with CoursePress.

= 1.2.6.2 =
* Fixed: MarketPress 3.0 fatal error warning when sending notifications.
* Fixed: Prompt for gateway settings even if gateway is enabled (MP3.0 integration)
* Fixed: Missing course prices when upgrading to MarketPress 3.0.
* Note:  CoursePress PRO: Temporarily reverting bundled MarketPress to version 2.9.6.2.

= 1.2.6.1 =
* Fixed: Resubmit link showing even if no answer has been submitted.
* Fixed: Can now freely move "back" to a previous page without having to complete mandatory elements.
* Fixed: PHP notice when attempting to make a unit live when it hasn't been saved yet.
* Fixed: Instructors can only assess their own students. Course creators need to add themselves as instructors if they want to assess.
* Fixed: Improved performance for Preview Units/Pages/Modules.
* Fixed: Input elements cannot be submitted when "Previewing" a Unit. It will display "Preview only".
* Fixed: New implementation of CoursePress editor now allows dynamic editors including Visual/HTML toggles.
* Fixed: Certificates correctly uses the selected 'Reports' font as set in settings.
* Added: Integration for MarketPress 3.0.
* Added: CoursePress now bundled with MarketPress 3.0. Note: Best way to upgrade is using WPMU Dev Dashboard. Alternatively, remove MarketPress 2.9 and install from CoursePress settings.

= 1.2.6.0 =
* Fixed completion system synchronization.
* Fixed discussion display in CoursePress theme.
* Fixed menu metabox to show all published courses.
* Fixed JavaScript conflict with sign up window.
* Fixed unordered lists in CoursePress theme.
* Fixed 1 showing when resubmitting answers.
* Fixed issues where virtual pages didn't work on some sub directory installs.
* Fixed resubmit limitations not working for some answers.
* Changed Users can now resubmit answers for file uploads and text boxes while grades are pending.
* Fixed uppercase usernames now supported by popup.
* Fixed possible compatibility issue with themes and page titles.

= 1.2.5.9 =
* Fix WP 4.3 compatibility issue with visual editors

= 1.2.5.8 =
* Fixed issues with WooCommerce sale price
* Added check for course category specific templates (i.e. archive-course-categoryslug.php)

= 1.2.5.7 =
* Fixed fatal error with single site setups

= 1.2.5.6 =
* Fixed redirection issue with multisite installations.

= 1.2.5.5 =
* Fixed issues with assessment, students and instructors tables being empty after WordPress 4.2 update.
* Fixed error message when submitting mandatory quiz items.
* Fixed error on setting a course category when first created.
* Fixed HTML showing on discussion page.
* Fixed Instructor capabilities not always saving.
* Improved handling of Virtual Pages and Custom Pages for CoursePress settings.


= 1.2.5.4 =
* Fixed issue with marking a order as paid with Manual Payments
* Fixed issue with Virtual pages
* Added additional hooks for developers
* Integration with WooCommerce (CoursePress > Settings > WooCommerce Integration)

= 1.2.5.3 =
* Security Update: Fixed possible WordPress XSS bug
* Fixed clearfix div
* Fixed broken virtual pages

= 1.2.5.2 =
* Fix missing class error for CoursePress Standard (free).

= 1.2.5.1 =
* Added basic certificate functionality to CoursePress Pro (templates planned for future release).
* Added additional capabilities for instructors
* Added formatting to the instructor single page
* Changed default 'subscriber' role for students to be actual default WordPress role set
* Fixed issue with enrolling a student to a paid course (paid via PayPal chained payments)
* Fixed issue with mandatory, assessable and limit attempts options (if once checked then unchecked)
* Fixed issue with uncompleted course even if unit elements (answer fields) were completed
* Fixed issue: Course Pre-Requisite still showing after required course completed
* Fixed theme translation issues
* Fixed issue with instructor profile pages when instructor username contains space
* Fixed issue with Course Structure links when course starts in the future
* Fixed "unit_page_title_tag_class" shortcode attribute to output valid HTML class
* Fixed issues with courses bulk actions
* Fixed issue with previewing a unit (when user needs to pass all mandatory assessments option is checked)
* Fixed issue with Order Complete Page MarketPress message
* Fixed issue with displaying 1970 date on the course calendar when clicking on the previous link
* Fixed issue with course order when Post Order Number is selected as an course order option
* Fixed issue with login and signup popup links
* Fixed issue with admin discussions pagination
* Fixed instructors courses list properly with pagination (10 courses+)
* Removed ping backs from courses (implementation on the feature request list).
* Fixed conflicts with BuddyPress Groups.
* Fixed issue with loading CoursePress styles on other admin pages.
* Fixed issue with broken file downloads in Units (sites using PHP 5.6+).
* Fixed issue where non-embeddable videos (e.g. some YouTube videos) shows nothing. Now it will show a clickable link.
* Added ability to hide related videos for YouTube videos.
* Fixed RTL issue causing horizontal scroll bug on Course Overview page.
* Fixed 0's showing up on CoursePress pages when Poll Voting Plugin is installed.
* Fixed new units automatically added to structure where it was not before.
* Fixed showing featured images in CoursePress theme.
* Fixed issue with paid courses not always enrolling when using MarketPress.
* Fixed issue with instructor marked mandatory results not calculating course completion correctly.
* Fixed broken 'Recent Posts' widget when viewing any CoursePress page.

= 1.2.5 =
* Added additional hooks and filters for developers

= 1.2.4.9 =
* Fixed: Auto correcting previous student responses for Single- and Multiple Choice questions without needing to re-submit answers.
* This release improves the changes made in version 1.2.4.8.

= 1.2.4.8 =
* Fixed potential issue when using quotation marks or special characters in Single- and Multiple Choice questions.
* Fixes auto-grading of questions and mandatory questions reporting. (Note: Students may need to resubmit some responses)

= 1.2.4.7 =
* Recommended performance update. Significant improvements made (e.g. From 17s down to 0.56s using high volume test sample.)
* Progress tracking changed from course focused to student focused reducing database queries. Pages might load a fraction slower (up to 1s in testing) the first time old students accesses a course.
* Shortcode performance improvements
* Removing redundant CoursePress metadata from database
* Fixing unit layout issues resulting in HTML being displayed on the screen.

= 1.2.4.6 =
* Performance: When persistent object caching (server setup or 3rd party) is not available CoursePress will fall back to using transients to speed up page loads.
* Fixed: [course_join_button] now works properly on pages (bug caused it only to work on posts).
* Changed: [course_thumbnail] deprecated. Will revert to preferred [course_media type="thumbnail"] using the proper Course List image as thumbnail.
* Fixed: Required fields error for enrolment popup.
* Fixed: 'Start Learning Now' button in enrolment popup.
* Fixed: Added missing translations.

= 1.2.4.5 =
* Fixes issue with marking an order as paid with MarketPress
* Fixed text domain issues with the CoursePress theme
* Fixes issue with the LOGIN_ADDRESS email tag and its URL
* Fixed jQuery issues on the front-end caused by "live" function

= 1.2.4.4 =
* Resolved issue with unit element content saving / removed unit HTML editor

= 1.2.4.3 =
* Fixed issues with Unit HTML editor

= 1.2.4.2 =
* Updated MarketPress to 2.9.6
* Added Unit HTML editor back (for Mac)
* Fixed bug with unit editor (double editor on switch)

= 1.2.4.1 =
* Updated course structure (admin and front) to reflect recent changes in the units builder logic
* Fixed issue with Jatpack's CSS editor
* Removed Unit Builder HTML editors for Mac users (until we find better solution)
* Added additional filters for developers in shortcodes

= 1.2.4 =
* Added option for deleting student answers / responses
* Added option for instructor to access units and other course inner pages without need to enroll into course
* Fixed JS conflicts caused issues with WP admin menu on hover
* Fixed responsive issues on the course archive page with the default CoursePress theme

= 1.2.3.9 =
* Added HTML editor to the units builder
* Fixed issue with hidden students in the reports list (multisite)
* Fixed issue with wrong redirection link when submitting data on the last unit page (front)

= 1.2.3.8 =
* Added scroll (slimscroll) for the long lists of units on the course unit admin page
* Added integration with Messaging (1.1.6.7 and above) plugin (http://premium.wpmudev.org/project/messaging/)
* Fixed issues with BBPress topics when CoursePress is active
* Fixed issues caused by clearfix located in the plugin
* Fixed UX issues with "Resubmit" answer link

= 1.2.3.7 =
* Course Calendar widget updated. New default CSS to work better across themes. Added date indicator selector for better presentation on light and dark themes. Including selector to use custom CSS defined by theme or CSS plugin.
* Fixed issue where the unit editor converts absolute URLs to relative URLs on sites hosted with WPEngine.
* Fixed issue with incorrect unit completion percentages.
* Fixed PHP warnings when using CoursePress with TwentyFifteen theme.

= 1.2.3.6 =
* Fixed: Date translations now work properly.
* Fixed issue with extra content on the unit page singe page
* Fixed Gravity Forms form submission and redirection on the unit pages

= 1.2.3.5 =
* Fixed (potential) issue with student signup when FORCE_SSL_ADMIN is turned on
* Fixed conflicts with Gravity Forms (admin and unit pages)
* Fixed issue with multisite and granting and revoking instructor capabilities.
* Fixed: Comments section no longer showing on course details page.
* Fixed issue with 'Start Learning/Continue Learning' buttons not showing for courses set to manual enrollments.
* Fixed: Instructor Capabilities On User Profile Page Not Saving When Granting/Revoking Capabilities
* Fixed: coursepress_student_withdrawn hook is firing twice for a single withdrawal
* Fixed issue with unique course and units slugs

= 1.2.3.4 =
* Added additional instructor capability for managing Course Categories
* Added unit elements preloader
* Course completion actions added for developers: 'coursepress_student_course_completed', 'coursepress_student_course_unit_completed'
* Unit completion actions added for developers: 'coursepress_student_course_unit_pages_viewed', 'coursepress_student_course_unit_mandatory_question_answered', 'coursepress_student_course_unit_gradable_question_passed'
* New options for "Who can enroll" when not allowing anyone to register to your site.
* Fixed WordPress 4.1 issues (hidden course list in the admin, hidden assessment list)
* Fixed "administrator" role for network sites.
  CoursePress menus and permissions now work properly for new sites.
  For old sites the administrator's role will have to be reset (change to "subscriber" then back to "administrator").
* Fixed shortcode typos on the settings page
* Fixed issue with prerequisite courses for non-logged-in users
* Fixed issues with enrollment/signup button
* Fixes issue with unit editors upon reordering elements (Firefox)
* Strip html tags from the assessment comment ALT and TITLE
* Fixed issues with dummy course not being created upon first install
* Other code improvements

= 1.2.3.3 =
* Added course category filter on the courses admin page
* Fixed issue with thumbnails not displaying or getting generated for courses.
* Fixed issues with WordPress search when CoursePress plugin is active
* Fixed oEmbeds when pasting links to supported websites in Unit Elements.
* Fixed issue with student access to the enrolled courses

= 1.2.3.2 =
* Fixed translation file

= 1.2.3.1 =
* Updated MarketPress to 2.9.5.9
* Added additional set of instructor capabilities for Discussions
* CSS improvements (added better CSS styles on the feature course buttons in the CoursePress theme)
* Updated translation file
* Added support for WordPress "Week Starts On" day in the course date fields and the Unit Availability field
* Fixed issue with saving course categories
* Fixed issue with showing "No elements have been added to this page yet" on the last unit page
* Fixed issue where users saving their own profiles remove instructor capabilities
* Fixed issue with MarketPress sale price (not being saved)
* Fixed issue with primary blog on multisite
* Fixed issue with pagination class (not displaying more than 10 pages)
* Fixed issue with not showing draft units preview (for both admin and assigned instructors)
* Fixed issue with duplicate course and MarketPress products
* Other code improvements

= 1.2.2.9 =
* Added course reordering on courses admin page (drag & drop)
* Added new options under CoursePress general settings for controlling course order in admin and front
* Added option for displaying different number of rows on the courses admin page
* New hooks for developers and code improvements
* Fixed issues with loosing element content

= 1.2.2.8 =
* Critical Fix: Fixed bug preventing elements being added to units.

= 1.2.2.7 =
* Resolving translation issues on general settings page and email body (functions)
* Included new translation file containing all localization strings
* Added course calendar locale for month and day of the week names
* Fixed: Primary blog tweaks on multisite installs.
* Fixed: Instructor capabilities on multisite installs.
* Fixed: [course_list show_media="yes"] now correctly shows the media defined in settings.
* Updated MarketPress (2.9.5.8)
* Other small code improvements

= 1.2.2.6 =
* Fixed issue with wrong MD5 for instructor username in shortcodes which caused broken instructor single page if "Show Instructor Username in URL" option is not selected
* Fixed issue with table prefix (instructor_by_hash)
* Fixed issue with SKU not being shown on course overview page and product list in MarketPress
* Fix broken redirect to cart on signup
* Small code improvements

= 1.2.2.5 =
* Multisite improvements for students and instructors.
* Added course categories and course categories widget (in order to make it work please re-save CoursePress settings)
* Fixed: CoursePress theme navigation restored in responsive/mobile views.
* Improved some responsive elements of the CoursePress theme.
* Fixed issue with mobile menu not appearing on the some Android devices
* Small code improvements
* Updated MarketPress to 2.9.5.7

= 1.2.2.4 =
* Added integration with "Terms of Service" plugin http://premium.wpmudev.org/project/terms-of-service/
* Improved CoursePress for multi-site.
* Improved CoursePress security for multi-site.
* Improved UX for MarketPress in the admin (MarketPress activation and installation menu, links and messages shown to users who don't have required permissions)
* Future integration with Ultimate Facebook plugin to better promote courses on Facebook using OpenGraph data. (Currently works with CoursePress theme, but requires future Ultimate Facebook 2.7.8+ for all other themes.)
* Fixed: Instructors can now successfully create own courses (provided capability is set in CoursePress settings).

= 1.2.2.3=
* Changed the method of activation and installation of MarketPress
* Resolved issue with incorrect SKU being returned in checkout process.

= 1.2.2.2=
* Fixed issue with not showing HTML tags in excerpt
* Resolved issues with UTF-8 characters in filename in the TCPDF library
* Fixed up issue with translation files not working properly.
  - Updated languages files.
  - Updated cp-en_GB translation (Enrollment vs Enrolment).
  - Placing translations in /coursepress/languages now works correctly.
* Added additional hooks for developers in class.course.unit.php and class.course.unit.module.php.
* Fixed issue with some shortcodes displaying content out of place on a page.

= 1.2.2.1=
* Fixed issues caused e-newsletter plugin to show blank page in admin
* Fixed possible issues with MarketPress update
* Fixed issues with clearing cookie data in course checkout message
* Updated translation files

= 1.2.2.0=
* Added new option in settings for PDF report font & Added new fonts
* Updated MarketPress to 2.9.5.4
* More consistent filters and actions for developers (more to come).
* Improved database performance with new instructor 'Privacy' setting (may need to re-add instructors to old courses if you use the privacy option).

= 1.2.1.9=
* Added new settings (Privacy) for controlling visibility of instructor username in the URL
* Resolved issues with cp_get_file_size functions and fatal error if filesize cannot be retrieved

= 1.2.1.8 =
* Fixed issue course excerpt (not showing on course single and archive pages)
* Fixed issue with popup windows (responsive)

= 1.2.1.7 =
* Resolved issue with plugin update

= 1.2.1.6 =
* Fixed bug where visual editor prevented unit elements from saving.
* Fixed bug after duplicating course. Can now edit the course again.

= 1.2.1.5 =
* Fixed issue with instructor's profile avatar shortcode
* Fixed conflicts with bbPress (not showing topics when CoursePress is active)
* Resolved issue with course front-end edit links (caused by empty spaces)

= 1.2.1.4 =
* Fixed issue with incorrect registration of module post type
* Fixed issues with hard coded http:\\ resources (google fonts and images in the theme and plugin)
* Fixed issue with not saving Login Slug
* Added additional options in settings for pages (instead of virtual pages) for enrollment process, login page, signup page, student dashboard and student settings
* Visual editor improvements.
* Small code improvements

= 1.2.1.3 =
* Fixed issue with MarketPress product page infinite loop when CoursePress is active
* Fixed issue with instructor avatars preview

= 1.2.1.2 =
* Fixed issue with enrollment date and time (it uses now current_time( 'timestamp') instead of time())
* Fixed issue with media shortcode display in the CoursePress theme
* Fixed issue with course archive for courses without media set

= 1.2.1.1 =
* Added additional settings for controlling wp-login redirection
* Fixed issue with "Instructor Capabilities" settings access as a student
* Various database improvements.
* Added course progress display to student workbook.
* Added unit progress to CoursePress theme on student workbook.
* Added categories in the single post and blog archive
* Fixed issue with hidden comment form when plugin is activated
* Added passcode fields on login and signup popup forms
* Minor changes to enrollment popup window.

= 1.2.1 =
* Fixed issue with incorrectly displayed footer on student login page
* Fixed issue with BuddyPress autocomplete on Compose Message page
* Added a number of hooks in the main CoursePress class

= 1.2 =
* Added Duplicate Course feature
* Fixed issue with "units" slugs
* Fixed jQuery conflicts with theme options in WPMU Dev themes
* Added Unit restriction options to avoid confusion between 'completed answers' and 'successfull/passed answers'.
* Fixed unit restriction checking on front end 'Units' page. Will now show restrictions required from previous unit.

= 1.1.1 =
* Fixed issue with protection of the next unit when previous unit has set "User needs to complete current unit in order to access the next one"
* Fixed bug with removing a Single Choice element from a Unit

= 1.1.0 =
* Fixed issue with course limits in PRO version

= 1.0.9 =
* Resolved issue with details button on courses archive and inconsistent shortcode used

= 1.0.8 =
-------------------------------------------------
* Upgraded MarketPress Bundle to 2.9.5.3
* Added warning message (for admins) to the course overview page if "anyone can register" is not selected
* Fixed issue with instructor capabilities settings and saving
* Fixes possible issues with rewrite rules formating and avoid 404s
* Fixed issue with non-protected discussions for students who didn't enroll to the course
* Fixed issue with visibility of the draft units for admins and instructors* Fixed up issues with course completion checking
* Added file size indicator next to downloadable files
* Fixed issue with Single and Multiple choice values not recording result if answer contains quotation marks.

= 1.0.7 =
-------------------------------------------------
* Resolved issues with wrong pre_get_posts filtering within the admin

= 1.0.6 =
-------------------------------------------------
* Improved security
* Fixed: Auto-update issue with text editor in course setup
* Slightly larger content editor for more convenient editing
* Fixed: Course completion now calculates correctly
* Resolved issue with incorrect saving of Single Line / Multiple Lines option in input text element
* Added student username (and link to the student's profile) in the assessment column
* Dynamic MarketPress path set

= 1.0.5 =
-------------------------------------------------
* Resolved issues with displaced content when PopUp Pro plugin is active
* Resolved issue with (not honoring) WP Settings for registrations
* CoursePress Theme CSS fixes
* Settings changes and Improved security


= 1.0.4 =
-------------------------------------------------
* Shortcode changes and Improved security
* Fixed textdomain issues
* Resolved potential issue if Mcrypt library is not installed on server

= 1.0.3 =
-------------------------------------------------
* Improved security
* Resolved CSS issues with MarketPress popup called from CoursePress
* Fixed issue with theme location in the CoursePress theme
* Fixed CSS issue with uploaded videos in CoursePress theme (plus better responsive)
* Resolved issue with output buffer in shortcodes
* Added missing text domain on a number of places
* Other code improvements

= 1.0.2 =
-------------------------------------------------
* Resolved issue with mobile menu
* Resolved issue with listing images, videos and overlapping content in the CoursePress theme
* Responsive fixes for admin pages

= 1.0.1 =
-------------------------------------------------
* Resolved issue with deleting media files (selected in elements) upon deleting a unit or a module.

= 1.0.0 =
-------------------------------------------------
* 1.0 First Release.
