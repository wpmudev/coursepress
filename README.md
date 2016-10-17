# README

The **only** development branch for CoursePress 2 is `coursepress/2.0-dev`. This branch ultimately is responsible for creating the production branches that are finally published. Consider it to be a "super branch".

Production branches are automatically built, based on the dev branch. Any changes made to those other branches will be overwritten!

**Remember:** `coursepress/2.0-dev` is the ONLY branch that should be edited and forked!

**Notes:**

1. Only fork and submit pull-requests to the super branch `coursepress/2.0-dev`!
2. Never fork the production branches (below)!
3. Never publish/release the super branch `coursepress/2.0-dev` anywhere!

-----

# PRODUCTION BRANCHES

Production branches are always supposed to be stable and can be released/published at any time.


## CoursePress Pro (coursepress/2-pro)

CoursePress Pro is the official premium plugin that lives on WPMU DEV. Also this plugin is used to power the WP Academy site.

## CoursePress (coursepress/2-free)

CoursePress is the free limited version that gets published to the WordPress plugin directory.

## CoursePress Campus (coursepress/2-campus)

CoursePress Campus is the version that is used on Edublogs and CampusPress.


-----

# DEVELOPMENT

As mentioned above: Only directly edit the branch `coursepress/2.0-dev`. Other branches should be only updated via grunt tasks (see section "Automation" below).

Important: Do not let your IDE change the **source order** of the code. Fixing up formatting is fine, but moving code blocks around is not! It will confuse grunt and produce problems.

## Implement version differences

As mentioned, we will only update the super branch with all changes, even if those changes only relate to a specific product (like Premium version). There are two ways to add code that is specific to a single product only:

1. Put the code into a product directory (prefered).
2. Wrap code in product conditions.

### Product directories

The prefered way to implement different code is to move pro/campus code into the subfolders `/campus` and `/premium`. Code in the other directories is supposed to be core-plugin code (i.e. free plugin).

Any images/js/css code that is not for free plugin should also be moved into the product directory (e.g. pro-only jS goes to `premium/asset/js/src`).

### Product conditions

There are special comments in the `coursepress/2.0-dev` branch will make sure some code only end up on the pro plugin and some code only end up in the free plugin.

Those are:

```
#!php
/* start:pro */
echo 'This is only in coursepress/2-pro';
/* end:pro */

/* start:free */
echo 'This is only in coursepress/2-free';
/* end:free */

/* start:campus */
echo 'This is only in coursepress/2-campus';
/* end:campus */
```


## WP-Academy

Updating CoursePress in wp-academy is very simple. It takes two steps:
First update and push CoursePress 2-Pro to latest version.
Second clone and commit CoursePress 2-Pro in WP-Academy.

**First:**

* In the *coursepress/2.0-dev* branch:
* `grunt build:pro`
* Push the changes to bitbucket.

**Second:**

* In the *wpmu-dev/wp-academy* branch:
* `grunt coursepress`
* This command will automatically clone the CP 2-Pro branch (that we updated in first step) inside the wp-academy branch.
* Now you simply need to commit and push the changes. Easy!


## Working with the branches

### Cloning

CoursePress uses submodules, so use the `--recursive` flag if you clone from command line:

```
#!bash
$ git clone git@bitbucket.org:incsub/coursepress.git --recursive
```

If you already have a cloned repo, you will need to *init* the submodule.

```
#!bash
$ git submodule init --
$ git submodule update
```

### Agile workflow

Every bug fix/change must be made in a separate branch. Create a branch with name `agile/2.0-[id]-[short-desc]` and make all the changes and alpha-tests there. Once stable submit a pull request to the super branch `coursepress/2.0-dev`.

**Do not directly update the super branch, always use pull requests**

### JS and CSS files

Only edit/create javascript and css files inside the `/src` folders:

* `asset/js/src/*` for javascript.
* `asset/css/src/*` for css. Use .scss extension (SASS)!

Important: Those folders are scanned and processed when running grunt. Files in base of `asset/js/` and `asset/css/` are overwritten by grunt.

*Note:*
There is a hardcoded list of js and scss files that are monitored and compiled by grunt. If you add a new js or scss file then you need to edit `Gruntfile.js` and add the new file to the file list in `js_files_concat` or `css_files_compile`.

### Folder structure

Plugin code:

* `asset/` .. contains all images, js, css (scss) and font files.
> *Special folders inside asset:*
>  `asset/js/src/` (source js-files)
>  `asset/css/src/` (source scss-files)
>
>  *Do not edit the .css and .js files in root of `asset/js` and `asset/css`, they are overwritten by grunt!*
* `include/` .. All php code of the core (free version) goes here.
* `premium/` .. All Premium-Only code belongs here!
* `campus/` .. All CampusPress-Only code belongs here!
* `test/` .. contains PHP Unit Tests (run by grunt).

Files in these folders should not be modified directly:

* `language/` .. contains .pot translation files (generated by grunt, do not modify).
* `themes/` .. contains the CoursePress theme. This is a submodule that is maintained in a different reposotiry. Do not modify.
* `node_modules/` .. files needed by grunt (see "Set up grunt" below).
* `vendor/` .. files needed by grunt (see "Set up grunt" below).

