<?php
/*
	Section: Better Carousel
	Author: Nick Haskins
	Author URI: http://nickhaskins.com
	Description: A responsive flickr, nextgen, or featured image carousel with post type support.
	Class Name: baBetterCarousel
	Cloning: true
	Workswith: content, header, footer, main 
	Demo: http://better-carousel.nichola.us
	Version: 1.0.2
*/

class baBetterCarousel extends PageLinesSection {

	const version = '1.0.2';
	
	function section_scripts(){
		wp_enqueue_script('better-carousel',$this->base_url.'/js/jquery.flexslider-min.js',array('jquery'),self::version);
	}
	
	function section_head( $clone_id = null ) {   

		$better_carousel_slideshow = ploption('better_carousel_ss',$this->oset) ? 1 : 0;
		$better_carousel_ss_speed = ploption('better_carousel_pause_time',$this->oset) ? ploption('better_carousel_pause_time',$this->oset) : 600;
		$better_carousel_anim_speed = ploption('better_carousel_scroll_time',$this->oset) ? ploption('better_carousel_scroll_time',$this->oset) : 7000;
		$better_carousel_item_width = ploption('better_carousel_item_width',$this->oset) ? ploption('better_carousel_item_width',$this->oset) : 125;
		$better_carousel_show_ctrlnav = ploption('better_carousel_show_ctrlnav',$this->oset) ? 1 : 0;
		$clone_class = 'better-clone'.$clone_id;

		?>
		<script type="text/javascript">
            jQuery(window).load(function() {
              jQuery('.better-carousel.<?php echo $clone_class;?>').flexslider({
                animation: "slide",
                controlNav: <?php echo $better_carousel_show_ctrlnav;?>,
                animationLoop: true,
                slideshow: <?php echo $better_carousel_slideshow;?>,
                slideshowSpeed: <?php echo $better_carousel_ss_speed;?>,
                itemWidth: <?php echo $better_carousel_item_width;?>,
                itemMargin: 5,
                namespace: "better-",
              });

              jQuery('.no-js .slides').removeClass('li:first-child');
            });
		</script>
	<?php }

