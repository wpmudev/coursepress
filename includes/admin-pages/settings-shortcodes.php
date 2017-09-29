<div id="poststuff" class="metabox-holder m-settings cp-shortcode-box cp-wrap">
	<form action='' method='post'>

		<div class="postbox">
			<h3 class='hndle cp-shortcode-heading'><span><?php _e( 'Shortcodes', 'coursepress' ); ?></span></h3>

			<div class="inside">
				<p><?php _e( 'Shortcodes allow you to include dynamic content in posts and pages on your site. Simply type or paste them into your post or page content where you would like them to appear. Optional attributes can be added in a format like <em>[shortcode attr1="value" attr2="value"]</em>. ', 'coursepress' ); ?></p>
				<table class="form-table">
					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Instructors List', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_instructors]</span><br/>
							<span class=""><?php _e( 'Display a list or count of Instructors ( gravatar, name and link to profile page )', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span>
									– <?php _e( 'ID of the course instructors are assign to ( required if use it outside of a loop )', 'coursepress' ); ?>
								</li>
								<li><span>style</span>
									– <?php _e( 'How to display the instructors. Options: <em>block</em> (default), <em>list</em>, <em>list-flat</em>, <em>count</em> (counts instructors for the course).', 'coursepress' ); ?>
								</li>
								<li><span>label</span>
									– <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'coursepress' ); ?>
								</li>
								<li><span>label_plural</span>
									– <?php _e( 'Plural if more than one instructor. Default: Instructors', 'coursepress' ); ?>
								</li>
								<li><span>label_delimeter</span>
									– <?php _e( 'Symbol to put after label. Default is colon (<strong>:</strong>)', 'coursepress' ); ?>
								</li>
								<li><span>label_tag</span>
									– <?php _e( 'HTML tag to wrap the label (without brackets, e.g. <em>h3</em>). Default: empty', 'coursepress' ); ?>
								</li>
								<li><span>link_text</span>
									– <?php _e( 'Text to click to link to full profiles. Default: "View Full Profile".', 'coursepress' ); ?>
								</li>
								<li><span>show_label</span>
									– <?php _e( 'Show the label. Options: <em>yes</em>, <em>no</em>.', 'coursepress' ); ?></li>
								<li><span>summary_length</span>
									– <?php _e( 'Length of instructor bio to show when style is "blocl". Default: 50', 'coursepress' ); ?>
								</li>
								<li><span>list_separator</span>
									– <?php _e( 'Symbol to use to separate instructors when styl is "list" or "list-flat". Default: comma (,)', 'coursepress' ); ?>
								</li>
								<li><span>avatar_size</span>
									– <?php _e( 'Pixel size of the avatars when viewing in block mode. Default: 80', 'coursepress' ); ?>
								</li>
								<li><span>default_avatar</span>
									– <?php _e( 'URL to a default image if the user avatar cannot be found.', 'coursepress' ); ?>
								</li>
								<li><span>show_divider</span>
									– <?php _e( 'Put a divider between instructor profiles when style is "block".', 'coursepress' ); ?>
								</li>
								<li><span>link_all</span>
									– <?php _e( 'Make the entire instructor profile a link to the full profile.', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes to use for further styling.', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_instructors]<br/>[course_instructors course_id="5"]<br/>[course_instructors
								style="list"]</code>
							<span class="description"></span>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Instructor Avatar', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_instructor_avatar]</span><br/>
							<span class=""><?php _e( 'Display an instructor’s avatar.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>instructor_id</span> – <?php _e( 'The user id of the instructor.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>thumb_size</span>
									– <?php _e( 'Size of avatar thumbnail. Default: 80', 'coursepress' ); ?></li>
								<li><span>class</span>
									– <?php _e( 'CSS class to use for the avatar. Plugin Default: small-circle-profile-image', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_instructor_avatar instructor_id="1"]</code>
						</td>
					</tr>

					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Instructor Profile URL', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[instructor_profile_url]</span><br/>
							<span class=""><?php _e( 'Returns the URL to the instructor profile.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>instructor_id</span> – <?php _e( 'The user id of the instructor.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[instructor_profile_url instructor_id="1"]</code>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Details', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course]</span><br/>
							<span class=""><?php _e( 'This shortcode allows you to display details about your course. <br /><strong>Note:</strong> All the same information can be retrieved by using the specific course shortcodes following.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
								<li>
									<span>show</span>
									– <?php _e( 'All the fields you would like to show. Default: summary', 'coursepress' ); ?>
									<p class="description"><strong><?php _e( 'Available fields:', 'coursepress' ) ?></strong>
										title, summary, description, start, end, dates, enrollment_start,
										enrollment_end, enrollment_dates, enrollment_type, class_size, cost, language,
										instructors, image, video, media, button, action_links, calendar</p>
								</li>
							</ul>


							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>show_title</span>
									– <?php _e( 'yes | no - Required when showing the "title" field.', 'coursepress' ); ?></li>
								<li><span>date_format</span>
									– <?php _e( 'PHP style date format. Default: WordPress setting.', 'coursepress' ); ?></li>
								<li><span>label_tag</span>
									– <?php _e( 'HTML tag (without brackets) to use for the individual labels. Default: strong', 'coursepress' ); ?>
								</li>
								<li><span>label_delimeter</span>
									– <?php _e( 'Symbol to use after the label. Default is colon (:)', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course show="title,summary,cost,button" course_id="5"]</code>
						</td>
					</tr>

					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Title', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_title]</span><br/>
							<span class=""><?php _e( 'Displays the course title.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>title_tag</span>
									– <?php _e( 'The HTML tag (without brackets) to use for the title. Default: h3', 'coursepress' ); ?>
								</li>
								<li><span>link</span>
									– <?php _e( 'Should the title link to the course?  Accepts "yes" or "no". Default: no', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>


							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_title course_id="4"]<br/>[course_title]</code>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Summary', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_summary]</span><br/>
							<span class=""><?php _e( 'Displays the course summary/excerpt.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>


							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>length</span>
									– <?php _e( 'Text length of the summary. Default: empty (uses WordPress excerpt length)', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>


							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_summary course_id="4"]<br/>[course_summary]</code>
						</td>
					</tr>

					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Description', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_description]</span><br/>
							<span class=""><?php _e( 'Displays the longer course description (post content).', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>


							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>


							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_description course_id="4"]<br/>[course_description]</code>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Start Date', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_start]</span><br/>
							<span class=""><?php _e( 'Shows the course start date.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>


							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>date_format</span>
									– <?php _e( 'PHP style date format. Default: WordPress setting.', 'coursepress' ); ?></li>
								<li><span>label</span>
									– <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'coursepress' ); ?>
								</li>
								<li><span>label_tag</span>
									– <?php _e( 'HTML tag (without brackets) to use for the individual labels. Default: strong', 'coursepress' ); ?>
								</li>
								<li><span>label_delimeter</span>
									– <?php _e( 'Symbol to use after the label. Default is colon (:)', 'coursepress' ); ?></li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>


							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_start]<br/>[course_start label="Awesomeness begins on" label_tag="h3"]</code>
						</td>
					</tr>

					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course End Date', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_end]</span><br/>
							<span class=""><?php _e( 'Shows the course end date.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>


							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>date_format</span>
									– <?php _e( 'PHP style date format. Default: WordPress setting.', 'coursepress' ); ?></li>
								<li><span>label</span>
									– <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'coursepress' ); ?>
								</li>
								<li><span>label_tag</span>
									– <?php _e( 'HTML tag (without brackets) to use for the individual labels. Default: strong', 'coursepress' ); ?>
								</li>
								<li><span>label_delimeter</span>
									– <?php _e( 'Symbol to use after the label. Default is colon (:)', 'coursepress' ); ?></li>
								<li><span>no_date_text</span>
									– <?php _e( 'Text to display if the course has no end date. Default: No End Date', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_end]<br/>[course_end label="The End." label_tag="h3" course_id="5"]</code>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Dates', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_dates]</span><br/>
							<span class=""><?php _e( 'Displays the course start and end date range. Typically as [course_start] - [course_end].', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>date_format</span>
									– <?php _e( 'PHP style date format. Default: WordPress setting.', 'coursepress' ); ?></li>
								<li><span>label</span>
									– <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'coursepress' ); ?>
								</li>
								<li><span>label_tag</span>
									– <?php _e( 'HTML tag (without brackets) to use for the individual labels. Default: strong', 'coursepress' ); ?>
								</li>
								<li><span>label_delimeter</span>
									– <?php _e( 'Symbol to use after the label. Default is colon (:)', 'coursepress' ); ?></li>
								<li><span>no_date_text</span>
									– <?php _e( 'Text to display if the course has no end date. Default: No End Date', 'coursepress' ); ?>
								</li>
								<li><span>alt_display_text</span>
									– <?php _e( 'Alternate display when there is no end date. Default: Open-ended', 'coursepress' ); ?>
								</li>
								<li><span>show_alt_display</span>
									– <?php _e( 'If set to "yes" use the alt_display_text. If set to "no" use the "no_date_text". Default: no', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_dates course_id="42"]<br/>[course_dates course_id="42" show_alt_display="yes"
								alt_display_text="Learn Anytime!"]</code>
						</td>
					</tr>

					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Enrollment Start', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_enrollment_start]</span><br/>
							<span class=""><?php _e( 'Displays the course enrollment start date.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>


							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>date_format</span>
									– <?php _e( 'PHP style date format. Default: WordPress setting.', 'coursepress' ); ?></li>
								<li><span>label</span>
									– <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'coursepress' ); ?>
								</li>
								<li><span>label_tag</span>
									– <?php _e( 'HTML tag (without brackets) to use for the individual labels. Default: strong', 'coursepress' ); ?>
								</li>
								<li><span>label_delimeter</span>
									– <?php _e( 'Symbol to use after the label. Default is colon (:)', 'coursepress' ); ?></li>
								<li><span>no_date_text</span>
									– <?php _e( 'Text to display if the course has no defined enrollment start date. Default: Enroll Anytime', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>


							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_enrollment_start]<br/>[course_enrollment_start label="Signup from"
								label_tag="em"]</code>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Enrollment End', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_enrollment_end]</span><br/>
							<span class=""><?php _e( 'Shows the course enrollment end date.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>


							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>date_format</span>
									– <?php _e( 'PHP style date format. Default: WordPress setting.', 'coursepress' ); ?></li>
								<li><span>label</span>
									– <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'coursepress' ); ?>
								</li>
								<li><span>label_tag</span>
									– <?php _e( 'HTML tag (without brackets) to use for the individual labels. Default: strong', 'coursepress' ); ?>
								</li>
								<li><span>label_delimeter</span>
									– <?php _e( 'Symbol to use after the label. Default is colon (:)', 'coursepress' ); ?></li>
								<li><span>no_date_text</span>
									– <?php _e( 'Text to display if there is no enrollment end date. Default: Enroll Anytime', 'coursepress' ); ?>
								</li>
								<li><span>show_all_dates</span>
									– <?php _e( 'If "yes" it will display the no_date_text even if there is no date. If "no" then nothing will be displayed. Default: no', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_end]<br/>[course_end label="End" label_delimeter="-"]</code>
						</td>
					</tr>

					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Enrollment Dates', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_enrollment_dates]</span><br/>
							<span class=""><?php _e( 'Displays the course enrollment start and end date range. Typically as [course_enrollment_start] - [course_enrollment_end].', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>date_format</span>
									– <?php _e( 'PHP style date format. Default: WordPress setting.', 'coursepress' ); ?></li>
								<li><span>label</span>
									– <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'coursepress' ); ?>
								</li>
								<li><span>label_tag</span>
									– <?php _e( 'HTML tag (without brackets) to use for the individual labels. Default: strong', 'coursepress' ); ?>
								</li>
								<li><span>label_delimeter</span>
									– <?php _e( 'Symbol to use after the label. Default is colon (:)', 'coursepress' ); ?></li>
								<li><span>no_date_text</span>
									– <?php _e( 'Text to display if there is no enrollment start or end dates. Default: Enroll Anytime', 'coursepress' ); ?>
								</li>
								<li><span>alt_display_text</span>
									– <?php _e( 'Alternate display when there is no enrollment start or end dates. Default: Open-ended', 'coursepress' ); ?>
								</li>
								<li><span>show_alt_display</span>
									– <?php _e( 'If set to "yes" use the alt_display_text. If set to "no" use the "no_date_text". Default: no', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_enrollment_dates]<br/>[course_enrollment_dates no_date_text="No better time
								than now!"]</code>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Coure Enrollment Type', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_enrollment_type]</span><br/>
							<span class=""><?php _e( 'Shows the type of enrollment (manual, prerequisite, passcode or anyone).', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>label</span>
									– <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'coursepress' ); ?>
								</li>
								<li><span>label_tag</span>
									– <?php _e( 'HTML tag (without brackets) to use for the individual labels. Default: strong', 'coursepress' ); ?>
								</li>
								<li><span>label_delimeter</span>
									– <?php _e( 'Symbol to use after the label. Default is colon (:)', 'coursepress' ); ?></li>
								<li><span>manual_text</span>
									– <?php _e( 'Text to display for manual enrollments. Default: Students are added by instructors.', 'coursepress' ); ?>
								</li>
								<li><span>prerequisite_text</span>
									– <?php _e( 'Text to display when there is a prerequisite. Use %s as placeholder for prerequisite course title.  Default: Students need to complete "%s" first.', 'coursepress' ); ?>
								</li>
								<li><span>passcode_text</span>
									– <?php _e( 'Text to display when a passcode is required. Default: A passcode is required to enroll.', 'coursepress' ); ?>
								</li>
								<li><span>anyone_text</span>
									– <?php _e( 'Text to display when anyone can enroll. Default: Anyone', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_enrollment_type]<br/>[course_enrollment_type course_id="42"]<br/>[course_enrollment_type
								passcode_text="Whats the magic word?"]</code>
						</td>
					</tr>

					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Class Size', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_class_size]</span><br/>
							<span class=""><?php _e( 'Shows the course class size, limits and remaining seats.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>label</span>
									– <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'coursepress' ); ?>
								</li>
								<li><span>label_tag</span>
									– <?php _e( 'HTML tag (without brackets) to use for the individual labels. Default: strong', 'coursepress' ); ?>
								</li>
								<li><span>label_delimeter</span>
									– <?php _e( 'Symbol to use after the label. Default is colon (:)', 'coursepress' ); ?></li>
								<li><span>show_no_limit</span>
									– <?php _e( 'If "yes" it will show the no_limit_text. If "no" then nothing will display for unlimited courses. Default: no', 'coursepress' ); ?>
								</li>
								<li><span>show_remaining</span>
									– <?php _e( 'If "yes" show remaining_text. If "no" don’t show remaining places. Default: "Yes"', 'coursepress' ); ?>
								</li>
								<li><span>no_limit_text</span>
									– <?php _e( 'Text to display for unlimited class sizes. Default: Unlimited', 'coursepress' ); ?>
								</li>
								<li><span>remaining_text</span>
									– <?php _e( 'Text to display for remaining places. Use %d for the remaining number. Default: (%d places left)', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_class_size]<br/>[course_class_size course_id="42" no_limit_text="The more the
								merrier"]<br/>[course_class_size remaining_text="Only %d places remaining!"]</code>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Cost', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_cost]</span><br/>
							<span class=""><?php _e( 'Shows the pricing for the course or free for unpaid courses.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>label</span>
									– <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'coursepress' ); ?>
								</li>
								<li><span>label_tag</span>
									– <?php _e( 'HTML tag (without brackets) to use for the individual labels. Default: strong', 'coursepress' ); ?>
								</li>
								<li><span>label_delimeter</span>
									– <?php _e( 'Symbol to use after the label. Default is colon (:)', 'coursepress' ); ?></li>
								<li><span>no_cost_text</span>
									– <?php _e( 'Text to display for unpaid courses. Default: FREE', 'coursepress' ); ?></li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_cost]<br/>[course_cost no_cost_text="Free as in beer."]</code>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Time Estimation', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_time_estimation]</span><br/>
							<span class=""><?php _e( 'Shows the total time estimation based on calculation of unit elements.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>label</span>
									– <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'coursepress' ); ?>
								</li>
								<li><span>label_tag</span>
									– <?php _e( 'HTML tag (without brackets) to use for the individual labels. Default: strong', 'coursepress' ); ?>
								</li>
								<li><span>label_delimeter</span>
									– <?php _e( 'Symbol to use after the label. Default is colon (:)', 'coursepress' ); ?></li>
								<li><span>wrapper</span>
									– <?php _e( 'Wrap inside a div tag (yes|no). Default: no', 'coursepress' ); ?></li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_time_estimation course_id="42" wrapper="yes"]<br />[course_time_estimation course_id="42"]<br />[course_time_estimation wrapper="yes"]</code>
						</td>
					</tr>

					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Language', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_language]</span><br/>
							<span class=""><?php _e( 'Displays the language of the course (if set).', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>label</span>
									– <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'coursepress' ); ?>
								</li>
								<li><span>label_tag</span>
									– <?php _e( 'HTML tag (without brackets) to use for the individual labels. Default: strong', 'coursepress' ); ?>
								</li>
								<li><span>label_delimeter</span>
									– <?php _e( 'Symbol to use after the label. Default is colon (:)', 'coursepress' ); ?></li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code> [course_language]<br/>[course_language label="Delivered in"]</code>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course List Image', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_list_image]</span><br/>
							<span class=""><?php _e( 'Displays the course list image. (See [course_media])', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>width</span> – <?php _e( 'Width of image. Default: Original width', 'coursepress' ); ?>
								</li>
								<li><span>height</span>
									– <?php _e( 'Height of image. Default: Original height', 'coursepress' ); ?></li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_list_image]<br/>[course_list_image width="100" height="100"]</code>
						</td>
					</tr>

					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Featured Video', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_featured_video]</span><br/>
							<span class=""><?php _e( 'Embeds a video player with the course’s featured video. (See [course_media])', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>width</span>
									– <?php _e( 'Width of video player. Default: Default player width', 'coursepress' ); ?></li>
								<li><span>height</span>
									– <?php _e( 'Height of video player. Default: Default player height', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_featured_video]<br/>[course_featured_video width="320" height="240"]</code>
						</td>
					</tr>

					<!--					<tr>-->
					<!--						<th scope="row" class="cp-shortcode-title">-->
					<?php //_e( 'Course Thumbnail', 'coursepress' ); ?><!--</th>-->
					<!--						<td>-->
					<!--							<span class="cp-shortcode-code">[course_thumbnail]</span><br />-->
					<!--							<span class="">-->
					<?php //_e( 'Shows the course thumbnail image that is generated from list image. (See [course_media])', 'coursepress' ); ?><!--</span>-->
					<!---->
					<!--							<p class="cp-shortcode-subheading">-->
					<?php //_e( 'Required Attributes:', 'coursepress' ); ?><!--</p>-->
					<!---->
					<!--							<ul class="cp-shortcode-options">-->
					<!--								<li><span>course_id</span> – -->
					<?php //_e( 'If outside of the WordPress loop.', 'coursepress' ); ?><!--</li>-->
					<!--							</ul>-->
					<!---->
					<!--							<p class="cp-shortcode-subheading">-->
					<?php //_e( 'Optional Attributes:', 'coursepress' ); ?><!--</p>-->
					<!---->
					<!--							<ul class="cp-shortcode-options">-->
					<!--								<li><span>wrapper</span> – -->
					<?php //_e( 'The HTML tag to wrap around the thumbnail. Default: figure', 'coursepress' ); ?><!--</li>-->
					<!--								<li><span>class</span> – -->
					<?php //_e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?><!--</li>-->
					<!--							</ul>-->
					<!--		-->
					<!--							<p class="cp-shortcode-subheading">-->
					<?php //_e( 'Examples:', 'coursepress' ); ?><!--</p>-->
					<!--							<code>[course_thumbnail]<br />[course_thumbnail course_id="22" wrapper="div"]</code>-->
					<!--						</td>-->
					<!--					</tr>-->

					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Media', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_media]</span><br/>
							<span class=""><?php _e( 'Displays either the list image or the featured video (with the other option as possible fallback).', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>


							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>type</span>
									– <?php _e( 'Use "image" to only display list image if available. Use "video" to only show the video if available. Use "thumbnail" to show the course thumbnail (shortcut for type="image" and priority="image"). Use "default" to enable priority mode (see priority attribute). Default: CoursePress Settings', 'coursepress' ); ?>
								</li>
								<li><span>priority</span>
									– <?php _e( 'Use "image" to try to show the list image first. If not available, then try to use the featured video.  Use "video" to try to show the featured video first. If not available, try to use the list image. Default: CoursePress Settings', 'coursepress' ); ?>
								</li>
								<li><span>list_page</span>
									– <?php _e( 'Use "yes" to use the CoursePress Settings for "Course Listings". Use "no" to use the CoursePress Settings for "Course Details Page". Default: no', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>


							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_media]<br/>[course_media list_page="yes"]<br/>[course_media type="video"]<br/>[course_media
								priority="image"]<br/>[course_media type="thumbnail"]</code>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Join Button', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_join_button]</span><br/>
							<span class=""><?php _e( 'Shows the Join/Signup/Enroll button for the course. What it displays is dependent on the course settings and the user’s status/enrollment.<br />See the attributes for possible button labels.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_full_text</span>
									– <?php _e( 'Text to display if the course is full. Default: Course Full', 'coursepress' ); ?>
								</li>
								<li><span>course_expired_text</span>
									– <?php _e( 'Text to display when the course has expired. Default: Not available', 'coursepress' ); ?>
								</li>
								<li><span>enrollment_finished_text</span>
									– <?php _e( 'Text to display when enrollments are finished (expired). Default: Enrollments Finished', 'coursepress' ); ?>
								</li>
								<li><span>enrollment_closed_text</span>
									– <?php _e( 'Text to display when enrollments haven’t started yet. Default: Enrollments Closed', 'coursepress' ); ?>
								</li>
								<li><span>enroll_text</span>
									– <?php _e( 'Text to display when course is ready for enrollments. Default: Enroll now', 'coursepress' ); ?>
								</li>
								<li><span>signup_text</span>
									– <?php _e( 'Text to display when course is ready for enrollments, but the user is not logged in (visitor). Default: Signup!', 'coursepress' ); ?>
								</li>
								<li><span>details_text</span>
									– <?php _e( 'Text for the button that takes you to the full course page. Default: Course Details', 'coursepress' ); ?>
								</li>
								<li><span>prerequisite_text</span>
									– <?php _e( 'Text to display if the course has a prerequisite. Default: Pre-requisite Required', 'coursepress' ); ?>
								</li>
								<li><span>passcode_text</span>
									– <?php _e( 'Text to display if the course requires a password. Default: Passcode Required', 'coursepress' ); ?>
								</li>
								<li><span>not_started_text</span>
									– <?php _e( 'Text to display when a student is enrolled, but the course hasn’t started yet. Default: Not available', 'coursepress' ); ?>
								</li>
								<li><span>access_text</span>
									– <?php _e( 'Text to display when the user is enrolled and ready to learn. Default: Start Learning', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_join_button]<br/>[course_join_button course_id="11" course_expired_text="You
								missed out big time!"]</code>
						</td>
					</tr>

					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Action Links', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_action_links]</span><br/>
							<span class=""><?php _e( 'Shows  "Course Details" and "Withdraw" links to students.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_action_links]</code>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Calendar', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_calendar]</span><br/>
							<span class=""><?php _e( 'Shows the course calendar (bounds are restricted by course start and end dates). Will always attempt to show today’s date on a calendar first.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>month</span>
									– <?php _e( 'Month to display as number (e.g. 03 for March). Default: Today’s date', 'coursepress' ); ?>
								</li>
								<li><span>year</span>
									– <?php _e( 'Year to display as 4-digit number (e.g. 2014). Default: Today’s date', 'coursepress' ); ?>
								</li>
								<li><span>pre</span>
									– <?php _e( 'Text to display for previous month link. Default: « Previous', 'coursepress' ); ?>
								</li>
								<li><span>next</span>
									– <?php _e( 'Text to display for next month link. Default: Next »', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_calendar]<br/>[course_calendar pre="< Previous" next="Next >"]</code>
						</td>
					</tr>

					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course List', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_list]</span><br/>
							<span class=""><?php _e( 'Displays a listing of courses. Can be for all courses or restricted by instructors or students (only one or the other, if both specified only students will be used).', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>status</span>
									– <?php _e( 'The status of courses to show (uses WordPress status). Default: published', 'coursepress' ); ?>
								</li>
								<li><span>instructor</span>
									– <?php _e( 'The instructor id to list courses for a specific instructor. Can also specify multiple instructors using commas. (e.g. instructor="1,2,3"). Default: empty', 'coursepress' ); ?>
								</li>
								<li><span>student</span>
									– <?php _e( 'The student id to list courses for a specific student. Can also specify multiple students using commas. (e.g. student="1,2,3"). Default: empty', 'coursepress' ); ?>
									<br/>
									<strong>Note:</strong> If both student and instructor are specified, only the
									student will be used.
								</li>
								<li><span>two_column</span>
									– <?php _e( 'Use "yes" to display primary fields in left column and actions in right column. Use "no" for a single column. Default: yes', 'coursepress' ); ?>
								</li>
								<li><span>left_class</span>
									– <?php _e( 'Additional CSS classes for styling the left column (if selected). Default: empty', 'coursepress' ); ?>
								</li>
								<li><span>right_class</span>
									– <?php _e( 'Additional CSS classes for styling the right column (if selected). Default: empty', 'coursepress' ); ?>
								</li>
								<li><span>title_link</span>
									– <?php _e( 'Use "yes" to turn titles into links to the course. Use "no" to display titles without links. Default: yes', 'coursepress' ); ?>
								</li>
								<li><span>title_tag</span>
									– <?php _e( 'The HTML element (without brackets) to use for course titles. Default: h3', 'coursepress' ); ?>
								</li>
								<li><span>title_class</span>
									– <?php _e( 'Additional CSS classes for styling the course titles. Default: empty', 'coursepress' ); ?>
								</li>
								<li><span>show</span>
									– <?php _e( 'The fields to show for the course body. See [course] shortcode. Default: dates,enrollment_dates,class_size,cost', 'coursepress' ); ?>
								</li>
								<li><span>show_button</span>
									– <?php _e( 'Show [course_join_button]. Accepts "yes" and "no". Default: yes', 'coursepress' ); ?>
								</li>
								<li><span>show_divider</span>
									– <?php _e( 'Add divider between courses. Accepts "yes" or "no". Default: yes', 'coursepress' ); ?>
								</li>
								<li><span>show_media</span>
									– <?php _e( 'Show [course_media] if "yes". Default: no', 'coursepress' ); ?></li>
								<li><span>media_type</span>
									– <?php _e( 'Type to use for media. See [course_media]. Default: CoursePress settings for Course Listing Pages.', 'coursepress' ); ?>
								</li>
								<li><span>media_priority</span>
									– <?php _e( 'Priority to use for media. See [course_media]. Default: CoursePress settings for Course Listing Pages.', 'coursepress' ); ?>
								</li>
								<li><span>course_class</span>
									– <?php _e( 'Additional CSS classes for styling each course. Default: empty', 'coursepress' ); ?>
								</li>
								<li><span>limit</span>
									– <?php _e( 'Limit the number of courses. Use -1 to show all. Default: -1', 'coursepress' ); ?>
								</li>
								<li><span>order</span>
									– <?php _e( 'Order the courses by title. "ASC" for ascending order. "DESC" for descending order. Empty for WordPress default. Default: "ASC"', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling the whole list. Default: empty', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_list]<br/>[course_list instructor="2"]<br/>[course_list student="3"]<br/>[course_list
								instructor="2,4,5"]<br/>[course_list show="dates,cost" limit="5"]</code>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Featured Course', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_featured]</span><br/>
							<span class=""><?php _e( 'Shows a featured course.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span>
									– <?php _e( 'If no id is pecified then it will return empty text.', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>featured_title</span>
									– <?php _e( 'The title to display for the featured course. Default: Featured Course', 'coursepress' ); ?>
								</li>
								<li><span>button_title</span>
									– <?php _e( 'Text to display on the call to action button. Default: Find out more.', 'coursepress' ); ?>
								</li>
								<li><span>media_type</span>
									– <?php _e( 'Media type to use for featured course. See [course_media]. Default: default', 'coursepress' ); ?>
								</li>
								<li><span>media_priority</span>
									– <?php _e( 'Media priority to use for featured course. See [course_media]. Default: video', 'coursepress' ); ?>
								</li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_featured course_id="42"]<br/>[course_featured course_id="11"
								featured_title="The best we got!"]</code>
						</td>
					</tr>

					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Structure', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_structure]</span><br/>
							<span class=""><?php _e( 'Displays a tree view of the course structure.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Required Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>course_id</span> – <?php _e( 'If outside of the WordPress loop.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>free_text</span>
									– <?php _e( 'Text to show for FREE preview items. Default: Free', 'coursepress' ); ?></li>
								<li><span>show_title</span>
									– <?php _e( 'Show course title in structure, "yes" or "no". Default: "no"', 'coursepress' ); ?>
								</li>
								<li><span>show_label</span>
									– <?php _e( 'Show label text as tree heading, "yes" or "no". Default: no', 'coursepress' ); ?>
								</li>
								<li><span>show_divider</span>
									– <?php _e( 'Show divider between major items in the tree, "yes" or "no". Default: yes', 'coursepress' ); ?>
								</li>
								<li><span>label</span>
									– <?php _e( 'Label to display for the output. Set label to "" to hide the label completely.', 'coursepress' ); ?>
								</li>
								<li><span>label_tag</span>
									– <?php _e( 'HTML tag (without brackets) to use for the individual labels. Default: strong', 'coursepress' ); ?>
								</li>
								<li><span>label_delimeter</span>
									– <?php _e( 'Symbol to use after the label. Default is colon (:)', 'coursepress' ); ?></li>
								<li><span>class</span>
									– <?php _e( 'Additional CSS classes for styling. Default: empty', 'coursepress' ); ?></li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_structure]<br/>[course_structure course_id="42" free_text="Gratis!"
								show_title="no"]<br/>[course_structure show_title="no" label="Curriculum"]</code>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Course Signup/Login Page', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[course_signup]</span><br/>
							<span class=""><?php _e( 'Shows a custom login or signup page for front-end user registration and login. <strong>Note:</strong> This is already part of CoursePress and can be set in CoursePress Settings. Links to default pages can be found in Appearance > Menus > CoursePress.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Optional Attributes:', 'coursepress' ); ?></p>

							<ul class="cp-shortcode-options">
								<li><span>failed_login_text</span>
									– <?php _e( 'Text to display when user doesn’t authenticate. Default: Invalid login.', 'coursepress' ); ?>
								</li>
								<li><span>failed_login_class</span>
									– <?php _e( 'CSS class to use for invalid login. Default: red', 'coursepress' ); ?></li>
								<li><span>logout_url</span>
									– <?php _e( 'URL to redirect to when user logs out. Default: Plugin defaults.', 'coursepress' ); ?>
								</li>
								<li><span>signup_title</span>
									– <?php _e( 'Title to use for Signup section. Default: &lt;h3>Signup&lt;/h3>', 'coursepress' ); ?>
								</li>
								<li><span>login_title</span>
									– <?php _e( 'Title to use for Login section. Default: &lt;h3>Login&lt;/h3>', 'coursepress' ); ?>
								</li>
								<li><span>signup_url</span>
									– <?php _e( 'URL to redirect to when clicking on "Don\'t have an account? Go to Signup!"  Default: Plugin defaults.', 'coursepress' ); ?>
								</li>
								<li><span>login_url</span>
									– <?php _e( 'URL to redirect to when clicking on "Already have an Account?". Default: Plugin defaults.', 'coursepress' ); ?>
								</li>
							</ul>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[course_signup]<br/>[course_signup signup_title="&lt;h1>Signup Now&lt;/h1>"]</code>
						</td>
					</tr>

					<tr class="cp-shortcode-alt">
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Student Dashboard Template', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[courses_student_dashboard]</span><br/>
							<span class=""><?php _e( 'Loads the student dashboard template.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[courses_student_dashboard]</code>
						</td>
					</tr>

					<tr>
						<th scope="row" class="cp-shortcode-title"><?php _e( 'Student Settings Template', 'coursepress' ); ?></th>
						<td>
							<span class="cp-shortcode-code">[courses_student_settings]</span><br/>
							<span class=""><?php _e( 'Loads the student settings template.', 'coursepress' ); ?></span>

							<p class="cp-shortcode-subheading"><?php _e( 'Examples:', 'coursepress' ); ?></p>
							<code>[courses_student_settings]</code>
						</td>
					</tr>

				</table>
			</div>
		</div>

	</form>
</div>