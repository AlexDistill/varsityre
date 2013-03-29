<?php
/*
	Section: Vertical Branding
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: Logo on top with navigation underneath, in vertical form
	Class Name: PageLinesLogo
	Version:1.0
	Workswith: templates, main, header, morefoot, sidebar1, sidebar2, sidebar_wrap
*/

/**
 * Logo Section
 *
 * @package PageLines Framework
 * @author PageLines
 */
class PageLinesLogo extends PageLinesSection {

	/**
	* PHP that always loads no matter if section is added or not.
	*/	
	function section_persistent(){
		register_nav_menus( array( 'Logo' => __( 'Logo Section Navigation', 'pagelines' ) ) );
	}
	
	/**
	*
	* @TODO document
	*
	*/
	function section_styles(){

	}

	/**
	* Section template.
	*/
 	function section_template() { 
	
			pagelines_main_logo( $this->id ); 
			
			
		if(has_action('Logo_after_brand')){
			pagelines_register_hook( 'Logo_after_brand', 'Logo' ); // Hook
		
		} else {
		
		?>
		
			<div class="Logo-nav main_nav fix">		
<?php 	
				wp_nav_menu( array('menu_class'  => 'main-nav tabbed-list'.pagelines_nav_classes(), 'container' => null, 'container_class' => '', 'depth' => 3, 'theme_location'=>'Logo', 'fallback_cb'=>'pagelines_nav_fallback') );

				
				pagelines_register_hook( 'Logo_after_nav', 'Logo' ); // Hook
?>
			</div>
		<div class="clear"></div>
<?php 	}
	}


		/**
		*
		* @TODO document
		*
		*/
		function section_head(){

	}
}