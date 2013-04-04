<?php
/*
Plugin Name: Hooker
Description: Easily add any code anywhere using all the built in hooks with a simple gui.
Version: 1.3
Author: Simon Prosser
Demo:
Author URI: http://pross.org.uk
PageLines: true
*/

/*
Instructions for PHP users.

To enable PHP mode you must add this to wp-config.php:

define( 'PL_HOOKS_PHP', true );

*/

class PLHooks {

	function __construct() {

		if ( ! function_exists( 'ploption' ) )
			return;
		if ( ! current_user_can( 'edit_theme_options' ) )
			return;

		global $hooks_menu;
		$hooks_menu = pagelines_insert_menu( PL_MAIN_DASH, 'Hook Editor', 'edit_theme_options', 'pagelines_hooks', array( &$this, 'get_admin_page' ) );

		add_action( 'load-' . $hooks_menu, 'pagelines_theme_settings_scripts' );
		add_action( 'load-' . $hooks_menu, array( &$this, 'save_settings' ) );
		add_action( 'admin_head', array( &$this, 'head' ), 999 );
	}

	function front_end() {

		global $options;
		global $wp_admin_bar;
		if ( current_user_can( 'edit_theme_options' ) && is_object( $wp_admin_bar ) ) {

			$wp_admin_bar->add_menu( array( 'id' => 'pl_hooks', 'parent' => 'pl_settings', 'title' => __( 'Hooker', 'pagelines' ), 'href' => admin_url( 'admin.php?page=pagelines_hooks' ) ) );
		}

		$options = get_option( 'pl_hooks_editor', array() );
		if( ! is_array( $options ) )
			$options = array();

		foreach( $options as $hook => $data ) {
			$data['hook_id'] = $hook;
			if( isset( $data['content'] ) && '' != $data['content'] && isset( $data['enabled'] ) && 'on' == $data['enabled'] ) {

				$page = ( isset( $data['page_id'] ) ) ? $data['page_id'] : array();
				global $post;
				if( is_array( $page ) )
					$page = array_filter( $page );


				if ( is_array( $page ) && ! empty( $page ) ) {

					foreach( $page as $o => $id )
						if( $id == $post->ID ) {
							add_action( $data['hook'], create_function( '$hook', "PLHooks::run_action($hook);" ), $data['priority'] );
						}
				} else {
					add_action( $data['hook'], create_function( '$hook', "PLHooks::run_action($hook);" ), $data['priority'] );
				}
			}
		}
	}

	function run_action( $hook_id ) {

		global $options;
		if( ! is_array( $options ) )
			$options = array();
		$hook = $options[$hook_id]['hook'];
		$options[$hook_id]['hook_id'] = $hook_id;
		echo self::get_action_code( $options[$hook_id], $hook );
	}


	function get_action_code( $option, $hook ) {

		if( defined( 'PL_HOOKS_PHP') && '' != $option['content'] && isset( $option['php'] ) && $option['php'] ) {

			global $hook_name;
			global $hook_contents;
			global $hook_id;
			$hook_id = $option['hook_id'];

			$php = stripslashes( $option['content'] );

			$hook_name = $hook;
			$hook_contents = $php;

			ob_start( 'fatal_error_handler_hooks' );
			eval( "?>$php<?php " );
			ob_end_clean();

			ob_start();
			eval( "?>$php<?php " );
			$out = ob_get_contents();
			ob_end_clean();
			return $out;
		}
	return stripslashes( do_shortcode( $option['content'] ) );
	}

