<?php
/*
	Section: Wicker
	Author: Bearded Avenger
	Author URI: http://nickhaskins.com
	Description: A responsive full width slider section.
	Class Name: baWicker
	Cloning: true
	Demo: http://wicker.nichola.us
	Workswith: main, templates, header
	Version: 1.2.4
*/

/**
 * Main section class
 *
 * @package PageLines Framework
 * @author PageLines
 */
class baWicker extends PageLinesSection {

	var $default_limit = 3;

	const version = '1.5.3';


	function section_persistent(){
		register_nav_menus( array( 'wicker_nav' => __( 'Wicker Navigation', 'wicker' ) ) );
	}

	function section_styles(){

		global $pagelines_ID;
		$oset = array('post_id'=> $pagelines_ID);

		wp_enqueue_script( 'wicker', $this->base_url.'/lib/responsiveslides.min.js',array('jquery'),self::version );
	}

	function section_head($clone_id){

		global $pagelines_ID;
		$oset = array('post_id'=> $pagelines_ID);

		$ssmode = (ploption('wicker_slideshow',$this->oset)) ? 'true' : 'false';
		$randomize = (ploption('wicker_randomize',$this->oset)) ? 'true' : 'false';
		$wicker_speed = (ploption('wicker_transition_speed',$this->oset)) ? (ploption('wicker_transition_speed',$this->oset)) : 500;
        $wicker_timeout = (ploption('wicker_timeout',$this->oset)) ? (ploption('wicker_timeout',$this->oset)) : 4000;

        //$wicker_pause = ploption('wicker_pause',$oset) ? 'true' : 'false';


		$clone_class = 'wicker-clone'.$clone_id;

		?>
			<script>
			jQuery(document).ready(function () {
			    jQuery(".wicker-slides.<?php echo $clone_class;?>").responsiveSlides({
			    	auto: <?php echo $ssmode;?>,
			    	random: <?php echo $randomize;?>,
			    	speed: <?php echo $wicker_speed;?>,
      				timeout: <?php echo $wicker_timeout;?>,
			    });

			    <?php if(ploption('wicker_do_hero',$this->oset)) { ?>

				    function matchHeight() {
					    var newHeight = jQuery('.wicker.has-hero').outerHeight()-150;
					    jQuery(".wicker-hero-right").height(newHeight);
					}

					jQuery.event.add(window,"load",matchHeight);
					jQuery.event.add(window,"resize",matchHeight);

					jQuery(".wicker-hero-container").responsiveSlides({
				    	auto: <?php echo $ssmode;?>,
				    	random: <?php echo $randomize;?>,
				    	speed: <?php echo $wicker_speed;?>,
	      				timeout: <?php echo $wicker_timeout;?>,
				    });


				<?php } ?>

			});

			</script>
		<?php
	}

