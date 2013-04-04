<?php
/*
Section: FitText Section
Author: PageLines
Author URI: http://www.pagelines.com
Version: 1.0.0
Description: A simple section that creates text that sizes to fit the container, and is responsive which means it scales with different size browsers.
Class Name: FitTextSection
Workswith: templates, main, sidebar1, sidebar2, sidebar_wrap, header, footer, morefoot
Cloning: true
*/

class FitTextSection extends PageLinesSection {


	function section_styles(){
		
		// wp_enqueue_script
		// wp_enqueue_style
		
		// $this->base_url
		
		wp_enqueue_script('fittext', $this->base_url.'/jquery.fittext.js');
		
		
	}

	function section_head( $clone_id ){
		
		// default = ''
		// clone = 2, 3, 4... 
		
		$prefix = ($clone_id != '') ? '.clone_'.$clone_id : '';
		
		?>
		
		<script type="text/javascript">
		/*<![CDATA[*/ 
			jQuery(document).ready(function(){ 
			
				jQuery('<?php echo $prefix;?> .fittext').fitText(.7);
			
			});
		/*]]>*/</script>
		
		<?php 
		
		echo load_custom_font( ploption('fittext-font', $this->oset), $prefix.' h2.fittext' );
		
	}


	function section_optionator( $settings ){
		
		$settings = wp_parse_args($settings, $this->optionator_default);
		
		$options = array(
			
			'fittext-text'	=> array(
				
				'title'			=> 'FitText Text', 
				'type'			=> 'text', 
				'inputlabel'	=> 'Add Text', 
				'shortexp'		=> 'Add the text for this FitText section.'
				
			), 
			'fittext-font'	=> array(
				
				'title'			=> 'FitText Font', 
				'type'			=> 'fonts', 
				'inputlabel'	=> 'Choose Font', 
				'exp'			=> 'This font will be used in this FitText area. If left blank, the default font will be used.', 
				'shortexp'		=> 'Select FitText Font'
				
			)
			
		); 
		
		$tab_settings = array(
			
			'id'		=> 'fittext-options', 
			'name'		=> 'FitText', 
			'icon'		=> $this->icon, 
			'clone_id'	=> $settings['clone_id'], 
			'active'	=> $settings['active']
		);
		
		
		register_metatab($tab_settings, $options, $this->class_name);
		
		
	}
	

	function section_template(){
	
	
		if(!ploption('fittext-text', $this->oset)){
			echo setup_section_notify($this, __('Please set up FitText text.'));
			return; 
		}
		
		?> 
		
		<div class="fittext-container">
			
			<h2 class="fittext"><?php echo ploption('fittext-text', $this->oset); ?></h2>
			
		</div>
		
		<?php
		
	}
	
	
} 