<?php
/*
	Section: ContentBox
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: A simple box and option for adding standard content.
	Class Name: PageLinesContentBox
	Workswith: templates, main, header, morefoot, sidebar1, sidebar2, sidebar_wrap, footer
	Version: 1.3
	Edition: pro
	Cloning: true
*/

class PageLinesContentBox extends PageLinesSection {

	function section_optionator( $settings ){
		
		$settings = wp_parse_args($settings, $this->optionator_default);
		
		$metatab_array = array(

				'content_box_class' => array(
					'type' 			=> 'text',	
					'title' 		=> 'Content Box Class',
					'shortexp' 		=> 'Applies this class to the content box for individual styling'
				),
				'content_box_content' => array(
					'type' 			=> 'textarea',
					'inputsize'		=> 'big',		
					'title' 		=> 'Content Box Content',
					'shortexp' 		=> 'Add content for the box'
				),
				
			);
		
		$metatab_settings = array(
				'id' 		=> $this->id.'meta',
				'name' 		=> $this->name,
				'icon' 		=> $this->icon, 
				'clone_id'	=> $settings['clone_id'], 
				'active'	=> $settings['active']
			);
		
		register_metatab($metatab_settings, $metatab_array);
	}

	function section_template( $clone_id ) { 

		$class = (ploption('content_box_class', $this->oset)) ? ploption('content_box_class', $this->oset) : 'cb-standard';
		$content = ploption('content_box_content', $this->oset);
			
		if($content){
			
			$c = do_shortcode( $content );
		
			printf('<div class="hentry %s"><div class="hentry-pad %s-pad entry_content">%s</div></div>', $class, $class, $c);
		
		} else
			echo setup_section_notify($this, __('Add content to meta option to activate.', 'pagelines') );
 
	}

} /* End of section class */