   	function section_template($clone_id) {

	   	$patturl = $this->images;
	   	$hidepatt = (ploption('wicker_hide_pattern',$this->oset)) ? 'hide-patt' : false;
	   	$wicker_height = (ploption('wicker_height',$this->oset)) ? ploption('wicker_height',$this->oset).'px' : '350px';
	   	$wickerpatt = ploption('wicker_pattern',$this->oset) ? ploption('wicker_pattern',$this->oset) :  'pat5';
	   	$menustyleclass = ploption('wicker_toggle_nav',$this->oset) ? 'has-menu' : '';
	   	$herostyleclass = ploption('wicker_do_hero',$this->oset) ? 'has-hero' : '';


		printf('<div class="wicker %s %s %s" style="max-height:%s;background:url(%s/%s.png)">',$hidepatt,$menustyleclass,$herostyleclass,$wicker_height,$patturl,$wickerpatt);


			$nav_pos = (ploption('wicker_nav_position')) ? (ploption('wicker_nav_position')) : false;
			$nav_align = sprintf('wicker-%s', $nav_pos);

			if(ploption('wicker_toggle_nav',$this->oset))

				if(function_exists('wp_nav_menu'))
					wp_nav_menu(
						array(
							'menu_class'  => 'unstyled wicker_type wicker_nav ' . $nav_align,
							'theme_location'=>'wicker_nav',
							'depth' => 1,
							'fallback_cb'=>'wicker_nav_fallback'
						)
					);
				else
					nav_fallback();
			?>

			<ul class="wicker-slides <?php echo 'wicker-clone'.$clone_id;?>">

			<?php

				$output = '';

				$slides = (ploption('wicker_num', $this->oset)) ? ploption('wicker_num', $this->oset) : $this->default_limit;

				for($i = 1; $i <=$slides; $i++) {

					if(ploption('wicker_image_'.$i, $this->oset)){

						$img = ploption('wicker_image_'.$i,$this->oset);
						$alt = ploption('wicker_image_alt_'.$i,$this->oset);

						$slide = sprintf('<img src="%s" alt="%s">',$img,$alt);

						$output .= sprintf('<li>%s</li>',$slide);
					}
				}

				if ($output == '') {

					$this->do_defaults();

				} else {

					echo $output;

				}

			?>

			</ul>

			<?php if(ploption('wicker_do_hero',$this->oset)) {
				?>
				<div class="wicker-hero-wrap <?php echo $menustyleclass;?>">
					<div class="wicker-hero-container">
						<?php $this->get_hero_carousel(); ?>

					</div>
				</div>
			<?php } ?>

		</div>

		<?php 
	}

	function do_defaults(){

		printf(
			'<li><img src="%s" /></li><li><img src="%s" /></li><li><img src="%s" /></li>',
			$this->images.'/1.jpg',
			$this->images.'/2.jpg',
			$this->images.'/3.jpg'
		);
	}

