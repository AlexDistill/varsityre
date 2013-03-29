<?php
/*
	Section: Letters
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: A blog/content section for typographers, writers and communicators.
	Class Name: plLetters
	Demo: http://letters.pagelines.me/
	Workswith: templates
	Cloning: false
	Version: 1.0.4
*/

/*
 * Main section class
 *
 * @package PageLines Framework
 * @author PageLines
 */
class plLetters extends PageLinesSection {

    function section_template( $clone_id ) { 
		?>
		<div class="pl-letters-container row">
		
				<?php if(is_single()):?>
				<div class="letters-nav fix"> 
					<span class="inav previous">
						<?php previous_post_link('%link', '<i class="icon-circle-arrow-left"></i> %title') ?>
					</span> 
					<span class="inav next">
						<?php next_post_link('%link', '%title <i class="icon-circle-arrow-right"></i>') ?>
					</span>
				</div>
				<?php endif; ?>
				
				<?php 
					if( have_posts() )
						while ( have_posts() ) : 
							the_post();  
							$this->get_article(); 
						endwhile;
					else 
						$this->posts_404();
				?>

				<?php pagelines_pagination(); ?>
	
		</div>
<?php 
	}
	
	function recents(){
		
	}
	
	function get_article(){
		global $post;
		ob_start();
		
		$perm = get_permalink($post->ID); 
		$title = get_the_title();
		$thumb_frame = ploption('pl_letters_frame', array('post_id' => $post->ID));
		?>
		<article id="post-<?php echo $post->ID;?>" class="letters-article <?php get_post_class("", $post->ID);?>">
			
			<?php 
			
				if(has_post_thumbnail($post->ID)) {
					printf(
						'<div class="letters-thumb"  >
							<a class="%s" href="%s">
								<div class="%s">
									<div class="box">%s</div>
								</div>
							</a>
						</div>', 
						($thumb_frame) ? 'pl-frame' : '',
						$perm,
						($thumb_frame) ? 'pl-vignette' : '',
						get_the_post_thumbnail( null, 'large' )
					); 
				}
			?>
			
			<h1 class="letters-title">
				<?php 
				if(!is_single())
					printf('<a href="%s">%s</a>', $perm , $title);
				else
					echo $title; 
				?>
			</h1>
			<div class="letters-content hentry">
				<?php 
				
				if( is_single() || is_page() ): 
					$this->page_meta();
			
				else: 
						
						echo '<p class="letters-excerpt">';
						echo get_the_excerpt().'&nbsp;'; 
						echo pledit($post->ID);
						
						printf(
							' <a class="continue-link btn btn-primary btn-mini" href="%s">%s</a>', 
							$perm, 
							__('Full Article &raquo;', 'pagelines')
						);
						echo '</p>';
						
				endif; 
				?>
			</div>
			<hr class="post-break soften" />
		</article>
		<?php 
		echo apply_filters('pagelines_get_article_output', ob_get_clean(), $post, array());
	}
	
	function page_meta(){
		global $post;
		
		if(is_single()){

			$share = '';
			$args = array( 
				'permalink' => get_permalink( $post->ID ), 
				'title' => wp_strip_all_tags( get_the_title( $post->ID ) ) 
			);
			$meta = sprintf('%s %s &mdash; %s', __('Posted On ', 'pagelines'), '[post_date]', '[post_comments]'); 
			
			if ( class_exists( 'PageLinesShareBar' ) ) {				
				$share = sprintf( ' <span class="lk">%s %s</span>',
				PageLinesShareBar::facebook( $args ),
				PageLinesShareBar::twitter( $args )						
			);				
			}
				printf(
					'<div class="letters-meta"><span>%s</span>%s</div>', 
					do_shortcode($meta), 
					$share
				);
		}

		echo apply_filters('the_content', get_the_content() . pledit($post->ID)); 
		if( is_single() || is_page() ){
			
			$pgn = array( 
				'before' 			=> __( "<div class='pagination'><span class='desc'>pages:</span><ul>", 'pagelines' ), 
				'after' 			=> '</ul></div>', 
				'link_before'		=> '<span class="pg">', 
				'link_after'		=> '</span>'
			);
			
			wp_link_pages( $pgn );
		}
	
		if ( is_single() && get_the_tags() )
			printf( 
				'<div class="p tags">%s&nbsp;</div>', 
				get_the_tag_list( 
					__( '<span class="note">Tagged with &rarr;</span> ', 'pagelines' ), 
					' &bull; ', 
					''
				)	 
			);
			
		if( is_single() )
			comments_template();	
	}

	function posts_404(){
		printf( 
		'<section class="boomboard">
			<div class="center fix">%s</div>
		</section>', 
		__('Nothing Found', 'pagelines'), 
		pagelines_search_form( false )
		);
	}

	function section_optionator( $settings ){
		
		$settings = wp_parse_args($settings, $this->optionator_default);
	
		$option_array = array(
			'pl_letters_frame' => array(
				'default'	=> false,
				'type'		=> 'check',
				'inputlabel'=> __( 'Add Image Frame?', 'pagelines' ),
				'title'		=> __( 'Post Thumb Frame', 'pagelines' ),
				'shortexp'	=> __( 'Adds a thumb and hover over effect to Letters thumbs.', 'pagelines' ),
				'exp'		=> ""
			)
		);
	
		$metatab_settings = array(
			'id' 		=> 'letters',
			'name' 		=> $this->name,
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