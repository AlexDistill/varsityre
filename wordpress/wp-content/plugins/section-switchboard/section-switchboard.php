<?php
/*
Plugin Name: Section Switchboard
Version: 1.2.3
Author: Evan Mattson
Description: Rapid Section Configuration Utility.
Plugin URI: http://evanmattson.pagelines.me/plugins/section-switchboard/
Author URI: http://evanmattson.pagelines.me
External: http://evanmattson.pagelines.me/plugins/section-switchboard/
Demo: http://evanmattson.pagelines.me/plugins/section-switchboard/
PageLines: true
Tags: section management, extension
*/

class SectionSwitchboard {

	const ver = '1.2.3';

	var $updated;
	var $section_types;
	var $section_defaults;

	function __construct() {

		$this->name = 'Section Switchboard';
		$this->slug = basename( dirname( __FILE__ ) );
		
		$this->path = sprintf( '%s/%s', WP_PLUGIN_DIR, $this->slug );
		$this->uri  = sprintf( '%s/%s', WP_PLUGIN_URL, $this->slug );

		add_action( 'pagelines_setup',			array(&$this, 'register_less') );
		add_action( 'admin_menu',				array(&$this, 'add_section_switchboard'), 12);
		add_action( 'admin_init',				array(&$this, 'register_settings'), 1 );
		add_action( 'load-options.php',			array(&$this, 'save_settings') );
		add_action( 'pagelines_options_section_switch_multi', array(&$this, 'section_switch_multi'), 10, 2 );
	}

	function add_section_switchboard() {	
		$this->hook = pagelines_insert_menu( PL_MAIN_DASH, $this->name, 'edit_theme_options', $this->slug, array( &$this, 'build_interface') );
		add_action( "load-{$this->hook}", 		array(&$this, 'option_page_actions') );
	}

	function register_less() {
		if ( function_exists('register_lessdev_dir') )
			register_lessdev_dir( 'aaemnnosttv', $this->slug, $this->name, $this->path.'/css' );
	}

	/**
	 * Fires actions that will run on our option page only
	 */
	function option_page_actions() {

		// filter settings saved message
		add_filter( 'pagelines_admin_confirms',	array(&$this, 'filter_confirms') );
		add_filter( 'pagelines_settings_main_title',	array(&$this, 'filter_ui_title') );
		add_filter( 'gettext',					array(&$this, 'gettext_filter'), 10, 3 );
		add_filter( 'pagelines_sections_dirs',	array(&$this, 'capture_section_types'), 99 );

		add_action( 'admin_enqueue_scripts',	array(&$this, 'admin_enqueue') );
		add_action( 'admin_print_scripts',		array(&$this, 'print_scripts'), 20 );
		
		// enqueue the necessary PL admin JS for UI and options
		add_action( 'admin_print_scripts',		'pagelines_theme_settings_scripts' );
	}

	function admin_enqueue() {

		wp_enqueue_style( $this->slug, "{$this->uri}/css/{$this->slug}.css", array(), self::ver );
	}

	/**
	 * Filters options ui title
	 * @param  string 	$ui_title 	title
	 * @return string
	 */
	function filter_ui_title( $ui_title ) {

		$ui_title = sprintf('%s <span class="btag grdnt">%s</span>',
			$this->ui_args['title'],
			'Version '. self::ver );
											
		return $ui_title;
	}

	function gettext_filter( $translated, $text, $domain ) {

		if ( 'pagelines' == $domain && 'Save Options' == $text )
			return __('Apply Changes', 'pagelines');

		return $translated;
	}

	/**
	 * Filters PageLines confirms
	 * @param  	array 	$c 		confirms
	 * @return  array
	 */
	function filter_confirms( $c ) {

		if ( isset( $_GET['settings-updated'] ) ) {
			$message = get_transient( "{$this->slug}_updated" );

			if ( $message )
				delete_transient( "{$this->slug}_updated" );

			$c[0]['text'] = $message ? $message : 'No changes made.';
		}

		return $c;
	}

	/**
	 * Filter callback for 'pagelines_sections_dirs'
	 */
	function capture_section_types( $dirs ) {

		$this->section_types = array_keys( (array) $dirs );
		// build defaults
		$this->section_defaults = array();
		foreach ( $this->section_types as $type => $sections_array )
			$this->section_defaults[ $type ] = array();

		return $dirs;
	}

	/**
	 * Whitelist options page
	 */
	function register_settings() {

		register_setting( $this->slug, $this->slug );
	}

