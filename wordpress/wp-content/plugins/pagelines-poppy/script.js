!function ($) {

	$(document).ready(function() {

		$('.send-poppy').on('click', function(){

			plSendMail()
		})

	})

	function plSendMail() {

		var name = $('.poppy-name').val()
		,	email = $('.poppy-email').val()
		,	custom = $('.poppy-custom').val()
		,	msg = $('.poppy-msg').val()
		,	captcha = $('.poppy-captcha').val()

		jQuery.ajax({
			type: 'POST'
			, url: poppyjs.ajaxurl
			, data: {
				action: 'ajaxcontact_send_mail'
				,	name: name
				,	email: email
				,	custom: custom
				,	msg: msg
				,	cap: captcha
				,	width:screen.width
				,	height:screen.height
				,	agent:navigator.userAgent
			}

			,	success: function(response){

					var responseElement = jQuery('.poppy-response')
					var poppyForm = jQuery('.poppy-form')

					responseElement
						.hide()
						.removeClass('alert alert-error alert-success')


					if (response == "ok") {
						responseElement
							.fadeIn()
							.html('Great work! Your message was sent.')
							.addClass('alert alert-success')

						poppyForm
							.html('')

						setTimeout(function() {
							jQuery('.poppy').modal('hide')
						}, 2000)

					} else {
						responseElement
							.fadeIn()
							.html(response)
							.addClass('alert alert-error')
					}
			}

			, error: function(MLHttpRequest, textStatus, errorThrown){
				console.log(errorThrown);
			}

		});

	}

}(window.jQuery);