<?php
/*
Plugin Name: Poppy
Plugin URI: http://www.pagelines.com
Description: Adds a simple contact form shortcode to be used anywhere on your site.
Author: PageLines
PageLines: true
Version: 1.3
Demo: http://poppy.pagelines.me
*/

class PageLinesPoppy {

	var $version = 1.3;
	function __construct() {

		$this->base_dir	= plugin_dir_path( __FILE__ );
		$this->base_url = plugins_url( '', __FILE__ );
		$this->icon		= plugins_url( '/icon.png', __FILE__ );
		$this->less		= $this->base_dir . '/style.less';
		add_filter( 'pagelines_lesscode', array( &$this, 'get_less' ), 10, 1 );
		add_action( 'admin_init', array( &$this, 'admin_page' ) );
		add_action( 'init', array( &$this, 'add_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'hooks_with_activation' ) );
		add_action( 'wp_ajax_nopriv_ajaxcontact_send_mail', array( &$this, 'ajaxcontact_send_mail' ) );
		add_action( 'wp_ajax_ajaxcontact_send_mail', array( &$this, 'ajaxcontact_send_mail' ) );
		add_action( 'plugins_loaded', array( &$this, 'translate') );
	}

	function translate() {
 		$plugin_dir = basename( dirname( __FILE__ ) );
 		load_plugin_textdomain( 'pagelines-poppy', false, $plugin_dir );
	}

	function hooks_with_activation() {
		wp_enqueue_script( 'poppyjs', plugins_url( '/script.js', __FILE__ ), array( 'jquery' ), $this->version );
		wp_localize_script( 'poppyjs', 'poppyjs', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		if( ! function_exists( 'pl_detect_ie' ) )
			return;
		$ie_ver = pl_detect_ie();
		if( $ie_ver < 10 )
			wp_enqueue_script( 'formalize', plugins_url( '/formalize.min.js', __FILE__ ), array( 'jquery' ), $this->version );
	}

	function get_less( $less ){

		$less .= pl_file_get_contents( $this->less );
		return $less;

	}
	function add_shortcode() {
		add_shortcode( 'poppy', array(  &$this, 'draw_button' ) );
	}

	function draw_button( $atts, $content = null ) {

		extract( shortcode_atts( array(
		    'class' => '',
		    'type'	=> 'button',
		), $atts ) );

		if( ! $content )
			$content = 'Contact';
		if( 'button' == $type )
			$class = 'btn ' . $class;
		if( 'label' == $type ) {
			$class = 'label ' . $class;
			$type = 'span';
		}
		$class = rtrim( $class ) . ' poppy-pointer';

		$button = sprintf( '<%s class="%s" data-toggle="modal" href="#poppy-modal">%s</%s>',
			$type,
			$class,
			$content,
			$type
			);
		add_action( 'wp_footer', array( &$this, 'form' ) );
		return $button;
	}

	function form() {
		ob_start();
		$email		= __( 'Email Address', 'pagelines-poppy' );
		$name		= __( 'Name', 'pagelines-poppy' );
		$message	= __( 'Your Message...', 'pagelines-poppy' );
		$send		= __( 'Send Message', 'pagelines-poppy' );
	?>
<div id="poppy-modal" class="hide fade modal poppy" >
	<div class="modal-header"><a class="close" data-dismiss="modal" aria-hidden="true">×</a>
		<h3><?php echo ploption( 'poppy_form_title' ) ?></h3>
	</div>
	<div class="modal-body">
		<div class="poppy-response"></div>
		<form class="poppy-form" id="ajaxcontactform" action="" method="post" enctype="multipart/form-data">
			<fieldset>
				<div class="control-group">
					<div class="controls form-inline">
						<?php
						printf( '<input class="poppy-input poppy-name" placeholder="%1$s" id="ajaxcontactname" type="text" name="%1$s">', $name );
						printf( '<input class="poppy-input poppy-email" placeholder="%1$s" id="ajaxcontactemail" type="text" name="%1$s">',$email );
						if ( ploption( 'poppy_enable_extra' ) && '' != ploption( 'poppy_extra_field' ) )
							printf( '<input class="poppy-input poppy-custom" placeholder="%1$s" id="ajaxcontactcustom" type="text" name="%1$s">', stripslashes( ploption( 'poppy_extra_field' ) ) );
						?>
					</div>
				</div>
			<div class="control-group">
				<div class="controls">
					<div class="textarea">
						<?php printf( '<textarea class="poppy-msg" row="8" placeholder="%s" id="ajaxcontactcontents" name="%s"></textarea>', $message, $message ); ?>
					</div>
				</div>
			</div>

			<?php if ( ploption( 'poppy_enable_captcha' ) ) $this->captcha(); ?>

			<div class="controls">
				<?php printf( '<a class="btn btn-primary send-poppy">%s</a>', $send ); ?>
			</div>
			</fieldset>
		</form>
	</div>
</div>
		<?php
		$form = ob_get_clean();
		echo $form;
	}


	function captcha() {

		$code = sprintf( '<div class="control-group">
		<label class="control-label">Captcha</label>
		<div class="controls">
			<input class="span2 poppy-captcha" placeholder="%s" id="ajaxcontactcaptcha" type="text" name="ajaxcontactcaptcha" />
		</div>
	</div>', stripslashes( ploption( 'poppy_captcha_question' ) ) );
	echo $code;
	}


	function admin_page() {

		if ( ! function_exists( 'ploption' ) )
			return;
		$option_args = array(

			'name'		=> 'Poppy',
			'array'		=> $this->options_array(),
			'icon'		=> $this->icon,
			'position'	=> 6
		);
		pl_add_options_page( $option_args );
	}

	function options_array() {

		$options = array(

			'poppy_options'	=> array(
				'type'	=> 'multi_option',
				'title'	=>	__( 'Poppy Options', 'pagelines-poppy' ),
				'shortexp'	=> __( 'Configure poppy popup forms, click more info for shortcode examples', 'pagelines-poppy' ),
				'layout'	=> 'full',
				'exp'	=> 'Here are a few examples:<br /><br /><strong>[poppy]</strong><br />Creates a standard button with the word "Contact"<br /><br /><strong>[poppy]Email me![/poppy]</strong><br />Same as above with custom text.<br /><br /><strong>[poppy type="a"]Contact me.[/poppy]</strong><br />This uses a standard HTML link.<br /><br /><strong>[poppy type="button" class="btn-important"]Email.[/poppy]</strong><br />Here we are using the bootstrap button, and adding a class.<br /><br /><strong>[poppy type="label" class="label-warning"]CONTACT[/poppy]</strong><br />What about a bootstrap label?<br /><br /><strong>[poppy type="i" class="icon-envelope icon-4x"]&nbsp;[/poppy]</strong><br />Finally a giant font-awesome envelope!',
				'selectvalues'	=> array(
					'poppy_form_title' => array(
						'type' 		=> 'text',
						'inputlabel'	=> __( 'Form Title', 'pagelines-poppy' ),
						'default'	=> __( 'Contact Us!', 'pagelines-poppy' ),
						'shortexp' => __( 'Main title for the form.', 'pagelines-poppy' )
						),
					'poppy_email'	=> array(
						'type'	=> 'text',
						'inputlabel'	=> __( 'Default email send address', 'pagelines-poppy' ),
						'exp'	=> __( 'Email address to send for To. Leave blank to use admin email', 'pagelines-poppy' )
						),
					'poppy_enable_extra'	=> array(
						'type'	=> 'check',
						'default'	=> false,
						'inputlabel'	=> __( 'Enable extra custom field', 'pagelines-poppy' )
						),
					'poppy_extra_field'	=> array(
						'type'	=> 'text',
						'default'	=> '',
						'inputlabel'	=> __( 'Extra field text', 'pagelines-poppy' )
						),
					'poppy_enable_captcha'	=> array(
						'type'	=> 'check',
						'default'	=> true,
						'inputlabel'	=> __( 'Enable simple antispam question', 'pagelines-poppy' )
						),
					'poppy_captcha_question'	=> array(
						'type'	=> 'text',
						'default'	=> '2 + 5',
						'inputlabel'	=> __( 'Antispam question', 'pagelines-poppy' )
						),
					'poppy_captcha_answer'	=> array(
						'type'	=> 'text',
						'default'	=> '7',
						'inputlabel'	=> __( 'Antispam answer', 'pagelines-poppy' )
						),
					'poppy_email_layout'	=> array(
						'type'	=> 'text',
						'inputlabel'	=> __( 'Format for email subject. Possible values: %name% %blog%', 'pagelines-poppy' ),
						'default'	=> '[%blog%] New message from %name%.',

						)
					)
				)
			);
	return $options;
	}

	function ajaxcontact_send_mail(){

		$data = stripslashes_deep( $_POST );

		$defaults = array(
			'name'	=> '',
			'email'	=> '',
			'custom'=> '',
			'msg'	=> '',
			'cap'	=> '',
			'width'	=> '',
			'height'=> '',
			'agent' => ''
		);

		$data = wp_parse_args($data, $defaults);

		$name			= $data['name'];
		$email			= $data['email'];
		$custom			= $data['custom'];
		$custom_field	= ( ploption( 'poppy_enable_extra' ) ) ? ploption( 'poppy_extra_field' ) : '';
		$contents		= $data['msg'];
		$admin_email	= ( ploption( 'poppy_email' ) ) ? ploption( 'poppy_email' ) : get_option( 'admin_email' );
		$captcha		= $data['cap'];
		$captcha_ans	= ploption( 'poppy_captcha_answer' );
		$width			= $data['width'];
		$height			= $data['height'];
		$ip				= $_SERVER['REMOTE_ADDR'];
		$agent			= $data['agent'];

		if ( ploption( 'poppy_enable_captcha' ) ){
			if( '' == $captcha )
				die( __( 'Captcha cannot be empty!', 'pagelines-poppy' ) );
			if( $captcha !== $captcha_ans )
				die( __( 'Captcha does not match.', 'pagelines-poppy' ) );
		}

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			die( __( 'Email address is not valid.', 'pagelines-poppy' ) );
		} elseif( strlen( $name ) == 0 ) {
			die( __( 'Name cannot be empty.', 'pagelines-poppy' ) );
		} elseif( strlen( $contents ) == 0 ) {
			die( __( 'Content cannot be empty.', 'pagelines-poppy' ) );
		}

		// create an email.
		$subject_template	= ( '' != ploption( 'poppy_email_layout' ) ) ? ploption( 'poppy_email_layout' ) : '[%blog%] New message from %name%.';
		$subject			= str_replace( '%blog%', get_bloginfo( 'name' ), str_replace( '%name%', $name, $subject_template ) );
		$custom 			= ( $custom_field ) ? sprintf( '%s: %s', $custom_field, $custom ) : '';
		$fields = 'Name: %s %7$sEmail: %s%7$sContents%7$s=======%7$s%s %7$s%7$sUser Info.%7$s=========%7$sIP: %s %7$sScreen Res: %s %7$sAgent: %s %7$s%7$s%8$s';

		$template = sprintf( $fields,
			$name,
			$email,
			$contents,
			$ip,
			sprintf( '%sx%s', $width, $height ),
			$agent,
			"\n",
			$custom
			);
		if( wp_mail( $admin_email, $subject, $template ) ) {
			die( 'ok' );
		} else {
			 die( __( 'Unknown wp_mail() error.', 'pagelines-poppy' ) );
		}
	}
}
new PageLinesPoppy;