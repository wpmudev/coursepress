/*global require*/

/**
 * When grunt command does not execute try these steps:
 *
 * - delete folder 'node_modules' and run command in console:
 *   $ npm install
 *
 * - Run test-command in console, to find syntax errors in script:
 *   $ grunt hello
 */

module.exports = function(grunt) {
	// Show elapsed time at the end.
	require( 'time-grunt' )(grunt);

	// Load all grunt tasks.
	require( 'load-grunt-tasks' )(grunt);

	var buildtime = new Date().toISOString();

	// -------------------------------------------------------------------------
	// Configuration.
	var conf = {
		// Folder that contains the JS files.
		js_folder: 'assets/js/',

		// Folder that contains the CSS files.
		css_folder: 'assets/css/',
		sass_folder: 'assets/sass/',

		js_files_concat: {
			'assets/js/admin-general.js': [
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
			'assets/js/coursepress.js': [
				// Add JS that will be loaded at courselist page
				// assets/js/src/admin/courselist/...
				'assets/js/src/admin/courselist/courselist.js'
			],
			'assets/js/coursepress_course.js': [
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
			'assets/js/coursepress_students.js': [
				// Add JS that will be loaded at Students page
				// assets/js/src/admin/students/...
				'assets/js/src/admin/students/studentlist.js'
			],
			'assets/js/coursepress_instructors.js': [
				// Add JS that will be loaded at Instructors page
				// assets/js/src/admin/instructors
				'assets/js/src/common/cp-search-form.js',
				'assets/js/src/admin/instructors/instructors.js'
			],
			'assets/js/coursepress_assessments.js': [
				// Add JS that will be loaded at Assessments page
				// assets/js/src/admin/assessments/...
				'assets/js/src/admin/assesments/assesments.js'
			],
			'assets/js/coursepress_forum.js': [
				// Add JS that will be loaded at Forum page
				// assets/js/src/admin/forum/...
				'assets/js/src/common/cp-search-form.js',
				'assets/js/src/admin/forum/edit.js'
			],
			'assets/js/coursepress_comments.js': [
				// Add JS that will be loaded at comments page
				// assets/js/src/admin/comments/...
				'assets/js/src/admin/comments/general.js'
			],
			'assets/js/coursepress_notifications.js': [
				// Add JS that will be loaded at Notifications page
				// assets/js/src/admin/notifications/...
				'assets/js/src/admin/notifications/notification-emails.js',
				'assets/js/src/admin/notifications/notification-alerts.js',
				'assets/js/src/admin/notifications/notification-alerts-form.js',
				'assets/js/src/admin/notifications/notification.js'
			],
			'assets/js/coursepress_reports.js': [
				// Add JS that will be loaded at reports page
				// assets/js/src/admin/reports/...
				'assets/js/src/common/cp-search-form.js',
				'assets/js/src/admin/reports/reports.js'
			],
			'assets/js/coursepress_settings.js': [
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
			'assets/js/coursepress-front.js': [
                'assets/js/src/common/coursepress.js',
                'assets/js/src/common/request.js',
                'assets/js/src/common/view.js',
                'assets/js/src/front/course-overview.js',
                'assets/js/src/front/comment-reply.js',
                'assets/js/src/front/steps.js',
				'assets/js/src/front/email-unsubscribe.js',
			]
		},

		// List of JS files to validate
		js_src_files: [
			'Gruntfile.js',
			'assets/js/src/**/*.js',
			'!assets/js/src/common/heading.js',
			'!assets/js/src/common/footer.js'
		],

		// SASS files to process. Resulting CSS files will be minified as well.
		css_files_compile: {
			'assets/css/admin-global.css': 'assets/sass/admin-global.scss',
			'assets/css/front.css': 'assets/sass/front.scss',
			'assets/css/admin-common.css': 'assets/sass/admin-common.scss',
			'assets/css/coursepress.css': 'assets/sass/coursepress.scss',
			'assets/css/coursepress_course.css': 'assets/sass/coursepress_course.scss',
			'assets/css/coursepress_students.css': 'assets/sass/coursepress_students.scss',
			'assets/css/coursepress_instructors.css': 'assets/sass/coursepress_instructors.scss',
			'assets/css/coursepress_assessments.css': 'assets/sass/coursepress_assessments.scss',
			'assets/css/coursepress_forum.css': 'assets/sass/coursepress_forum.scss',
			'assets/css/coursepress_notifications.css': 'assets/sass/coursepress_notifications.scss',
			'assets/css/coursepress_settings.css': 'assets/sass/coursepress_settings.scss',
			'assets/css/coursepress_comments.css': 'assets/sass/coursepress_comments.scss',
			'assets/css/coursepress_reports.css': 'assets/sass/coursepress_reports.scss'
		},

		// PHP files to validate.
		php_files: [
			'coursepress.php',
			//'inc/*.php',
			//'inc/**/*.php',
			//'views/*.php',
			//'views/**/*.php',
			//'tests/php/*.php',
			//'tests/php/**/*.php'
		],

		// Regex patterns to exclude from transation.
		translation: {
			ignore_files: [
				'(^.php)',	  // Ignore non-php files.
				'tests/', // Upgrade tests
				'node_modules/',
				'docs/'
			],
			pot_dir: '/languages/',  // With trailing slash.
			textdomain: 'cp'   // Campus uses same textdomain.
		},

		// Build branches.
		plugin_branches: {
			exclude_pro: [
				'../release/docs',
				'../release/readme.MD',
				'../release/README.md',
				'../release/Gulpfile.js',
				'../release/readme.txt'
			],
			exclude_free: [
				'../release/docs',
				'../release/test',
				'../release/campus',
				'../release/README.md',
				'../release/Gulpfile.js',
				'../release/changelog.txt',
				'../release/premium/'
			],
			base: 'coursepress/2.0-release',
			pro: 'coursepress/2.0-release-pro',
			free: 'coursepress/2.0-release-free',
			campus: 'coursepress/2.0-release-campus',
			dev: 'coursepress/2.0-release-dev'
		},

		// BUILD patterns to exclude code for specific builds.
		plugin_patterns: {
			pro: [
				{ match: /CoursePress Base/g, replace: 'CoursePress Pro' },
				{ match: /PLUGIN_VERSION/g, replace: '<%= pkg.version %>' },
				{ match: /BUILDTIME/g, replace: buildtime }
			],
			free: [
				{ match: /CoursePress Base|CoursePress Pro/g, replace: 'CoursePress' },
				{ match: /PLUGIN_VERSION/g, replace: '<%= pkg.version %>' },
				{ match: /BUILDTIME/g, replace: buildtime }
			],
			campus: [
				{ match: /CoursePress Base/g, replace: 'CoursePress Campus' },
				{ match: /PLUGIN_VERSION/g, replace: '<%= pkg.version %>' },
				{ match: /BUILDTIME/g, replace: buildtime }
			],
			// Files to apply above patterns to (not only php files).
			files: {
				expand: true,
				src: [
					'../release/*.php',
					'../release/inc/*.php',
					'../release/inc/**/*.php',
					'../release/views/*.php',
					'../release/views/**/*.php',
					'../release/assets/css/*.css',
					'../release/assets/js/*.js',
					'!../release/themes/*'
				]
			}
		},

		// Different plugin settings.
		plugin_file: 'coursepress.php',
		plugin_dir: 'coursepress'
	};

	// Define grunt tasks.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		conf: conf,
		// JS: Validate JS files (1).
		jsvalidate: {
			all: conf.js_src_files
		},

		// JS: Validate JS files (2).
		jshint: {
			all: conf.js_src_files,
			options: {
				curly:   true,
				browser: true,
				eqeqeq:  true,
				immed:   true,
				latedef: true,
				newcap:  true,
				noarg:   true,
				sub:     true,
				undef:   true,
				boss:    true,
				eqnull:  true,
				unused:  true,
				quotmark: 'single',
				predef: [
					'jQuery',
					'Backbone',
					'_'
				],
				globals: {
					exports: true,
					module:  false
				}
			}
		},

		// JS: Concatenate JS files into single file.
		concat: {
			options: {
				stripBanners: true,
				banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
					' * <%= pkg.homepage %>\n' +
					' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
					' * Licensed GPLv2+' +
					' */\n'
			},
			scripts: {
				files: conf.js_files_concat
			},
		},

		// JS: Compile/minify js files.
		uglify: {
			all: {
				files: [{
					expand: true,
					src: ['*.js', '!*.min.js'],
					cwd: conf.js_folder,
					dest: conf.js_folder,
					ext: '.min.js',
					extDot: 'last'
				}],
				options: {
					banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
						' * <%= pkg.homepage %>\n' +
						' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
						' * Licensed GPLv2+' +
						' */\n',
					mangle: {
						except: ['jQuery']
					}
				}
			}
		},

		// CSS: Compile .scss into .css files.
		sass:   {
			all: {
				options: {
					'sourcemap=none': true, // 'sourcemap': 'none' does not work...
					unixNewlines: true,
					style: 'expanded'
				},
				files: conf.css_files_compile
			}
		},

		// CSS: Add browser-specific CSS prefixes to css files.
		autoprefixer: {
			options: {
				browsers: ['last 2 version', 'ie 8', 'ie 9'],
				diff: false
			},
			single_file: {
				files: [{
					expand: true,
					src: ['*.css', '!*.min.css'],
					cwd: conf.css_folder,
					dest: conf.css_folder,
					ext: '.css',
					extDot: 'last'
				}]
			}
		},

		// CSS: Minify css files (create a .min.css file).
		cssmin: {
			options: {
				banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
					' * <%= pkg.homepage %>\n' +
					' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
					' * Licensed GPLv2+' +
					' */\n'
			},
			minify: {
				expand: true,
				src: ['*.css', '!*.min.css'],
				cwd: conf.css_folder,
				dest: conf.css_folder,
				ext: '.min.css',
				extDot: 'last'
			}
		},

		// CSS/JS: Watch for file changes.
		watch:  {
			css: {
				files: [
					conf.sass_folder + '**/*.scss',
					conf.sass_folder + '**/**/*.scss'
				],
				tasks: ['css'],
				options: {
					debounceDelay: 100
				}
			},
			js: {
				files: conf.js_src_files,
				tasks: ['js'],
				options: {
					debounceDelay: 100
				}
			}
		},

		// POT: Create the .pot translation index.
		makepot: {
			target: {
				options: {
					cwd: '',
					domainPath: conf.translation.pot_dir,
					exclude: conf.translation.ignore_files,
					mainFile: conf.plugin_file,
					potFilename: conf.translation.textdomain + '.pot',
					potHeaders: {
						'poedit': true, // Includes common Poedit headers.
						'language-team': 'WPMU Dev <support@wpmudev.org>',
						'report-msgid-bugs-to': 'http://wordpress.org/support/plugin/coursepress',
						'last-translator': 'WPMU Dev <support@wpmudev.org>',
						'x-generator': 'grunt-wp-i18n',
						'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
					},
					type: 'wp-plugin', // wp-plugin or wp-theme
					include: [
						'coursepress.php',
						'upgrade/*.',
						'2.0/coursepress.php',
						'2.0/admin/*.php',
						'2.0/admin/.*',
						'2.0/include/coursepress/.*'
					]
				}
			}
		},
		wpmu_pot2mo: {
			files: {
				src: 'languages/*.pot',
				expand: true
			}
		},

		// COMPRESS: Create a zip-archive of the plugin (for distribution).
		compress: {
			release_pro: {
				options: {
					mode: 'zip',
					archive: '../release/<%= pkg.name %>-pro-<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: '../release',
				dest: conf.plugin_dir,
				src: [
					'*',
					'**',
					'!*.zip',
					'!**.zip'
				]
			},
			release_free: {
				options: {
					mode: 'zip',
					archive: '../release/<%= pkg.name %>-free-<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: '../release',
				dest: conf.plugin_dir,
				src: [
					'*',
					'**',
					'!*.zip',
					'!**.zip'
				]
			}
		},

		// PHP: Validate file syntax.
		phplint: {
			src: conf.php_files,
			options: {
				swapPath: '/tmp'  // Make sure this folder exists; its for caching to speed up the task.
			}
		},

		// PHP: Code Sniffer to validate WP Coding Standards.
		phpcs: {
			sniff: {
				src: conf.php_files,
				options: {
					bin: '../../../../../phpcs/scripts/phpcs',
					standard: 'WordPress-Core',
					verbose: true
				}
			}
		},

		phpcbf: {
			options: {
				noPatch: true,
				bin: '../../../../../phpcs/scripts/phpcs',
				standard: 'WordPress-Core'
			},
			main: conf.php_files
		},

		// PHP: Unit tests.
		phpunit: {
			classes: {
				dir: ''
			},
			options: {
				bootstrap: 'test/bootstrap.php',
				testsuite: 'default',
				configuration: 'test/phpunit.xml',
				colors: true,
				staticBackup: false,
				noGlobalsBackup: false
			}
		},

		// BUILD: Replace conditional tags in code
		replace: {
			pro: {
				options: {
					patterns: conf.plugin_patterns.pro
				},
				files: [conf.plugin_patterns.files]
			},
			free: {
				options: {
					patterns: conf.plugin_patterns.free
				},
				files: [conf.plugin_patterns.files]
			},
			campus: {
				options: {
					patterns: conf.plugin_patterns.campus
				},
				files: [conf.plugin_patterns.files]
			}
		},

		// BUILD: Remove files that are not relevant for target product.
		clean: {
			options: { force: true },
			release: {
				options: { force: true },
				src: ['../release', '../release/*', '../release/**']
			},
			release_pro: {
				src: [
					'release/<%= pkg.version %>-pro/',
					'release/<%= pkg.version %>-pro-<%= pkg.version %>.zip'
				]
			},
			pro: conf.plugin_branches.exclude_pro,
			free: conf.plugin_branches.exclude_free,
			campus: conf.plugin_branches.exclude_campus,
			upgrade: conf.plugin_branches.exclude_upgrade
		},

		// BUILD: Copy files.
		copy: {
			release: {
				expand: true,
				src: [
					'*',
					'**',
					'!node_modules',
					'!node_modules/*',
					'!node_modules/**',
					'!bitbucket-pipelines.yml',
					'!.idea', // PHPStorm settings
					'!.git',
					'!Gruntfile.js',
					'!package.json',
					'!tests/*',
					'!tests/**',
					'!assets/js/src',
					'!assets/js/src/*',
					'!assets/js/src/**',
					'!assets/sass',
					'!assets/sass/*',
					'!assets/sass/**',
					'themes/coursepress/.git'
				],
				dest: '../release',
				noEmpty: true
			},
			release_free: {
				expand: true,
				src: [
					'*',
					'**',
					'!node_modules',
					'!node_modules/*',
					'!node_modules/**',
					'!bitbucket-pipelines.yml',
					'!.git',
					'!Gruntfile.js',
					'!package.json',
					'!tests/*',
					'!tests/**',
					'!assets/js/src',
					'!assets/js/src/*',
					'!assets/js/src/**',
					'!assets/sass',
					'!assets/sass/*',
					'!assets/sass/**',
					'themes/coursepress/.git'
				],
				dest: '../release',
				noEmpty: true
			},
			translation: {
				src: conf.translation.pot_dir + conf.translation.textdomain + '.pot',
				dest: conf.translation.pot_dir + conf.translation.textdomain + '.pot',
				nonull: true
			}
		}
	} );

	// Translate plugin.
	grunt.registerTask( 'lang', 'Create all translation files', function() {
		// Generate the text-domain for Pro/Campus.
		grunt.task.run( 'makepot' );

		// Simply copy the pro-translations to the Free plugin .pot file.
		grunt.task.run( 'copy:translation' );
	});

	// Test task.
	grunt.registerTask( 'hello', 'Test if grunt is working', function() {
		grunt.log.subhead( 'Hi there :)' );
		grunt.log.writeln( 'Looks like grunt is installed!' );
	});

	grunt.task.run( 'clear' );

	// Define default tasks.
	grunt.registerTask( 'js', ['jsvalidate', 'jshint', 'concat', 'uglify'] );
	grunt.registerTask( 'css', ['sass', 'autoprefixer', 'cssmin'] );
	grunt.registerTask( 'assets', ['js', 'css'] );

	grunt.registerTask( 'test', ['phpunit'] );
	grunt.registerTask( 'php', ['phplint', 'phpcs:sniff'] );

	grunt.registerTask( 'default', ['php', 'test', 'js', 'css'] );

	// Adapted from https://github.com/MicheleBertoli/grunt-po2mo
	grunt.registerMultiTask('wpmu_pot2mo', 'Compile .pot files into binary .mo files with msgfmt.', function() {
		this.files.forEach(function(file) {

		  var dest = file.dest;
		  if (dest.indexOf('.pot') > -1) {
				dest = dest.replace('.pot', '.mo');
		  }
		  grunt.file.write(dest);

		  var exec = require('child_process').exec;
		  var command = 'msgfmt -o ' + dest + ' ' + file.src[0];

		  grunt.verbose.writeln('Executing: ' + command);
		  exec(command);

		});
	});

	grunt.registerTask( 'release', 'Generating release copy', function( target ) {
		if ( ! target ) {
			return;
		}

		grunt.task.run( 'clean:release');
		grunt.task.run( 'copy:release' );

		if ( 'pro' === target ) {
			grunt.task.run( 'replace:pro' );
			grunt.task.run( 'clean:pro' );
			grunt.task.run( 'compress:release_pro' );
		} else if ( 'free' === target ) {
			grunt.task.run( 'replace:free' );
			grunt.task.run( 'clean:free' );
			grunt.task.run( 'compress:release_free' );
		}
	});
};