	function save_settings() {

		if( isset( $_POST['import-hooks'] ) ) {

			$hooks = pl_file_get_contents( $_FILES['file']['tmp_name'] );
			$hooks = json_decode( $hooks );
			if ( is_object( $hooks ) ) {
				update_option( 'pl_hooks_editor', json_decode( json_encode( $hooks ), true ) );
				wp_redirect( admin_url( 'admin.php?page=pagelines_hooks&imported=true' ) );
			}
		}

		if( isset( $_POST['export-hooks'] ) ) {

			$options = get_option( 'pl_hooks_editor', array() );

			if ( isset($options) && is_array( $options) ) {

				header( 'Cache-Control: public, must-revalidate' );
				header( 'Pragma: hack' );
				header( 'Content-Type: text/plain' );
				header( 'Content-Disposition: attachment; filename="hookers.pimp"' );
				echo json_encode( $options );
				exit();
			}
		}

		if( isset( $_POST['hooks-delete'] ) && '' != $_POST['hook'] ) {

			$options = get_option( 'pl_hooks_editor', array() );
			$hook = $_POST['hook'];
			if ( isset( $options[$hook] ) )
				unset( $options[$hook] );
			update_option( 'pl_hooks_editor', $options );
		return;
		}

		if( isset( $_POST['hooks-post'] ) && '' != $_POST['hook'] ) {
			$options = get_option( 'pl_hooks_editor', array() );

			if( ! isset( $_POST['action'] ) || '' == $_POST['action'] ) {
				$id = time();
				$hook = $_POST['hook'];
			} else {
				$id = $_POST['hook'];
				$hook = $_POST['action'];
			}


			$options[$id]['hook'] = $hook;
			$options[$id]['content'] = stripslashes( $_POST['content'] );

			if( isset( $_POST['php'] ) )
				$options[$id]['php'] = true;
			else
				$options[$id]['php'] = false;

			$options[$id]['error'] = false;

			if( isset( $_POST['priority'] ) )
				$options[$id]['priority'] = $_POST['priority'];

			if( isset( $_POST['enabled'] ) )
				$options[$id]['enabled'] = $_POST['enabled'];
			else
				$options[$id]['enabled'] = 'off';

			if( isset( $_POST['page_id'] ) )
				$options[$id]['page_id'] = explode( ',', $_POST['page_id'] );
		update_option( 'pl_hooks_editor', $options );
		}
	}

	function get_admin_page() {

		$args = array(
			'title'			=> __( 'Hook Editor', 'hooker' ),
			'show_save'		=> false,
			'show_reset'	=> false,
			'callback'		=> array( &$this, 'setup_admin_page' ),
		);
		$optionUI = new PageLinesOptionsUI($args);
	}

	function setup_admin_page() {

		return array(
			'hook_editor' => array(
				'icon'		=> PL_ADMIN_ICONS.'/extend-sections.png',
				'htabs' 	=> array(
					'Settings'	=> array(
					'title'		=> '',
					'callback'	=> $this->render_admin_page()
						)
					)
				),
			'import/export'	=> array(
				'icon'		=> PL_ADMIN_ICONS.'/extend-sections.png',
				'htabs' 	=> array(
					'Settings'	=> array(
					'title'		=> '',
					'callback'	=> $this->import_page()
						)
					)
				)
			);
	}

	function render_admin_page() {

		ob_start();
		$wp_hooks = $this->wp_hooks();

		// draw dropdown....
		global $options;
		$options = get_option( 'pl_hooks_editor', array() );
		echo'<p><select id="hooks" name="hooks">';
		printf( "<option name='none' id='none' value='none'>%s</option>", __( 'Add a new hook...', 'hooker' ) );
		printf( '<option disabled="disabled">%s</option>', __( 'WordPress Hooks', 'hooker' ) );
		foreach ( $wp_hooks as $k => $hook ) {
			echo "<option name='{$hook}' id='hook-{$hook}' value='{$hook}'>{$hook}</option>";
		}
		printf( '<option disabled="disabled">%s</option>', __( 'PageLines Hooks', 'hooker' ) );

		$hooks = json_decode( $this->get_pl_hooks() );

		foreach ( $hooks as $o => $hook ) {

			if( ! preg_match( '#[a-z]$#', $hook ) )
				continue;

			echo "<option name='{$hook}' id='hook-{$hook}' value='{$hook}'>{$hook}</option>";
		}
		printf( '<option disabled="disabled">%s</option>', __( 'PageLines Sections', 'hooker' ) );
		$sections = $this->get_section_hooks();
		foreach ( $sections as $o => $hook ) {

			if( ! preg_match( '#[a-z]$#', $hook ) )
				continue;

			echo "<option name='{$hook}' id='hook-{$hook}' value='{$hook}'>{$hook}</option>";
		}
		echo '</select></p>';
		if( ! empty( $options ) ) {
			foreach ( $options as $o => $hook ) {
				if( empty( $hook['content'] ) )
					unset( $options[$o]);
			}
		}
		if( ! empty( $options ) ) {
			echo'<p><select id="hooks_active" name="hooks_active">';
			printf( "<option name='none' id='none' value='none'>%s</option>", __( 'Edit hook...', 'hooker' ) );

			foreach ( $options as $o => $hook ) {
				$error = '';
				if( isset( $hook['error'] ) && '' != $hook['error'] )
					$error = sprintf( '[! %s !] ', __( 'Error', 'hooker' ) );

				if( isset( $hook['content'] ) && '' != $hook['content'] )
					echo "<option name='{$o}' id='hook-{$o}' value='{$o}'>{$error}{$hook['hook']}</option>";
			}
		echo '</select></p>';
	}
	$this->draw_options();
	$out = ob_get_clean();
	return $out;
	}