	/**
	 * Save Callback
	 */
	function save_settings() {

		if ( empty( $_POST ) || !isset($_POST['option_page']) || $this->slug != $_POST['option_page'] )
			return;

		// populate defaults
		global $load_sections;
		$available = $load_sections->pagelines_register_sections( false, true );

		$all = array();
		$defaults = array();
		foreach ( $available as $type => $section_array ) {
			$defaults[ $type ] = array();
			$all[ $type ] = is_array( $section_array ) ? array_keys( $section_array ) : array();
		}

		// ensure all section types are accounted for
		$enabled  = isset( $_POST['enabled'] ) ? $_POST['enabled'] : array();
		$enabled  = wp_parse_args( $enabled, $defaults );

		$disabled = self::get_disabled_sections();

		$changed = array(
			'activated'   => array(),
			'deactivated' => array()
		);

		foreach ( $all as $type => $sections ) {
			
			$e = array_keys( $enabled[ $type ] );
			$d = array_keys( $disabled[ $type ] );

			foreach ( $sections as $class ) {

				// enable disabled
				if ( in_array( $class, $e ) && in_array( $class, $d ) ) {
					self::section_activate( $type, $class );
					$changed['activated']["{$type}_{$class}"] = 'activated';
				}

				// disable
				elseif ( !in_array( $class, $e ) && !in_array( $class, $d ) ) {
					self::section_deactivate( $type, $class );
					$changed['deactivated']["{$type}_{$class}"] = 'deactivated';
				}
				// otherwise it's already enabled or disabled
			}
		}

		$updated = '';

		if ( !empty( $changed['activated'] ) ) {
			$a_count = count($changed['activated']);
			$updated .= sprintf('%s Section%s Activated. ', $a_count, ($a_count > 1) ? 's' : '' );
		}
		if ( !empty( $changed['deactivated'] ) ) {
			$d_count = count($changed['deactivated']);
			$updated .= sprintf('%s Section%s Deactivated. ', $d_count, ($d_count > 1) ? 's' : '' );
		}

		if ( $updated )
			self::sections_reset();


		set_transient( "{$this->slug}_updated", $updated, 60 );
	}

	function build_interface() {

		$this->ui_args = array(
			'title'			=> $this->name,
			'settings' 		=> $this->slug,
			'show_save'		=> true, 
			'show_reset'	=> false,
			'callback'		=> array( &$this, 'get_ui_option_array' ),
		);
		$optionUI = new PageLinesOptionsUI( $this->ui_args );
	}

	function get_ui_option_array() {

		global $load_sections;
		$available = $load_sections->pagelines_register_sections( false, true );

		$sorted = array(
			'parent' => isset( $available['parent']	) ? $available['parent']	: array(),
			'child'  => isset( $available['child']	) ? $available['child']		: array(),
			'custom' => isset( $available['custom']	) ? $available['custom']	: array()
		);

		$this->sections = $sorted;
		$this->sections_active = $this->get_active_sections();


		foreach ( array_keys( $sorted ) as $type ) {

			$op_args = array(
				'type'         => 'section_switch_multi',
				'title'        => 'Toggle Sections',
				'shortexp'     => '<p class="shortexp"><strong>Checked = Activated</strong><br>
								   <strong>Active = In use</strong> (<em>Assigned to one or more template areas in Drag &amp; Drop</em>)<br>
								   <strong>Inactive = Unused</strong> (<em>Activated, but absent from all Drag &amp; Drop template areas</em>)<br>
								   <strong>Deactivated = Not loaded at all</strong></p>',
				'section_type' => $type
			);

			switch ( $type ) {
				case 'parent' :
					$tabs['core_framework'] = array(
						'icon'             => PL_ADMIN_ICONS . '/leaf.png',
						"{$type}_sections" => $op_args
					);

					break;

				// store
				case 'child' :
					$tabs['store_added'] = array(
						'icon'             => PL_ADMIN_ICONS . '/extend-inout.png',
						"{$type}_sections" => $op_args
					);

					break;

				// child theme
				case 'custom' :
					$tabs['child_theme'] = array(
						'icon'             => PL_ADMIN_ICONS . '/extend-themes.png',
						"{$type}_sections" => $op_args
					);

					break;
			}
		}

		return $tabs;
	}

	/**
	 * get_active_sections
	 * 
	 * Builds an array of all sections that are currently active in some template area
	 * @return array [description]
	 */
	function get_active_sections() {

		global $pagelines_template;
		$map = $pagelines_template->map;

		$a = array(); // active container

		foreach ( $map as $area => $data ) {
			
			if ( 'templates' == $area || 'main' == $area ) {

				$templates = $data['templates'];

				foreach ( $templates as $tslug => $tdata ) {
					if ( isset($tdata['sections']) && is_array($tdata['sections']) )
						$a = array_merge( $tdata['sections'], $a );
				}
			}
			elseif ( isset($data['sections']) && is_array($data['sections']) )
				$a = array_merge( $data['sections'], $a );
		}

		return array_unique( $a );
	}

