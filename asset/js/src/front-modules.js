/* global CoursePress */
/** MODULES **/
(function( $ ) {
	CoursePress.timer = function( container ) {
		var timer_span = container.find( '.quiz_timer' ).show(),
			module_elements = container.find( '.module-elements' );

		if ( 0 === timer_span.length ) {
			return;
		}
		// Don't run the timer when module element is hidden
		if ( ! module_elements.is( ':visible' ) ) {
			timer_span.hide();
			return;
		}

		var duration = timer_span.data( 'limit' ), repeat = timer_span.data( 'retry' ),
			hours = 0, minutes = 0, seconds = 0, total_limit = 0, timer,
			_seconds = 60, _minutes = '00', _hours = '00', dtime, info, send, expired, inputs;

		duration = duration.split( ':' );

		seconds = duration.pop();
		_seconds = seconds = parseInt( seconds );

		if ( duration.length > 0 ) {
			_minutes = minutes = duration.pop();
			minutes = parseInt( minutes ) * 60;
		}

		if ( duration.length > 0 ) {
			_hours = hours = duration.pop();
			hours = parseInt( hours ) * 60 * 60;
		}

		total_limit = hours + minutes + seconds;

		info = container.find( '.quiz_timer_info' );
		inputs = container.find( '.module-elements input, .module_elements select, .module-elements textarea, .module-elements .video_player' );
		inputs.removeAttr('disabled');

		expired = function() {
			inputs.attr( 'disabled', 'disabled' );
			info.show();
		};

		if ( 0 === total_limit ) {
			if ( 'no' === repeat ) {
				expired();
			} else {
				timer_span.hide();
			}
			return;
		}

		timer = setInterval(function(){

			if ( 60 == _seconds ) {
				if ( parseInt( _minutes ) > 0 ) {
					_minutes = parseInt( _minutes ) - 1;
				}
			}

			_seconds = parseInt( _seconds ) - 1;

			if ( _seconds <= 0 && _minutes <= 0 && _hours <= 0 ) {
				clearInterval( timer );
				expired();
				// Send record data in silence
				send = new CoursePress.SendRequest();
				send.set({
					cpnonce: _coursepress.cpnonce,
					className: 'CoursePress_Module',
					method: 'record_expired_answer',
					module_id: container.data( 'id' ),
					course_id: container.find( '[name="course_id"]' ).val(),
					unit_id: container.find( '[name="unit_id"]' ).val(),
					student_id: container.find( '[name="student_id"]' ).val(),
					action: 'record_time'
				});
				send.save();
				// Enable retry button here
				info.on( 'click', function() {
					inputs.removeAttr( 'disabled' );
					info.hide();
					CoursePress.timer( container );
				});
			}
			if ( _seconds < 0 ) {
				_seconds = 59;
				if ( parseInt( _minutes ) > 0 ) {
					_minutes = parseInt( _minutes ) - 1;
				}
				if ( parseInt( _minutes ) <= 0 ) {
					if ( parseInt( _hours ) > 0 ) {
						_hours = parseInt( _hours ) - 1;
						_minutes = 59;
						if ( _hours < 10 ) {
							_hours = '0' + parseInt( _hours );
						}
					}
				}
			}
			if ( _seconds < 10 ) {
				_seconds = '0' + parseInt( _seconds );
			}
			if ( _minutes < 10 ) {
				_minutes = '0' + parseInt( _minutes );
			}
			if ( '00' == _hours ) {
				dtime = _minutes + ':' + _seconds;
			} else {
				dtime = _hours + ':' + _minutes + ':' + _seconds;
			}
			timer_span.html( dtime );
		}, 1000);
	};

	CoursePress.MediaElements = function( container ) {
		if ( $.fn.mediaelementplayer ) {
			var media = $( 'audio,video', container );

			if(videojs.getPlayers()) {
				var player = videojs(media[0].id);
			}

			if ( media.length > 0 ) {
				media.mediaelementplayer();
			}
		}
	};

	CoursePress.LoadFocusModule = function() {
		var nav = $(this),
			data = nav.data(),
			container = $( '.coursepress-focus-view' ),
			url = [ _coursepress.home_url, 'coursepress_focus' ],
			parents = $( '.cp, .coursepress-focus-view' )
		;

		if ( 'submit' === nav.attr( 'type' ) ) {
			// It's a submit button, continue submission
			return;
		}

		if ( 'course' === data.type ) {
			// Reload
			window.location = data.url;
			return;
		}

		if ( data.unit ) {
			//Find current unit serve
			var current_unit = parents.find( '[name="unit_id"]' );

			if ( 0 === current_unit.length || data.unit !== current_unit.val() ) {
				window.location = data.url;
				return;
			}
		}

		url.push( data.course, data.unit, data.type, data.id );
		url = url.join( '/' );
		container.load( url, function() {
			CoursePress.resetBrowserURL( data.url );
			CoursePress.timer( container.find( '.cp-module-content' ) );
			CoursePress.MediaElements( container.find( '.cp-module-content' ) );
		});

		return false;
	};

	CoursePress.validateUploadModule = function() {
		var input_file = $(this),
			parentDiv = input_file.parents( '.module-elements' ).first(),
			warningDiv = parentDiv.find( '.invalid-extension, .current-file' ),
			filename = input_file.val(),
			extension = filename.split( '.' ).pop(),
			allowed_extensions = _.keys( _coursepress.allowed_student_extensions )
		;

		if ( 0 < warningDiv.length ) {
			// Remove warningdiv
			warningDiv.detach();
		}

		if ( ! _.contains( allowed_extensions, extension ) ) {
			warningDiv = $( '<div class="invalid-extension">' ).insertAfter( input_file.parent() );
			warningDiv.html( _coursepress.invalid_upload_message )
		} else {
			var file = input_file.get(0);

			if ( file.files && file.files.length ) {
				for (var i=0; i < file.files.length; i++) {
					filename = file.files[i].name;
				}
			}

			warningDiv = $( '<div class="current-file"></div>' ).html( filename );
			warningDiv.insertAfter( input_file.parent() );
		}
	};

	CoursePress.ModuleSubmit = function() {
		var form = $(this),
			error_box = form.find( '.cp-error-box' ),
			focus_box = form.parents( '.coursepress-focus-view, .cp.unit-wrapper' ),
			iframe = false,
			timer = false,
			module_elements = $( '.module-elements[data-required="1"]', form ),
			module_response = module_elements.next( '.module-response' ),
			is_focus = form.parents( '.coursepress-focus-view' ).length > 0,
			error = 0, mask,
            validate = $('[name=save_progress_and_exit]').length < 1
		;
		if ( 0 < error_box.length ) {
			error_box.remove();
		}

		// Validate required submission
		if ( validate ) {
			module_elements.each( function() {
				var module = $(this),
				module_type = module.data( 'type' ),
				input;
				// Validate radio and checkbox
				if ( _.contains( ['input-checkbox', 'input-radio', 'input-quiz'], module_type ) ) {
					input = $( ':checked', module );
					if ( 0 == input.length ) {
						error += 1;
					}
				} else if ( 'input-upload' === module_type && 0 === module_response.length ) {
					input = $( '[type="file"]', module );
					if ( '' === input.val() ) {
						error += 1;
					}
					// Validate input module
				} else if ( _.contains( ['input-text', 'input-textarea', 'input-select'], module_type ) ) {
					input = $( 'input,textarea,select', module );
					if ( '' === input.val() ) {
						error += 1;
					}
				}
			} );
			if ( error > 0 ) {
				// Don't submit if an error is found!
				new CoursePress.WindowAlert({
					message: _coursepress.module_error[ is_focus ? 'required' : 'normal_required' ]
				});
				return false;
			}
		}

		// Mask the page
		mask = CoursePress.Mask();

		// Insert ajax marker
		form.append( '<input type="hidden" name="is_cp_ajax" value="1" />' );

		// Create iframe to trick the browser
		iframe = $( '<iframe name="cp_submitter" style="display:none;">' ).insertBefore( form );

		// Set the form to submit unto the iframe
		form.attr( 'target', 'cp_submitter' );

		// Apply tricks
		iframe.on( 'load', function() {
			var that = $(this).contents().find( 'body' );

			timer = setInterval(function() {
				var html = that.text();

				if ( '' != html ) {
					// Kill timer
					clearInterval( timer );
					// Remove the mask
					mask.done();

					var data = window.JSON.parse( html );

					if ( true === data.success ) {
						// Process success
						if ( data.data.url ) {
							if ( false === is_focus || true === data.data.is_reload || data.data.type && 'completion' === data.data.type ) {
								window.location = data.data.url;
							} else {
								focus_box.html( data.data.html );
								CoursePress.resetBrowserURL( data.data.url );
								CoursePress.timer( focus_box.find( '.cp-module-content' ) );
								CoursePress.MediaElements( focus_box.find( '.cp-module-content' ) );
							}
						}
					} else {
						// Focus on the error box
						if ( data.data.html ) {
							focus_box.html( data.data.html );
						}
						new CoursePress.WindowAlert({
							message: data.data.error_message
						});
					}
				}
			}, 100 );
		});
	};

	CoursePress.toggleModuleState = function() {
		var button = $(this),
			parentDiv = button.closest( '.cp-module-content' ),
			elementsDiv = $( '.module-elements', parentDiv ),
			responseDiv = $( '.module-response', parentDiv ),
			moduleHidden = $( '.cp-is-hidden-module', parentDiv )
		;

		responseDiv.hide();
		elementsDiv.show();
		moduleHidden.val(0);
		CoursePress.timer( parentDiv );

		return false;
	};

	// Recreate comment-reply js
	CoursePress.commentReplyLink = function() {
		var link = $(this),
			datacom = link.parents( '[data-comid]' ).first(),
			com_id = datacom.data( 'comid' ),
			module_content = link.parents( '.cp-module-content' ).first(),
			form = $( '#respond', module_content ),
			comment_div = $( '#comment-' + com_id ),
			comment_parent = $( '[name="comment_parent"]', form ),
			tempDiv = $( '.cp-temp-div' ),
			cancel_link = form.find( '#cancel-comment-reply-link' )
		;

		// Add marker to the original form position
		if ( 0 === tempDiv.length ) {
			tempDiv = $( '<div class="cp-temp-div"></div>' ).insertAfter( form );
		}

		comment_parent.val( com_id );
		form.hide();
		comment_div.append( form.slideDown() );

		cancel_link.off( 'click' );
		cancel_link.show().on( 'click', function() {
			form.insertBefore( tempDiv );
			cancel_link.hide();
			tempDiv.remove();

			return false;
		});

		// Focus to the form
		CoursePress.Focus( form );
		// Focus to textarea
		form.find( 'textarea[name="comment"]' ).focus();

		return false;
	};

	CoursePress.addComment = function(ev) {
		var button = $(this),
			module_content = button.parents( '.cp-module-content' ).first(),
			form = $( '#respond', module_content ),
			cp_form = $( '.cp-comment-form', module_content ),
			comment = $( '[name="comment"]', form ),
			comment_parent = $( '[name="comment_parent"]', form ),
			comment_post_ID = $( '[name="comment_post_ID"]', form ),
			subscribe = $( '[name="coursepress_subscribe"]', form ),
			student_id = $( '[name="student_id"]', module_content ),
			course_id = $( '[name="course_id"]', module_content ),
			unit_id = $( '[name="module_content"]', module_content ),
			cp_error = $( '.cp-error-box', form ),
			comment_list = $( '.comment-list', module_content ),
			params = {},
			is_reply = 0 < parseInt( comment_parent.val() ),
			request = new CoursePress.SendRequest(),
			restore_form,
			mask
		;

		// Remove previous error box
		cp_error.remove();

		if ( '' === comment.val() ) {
			// Alert the user
			new CoursePress.WindowAlert({
				message: _coursepress.comments.require_valid_comment
			});

			// Prevent the form from submitting
			ev.stopImmediatePropagation();
			return false;
		}

		params = {
			comment: comment.val(),
			comment_parent: comment_parent.val(),
			comment_post_ID: comment_post_ID.val(),
			subscribe: subscribe.val(),
			cpnonce: _coursepress.cpnonce,
			method: 'add_single_comment',
			className: 'CoursePress_Module',
			course_id: course_id,
			unit_id: unit_id,
			student_id: student_id.val(),
			action: 'add_single_comment'
		};

		mask = CoursePress.Mask();
		restore_form = function() {
			var cancel_link = form.find( '#cancel-comment-reply-link' );

			comment.val( '' );
			comment_parent.val( '' );

			if ( cancel_link.is( ':visible' ) ) {
				cancel_link.trigger( 'click' );
			}

			// Remove cover mask
			mask.done();
		};


		request.set( params );
		request.off( 'coursepress:add_single_comment_success' );
		request.on( 'coursepress:add_single_comment_success', function( data ) {
			// Restore the form to it's orig position
			restore_form();

			if ( 0 < comment_list ) {
				comment_list = $( '<ol class="comment-list"></ol>' ).insertAfter( form );
			}

			var current_parent = comment_list,
				insert_type = cp_form.is( '.comment-form-desc' ) ? 'append' : 'prepend',
				child_list;

			if ( true === is_reply ) {
				current_parent = $( '#comment-' + params.comment_parent );
				child_list = current_parent.find( '.children' );

				if ( 0 === child_list.length ) {
					// Create a new .children ul
					current_parent.append( '<ul class="children"></ul>' );
					child_list = current_parent.find( '.children' );
				} else {
					child_list = 'append' === insert_type ? child_list.last() : child_list.first();
				}
				child_list[ insert_type ]( data.html );
			} else {
				current_parent[ insert_type ]( data.html );
			}

			// Focus to the last inserted comment
			CoursePress.Focus( '#comment-' + data.comment_id );
		} );
		request.on( 'coursepress:add_single_comment_error', function() {
			// Remove cover mask
			mask.done();
			// Alert the user
			CoursePress.showError( _coursepress.server_error, form );
		});
		request.save();

		// Prevent the form from submitting
		ev.stopImmediatePropagation();

		return false;
	};

	CoursePress.singleFolded = function() {
			var target = $('>ul', $(this).parent() );
			var unit = $('.unit-archive-single-title', $(this).parent());
			var modules_container = $('.unit-archive-module-wrapper', $(this).parent());
			var container = $(this);
			if ( container.hasClass('folded') ) {
				target.slideDown( function() {
					container.removeClass('folded');
					container.closest('li').removeClass('folded').addClass('unfolded');
					unit.attr('href', unit.data('original-href'));
					unit.off('click');
				});
			} else {
				target.slideUp(function() {
					container.addClass('folded');
					container.closest('li').removeClass('unfolded').addClass('folded');
					if ( "undefined" == typeof( unit.data('href') ) ) {
						/**
						 * find last seen module
						 */
						var module = $('.module-seen', modules_container).last();
						if ( module.length ) {
							module = $('.module-title', module );
							if ( module.length ) {
								unit.attr('href', unit.attr('href') + '#module-'+ module.data('id') );
								return false;
							}
						}
						/**
						 * find last seen section
						 */
						var section = $('.section-seen', modules_container).last();
						if ( section.length ) {
							section = $('.section-title', section );
							if ( section.length ) {
								unit.attr('href', unit.attr('href') + '#section-'+ section.data('id') );
								return false;
							}
						}
					}
				});
			}
			return false;
	};

	CoursePress.unitFolded = function() {
			var span = $(this),
				container = span.parents( 'li' ).first(),
				module_wrapper = container.find( '.unit-structure-modules' ),
				is_open = container.is( '.folded' )
			;

			if ( is_open ) {
				container.removeClass( 'folded' ).addClass( 'unfolded' );
				span.removeClass( 'folded' );
				module_wrapper.slideDown();
			} else {
				container.removeClass( 'unfolded' ).addClass( 'folded' );
				span.addClass( 'folded' );
				module_wrapper.slideUp();
			}
	};

	/**
	 * Save Progress & Exit
	 */
	CoursePress.saveProgressAndExit = function() {
		var form = $(this).closest('form');
		$("#respond", form).detach();
		form.append( '<input type="hidden" name="save_progress_and_exit" value="1" />' );
		form.submit();
	}

	CoursePress.hookModuleVideos = function() {

		$('.video-js').each(function(){
			var video_id = $(this).attr('id');
			var video = videojs(video_id);

			video.on('ready', function(){
				var player = this,
					player_element = $(player.el());

				function change_video_status(player)
				{
					if( $(player.el()).closest('.video_player').is('[disabled="disabled"]') )
					{
						player.pause();
					}
				}

				if(player_element.is('[autoplay]'))
				{
					player.play();
				}

				if(player_element.is('[muted]'))
				{
					player.muted(true);
				}

				player.one('click', function(){
					player.play();
				});

				player.one('play', function(){
					CoursePress.timer(player_element.closest('.cp-module-content'));
				});

				player.on('play', function(){
					change_video_status(player);
				});

				player.on('timeupdate', function(){
					change_video_status(player);
				});
			});
		});
	};

	$( document )
		.ready(function(){
			$('.cp-module-content').each(function(){
				var content = $(this);
				if(content.data('type') !== 'video')
				{
					CoursePress.timer(content);
				}
			});

			CoursePress.hookModuleVideos();
		})
		.on( 'submit', '.cp-form', CoursePress.ModuleSubmit )
		.on( 'click', '.focus-nav-prev, .focus-nav-next', CoursePress.LoadFocusModule )
		.on( 'click', '.button-reload-module', CoursePress.toggleModuleState )
		.on( 'click', '.cp-module-content .comment-reply-link', CoursePress.commentReplyLink )
		.on( 'click', '.cp-comment-submit', CoursePress.addComment )
		.on( 'change', '.cp-module-content .file input', CoursePress.validateUploadModule )
		.on( 'click', '.unit-archive-single .fold', CoursePress.singleFolded )
		.on( 'click', '.course-structure-block .unit .fold, .unit-archive-list .fold', CoursePress.unitFolded )
		.on( 'click', '.save-progress-and-exit', CoursePress.saveProgressAndExit );


})(jQuery);
