module.exports = function(grunt) {
	// -------------------------------------------------------------------------
	// Configuration.
	var conf = {
		// Folder that contains the CSS files.
		js_folder: 'coursepress-files/scripts/',

		// Concatenate those JS files into a single file (target: [source, source, ...]).
		js_files_concat: {
			'{js}admin-general.js':           ['{js}src/admin-general.js'],
			'{js}CoursePress.js':             ['{js}src/CoursePress.js'],
			'{js}CoursePressCourse.js':       ['{js}src/CoursePressCourse.js'],
			'{js}CoursePressCourseList.js':   ['{js}src/CoursePressCourseList.js'],
			'{js}CoursePressFront.js':        ['{js}src/CoursePressFront.js'],
			'{js}CoursePressUnitsBuilder.js': ['{js}src/CoursePressUnitsBuilder.js']
		},

		// Folder that contains the CSS files.
		css_folder: 'coursepress-files/styles/',

		// SASS files to process. Resulting CSS files will be minified as well.
		css_files_compile: {
			'{css}admin-general.css':     '{css}sass/admin/admin-general.scss',
			'{css}admin-global.css':      '{css}sass/admin/admin-global.scss',
			'{css}coursepress_front.css': '{css}sass/coursepress_front.scss',
			'{css}bbm.modal.css':         '{css}sass/bbm.modal.scss',
			'{css}editor.css':            '{css}sass/editor.scss'
		},

		// Regex patterns to exclude from transation.
		no_translation: [
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

		// Different plugin settings.
		translation_dir: 'languages/',
		textdomain: 'cp',
		plugin_dir: 'coursepress/',
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

		// JS: Validate JS files.
		jshint: {
			all: [
				'Gruntfile.js',
				conf.js_folder + 'src/*.js'
			],
			options: {
				curly:   true,
				eqeqeq:  true,
				immed:   true,
				latedef: true,
				newcap:  true,
				noarg:   true,
				sub:     true,
				undef:   true,
				boss:    true,
				eqnull:  true,
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

		// POT: Create the .pot translation index.
		makepot: {
			target: {
				options: {
					cwd: '',
					domainPath: conf.translation_dir,
					exclude: conf.no_translation,
					mainFile: conf.plugin_file,
					potFilename: conf.textdomain + '.pot',
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
		}

	} );

	// Load external task handlers.
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-autoprefixer' );
	grunt.loadNpmTasks( 'grunt-contrib-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-concat' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );

	// Define default tasks.
	grunt.registerTask( 'js', ['jshint', 'concat', 'uglify'] );
	grunt.registerTask( 'css', ['sass', 'autoprefixer', 'cssmin'] );
	grunt.registerTask( 'default', ['js', 'css'] );
	grunt.registerTask( 'build', ['default', 'makepot'] );

};
