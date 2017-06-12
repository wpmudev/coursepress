# CoursePress 3.0

DEVELOPMENT GUIDE
-

## Folder Structure

### JS Files
All development source files are located at **coursepress/assets/js/src/**

* ***assets/js/src/common*** - Contains common reusable JS files generated unto `admin-general.js` and prepended unto `front.js`.
* ***assets/js/src/admin/courselist*** - Contains JS files used in the main courses (`coursepress.js`)
* ***assets/js/src/admin/course-edit*** - Contains JS files used in **New Course** or **Edit Course** (`coursepress_course.js`)
* ***assets/js/src/admin/assessments*** - Contains JS files used in **Assessments** page (`coursepress_assessments.js`)
* ***assets/js/src/admin/forum*** - Contains JS files used in **Forum** page (`coursepress_forum.js`)
* ***assets/js/src/admin/instructors*** - Contains JS files used in **Instructors** page (`coursepress_instructors.js`)
* ***assets/js/src/admin/students*** - Contains JS files used in **Students** page (`courseperess_students.js`)
* ***assets/js/src/admin/notifications*** - Contains JS files used in **Notifications** page (`coursepress_notifications.js`)
* ***assets/js/src/admin/settings*** - Contains JS files used in **Settings** page (`coursepress_settings.js`)
* ***assets/js/src/front*** - Contains JS files used in front pages.

### CSS Files
CSS are auto-generated using either **GULP** or **GRUNT** development tools.

### Development Tools
**Validates JS files and regenerates production JS**
```
#!bash
$ gulp js
$ grunt js
```
**Generates both un-minified and minified production CSS**
```
#!bash
$ gulp css
$ grunt css
```
**Generate new language pot**
```
#!bash
$ gulp makepot
$ grunt makepot
```
**Validates PHP files and run PHP Unit Test**
```
#!bash
$ gulp php
$ grunt php
```


### Templates Templates Templates
> Strictly NO **inline** or **concatenated** HTML blocks inside **PHP Classes** and **JS** files. If you are compelled to write short HTML structure, use **coursepress_create_html** function to do so.

> All templates, php or backbone templating must reside inside **views/** folder.

CUSTOMIZING COURSEPRESS
-

#### FRONT END TEMPLATES
CoursePress 3.0 is now totally customizable by users. It works similarly to how **WooCommerce** templating style.
Our virtual pages reside at `templates/` folder. User can copy and paste this folder into their theme or child theme and extend the templates however they choose.

> All templates that lives in this folder must be templates that users are allowed to customized. No built-in templates!

#### PHP Functions
Useful functions

#### PHP Classes

#### Actions and Filter Hooks
