<?php
/*
Plugin Name: LESS Developer
Author: Evan Mattson
Version: 1.1.1
Description: A powerful plugin that makes the PageLines LESS compiler very accessible and easy to use.  Great for development & testing, as well as managing your custom LESS/CSS files.
Plugin URI: http://evanmattson.pagelines.me/plugins/less-developer
Author URI: http://evanmattson.pagelines.me
Demo: http://evanmattson.pagelines.me/plugins/less-developer#demo
External: http://evanmattson.pagelines.me/plugins/less-developer
PageLines: true
Tags: LESS, extension
*/


class LessDeveloperPlugin {

	const ver = '1.1.1';

	var $pl_integration;
	var $codeglow;
	var $requirements;
	private $resources;
	private $sandbox;

	function __construct() {

		$this->slug    = 'less-developer';
		$this->_slug   = str_replace( '-', '_', $this->slug );
		$this->name    = 'LESS Developer';
		$this->path    = sprintf( '%s/%s', WP_PLUGIN_DIR, $this->slug );
		$this->uri     = sprintf( '%s/%s', WP_PLUGIN_URL, $this->slug );
		$this->sandbox = WP_CONTENT_DIR.'/less-dev';
		$this->resources = array();

		$this->option_defaults = array(
			'less' => array(
				'data'         => '',
				'current_file' => '',
			),
			'css'  => array(
				'data'         => '',
				'current_file' => '',
			),
		);

		if ( is_admin() )
			$this->admin_actions();
	}

			################### ACTIONS ###################
	
	/*
	add_action( '___',	array(&$this, '___'), 10 	);
	add_filter( '___',	array(&$this, '___'), 10, 1 );
	*/

	// admin only
	function admin_actions() {
		add_action( 'pagelines_setup',				array(&$this, 'integration_checks') );
		add_action( 'wp_ajax_lessdev_live_compile',	array(&$this, 'live_compile') );
		add_action( 'wp_ajax_lessdev_load_file',	array(&$this, 'load_file') );
		add_action( 'wp_ajax_lessdev_save_file',	array(&$this, 'save_file') );
		add_action( 'wp_ajax_lessdev_refresh_files',array(&$this, 'refresh_select_options') );
	}

	function register_less() {
		register_lessdev_dir( 'aaemnnosttv', $this->slug, $this->name, $this->path.'/css' );
	}

	/**
	 * Option Page-Only Actions
	 *
	 * Only fires if minimum requirements are met
	 */
	function option_page_actions() {

		if ( isset($_GET['reset']) && $_GET['reset'] ) {
			$this->reset_option();
			wp_safe_redirect( admin_url( 'admin.php?page='.$this->slug ) );
			exit;
		}

		$this->register_resources();

		add_action( 'admin_enqueue_scripts',		array(&$this, 'option_page_enqueue_scripts'),	10	);
		add_action( 'admin_print_styles',			array(&$this, 'print_dynamic_css')					);
		add_action( 'admin_print_footer_scripts',	array(&$this, 'print_zeroclip_js'),				20	);

		// add contextual help
		$screen = get_current_screen();

		if ( $screen->id == $this->hook ) {
			$screen->add_help_tab( array(
				'id'      => 'lessdev_overview',
				'title'   => 'Less Developer Overview',
				'content' => self::get_help_tab_content('overview'),
			) );
		}
	}

