# README #

The **only** development branch for CoursePress 2 is `coursepress/2.0-dev`. This branch ultimately is responsible for creating the production branches that are finally published. Consider it to be a "super branch".  

Production branches are automatically built, based on the dev branch. Any changes made to those other branches will be overwritten!

**Remember:** `coursepress/2.0-dev` is the ONLY branch that should be edited and forked!

**Notes:** 

1. Only fork and submit pull-requests to the super branch `coursepress/2.0-dev`!
2. Never fork the production branches (below)!
3. Never publish/release the super branch `coursepress/2.0-dev` anywhere!

-----

# PRODUCTION BRANCHES   

## CoursePress Pro (coursepress/2-pro)  

CoursePress Pro is the official premium plugin that lives on WPMU DEV. Also this plugin is used to power the WP Academy site.

## CoursePress (coursepress/2-free)  

CoursePress is the free limited version that gets published to the WordPress plugin directory.

## CoursePress Campus (coursepress/2-campus)  

CoursePress Campus is the version that is used on Edublogs and CampusPress.

-----

# DEVELOPMENT

As mentioned above: Only directly edit the branch `coursepress/2.0-dev`. Other branches should be only updated via grunt tasks:

**Update Production Branches:**

* `grunt build:pro` updates `coursepress/2-pro`.
* `grunt build:free` updates `coursepress/2-free`.
* `grunt build:campus` updates `coursepress/2-campus`.
* `grunt build` updates all three branches.

This is important: DO NOT let your IDE change the **source order** of the code. Fixing up formatting is fine, but moving code blocks around is not! It will confuse grunt and produce problems.

There are special comments in the `coursepress/2.0-dev` branch will make sure some code only end up on the pro plugin and some code only end up in the free plugin.

Those are:

    /* start:pro */
    echo 'This is only in coursepress/2-pro';  
    /* end:pro */
  
    /* start:free */
    echo 'This is only in coursepress/2-free';  
    /* end:free */

    /* start:campus */
    echo 'This is only in coursepress/2-campus';  
    /* end:campus */


### Working with the branches

#### Cloning ####

CoursePress uses submodules, so use the `--recursive` flag if you clone from command line:  

    $ git clone git@bitbucket.org:incsub/coursepress.git --recursive  

If you already have a cloned repo, you will need to *init* the submodule.  

    $ git submodule init --   
    $ git submodule update  

#### Agile workflow

Every bug fix/change must be made in a separate branch. Create a branch with name `agile/2.0-<id>-<short-desc>` and make all the changes and alpha-tests there. Once stable submit a pull request to the super branch `coursepress/2.0-dev`.

Do not directly update the super branch, always use pull requests!

#### JS and CSS files

Only edit/create javascript and css files inside the `/src` folders:

* `scripts/src/*` for javascript.
* `styles/src/*` for css. Use .scss extension (SASS)!

Important: Those folders are scanned and processed when running grunt. Files in base of `scripts/` and `styles/` are overwritten by grunt.


#### Working with MarketPress in CoursePress  

##### Preparing MarketPress for CoursePress Standard  

No steps required here as CoursePress Standard now fetches MarketPress Lite directly from the WordPress.org directory when the user wants to enable it.  

##### Preparing MarketPress for CoursePress Pro

* Download MarketPress from WPMU DEV Premium.  
* Save the zip file as `/files/marketpress-pro.zip` (replace existing file).  

# RELEASING #

See notes below on how to correctly set up grunt. *This has changed since 1.x!*

#### Grunt Task Runner  

**ALWAYS** use Grunt to build CoursePress production branches. Use the following commands:  

* `grunt watch` .. watch js and scss files, auto process them when changed.
* `grunt watch:js` .. only watch js files.
* `grunt watch:css` .. only watch css files.

* `grunt js` .. validate and minify js files.
* `grunt css` .. validate and compile scss files to css.
* `grunt lang` .. update the translations pot file.

* `grunt test` .. runs the unit tests.
* `grunt php` .. validate WP Coding Standards in php files.
* `grunt php-fix` .. tries to auto-fix incorrect code formatting.
* `grunt` .. run tasks: php, test, js, css

* `grunt build` .. runs all tasks (php, test, js, css, lang) and builds all production versions.
* `grunt build:pro` .. same as build, but only build the pro plugin version.
* `grunt build:free` .. same as build, but only build the free plugin version.
* `grunt build:campus` .. same as build, but only build the campus plugin version.


#### Set up grunt

##### 1. npm

First install node.js from: <http://nodejs.org/>  

    # Test it:
    $ npm -v
    
    # Install it system wide:
    $ npm install -g npm

##### 2. grunt

Install grunt by running this command in command line:

    # Install grunt:
    $ npm install -g grunt-cli

##### 3. Setup project

In command line switch to the `coursepress` plugin folder. Run this command to set up grunt for the coursepress plugin:

    # Install automation tools for coursepress:
    $ npm install
    
    # Test it:
    $ grunt test

##### 4. Install required tools

Same as 3: Run commands in the `coursepress` plugin folder:

    # Install composer:
    $ php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
    $ php composer-setup.php --filename=composer
    $ php -r "unlink('composer-setup.php');"
    
    # Install PHP Unit
    $ composer require --dev "phpunit/phpunit=4.8.*"
    
    # Install PHP Code Sniffer:
    $ php composer require --dev "squizlabs/php_codesniffer:2.*"
    
    # Install WP Coding Standards:
    $ git clone -b master https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.git vendor/wpcs
    $ vendor/bin/phpcs --config-set installed_paths ../../wpcs
    
    # Config git with your Name/Email
    $ git config user.email "<your email>"
    $ git config user.name "<your name>"


#### Specifying i18 tools location  

If `makepot` is not available in your system path you can set your i18 tools path in a private config.json file (excluded by .gitignore). Create config.json and add the following to it:  

    {
       "i18nToolsPath": "/path/to/i18n-tools/"
    }


#### Set up wordpress-develop for unit tests

If the command `grunt test` fails you possibly need to follow these steps and install the wordpress-develop repository to your server.

The repository must exist at one of those directories:

* `/srv/www/wptest/wordpress-develop`
* `/srv/www/wordpress-develop/trunk`    
* Or set the environment variable `WP_TESTS_DIR` to the directory

(See: tests/bootstrap.php line 12-21 for logic)

    $ mkdir /srv/www/wordpress-develop
    $ cd /srv/www/wordpress-develop
    $ svn co http://develop.svn.wordpress.org/trunk/
    $ cd trunk
    $ svn up


#### Unit testing notes

Introduction to unit testing in WordPress: http://codesymphony.co/writing-wordpress-plugin-unit-tests/
