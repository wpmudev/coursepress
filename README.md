# README #

CoursePress has three primary branches: coursepress/pro, coursepress/campus and coursepress/standard.

## CoursePress Pro (coursepress/pro)  

CoursePress Pro is the official premium plugin that lives on WPMU Dev and will ultimately always be merged back into **master**.

## CoursePress Campus (coursepress/campus)  

CoursePress Campus is the branch that is integrated with CampusPress.  It is just about identical to CoursePress Pro but strips out the MarketPress bundling, the CoursePress theme and removes paid courses features (all code that is implemented in CoursePress Pro).  The theme is included via a submodule instead ( [CoursePress Theme Repository](https://bitbucket.org/incsub/coursepress-theme/src) ).  

All development of CoursePress Campus needs to happen on the **coursepress/campus-dev** branch before merging it to the coursepress/campus branch (see releasing below).

## CoursePress (coursepress/standard)  

CoursePress is the free limited version that gets published to the WordPress plugin directory.

### Working with the branches

#### Cloning ####

When cloning CoursePress to your local repo please use the --recursive flag as part of your clone command:  

    git clone git@bitbucket.org:incsub/coursepress.git --recursive  

This will ensure you grab all the required submodules.  

**Note: ** If you already have a cloned repo, you will need to *init* the submodule.  You may need to do this in the branches.

    git submodule init --   
    git submodule update  

#### Checking out branches  

When checking out branches please make sure that you run 'git clean -dff' and then 'git submodule update'.  

This is required as CoursePress makes use of submodules, and different branches could be using different submodules. Checking out the branches without cleaning up and initialising will cause a bit of a mess in your staging environment.

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

CoursePress uses the latest (at the time) release package of MarketPress instead of a submodule as this caused several issues with bundling. Make sure that the latest package is always released and placed in includes/plugins and update the `public $mp_file` to reflect the latest plugin file.

#### Working with the CoursePress Theme

As of CoursePress Pro version 1.2.3.4 the theme is no longer included in the CoursePress repo, but included via a submodule. This is to work better with integration with CampusPress.  To make sure the latest theme updates are included in a release, make sure the submodules are properly updated.

##### Properly Update the Repo

**Note:**  
`git submodule update` or `git submodule update --recursive` only updates the local repo, but the associated commit ID does not match.  

You can verify this by running the following commands:  

    # This will show the 'actual' commit ID used within coursepress/pro branch
	git ls-files --stage | grep "themes/coursepress"
	
	# This will show the commit ID of the local repo (pushing to origin will not keep this ID!)  
	git submodule status  

To completely update the theme you will need to go to the themes/coursepress folder and do the following:  

    # Checkout 'master' because submodules often end up in a detached HEAD state  
	git checkout master  
	
	# Pull the update  
	git pull origin master  

Now when you run a `git status` you will see that the submodule has been updated.

    git status
	# ... modified:   themes/coursepress (new commits)
	
Now add, commit and push:  

    git add . -A  
	git commit  
	git push  

The theme should now be updated.

##### When someone else updated the theme  

When running `git submodule update` it makes sure that you have the latest submodule in your branch updated to the latest commit ID of the submodule. Perhaps even do this after fetching changes from the repo.  

### Releasing

## CoursePress Campus

CoursePress Campus is identical to CoursePress Pro, except that the theme is removed and the dashboard notification plugin is removed.  To release an update to CoursePress Campus, make sure  that coursepress/campus-dev is working properly, then checkout the coursepress/campus repo and merge in the development branch.  

    git checkout coursepress/campus  
	git merge coursepress/campus-dev   
	git commit    

It does not get released as a package, but is used by the CampusPress team for CampusPress integrations.

## CoursePress (wp.org version)

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