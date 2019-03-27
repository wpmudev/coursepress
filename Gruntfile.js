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
		// 1.x version
		version_1: '2.0.3',
		// Folder that contains the CSS files.
		js_folder: 'upgrade/js/',

		// Folder that contains the CSS files.
		css_folder: 'upgrade/css/',

		js_files_concat: {
			'upgrade/js/admin-upgrade.js':            ['upgrade/js/src/admin-upgrade.js'],
		},

		// SASS files to process. Resulting CSS files will be minified as well.
		css_files_compile: {
			'upgrade/css/upgrade.css':    'upgrade/css/src/upgrade.scss'
		},

		// PHP files to validate.
		php_files: [
			'coursepress.php',
			'upgrade/*.php'
		],

		// Regex patterns to exclude from transation.
		translation: {
			ignore_files: [
				'2.0/campus/*',
				'2.0/include/tcpdf/.*', // External module.
				'2.0/node_modules/.*',
				'2.0/premium/',
				'2.0/.sass-cache/',
				'2.0/tcpdf/.*',
				'2.0/test/',      // Unit testing.
				'2.0/themes/.*',
				'2.0/themes/.*',    // External module.,
				'.git*',
				'.idea/',
				'node_modules/.*',
				'node_modules/',
				'(^.php)',		 // Ignore non-php files.
				'release/.*',	  // Temp release files.
				'.sass-cache/.*',
				'.sass-cache/',
				'tests/.*',		// Unit testing.
				'tests/' // Upgrade tests
			],
			pot_dir: 'languages/', // With trailing slash.
			textdomain: 'coursepress'
		},

		// Build branches.
		plugin_branches: {
			exclude_1_pro: [
				'../release/1.x/readme.txt'
			],
			exclude_2_pro: [
				'../release/2.0/readme.txt'
			],
			exclude_1_free: [
				'../release/1.x/readme.txt',
				'../release/1.x/changelog.txt',
				'../release/1.x/excludes/external/dashboard/'
			],
			exclude_2_free: [
				'../release/2.0/license.txt',
				'../release/2.0/campus',
				'../release/2.0/changelog.txt',
				'../release/2.0/composer.json',
				'../release/2.0/package-lock.json',
				'../release/2.0/premium/',
				'../release/2.0/test',
				'../release/changelog.txt',
				'../release/languages/*mo',
				'../release/languages/*po',
			],
			base: 'coursepress/2.0-release',
			pro: 'coursepress/2.0-release-pro',
			free: 'coursepress/2.0-release-free',
			campus: 'coursepress/2.0-release-campus',
			dev: 'coursepress/2.0-release-dev'
		},

		// BUILD patterns to exclude code for specific builds.
		plugin_patterns: {
			pro_1: [
				{ match: /CoursePress Base/g, replace: 'CoursePress Pro' },
				{ match: /<%= wpmudev.plugin.version %>/g, replace: '<%= conf.version_1%>' },
				{ match: /coursepress_base_td/g, replace: 'cp' },
				{ match: /\/\/<wpmudev.plugin.free_only([^<]+)/mg, replace: '' },
				{ match: /<\/wpmudev.plugin.free_only>/g, replace: '' },
				{ match: /\/\/<wpmudev.plugin.pro_only>/g, replace: '' },
				{ match: /\/\/<\/wpmudev.plugin.pro_only>/g, replace: '' }
			],
			pro_2: [
				{ match: /CoursePress Base/g, replace: 'CoursePress Pro' },
				{ match: /BUILDTIME/g, replace: buildtime },
				{ match: /PLUGIN_VERSION/g, replace: '<%= pkg.version %>' },
				{ match: /\/\* start:pro \*\//g, replace: '' },
				{ match: /\/\* end:pro \*\//g, replace: '' },
				{ match: /\/\* start:free \*[^\*]+\* end:free \*\//mg, replace: '' },
				{ match: /\/\* start:campus \*[^\*]+\* end:campus \*\//mg, replace: '' }
			],
			free_1: [
				{ match: /CoursePress Base|CoursePress Pro/g, replace: 'CoursePress' },
				{ match: /<%= wpmudev.plugin.version %>/g, replace: '<%= conf.version_1%>' },
				{ match: /coursepress_base_td/g, replace: 'coursepress' },
				{ match: /\/\/<wpmudev.plugin.pro_only([^<]+)/mg, replace: '' },
				{ match: /<\/wpmudev.plugin.pro_only>/g, replace: '' },
				{ match: /\/\/<wpmudev.plugin.free_only>/g, replace: '' },
				{ match: /\/\/<\/wpmudev.plugin.free_only>/g, replace: '' },
				{ match: /<%= wpmudev.plugin.changelog %>/g, replace: (function() {
					var changelog = grunt.file.read('./changelog.txt');
					changelog = changelog.replace(/^(\S|\s)*==.changelog.==\S*/igm, '' ).trim();
					return changelog;
				})() }
			],
			free_2: [
				{ match: /CoursePress Base|CoursePress Pro/g, replace: 'CoursePress' },
				{ match: /BUILDTIME/g, replace: buildtime },
				{ match: /PLUGIN_VERSION/g, replace: '<%= pkg.version %>' },
				{ match: /\/\* start:free \*\//g, replace: '' },
				{ match: /\/\* end:free \*\//g, replace: '' },
				{ match: /\/\* start:pro \*[^\*]+\* end:pro \*\//mg, replace: '' },
				{ match: /\/\* start:campus \*[^\*]+\* end:campus \*\//mg, replace: '' }
			],
			campus: [
				{ match: /CoursePress Base/g, replace: 'CoursePress Campus' },
				{ match: /BUILDTIME/g, replace: buildtime },
				{ match: /PLUGIN_VERSION/g, replace: '<%= pkg.version %>' },
				{ match: /\/\* start:campus \*\//g, replace: '' },
				{ match: /\/\* end:campus \*\//g, replace: '' },
				{ match: /\/\* start:pro \*[^\*]+\* end:pro \*\//mg, replace: '' },
				{ match: /\/\* start:free \*[^\*]+\* end:free \*\//mg, replace: '' }
			],
			// Files to apply in 1.x version
			files_1: {
				expand: true,
				src: [
					'../release/1.x/*.php',
					'../release/1.x/includes/*.php',
					'../release/1.x/includes/**/*.php',
					'../release/1.x/**/*.css',
					'../release/1.x/**/*.js',
					'../release/1.x/**/*.html',
					'../release/1.x/**/*.txt',
					'!../release/1.x/external/*',
					'!../release/1.x/themes/*'
				]
			},
			// Files to apply above patterns to (not only php files).
			files_2: {
				expand: true,
				src: [
					'../release/*.php',
					'../release/upgrade/*.php',
					'../release/upgrade/css/*.css',
					'../release/upgrade/js/*.js',
					'../release/2.0/*.php',
					'../release/2.0/admin/*.php',
					'../release/2.0/admin/**/*.php',
					'../release/2.0/include/coursepress/*.php',
					'../release/2.0/include/coursepress/**/*.php',
					'../release/2.0/include/coursepress/**/**/*.php',
					'../release/2.0/**/asset/js/*.js',
					'../release/2.0/**/asset/css/*.css',
					'!../release/2.0/themes/*'
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
			all: [
				'Gruntfile.js',
				conf.js_folder + 'src/*.js'
			]
		},

		// JS: Validate JS files (2).
		jshint: {
			all: [
				'Gruntfile.js',
				conf.js_folder + 'src/*.js'
			],
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
			}
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
					conf.css_folder + 'src/**/*.scss'
				],
				tasks: ['clear', 'sass', 'autoprefixer'],
				options: {
					debounceDelay: 500
				}
			},

			js: {
				files: [
					conf.js_folder + 'src/**/*.js'
				],
				tasks: ['clear', 'concat'],
				options: {
					debounceDelay: 500
				}
			}
		},

		// POT: Create the .pot translation index.
		makepot: {
			target: {
				options: {
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
					type: 'wp-plugin',
					updateTimestamp: true,
					updatePoFiles: true
				}
			}
		},
		potomo: {
			dist: {
				options: {
					poDel: false
				},
				files: [{
					expand: true,
					cwd: conf.translation.pot_dir,
					src: ['*.po'],
					dest: conf.translation.pot_dir,
					ext: '.mo',
					nonull: true
				}]
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
					bin: '/usr/bin/phpcbf',
					standard: 'WordPress-Core',
					verbose: true
				}
			}
		},

		phpcbf: {
			options: {
				noPatch: true,
				bin: '/usr/bin/phpcbf',
				standard: 'WordPress-Core'
			},
			main: {
				src: [ '*.php', '2.0/*.php', '2.0/include/coursepress/*.php' ]
			},
			admin: {
				src: [
					'2.0/admin/*.php',
					'2.0/admin/controller/*.php',
					'2.0/admin/view/*.php'
				]
			},
			data: {
				src: [
					'2.0/include/coursepress/data/*.php',
					'2.0/include/coursepress/data/discussion/*.php',
					'2.0/include/coursepress/data/shortcode/*.php'
				]
			},
			helper: {
				src: [
					'2.0/include/coursepress/helper/*.php',
					'2.0/include/coursepress/helper/extension/*.php',
					'2.0/include/coursepress/helper/integration/*.php',
					'2.0/include/coursepress/helper/query/*.php',
					'2.0/include/coursepress/helper/setting/*.php',
					'2.0/include/coursepress/helper/table/*.php',
					'2.0/include/coursepress/helper/ui/*.php'
				]
			},
			template: {
				src: [
					'2.0/include/coursepress/template/*.php'
				]
			},
			view: {
				src: [
					'2.0/include/coursepress/view/admin/*.php',
					'2.0/include/coursepress/view/admin/assessment/*.php',
					'2.0/include/courseperss/view/admin/communication/*.php',
					'2.0/include/coursepress/view/admin/course/*.php',
					'2.0/include/coursepress/view/admin/setting/*.php',
					'2.0/include/coursepress/view/admin/student/*.php',
					'2.0/include/coursepress/view/front/*.php'
				]
			},
			widget: {
				src: [
					'2.0/include/coursepress/widget/*.php'
				]
			},
			campus: {
				src: [
					'2.0/campus/*.php',
					'2.0/campus/include/*.php'
				]
			},
			premium: {
				src: [
					'2.0/premium/*.php',
					'2.0/premium/include/*.php'
				]
			},
			upgrade: {
				src: [
					'*.php',
					'upgrade/*.php'
				]
			}
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
			pro_1: {
				options: {
					patterns: conf.plugin_patterns.pro_1
				},
				files: [conf.plugin_patterns.files_1]
			},
			pro_2: {
				options: {
					patterns: conf.plugin_patterns.pro_2
				},
				files: [conf.plugin_patterns.files_2]
			},
			free_1: {
				options: {
					patterns: conf.plugin_patterns.free_1
				},
				files: [ conf.plugin_patterns.files_1 ]
			},
			free_2: {
				options: {
					patterns: conf.plugin_patterns.free_2
				},
				files: [conf.plugin_patterns.files_2]
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
			pro_1: conf.plugin_branches.exclude_1_pro,
			pro_2: conf.plugin_branches.exclude_2_pro,
			free_1: conf.plugin_branches.exclude_1_free,
			free_2: conf.plugin_branches.exclude_2_free,
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
					'!.git',
					'!Gruntfile.js',
					'!package.json',
					'!tests/*',
					'!tests/**',
					/** UPGRADE **/
					'!upgrade/css/src',
					'!upgrade/css/src/*',
					'!upgrade/css/src/**',
					'!upgrade/js/src',
					'!upgrade/js/src/*',
					'!upgrade/js/src/**',
					'!tests',
					/** 2.0 **/
					'!2.0/.git',
					'!2.0/.gitattributes',
					'!2.0/.gitmodules',
					'!2.0/.gitignore',
					'!2.0/Gruntfile.js',
					'!2.0/package.json',
					'!2.0/README.md',
					'!2.0/node_modules',
					'!2.0/node_modules/*',
					'!2.0/node_modules/**',
					'!2.0/test',
					'!2.0/test/*',
					'!2.0/test/**',
					'!2.0/campus',
					'!2.0/campus/*',
					'!2.0/campus/**',
					'!2.0/themes/coursepress/.git',
					'!2.0/asset/css/src',
					'!2.0/asset/css/src/*',
					'!2.0/asset/css/src/**',
					'!2.0/asset/js/src',
					'!2.0/asset/js/src/*',
					'!2.0/asset/js/src/**',
					'!2.0/bitbucket-pipelines.yml'
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
					/** UPGRADE **/
					'!upgrade/css/src',
					'!upgrade/css/src/*',
					'!upgrade/css/src/**',
					'!upgrade/js/src',
					'!upgrade/js/src/*',
					'!upgrade/js/src/**',
					'!tests',
					/** 1.x **/
					'!1.x/.git',
					'!1.x/.gitattributes',
					'!1.x/.gitmodules',
					'!1.x/.gitignore',
					'!1.x/Gruntfile.js',
					'!1.x/package.json',
					'!1.x/README.md',
					'!1.x/node_modules',
					'!1.x/node_modules/*',
					'!1.x/themes/coursepress/.git',
					/** 2.0 **/
					'!2.0/.git',
					'!2.0/.gitattributes',
					'!2.0/.gitmodules',
					'!2.0/.gitignore',
					'!2.0/Gruntfile.js',
					'!2.0/package.json',
					'!2.0/README.md',
					'!2.0/node_modules',
					'!2.0/node_modules/*',
					'!2.0/node_modules/**',
					'!2.0/test',
					'!2.0/test/*',
					'!2.0/test/**',
					'!2.0/campus',
					'!2.0/campus/*',
					'!2.0/campus/**',
					'!2.0/themes/coursepress/.git',
					'!2.0/asset/css/src',
					'!2.0/asset/css/src/*',
					'!2.0/asset/css/src/**',
					'!2.0/asset/js/src',
					'!2.0/asset/js/src/*',
					'!2.0/asset/js/src/**',
					'!2.0/bitbucket-pipelines.yml'
				],
				dest: '../release',
				noEmpty: true
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
	grunt.registerTask( 'i18n', ['makepot', 'potomo' ] );

	grunt.registerTask( 'test', ['phpunit'] );
	grunt.registerTask( 'php', ['phplint', 'phpcs:sniff'] );

	grunt.registerTask( 'default', ['php', 'test', 'js', 'css', 'i18n' ] );

	grunt.registerTask( 'release', 'Generating release copy', function( target ) {
		if ( ! target ) {
			return;
		}

		grunt.task.run( 'clean:release');
		grunt.task.run( 'copy:release' );

		if ( 'pro' === target ) {
			grunt.task.run( 'replace:pro_2' );
			grunt.task.run( 'clean:pro_2' );
			grunt.task.run( 'compress:release_pro' );
		} else if ( 'free' === target ) {
			grunt.task.run( 'replace:free_2' );
			grunt.task.run( 'clean:free_2' );
			grunt.task.run( 'compress:release_free' );
		}
	});
};
