module.exports = function(grunt) {
    'use strict';

    // Plugin Config
    var plugin_info = {
        version: '1.2.6.3',
        branches: {
            dev: 'coursepress/pro',
            wporg: 'coursepress/standard',
            base: 'coursepress/base'
        },
        marketpress_file: '128762_marketpress-ecommerce-3.0.0.2.zip'
    };

    var plugin_patterns = {
        dev: [
            { match: /<%= wpmudev.plugin.name %>/g, replace: 'CoursePress Pro' },
            { match: /<%= wpmudev.plugin.version %>/g, replace: plugin_info.version },
            { match: /<%= wpmudev.plugin.textdomain %>/g, replace: 'cp' },
            { match: /<%= wpmudev.plugin.option.is_pro %>/g, replace: 'true' },
            { match: /<%= wpmudev.plugin.option.marketpress_file %>/g, replace: plugin_info.marketpress_file },
            { match: /\/\/<wpmudev.plugin.pro_only>/g, replace: '' },
            { match: /<\/wpmudev.plugin.pro_only>/g, replace: '' },
            { match: /\/\/<wpmudev.plugin.free_only([^<]+)/mg, replace: '' },
            { match: /<\/wpmudev.plugin.free_only>/g, replace: '' },
        ],
        wporg: [
            { match: /<%= wpmudev.plugin.name %>/g, replace: 'CoursePress' },
            { match: /<%= wpmudev.plugin.version %>/g, replace: plugin_info.version },
            { match: /<%= wpmudev.plugin.textdomain %>/g, replace: 'coursepress' },
            { match: /<%= wpmudev.plugin.option.is_pro %>/g, replace: 'false' },
            { match: /\/\/<wpmudev.plugin.pro_only([^<]+)/mg, replace: '' },
            { match: /<\/wpmudev.plugin.pro_only>/g, replace: '' },
            { match: /\/\/<wpmudev.plugin.free_only>/g, replace: '' },
            { match: /<\/wpmudev.plugin.free_only>/g, replace: '' },
            { match: /<%= wpmudev.plugin.changelog %>/g, replace: (function() {
                var changelog = grunt.file.read('./changelog.txt');
                changelog = changelog.replace(/^(\S|\s)*==.changelog.==\S*/igm, '' ).trim();
                return changelog;
            })() }
        ],
        files: [ { expand: true, src: [
            '**/*.php',
            '**/*.css',
            '**/*.js',
            '**/*.html',
            '**/*.txt',
            '!node_modules/**',
            '!includes/external/**',
            '!Gruntfile.js',
            '!package.json',
            '!build/**',
            '!grunt_tasks/**',
            '!.git/**'
        ], dest: './' } ]
    };

	// Grunt configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

        // Plugin config
        build: {
            dev: {
            },
            wporg: {
            }
        },

        // Git config
        gitcheckout: {
            dev: {
                options: { branch: plugin_info.branches.dev, overwrite: true }
            },
            wporg: {
                options: { branch: plugin_info.branches.wporg, overwrite: true }
            },
            base: {
                options: { branch: plugin_info.branches.base }
            }
        },
        gitadd: {
            dev: {
                options: { all: true }
            },
            wporg: {
                options: { all: true }
            }
        },
        gitcommit: {
            dev: {
                options: { message: "Built from '" + plugin_info.branches.base + "'", allowEmpty: true },
                files: { src: ['.'] }
            },
            wporg: {
                options: { message: "Built from '" + plugin_info.branches.base + "'", allowEmpty: true },
                files: { src: ['.'] }
            }
        },

        // Cleanup config
        clean: {
            dev: [
                "./readme.txt",
            ],
            wporg: [
                "./includes/classes/class.basic.certificate.php",
                "./includes/external/dashboard/",
                './includes/plugins/*marketpress*.zip',
                './readme.md',
                './changelog.txt'
            ]
        },

        // Replace config
        replace: {
            dev: {
                options: {
                    patterns: plugin_patterns.dev
                },
                files: plugin_patterns.files
            },
            wporg: {
                options: {
                    patterns: plugin_patterns.wporg
                },
                files: plugin_patterns.files
            }
        },

        // i18n config
		makepot: {
		    target: {
		        options: {
					domainPath: '/languages',
					mainFile: 'coursepress.php',
					potFilename: 'cp-default.pot',
					potHeaders: {
						'poedit': true,
						'language-team': 'WPMU Dev <support@wpmudev.org>',
						'report-msgid-bugs-to': 'http://wordpress.org/support/plugin/coursepress',
						'last-translator': 'WPMU Dev <support@wpmudev.org>',
						'x-generator': 'grunt-wp-i18n'
					},
		            type: 'wp-plugin'
		        }
		    },
            dev: {
                options: {
                    domainPath: '/languages',
                    mainFile: 'coursepress.php',
                    potFilename: 'cp-default.pot',
                    potHeaders: {
                        'poedit': true,
                        'language-team': 'WPMU Dev <support@wpmudev.org>',
                        'report-msgid-bugs-to': 'http://wordpress.org/support/plugin/coursepress',
                        'last-translator': 'WPMU Dev <support@wpmudev.org>',
                        'x-generator': 'grunt-wp-i18n'
                    },
                    type: 'wp-plugin'
                }
            },
            wporg: {
                options: {
                    domainPath: '/languages',
                    mainFile: 'coursepress.php',
                    potFilename: 'coursepress-default.pot',
                    potHeaders: {
                        'poedit': true,
                        'language-team': 'WPMU Dev <support@wpmudev.org>',
                        'report-msgid-bugs-to': 'http://wordpress.org/support/plugin/coursepress',
                        'last-translator': 'WPMU Dev <support@wpmudev.org>',
                        'x-generator': 'grunt-wp-i18n'
                    },
                    type: 'wp-plugin'
                }
            }
		},
		wpmu_pot2mo: {
		    files: {
		        src: 'languages/*.pot',
		        expand: true
		    }
		}
	});

	// Load grunt modules
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-git');
    grunt.loadNpmTasks('grunt-replace');

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

	// Default task(s).
	grunt.registerTask( 'default', ['makepot', 'wpmu_pot2mo'] );

    // Plugin build tasks
    grunt.registerTask('build', 'Run all tasks.', function(target) {
        if (target == null) {
            grunt.warn('Target must be specified - build:dev or build:wporg');
        }

        grunt.task.run('gitcheckout:' + target );
        grunt.task.run('replace:' + target );
        grunt.task.run('clean:' + target );
        grunt.task.run('makepot:' + target );
        grunt.task.run('wpmu_pot2mo:' + target );
        grunt.task.run('gitadd:' + target );
        //grunt.task.run('gitcommit:' + target );
        //grunt.task.run('gitcheckout:base');

    });

    // Build pro and standard repo
    grunt.registerTask( 'buildAll', function() {
        grunt.task.run('build:dev');
        grunt.task.run('build:wporg');
    } );
	

};