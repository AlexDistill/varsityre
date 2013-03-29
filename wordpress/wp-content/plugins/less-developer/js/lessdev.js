jQuery(document).ready( function( $ ) {

	// codemirror object reference
	var lessdev_editors = new Array();
	lessdev_editors['less'] = lessdev_less_editor;
	lessdev_editors['css'] = lessdev_css_editor;
	
	$lessdev = $('#lessdev');
	$lessdev.data('status', false);

	var current_file = new Array();
	current_file['less'] = $('#current_less_file');
	current_file['css'] = $('#current_css_file');

	var select = new Array();
	select['less'] = $('#lessdev_less_files');
	select['css'] = $('#lessdev_css_files')

	$message = $('#message');
	$action_buttons = $('.action', '#lessdev .controls');


	/**
	 * Copy to clipboard
	 * ZeroClipboard
	 */
	var clip = new ZeroClipboard.Client();
	
	clip.setText( lessdev_editors['css'].getValue() );
	clip.glue('copy_css', 'copy_css_container');
	clip.addEventListener( 'onComplete', css_copied );

	function css_copied( client, text ) {
		successMessage( 'CSS Copied to Clipboard.' );
	}

	/**
	 * Control + Enter compiling
	 */
	$lessdev.keypress(function (event) {                                 
		var keyCode = (event.which ? event.which : event.keyCode);          
		
		if (keyCode === 10 || keyCode == 13 && event.ctrlKey) {
			getCompiledLess();
			return false;
		}

		return true;
	});		

	/**
	 * LUDICRIS SPEED: GO
	 */
	$('#compile').click( function() {
		getCompiledLess();		
	});

	function getCompiledLess() {

		if ( $lessdev.data('status') ) {
			failMessage('Busy, wait for the previous operation to complete and try again.');
			return false;
		}

		$lessdev.data('status', 'Compiling LESS');

		$loading = $('#compiling');
		
		// show loading
		$loading.fadeIn('fast');

		var data = {
			action: 'lessdev_live_compile',
			input: lessdev_editors['less'].getValue(),
			less_file: select['less'].val(),
			css_file: select['css'].val()
		};

		//console.log({compile: data});

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(ajaxurl, data, function( response ) {

			res = prepareJSON( response );
			// alertbox
			setMessage( res.message, res.success );

			$loading.fadeOut('fast');
			// set new editor content
			lessdev_editors['css'].setValue( res.compiled );
			// update clip
			clip.setText( res.compiled );

			$lessdev.data('status', false);
		});
	}

	/**
	 * MODE TOGGLE
	 */
	$toggles = $('.toggles > a', '#wpbody-content');
	$toggles.click( function(event) {

		$toggle = $(this);
		action = $toggle.attr('data-action');

		if ( 'reset' == action ) {
			if ( !confirm('This will clear all data from both editors.  Does not affect files.') ) {
				event.preventDefault();
				return;
			}
		}


		if ( 'full-width' == action )
			$lessdev.addClass('fullwidth');
		else if ( 'columns' == action )
			$lessdev.removeClass('fullwidth');


	});


	/**
	 * ACTION BUTTONS
	 *
	 * REFRESH / LOAD / EDIT / SAVE / CANCEL
	 * 
	 */
	$action_buttons.click( function() {

		if ( $lessdev.data('status') ) {
			failMessage('Busy, wait for the previous operation to complete and try again.');
			return false;
		}

		$button       = $(this);
		$buttons      = $button.siblings('input.action');
		ext           = $button.attr('data-ext');
		action        = $button.attr('data-action');
		$select       = $('#lessdev_'+ext+'_files');
		filename      = $select.val();
		$current_file = current_file[ ext ];

		if ( !filename && ('refresh' != action && 'clear' != action) )
			return false;

		//console.log(ext);
		//console.log(action);

		$load   = $buttons.filter('.load');
		$edit   = $buttons.filter('.edit');
		$save   = $buttons.filter('.save');
		$cancel = $buttons.filter('.cancel');

		// loading graphic
		$loading  = $('#'+ext+'_loading');

		/**
		 * REFRESH
		 */
		if ( 'refresh' == action ) {

			$lessdev.data('status', 'Refreshing file lists');

			// target both
			$loading = $('.loading', '#lessdev .controls');
			$selects = select[ ext ];

			// disable
			$selects.attr('disabled','disabled');

			// show loading
			$loading.fadeIn('fast');

			var data = {
				action: 'lessdev_refresh_files',
				current_less_file: current_file['less'].val(),
				current_css_file: current_file['css'].val()
			}

			// do the ajax thing
			$.post(ajaxurl, data, function( response ) {

				//console.log(response);

				res = prepareJSON( response );

				$loading.fadeOut('fast');

				// alert
				setMessage( res.message, res.success );
				// on success only
				if ( res.success ) {

					$selects.filter('.less').html( res.less_options );
					$selects.filter('.css').html( res.css_options );
				}
				$selects.removeAttr('disabled');

				$lessdev.data('status', false);
			});

		}

		/**
		 * LOAD
		 */
		if ( 'load' == action ) {

			$lessdev.data('status', 'Loading '+filename );

			// show loading
			$loading.fadeIn('fast');

			var data = {
				action: 'lessdev_load_file',
				editor: ext,
				file: filename
			}

			$.post(ajaxurl, data, function( response ) {

				//console.log(response);

				res = prepareJSON( response );

				$loading.fadeOut('fast');

				// on success only
				if ( res.success ) {
					// set new editor content
					lessdev_editors[ ext ].setValue( res.filedata );

					// update clip with current lessdev_editors['css'] value
					// clip.setText( res.filedata );
					// current file
					$current_file.val( filename );

					$lessdev.data('status', false);
				}

				// alert
				setMessage( res.message, res.success );
			});
		}
		/**
		 * EDIT
		 */
		if ( 'edit' == action ) {
			$current_file.val( filename );
			$button.hide();
			$save.show();
			$cancel.val('Cancel').show();
			$select.attr('disabled', 'disabled');
		}
		/**
		 * SAVE
		 */
		if ( 'save' == action ) {

			$lessdev.data('status', 'Saving '+filename);

			// show loading
			$loading.fadeIn('fast');

			new_data = lessdev_editors[ ext ].getValue();

			var data = {
				action: 'lessdev_save_file',
				file: filename,
				ext: ext,
				new_data: new_data
			}

			// save_file ajax
			$.post(ajaxurl, data, function( response ) {

				//console.log(response);

				res = prepareJSON( response );

				// alert
				setMessage( res.message, res.success );

				$loading.fadeOut('fast');
				$cancel.val('Unlock');
				$lessdev.data('status', false);
			});
		}
		/**
		 * CLEAR
		 */
		if ( 'clear' == action ) {

			if ( confirm('Clear '+ext.toUpperCase()+' editor?') ) {
				lessdev_editors[ ext ].setValue('');
				select[ ext ][0].selectedIndex = 0;
				$current_file.val('');
				successMessage(ext.toUpperCase()+' Editor Cleared Successfully.');
			}
		}

		/**
		 * CANCEL / UNLOCK
		 */
		if ( 'cancel' == action ) {
			$button.hide();
			$save.hide();
			$edit.show();
			$select.removeAttr('disabled');
		}
	});

	function successMessage(text) {
		setMessage(text, true);
	}
	function failMessage(text) {
		setMessage(text, false);
	}

	function setMessage(text, success) {

		if ( 'undefined' != typeof message_timer )
			clearTimeout(message_timer);

		if ( success ) {
			$message.removeClass('error');
			$message.addClass('success');
			time = 4000;
		}
		else {
			$message.removeClass('success');
			$message.addClass('error');	
			time = 10000;
		}

		fadeOutMessage = function() {
			$message.fadeOut('slow');
		}

		// update text
		if ( ! $message.is(':visible') )
			$message.fadeIn('fast')

		$message.html( '<p>'+text+'</p>' )

		//console.log({before: message_timer});
		message_timer = setTimeout( fadeOutMessage, time);
		//console.log({after: message_timer});
	}


	function prepareJSON( response ) {

		// console.log( response );
		var data = eval('(' + response + ')');
		// console.log( data );

		return data;
	}
});