	function get_hero_carousel() {

		global $pagelines_ID;
		global $post;
		$oset = array('post_id'=> $pagelines_ID);
		$wickerslides = ploption('wicker_hero_slide_num',$oset) ? ploption('wicker_hero_slide_num',$oset) : $this->default_limit;


		if(ploption('wicker_hero_posts_mode',$this->oset)) {

	    	$show = (ploption('wicker_hero_post_count',$this->oset)) ? ploption('wicker_hero_post_count',$this->oset) : $this->default_limit;

	    	$cat = ploption('wicker_hero_post_incl_cats',$this->oset);
			$anypt = ploption('wicker_hero_post_incl_types',$this->oset);
			$orderby = ploption('wicker_hero_post_orderby', $this->oset);
			$order = ploption('wicker_hero_post_order', $this->oset);


			$args = array(
				'post__not_in' => get_option('sticky_posts'), // skip stickies breaks layout
				'posts_per_page' => $show,
				'category_name' => $cat,
				'post_type'  => $anypt,
				'orderby'   => $orderby,
				'order'    => $order,
			);

	    	$wickerheropost = new WP_Query($args);

	    	while ($wickerheropost->have_posts()) : $wickerheropost->the_post();

				$img_id = get_post_thumbnail_id($post->ID);
				$url = wp_get_attachment_url( get_post_thumbnail_id($post->ID, 'thumbnail') );

	    		$alt_text = get_post_meta($img_id,'_wp_attachment_image_alt',true);

	    		$postdesc = get_the_excerpt();

	    		$readmore = sprintf('<a href="%s" class="btn btn-large btn-primary" title="continue&nbsp;reading">Read More&nbsp;&nbsp;&nbsp;&rarr;</a>',get_permalink() );

				$herolt = sprintf('<h1>%s</h1><p>%s</p><div class="wicker-hero-cta">%s</div>',get_the_title(),$postdesc,$readmore);

				?><div class="wicker-hero-slide"><div class="wicker-hero-left"><?php echo $herolt;?></div><div class="wicker-hero-right wicker-match-height"><img class="wicker-hero-img" src="<?php echo $url ;?>" alt="<?php echo $alt_text;?>"></div></div><?php

			endwhile;

    		wp_reset_query();


		} else {

			$output = '';

			for($w = 1; $w <=$wickerslides; $w++){

				if(ploption('wicker_hero_slide_img_'.$w,$this->oset)) {

					$heroimg = ploption('wicker_hero_slide_img_'.$w,$this->oset) ? ploption('wicker_hero_slide_img_'.$w,$this->oset) : 'http://placehold.it/390x250';
					$heroalt = ploption('wicker_hero_slide_img_alt_'.$w,$this->oset);
					$herodesc = ploption('wicker_hero_slide_img_cap_desc_'.$w,$this->oset) ? ploption('wicker_hero_slide_img_cap_desc_'.$w,$this->oset) : 'Desc';
					$herolinkname = ploption('wicker_hero_slide_img_link_name_'.$w,$this->oset) ? ploption('wicker_hero_slide_img_link_name_'.$w,$this->oset) : 'Label';
					$herolink = ploption('wicker_hero_slide_img_link_'.$w,$this->oset) ? ploption('wicker_hero_slide_img_link_'.$w,$this->oset) : '#';
					$herolinktarget = ploption('wicker_hero_slide_img_link_target_'.$w,$this->oset);
					$heroheading = ploption('wicker_hero_slide_heading_'.$w,$this->oset) ? ploption('wicker_hero_slide_heading_'.$w,$this->oset) : 'The Heading';
					$heroimgalt = ploption('wicker_hero_slide_img_alt_'.$w,$this->oset);
					$buttheme = ploption('wicker_hero_slide_button_theme_'.$w,$this->oset) ? ploption('wicker_hero_slide_button_theme_'.$w,$this->oset) : 'primary';
					$herobutticon = ploption('wicker_hero_slide_icon_'.$w,$this->oset);

						if($herobutticon){
							$herolinkfull = sprintf('<a href="%s" target="%s" class="btn btn-%s btn-large"><i class="icon-%s"></i> %s</a>',$herolink,$herolinktarget,$buttheme,$herobutticon,$herolinkname);
						} else {
							$herolinkfull = sprintf('<a href="%s" target="%s" class="btn btn-%s btn-large">%s</a>',$herolink,$herolinktarget,$buttheme,$herolinkname);
						}

						$herolt = sprintf('<h1>%s</h1><p>%s</p><div class="wicker-hero-cta">%s</div>',do_shortcode($heroheading),do_shortcode($herodesc),$herolinkfull);

						$herort = sprintf('<img class="wicker-hero-img" src="%s" alt="%s">',$heroimg,$heroimgalt);

					$output .= sprintf('<div class="wicker-hero-slide"><div class="wicker-hero-left">%s</div><div class="wicker-hero-right wicker-match-height">%s</div></div>',$herolt,$herort);

				}

			}

			if ($output == '') {

				$this->wicker_hero_slide_defaults();

			} else {

				echo $output;

			}

		}
	}

