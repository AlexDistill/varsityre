<?php
/*
	Section: Soapboxes
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: A responsive, special type of boxes that allows additional links and multiple setup options.
	Class Name: PageLinesSoapbox
	Version: 1.0.0
	Depends: PageLinesBoxes
	Edition: pro
	Workswith: templates, main, header, morefoot
*/

class PageLinesSoapbox extends PageLinesSection {

	/*
		Loads php that will run on every page load (admin and site)
		Used for creating administrative information, like post types
	*/

	function section_persistent(){

		$type_meta_array = array();
		
		for($i = 1; $i <= 3; $i++){
			
			$type_meta_array['sb'.$i] = array(
				'title'		=> "Soapbox Link ".$i,
				'shortexp'	=> "Set up link ".$i, 
				'type'		=> 'text_multi',
				'selectvalues'	=> array(
					'_soapbox_link_'.$i => array(				
							'inputlabel'	 => 'URL',
						),
					'_soapbox_link_'.$i.'_text' => array(
							'inputlabel' 	=> 'Text',			
						),
					'_soapbox_link_'.$i.'_class' => array(
							'inputlabel'	=> 'Class'
						),
				)
			);
			
		}
		
		$post_types = array($this->settings['posttype']);
		
		$type_metapanel_settings = array(
		 						'id' 		=> 'soapbox-metapanel',
		 						'name' 		=> "Soapbox Section Options",
		 						'posttype' 	=> $post_types, 
		 					);
		 
		global $boxes_meta_panel;		

		$type_metatab_settings = array(
				'id' 		=> 'soapbox-type-metatab',
				'name' 		=> "Soapbox Setup Options",
				'icon' 		=> $this->icon,
		);

		$boxes_meta_panel->register_tab( $type_metatab_settings, $type_meta_array );
		
	}

	function section_optionator( $settings ){
		$settings = wp_parse_args($settings, $this->optionator_default);
		
		$array = array(

				'_soapbox_set' => array(
					'version' 		=> 'pro',
					'type' 			=> 'select_taxonomy',
					'taxonomy_id'	=> "box-sets",				
					'title' 		=> 'Select Box-Set To Use For Soapbox Section',
					'shortexp'		=> 'If you are using the soapbox section, select the box-set you would it to use on this page.'
				), 
				'_soapbox_items' => array(
					'type' 		=> 'text_small',
					'label'		=> 'Enter max number of soapboxes',
					'title' 	=> 'Soapbox Posts Limit',					
					'shortexp' 	=> 'Add the limit or soapboxes that can be shown on this page. Default is 10.',
					),
				'_soapbox_height_media' => array(
					'version' 	=> 'pro',
					'type' 		=> 'text_small',
					'label'		=> 'Enter height in pixels',
					'title' 	=> 'Soapbox Media Height (in Pixels)',
					'shortexp' 	=> 'For the "soapboxes" to line up correctly, the height of the media needs to be set. Add it here in pixels.'
					), 
			);
			
			for($i = 1; $i <= 3; $i++){
			
				$array['soapbox_set_'.$i] = array(
					'version'	=> 'pro',
					'type'		=> 'text_multi',
					'selectvalues'	=> array(
						'_soapbox_link_'.$i.'_text'	=> array('inputlabel'=> __( 'Link Text', 'pagelines' )),
						'_soapbox_link_'.$i.'_class'=> array('inputlabel'=> __( 'Link Class', 'pagelines' )),
					),
					'title'		=> __( 'Link Options ', 'pagelines' ) .$i,
					'shortexp'	=> __( 'Sets the appearance of the soapbox link number ', 'pagelines' ) . $i,
					'exp'		=> __( 'Add text to be used in this link. Can be overridden in the box meta options.<br/> Add extra CSS classes for this link for additional styling and custom CSS options.', 'pagelines' )	
				);
				
			}
			

			$metatab_settings = array(
					'id' => 'soapbox_meta',
					'name' => "Soapboxes",
					'icon' => $this->icon,
					'clone_id'	=> $settings['clone_id'], 
					'active'	=> $settings['active']
				);
			
			
			register_metatab($metatab_settings, $array);
	}

	function section_template( $clone_id = '') {    

			global $post; 
			global $pagelines_ID;

			$oset = array( 'post_id' => $pagelines_ID, 'clone_id' => $clone_id);

			$perline = (ploption('soap_cols', $oset)) ? ploption('soap_cols', $oset) : 2;

			$set = (plmeta('_soapbox_set', $oset)) ? plmeta('_soapbox_set', $oset) : null;

			$limit = (ploption('_soapbox_items', $oset)) ? ploption('_soapbox_items', $oset) : null;
			
			$b = $this->load_pagelines_boxes($set, $limit); 
			
			if ( empty( $b ) )
				echo setup_section_notify($this, 'Set Soapbox meta fields to activate.' );
			else
				$this->draw_boxes($b, $perline, $set, $clone_id );
	}
	