	function option_page_enqueue_scripts() {

		wp_enqueue_script( 'zero-clipboard',	$this->uri.'/js/ZeroClipboard.min.js', array(), '1.0.8' );
		wp_enqueue_script( 'lessdev',			$this->uri.'/js/lessdev.js', array('jquery','codemirror','zero-clipboard'), self::ver );
		wp_enqueue_style ( 'lessdev',			$this->uri.'/css/lessdev.css', array(), self::ver );

		if ( $this->codeglow ) {
			register_codeglow_editor('lessdev_less', array(
				'config' => 'mode=less',
				'id'     => 'less_editor',
				'hooks'  => $this->hook ));
			register_codeglow_editor('lessdev_css', array(
				'config' => 'mode=css',
				'id'     => 'css_editor',
				'hooks'  => $this->hook ));
		}
		else {
			// codemirror core
			wp_enqueue_style(  'codemirror',		PL_ADMIN_JS . '/codemirror/codemirror.css' );
			wp_enqueue_script( 'codemirror',		PL_ADMIN_JS . '/codemirror/codemirror.js' );
			// modes
			wp_enqueue_script( 'codemirror-less',	PL_ADMIN_JS . '/codemirror/less/less.js' );
			wp_enqueue_script( 'codemirror-css',	PL_ADMIN_JS . '/codemirror/css/css.js' );
			// codemirror activation
			add_action( 'admin_print_footer_scripts', array(&$this, 'print_codemirror_js') );
		}

	}

	
	function integration_checks() {

		$this->maybe_define_constants();

		// dependancy
		$this->codeglow       = isset( $GLOBALS['codeglow'] ) && is_object( $GLOBALS['codeglow'] );
		$this->pl_integration = version_compare( PL_CORE_VERSION, '2.3', '>=');

		// child theme
		$this->is_pl_child_theme = (bool) ( 'pagelines' == wp_get_theme()->template );

		// minimum requirements check
		if ( $this->pl_integration )
			$this->init();
		else
			$this->fallback_init();
	}

	function maybe_define_constants() {
		$constants = array(
			'PL_CORE_VERSION' => 'CORE_VERSION',
			'PL_THEMENAME'    => 'THEMENAME',
			'PL_CHILD_DIR'    => get_stylesheet_directory(),
			'PL_CHILD_LESS'   => get_stylesheet_directory().'/less'
		);

		foreach ( $constants as $namespaced => $deprecated ) {
			if ( ! defined( $namespaced ) ) {
				
				$value = ( defined( $deprecated ) ) ? constant( $deprecated ) : $deprecated;
				define( $namespaced, $value );
			}
		}
	}

	/**
	 * Initialize plugin
	 * Minimum requirements are met
	 */
	function init() {

		$this->register_less();

		add_action( 'admin_menu',	array(&$this, 'add_admin_menu') );
	}
	/**
	 * Uh oh.
	 */
	function fallback_init() {
		
		ob_start();
		?>
		<div class="updated fade">
			<p style="text-align:center;"><strong>LESS Developer requires at least PageLines Framework v2.3+</strong></p>
		</div>
		<?php
		
		$message = ob_get_clean();

		add_action( 'admin_notices', create_function('', "echo '$message';" ) );
	}

	/**
	 * Adds top level menu for our option page
	 * @uses  $codeglow
	 *
	 * @todo  possibly change the way extended codeglow editors are added.
	 */
	function add_admin_menu() {
		$this->hook = add_menu_page( $this->name, $this->name, 'edit_posts', $this->slug, array(&$this, 'build_interface'), $this->uri.'/img/icon.png' );
		add_action( "load-{$this->hook}", 	array(&$this, 'option_page_actions') );
	}

	/**
	 * Adds files to main array of resources
	 * 
	 * @param  string 	$handle   unique identifier / resource slug
	 * @param  string 	$name     UI / Display name
	 * @param  string 	$path     absolute path
	 * @param  array 	$sub_dirs array of sub directories (subhandle => path_relative_to_handle_root )
	 */
	function register_resource( $handle, $name, $path, $sub_dirs = array() ) {

		$this->_register_resource( $handle, $name, $path );

		foreach ( $sub_dirs as $key => $relative )
			$this->_register_resource( "_{$handle}_$key", $relative, "$path$relative" );
	}

	private function _register_resource( $handle, $name, $path ) {
		$this->resources[ $handle ] = array(
			'path'     => $path,
			'name'     => $name,
			'files'    => array()
		);
	}

