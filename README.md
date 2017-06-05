# CoursePress 3.0

# DEVELOPMENT GUIDE

## Folder Structure

### JS Files
All development source files are located at **coursepress/assets/js/src/**


### CSS Files
CSS are auto-generated using either **GULP** or **GRUNT** development tools.


### HTML Templates
> Avoid writing **inline** or **concatenated** HTML structure inside **PHP Classes** and/or **JS** files. If you are compelled to write short HTML structure, use **coursepress_create_html** function to do so.

> All templates, php or backbone templating must reside inside **views/** folder.