		function draw_boxes($b, $perline = 3, $class = "", $clone_id = ''){ 
			global $post;
			global $pagelines_ID;

			$post_count = count($b);
			$current_box = 1;
			$row_count = $perline;

	?>
			<div class="pprow <?php echo $class;?> soapboxes fix">
	<?php 	foreach($b as $bpost):
				setup_postdata($bpost); 
				$oset = array('post_id' => $bpost->ID);
				$box_link = plmeta('the_box_icon_link', $oset);
				$box_icon = plmeta('the_box_icon', $oset);

				$box_row_start = ( $row_count % $perline == 0 ) ? true : false;
				$box_row_end = ( ( $row_count + 1 ) % $perline == 0 || $current_box == $post_count ) ? true : false;
				$grid_class = ($box_row_end) ? 'pplast pp'.$perline : 'pp'.$perline;

	?>
				<section id="<?php echo 'fbox_'.$bpost->ID;?>" class="<?php echo $grid_class;?> soapbox">
					<div class="dcol-pad">	

						<?php if($box_icon)
								echo self::_get_box_image( $bpost, $box_icon, $box_link, $clone_id); ?>

							<div class="fboxinfo fix bd">

								<div class="fboxtitle">
									<h3>
	<?php 							if($box_link) 
										printf('<a href="%s">%s</a>', $box_link, $bpost->post_title );
									else 
										echo do_shortcode($bpost->post_title); ?>
									</h3>
								</div>
								<div class="fboxtext">
									<?php 
									echo apply_filters('the_content', $bpost->post_content.pledit($bpost->ID)); 
									$this->_get_soapbox_links( $bpost, $clone_id);
									?>
								</div>
							</div>
							<?php pagelines_register_hook( 'pagelines_box_inside_bottom', $this->id ); // Hook ?>
					</div>
				</section>
	<?php 
				$row_count++;
				$current_box++; 
			endforeach;	?>
			</div>
	<?php }
	


	function _get_soapbox_links( $bpost, $clone_id ){ 
		?>
		<div class="soapbox-links">
			<?php for( $i = 1; $i <= 3; $i++){
					
					$thelink = '_soapbox_link_'.$i;

					$oset = array('post_id' => $bpost->ID, 'clone_id' => $clone_id);
					$link = ploption($thelink, $oset);

					global $post;
					$oset = array('post_id' => $post->ID, 'clone_id' => $clone_id);
		
					if ( !ploption( $thelink.'_text', $oset ) ){
						$oset = array('post_id' => $bpost->ID, 'clone_id' => $clone_id);					
					}

					if( ploption( $thelink.'_text', $oset ) ){
						
						$link_text = ( ploption( $thelink.'_text', $oset) ) ? ploption( $thelink.'_text', $oset) : 'Go'; 
						$link_class = ploption( $thelink.'_class', $oset);
						
						printf('<a href="%s" class="%s button">%s</a>', $link, $link_class, $link_text ); 
						
					}
				}
			pagelines_register_hook( 'pagelines_soapbox_links', $this->id ); // Hook 
			?>
		</div>
	<?php }

	function _get_box_image( $bpost, $box_icon, $box_link = false, $clone_id = ''){
			global $pagelines_ID;
			global $post;
			
			$oset = array( 'post_id' => $pagelines_ID, 'clone_id' => $clone_id );
			
			$soapbox_height_media = ( ploption('_soapbox_height_media', $oset) ) ? ploption('_soapbox_height_media', $oset) : 200; 
			
			// Make the image's tag with url
			$image_tag = sprintf('<img src="%s" alt="%s" style="display: inline;" />', $box_icon, esc_html($bpost->post_title) );
			
			// If link for box is set, add it
			if( $box_link ) 
				$image_output = sprintf('<a href="%s" title="%s">%s</a>', $box_link, esc_html($bpost->post_title), $image_tag );
			else 
				$image_output = $image_tag;
			
			$style = sprintf('width: 100%%;line-height: %1$spx; height: %1$spx;', $soapbox_height_media);
			
			$wrapper = sprintf('<div class="fboxgraphic img" style="%s">%s</div>', $style, $image_output);
			
			// Filter output
			return apply_filters('pl_soapbox_image', $wrapper, $bpost->ID);
	}

	function load_pagelines_boxes($set = null, $limit = null){
		$query = array();
		
		$query['post_type'] = 'boxes'; 
		$query['orderby'] 	= 'ID'; 
		
		if(isset($set)) 
			$query['box-sets'] = $set; 
			
		if(isset($limit)) 
			$query['showposts'] = $limit; 

		$q = new WP_Query($query);
		
		if(is_array($q->posts)) 
			return $q->posts;
		else 
			return array();
	
	}
// End of Section Class //
}
