**CoursePress 3.0 Readme:**

https://github.com/wpmudev/coursepress/src/27834d5ef2b02fbf7c3bb8c40353d56666f14e80/?at=development

**CoursePress 2.0 Readme:**

https://github.com/wpmudev/coursepress/src/493fe75d6a198f29439848aa837d32cbd3905b18?at=coursepress/2.0-dev

-----

**This is outdated 1.3 documentation:**


# README #

The **core** development branch for CoursePress 1.x is `coursepress/base`. This branch ultimately is responsible for creating the `coursepress/pro` and `coursepress/standard` branches. Consider it to be a "super branch".  

`coursepress/base` is the **ONLY** branch that should be edited with bug fixes and changes for CoursePress 1.x.  Changes will automatically be built for `coursepress/pro` and `coursepress/standard` using **grunt** tasks: `grunt build:dev` and `grunt build:wporg` respectively. Or `grunt buildAll` to build both branches. This will not be automatically pushed to this repo. Test locally first!

**Note to support: ** Do NOT fork `coursepress/pro` or `coursepress/standard`. Pull requests need to be made to the `coursepress/base` branch.

**Note to devs: ** Please do not release the `coursepress/base` branch. It will run stand alone for testing and development, but uses `CoursePress Base` as the plugin name and a number of other variables or strings that are required to build the two release versions.

# DEVELOPMENT BRANCHES   

CoursePress has a number of development branches: coursepress/base, coursepress/campus-dev and coursepress/2.0-dev  

## CoursePress Pro (coursepress/base) - CoursePress 1.x    

This branch is the "super" branch for CoursePress 1.x. All development and bug fixes should happen in this branch (**NOT** in coursepress/pro or coursepress/standard).  

This bit is *very very important*: **DO NOT** let your IDE change the source order of your code, fixing up formatting is fine, but moving code blocks around is not. Here is why...

The `coursepress/base` branch rely heavily on Grunt to produce the development branches: `coursepress/pro` and `coursepress/standard`.

Special comments in the base branch will make sure some code only end up on the /pro branch and some code only end up in the /standard branch.

E.g.  

    //<wpmudev.plugin.pro_only>  
    echo "This is only in coursepress/pro";  
    //</wpmudev.plugin.pro_only>  
  
    //<wpmudev.plugin.free_only>  
    echo "This is only in coursepress/standard";  
    //</wpmudev.plugin.free_only>  

**NEVER** under any circumstances package and release `coursepress/base` on Premium or WordPress.org.

## CoursePress Campus (coursepress/campus-dev) - CoursePress for Edublogs/CampusPress    

This branch is not usually touched by the CoursePress developers unless `coursepress/pro` production branch changes.  Make sure that `coursepress/pro` gets merged into `coursepress/campus-dev`, from this point on, the campus team takes over and do what they need to do.

## CoursePress Pro 2.0 (coursepress/2.0-dev)  

This is the development branch for **CoursePress 2.0** (still unreleased at this stage). At the moment development is primarily happening as part of the **WP Academy** project in the `wpmu-dev` repo.  Changes are then manually merged back to `coursepress/2.0-dev` here.  

Upon completion of **WP Academy** the `coursepress/2.0-dev` branch will receive a structure similar to that of `coursepress/base`.  It requires a "super branch" that will be responsible for generation both a Pro and Standard version of CoursePress 2.0 by using Grunt tasks (these do not exist yet).

# RELEASE BRANCHES   

## CoursePress Pro (coursepress/pro)  

CoursePress Pro is the official premium plugin that lives on WPMU Dev.

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

# RELEASING #

#### Versioning  

Before running any Grunt tasks to build the releases, please update the CoursePress version in the Plugin Header and update the variable, `public $version = '1.2.5.7';`.  *(For 2.0 only header modification will be required)*  

You only need to perform this update in the `coursepress/base` branch as the release branches will be generated from this branch with Grunt.

#### Changelog  

Please update changelog.txt in `coursepress/base` only.  Make sure all entries are below `== Changelog ==` and never remove this line.  It is required by Grunt to update `readme.txt` for release on WordPress.org.  

readme.txt has a special tag `<%= wpmudev.plugin.changelog %>` that will be replaced by the changelog in changelog.txt.  

#### Bundling MarketPress  

##### Preparing MarketPress for CoursePress Standard  

No steps required here as CoursePress Standard now fetches MarketPress Lite directly from the WordPress.org directory when the user wants to enable it.  

##### Preparing MarketPress for CoursePress Pro

Its now easier to bundle MarketPress with CoursePress.  

* Download MarketPress from WPMU DEV Premium.  
* Place the zip file in `includes/plugins` and remove the old zip.  
* Update the zip file name in `coursepress.php` variable. e.g. `$this->mp_file = '128762_marketpress-ecommerce-3.0.0.2.zip';`  


#### Grunt Task Runner  

**ALWAYS** use Grunt to build CoursePress release branches. Use the following commands:  

`grunt buildAll`  - This builds both the `coursepress/pro` and `coursepress/standard` branches from `coursepress/base`.  

`grunt build:dev` - This builds `coursepress/pro` branch for release on WPMU DEV.  

`grunt build:wporg` - This builds `coursepress/standard` branch for release on WordPress.org. 

**Primary Developer NOTE: ** Please see the Gruntfile.js, lines 220 - 224.  Test and confirm automatic generation of POT files then remove all comments on those lines. Getting varied results at the moment.  

**Note:**  It assumes that i18 tools are installed, see 'Other' below)  

To use `grunt` you will need to have NPM (Node.js Package Manager) installed. (See 'Other' below)

#### Packaging release branches for release

##### CoursePress Pro  

The recommended way to package the `coursepress/pro` branch is to use the packaging script by Aaron, found here: https://gist.github.com/uglyrobot/e872d1a9efc122b6bae2 OR by using `git-archive-all --force-submodules`.  

This will respect the .gitattributes filter and produce a zip file that can be uploaded to the WPMU DEV Project.  

In both cases, please make sure that you tag the `coursepress/base` branch with the version.  

##### WordPress.org  

The `coursepress/standard` branch is created for release on WordPress.org. You will need to copy the folders (only visible ones) to the `trunk` folder of your local SVN checkout of the WordPress.org hosted repo.

Then follow the recommended WordPress.org guides for tagging the release with the current version number. See <https://wordpress.org/plugins/about/svn/>   

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
