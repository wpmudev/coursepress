# README #

CoursePress has three primary development branches: coursepress/pro, coursepress/campus and coursepress/standard  

#### CoursePress Pro (coursepress/pro)  

CoursePress Pro is the official premium plugin that lives on WPMU Dev and will ultimately always be merged back into **master**.

#### CoursePress Campus (coursepress/campus)  

CoursePress Campus is the branch that is integrated with CampusPress/Edublogs.  It is just about identical to CoursePress Pro but strips out the MarketPress bundling and removes paid courses features (all code that is implemented in CoursePress Pro).  

#### CoursePress (coursepress/standard)  

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