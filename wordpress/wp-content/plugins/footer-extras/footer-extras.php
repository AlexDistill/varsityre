<?php
/*
Plugin Name: Footer Extras
Plugin URI: http://pagelines.ellenjanemoore.com/footer-extras/
Description: Puts a dynamic copyright notice, Business NAP formatted with rich snippets for local seo ranking and an extra line of text
Version: 1.1
Author: Ellen Moore
Author URI: http://www.pagelines.ellenjanemoore.com
Demo: http://pagelines.ellenjanemoore.com/footer-extras/
PageLines: true
Tags: extension
*/

/*
Plugins have to follow the same rules as normal WordPress plugins for the header
with a few extra fields added:

	PageLines: true
	This is used internally by the framework to check for updates etc.
	
	Demo: http://a.link.com
	Use this to point to a demo for this product.
	
	External: http://a.link.com
	Use this to point to an external site, authors home page for example.
	
	Long: Blank plugin example for developing plugins.
	Add a full description, used on the actual store page on http://www.pagelines.com/store/
	
*/

/**
 *
 * File Naming Conventions
 * -------------------------------------
 *  my-plugin.php 	- The plugin filename MUST match the folder slug, you will be given this by the PageLines staff when you are setup.
 *  thumb.png		- Thumbnail image used in the store and on pagelines.com for your product.
 *  screenshot.png	- Primary Screenshot, logo or graphic for your extension item (300px by 225px).
 *	screenshot-1.png - Additional screenshots -1 -2 -3 etc (optional).
 */

add_action('pagelines_setup' , 'footer_extras_check');
// add_action('pagelines_setup', 'footer)extras_less');
	function footer_extras_check() {
		if( !function_exists('ploption') )
		return;
	}
	

class Footer_Extras {
	
	function __construct() {
		
		$this->base_url = sprintf( '%s/%s', WP_PLUGIN_URL,  basename(dirname( __FILE__ )));
		
		$this->icon = $this->base_url . '/icon.png';
		
		$this->base_dir = sprintf( '%s/%s', WP_PLUGIN_DIR,  basename(dirname( __FILE__ )));
		
		$this->base_file = sprintf( '%s/%s/%s', WP_PLUGIN_DIR,  basename(dirname( __FILE__ )), basename( __FILE__ ));
		
		// register plugin hooks...
		$this->plugin_hooks();
		
	}


	function plugin_hooks(){
	
		// Always run 
		add_action( 'pagelines_setup', array( &$this, 'options' ));
	
	
		add_filter( 'pagelines_lesscode', array( &$this, 'get_less' ), 10, 1 );
		add_action( 'pagelines_after_footer', array( &$this, 'footer_extras_template' ));
	
		add_filter ( 'pagelines_settings_whitelist', 'footer_extras_whitelist' );
		
	function footer_extras_whitelist($whitelist) {
		// Included fields that may contain link or special characters
		$footer_extras_text = array('footer_extras_copyright_text', 'nap_name', 'nap_street', 'nap_city' , 'footer_extras_additional_text');
		return array_merge( $whitelist, $footer_extras_text );
		}	
	
	}

	