	function get_pl_hooks() {

		// see if we have hooks already....

		$url = 'api.pagelines.com/framework/hooks.php?api=1';
		if( $hooks = get_transient( 'pagelines_hooks' ) )
			return $hooks;
		$response = pagelines_try_api( $url, false );

		if ( $response !== false ) {
			if( ! is_array( $response ) || ( is_array( $response ) && $response['response']['code'] != 200 ) ) {
				$out = '';
			} else {
				$hooks = wp_remote_retrieve_body( $response );
				set_transient( 'pagelines_hooks', $hooks, 86400 );
				$out = $hooks;
			}
		}
	return $out;
	}

	function get_section_hooks() {

		global $load_sections;
		$available = $load_sections->pagelines_register_sections( false, true );

		$sections = array();
		foreach( $available as $type ) {
			foreach( $type as $key => $data ) {

				$sections[] = sprintf( 'pagelines_before_%s', basename( $data['base_dir'] ) );
				$sections[] = sprintf( 'pagelines_inside_bottom_%s', basename( $data['base_dir'] ) );
				$sections[] = sprintf( 'pagelines_after_%s', basename( $data['base_dir'] ) );
				$sections[] = sprintf( 'pagelines_outer_%s', basename( $data['base_dir'] ) );
			}
		}
	return $sections;
	}

	function draw_options() {

		$defaults = array(
			'content'	=> '',
			'php'		=> false,
			'priority'	=> '10',
			'error'		=> false,
			'page_id'	=> array(),
			'enabled'	=> 'on',
		);

		global $options;

		$wp_hooks = $this->wp_hooks();
		$hooks = $this->get_pl_hooks();
		$sections = $this->get_section_hooks();

		foreach( array_merge( $wp_hooks, json_decode( $hooks ), $sections ) as $k => $hook ) {

			if ( ! isset( $options[ $hook ] ) )
				$options[ $hook ] = $defaults;
		}

		// draw option boxes....

		foreach( $options as $o => $d ) {

			$d = wp_parse_args( $d, $defaults );

			echo '</form><form action="" method="post">';
			echo "<div id='hook_{$o}' class='hook_hide'><textarea id='{$o}' name='content'>{$d['content']}</textarea>";
			echo "<input type='hidden' name='hook' value='{$o}' />";

			if( isset( $d['enabled'] ) && 'on' === $d['enabled'] )
				$check = 'checked';
			else
				$check = 'unchecked';
			printf( "<br /><input type='checkbox' name='enabled' %s /> %s", $check, __( 'Enable this hook.', 'hooker' ) );

			if ( defined( 'PL_HOOKS_PHP' ) ) {

				if( isset( $d['error'] ) && false != $d['error'] )
					printf( "<p><span style='color:red' >%s</span><br >%s <strong>%s</strong></p>",
					__( 'This hook generated a PHP Fatal Error the last time it was executed.', 'hooker' ),
					__( 'The error reported was:', 'hooker' ),
					$d['error']
					);

				if( isset( $d['php'] ) && true === $d['php'] )
					$check = 'checked';
				else
					$check = 'unchecked';
				printf( "<br /><input type='checkbox' name='php' %s /> %s.",
					$check,
					__( 'Enable PHP for this hook', 'hooker' )
					);
			}
			printf( "<br /><input  name='priority' value='{$d['priority']}' /> %s", __( 'Priority. (default is 10)', 'hooker' ) );
			printf( '<br /><input name="page_id" value="%s" /> %s.',
				implode( ',', $d['page_id'] ),
				__( 'Only show on these pages/posts (coma seperated list of IDs)', 'hooker' )
				);
			printf( '<p><input type="submit" name="hooks-post" value="%s" class="button-primary" />', __( 'Save Hook', 'hooker' ) );

			if( '' != $d['content'] ) {

				$h = str_replace( '-', '_', $d['hook'] );
				printf( "<input type='submit' name='hooks-delete' value='%s' class='button-primary' onClick='return ConfirmRestore();'/>", __( 'Delete Hook', 'hooker' ) );
				pl_action_confirm( 'ConfirmRestore', "Are you sure??\n\nThis will delete this hook.\n\nDont be complaining after you have deleted it!!" );
			}
			if ( isset( $d['hook'] ) )
				echo "<input type='hidden' name='action' value='{$d['hook']}' />";
		echo '</p></div>';
		}

			?>

			<div class="hook_welcome" >
				<h2>Hook Editor instructions</h2>
				<p>
					Select the action/hook you want to edit from the dropdown above.<br />
					Add your HTML/JS/CSS to the textarea.<br />
					Change the priority of the hook.<br />
					Click save.<br />
					Thats it!!<br />
				</p>

<?php if( defined( 'PL_HOOKS_PHP' ) && PL_HOOKS_PHP ) {

	?>
	<h3>Additional instructions for PHP users.</h3>
	<p>
		You will have an extra checkbox to enable PHP execution for the hook.<br />
		<strong>IMPORTANT</strong> Make sure your PHP code is properly surrounded by tags.<br />
		It MUST start and end with PHP tags or errors will occur. Heres an example:<br />
&lt;?php<br />
&nbsp;&nbsp;&nbsp;&nbsp;echo 'hello';<br />
?&gt;

<?php }

echo '</div></form>';

	}

