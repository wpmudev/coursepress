# README #

**NOTE: This is new**

The **core** development branch for CoursePress 1.x is `coursepress/base`. This branch ultimately is responsible for creating the `coursepress/pro` and `coursepress/standard` branches. Consider it to be a "super branch".  

`coursepress/base` is the **ONLY** file that should be edited with bug fixes and changes for CoursePress 1.x.  Changes will automatically be built for `coursepress/pro` and `coursepress/standard` using **grunt** tasks: `grunt build:dev` and `grunt build:wporg` respectively. Or `grunt buildAll` to build both branches. This will not be automatically pushed to this repo. Test locally first!

**Note to support: ** Do NOT fork `coursepress/pro` or `coursepress/standard`. Pull requests need to be made to the `coursepress/base` branch.

**Note to devs: ** Please do not release the `coursepress/base` branch. It will run stand alone for testing and development, but uses `CoursePress Base` as the plugin name and a number of other variables or strings that are required to build the two release versions.

CoursePress has three primary development branches: coursepress/pro, coursepress/campus and coursepress/standard  

## CoursePress Pro (coursepress/pro)  

CoursePress Pro is the official premium plugin that lives on WPMU Dev and will ultimately always be merged back into **master**.

## CoursePress Campus (coursepress/campus)  

CoursePress Campus is the branch that is integrated with CampusPress/Edublogs.  It is just about identical to CoursePress Pro but strips out the MarketPress bundling and removes paid courses features (all code that is implemented in CoursePress Pro).  

## CoursePress (coursepress/standard)  

CoursePress is the free limited version that gets published to the WordPress plugin directory.

### Working with the branches

#### Cloning ####

When cloning CoursePress to your local repo please use the --recursive flag as part of your clone command:  

    git clone git@bitbucket.org:incsub/coursepress.git --recursive  

This will ensure you grab all the required submodules.  

**Note: ** If you already have a cloned repo, you will need to *init* the submodule.  

    git submodule init --   
    git submodule update  

#### Checking out branches  

When checking out the *coursepress/pro* or *coursepress/standard* branches please make sure that you run 'git clean -dff' and then 'git submodule update'.  

This is required as CoursePress Pro uses MarketPress as a submodule and CoursePress uses a version of MarketPress Lite and checking out the branches without cleaning up and initialising will cause a bit of a mess in your staging environment.

Examples:

    git checkout master  
    git clean -dff  
    git submodule update  

or  

    git checkout coursepress/pro  
    git clean -dff  
    git submodule update  

Please note the double -f flag in the clean command. This is required to clean directories that contain a submodule repo.  

#### Working with MarketPress in coursepress/pro  

##### Properly Update the Repo

**Note:**  
`git submodule update` or `git submodule update --recursive` only updates the local repo, but the associated commit ID does not match.  

You can verify this by running the following commands:  

    # This will show the 'actual' commit ID used within coursepress/pro branch
	git ls-files --stage | grep "includes/marketpress"
	
	# This will show the commit ID of the local repo (pushing to origin will not keep this ID!)  
	git submodule status  

To completely update MarketPress in coursepress/pro you will need to go to the submodule folder and do the following:  

    # Checkout 'master' because submodules often end up in a detached HEAD state  
	git checkout master  
	
	# Pull the update  
	git pull origin master  

Now when you run a `git status` in the coursepress/pro branch you will see that the submodule has been updated.

    git status
	# ... modified:   includes/marketpress (new commits)

##### Preparing MarketPress for CoursePress Pro

**Copy** (not move) `includes/marketpress/marketpress.php` to CoursePress root folder.  

Edit the new file to make it CoursePress friendly:  

    // Change name and version 
	/*  
	Plugin Name: MarketPress (CoursePress Pro Bundle)  
	Version: 2.9.5.3   

Remove the dashboard as CoursePress is now responsible.

Find includes/requires and update accordingly:  

    // localization()
	$lang_dir = dirname(plugin_basename($this->plugin_file)) . 'includes/marketpress/marketpress-includes/languages/';
	
	// init_vars()  
	$this->plugin_dir = plugin_dir_path(__FILE__) . 'includes/marketpress/marketpress-includes/';
	$this->plugin_url = plugin_dir_url(__FILE__) . 'includes/marketpress/marketpress-includes/';

With these changes in place it should be good to go.

    git status
	# ... modified:   includes/marketpress (new commits)
	# ... modified:   marketpress.php

Now add, commit and push:  

    git add . -A  
	git commit  
	git push  

MarketPress is now updated for CoursePress Pro.

##### Someone else updated MarketPress Bundle  

This is where `git submodule update` fits in. It makes sure that you have the latest submodule in your branch updated to the commit ID of the submodule in your branch.  


### Releasing

#### Grunt Task Runner (automating)  

**NOTE: This section needs updating. Additional grunt tasks now exist to make deployment easier.**

You can use `grunt` to run a few automation tasks:  

* i18n : Creates a cp-default.pot file and compiles it into a .mo file. (Note, it assumes that i18 tools are installed, see 'Other' below)  

To use `grunt` you will need to have NPM (Node.js Package Manager) installed. (See 'Other' below)

Grunt uses node.js modules. To install the modules to node_modules (ignored by .gitignore) run `npm install` in the coursepress/ folder. This pulls the configuration from package.json. You don't need to do this too often.  

    npm install

Now that its all setup and good to go, from now on you can just run `grunt` whenever you want to run the automation tasks. Preferably just before you release.  

    grunt  

For reference: Grunt's configuration is kept in Gruntfile.js.

#### CoursePress Campus

Its identical to CoursePress Pro, but be sure to remove marketpress.php, includes/marketpress/* and includes/extra/dashboard and commit the branch.  

It does not get released, but is used by the CampusPress team for CampusPress integrations.

#### CoursePress (wp.org version)

CoursePress is identical to CoursePress Pro, but the following changes need to be made:  

* Copy/Merge ALL code from Pro to Standard (**dont remove readme.txt**)
* Remove /includes/marketpress/  
* Remove ./marketpress.php  
* Add MarketPress Lite to /includes/wordpress-ecommerce
* Copy MarketPress Lite's marketpress.php to ./a-marketpress.php  
* Update paths in ./a-marketpress.php  
* Change plugin name in ./a-marketpress.php  
* Change plugin name in ./coursepress.php  
* Remove WPMUDev Dashboard notifications and require statements  
* Change return value of is_pro() to false in ./includes/classes/class.coursepress-capabilities.php

### Other

#### Installing NPM and Grunt

The easiest way to get `npm` is to install Node.js from: <http://nodejs.org/>  

Once Node.js is installed you can check that you have `npm` and update it to the latest version.  

    npm -v  
	npm install -g npm

Next step is to install grunt-cli via npm:  

    npm install -g grunt-cli  

#### Specifying i18 tools location  

If `makepot` is not available in your system path you can set your i18 tools path in a private config.json file (excluded by .gitignore). Create config.json and add the following to it:  

	{
	    "i18nToolsPath": "/path/to/i18n-tools/"
	}