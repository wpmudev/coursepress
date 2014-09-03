# README #

CoursePress has two primary development branches: coursepress/pro and coursepress/standard  

** CoursePress Pro (coursepress/pro) **  

CoursePress Pro is the official premium plugin that lives on WPMU Dev.

** CoursePress (coursepress/standard) **  

CoursePress is the free limited version that gets published to the WordPress plugin directory.

### Working with the branches ###

#### Cloning ####

When cloning CoursePress to your local repo please use the --recursive flag as part of your clone command:  

    git clone git@bitbucket.org:incsub/coursepress.git --recursive  

This will ensure you grab all the required submodules.

#### Checking out branches ####  

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