	private function register_resources() {

		// create less-dev dir if it doesn't exist
		/*if ( !file_exists( $this->sandbox ) ) 
			mkdir( $this->sandbox );*/

		$this->register_resource( $this->_slug, 'LESS Developer Sandbox', $this->sandbox );
		$this->register_resource( 'pl_customize', 'PageLines Customize Plugin', EXTEND_CHILD_DIR );

		// only enable for pagelines child themes
		if ( $this->is_pl_child_theme ) {

			$child_sub_dirs = array();

			if ( file_exists( PL_CHILD_LESS ) )
				$child_sub_dirs['less'] = '/less';

			$child_sections_path = PL_CHILD_DIR.'/sections';

			if ( file_exists( $child_sections_path ) ) {
				foreach ( scandir( $child_sections_path ) as $file ) {
					
					$filepath = "$child_sections_path/$file";

					// filter - directories only
					if ( !is_dir( $filepath ) || in_array( $file , array('.','..') ) )
						continue;

					// dir slug
					$dir = basename( $file );
					// directory path relative 
					$relative = str_replace( PL_CHILD_DIR, '', $filepath );
					// add to array
					$child_sub_dirs[ "section_$dir" ] = $relative;
				}
			}

			$this->register_resource( 'pl_child_theme', wp_get_theme()->name, PL_CHILD_DIR, $child_sub_dirs );

		}
		
		$this->register_extended_resources();
	}

	private function register_extended_resources() {

		global $lessdev_ext;

		$on    = (bool) ( defined('LESSDEV_EXT_DIRS') && LESSDEV_EXT_DIRS );
		$group = defined('LESSDEV_EXT_GROUP') ? LESSDEV_EXT_GROUP : null;

		$ext_resources = ( isset( $lessdev_ext[ $group ] ) && is_array( $lessdev_ext[ $group ] ) )
					? $lessdev_ext[ $group ]
					: null;

		if ( !empty( $ext_resources ) )
			foreach ( $ext_resources as $handle => $a )
				$this->register_resource( $handle, $a['name'], $a['path'], $a['sub_dirs'] );
	}

	function refresh_resources() {
		$this->resources = array();
		$this->register_resources();
		$this->load_files();
	}

	/**
	 * Populates master resource file list
	 */
	private function load_files() {

		foreach ( $this->resources as $handle => $a )
			$this->add_files( $handle );
	}

	/**
	 * Scans a given directory & adds found files to master files array
	 * @param string $handle 	array key for resource
	 */
	private function add_files( $handle ) {

		// checks
		if ( ! isset( $this->resources[ $handle ]['path'] ) || ! file_exists( $this->resources[ $handle ]['path'] ) )
			return;

		$exts  = array( 'css', 'less' );

		$root  = $this->resources[ $handle ]['path'];
		$files = scandir( $root );

		if ( ! empty( $files ) ) {

			foreach ( $files as $filename ) {

				$filepath = "$root/$filename";

				if ( false === strpos( $filename, '.' ) )
					continue;

				$n    = explode( '.', $filename );
				$bits = count( $n );
				$ext  = $n[ $bits -1 ]; // last


				if ( in_array( $ext, $exts ) )
					$this->resources[ $handle ]['files'][ $ext ][] = $filename;
			}
		}
	}

	/**
	 * Helper for processing $_POST array using just an array of keys to check
	 *
	 * @param $a (array) expected POST array keys to prepare & return
	 * 
	 * @return (array) slash-stripped POST value or FALSE if not set in POST
	 */
	function process_post( $a = array() ) {

		$new = array();
		foreach ( (array) $a as $key )
			$new[ $key ] = isset( $_POST[ $key ] ) ? stripslashes( $_POST[ $key ] ) : false;
		
		return $new;
	}

