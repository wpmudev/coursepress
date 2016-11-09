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
		version_1: '1.2.6.7',
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
				'(^.php)',      // Ignore non-php files.
				'bin/.*',       // Unit testing.
				'2.0/test/.*',      // Unit testing.
				'2.0/test/php/.*', // Uni testing
				'node_modules/.*',
				'2.0/node_modules/.*',
				'2.0/tcpdf/.*',
				'2.0/themes/.*',
				'1.x/node_moudles/.*',
				'lib/TCPDF/.*', // External module.
				'themes/.*',    // External module.
			],
			pot_dir: 'language/',  // With trailing slash.
			textdomain_pro: 'cp',   // Campus uses same textdomain.
			textdomain_free: 'coursepress',
		},

		// Build branches.
		plugin_branches: {
			exclude_1_pro: [
				'1.x/node_modules',
				'1.x/.gitignore',
				'1.x/.gitmodules',
				'1.x/Gruntfile.js',
				'1.x/package.json',
				'1.x/README.md',
				'1.x/readme.txt',
				'1.x/themes/.gitattributes',
				'1.x/themes/.gitignore',
				'1.x/themes/.gitmodules',
				'1.x/themes/Gruntfile.js',
				'1.x/themes/package.json',
				'1.x/themes/README.md',
				'1.x/themes/readme.txt'
			],
			exclude_2_pro: [
				'2.0/test',
				'2.0/readme.txt',
				'2.0/language/coursepress.pot',
				'2.0/campus',
				'2.0/node_modules',
				'2.0/vendor',
				'2.0/.gitattributes',
				'2.0/.gitignore',
				'2.0/.gitmodules',
				'2.0/composer.json',
				'2.0/composer.lock',
				'2.0/Gruntfile.js',
				'2.0/package.json',
				'2.0/README.md',
				'2.0/asset/css/src',
				'2.0/asset/js/src',
				'2.0/themes/.gitattributes',
				'2.0/themes/.gitignore',
				'2.0/themes/.gitmodules',
				'2.0/themes/bitbucket-pipelines.yml',
				'2.0/themes/README.md',
				'2.0/themes/package.json',
				'2.0/thems/Gruntfile.js'
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
				{ match: /'CP_TD'/g, replace: '\'cp\'' },
				{ match: /\/\* start:pro \*\//g, replace: '' },
				{ match: /\/\* end:pro \*\//g, replace: '' },
				{ match: /\/\* start:free \*[^\*]+\* end:free \*\//mg, replace: '' },
				{ match: /\/\* start:campus \*[^\*]+\* end:campus \*\//mg, replace: '' }
			],
			free: [
				{ match: /CoursePress Base/g, replace: 'CoursePress' },
				{ match: /BUILDTIME/g, replace: buildtime },
				{ match: /'CP_TD'/g, replace: '\'coursepress\'' },
				{ match: /\/\* start:free \*\//g, replace: '' },
				{ match: /\/\* end:free \*\//g, replace: '' },
				{ match: /\/\* start:pro \*[^\*]+\* end:pro \*\//mg, replace: '' },
				{ match: /\/\* start:campus \*[^\*]+\* end:campus \*\//mg, replace: '' }
			],
			campus: [
				{ match: /CoursePress Base/g, replace: 'CoursePress Campus' },
				{ match: /BUILDTIME/g, replace: buildtime },
				{ match: /'CP_TD'/g, replace: '\'cp\'' },
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
					'!../release/1.x/external/*'
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
					'../release/2.0/**/asset/css/*.css'
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

		fixmyjs: {
			options: {
				config: '.jshintrc',
				indentPref: 'tabs'
			},
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
					cwd: '',
					domainPath: conf.translation.pot_dir,
					exclude: conf.translation.ignore_files,
					mainFile: conf.plugin_file,
					potFilename: conf.translation.textdomain_pro + '.pot',
					potHeaders: {
						'poedit': true, // Includes common Poedit headers.
						'language-team': 'WPMU Dev <support@wpmudev.org>',
						'report-msgid-bugs-to': 'http://wordpress.org/support/plugin/coursepress',
						'last-translator': 'WPMU Dev <support@wpmudev.org>',
						'x-generator': 'grunt-wp-i18n',
						'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
					},
					type: 'wp-plugin' // wp-plugin or wp-theme
				}
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
			pro: {
				options: {
					mode: 'zip',
					archive: './release/<%= pkg.name %>-pro-<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: 'release/<%= pkg.version %>-pro/',
				src: [ '**/*' ],
				dest: conf.plugin_dir
			},
			free: {
				options: {
					mode: 'zip',
					archive: './release/<%= pkg.name %>-free-<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: 'release/<%= pkg.version %>-free/',
				src: [ '**/*' ],
				dest: conf.plugin_dir
			},
			campus: {
				options: {
					mode: 'zip',
					archive: './release/<%= pkg.name %>-campus-<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: 'release/<%= pkg.version %>-campus/',
				src: [ '**/*' ],
				dest: conf.plugin_dir
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
					bin: '^/srv/www/phpcs',
					standard: 'WordPress-Core'
				}
			}
		},

		phpcbf: {
			options: {
				noPatch: true,
				bin: 'vendor/bin/phpcbf',
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
			release_free: {
				src: [
					'release/<%= pkg.version %>-free/',
					'release/<%= pkg.version %>-free-<%= pkg.version %>.zip'
				]
			},
			release_campus: {
				src: [
					'release/<%= pkg.version %>-campus/',
					'release/<%= pkg.version %>-campus-<%= pkg.version %>.zip'
				]
			},
			pro_1: conf.plugin_branches.exclude_1_pro,
			pro_2: conf.plugin_branches.exclude_2_pro,
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
					/** UPGRADE **/
					'!upgrade/css/src',
					'!upgrade/css/src/*',
					'!upgrade/css/src/**',
					'!upgrade/js/src',
					'!upgrade/js/src/*',
					'!upgrade/js/src/**',
					'!tests',
					'!tests/*',
					'!tests/**',
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
					'!2.0/test',
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
			translation: {
				src: conf.translation.pot_dir + conf.translation.textdomain_pro + '.pot',
				dest: conf.translation.pot_dir + conf.translation.textdomain_free + '.pot',
				nonull: true
			},
			pro: {
				src: [ conf.plugin_patterns.files_1.src, conf.plugin_patterns.files_2],
				dest: 'release/<%= pkg.version %>-pro/'
			},
			free: {
				//src: conf.plugin_patterns.files.src,
				//dest: 'release/<%= pkg.version %>-free/'
			},
			campus: {
				//src: conf.plugin_patterns.files.src,
				//dest: 'release/<%= pkg.version %>-campus/'
			}
		},

		// BUILD: Git control (check out branch).
		gitcheckout: {
			dev: {
				options: {
					verbose: true,
					branch: conf.plugin_branches.dev,
					overwrite: true
				}
			},
			pro: {
				options: {
					verbose: true,
					branch: conf.plugin_branches.pro,
					overwrite: true
				}
			},
			free: {
				options: {
					branch: conf.plugin_branches.free,
					overwrite: true
				}
			},
			campus: {
				options: {
					branch: conf.plugin_branches.campus,
					overwrite: true
				}
			},
			base: {
				options: {
					branch: conf.plugin_branches.base
				}
			}
		},

		// BUILD: Git control (add files).
		gitadd: {
			pro: {
				options: {
				verbose: true, all: true }
			},
			free: {
				options: { all: true }
			},
			campus: {
				options: { all: true }
			}
		},

		// BUILD: Git control (commit changes).
		gitcommit: {
			pro: {
				verbose: true,
				options: {
					message: 'Built from: ' + conf.plugin_branches.base,
					allowEmpty: true
				},
				files: { src: ['.'] }
			},
			free: {
				options: {
					message: 'Built from: ' + conf.plugin_branches.base,
					allowEmpty: true
				},
				files: { src: ['.'] }
			},
			campus: {
				options: {
					message: 'Built from: ' + conf.plugin_branches.base,
					allowEmpty: true
				},
				files: { src: ['.'] }
			}
		},

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

	grunt.registerTask( 'test', ['phpunit'] );
	grunt.registerTask( 'php', ['phplint', 'phpcs:sniff'] );

	grunt.registerTask( 'default', ['php', 'test', 'js', 'css'] );

	grunt.registerTask( 'release', 'Generating release copy', function( target ) {
		if ( ! target ) {
			return;
		}

		grunt.task.run( 'clean:release' );
		grunt.task.run( 'copy:release' );

		if ( 'pro' == target ) {
			grunt.task.run( 'replace:pro_1' );
			grunt.task.run( 'replace:pro_2' );
			grunt.task.run( 'compress:release_pro' );
		}
	});

	grunt.registerTask( 'zipped', 'Compressing release version', function( target ) {
		grunt.task.run( 'compress:release_pro' );
	});
};
