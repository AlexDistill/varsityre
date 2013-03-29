<?php
/*
	Section: Response Gallery
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: A responsive photo gallery section
	Class Name: PLGal
	Demo: http://response.pagelines.me/
	Workswith: templates, main, header, morefoot, content,sidebar1,sidebar2,sidebar_wrap
	Cloning: true
	Version:1.1.1
*/

/*
 * Main section class
 *
 * @package PageLines Framework
 * @author PageLines
 */
class PLGal extends PageLinesSection {

    var $tabID = 'response_meta';

	function section_head() {

		$ssmode = (ploption('plgallery_slideshow',$this->oset) == 'on');

			?>
				<script type="text/javascript">
					jQuery(window).load(function() {
					  // The slider being synced must be initialized first
					  jQuery('#carousel').flexslider({
					    animation: "slide",
					    controlNav: false,
					    animationLoop: true,
					    slideshow: false,
					    itemWidth: 125,
					    itemMargin: 5,
					    asNavFor: '#slider',
					    namespace: "gall-",
					  });
					   
					  jQuery('#slider').flexslider({
					    animation: 'fade',
					    controlNav: false,
					    smoothHeight: true, 
					    animationSpeed: 400, 
					    animationLoop: true,
					    slideshow: '<?php echo $ssmode;?>',
					    slideshowSpeed: 5000,
					    sync: "#carousel",
					    namespace: "gall-",
					  });

					  jQuery('.no-js .slides').removeClass('li:first-child');

					});
		 		</script>
	 		<?php
	 	
	}


	function section_styles(){
		wp_enqueue_script( 'flexslider',  $this->base_url.'/jquery.flexslider-min.js', array('jquery'), '1.4.8', true );
		wp_enqueue_style( 'flexslider-css' );
	}

	/**
	* Section template.
	*/
    function section_template( $clone_id ) { 

		global $post;

		$plgallery_layout = ploption('plgallery_layout_chooser',$this->oset);
		
		$plg_numposts = (ploption('plg_numposts',$this->oset)) ? ploption('plg_numposts',$this->oset) : -1;
		$plg_order = (ploption('plg_order',$this->oset)) ? ploption('plg_order',$this->oset) : 'ASC';
		$plg_orderby = (ploption('plg_orderby',$this->oset)) ? ploption('plg_orderby',$this->oset) : 'menu_order';
		$plg_include = (ploption('plg_include',$this->oset)) ? ploption('plg_include',$this->oset) : false;
		$plg_exclude = (ploption('plg_exclude',$this->oset)) ? ploption('plg_exclude',$this->oset) : false;
		
		$this->max_width = (ploption('plg_width',$this->oset)) ? ploption('plg_width',$this->oset).'px' : '600px';

		
		$query = array(  
		    'post_status' => null,
		    'post_type' => 'attachment',
		    'orderby' => $plg_orderby,  
		    'order'=> $plg_order,
		    'post_mime_type' => 'image',
		    'post_parent' => $post->ID,
		    
			'numberposts' => $plg_numposts,  
		);
		
		if($plg_exclude)
			$query['exclude'] = $plg_exclude; 
			
		if($plg_include)
			$query['include'] = $plg_include; 
		
		$images = get_posts( $query ); 
		
		if(!is_array($images) || empty($images) || count($images) <= 2){
			echo setup_section_notify($this, __('Not enough images in page media library.', 'pagelines'), null, 'Upload Media');
			return;
		}
		
		switch ($plgallery_layout) {

			case 'nothumbs':
				$this->nothumbs( $images );
				break;
			case 'thumbtop':
			 	$this->thumbtop( $images );
			 	break;
			default:
				$this->thumbbott( $images );
				break;

		}

	}