	/**
	 * AJAX Callback
	 *
	 * COMPILE
	 */
	function live_compile() {

		global $wpdb; // this is how you get access to the database

		extract( self::process_post( array('input','less_file','css_file') ) );

		// compile it
		$compiled = self::compile_less( $input );

		// setup response
		$r             = self::new_ajax_response();
		$r['compiled'] = $compiled;
		$r['message']  = 'LESS Compiled Successfully.';
		$r['success']  = true;
		// echo encoded response
		self::encode_response( $r );

		// save stuff
		$o                         = $this->get_option();
		$o['less']['data']         = $input;
		$o['less']['current_file'] = $less_file;
		$o['css']['data']          = $compiled;
		$o['css']['current_file']  = $css_file;

		update_option( $this->_slug, $o );

		die(); // this is required to return a proper result
	}	

	/**
	 * AJAX Callback
	 *
	 * LOAD
	 */
	function load_file() {
		
		extract( self::process_post( array('file','editor') ) );
		// $file - filepath
		// $editor - less/css

		$data = file_get_contents( $file );

		$r = self::new_ajax_response();

		if ( false !== $data ) {

			$r['success']  = true;
			$r['message']  = 'File loaded successfully.';
			$r['filedata'] = $data; // loaded file data - less/css
			
			// refresh & update option
			$o = $this->get_option();
			$o[ $editor ]['data'] = $data;
			$o[ $editor ]['current_file'] = $file;

			update_option( $this->_slug, $o );
		}
		else {
			$r['message']  = 'File not found.';
		}

		self::encode_response( $r );

		die(); // this is required to return a proper result
	}	

	/**
	 * AJAX Callback
	 *
	 * SAVE
	 */
	function save_file() {
		
		extract( self::process_post( array('file','new_data','ext') ) );

		$r = self::new_ajax_response();

		if ( file_exists( $file ) ) {

			if ( is_writable( $file ) ) {
				// check if present in $_POST
				if ( false !== $new_data ) {

					// write new file content
					// update some option info?
					/*$info = array(
						'file' => $file,
						'new'  => $new_data
					);*/

					// backup with a 1 week transient - just in case
					// $backup = file_get_contents( $file );
					// set_transient( 'lessdev_file_backup_'.time(), $backup, 60*60*24*7 );

					if ( false !== file_put_contents( $file, $new_data, LOCK_EX ) ) {

						$r['success'] = true;
						$message      = 'File updated successfully.';
						
						$o                         = $this->get_option();
						$o[ $ext ]['data']         = $new_data;
						$o[ $ext ]['current_file'] = $file;

						update_option( $this->_slug, $o );
					}
					else {
						$message = 'File could not be updated. Try again.';
					}

				}
				else {
					$message = 'New file content error.';
				}
			}
			else {
				$message = 'File is not writable.';
			}
		}
		else {
			$message = 'File not found.';
		}

		$r['message'] = $message;

		self::encode_response( $r );

		die(); // this is required to return a proper result
	}

	/**
	 * AJAX Callback
	 *
	 * REFRESH
	 */
	function refresh_select_options() {

		$o = $this->get_option();
		$p = self::process_post( array('current_less_file','current_css_file') );
		$saved = array(
			'current_less_file' => $o['less']['current_file'],
			'current_css_file'  => $o['css']['current_file']
		);
		extract( wp_parse_args( $p, $saved ) );		

		$r = self::new_ajax_response(null, true);

		$this->refresh_resources();

		$r['less_options'] = $this->load_file_dropdown_options('less', $current_less_file);
		$r['css_options']  = $this->load_file_dropdown_options('css', $current_css_file);
		$r['message'] = 'File Lists Refreshed Successfully.';

		self::encode_response( $r );

		die(); // this is required to return a proper result
	}

	/**
	 * Compile Helper
	 */
	function compile_less( $input ) {
		if ( ! empty($input) ) {
			$pless = new PagelinesLess();
			$compiled = $pless->raw_less( $input, 'lessdev' );
		}
		else {
			$compiled = '';
		}

		return $compiled;
	}