	/**
	 * Custom Multi-checkbox option
	 * @param  [type] $oid option id
	 * @param  [type] $o   option array
	 */
	function section_switch_multi( $oid, $o ) {

		$engine   = new OptEngine();
		$disabled = get_option('pagelines_sections_disabled');
		$stype    = $o['section_type'];
		$set      = $this->sections[ $stype ];

		$group = array(
			'activated'  => array(),
			'inactive' => array(),
			'deactivated' => array()
		);

		foreach ( $set as $sclass => $s ) {

			$checked = checked( !$this->is_disabled($stype, $sclass), true, false );
			
			$inpid = "enabled_{$stype}_{$sclass}";
			$iname = "enabled[{$stype}][{$sclass}]";
			$input = $engine->input_checkbox($inpid, $iname, $checked);
			$classes = array('inln');
			
			$status = $checked ? 'activated' : 'deactivated';
			$classes[] = $status;

			if ( $checked && !in_array( $sclass, $this->sections_active ) ) {
				$status = 'inactive';
				$classes[] = $status;
			}

			
			if ('deactivated' != $status)
				$label_text = sprintf('%s <span class="status">%s</span>', $s['name'],
					('inactive' == $status) ? $status : 'active' );
			else
				$label_text = $s['name'];

			
			$class = implode( ' ', $classes);


			$out = $engine->input_label_inline($inpid, $input, $label_text, $class);

			$group[ $status ][ $s['name'] ] = $out;
		}

		// sort each group of sections by name
		foreach ( $group as &$s )
			ksort( $s );
		// destroy
		unset($s);

		// make sure activated & inactive are separated
		if ( !empty( $group['activated'] ) )
			$group['activated'][] = '<div class="clear activated-inactive-sep"></div>';

		// group inactive with enabled but at the end
		$group['activated'] = array_merge( $group['activated'], $group['inactive'] );
		unset( $group['inactive'] );


		$js_btns = sprintf('<p class="toggle-row">
			<button class="select-toggle button-secondary toggle-activated first" section-type="%1$s">Toggle Activated</button>
			<button class="select-toggle button-secondary toggle-inactive" section-type="%1$s">Toggle Inactive</button>
			<button class="select-toggle button-secondary toggle-deactivated" section-type="%1$s">Toggle Deactivated</button>
			<button class="select-toggle button-secondary select-all" section-type="%1$s">Select All</button>
			<button class="select-toggle button-secondary select-none last" section-type="%1$s">Select None</button>
			<div class="clear"></div>
		</p>', $stype);


		// begin output!

		echo $js_btns;
		echo "<div id='{$oid}_multi' class='section_switch_multi'>";

		foreach ( $group as $status => $s ) {

			if ( empty( $group ) ) {
				printf('<div class="no-sections"><h3>%s</h3></div>', 'Nothing to see here...' );
				break;
			}

			if ( empty( $s ) )
				continue;

			echo "<div class='{$status}-sections section-group'>";
			
			printf('<h4 class="status-heading">%s</h4>', ucfirst( $status ));

			foreach ( $s as $out )
				echo $out;

			echo '<div class="clear"></div>';
			echo '</div>';
		}

			echo '<div class="clear"></div>';
		echo '</div>';

		// show toggle buttons at the bottom only if there are more than 10
		if ( 10 < count( $set ) )
			echo $js_btns;

	}

	function is_disabled( $type, $class ) {		
		$disabled = self::get_disabled_sections();
		return ( isset( $disabled[$type][$class] ) && $disabled[$type][$class] ) ? true : false;
	}

	function get_disabled_sections() {
		$d = array(
			'child'  => array(),
			'parent' => array(),
			'custom' => array()
		);

		$defaults = is_array( $this->section_defaults ) ? $this->section_defaults : $d;

		$saved = get_option( 'pagelines_sections_disabled', array() );
		return wp_parse_args( $saved, $defaults );
	}

	function section_activate( $type, $class ) {
		$disabled = self::get_disabled_sections();
		unset( $disabled[ $type ][ $class ] );
		update_option( 'pagelines_sections_disabled', $disabled );		
	}

	function section_deactivate( $type, $class ) {
		$disabled = self::get_disabled_sections();
		$disabled[ $type ][ $class ] = true;
		update_option( 'pagelines_sections_disabled', $disabled );
	}
	function sections_reset() {
		global $load_sections;
		delete_transient( 'pagelines_sections_cache' );
		$load_sections->pagelines_register_sections( true, false );
	}

	function print_scripts() {
		?>
		<script type="text/javascript">
			jQuery(document).ready( function( $ ) {
				$selbtns = $('button.select-toggle', '#tabs');
				$selbtns.click( function(event) {
					event.preventDefault();
					$btn = $(this);
					type = $btn.attr('section-type');

					$checks = $('input:checkbox', '#'+ type +'_sections_multi');

					if ( $btn.hasClass('select-all') )
						$checks.attr('checked', 'checked');
					else if ( $btn.hasClass('select-none') )
						$checks.removeAttr('checked');
					else if ( $btn.hasClass('toggle-activated') )
						toggleSections(type, 'activated');
					else if ( $btn.hasClass('toggle-deactivated') )
						toggleSections(type, 'deactivated');
					else if ( $btn.hasClass('toggle-inactive') )
						toggleSections(type, 'inactive');
				});
				
				function toggleSections(type, status) {

					// identify
					$sections = $('label.'+status+' > input:checkbox', '#'+ type +'_sections_multi');

					// toggle
					if ( 'checked' == $sections.attr('checked') )
						$sections.removeAttr('checked');
					else
						$sections.attr('checked', 'checked');
				}
			} );
		</script>
		<?php
	}


} // end class

###############################################################
add_action('pagelines_hook_pre', 'init_section_switchboard');
function init_section_switchboard() { new SectionSwitchboard; }
###############################################################
