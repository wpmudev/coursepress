/* global require */
(function() {
    'use strict';

    var gulp = require('gulp'),
        sourcemaps = require('gulp-sourcemaps'),
        header = require('gulp-header'),
        date = new Date(),
        datestring = date.toDateString(),
        fs = require('fs'),
        pkg = JSON.parse(fs.readFileSync('./package.json')),
        banner_args = {
            title: pkg.title,
            version: pkg.version,
            datestring: datestring,
            homepage: pkg.homepage
        },
        banner = '/*! <%= title %> - v<%= version %>\n' +
            ' * <%= homepage %>\n' +
            ' * Copyright (c) <%=datestring%>;' +
            ' * Licensed GPLv2+' +
            ' */\n',
        php_files = [
            '*.php',
            'inc/*.php',
            'inc/**/*.php',
            'views/*.php',
            'views/**/*.php',
            '!node_modules/**'
        ],
        js_files = [
            'assets/js/src/*.js',
            'assets/js/src/**/*.js',
            'assets/js/src/**/**/*.js',
            'Gulpfile.js',
            'Gruntfile.js'
        ],
        js_files_concat = {
            'admin-general.js': [
                // Add JS that will be loaded to all CP pages
                'assets/js/src/common/coursepress.js',
                'assets/js/src/common/request.js',
                'assets/js/src/common/view.js',
                'assets/js/src/common/add-image.js',
                'assets/js/src/common/add-video.js',
                'assets/js/src/common/dropdown-menu.js',
                'assets/js/src/common/popup.js',
                'assets/js/src/common/upload.js',
                'assets/js/src/common/add-media.js',
                'assets/js/src/cp-search-form.js'
                // assets/js/src/admin/general/....
            ],
            'coursepress.js': [
                // Add JS that will be loaded at courselist page
                // assets/js/src/admin/courselist/...
                'assets/js/src/admin/courselist/courselist.js'
            ],
            'coursepress_course.js': [
                // ADd JS that will be loaded at course edit page
                // assets/js/src/admin/course-edit/...
                'assets/js/src/admin/course-edit/course-model.js',
                'assets/js/src/admin/course-edit/sample-course.js',
                'assets/js/src/admin/course-edit/course-type.js',
                'assets/js/src/admin/course-edit/course-settings.js',
                'assets/js/src/admin/course-edit/course-settings-modal.js',
                'assets/js/src/admin/course-edit/course-completion.js',
                'assets/js/src/admin/course-edit/step-text.js',
                'assets/js/src/admin/course-edit/step-image.js',
                'assets/js/src/admin/course-edit/step-video.js',
                'assets/js/src/admin/course-edit/step-audio.js',
                'assets/js/src/admin/course-edit/step-file-upload.js',
                'assets/js/src/admin/course-edit/step-quiz.js',
                'assets/js/src/admin/course-edit/step-zip.js',
                'assets/js/src/admin/course-edit/step-written.js',
                'assets/js/src/admin/course-edit/step-discussion.js',
                'assets/js/src/admin/course-edit/step-download.js',
                'assets/js/src/admin/course-edit/unit-steps.js',
                'assets/js/src/admin/course-edit/step.js',
                'assets/js/src/admin/course-edit/unit-help.js',
                'assets/js/src/admin/course-edit/unit-modules.js',
                'assets/js/src/admin/course-edit/unit-details.js',
                'assets/js/src/admin/course-edit/unit-list.js',
                'assets/js/src/admin/course-edit/unit-collection.js',
                'assets/js/src/admin/course-edit/units-with-module-list.js',
                'assets/js/src/admin/course-edit/course-units.js',
                'assets/js/src/admin/course-edit/course-students.js',
                'assets/js/src/admin/course-edit/course-setup.js'
            ],
            'coursepress_students.js': [
                // Add JS that will be loaded at Students page
                // assets/js/src/admin/students/...
                'assets/js/src/admin/students/studentlist.js'
            ],
            'coursepress_instructors.js': [
                // Add JS that will be loaded at Instructors page
                // assets/js/src/admin/instructors
                'assets/js/src/common/cp-search-form.js',
                'assets/js/src/admin/instructors/instructors.js'
            ],
            'coursepress_assessments.js': [
                // Add JS that will be loaded at Assessments page
                // assets/js/src/admin/assessments/...
                'assets/js/src/admin/assessments/assessments.js'
            ],
            'coursepress_comments.js': [
                // Add JS that will be loaded at Comments page
                // assets/js/src/admin/comments/...
                'assets/js/src/admin/comments/general.js'
            ],
            'coursepress_forum.js': [
                // Add JS that will be loaded at Forum page
                // assets/js/src/admin/forum/...
                'assets/js/src/common/cp-search-form.js',
                'assets/js/src/admin/forum/edit.js',
                'assets/js/src/admin/forum/forums.js'
            ],
            'coursepress_notifications.js': [
                // Add JS that will be loaded at Notifications page
                // assets/js/src/admin/notifications/...
                'assets/js/src/admin/notifications/notification-emails.js',
                'assets/js/src/admin/notifications/notification-alerts.js',
                'assets/js/src/admin/notifications/notification-alerts-form.js',
                'assets/js/src/admin/notifications/notification.js'
            ],
            'coursepress_reports.js': [
                // Add JS that will be loaded at reports page
                // assets/js/src/admin/reports/...
                'assets/js/src/common/cp-search-form.js',
                'assets/js/src/admin/reports/reports.js'
            ],
            'coursepress_settings.js': [
                // Add JS that will be loaded at Settings page
                // assets/js/src/admin/settings/....
                'assets/js/src/admin/settings/general.js',
                'assets/js/src/admin/settings/slugs.js',
                'assets/js/src/admin/settings/emails.js',
                'assets/js/src/admin/settings/capabilities.js',
                'assets/js/src/admin/settings/certificate.js',
                'assets/js/src/admin/settings/shortcodes.js',
                'assets/js/src/admin/settings/extensions.js',
                'assets/js/src/admin/settings/import-export.js',
                'assets/js/src/admin/settings/settings.js'
            ],
            'coursepress-front.js': [
                'assets/js/src/common/coursepress.js',
                'assets/js/src/common/request.js',
                'assets/js/src/common/view.js',
                'assets/js/src/front/course-overview.js',
                'assets/js/src/front/comment-reply.js',
                'assets/js/src/front/steps.js',
                'assets/js/src/front/fold-unit.js'
            ]
        };


    gulp.task('phpunit', function () {
        var phpunit = require('gulp-phpunit'),
            path = '.\\..\\..\\..\\..\\vendor\\bin\\phpunit',
            options = {
                //bootstrap: './tests/bootstrap.php',
                testSuite: 'default',
                configurationFile: './tests/phpunit.xml',
                colors: true,
                staticBackup: false,
                noGlobalsBackup: false
            };

        gulp.src('./tests/phpunit.xml').pipe(phpunit( path, options ));
    });

    gulp.task('phplint', function () {
        var phplint = require('gulp-phplint');

        gulp.src(php_files).pipe(phplint());
    });

    gulp.task('phpcs', function () {
        var phpcs = require('gulp-phpcs'),
            options = {
                // Change this to your actual phpcs location
                bin: '../../../../../phpcs/scripts/phpcs',
                standard: 'WordPress-Core',
                showSniffCode: true
            };

        gulp.src(php_files).pipe(phpcs(options)).pipe(phpcs.reporter('log'));
    });

    gulp.task( 'php', ['phplint', 'phpcs']);

    gulp.task('jsvalidate', function () {
        var jsvalidate = require('gulp-jsvalidate');

        gulp.src(js_files).pipe(jsvalidate());
    });

    gulp.task('jshint', function () {
        var jshint = require('gulp-jshint'),
            options = {
                curly: true,
                browser: true,
                eqeqeq: true,
                immed: true,
                latedef: true,
                newcap: true,
                noarg: true,
                sub: true,
                undef: true,
                boss: true,
                eqnull: true,
                unused: true,
                quotmark: 'single',
                predef: ['jQuery', 'Backbone', '_'],
                globals: {
                    exports: true,
                    module: false
                }
            };

        gulp.src(js_files).pipe(jshint(options)).pipe(jshint.reporter('default'));
    });

    gulp.task('js', ['jsvalidate', 'jshint'], function () {
        // Concat common js
        var concat = require('gulp-concat'),
            //replace = require('gulp-replace'),
            rename = require('gulp-rename'),
            uglify = require('gulp-uglify');

        function minified() {
            gulp.src(['assets/js/*.js', '!assets/js/*.min.js'])
                .pipe(sourcemaps.init())
                .pipe(uglify({preserveComments: 'license'}))
                .pipe(rename(function (path) {
                    path.basename = path.basename + '.min';
                }))
                .pipe(sourcemaps.write('maps'))
                .pipe(gulp.dest('assets/js/'));
        }

        for( var file in js_files_concat ) {
            if ( js_files_concat[file] ) {
                var src = js_files_concat[file];

                if (src.length) {
                    gulp.src(src)
                        .pipe(concat(file))
                        .pipe(header(banner, banner_args))
                        .pipe(gulp.dest('assets/js/'))
                        .on('finish', minified);
                }
            }
        }
    });

    gulp.task('css', function () {
        var autoprefixer = require('gulp-autoprefixer'),
            sass = require('gulp-sass'),
            rename = require('gulp-rename'),
            css = ['*.scss'];

        gulp.src(css, {cwd: './assets/sass'})
            .pipe(sass({outputStyle: 'expanded'}))
            .pipe(autoprefixer('last 2 version', '> 1%', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'))
            .pipe(gulp.dest('assets/css/'));

        gulp.src(css, {cwd: './assets/sass'})
            .pipe(sourcemaps.init())
            .pipe(sass({outputStyle: 'compressed'}))
            .pipe(rename(function (path) {
                path.basename = path.basename + '.min';
            }))
            .pipe(header(banner, banner_args))
            .pipe(autoprefixer('last 2 version', '> 1%', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'))
            .pipe(sourcemaps.write('maps'))
            .pipe(gulp.dest('assets/css/'));
    });

    gulp.task('watch', ['css', 'js'], function () {
        gulp.watch(['assets/js/src/*.js', 'assets/js/src/**/*.js', 'assets/js/src/**/**/*.js'], ['js']);
        gulp.watch(['assets/sass/*.scss', 'assets/sass/**/*.scss', 'assets/sass/**/**/*.scss'], ['css']);
        gulp.watch(['assets/js/src/*.js', 'assets/sass/*.scss'], ['clearcache']);
    });
    gulp.task( 'watch-js', ['js'], function() {
        gulp.watch(['assets/js/src/*.js', 'assets/js/src/**/*.js', 'assets/js/src/**/**/*.js'], ['js']);
        gulp.watch(['assets/js/src/*.js', 'assets/sass/*.scss'], ['clearcache']);
    });

    gulp.task( 'clearcache', function() {
        var cache = require( 'gulp-cache' );

        gulp.src( ['assets/js/*.js', 'assets/sass/*.scss'] )
            .pipe(cache.clear());
    });

    gulp.task('makepot', function () {
        var wpPot = require('gulp-wp-pot');

        gulp.src(php_files)
            .pipe(wpPot({'package': pkg.title + ' ' + pkg.version}))
            .pipe(gulp.dest('languages/' + pkg.name + '-en_US.pot'));
    });

    gulp.task('generate-zip', function () {
        var zip = require('gulp-zip'),
            notify = require('gulp-notify'),
            zipfile = pkg.name + '-' + pkg.version + '.zip',
            files = [
                '*',
                '**',
                '!docs/*',
                '!docs/**',
                '!docs',
                '!node_modules/*',
                '!node_modules/**',
                '!node_modules',
                '!.git/*',
                '!.git/**',
                '!.git',
                '!.gitignore',
                '!Gulpfile.js',
                '!Gruntfile.js',
                '!package.json',
                '!assets/sass/*',
                '!assets/sass/**',
                '!assets/sass',
                '!assets/js/common/*',
                '!assets/js/common',
                '!assets/**/maps/*',
                '!assets/**/maps',
                '!temp/*',
                '!temp/**',
                '!temp',
                '!tests/*',
                '!tests/**',
                '!tests',
                '!README.md'
            ];

        gulp.src(files, {base: '../'})
            .pipe(zip(zipfile))
            .pipe(gulp.dest('../'))
            .pipe(notify({message: zipfile + ' file successfully generated!', onLast: true}));
    });
})();
