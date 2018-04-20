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
		// Folder that contains the CSS files.
		js_folder: 'asset/js/',

		// Folder that contains the CSS files.
		css_folder: 'asset/css/',

		// Concatenate those JS files into a single file (target: [source, source, ...]).
		js_files_concat: {
			'{js}admin-general.js':            ['{js}src/admin-general.js'],
			'{js}admin-upgrade.js':            ['{js}src/admin-upgrade.js'],
			'{js}coursepress.js':              ['{js}src/coursepress.js'],
			'{js}coursepress-course.js':       ['{js}src/coursepress-course.js'],
			'{js}coursepress-courselist.js':   ['{js}src/coursepress-courselist.js'],
			'{js}coursepress-front.js':        ['{js}src/coursepress-front.js'],
			'{js}coursepress-unitsbuilder.js': ['{js}src/coursepress-unitsbuilder.js'],
			'{js}coursepress-calendar.js':     ['{js}src/coursepress-calendar.js'],
			'{js}coursepress-featured.js':     ['{js}src/coursepress-featured.js'],
			'{js}coursepress-assessment.js':   ['{js}src/coursepress-assessment.js'],
			'{js}admin-ui.js':                 ['{js}src/admin-ui.js'],
			'{js}front.js':						[
				'{js}src/front-core.js',
				'{js}src/front-modules.js',
				'{js}src/front-enrollment.js',
				'{js}src/front-dashboard.js'
			]
		},

		// SASS files to process. Resulting CSS files will be minified as well.
		css_files_compile: {
			'{css}admin-general.css':     '{css}src/admin/admin-general.scss',
			'{css}admin-global.css':      '{css}src/admin/admin-global.scss',
			'{css}coursepress_front.css': '{css}src/coursepress_front.scss',
			'{css}bbm.modal.css':         '{css}src/bbm.modal.scss',
			'{css}editor.css':            '{css}src/editor.scss',
			'{css}editor-rtl.css':        '{css}src/editor.scss',
			'{css}admin-ui.css':          '{css}src/admin/admin-ui.scss',
			'{css}front.css':             '{css}src/front.scss',
			'{css}admin-menu.css':		   '{css}src/admin-menu.scss'
		},

		// PHP files to validate.
		php_files: [
			'coursepress.php',
			'premium/**/*.php',
			'campus/*.php',
			'include/coursepress/**/*.php',
			'!**/helper/utility.php',  // TODO: Too complex. Manually fix this file first!
			'!**/model/shortcode.php', // TODO: Too complex. Manually fix this file first!
			'!**/external/**/*.php'
		],

		// BUILD branches.
		plugin_branches: {
			exclude_pro: [
				'./test',
				'./readme.txt',
				'./campus',
				'./node_modules',
				'./vendor',
				'./.gitattributes',
				'./.gitignore',
				'./.gitmodules',
				'./composer.json',
				'./composer.lock',
				'./Gruntfile.js',
				'./package.json',
				'./README.md',
				'./asset/css/src',
				'./asset/js/src'
			],
			exclude_free: [
				'./test',
				'./premium',
				'./campus',
				'./node_modules',
				'./vendor',
				'./.gitattributes',
				'./.gitignore',
				'./composer.json',
				'./composer.lock',
				'./Gruntfile.js',
				'./package.json',
				'./README.md'
			],
			exclude_campus: [
				'./test',
				'./readme.txt',
				'./node_modules',
				'./vendor',
				'./.gitattributes',
				'./.gitignore',
				'./composer.json',
				'./composer.lock',
				'./Gruntfile.js',
				'./package.json',
				'./README.md'
			],
			base: 'coursepress/2.0-dev',
			pro: 'coursepress/2.0-pro-test',
			free: 'coursepress/2.0-free-test',
			campus: 'coursepress/2.0-campus-test'
		},

		// BUILD patterns to exclude code for specific builds.
		plugin_patterns: {
			pro: [
				{ match: /CoursePress Base/g, replace: 'CoursePress Pro' },
				{ match: /BUILDTIME/g, replace: buildtime },
				{ match: /\/\* start:pro \*\//g, replace: '' },
				{ match: /\/\* end:pro \*\//g, replace: '' },
				{ match: /\/\* start:free \*[^\*]+\* end:free \*\//mg, replace: '' },
				{ match: /\/\* start:campus \*[^\*]+\* end:campus \*\//mg, replace: '' }
			],
			free: [
				{ match: /CoursePress Base/g, replace: 'CoursePress' },
				{ match: /BUILDTIME/g, replace: buildtime },
				{ match: /\/\* start:free \*\//g, replace: '' },
				{ match: /\/\* end:free \*\//g, replace: '' },
				{ match: /\/\* start:pro \*[^\*]+\* end:pro \*\//mg, replace: '' },
				{ match: /\/\* start:campus \*[^\*]+\* end:campus \*\//mg, replace: '' }
			],
			campus: [
				{ match: /CoursePress Base/g, replace: 'CoursePress Campus' },
				{ match: /BUILDTIME/g, replace: buildtime },
				{ match: /\/\* start:campus \*\//g, replace: '' },
				{ match: /\/\* end:campus \*\//g, replace: '' },
				{ match: /\/\* start:pro \*[^\*]+\* end:pro \*\//mg, replace: '' },
				{ match: /\/\* start:free \*[^\*]+\* end:free \*\//mg, replace: '' }
			],
			// Files to apply above patterns to (not only php files).
			files: {
				expand: true,
				src: [
					'**',
					'**/*.php',
					'**/*.css',
					'**/*.js',
					'**/*.html',
					'**/*.txt',
					'!node_modules/**',
					'!vendor/**',
					'!release/**',
					'!test/**',
					'!asset/file/**',
					'!Gruntfile.js',
					'!package.json',
					'!bitbucket-pipelines.yml',
					'!build/**',
					'!.git/**'
				],
				dest: './'
			}
		},

		// Different plugin settings.
		plugin_file: 'coursepress.php',
		plugin_dir: 'coursepress'
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

		// BUILD: Copy files.
		copy: {
			pro: {
				src: conf.plugin_patterns.files.src,
				dest: 'release/<%= pkg.version %>-pro/'
			},
			free: {
				src: conf.plugin_patterns.files.src,
				dest: 'release/<%= pkg.version %>-free/'
			},
			campus: {
				src: conf.plugin_patterns.files.src,
				dest: 'release/<%= pkg.version %>-campus/'
			}
		},

		// COMPRESS: Create a zip-archive of the plugin (for distribution).
		compress: {
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
					bin: '/usr/bin/phpcs',
					standard: 'WordPress-Core'
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
				src: [ '*.php', 'include/coursepress/*.php' ]
			},
			admin: {
				src: [
					'admin/*.php',
					'admin/controller/*.php',
					'admin/view/*.php'
				]
			},
			data: {
				src: [
					'include/coursepress/data/*.php',
					'include/coursepress/data/discussion/*.php',
					'include/coursepress/data/shortcode/*.php'
				]
			},
			helper: {
				src: [
					'include/coursepress/helper/*.php',
					'include/coursepress/helper/extension/*.php',
					'include/coursepress/helper/integration/*.php',
					'include/coursepress/helper/query/*.php',
					'include/coursepress/helper/setting/*.php',
					'include/coursepress/helper/table/*.php',
					'include/coursepress/helper/ui/*.php'
				]
			},
			template: {
				src: [
					'include/coursepress/template/*.php'
				]
			},
			view: {
				src: [
					'include/coursepress/view/admin/*.php',
					'include/coursepress/view/admin/assessment/*.php',
					'include/courseperss/view/admin/communication/*.php',
					'include/coursepress/view/admin/course/*.php',
					'include/coursepress/view/admin/setting/*.php',
					'include/coursepress/view/admin/student/*.php',
					'include/coursepress/view/front/*.php'
				]
			},
			widget: {
				src: [
					'include/coursepress/widget/*.php'
				]
			},
			campus: {
				src: [
					'campus/*.php',
					'campus/include/*.php'
				]
			},
			premium: {
				src: [
					'premium/*.php',
					'premium/include/*.php'
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

	// Plugin build tasks
	grunt.registerTask( 'build', 'Run all tasks.', function(target) {
		var build = [], i, branch;

		if ( target ) {
			build.push( target );
		} else {
			build = ['pro', 'free', 'campus'];
		}

		// Run the default tasks (js/css/php validation)
		//HIDE:grunt.task.run( 'default' );

		// Generate all translation files (pro and free)
		//grunt.task.run( 'lang' );

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

			// Create a distributable zip-file of the plugin branch.
			grunt.task.run( 'clean:release_' + branch );
			grunt.task.run( 'copy:' + branch );
			grunt.task.run( 'compress:' + branch );

			grunt.task.run( 'gitcheckout:base');
		}
	});

	// Test task.
	grunt.registerTask( 'hello', 'Test if grunt is working', function() {
		grunt.log.subhead( 'Hi there :)' );
		grunt.log.writeln( 'Looks like grunt is installed!' );
	});

	grunt.task.run( 'clear' );

	// Define default tasks.
	//grunt.registerTask( 'js', ['jsvalidate', 'jshint', 'concat', 'uglify'] );
	grunt.registerTask( 'js', ['concat', 'uglify'] );
	grunt.registerTask( 'css', ['sass', 'autoprefixer', 'cssmin'] );

	grunt.registerTask( 'test', ['phpunit'] );
	grunt.registerTask( 'php', ['phplint', 'phpcs:sniff'] );

	grunt.registerTask( 'default', ['php', 'test', 'js', 'css'] );
};
