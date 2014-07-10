<?php

do_shortcode('[course_signup page="signup" signup_title="" login_url="' . CoursePress::instance()->get_login_slug(true) . '"] logout_url="' . CoursePress::instance()->get_signup_slug(true) . '"]');