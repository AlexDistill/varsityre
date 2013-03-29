<?php

// Setup
require_once( dirname(__FILE__) . '/setup.php' );

// Insert comments after excerpt if available
function swisscomments_after () {
	
	global $post;

	$count = get_comments_number();
	if ($count == 0) {
		return '';
	
	} else { 
		?>
			<span class="post-comments sc swiss"><?php comments_popup_link('0 comments', '1 reply','% replies', 'comments-link', ''); ?></span>
		<?php 
	}

}
add_action ('pagelines_loop_after_excerpt','swisscomments_after');

// Autoset some colors
// Set Default Body Background Color //
pl_default_setting( 
 	array( 
	    'key' => 'bodybg', 
	    'value' => '#FFFFFF'
    ) 
);



// Set Default Primary Text Color //
pl_default_setting(
	array(
		'key' => 'text_primary',
		'value' => '#3D3D3D'
	)
);


// Set Default Headers Color //
pl_default_setting(
	array(
		'key' => 'headercolor',
		'value' => '#6E0000'
	)
);


// Set Default Links Color //
pl_default_setting(
	array(
		'key' => 'linkcolor',
		'value' => '#6E0000'
	)
);

// Set blog layout as default //
pl_default_setting(
	array(
		'key' => 'blog_layout_mode',
		'value' => 'blog'
	)
);


// Set custom meta for LLT //
pl_default_setting(
	array(
		'key' => 'metabar_clip',
		'value' => '[post_date] [post_edit]'
	)
);



// Set meta standard for LLT //
pl_default_setting(
	array(
		'key' => 'metabar_standard',
		'value' => '[post_date] [post_edit]'
	)
);