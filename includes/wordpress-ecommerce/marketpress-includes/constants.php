<?php

//allows products with 0 price to be imported - defaults to false
if ( !defined('MP_IMPORT_ALLOW_NO_PRICE') ) define('MP_IMPORT_ALLOW_NO_PRICE', false);

//set the root blog in multisite installs - set to the blog id of your choice
if ( !defined('MP_ROOT_BLOG') ) define('MP_ROOT_BLOG', false);

//hide the option to login when to checking out
if ( !defined('MP_HIDE_LOGIN_OPTION') ) define('MP_HIDE_LOGIN_OPTION', true);

//hide the shipping order status
if ( !defined('MP_HIDE_ORDERSTATUS_SHIPPING') ) define('MP_HIDE_ORDERSTATUS_SHIPPING', true);

//is this mp lite version?
if ( !defined('MP_LITE') ) define('MP_LITE', true);

//hide the MarketPress menus
if ( !defined('MP_HIDE_MENUS') ) define('MP_HIDE_MENUS', true);

//remove WPMU DEV branding
if ( !defined('WPMUDEV_REMOVE_BRANDING') ) define('WPMUDEV_REMOVE_BRANDING', true);

//if your getting out of memory errors with large downloads, you can use a redirect instead, it's not so secure though
if ( !defined('MP_LARGE_DOWNLOADS') ) define('MP_LARGE_DOWNLOADS', false);