	function print_codemirror_js() {
		?>
		<script type="text/javascript">
		var lessdev_less_editor = CodeMirror.fromTextArea(document.getElementById("less_editor"), {"mode":"less","lineWrapping":true,"lineNumbers":true,"matchBrackets":true,"indentUnit":4,"indentWithTabs":true,"tabSize":4} );
		var lessdev_css_editor = CodeMirror.fromTextArea(document.getElementById("css_editor"), {"mode":"css","lineWrapping":true,"lineNumbers":true,"matchBrackets":true,"indentUnit":4,"indentWithTabs":true,"tabSize":4} );
		</script>
		<?php
	}

	function print_dynamic_css() {
		
		// font-awesome
		$font_dir  = get_template_directory_uri().'/fonts';
		$font_file = version_compare(PL_CORE_VERSION, '2.4', '<') ? 'plfont-regular' : 'fontawesome-webfont';
		$font_path = "$font_dir/$font_file";

		?>
		<style type="text/css">
		@font-face {
			font-family: 'PageLinesFont';
			src: url('<?php echo $font_path; ?>.eot');
			src: url('<?php echo $font_path; ?>.eot?#iefix') format('embedded-opentype'),
				 url('<?php echo $font_path; ?>.woff') format('woff'),
				 url('<?php echo $font_path; ?>.ttf') format('truetype'),
				 url('<?php echo $font_path; ?>.svg') format('svg');
			font-weight: normal;
			font-style: normal;
		}

		[class^="icon-"]:before,
		[class*=" icon-"]:before {
		  font-family: PageLinesFont;
		  font-weight: normal;
		  font-style: normal;
		  display: inline-block;
		  text-decoration: inherit;
		}
		.icon-refresh:before              { content: "\f021"; }
		.icon-fullscreen:before           { content: "\f0b2"; }
		.icon-resize-full:before          { content: "\f065"; }
		.icon-resize-small:before         { content: "\f066"; }
		.icon-columns:before              { content: "\f0db"; }
		.icon-resize-horizontal:before    { content: "\f07e"; }
		.icon-external-link:before        { content: "\f08e"; }
		.icon-circle-blank:before         { content: "\f10c"; }
		.icon-repeat:before               { content: "\f01e"; }
		</style>
		<?php
	}

	function encode_response( $r ) {

		$a = array();

		if ( 'string' == gettype( $r ) )
			$a['text'] = $r;

		// return it!
		echo json_encode( (object) $r );
	}

	/**
	 * Loads controls
	 */
	function load_controls( $ext = 'less', $reverse = false ) {

		$resources    = $this->resources;
		$uri          = $this->uri;
		$o            = $this->get_option();

		$elements = array();

		$elements['select'] = $this->load_file_dropdown( $ext );
		$elements['action_buttons']['refresh'] = "<a class='button-secondary action refresh' data-ext='$ext' data-action='refresh' title='Refresh file list'><i class='icon-refresh'></i></a>";
		$elements['action_buttons']['load']    = "<input type='button' data-action='load' data-ext='$ext' class='button-secondary action load' value='Load' />";
		$elements['action_buttons']['clear']   = "<input type='button' data-action='clear' data-ext='$ext' class='button-secondary action clear' value='Clear' />";
		$elements['action_buttons']['edit']    = "<input type='button' data-action='edit' data-ext='$ext' class='button-primary action edit' value='Edit' />";
		$elements['action_buttons']['save']    = "<input type='button' data-action='save' data-ext='$ext' class='button-primary action save' value='Save' />";
		$elements['action_buttons']['cancel']  = "<input type='button' data-action='cancel' data-ext='$ext' class='button-secondary action cancel' value='Cancel' />";
		$elements['loading'] = "<img src='$uri/img/89.gif' id='{$ext}_loading' class='loading' />";

		if ( $reverse ) {
			$elements['action_buttons'] = array_reverse( $elements['action_buttons'] );
			$elements = array_reverse( $elements );
		}

		$elements['action_buttons'] = join( "\n", $elements['action_buttons'] );

		return join( "\n", $elements );
	}