Product folders `premium` and `campus` also contain the same subfolders as the core plugin: `asset` for js/css/images and `include` for php files.

**Naming convention for files/folders:**

* Prefix files that contian a php class with term "class-".
* Use lower-case only
> Example: *"class-templatetag.php" not "class-templateTag.php"*
* Use hyphen "-" instead of underscore "_"
> Example: *"class-core.php" not "class_core.php"*
* Try to keep all folder and file names in singular
> Example: *"include" not "includes"*

### Working with MarketPress in CoursePress

#### Preparing MarketPress for CoursePress Standard

No steps required here as CoursePress Standard now fetches MarketPress Lite directly from the WordPress.org directory when the user wants to enable it.

#### Preparing MarketPress for CoursePress Pro

* Download MarketPress from WPMU DEV Premium.
* Save the zip file as `/asset/file/marketpress-pro.zip` (replace existing file).


-----

# AUTOMATION

See notes below on how to correctly set up and use grunt. *This has changed since 1.x!*

Many tasks as well as basic quality control are done via grunt. Below is a list of supported tasks.

**Important**: Before making a pull-request to the super branch (2.0-dev) always run the task `grunt` - this ensures that all .php, .js and .css files are validated and existing unit tests pass. If an problems are reported then fix those problems before submitting the pull request.

### Grunt Task Runner

**ALWAYS** use Grunt to build CoursePress production branches. Use the following commands:

Category | Command | Action
---------| ------- | ------
Edit | `grunt watch` | Watch js and scss files, auto process them when changed. Same as running `grunt js` and `grunt css` after each js/css change.
Edit | `grunt js` | Manually validate and minify js files. Do this after you merge changes or switch to a different branch.
Edit | `grunt css` | Manually validate and compile scss files to css. Same as js: After merge/switch branch.
Test | `grunt test` | Runs the unit tests.
Test | `grunt php` | Validate WP Coding Standards in php files.
**Build** | `grunt` | Run all default tasks: php, test, js, css. **Run this task before submitting a pull-request**.
Build | `grunt lang` | Update the translations pot file.
Build | `grunt build` | Runs all default tasks + lang, builds all production versions.
Build | `grunt build:pro` | Same as build, but only build the pro plugin version.
Build | `grunt build:free` | Same as build, but only build the free plugin version.
Build | `grunt build:campus` | Same as build, but only build the campus plugin version.


### Update product versions

The example shows how to update the Pro-version, but the process for free nad campus versions are identical.

1. **Switch** to branch `coursepress/2.0-dev`
1. Run **grunt** command `$ grunt build:pro`
1. **Switch** to branch `coursepress/2-pro`
1. Do a git **pull**, *possibly some conflicts are identified!*
1. Do NOT resolve the conflicts, but **revert** the conflicting files to last version!!
> Grunt already committed the correct file version to git. The conflicts are irrelevant!
1. Now **commit** and **push** the changes to bitbucket

### Set up grunt

#### 1. npm

First install node.js from: <http://nodejs.org/>

```
#!bash
# Test it:
$ npm -v

# Install it system wide (optional but recommended):
$ npm install -g npm
```

#### 2. grunt

Install grunt by running this command in command line:

```
#!bash
# Install grunt:
$ npm install -g grunt-cli
```

#### 3. Setup project

In command line switch to the `coursepress` plugin folder. Run this command to set up grunt for the coursepress plugin:

```
#!bash
# Install automation tools for coursepress:
$ cd <path-to-wordpress>/wp-content/plugins/coursepress
$ npm install

# Test it:
$ grunt hello
```

#### 4. Install required tools

Same as 3: Run commands in the `coursepress` plugin folder:

```
#!bash
$ cd <path-to-wordpress>/wp-content/plugins/coursepress

# Install composer:
$ php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
$ php composer-setup.php --filename=composer
$ php -r "unlink('composer-setup.php');"

# Install PHP Unit
$ php composer require --dev "phpunit/phpunit=4.8.*"

# Install PHP Code Sniffer:
$ php composer require --dev "squizlabs/php_codesniffer:2.*"

# Install WP Coding Standards:
$ git clone -b master https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.git vendor/wpcs
$ vendor/bin/phpcs --config-set installed_paths ../../wpcs

# Config git with your Name/Email
$ git config user.email "<your email>"
$ git config user.name "<your name>"
```

### Unit Testing

Run the command `grunt phpunit` to run unit tests. If it fails you possibly need to follow these steps and install the test environment:

The repository must exist at one of those directories:

* `/tmp/wordpress-tests-lib/` (default location as setup by wp cli)
* `/srv/www/wordpress-develop/trunk/tests/phpunit/`  (VVV location)
* Or set the environment variable `WP_TESTS_DIR` to the directory

(See: tests/bootstrap.php line 12-21 for logic)

```
#!bash
# This one command will setup the test env:
$ bash test/install-wp-tests.sh
```


### Unit testing notes

Introduction to unit testing in WordPress: http://codesymphony.co/writing-wordpress-plugin-unit-tests/