	function section_optionator( $settings ){
		$settings = wp_parse_args($settings, $this->optionator_default);
		
			$metatab_array = array(
					'better_carousel_numbers' => array(
							'type' 		=> 'multi_option',
							'title' 	=> 'Carousel Autoscroll',
							'shortexp' 	=> 'Auto rotation options',
							'selectvalues'=> array(
								'better_carousel_ss'	=> array(
									'type'      => 'check',
									'inputlabel'  => 'Enable Auto Scroll',
								),								
								'better_carousel_pause_time'	=> array(
									'type'      => 'text_small',
									'inputlabel'  => 'Autoscroll Delay',
								),
								'better_carousel_scroll_time'		=> array(
									'type'      => 'text_small',
									'inputlabel'  =>'Autoscroll Speed (milliseconds)',
								),
							),
							'exp' 		=> '<strong>Enable Auto Scroll: </strong>Toggles auto rotation on the carousel.<br /><br />
											<strong>Autoscroll Delay:</strong> Controls the time between slides.<br /><br />
											<strong>Autoscroll Speed:</strong> Controls the speed of the animation (in milliseconds).<br /><br />',
					),
					'better_carousel_layout' => array(
							'type' 		=> 'multi_option',
							'title' 	=> 'Carousel Layout',
							'shortexp' 	=> 'Layout Options',
							'selectvalues'=> array(
								'better_carousel_show_ctrlnav' => array(
									'type'      => 'check',
									'inputlabel'  => 'Show Control Navigation',								
								),
								'better_carousel_hide_title' => array(
									'type'      => 'check',
									'inputlabel'  => 'Hide Item Titles',	
								),
								'better_carousel_items'	=> array(
									'type'      => 'text_small',
									'inputlabel'  =>'Items to Show',
								),
								'better_carousel_item_width' => array(
									'type'      => 'text_small',
									'inputlabel'  =>'Width of Items (default is 125)',									
								),							
							),
							'exp' 		=> '<strong>Show Control Navigation: </strong>Toggle controls for carousel.<br /><br />
											<strong>Hide Item Titles:</strong> Hides the post title and will only show the thumbnail.<br /><br />
											<strong>Items to Show:</strong> Controls how many posts are shown when using Posts Mode.<br /><br />
											<strong>Width of Items:</strong> Controls width of the items in the carousel.<br /><br />',
					),					
					'better_carousel_mode' => array(
						'type' => 'select',
						'default'	=> 'posts',
						'selectvalues'=> array(
							'posts' 		=> array( 'name' => 'Post Thumbnails (posts)'),							
							'flickr'		=> array( 'name' => 'Flickr Feed'),
							'ngen_gallery' 	=> array( 'name' => 'NextGen Gallery'), 
							'hook'			=> array( 'name' => 'Hook: "pagelines_better_carousel_list"')
						),					
						'title' 	=> 'Carousel Link Mode',
						'shortexp' 	=> 'Select the source of the thumbnails.',
						'exp'		=> '<strong> Post Thumbnails (default)</strong> - Uses links and thumbnails from posts <br/><strong>Flickr</strong> - Streams last 20 uploaded images.<br/><strong>NextGen Gallery</strong> - Uses an image gallery from the NextGen Gallery Plugin',
					),
					'better_carousel_post_options' => array(
						'type' => 'multi_option',
						'title' => 'Post Filtering',
						'shortexp' => 'For using with Posts',
						'selectvalues' => array(
							'better_carousel_post_id' => array(
								'type' 			=> 'text',
								'inputlabel' 	=> 'Include these Categories',
							),
							'better_carousel_cpt' => array(
								'type' 			=> 'text',	
								'inputlabel' 	=> 'Include these post types (when not using categories)',
							),
						),
						'exp' => 'You can either specify what categories to show the posts from, or choose a post type, but not both. If nothign is filled out, it will fetch the number of posts you specify above.',
					),
					'better_carousel_order_options' => array(
						'type'		=> 'multi_option',
						'title'		=> 'Post Ordering',
						'shortexp'	=> 'Post order options',
						'selectvalues'	=> array(
							'better_carousel_offset' => array(
								'type' => 'text_small',
								'inputlabel' => 'Post Offset'
							),
							'better_carousel_orderby' => array(
								'type' => 'select',
								'selectvalues' => array(
									'ID' 			=> array('name' => __( 'Post ID', 'pagelines' ) ),
									'title' 		=> array('name' => __( 'Title', 'pagelines' ) ),
									'date' 			=> array('name' => __( 'Date', 'pagelines' ) ),
									'modified' 		=> array('name' => __( 'Last Modified', 'pagelines' ) ),
									'rand' 			=> array('name' => __( 'Random', 'pagelines' ) ),							
									),
								'inputlabel'	=> __( 'Order posts by...', 'pagelines' ),
							),
							'better_carousel_order' => array(
								'type'		=> 'select',
								'selectvalues'	=> array(
									'DESC' 		=> array('name' => __( 'Descending', 'pagelines' ) ),
									'ASC' 		=> array('name' => __( 'Ascending', 'pagelines' ) ),
								),
								'inputlabel'	=> __( 'Select sort order.', 'pagelines' ),
							),
						),
					),
					'better_carousel_flickr_id' => array(
						'type' => 'text',					
						'title' => __( 'Flickr User ID (Flickr Mode)', 'pagelines' ),
						'shortexp' => __( 'Enter the ID of the Flickr user.', 'pagelines' ), 
						'exp'		=> __( 'Get your Flickr ID <a href="http://idgettr.com" target="_blank">here</a>', 'pagelines' )
					),
					'better_carousel_ngen_gallery' => array(
						'type' => 'text_small',					
						'title' => __( 'NextGen Gallery ID (NextGen Mode)', 'pagelines' ),
						'shortexp' => __( 'Enter the ID of the NextGen Image gallery for the carousel.', 'pagelines' ), 
						'exp'		=> __( '<strong>Note:</strong>The NextGen Gallery and carousel template must be selected.', 'pagelines' )
					),
					'better_carousel_more_info'    => array(
						'type' 			=> '',
						'title'      => '<strong style="display:block;font-size:16px;color:#eaeaea;text-shadow:0 1px 0 black;padding:7px 7px 5px;background:#333;margin-top:5px;border-radius:3px;border:1px solid white;letter-spacing:0.1em;box-shadow:inset 0 0 3px black;">TIPS ON USE:</strong>',
						'shortexp'   => '<strong>&bull; Posts Mode:</strong> Set a featured image on a post in order for a thumbnail to be shown. By default it will show the thumbnail size version of your featured image. Square images work best at minimum size 125px.
										<br/><br /><strong>&bull; Flickr Mode:</strong> Will stream in the last 20 images uploaded to your Flickr account. Supply a Flickr User ID if using Flickr Mode. FlickrRSS is NOT needed for Flickr Mode to work.
										<br/><br /><strong>&bull; NextGen Mode:</strong> Must have NextGen Gallery plugin installed, and activated. Supply the ID of the NextGen gallery to display thumbnails linked to a lightbox. Hint: adjust NextGen gallery thumbnails to be at minimum 125px across.
										<br/><br /><strong>&bull; Element Flickering on Sliding:</strong> On some occasions you may notice a slight flickering of anchor links on your site due to the way css transforms are carried out. Good news is, there\'s an easy way of fixing this. Apply the following css to sections affected by this. For example, if your Boxes section is flickering:<br />
										<pre>#boxes{-webkit-backface-visibility:hidden;}</pre>',
					),					
				);
			
			$metatab_settings = array(
					'id' 		=> 'better_carousel_meta',
					'name'	 	=> 'Better Carousel',
					'icon' 		=> $this->icon,
					'clone_id'	=> $settings['clone_id'], 
					'active'	=> $settings['active']
				);
			
			register_metatab($metatab_settings, $metatab_array);
	}
	
