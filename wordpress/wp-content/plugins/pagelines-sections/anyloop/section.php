<?php
/*
	Section: Any Loop
	Author: Bearded Avenger
	Author URI: http://nickhaskins.com
	Description: Display any post, from any category, with any tag, in any amount, anywhere, any time.
	Class Name: baAnyLoop
	Cloning: true
	Workswith: main, templates, header,footer,sidebar_wrap,sidebar1,sidebar2
	Version: 1.0.5
	Demo: http://anyloop.nichola.us
*/

class baAnyLoop extends PageLinesSection {

	function section_persistent(){
		add_shortcode( 'anyloop-recent', array(&$this,'anyloop_recentposts_shortcode' ));
	}


	function section_scripts() {

		wp_enqueue_script('anyloop-masonry',$this->base_url.'/js/jquery.masonry.min.js',array('jquery'));
	}

	// JS Stuffs
	function section_head($clone_id) {
		
		$clone_class = 'any-clone'.$clone_id;
		$gridarticlewidth = (ploption('anyloop_grid_article_width',$this->oset)) ? (ploption('anyloop_grid_article_width',$this->oset)) : 200;

		?>
		<style>.anyloop-grid-posts article {width: <?php echo $gridarticlewidth;?>px; }</style>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					
					var container = jQuery('.anyloop-grid-posts.<?php echo $clone_class;?>');
					
					container.imagesLoaded( function(){
						container.masonry({
						  itemSelector: 'article',
						  isAnimated:true,
						  isFitWidth: true
						}); 
					});

				    jQuery('.anyloop_sharepop.<?php echo $clone_class;?>, .anyloop_comment_drawer_wrap.<?php echo $clone_class;?>').hide();
				    jQuery('.anyloop_share.<?php echo $clone_class;?>, .anyloop_comment_trigger.<?php echo $clone_class;?>').click(function(event){
				    	event.preventDefault();
	   					jQuery(this.parentNode).toggleClass('open');
	    				jQuery(this).siblings('div').slideToggle();
				    });
 
				    jQuery('.anyloop-recent-posts.<?php echo $clone_class;?> li').hover(function(){
				    	jQuery(this).toggleClass('anyfade');
				    });

				});
			</script>
	<?php }


	// Layout
   	function section_template( $clone_id ) { 

   		$anyloop = ploption('anyloop_layout',$this->oset);

   		if($anyloop == 'anyloop_full_posts') {
	   		
	   		$this->option_full_posts($clone_id);

	   	} elseif($anyloop == 'anyloop_recent_posts') {
	   		
	   		$this->option_recent_posts($clone_id);

	   	} elseif($anyloop == 'anyloop_grid_posts') {
	   		
	   		$this->option_grid_posts($clone_id);

	   	} else {

	   		echo setup_section_notify($this, __('Choose a layout for the posts under Site Options--->AnyLoop', 'pagelines'));

	   	}

	}

	// Recent Posts mode
	function option_recent_posts($clone_id = null) {
		
		$recent = (ploption('anyloop_post_count',$this->oset)) ? ploption('anyloop_post_count',$this->oset) : '3';

		$cat = ploption('anyloop_incl_cats',$this->oset);
		$anycatnot = ploption('anyloop_excl_cats',$this->oset);	
		$anyauth = ploption('anyloop_incl_authors',$this->oset);	
		$anytag = ploption('anyloop_incl_taxos',$this->oset);
		$anytagnot = ploption('anyloop_excl_taxos',$this->oset);	
		$anypageid = ploption('anyloop_page_id',$this->oset);	
		$anypt = ploption('anyloop_incl_types',$this->oset);
		$orderby = ploption('anyloop_orderby', $this->oset);
		$order = ploption('anyloop_order', $this->oset);

		$args = array(
			'posts_per_page' => $recent,
			'category_name' => $cat,
			'category__not_in' => $anycatnot,
			'author' => $anyauth,
			'tag'   => $anytag,
			'tag__not_in' => $anytagnot,
			'page_id'    => $anypageid,
			'post_type'  => $anypt,
			'orderby'   => $orderby,
			'order'    => $order,
		);
	
    	$anyrecent = new WP_Query($args);

    	?> <div class="anyloop-recent-posts <?php echo 'any-clone'.$clone_id;?>"><ul class="unstyled"> <?php

	   		while ($anyrecent->have_posts()) : $anyrecent->the_post();

				?>
				   <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute();?>"><li>
						<?php 

							if(!ploption('anyloop_hide_thumb', $this->oset)){
								$this->anyloop_thumb();
							}						

							if(!ploption('anyloop_hide_title', $this->oset)){
								$this->anyloop_recenttitle();
							}

							echo '<i class="icon-external-link"></i>';

			            ?>
			      	</li></a>
				<?php

			endwhile; 

    	wp_reset_query();

    	printf('</ul></div>');

	}

	// Recent Posts Shortcode
	function anyloop_recentposts_shortcode($atts,$clone_id = null) {
		
		extract( shortcode_atts( array( 
			'limit'     => 5,
			'category'  => '',
			'tag'       => '',
			'cpt'       => '',
			'author'    => '',
		), $atts ) );

		$args = array(
			'posts_per_page' => $limit, 
			'category_name' => $category,
			'category__not_in' => $tag,
			'author' => $author,
			'tag'   => $tag,
			'post_type'  => $cpt,
		);

		global $post;

		$q = new WP_Query($args);

    	$output = '<div class="anyloop-recent-posts any-clone"><ul class="unstyled">';

	   		while ($q->have_posts()) : $q->the_post();

				$output .= '<a href="'.get_permalink() .'" title="'.get_the_title().'"><li>'.get_the_post_thumbnail($post->ID, 'thumbnail').'<h6>'.get_the_title().'</h6><i class="icon-external-link"></i></li></a>';

			endwhile; 

    		wp_reset_query();

    	return $output .'</ul></div>';
	}

	// Full Posts Mode
	function option_full_posts($clone_id = null) {

		$recent = (ploption('anyloop_post_count',$this->oset));
		$cat = ploption('anyloop_incl_cats',$this->oset);
		$anycatnot = ploption('anyloop_excl_cats',$this->oset);	
		$anyauth = ploption('anyloop_incl_authors',$this->oset);	
		$anytag = ploption('anyloop_incl_taxos',$this->oset);
		$anytagnot = ploption('anyloop_excl_taxos',$this->oset);	
		$anypageid = ploption('anyloop_page_id',$this->oset);	
		$anypt = ploption('anyloop_incl_types',$this->oset);
		$orderby = ploption('anyloop_orderby', $this->oset);
		$order = ploption('anyloop_order', $this->oset);
		
		$args = array(
			'posts_per_page' => $recent,
			'category_name' => $cat,
			'category__not_in' => $anycatnot,
			'author' => $anyauth,
			'tag'   => $anytag,
			'tag__not_in' => $anytagnot,
			'page_id'    => $anypageid,
			'post_type'  => $anypt,
			'orderby'   => $orderby,
			'order'    => $order,
			'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1
		);

    	$anyfull = new WP_Query($args);

    	//if( ($this->view == 'single') &&  ($this->view == 'archive') )

    		?><div class="anyloop-full-posts <?php echo 'any-clone'.$clone_id;?>"><?php
		    
			    while ($anyfull->have_posts()) : $anyfull->the_post();
			    
				    ?><article id="post-<?php the_ID();?>" <?php post_class(); ?>>
						<?php 

							if(!ploption('anyloop_hide_title', $this->oset)){
								$this->anyloop_title();
							}

							if(!ploption('anyloop_hide_date', $this->oset)){
								$this->anyloop_date();
							}

							if(!ploption('anyloop_hide_content', $this->oset)){
								$this->anyloop_content();
							}

							if(!ploption('anyloop_hide_comments', $this->oset)){
								$this->anyloop_fullcomments($clone_id);
							}

			        printf('</article>'); 

				endwhile;

				wp_reset_query();

			printf('</div>');
	
	}

	// Grid Posts Mode
	function option_grid_posts($clone_id = null) {

		$recent = (ploption('anyloop_post_count',$this->oset)) ? ploption('anyloop_post_count',$this->oset) : '3';
		$cat = ploption('anyloop_incl_cats',$this->oset);
		$anycatnot = ploption('anyloop_excl_cats',$this->oset);	
		$anyauth = ploption('anyloop_incl_authors',$this->oset);	
		$anytag = ploption('anyloop_incl_taxos',$this->oset);
		$anytagnot = ploption('anyloop_excl_taxos',$this->oset);	
		$anypageid = ploption('anyloop_page_id',$this->oset);	
		$anypt = ploption('anyloop_incl_types',$this->oset);
		$orderby = ploption('anyloop_orderby', $this->oset);
		$order = ploption('anyloop_order', $this->oset);

		$args = array(
			'posts_per_page' => $recent,
			'category_name' => $cat,
			'category__not_in' => $anycatnot,
			'author' => $anyauth,
			'tag'   => $anytag,
			'tag__not_in' => $anytagnot,
			'page_id'    => $anypageid,
			'post_type'  => $anypt,
			'orderby'   => $orderby,
			'order'    => $order,
			'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1
		);
	
    	$anygrid = new WP_Query($args);

    	//if( ($this->view == 'single') &&  ($this->view == 'archive') )

    	?>
    	<div class="anyloop-grid-posts <?php echo 'any-clone'.$clone_id;?>">
    	<?php

	   		while ($anygrid->have_posts()) : $anygrid->the_post();	
		    
			    ?>
				    <article <?php post_class(); ?> id="post-<?php the_ID();?>">

						<?php 

							if(!ploption('anyloop_hide_title', $this->oset)){
								$this->anyloop_title();
							}
							
							if(!ploption('anyloop_hide_date', $this->oset)){
								$this->anyloop_date();
							}

							if(!ploption('anyloop_hide_thumb', $this->oset)){
								$this->anyloop_thumb();
							}
							
							if(!ploption('anyloop_hide_excerpt', $this->oset)){
								$this->anyloop_excerpt();
							}
							
							if(!ploption('anyloop_hide_comments', $this->oset)){
								$this->anyloop_comments();
							}

							$this->anyloop_share($clone_id);

						?>

			      	</article>
				<?php 

			endwhile;

			wp_reset_query();

		printf('</div>');
	}

	// Get the Title
	function anyloop_title() {
		?>
		<h3><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute();?>"><?php the_title_attribute();?></a></h3>
		<?php
	}

	function anyloop_recenttitle() {
		?>
		<h6><?php the_title_attribute();?></h6>
		<?php		
	}

	// Get the date
	function anyloop_date() {
		printf('<small class="anyloop_date">'); 
			the_time('M j, Y');
		printf('</small>');		
	}

	// Get the excerpt
	function anyloop_excerpt() {
		printf('<aside class="anyloop_excerpt">'); 
			the_excerpt();
		printf('</aside>');
	}

	// Get the post thumbnail
	function anyloop_thumb() {
		the_post_thumbnail('thumbnail');
	}

	// Get the content
	function anyloop_content() {
		printf('<div class="anyloop_content">');
			the_content();
			global $withcomments;
			$withcomments = 1;
		printf('</div>');
	}

	// Get Comments Link
	function anyloop_comments() {
	   	if ( comments_open() ) :
			printf('<div class="anyloop-meta">');
			  echo do_shortcode('[post_comments]');
			printf('</div>');
		endif;
	}

	// Get Full Comments
	function anyloop_fullcomments($clone_id = null) {
		
	   	if ( comments_open() ) {

			printf('<div class="anyloop-meta row">');
			  	
			  	echo '<a href="#" class="anyloop_comment_trigger any-clone'.$clone_id.'"><i class="icon-comment"></i>';
			  		comments_number();
			  	echo '</a>';

			  	echo do_shortcode('<i class="icon-list"></i>[post_categories]');

			  	echo do_shortcode('[post_tags]');

				printf('<div class="anyloop_comment_drawer_wrap any-clone'.$clone_id.'">');
 	
  					comments_template();

				printf('</div>');

			printf('</div>');

		} else {
			printf('<div class="anyloop-meta row"><div class="span6 zmb">');
			    echo do_shortcode('<i class="icon-list"></i>[post_categories]');
			printf('</div><div class="span6 zmb">');
				echo do_shortcode('[post_tags]');
			printf('</div></div>');
		}	
	}

	// Share
	function anyloop_share($clone_id = null) { ?> 
		<div class="anyloop_share <?php echo 'any-clone'.$clone_id;?>">
			<a href="#" class="anyloop_share <?php echo 'any-clone'.$clone_id;?>"><i class="icon-share"></i></a>
		</div> 
		<div class="anyloop_sharepop <?php echo 'any-clone'.$clone_id;?>">
			<small>Share &rarr;</small>
			<a href="http://twitter.com/share?text=<?php the_permalink(); ?>&url=<?php the_permalink();?>" target="_blank"><i class="icon-twitter"></i></a>
			<a href="http://www.facebook.com/sharer.php?u=<?php the_permalink();?>&t=<?php the_title(); ?>" target="blank"><i class="icon-facebook"></i></a>
			<a href="mailto:info@email.com?subject=<?php json_encode(the_permalink());?>"><i class="icon-envelope-alt"></i></a>
		</div>
	<?php }

	// Options
	function section_optionator( $settings ){
		
		$settings = wp_parse_args($settings, $this->optionator_default);
		
		$option_array = array(
			'anyloop_layout'    => array(
				'type'		    => 'graphic_selector',
            	'showname'	    => true,
				'sprite'		=> PL_EXTEND_URL.'/anyloop/layout-sprite.png',
				'height'		=> '88px', 
				'width'			=> '130px',
				'layout' 		=> 'interface',	
				'selectvalues'	=> array(
					'anyloop_recent_posts'	=> array('name' => __( "Compact", 'pagelines' ), 'offset' => '-4px -13px'),
					'anyloop_full_posts'	=> array('name' => __( "Full", 'pagelines' ), 'offset' => '-4px -107px'),
					'anyloop_grid_posts'	=> array('name' => __( "Grid", 'pagelines' ), 'offset' => '-4px -205px'),
				),
               'title'          => __( 'Post Layout', 'pagelines' ),                        
               'shortexp'       => __( 'Select a layout for the posts', 'pagelines' ),
               'exp'            => '<strong>Compact</strong>: This mode will show posts in a compact format, typically used in a sidebar. Set a featured image on the post to show a thumbnail.<br />
               						<strong>Full Posts</strong>: Shows the entire post, no excerpt. Also shows post categories, tags, and comments. Comments open on the same page.<br />
               						<strong>Grid Posts</strong>: Shows posts in a grid which animate when the screen is resized. Adjust the width of the posts in Grid Mode under "Grid Options" below.'	
			),
			'anyloop_show_posts'   => array(
				'type' 			=> 'multi_option',
				'title'      => 'Number of Posts',
				'shortexp'   => 'How many posts to show? (use -1 to show all)',
				'selectvalues' => array(
					'anyloop_post_count' => array(
						'type' 			=> 'text_small',
					),
				),
				'exp'   => 'This option will limit the number of posts on a page to the number you specify.<br /><br />
							<strong>Hint:</strong> Replace the "Post Loop" section, with <em>AnyLoop</em>, to use <em>AnyLoop</em> as your sole loop provider. Then, as long as you have your posts page set to a page with this section, Wordpress will provide pagination for the posts, and the number of posts you set above, will be the number of posts shown on each page. Also works with Page Navi plugin.',
			),
			'anyloop_grid_article_width' => array(
				'type'     => 'text_small',
				'title'   => 'Post Width (Grid Mode Only)',
				'shortexp'  => 'Enter a width for the article (default is 200)',
				'exp'        => 'Adjust the width of the posts in grid mode. This will only work when the section is shown in Grid Mode.',
			),			
			'anyloop_hidestuff' => array(
				'type' 			=> 'multi_option',
				'title'      => 'Hide Things',
				'shortexp'   => 'Options for hiding things',
				'selectvalues' => array(
					'anyloop_hide_title' => array(
						'type' => 'check',
						'inputlabel' => 'Hide the Title',
					),
					'anyloop_hide_date' => array(
						'type' => 'check',
						'inputlabel' => 'Hide the Date',
					),
					'anyloop_hide_thumb' => array(
						'type' => 'check',
						'inputlabel' => 'Hide the Thumb',
					),
					'anyloop_hide_content' => array(
						'type' => 'check',
						'inputlabel' => 'Hide the Content',
					),
					'anyloop_hide_excerpt' => array(
						'type' => 'check',
						'inputlabel' => 'Hide the Excerpt',
					),
					'anyloop_hide_comments' => array(
						'type' => 'check',
						'inputlabel' => 'Hide the Comment Link',
					),
				),
				'exp' => 'These options can be used to tailor the loop to your specific needs. They work in all available layout modes.',
			),
			'anyloop_single_options'  => array(
				'type' 			=> 'multi_option',
				'title'      => 'Single Page Options',
				'shortexp'   => 'Enter a page ID',
				'selectvalues' => array(
					'anyloop_page_id' => array(
						'type' => 'text_small',					),
				),
				'exp' => 'This is handy for showing the contents of a specific page, on another page, simply by inputting the ID of the page you want embedded. Kind of like, embedding a page into a page. Make sure your number of posts is set to 1, if you are going to use this option.',
			),
			'anyloop_category_options' => array(
				'type' 			=> 'multi_option',
				'title'      => 'Category Options',
				'shortexp'   => 'Post by Category Options',
				'selectvalues' => array(
					'anyloop_incl_cats' => array(
						'type' => 'text',
						'inputlabel' => 'Include these category(s)',
					),
					'anyloop_excl_cats' => array(
						'type' => 'text',
						'inputlabel' => 'Exclude these category(s)',
					),
				),
				'exp' => 'You can include or exclude any category, or categories by name. Enter multiple separated by commas. For example, to include posts from the categories Alpha and Bravo, list them as <code>alpha,bravo</code>.',
			),
			'anyloop_tax_options' => array(
				'type' 			=> 'multi_option',
				'title'      => 'Tag Options',
				'shortexp'   => 'Post by Tag Options',
				'selectvalues' => array(
					'anyloop_incl_taxos' => array(
						'type' => 'text',
						'inputlabel' => 'Include these tag(s)',
					),
					'anyloop_excl_taxos' => array(
						'type' => 'text',
						'inputlabel' => 'Exclude these tag(s)',
					),
				),
				'exp' => 'You can include or exclude any tag, or tags by ID. Enter multiple tag ID\'s by comma. Go to Posts->Tags, and click on the tag to get the ID of the tag (will be in the browsers address bar.',
			),
			'anyloop_posttype_options' => array(
				'type' 			=> 'multi_option',
				'title'      => 'Post Type Options',
				'shortexp'   => 'Post by Post Type Options',
				'selectvalues' => array(
					'anyloop_incl_types' => array(
						'type' => 'text',
						'inputlabel' => 'Post Types(s)',
					),
				),
				'exp' => 'You can include or exclude any custom post type, or post types by name. Enter multiple separated by commas. For example, to include posts from the custom post types Alpha and Bravo, list them as <code>alpha,bravo</code>. Does not currently support PageLines post types (like boxes), due to the way PageLines currently handles post type links.',
			),
			'anyloop_author_options' => array(
				'type' 			=> 'multi_option',
				'title'      => 'Authors Options',
				'shortexp'   => 'Post by Authors Options',
				'selectvalues' => array(
					'anyloop_incl_authors' => array(
						'type' => 'text',
						'inputlabel' => 'Author(s)',
					),
				),
				'exp' => 'You can include or exclude any author, or authors by name. Enter multiple separated by commas. For example, to include posts from the authors Sam and Elliot, list them as <code>sam,elliot</code>.',
			),
			'anyloop_order_options' => array(
				'type'		=> 'multi_option',
				'title'		=> 'Post Ordering',
				'shortexp'	=> 'Post order options',
				'selectvalues'	=> array(
					'anyloop_orderby' => array(
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
					'anyloop_order' => array(
						'type'		=> 'select',
						'selectvalues'	=> array(
							'DESC' 		=> array('name' => __( 'Descending', 'pagelines' ) ),
							'ASC' 		=> array('name' => __( 'Ascending', 'pagelines' ) ),
						),
						'inputlabel'	=> __( 'Select sort order.', 'pagelines' ),
					),
				),
			),
			'anyloop_more_info'    => array(
				'type' 			=> '',
				'title'      => '<strong style="display:block;font-size:16px;color:#eaeaea;text-shadow:0 1px 0 black;padding:7px 7px 5px;background:#333;margin-top:5px;border-radius:3px;border:1px solid white;letter-spacing:0.1em;box-shadow:inset 0 0 3px black;">TIPS ON USE:</strong>',
				'shortexp'   => '<strong>&bull; Using Multiple:</strong> PageLines does not allow clones to be passed from sidebar to content under Drag and Drop. So, to run a 2nd clone in a different mode in a sidebar, first drag the section into the sidebar. Then, clone the section, and delete the original. As long as you have AnyLoop #1, and AnyLoop #2 under Page Options, then you are good to go.<br/><br /><strong>&bull; Using as Main Loop:</strong> To use as the main loop, switch out all instances of PostLoop with AnyLoop under Drag and Drop. You\'ll be able to adjust options under Page Options->Blog Page. If you\'re using AnyLoop as the main loop, you won\'t be able to use a clone propertly. You can however, use the AnyRecent Shortcode outlined below, and on the demo at <a href="http://anyloopdemo.com" target="_blank">http://anyloopdemo.com</a>.<br/><br /><strong>&bull; AnyRecent Shortcode:</strong> This shortcode can be used in any widget to provide a recent posts viewing mode. The shortcode and avaialable attributes are listed below.<pre>[anyloop-recent]</pre><ul><li>limit="7" will limit the posts to show only 7</li><li>category="" list multiple categories by comma</li><li>tag="" show posts by a tag</li><li>cpt="" show posts via custom post types</li><li>author="" show posts only by certain authors</li></ul>',
			),
		);

		$settings = array(
			'id' 		=> $this->id.'_meta',
			'name' 		=> $this->name,
			'icon' 		=> $this->icon, 
			'clone_id'	=> $settings['clone_id'], 
			'active'	=> $settings['active']
		);

		register_metatab($settings, $option_array);
	}	
}