	function get_less( $less ){
		
		
		
		$less .= pl_file_get_contents( $this->base_dir.'/style.less' );

		return $less;
		
	}



	
	function footer_extras_template(){
		global $wpdb;

		$footer_align = (ploption('footer_extras_align')) ? ploption('footer_extras_align') : 'left';
		$nap_name = (ploption('nap_name')) ? ploption('nap_name') : '';
		$nap_street = (ploption('nap_street')) ? ploption('nap_street') : '';
		$nap_city = (ploption('nap_city')) ? ploption('nap_city') : '';
		$nap_state = (ploption('nap_state')) ? ploption('nap_state') : '';
		$nap_zip = (ploption('nap_zip')) ? ploption('nap_zip') : '';
		$nap_phone = (ploption('nap_phone')) ? ploption('nap_phone') : '';
		$nap_latitude = (ploption('nap_latitude')) ? ploption('nap_latitude') : '';
		$nap_longitude = (ploption('nap_longitude')) ? ploption('nap_longitude') : '';
		$footer_extras_additional = (ploption('footer_extras_additional_text')) ? ploption('footer_extras_additional_text') : '';
		$copyright_text = (ploption('footer_extras_copyright_text')) ? ploption('footer_extras_copyright_text') : '';
		$copyright_year = (ploption('footer_extras_copyright_year')) ? ploption('footer_extras_copyright_year') : null;
		
		$copyright_dates = $wpdb->get_results("
			SELECT
			YEAR(min(post_date_gmt)) AS firstdate,
			YEAR(max(post_date_gmt)) AS lastdate
			FROM
			$wpdb->posts
			WHERE
			post_status = 'publish'
			");
		$output = ' ';
		
		
		printf('<div class="footer-extras" style="text-align: %s;">', $footer_align);
			if(ploption('footer_extras_copyright')) {
				
					if($copyright_text) {
						$additional_copyright =  $copyright_text . '  </div>';	
					} else {
						$additional_copyright = '</div>';
					}
					if($copyright_year) {
						$copyright = '<div class="copyright"> &copy; ' . $copyright_year . '  ';
					
					} else {
						$copyright = '<div class="copyright"> &copy; ' . $copyright_dates[0]->firstdate . '  ';
					
						}
					if($copyright_dates[0]->firstdate != $copyright_dates[0]->lastdate) {
					$copyright .= '- ' . $copyright_dates[0]->lastdate . ' ' . $additional_copyright;
					}

					echo $copyright;
			 }			
			 
				if($nap_name) {
					$name_output = '<span itemscope="" itemtype="http://schema.org/LocalBusiness"><span itemprop="name">'. $nap_name . '</span>';
				} else {
					$name_output = '<span itemscope="" itemtype="http://schema.org/LocalBusiness">';
				}

				if($nap_street){
					$street_output = ' • <span itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress"><span itemprop="streetAddress">' .$nap_street . '</span>';

				} else {
					$street_output = '<span itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress">';
				}
				
				if($nap_city){
					$city_output = ' • <span itemprop="addressLocality">'. $nap_city . '</span>';

				} else {
					$city_output = null;
				}

				if($nap_state){
					$state_output = ', <span itemprop="addressRegion">' .$nap_state . '</span>';

				} else {
					$state_output = null;
				}

				if($nap_zip){
					$zip_output = '  <span itemprop="postalCode">' . $nap_zip .'</span></span>';

				} else {
					$zip_output = '</span>';
				}

				if($nap_phone){
					$phone_output = ' • <span itemprop="telephone">' . $nap_phone . '</span></span>';

				} else {
					$phone_output = '</span>';
				}


				if(ploption('footer_extras_nap')) {
					?>
					<div class="nap">
						<?php

					$nap = $name_output. ''  .$street_output . '' .$city_output . '' .$state_output . '' . $zip_output . '' . $phone_output;
					echo $nap;
					
					
					if ($nap_latitude&&$nap_longitude) {
					?>
					<span itemtype="http://schema.org/GeoCoordinates" itemscope="" itemprop="geo">
  					<?php
  					printf('<meta content="%s" itemprop="latitude">' , $nap_latitude);
  					printf('<meta content="%s" itemprop="longitude">' , $nap_longitude);
  					?>
				</span>
				<?php
				}
			 	?>	
			 	</div>



			 	<?php
			 	}	

			 	if(ploption('footer_extras_additional')) {
			 		printf('<div class="additional">%s</div>' , $footer_extras_additional);

			 	}
			 
			 	echo '</div>';

		
	
	}
	


	function options(  ){


		

		$options = array(
			'footer_extras_align' => array(
					'type' 			=> 'select',
					'title'		=> __('Alignment of Footer Extras', 'pagelines'), 
					'shortexp'	=> __('Choose the alignment for footer extras', 'pagelines'),
			
					'inputlabel' 	=> __( 'Footer Extras Align (Default: left)', 'pagelines'),
					'selectvalues'	=> array(
							'left'		=> array('name' => 'Left'),
							'right'		=> array('name' => 'Right'),
							'center'	=> array('name' => 'Center'),
						)
					), 
			'footer_extras_show_copyright' => array(
				'type'		=> 'multi_option', 
				'title'		=> __('Show Copyright Dates', 'pagelines'), 
				'shortexp'	=> __('If selected, copyright dates will be dynamically generated from the earliest post date to latest post date.', 'pagelines'),
				'selectvalues'	=> array(
					
					'footer_extras_copyright' => array(
						'type' 			=> 'check',			
						'inputlabel'	=> 'Show Copyright Notice?'
					),
					'footer_extras_copyright_text' => array(
						'type' 			=> 'text',			
						'inputlabel'	=> 'Additional copyright text (Business NAP shows after copyright so not necessary to put Business Name here if displaying NAP too, just additional text.)'
					),
					'footer_extras_copyright_year' => array(
						'type' 			=> 'text_small',			
						'inputlabel'	=> 'Start Year (Optional) (To override start year enter it here. Leave empty to pick up year from earliest post/page.)'
					),
				),
			),
			'footer_extras_nap_setup' => array(
				'type'		=> 'multi_option', 
				'title'		=> __('Business NAP (Name, Address, Phone)', 'pagelines'), 
				'shortexp'	=> __('Setup Business Name, Address and Phone formatted with rich text snippets for local seo. If you have a Google+ Local page or your business is listed in other directories make sure that the NAP is the same, otherwise you will not get the full value of the NAP. <br /><br />You can also enter your latitude and logitude(coordinates are not displayed, just coded for geolocation). Find your coordinates at http://geocoder.us.', 'pagelines'),
				'selectvalues'	=> array(
					
					'footer_extras_nap' => array(
						'type' 			=> 'check',			
						'inputlabel'	=> 'Show The Business NAP (Name, Address, Phone)?'
					),
					'nap_text'		=> array(
					'type'		=> 'text_multi',
					'selectvalues'	=> array(
						'nap_name'	=> array('inputlabel'=> __( 'Business Name', 'pagelines' ), ''),
						'nap_street'	=> array('inputlabel'=> __( 'Business Street Address', 'pagelines' ), ''),
						'nap_city'	=> array('inputlabel'=> __( 'Business City', 'pagelines' ), ''),
						'nap_state'	=> array('inputlabel'=> __( 'Business State', 'pagelines' ), ''),
						'nap_zip'	=> array('inputlabel'=> __( 'Business Zip', 'pagelines' ), ''),
						'nap_phone'	=> array('inputlabel'=> __( 'Business Phone Number', 'pagelines' ), ''),
						'nap_latitude'	=> array('inputlabel'=> __( 'Address Latitude', 'pagelines' ), ''),
						'nap_longitude'	=> array('inputlabel'=> __( 'Address Longitude', 'pagelines' ), ''),
					
					),
					),
				),
					
			),
			'footer_extras_show_additional' => array(
				'type'		=> 'multi_option', 
				'title'		=> __('Show Additional Line of Text', 'pagelines'), 
				'shortexp'	=> __('Area for an extra line of text at the end of your page.', 'pagelines'),
				'selectvalues'	=> array(
					
					'footer_extras_additional' => array(
						'type' 			=> 'check',			
						'inputlabel'	=> 'Show Additional Line of Text?'
					),
					'footer_extras_additional_text' => array(
						'type' 			=> 'text',			
						'inputlabel'	=> 'Additional text (Great place to have "Website Designed by" credit and link)'
					),
				),
			),
					
				
		);
		
		
		

		$option_args = array(
			'name'		=> 'Footer_Extras',
			'array'		=> $options,
			'icon'		=> $this->icon,
			'position'	=> 9
		);

		pl_add_options_page( $option_args );


	}

	
}

new Footer_Extras;