	function wicker_hero_slide_defaults() {

		$herolt = sprintf('');
		$herort = sprintf('');

		for($o=1; $o<=3; $o++){
			$output = printf('<div class="wicker-hero-slide">
				<div class="wicker-hero-left">
									<h1>Heading</h1>
									<p>Desription</p>
									<a href="#" class="btn btn-primary btn">Label</a>
									</div>
									<div class="wicker-hero-right">
									<img class="wicker-hero-img wicker-match-height" src="http://placehold.it/390x250" alt="alt">
									</div>
							</div>');
		}
		
		return $output;
	}

	function section_optionator( $settings ){

		global $post_ID;
		$oset = array('post_id' => $post_ID, 'clone_id' => $settings['clone_id'], 'type' => $settings['type']);

		$slides = (ploption('wicker_num', $oset)) ? ploption('wicker_num', $oset) : $this->default_limit;
		$wickerslides = ploption('wicker_hero_slide_num',$oset) ? ploption('wicker_hero_slide_num',$oset) : $this->default_limit;
		$settings = wp_parse_args( $settings, $this->optionator_default );

		$postmode = ploption('wicker_hero_posts_mode',$oset);

		$array = array();

		$array['wicker_num'] = array(
			'type' 			=> 'count_select',
			'count_start'	=> 1,
			'count_number'		=> 10,
			'default'		=> '3',
			'inputlabel' 	=> __( 'Number of Slides to Configure', 'wicker' ),
			'title' 		=> __( 'Number of Slides', 'wicker' ),
			'shortexp' 		=> __( 'Choose the number of slides to configure. <strong>Default is 3</strong>', 'wicker' ),
			'exp' 			=> __( "This number will be used to generate slides and option setup.", 'wicker' ),

		);

		for($i = 1; $i <= $slides; $i++){


			$array['wicker_slide_'.$i] = array(
				'type' 			=> 'multi_option',
				'selectvalues' => array(
					'wicker_image_'.$i 	=> array(
						'inputlabel' 	=> __( 'Slide Image', 'wicker' ),
						'type'			=> 'image_upload'
					),
					'wicker_image_alt_'.$i  => array(
						'inputlabel'    => __('Slide Alt Text','wicker'),
						'type'          => 'text'
					),
				),
				'title' 		=> __( 'Wicker Slide ', 'wicker' ) . $i,
				'shortexp' 		=> __( 'Setup options for slide number ', 'wicker' ) . $i,
				'exp'			=> __( 'For best results all images in the slider should have the same dimensions.', 'wicker')
			);

		}

		$array['wicker_pattern'] = array(
			'default'	=> '',
			'type'		=> 'graphic_selector',
        	'showname'	=> true,
			'sprite'		=> $this->images.'/pattern-sprite.png',
			'height'		=> '30px',
			'width'			=> '30px',
			'layout' 		=> 'interface',
			'selectvalues'	=> array(
				'pat1'	   => array('name' => '1','offset' => '-348px 0px'),
				'pat2'	   => array('name' => '2','offset' => '-313px 0px'),
				'pat3'	   => array('name' => '3','offset' => '-35px 0px'),
				'pat4'	   => array('name' => '4','offset' => '-522px 0px'),
				'pat5'	   => array('name' => '5','offset' => '-661px 0px'),
			),
           'inputlabel'  => 'Select a pattern overlay', 'wicker',
           'title'       => 'Pattern Overlay', 'wicker',
           'shortexp'    => 'Select a pattern to overlay the section', 'wicker',
           'exp' => 'The patterns above are semi-transparent, so your image will show through.',
	    );

		$array['wicker_slide_options'] = array(
			'type'         => 'multi_option',
			'title' 		=> 'Wicker Options',
			'shortexp'      => 'Setup options for slider',
			'selectvalues'  => array(
				'wicker_hide_pattern' => array(
					'type' 	=> 'check',
					'inputlabel'  => 'Hide Pattern'
				),
				'wicker_slideshow' => array(
					'type' 			=> 'check',
					'inputlabel'	=> 'Slideshow Mode',
				),
				'wicker_randomize'  => array(
					'type' 			=> 'check',
					'inputlabel'	=> 'Randomize Slides',
				),
				'wicker_height' => array(
					'type' 			=> 'text_small',
					'shortexp' 		=> __( 'Sets a maximum height for the aection', 'wicker' ),
					'inputlabel'	=> __( 'Maximum Height of the section in Pixels (default is 400)', 'wicker' ),
				),
				'wicker_transition_speed'  => array(
					'type' 			=> 'text_small',
					'inputlabel'	=> 'Transition Speed (default is 1000)',
				),
				'wicker_timeout'  => array(
					'type' 			=> 'text_small',
					'inputlabel'	=> 'Transition Timeout (default is 4000)',
				),
			),
		);

		$array['wicker_nav_multi'] = array(
			'type' 	=> 'multi_option',
			'title' => 'Menu Setup',
			'shortexp' => 'Show an optional Worpdress menu',
			'selectvalues'  => array(
				'wicker_toggle_nav' => array(
					'type'    =>'check',
					'inputlabel' => 'Show Wordpress Menu',
				),
				'wicker_nav_position' => array(
					'type' => 'select',
					'inputlabel' =>'Menu Position',
					'selectvalues'	=> array(
						'left'	   => array('name'	=>'Align Left'),
						'right'	=> array('name'	=>'Align Right'),
					),
				),
			),
			'exp' => 'Setup your menu and specify the menu under Apperence-->Menus',
		);

		$array['wicker_hero_options'] = array(
			'type'         => 'multi_option',
			'title' 		=> 'Wicker Hero',
			'shortexp'      => 'Setup options for Hero Mode',
			'selectvalues'  => array(
				'wicker_do_hero' => array(
					'type' 	=> 'check',
					'inputlabel'  => 'Enable Hero Mode'
				),
			),
		);

		if(ploption('wicker_do_hero',$oset)) {

			if(!ploption('wicker_hero_posts_mode',$oset)) {

				$array['wicker_hero_slide_num'] = array(
					'type' 			=> 'count_select',
					'count_start'	=> 1,
					'count_num'		=> 20,
					'default'		=> '3',
					'title' 		=> __( 'Number of Images to Setup', 'reveal' ),
					'shortexp' 		=> __( 'Enter the number of images to setup. <strong>Default is 3</strong>', 'reveal' ),
					'exp' 			=> __( "This number will be used to generate option fields below. To control number of Posts in Posts Mode, use the above options.", 'reveal' ),
				);

				for($w = 1; $w <= $wickerslides; $w++){

					$array['wicker_hero_image_options_'.$w] = array(
						'type' => 'multi_option',
						'selectvalues' => array(
							'wicker_hero_slide_heading_'.$w => array(
								'type' => 'text',
								'inputlabel' => 'Slide Heading'
							),
							'wicker_hero_slide_img_cap_desc_'.$w => array(
								'type' => 'textarea',
								'inputlabel' => 'Caption Description'
							),
							'wicker_hero_slide_img_link_'.$w => array(
								'type' => 'text',
								'inputlabel' => 'Button Link'
							),
							'wicker_hero_slide_img_link_name_'.$w => array(
								'type' => 'text',
								'inputlabel' => 'Button Label'
							),
							'wicker_hero_slide_icon_'.$w => array(
								'type' => 'text',
								'inputlabel' => 'Button Icon'
							),
							'wicker_hero_slide_img_link_target_'.$w => array(
								'type' => 'text_small',
								'inputlabel' => 'Button Link Target'
							),
							'wicker_hero_slide_img_'.$w => array(
								'type' => 'image_upload',
								'inputlabel' => 'Hero Image'
							),
							'wicker_hero_slide_img_alt_'.$w => array(
								'type' => 'text',
								'inputlabel' => 'Hero Image Alt'
							),
							'wicker_hero_slide_button_theme_'.$w => array(
								'type' => 'select',
								'inputlabel' => 'Button Theme',
								'selectvalues'	=> array(
									'primary'	=> array('name' => 'Blue'),
									'warning'	=> array('name' => 'Orange'),
									'important'	=> array('name' => 'Red'),
									'success'	=> array('name' => 'Green'),
									'info'		=> array('name' => 'Light Blue'),
									'reverse'	=> array('name' => 'Grey'),
									'inverse'	=> array('name' => 'Black'),
								),
							),
						),
						'title' 		=> __( 'Wicker Hero Slide ', 'wicker' ) . $w,
						'shortexp' 		=> __( 'Setup options for Wicker Hero Slide ', 'wicker' ) . $w,
						'exp'           => __('','wicker')
					);
				}

			}

			$array['wicker_hero_posts_mode'] = array(
				'title' => 'Enable Hero Posts Mode',
				'type' => 'check',
				'shortexp' => 'Enable posts to be used instead of options',
				'inputlabel' => 'Use Posts for Source',
				'exp'     => 'Click "Save Meta Settings" above after toggling this box, and refresh the page to see the new options.'
			);

			if(ploption('wicker_hero_posts_mode',$oset)) {
				$array['wicker_hero_post_mode_options'] = array(
					'title' => 'Hero Post Mode Options',
					'shortexp' => 'Options for when using in Hero Posts Mode',
					'type' => 'multi_option',
					'selectvalues' => array(
						'wicker_hero_post_count' => array(
							'type' => 'text_small',
							'inputlabel' => 'Number of Posts to Show'
						),
						'wicker_hero_post_incl_cats' => array(
							'type' => 'text',
							'inputlabel' => 'Include these category(s)',
						),
						'wicker_hero_post_incl_types' => array(
							'type' => 'text',
							'inputlabel' => 'Post Types(s)',
						),
						'wicker_hero_post_orderby' => array(
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
						'wicker_hero_post_order' => array(
							'type'		=> 'select',
							'selectvalues'	=> array(
								'DESC' 		=> array('name' => __( 'Descending', 'pagelines' ) ),
								'ASC' 		=> array('name' => __( 'Ascending', 'pagelines' ) ),
							),
							'inputlabel'	=> __( 'Select sort order.', 'pagelines' ),
						),
					),
					'exp'=>'<strong>Number of Posts</strong>: By default it will pull all posts from whatever you specify.<br /><br />
		               		<strong>Include/Exclude Categories</strong>: You can include or exclude any category, or categories by name. Enter multiple separated by commas. For example, to include posts from the categories Alpha and Bravo, list them as <code>alpha,bravo</code>.<br /><br />
		               		<strong>Include/Exclude Taxonomies</strong>: You can include or exclude any tag, or tags by name. Enter multiple separated by commas. For example, to include posts from the tags Alpha and Bravo, list them as <code>alpha,bravo</code>.<br /><br />
		               		<strong>Include/Exclude Post Types</strong>: You can include or exclude any custom post type, or post types by name. Enter multiple separated by commas. For example, to include posts from the custom post types Alpha and Bravo, list them as <code>alpha,bravo</code>. Does not currently support PageLines post types (like boxes), due to the way PageLines currently handles post type links.<br /><br />
		               		<strong>Include Authors</strong>: You can include or exclude any author, or authors by name. Enter multiple separated by commas. For example, to include posts from the authors Sam and Elliot, list them as <code>sam,elliot</code>.<br /><br />
		               		<strong>Page ID</strong>: This is handy for showing the contents of a specific page, on another page, simply by inputting the ID of the page you want embedded. Kind of like, embedding a page into a page. Make sure your number of posts is set to 1, if you are going to use this option.',
				);
			}

		} // end wicker_do_hero


		$array['wicker_more_info' ]   = array(
			'type' 			=> '',
			'title'      => '<strong style="display:block;font-size:16px;color:#eaeaea;text-shadow:0 1px 0 black;padding:7px 7px 5px;background:#333;margin-top:5px;border-radius:3px;border:1px solid white;letter-spacing:0.1em;box-shadow:inset 0 0 3px black;">TIPS ON USE:</strong>',
			'shortexp'   => '<strong>&bull; Hero Mode:</strong> When you enable Hero Mode, you can specify whether or not Wicker Hero will use PageLines Options for Slides, or Wordpress Posts.<br/><br /><strong>&bull; Slides as Hero:</strong> By default 3 slides are used, however you can use the dropdown to specify up to 10 slides. Each slide has it\'s own option for a slide title, description, link, link icon, image, and image alt tag.<br/><br /><strong>&bull; Posts as Hero:</strong> By default this mode will use posts for content. It will use the excerpt for the description, and will use the Posts\' featured image for the image.<br/><br /><strong>&bull; Slide Synchronization:</strong> Any Options you apply to the main slides will be applied to the inner slides as well. This means that if you tick Slideshow Mode above, then both sets of slides will rotate at the same time.',
		);

		$metatab_settings = array(
			'id' 		=> 'wicker_options',
			'name' 		=> __( 'Wicker', 'wicker' ),
			'icon' 		=> $this->icon,
			'clone_id'	=> $settings['clone_id']
		);

		register_metatab( $metatab_settings, $array );
	}

}

if(!function_exists('wicker_nav_fallback')){

	function wicker_nav_fallback() {

			$nav_pos = (ploption('wicker_nav_position')) ? (ploption('wicker_nav_position')) : false;
			$nav_align = sprintf('wicker-%s', $nav_pos);

		printf('<ul id="wicker_nav_fallback" class="unstyled wicker_nav wicker_type %s">%s</ul>', $nav_align, wp_list_pages( 'title_li=&sort_column=menu_order&depth=1&echo=0'));
	}
}