	function load_file_dropdown( $ext ) {

		$o            = $this->get_option();
		$indent       = '&nbsp;';
		$current_file = $o[ $ext ]['current_file'];

		$e            = array();
		$e['current'] = "<input type='hidden' id='current_{$ext}_file' value='{$current_file}' />";
		$e['open']    = "<select id='lessdev_{$ext}_files' class='$ext file-select code'>";
		$e['options'] = $this->load_file_dropdown_options( $ext, $current_file );
		$e['close']   = '</select>';
		
		return join( "\n", $e );
	}

	function load_file_dropdown_options( $ext, $current_file = false ) {

		$resources = $this->resources;
		$indent    = '&nbsp;';

		// begin
		$e = array();
		foreach ( $resources as $handle => $r ) {

			if ( !isset( $r['files'][ $ext ] ) || empty( $r['files'][ $ext ] ) )
				continue; // skip to next iteration

			// title/seperator option
			$e[] = "<option disabled='disabled' value=''>".esc_attr($r['name'])."</option>";
			
			foreach ( $r['files'][ $ext ] as $filename ) {

				$path     = $r['path'];
				$filepath = "$path/$filename";
				$selected = selected( $filepath, $current_file, false );

				$e[] = "<option value='$filepath' $selected>$indent$filename</option>";
			}
		}

		// first option
		$first = sprintf('<option value="%s" disabled="disabled" %s></option>',
			empty( $e ) ? 'No '.strtoupper($ext).' files found' : '',
			selected( $current_file, false, false )
		);

		array_unshift( $e, $first );

		return join( "\n", $e );
	}

	function new_ajax_response( $a = array(), $success = false ) {
		$d = array(
			'message' => 'Error.', // for alert - should be overriden
			'success' => $success, // true should be set on success, if it wasn't, something went wrong.
		);

		$new = is_array( $a ) ? wp_parse_args( $a, $d ) : $d;

		return $new;
	}

	function reset_option() {
		if ( !current_user_can( 'administrator' ) )
			return;

		update_option( $this->_slug, $this->option_defaults );
	}

	/**
	 * Helper for getting saved options
	 */
	function get_option() {
		$o = get_option( $this->_slug, array() );
		return wp_parse_args( $o, $this->option_defaults );
	}

	function build_interface() {

		$o = $this->get_option();
		$this->load_files();

		?>
		<div class="wrap">
			<h2 class="plugin-title">LESS Developer <span class="version">v<?php echo self::ver; ?></span><img id="compiling" src="<?php echo $this->uri.'/img/285.gif'; ?>" style="display: none;" /></h2>
			<div class="toggles">
				<a data-action="columns" class="button-secondary" title="Column Mode"><i class="icon-columns"></i></a>
				<a data-action="full-width" class="button-secondary" title="Full-Width Mode"><i class="icon-resize-horizontal"></i></a>
				<a data-action="link" href="http://leafo.net/lessphp/docs/" target="_blank" class="button-secondary" title="LESS Documentation"><i class="icon-external-link"></i></a>
				<a data-action="reset" href="<?php echo add_query_arg( array('reset'=>1) ); ?>" class="button-secondary reset-btn" title="Reset Editors"><i class="icon-repeat"></i></a>
			</div>

			<!-- message -->
			<div class="message-container">
				<div id="message" style="display:none;"></div>
			</div>

			<!-- 2 column container -->
			<div id="lessdev" class="lessdev-wrap">
				
				<!-- LESS -->
				<div id="LESS" class="less-container">
					<div class="controls">
						<?php echo $this->load_controls('less'); ?>

						<div class="area-title">
							<h3 class="title">LESS</h3>
						</div>
					</div>
					<div class="clear"></div>
					<textarea id="less_editor"><?php echo $o['less']['data']; ?></textarea>
				</div>

				<!-- CSS -->
				<div id="CSS" class="css-container">
					<div class="controls">
						<div class="area-title">
							<h3 class="title">CSS</h3>
						</div>
						<?php echo $this->load_controls('css', true); ?>
					</div>
					<div class="clear"></div>
					<textarea id="css_editor"><?php echo $o['css']['data']; ?></textarea>
				</div>
				<div class="clear"></div>

				<!-- footer -->
				<div class="lessdev-footer">
					<div id="copy_css_container" style="position:relative;">
						<input type="button" id="copy_css" class="button-secondary" value="Copy to Clipboard" />
					</div>
					<div class="instructions">
						<p>Simply enter your LESS in the editor on the left, and click the COMPILE button (or press <code>CTRL+ENTER</code>) to compile it!</p>
					</div>
					<input type="button" name="submit" id="compile" class="button-primary" value="COMPILE" title="LUDICRIS SPEED: GO!">
				</div>

			</div>
			<?php
				printf('<div class="ext-dirs-info"><a href="%s" target="_blank">extended directories</a>: <span class="status">%s</span></div>',
					'http://bit.ly/how-to-use-less-developer-with-your-project',
					( defined('LESSDEV_EXT_DIRS') && LESSDEV_EXT_DIRS ) ? 'on' : 'off'
				);
			?>
		</div>
		<?php
	}