	function import_page() {

		ob_start();

		if( isset( $_GET['imported'] ) && 'true' == $_GET['imported'] )
			echo '<div><p>Import was successfull!!</p></div>';

		echo '</form><form enctype="multipart/form-data" action="" method="post">';

		echo '<div><p>';

				printf( "<input type='submit' name='export-hooks' value='Export Hooks' class='button-primary' /> %s.</form>", __( 'Export your hooks to a file', 'hooker' ) );

				echo "<form enctype='multipart/form-data' action='' method='post'><input type='submit' name='import-hooks' value='Import Hooks' class='button-primary' onClick='return ConfirmImport();' />";
				echo '<input type="file" class="file_uploader text_input" name="file" id="hooks-file" />';

				pl_action_confirm( 'ConfirmImport', "Are you sure??\n\nThis will import hooks from a file, and overwrite any existing hooks." );

		echo '</p></div></form>';

		return ob_get_clean();
	}

	function head() {

		// JS
		$page = get_current_screen();
		if ( 'pagelines_page_pagelines_hooks' == $page->id ) :
?>
<script type="text/javascript">
jQuery(document).ready(function() {
<?php
		global $options;
		$options = get_option( 'pl_hooks_editor', array() );
		if( ! empty( $options ) ) {
			foreach( $options as $hook => $data ) {
				if( isset( $data['content']) && '' != $data['content'] )
					echo "var myCodeMirror_{$hook} = CodeMirror.fromTextArea(document.getElementById('{$hook}'), cm_customcss)\n";
			}
		} ?>
});
</script>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('.hook_welcome').show()
		jQuery('.hook_hide').hide()
		jQuery('#hooks').change(function() {
			jQuery('.hook_hide').hide()
			jQuery('.hook_welcome').hide()
			jQuery('#hook_' + jQuery(this).val()).show()
		});
		jQuery('#hooks_active').change(function() {
			jQuery('.hook_hide').hide()
			jQuery('.hook_welcome').hide()
			jQuery('#hook_' + jQuery(this).val()).show()
		});
	});
</script>
<?php
	endif;
	}

	function wp_hooks() {

		return array(
			'wp_head',
			'wp_footer',
			'get_search_form',
			'wp_meta',
			'get_sidebar',
			'dynamic_sidebar',
			'the_post',
			'loop_start',
			'loop_end'
			);
	}
} // end PLHooks class

add_action('admin_menu', 'setup_pl_hooker', 15);
function setup_pl_hooker() {

	new PLHooks;
}

add_action( 'template_redirect', array( 'PLHooks', 'front_end') );

function fatal_error_handler_hooks($buffer){
	global $hook_name;
	global $hook_contents;
	global $hook_id;
    $error=error_get_last();

    if($error['type'] == 1){
        // type, message, file, line
        $newBuffer='<html><header><title>Error</title></header>
                    <style>
                    .error_content{
                        background: ghostwhite;
                        vertical-align: middle;
                        margin:0 auto;
                        padding:10px;
                        width:50%;
                     }
                     .error_content label{color: red;font-family: Georgia;font-size: 16pt;font-style: italic;}
                     .error_content ul li{ background: none repeat scroll 0 0 FloralWhite;
                                border: 1px solid AliceBlue;
                                display: block;
                                font-family: monospace;
                                padding: 2%;
                                text-align: left;
                      }
                    </style>
                    <body style="text-align: center;">
                      <div class="error_content">
                          <label>An action has resulted in a Fatal error!</label>
                          <ul>
                          	<li><b>Hook: </b>' . $hook_name . '</li>
                            <li><b>Message: </b> '.$error['message'].'</li>
                          </ul>
                          <textarea>' . $hook_contents .' </textarea>

                          <br />
                          <p>
                          <strong>PHP execution for this hook has now been disabled.</strong></p>

                      </div>
                    </body></html>';

        $options = get_option( 'pl_hooks_editor', array() );
        $options[$hook_id]['php'] = false;
        $options[$hook_id]['error'] = $error['message'];
        update_option( 'pl_hooks_editor', $options );

        return $newBuffer;

    }
    return $buffer;
}