<?php
/*
	Section: Author Plus
	Author URI: http://www.pagelines.com
	Description: Section meant to display Author image and text together with social networks.
	Class Name: PLauthorplus
	Version: 1.0
	Workswith: templates, main, header, morefoot, sidebar1, sidebar2, sidebar_wrap
	Cloning: true
*/

/*
 * Main section class
 *
 * @package PageLines Framework
 * @author PageLines
 */
class PLauthorplus extends PageLinesSection {
    
    var $tabID = 'authorplus_meta';

	function section_optionator( $settings ){
		
		$settings = wp_parse_args($settings, $this->optionator_default);

		
		$option_array = array(

				'pagelines_authorplus_text' => array(
						'type' 				=> 'multi_option',
						'inputlabel' 		=> 'Enter text for your Author section',
						'title' 			=> $this->name.' Text',	
						'selectvalues'	=> array(
							'pagelines_authorplus_title' => array(
								'type'  => 'text',
								'inputlabel'=>'Heading', 
							),
							'pagelines_authorplus_tagline' => array(
								'type'   => 'textarea',
								'inputlabel'=>'Subtext'
							)
						),				
						'shortexp' 			=> 'The text for the Author Plus section Header and Subtext content.',
						'exp' 				=> 'The heading can be used for a one sentence welcome, and the Subtext can be used as a 3 line description of the author.'

				),
				'pagelines_authorplus_image' => array(
					'type' 			=> 'image_upload',
					'imagepreview' 	=> '270',
					'inputlabel' 	=> 'Upload custom image',
					'title' 		=> $this->name.' Image',						
					'shortexp' 		=> 'Input Full URL to your Author Plus image.',
					'exp' 			=> 'Places a custom image to the right of the text. If you keep your description to 3 lines, an image sized 195x165 fits perfectly.'
				),
				'pagelines_authorplus_widths' => array(
					'type'		=> 'multi_option', 
					'title'		=> __('Content Widths', 'pagelines'), 
					'shortexp'	=> __('Select the width of the image and text areas. Default is 66% and 33%.', 'pagelines'),
					'selectvalues'	=> array(
						'authorplus_left_width' => array(
							'type'			=> 'select',
							'default'		=> 'span6',
							'inputlabel'	=> 'Text Area Width',
							'selectvalues'	=> array(
								'span3'	 => array('name' => '25%'), 
								'span4'	 => array('name' => '33%'), 
								'span6'	 => array('name' => '50%'), 
								'span8'	 => array('name' => '66%'), 
								'span9'	 => array('name' => '75%'), 
								'span7'	 => array('name' => '90%'), 
							),
						),
						'authorplus_right_width' => array(
							'type'			=> 'select',
							'default'		=> 'span6',
							'inputlabel'	=> 'Image Area Width',
							'selectvalues'	=> array(
								'span3'	 => array('name' => '25%'), 
								'span4'	 => array('name' => '33%'), 
								'span6'	 => array('name' => '50%'), 
								'span8'	 => array('name' => '66%'), 
								'span9'	 => array('name' => '75%'), 
								'span7'	 => array('name' => '90%'), 
							),
						),
					),
				),
				'authorplus_social' => array(
						'type'		=> 'check_multi',
						'selectvalues'	=> array(
							'rsslink'	=> array('inputlabel'=> __( 'RSS', 'pagelines' ), 'default'=> true),
							'share_twitter'		=> array('inputlabel'=> __( 'Twitter', 'pagelines' ), 'default'=> true),
							'share_facebook'	=> array('inputlabel'=> __( 'Facebook', 'pagelines' ), 'default'=> true),
							'share_google'		=> array('inputlabel'=> __( 'Google+', 'pagelines' ), 'default'=> true),
							'share_linkedin'	=> array('inputlabel'=> __( 'LinkedIn', 'pagelines' ), 'default'=> true),
							'share_pinterest'	=> array('inputlabel'=> __( 'Pinterest', 'pagelines' ), 'default'=> true),
						),
						'inputlabel'=> __( 'Select which social network icons to show', 'pagelines' ),
						'title'		=> __( 'Author Plus social network icons', 'pagelines' ),						
						'shortexp'	=> __( 'Select which to show', 'pagelines' ),
						'exp'		=> __( "Select which social sharing buttons you would like to appear below the text in your Author Plus section.", 'pagelines' )
			    ),					
		);
		
		$metatab_settings = array(
				'id' 		=> $this->tabID,
				'name' 		=> 'Author Plus',
				'icon' 		=> $this->icon, 
				'clone_id'	=> $settings['clone_id'], 
				'active'	=> $settings['active']
			);
		
		register_metatab($metatab_settings, $option_array);


	}