	function section_optionator( $settings ){
		
		$settings = wp_parse_args($settings, $this->optionator_default);
	
		$option_array = array(
			'plgallery_layout_chooser' => array(
					'default'	=> '',
					'type'		=> 'graphic_selector',
	            	'showname'	=> true,
					'sprite'		=> PL_EXTEND_URL.'/responsive-gallery/images/theme-options.png',
					'height'		=> '88px', 
					'width'			=> '130px',
					'layout' 		=> 'interface',	
					'selectvalues'	=> array(
						'nothumbs'	=> array('name' => __( "No Thumbnails", 'pagelines' ), 'offset' => '0px 0px'),
						'thumbtop'	=> array('name' => __( "Thumbnails on Top", 'pagelines' ), 'offset' => '0px -87px'),
						'thumbbott'	=> array('name' => __( "Thumbnails on Bottom", 'pagelines' ), 'offset' => '0px -175px'),
					),
	               'title'        => __( 'Gallery Layout', 'pagelines' ),                        
	               'shortexp'    => __( 'Select a layout for your gallery', 'pagelines' ),
	               'exp'           => 'Choose a thumbnail position for the gallery. This gallery works with the standard <code>[gallery]</code> Wordpress shortcode, thats generated and inserted on the page where you want the gallery to display. <br /><br />1.Upload images using the Media Uploader on any page on your site, and insert a standard Worpdress gallery. <br /> 2.<em>Responsive Gallery</em> will automagically transform the generic Wordpress gallery into a beautiful <em>Responsive Gallery</em>.'
	       ),
			'plg_width' => array(
				'type' 			=> 'text_small',			
				'title' 		=> 'Maximum Gallery Width',
				'shortexp' 		=> 'Set a maximum limit to the width of the responsive gallery', 
				'inputlabel'	=> 'Maximum Width in Pixels (Default: 600)',
				
			),
			'plgallery_slideshow' => array(
				'type' 			=> 'check',			
				'title' 		=> 'Slideshow Mode',
				'shortexp' 		=> 'Images rotating every 5 seconds', 
				'inputlabel'	=> 'Turn on Slideshow Mode',
				'exp'           => 'Selecting this option will cause the images to automatically rotate in a slideshow type fashion.'
			),
			'plgallery_configs' => array(
				'type' 			=> 'multi_option',			
				'title' 		=> 'Config Options - Optional',
				'shortexp'		=> 'Control the selection, ordering and number of images in Response',
				'selectvalues'	=> array(
					'plg_order'		=> array('inputlabel' => 'Order Type (Default: ASC)', 'type'	=> 'select', 'selectvalues' => array(
						'ASC'			=> array('name'	=> "Ascending Order"),
						'DESC'			=> array('name'	=> "Descending Order"),
					)), 
					'plg_orderby'	=> array('inputlabel' => 'Order According To (Default: Menu Order)', 'type'	=> 'select', 'selectvalues' => array(
						'menu_order'	=> array('name'	=> "Using Add Media Popup (menu order)"),
						'title'			=> array('name'	=> "Using Image Title"),
						'post_date'		=> array('name'	=> "Using Post Date"),
						'rand'			=> array('name'	=> "Random Selection"),
						'ID'			=> array('name'	=> "Attachment ID"),
					)), 
					'plg_numposts'	=> array('inputlabel' => 'Maximum Amount of Images (Default: Unlimited; Must be more than 2)', 'type'	=> 'text_small'), 
					'plg_exclude'	=> array('inputlabel' => 'Exclude Attachment IDs - Comma Separated (Default: None)', 'type'	=> 'text'), 
					'plg_include'	=> array('inputlabel' => 'Include Attachments IDs - Comma Separated (Default: All)', 'type'	=> 'text'), 
				)
			),
		);
	
		$metatab_settings = array(
			'id' 		=> $this->tabID,
			'name' 		=> 'Response',
			'icon' 		=> $this->icon, 
			'clone_id'	=> $settings['clone_id'], 
			'active'	=> $settings['active']
		);
		
		register_metatab( $metatab_settings, $option_array);

	}



	function nothumbs( $images = false ) {

		if($images) { 
			?>
				<div class="pl-gallery-nothumb plg-gallery" style="max-width: <?php echo $this->max_width;?>">
					<?php $this->draw_images($images); ?>  	
				</div>
			<?php
		}
	}

	function thumbtop( $images = false ) {
		
		if($images) { 
			?>  
				<div class="pl-gallery-thumbstop plg-gallery" style="max-width: <?php echo $this->max_width;?>">
					<?php 
					$this->draw_thumbs($images);
						$this->draw_images($images);		
						
							?>  
					
				</div>
			<?php
		} 
	}	

	function thumbbott( $images = false ) {
		
		if($images) { 
			?>  
				<div class="pl-gallery-thumbsbott plg-gallery" style="max-width: <?php echo $this->max_width;?>">
					<?php 
						$this->draw_images($images);		
						$this->draw_thumbs($images);
							?>  
						
				</div>
			<?php 
		}
	}
	
	function draw_images( $images ){
		?>
		<div id="slider" class="gallslider">
			<ul class="slides">
				<?php
				
		foreach($images as $image) { 
			    	
			printf(
				'<li><img src="%s" alt="%s" /></li>', 
				wp_get_attachment_url($image->ID, 'full', false,''), 
				get_post_meta($image->ID, '_wp_attachment_image_alt', true)
			);    
			
		}
		?>
			</ul>
		</div>
		<?php
	}
	
	function draw_thumbs($images){
		
		?>
		<div id="carousel" class="gallslider">
			<ul class="slides">
				<?php 
		
		foreach($images as $image) { 
		      
		    printf(
				'<li><img src="%s" alt="%s"/></li>', 
				wp_get_attachment_thumb_url($image->ID, 'thumbnail', false, ''), 
				get_post_meta($image->ID, '_wp_attachment_image_alt', true)
			);  
		       
		}
		?>
			</ul>
		</div>
		<?php
		
	}
	
	
	
} // ---- End of Class --- //