	function print_zeroclip_js() {
		?><script type="text/javascript"><?php
			printf( 'ZeroClipboard.setMoviePath( \'%s/js/ZeroClipboard.swf\' );', $this->uri );
		?></script><?php
	}

	function get_help_tab_content( $tab ) {

		ob_start();

		// easy add more later
		switch ( $tab ) :

			case 'overview': ?>
				<h3>LESS Developer</h3>
				<p>LESS Developer makes managing your custom LESS/CSS files for your site easy by making them all available in one place.</p>
				<p>The plugin will automatically scan your WP installation for directories that are known to have these customization files.</p>
				<p>Scanned directories include:</p>
				<ul>
					<li>The active child theme's root directory
						<ul>
							<li>Child theme LESS overrides <code>/less</code></li>
							<li>Child theme sections <code>/sections/{section-name}/</code></li>
						</ul>
					</li>
					<li>PageLines Customize plugin directory <code>/wp-content/plugins/pagelines-customize/</code></li>
					<li>LESS Developer Sandbox <code>/wp-content/less-dev/</code></li>
				</ul>
				<p>Core PageLines directories are not scanned as those files are not intended to be manipulated.</p>

				<h4>File Actions</h4>

				<p>The only file actions made available in the plugin are essentially loading, editing, and saving (updating).
					File creation, renaming, deletion and other file-related actions are currently not part of the plugin's scope and capabilities.</p>

				<h4>Loading</h4>
				<p>To begin working with a file, it should first be loaded by selecting it from the appropriate dropdown menu, and clicking it's <code>Load</code> button.</p>
				<p>Once loaded, the file can then be modified as desired.  <strong>Note: Compiling alone does not save/update file data.</strong></p>
				
				<h4>Editing &amp; Saving</h4>
				<p>When the file is ready to be saved, click the <code>Edit</code> button to enable/arm the ability to save the file.
					It's a good idea to arm it once the file is loaded to prevent the dropdown from changing files and accidenally overwriting another file.
					Once the file is armed, you'll notice that the file dropdown menu for that extension is disabled to prevent this.
					To enable it again, you can click cancel, or if you have just made a save, you can click <code>Unlock</code>.
				</p>
			<?php
			break;

		endswitch;

		return ob_get_clean();
	}

}

##############################################
$GLOBALS['lessdev'] = new LessDeveloperPlugin;
##############################################

function register_lessdev_dir( $group, $handle, $name, $path, $sub_dirs = array() ) {

	if ( !defined('LESSDEV_EXT_DIRS') || !LESSDEV_EXT_DIRS )
		return;

	global $lessdev_ext;
	
	if ( ! is_dir( $path ) )
		return;

	if ( ! is_array( $lessdev_ext ) )
		$lessdev_ext = array();

	$args = array(
		'name'     => $name,
		'path'     => $path,
		'sub_dirs' => $sub_dirs
	);

	$lessdev_ext[ $group ][ $handle ] = $args;
}