   	function section_template( $clone_id ) { 
		
		$better_carousel_class = (isset($clone_id) && $clone_id != 1) ? 'crsl'.$clone_id : 'crsl';
		$better_carousel_item_width = ploption('better_carousel_item_width',$this->oset) ? ploption('better_carousel_item_width',$this->oset) : 125;
		$carouselitems = ploption('better_carousel_items', $this->oset);
		
		$cmode = (ploption('better_carousel_mode', $this->oset)) ? ploption('better_carousel_mode', $this->oset): null;
		$ngen_id = (ploption('better_carousel_ngen_gallery', $this->oset)) ? ploption('better_carousel_ngen_gallery', $this->oset) : 1;
		$clone_class = 'better-clone'.$clone_id;	

		if( ($cmode == 'ngen_gallery' && !function_exists('nggDisplayRandomImages')) ){
		
			echo setup_section_notify($this, __("NextGen Gallery has to be installed and activated.", 'pagelines'), admin_url().'plugins.php', 'Setup Plugin');
		
		} else {

			$hascap = (!ploption('better_carousel_hide_title',$this->oset)) ? '' : 'no-cap';
			$hasnav = (!ploption('better_carousel_show_ctrlnav',$this->oset)) ? '' : 'has-nav';

		?> <div class="better-carousel <?php echo 'better-clone'.$clone_id;?> <?php echo $hasnav;?>">
			<ul class="slides <?php echo $hascap;?>">
				<?php 
				
					if(function_exists('nggDisplayRandomImages')  && $cmode == 'ngen_gallery'){
				
						echo do_shortcode('[nggallery id='.$ngen_id.' template=plcarousel]');

					} elseif($cmode == 'flickr') {

						$this->get_flickr_images();
				
					} elseif($cmode == 'hook')

						pagelines_register_hook('pagelines_better_carousel_list');
						
					else { 

						$this->get_post_images();
					
					}
					
				} ?>
			</ul>
		</div>

	<?php }

	function get_post_images() {

		$carouselitems = ploption('better_carousel_items', $this->oset);
		$better_carousel_post_id = ploption('better_carousel_post_id',$this->oset);
		$better_carousel_cpt = (ploption('better_carousel_cpt', $this->oset)) ? ploption('better_carousel_cpt', $this->oset): null;
		$orderby = ploption('better_carousel_orderby', $this->oset);
		$order = ploption('better_carousel_order', $this->oset);
		$better_carousel_item_width = ploption('better_carousel_item_width',$this->oset) ? ploption('better_carousel_item_width',$this->oset) : 125;
		$offset = ploption('better_carousel_offset',$this->oset);

		$args = array(
			'posts_per_page' => $carouselitems,
			'category_name' => $better_carousel_post_id,
			'post_type'  => $better_carousel_cpt,
			'orderby'   => $orderby,
			'order'    => $order,
			'offset'   => $offset,
		);

    	$betterposts = new WP_Query($args);

	    while ($betterposts->have_posts()) : $betterposts->the_post();								
		
            printf('<li><a href="'.get_permalink().'" title="'.get_the_title().'">');
                the_post_thumbnail('thumbnail',array('title' => ""));

               if(!ploption('better_carousel_hide_title', $this->oset)){

            		printf('<span class="better-carousel-item-title" style="width:'.$better_carousel_item_width.'px">'.get_the_title().'</span></a></li>');

            	} else {

            		printf('</a></li>');

            	}

		endwhile;

		wp_reset_query();
	}

	function get_flickr_images(){

		$flickrid = ploption('better_carousel_flickr_id',$this->oset) ? ploption('better_carousel_flickr_id',$this->oset) : '85819182@N00';
		$rss_link = sprintf('http://api.flickr.com/services/feeds/photos_public.gne?id=%s&lang=en-us&format=rss_200',$flickrid);
		$rss = simplexml_load_file( $rss_link );

		$regx = "/<img(.+)\/>/";
		$matches = array();

		foreach( $rss->channel->item as $img ) {
		    preg_match( $regx, $img->description, $matches );

		    echo '<li><a href="' . $img->link . '">' . $matches[ 0 ] . '';
		    echo '<span class="better-carousel-item-title">' . $img->title . '</span>';
		    echo '</a></li>';
		}
	}

}
