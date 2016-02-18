/*global require*/

module.exports = function(grunt) {
	// Show elapsed time at the end.
	require( 'time-grunt' )(grunt);

	// Load all grunt tasks.
	require( 'load-grunt-tasks' )(grunt);

	// -------------------------------------------------------------------------
	// Configuration.
	var conf = {
		// Folder that contains the CSS files.
		js_folder: 'coursepress-files/assets/js/',

		// Folder that contains the CSS files.
		css_folder: 'coursepress-files/assets/css/',

		// Concatenate those JS files into a single file (target: [source, source, ...]).
		js_files_concat: {
			'{js}admin-general.js':           ['{js}src/admin-general.js'],
			'{js}CoursePress.js':             ['{js}src/CoursePress.js'],
			'{js}CoursePressCourse.js':       ['{js}src/CoursePressCourse.js'],
			'{js}CoursePressCourseList.js':   ['{js}src/CoursePressCourseList.js'],
			'{js}CoursePressFront.js':        ['{js}src/CoursePressFront.js'],
			'{js}CoursePressUnitsBuilder.js': ['{js}src/CoursePressUnitsBuilder.js']
		},

		// SASS files to process. Resulting CSS files will be minified as well.
		css_files_compile: {
			'{css}admin-general.css':     '{css}src/admin/admin-general.scss',
			'{css}admin-global.css':      '{css}src/admin/admin-global.scss',
			'{css}coursepress_front.css': '{css}src/coursepress_front.scss',
			'{css}bbm.modal.css':         '{css}src/bbm.modal.scss',
			'{css}editor.css':            '{css}src/editor.scss'
		},

		// PHP files to validate.
		php_files: [
			'coursepress.php',
			'coursepress-files/premium/**/*.php',
			'coursepress-files/campus/*.php',
			'coursepress-files/lib/CoursePress/**/*.php',
			'!**/Helper/Utility.php',   // TODO: Too complex. Manually fix this file first!
			'!**/Model/Shortcodes.php', // TODO: Too complex. Manually fix this file first!
			'!**/external/**/*.php'
		],

		// Regex patterns to exclude from transation.
		translation: {
			ignore_files: [
				'(^.php)',      // Ignore non-php files.
				'bin/.*',       // Unit testing.
				'tests/.*',     // Unit testing.
				'node_modules/.*',
				'lib/TCPDF/.*', // External module.
				'themes/.*',    // External module.
				'css/.*',       // Deprecated folder.
				'js/.*',        // Deprecated folder.
				'includes/.*'   // Deprecated folder.
			],
			pot_dir: 'languages/',  // With trailing slash.
			textdomain_pro: 'cp',   // Campus uses same textdomain.
			textdomain_free: 'coursepress',
		},

		// BUILD branches.
		plugin_branches: {
			exclude_pro: [
				'./readme.txt',
				'languages/coursepress.pot',
				'coursepress-files/campus'
			],
			exclude_free: [
				'languages/cp.pot',
				'coursepress-files/premium',
				'coursepress-files/campus'
			],
			exclude_campus: [
				'./readme.txt',
				'languages/coursepress.pot'
			],
			base: 'agile/2.0-A1IGTDI1-remove-old-v1-x-code',
			pro: 'coursepress/2-pro',
			free: 'coursepress/2-free',
			campus: 'coursepress/2-campus'
		},

		// BUILD patterns to exclude code for specific builds.
		plugin_patterns: {
			pro: [
				{ match: /CoursePress Base/g, replace: 'CoursePress Pro' },
				{ match: /'TD'/g, replace: 'cp' },
				{ match: /\/\* start:pro \*\//g, replace: '' },
				{ match: /\/\* end:pro \*\//g, replace: '' },
				{ match: /\/\* start:free \*[^\*]+\* end:free \*\//mg, replace: '' },
				{ match: /\/\* start:campus \*[^\*]+\* end:campus \*\//mg, replace: '' }
			],
			free: [
				{ match: /CoursePress Base/g, replace: 'CoursePress' },
				{ match: /'TD'/g, replace: 'coursepress' },
				{ match: /\/\* start:free \*\//g, replace: '' },
				{ match: /\/\* end:free \*\//g, replace: '' },
				{ match: /\/\* start:pro \*[^\*]+\* end:pro \*\//mg, replace: '' },
				{ match: /\/\* start:campus \*[^\*]+\* end:campus \*\//mg, replace: '' }
			],
			campus: [
				{ match: /CoursePress Base/g, replace: 'CoursePress Campus' },
				{ match: /'TD'/g, replace: 'cp' },
				{ match: /\/\* start:campus \*\//g, replace: '' },
				{ match: /\/\* end:campus \*\//g, replace: '' },
				{ match: /\/\* start:pro \*[^\*]+\* end:pro \*\//mg, replace: '' },
				{ match: /\/\* start:free \*[^\*]+\* end:free \*\//mg, replace: '' }
			],
			// Files to apply above patterns to (not only php files).
			files: {
				expand: true,
				src: [
					'**/*.php',
					'**/*.css',
					'**/*.js',
					'**/*.html',
					'**/*.txt',
					'!node_modules/**',
					'!vendor/**',
					'!languages/**',
					'!coursepress-files/files/**',
					'!Gruntfile.js',
					'!build/**',
					'!.git/**'
				],
				dest: './'
			}
		},

		// Different plugin settings.
		plugin_file: 'coursepress.php'
	};
	// -------------------------------------------------------------------------
	var key, ind, newkey, newval;
	for ( key in conf.js_files_concat ) {
		newkey = key.replace( '{js}', conf.js_folder );
		newval = conf.js_files_concat[key];
		delete conf.js_files_concat[key];
		for ( ind in newval ) { newval[ind] = newval[ind].replace( '{js}', conf.js_folder ); }
		conf.js_files_concat[newkey] = newval;
	}
	for ( key in conf.css_files_compile ) {
		newkey = key.replace( '{css}', conf.css_folder );
		newval = conf.css_files_compile[key].replace( '{css}', conf.css_folder );
		delete conf.css_files_compile[key];
		conf.css_files_compile[newkey] = newval;
	}
	// -------------------------------------------------------------------------


	// Define grunt tasks.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

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
				tasks: ['sass', 'autoprefixer'],
				options: {
					debounceDelay: 500
				}
			},

			js: {
				files: [
					conf.js_folder + 'src/**/*.js'
				],
				tasks: ['jshint', 'concat'],
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

		// BUILD: Copy files.
		copy: {
			translation: {
				src: conf.translation.pot_dir + conf.translation.textdomain_pro + '.pot',
				dest: conf.translation.pot_dir + conf.translation.textdomain_free + '.pot',
				nonull: true
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
					bin: 'vendor/bin/phpcs',
					standard: 'WordPress'
				}
			}
		},

		// PHP: Unit tests.
		phpunit: {
			classes: {
				dir: ''
			},
			options: {
				bin: 'vendor/phpunit/phpunit/phpunit',
				bootstrap: 'tests/bootstrap.php',
				testsuite: 'default',
				configuration: 'tests/phpunit.xml',
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
			pro: conf.plugin_branches.exclude_pro,
			free: conf.plugin_branches.exclude_free,
			campus: conf.plugin_branches.exclude_campus
		},

		// BUILD: Git control (check out branch).
		gitcheckout: {
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

	// Plugin build tasks
	grunt.registerTask( 'build', 'Run all tasks.', function(target) {
		var build = [], i, branch;

		if ( target ) {
			build.push( target );
		} else {
			build = ['pro', 'free', 'campus'];
		}

		grunt.log.subhead( 'Prepare the dev branch...' );

		// Run the default tasks (js/css/php validation)
		grunt.task.run( 'default' );

		// Generate all translation files (pro and free)
		grunt.task.run( 'lang' );

		for ( i in build ) {
			branch = build[i];
			grunt.log.subhead( 'Update product branch [' + branch + ']...' );

			// Checkout the destination branch.
			grunt.task.run( 'gitcheckout:' + branch );

			// Remove code and files that does not belong to this version.
			grunt.task.run( 'replace:' + branch );
			grunt.task.run( 'clean:' + branch );

			// Add the processes/cleaned files to the target branch.
			grunt.task.run( 'gitadd:' + branch );
			grunt.task.run( 'gitcommit:' + branch );
			grunt.task.run( 'gitcheckout:base');
		}
	});

	// Test task.
	grunt.registerTask( 'test', 'Test if grunt is working', function() {
		grunt.log.subhead( 'Looks like grunt is installed!' );
	});

	// Define default tasks.
	grunt.registerTask( 'js', ['jsvalidate', 'jshint', 'concat', 'uglify'] );
	grunt.registerTask( 'css', ['sass', 'autoprefixer', 'cssmin'] );

	grunt.registerTask( 'test', ['phpunit'] );
	grunt.registerTask( 'php', ['phplint', 'phpcs:sniff'] );

	grunt.registerTask( 'default', ['php', 'test', 'js', 'css'] );
};