	/**
	* Section template.
	*/
   function section_template( $clone_id ) { 

		$authorplus_lt_width = ploption( 'authorplus_left_width', $this->oset );
			 if ( ! $authorplus_lt_width )$authorplus_lt_width = 'span8';
		$authorplus_rt_width = ploption( 'authorplus_right_width', $this->oset );
			if ( ! $authorplus_rt_width )$authorplus_rt_width = 'span4';
   		$authorplus_title = ploption( 'pagelines_authorplus_title', $this->tset );
		$authorplus_tag = ploption( 'pagelines_authorplus_tagline', $this->tset );
		$authorplus_img = ploption( 'pagelines_authorplus_image', $this->tset );
		$authorplus_social = ploption( 'authorplus_social', $this->oset );

   		if($authorplus_title)	{ ?>

	   	<div class="pl-authorplus-wrap row">

		   	<?php
		   	if($authorplus_lt_width)
				printf('<div class="pl-authorplus zmb %s">',$authorplus_lt_width);
				?>
					<?php

						if($authorplus_title)
							printf('<h1 class="m-bottom">%s</h1>',$authorplus_title);
						
						if($authorplus_tag)
			  				printf('<p>%s</p>',$authorplus_tag);
			  			
			  			?>

			  			<div class="row">
			  				<div class="span12 zmb">

						  		<?php
					  			
						  			if($authorplus_social)
									printf('<div class="icons_wrap">');
								
									pagelines_register_hook( 'pagelines_before_authorplus_icons', 'authorplus' ); // Hook 
											
									printf('<div class="authorplusicons">');
											
											pagelines_register_hook( 'pagelines_authorplus_icons_start', 'authorplus' ); // Hook 
											
											if(VPRO) {
												if(ploption('rsslink'))
													printf('<a target="_blank" href="%s" class="author_rss"><img src="%s" alt="Twitter"/></a>', ploption('rsslink'), $this->base_url.'/rss.png');

												if(ploption('share_twitter'))
													printf('<a target="_blank" href="%s" class="twitterlink"><img src="%s" alt="Twitter"/></a>', ploption('twitterlink'), $this->base_url.'/twitter.png');
											
												if(ploption('share_facebook'))
													printf('<a target="_blank" href="%s" class="facebooklink"><img src="%s" alt="Facebook"/></a>', ploption('facebooklink'), $this->base_url.'/facebook.png');
												
												if(ploption('share_linkedin'))
													printf('<a target="_blank" href="%s" class="linkedinlink"><img src="%s" alt="LinkedIn"/></a>', ploption('linkedinlink'), $this->base_url.'/linkedin.png');
												
												if(ploption('share_pinterest'))
													printf('<a target="_blank" href="%s" class="pinterest"><img src="%s" alt="Youtube"/></a>', ploption('youtubelink'), $this->base_url.'/pinterest.png');
												
												if(ploption('gpluslink'))
													printf('<a target="_blank" href="%s" class="gpluslink"><img src="%s" alt="Google+"/></a>', ploption('gpluslink'), $this->base_url.'/google.png');
												
												pagelines_register_hook( 'pagelines_authorplus_icons_end', 'authorplus' ); // Hook 
										
											}
											
									printf('</div></div>');
				  				?>

				  			</div>	
			  			</div>

		   	<?php
		   	if($authorplus_rt_width)
				printf('<div class="pl-authorplus-image zmb %s">',$authorplus_rt_width);
				?>
					<?php 
					    
						if($authorplus_img)
							printf('<div class="authorplus_image"><img src="%s" /></div>', apply_filters( 'pl_authorplus_image', $authorplus_img ) );
						
					?>
				</div>

		</div>

		<?php

		} else
			echo setup_section_notify($this, __('Set authorplus page options to activate.', 'pagelines') );
	}

}