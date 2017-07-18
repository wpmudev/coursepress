<script type="text/template" id="coursepress-course-type-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'Pick name and type of course to create', 'cp' ); ?></h2>
	</div>
    <div class="cp-box-content">
        <div class="box-label-area">
            <h3 class="label"><?php _e( 'Course name and language', 'cp' ); ?></h3>
        </div>
        <div class="box-inner-content">
            <div class="cp-box">
                <label class="label"><?php _e( 'Course Name', 'cp' ); ?></label>
                <input type="text" class="widefat" name="post_title" value="{{post_title}}" />
            </div>

            <div class="cp-box">
                <label class="label"><?php _e( 'Course Slug', 'cp' ); ?></label>
                <input type="text" class="widefat" name="post_name" value="{{post_name}}" />
                <p class="description"><?php echo coursepress_get_url(); ?><span class="cp-slug">{{post_name}}</span>/</p>
            </div>

            <div class="cp-box">
                <label class="label"><?php _e( 'Language', 'cp' ); ?></label>
                <input type="text" class="widefat" name="course_language" value="{{course_language}}" />
            </div>
        </div>
    </div>

    <div class="cp-box-content">
        <div class="box-label-area">
            <h3 class="label"><?php _e( 'Course type', 'cp' ); ?></h3>
            <p class="description"><?php _e( 'Pick a type of course you want to create', 'cp' ); ?></p>
        </div>
        <div class="box-inner-content">
            <div class="cp-box">
                <label class="label"><?php _e( 'Pick course type or load example course', 'cp' ); ?></label>
                <ul class="cp-flex cp-input-group">
                    <li class="cp-div-flex {{'auto-moderated'===course_type?'active':''}}">
                        <label>
                            <input type="radio" name="meta_course_type" value="auto-moderated" {{_.checked('auto-modated', course_type)}} />
                            <?php _e( 'Auto-moderated', 'cp' ); ?>
                        </label>
                    </li>
                    <li class="cp-div-flex {{'manual'===course_type?'active':''}}">
                        <label>
                            <input type="radio" name="meta_course_type" value="manual" {{_.checked('manual', course_type)}} />
                            <?php _e( 'Manual moderation', 'cp' ); ?>
                        </label>
                    </li>
                    <li class="cp-div-flex {{'sample_course'===course_type?'active':''}}">
                        <label>
                            <input type="radio" name="meta_course_type" value="sample" value="sample_course" {{_.checked('sample', course_type)}} />
                            <?php _e( 'Example course', 'cp' ); ?>
                        </label>
                    </li>
                </ul>
            </div>

            <div class="cp-box cp-course-type {{'auto-moderated'===course_type?'active':'inactive'}}" id="type-auto-moderated">
                <div class="cp-alert cp-alert-info">
                    <p><?php _e( 'All grading is done manually, any number of students can enroll in this course at any time. Similar to Envato & Treehouse courses. Instructors can participate in discussion.', 'cp' ); ?></p>
                    <p><?php _e( '(These settings can be changed at any time).', 'cp' ); ?></p>
                </div>
            </div>

            <div class="cp-box cp-sep cp-course-type {{'manual'===course_type?'active':'inactive'}}" id="type-manual">
                <div class="cp-box">
                    <label class="label"><?php _e( 'Class Size', 'cp' ); ?></label>
                    <p><?php _e( 'Number of students', 'cp' ); ?> <input type="number" name="meta_class_size" class="input-inline" value="{{class_size}}" /></p>
                </div>

                <label class="label"><?php _e( 'Course Availability', 'cp' ); ?></label>
                <div class="cp-flex">
                    <div class="cp-div-flex cp-pad-right">
                        <span class="course-title-tag"><?php _e( 'Start Date', 'cp' ); ?></span>
                        <input type="text" name="meta_course_start_date" value="{{course_start_date}}" />
                    </div>
                    <div class="cp-div-flex cp-pad-left">
                        <span class="course-title-tag"><?php _e( 'End Date', 'cp' ); ?></span>
                        <input type="text" name="meta_course_end_date" value="{{course_end_date}}" />
                    </div>
                </div>
                <br />
                <label class="label"><?php _e( 'Enrollment Date', 'cp' ); ?></label>
                <div class="cp-flex">
                    <div class="cp-div-flex cp-pad-right">
                        <span class="course-title-tag"><?php _e( 'Start Date', 'cp' ); ?></span>
                        <input type="text" name="meta_enrollment_start_date" value="{{enrollment_start_date}}" />
                    </div>
                    <div class="cp-div-flex cp-pad-left">
                        <span class="course-title-tag"><?php _e( 'End Date', 'cp' ); ?></span>
                        <input type="text" name="meta_enrollment_end_date" value="{{enrollment_end_date}}" />
                    </div>
                </div>
            </div>

            <div class="cp-box cp-course-type {{'sample_course'===course_type?'active':'inactive'}}" id="type-sample">

            </div>

            <div class="cp-box cp-toggle-box cp-sep">
                <label>
                    <input type="checkbox" name="meta_allow_discussion" {{_.checked(true, allow_discussion)}} class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php _e( 'Enable course discussion', 'cp' ); ?></span>
                </label>
                <p class="description"><?php _e( 'Creates discussion area where users can post questions and get help from instructors, facilitators and other students', 'cp' ); ?></p>
            </div>

            <div class="cp-box cp-toggle-box cp-sep">
                <label>
                    <input type="checkbox" name="meta_allow_workbook" {{_.checked(true, allow_workbook)}} class="cp-toggle-input" autocomplete="off" /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php _e( 'Enable workbook', 'cp' ); ?></span>
                </label>
                <p class="description"><?php _e( 'Users can access their workbook which will show their progress/scores for the course.', 'cp' ); ?></p>
            </div>

            <div class="cp-box cp-toggle-box">
                <label>
                    <input type="checkbox" name="meta_payment_paid_course" {{_.checked( true, payment_paid_course)}} class="cp-toggle-input" autocomplejte="off" /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php _e( 'This is a paid course', 'cp' ); ?></span>
                </label>
                <p class="description"><?php _e( 'Will allow you to set-up payment gateway/options.', 'cp' ); ?></p>
            </div>

            <div class="cp-box cp-box-off{{  true == payment_paid_course? '':' hidden' }}">
                <div class="payment-message">
                    <h3><?php _e( 'Sell your courses online with MarketPress.', 'cp' ); ?></h3>
                    <p><?php _e( 'To start selling your course, please install and activate MarketPress or contact your administrator to enable MarketPress for your site.', 'cp' ); ?></p>
                    <p><?php _e( 'Other supported plugins: WooCommerce', 'cp' ); ?></p>
                </div>
            </div>

            <div class="cp-box cp-box-marketpress{{  true == payment_paid_course? '':' hidden' }}">
                <h3><?php _e( 'MarketPress Product Settings', 'cp' ); ?></h3>
                <p class="description"><?php _e( 'Your course will be a new product in MarketPress. Enter your course\'s payment settings below.', 'cp' ); ?></p>
            </div>

            <div class="cp-box cp-box-marketpress{{  true == payment_paid_course? '':' hidden' }}">
                <label class="label"> <?php esc_html_e( 'Full Price', 'cp' ); ?></label>
                <input type="text" name="meta_mp_product_price" value="{{mp_product_price}}" />
            </div>

            <div class="cp-box cp-box-marketpress{{  true == payment_paid_course? '':' hidden' }}">
                <label class="label"><?php esc_html_e( 'Sale Price', 'cp' ); ?></label>
                <input type="text" name="meta_mp_product_sale_price" value="{{mp_product_sale_price}}" />
            </div>

            <div class="cp-box cp-box-marketpress{{  true == payment_paid_course? '':' hidden' }}">
                <label>
                    <input type="checkbox" name="meta_mp_sale_price_enabled" {{_.checked(true, mp_sale_price_enabled)}} class="cp-toggle-input" autocomplejte="off" /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php esc_html_e( 'Enable Sale Price', 'cp' ); ?></span>
                </label>
            </div>

            <div class="cp-box cp-box-marketpress{{  true == payment_paid_course? '':' hidden' }}">
                <label class="label"><?php esc_html_e( 'Course SKU:', 'cp' ); ?></label>
                <input type="text" name="meta_mp_sku" placeholder="{{mp_sku_placeholder}}" value="{{mp_sku}}" />
            </div>

            <div class="cp-box cp-box-marketpress{{  true == payment_paid_course? '':' hidden' }}">
                <label>
                    <input type="checkbox" name="meta_mp_sale_price_enabled" {{_.checked(true, mp_auto_sku)}} class="cp-toggle-input" autocomplejte="off" /> <span class="cp-toggle-btn"></span>
                    <span class="label"><?php esc_html_e( 'Automatically generate Stock Keeping Units (SKUs)', 'cp' ); ?></span>
                </label>
            </div>
        </div>
    </div>

    <?php
	/**
	 * Trigger when all course type fields are printed.
	 *
	 * @since 3.0
	 * @param int $course_id Current course ID created or edited.
	 */
	do_action( 'coursepress_course_setup-course-type', $course_id );
	?>
</script>
