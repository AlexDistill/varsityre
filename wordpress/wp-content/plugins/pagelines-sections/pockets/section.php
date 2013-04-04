<?php
/*
    Section: Pockets
    Author: Nick Haskins
    Author URI:
    Demo: http://pockets.nichola.us
    Description: Pockets are extremely versatile post types with multiple content displays
    Class Name: baPockets
    Cloning: false
    Version: 1.0.2
    Workswith: main
*/

class baPockets extends PageLinesSection{

    var $ptID = 'pockets';
    var $taxID = 'pocket-sets';
    var $default_limit = 3;
    const version = '1.0';

    function section_persistent(){

        $this->post_type_setup();
        $this->post_meta_setup();

        // Add the post categories as classes
        add_filter( 'post_class', array(&$this,'pocket_taxo_post_class'));
        add_filter( 'postsmeta_settings_array', array( &$this, 'pockets_meta' ), 10, 1 );

        // Add Pocket Shortcodes
        add_shortcode('pocket_list',array(&$this,'pocket_list_shortcode'));
        add_shortcode('pocket_carousel',array(&$this,'pocket_carousel_shortcode'));
    }


    // Pocket List View Shortcode
    function pocket_list_shortcode($atts,$content = null) {

        global $post;

        // available atts include horizontal, vertical, and grid
        extract( shortcode_atts( array(
            'number'     => 5,
            'display'    => 'horizontal',
            'category' => ''
        ), $atts ) );

        if($category == '') {

            $args = array(
                'posts_per_page' => $number, 
                'post_type'  => $this->ptID,
            );

        } else {

            $args = array(
                'posts_per_page' => $number, 
                'post_type'  => $this->ptID,
                'tax_query' => array(array(
                    'taxonomy' => 'pocket-sets',
                    'field' => 'slug',
                    'terms' => array($category),
                ))
            );
        }

        if($display == 'grid') {

            wp_enqueue_script( 'pocket-list-equalizer',  $this->base_url.'/js/jquery.pocket-equalize.js', array('jquery'), self::version);
        }

        $q = new WP_Query($args);

        $output = '<div class="pocket-list-shortcode '.$display.' pocket-equalize"><ul class="unstyled">';

            while ($q->have_posts()) : $q->the_post();

                $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
                $theimage = $image[0];

                $output .= '<a class="pocket-list-item" href="'.get_permalink() .'" title="'.get_the_title().'" style="background-image: url('.$theimage.');background-size:cover;background-position:50% 50%;" ><li><h4><span>'.get_the_title().'</span></h4><i class="icon-external-link"></i></li></a>';

            endwhile;

            wp_reset_query();

        return $output .'</ul></div><div class="clear"></div>';
    }

    // Pocket Carousel View Shortcode
    function pocket_carousel_shortcode($atts,$content = null) {

        global $post;

        extract( shortcode_atts( array(
            'number'     => 5,
        ), $atts ) );

        $args = array('posts_per_page' => $number, 'post_type'  => $this->ptID);

        $pcs = new WP_Query($args);

        wp_enqueue_script( 'pocket-cycle',  $this->base_url.'/js/jquery.cycle2.js', array('jquery'), self::version);
        wp_enqueue_script( 'pocket-cycle-carousel',  $this->base_url.'/js/jquery.cycle-carousel.min.js', array('jquery'), self::version);

        $output = '<div class="cycle-slideshow pocket-carousel-shortcode"
                            data-cycle-fx=carousel
                            data-cycle-timeout="0"
                            data-cycle-speed="%s"
                            data-cycle-next="#next"
                            data-cycle-prev="#prev"
                            data-allow-wrap=false
                            data-cycle-pause-on-hover="true"
                            data-cycle-slides="> a"
                            >';

            while ($pcs->have_posts()) : $pcs->the_post();

                $imagesrc = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' );
                $imageurl = $imagesrc[0];
                $thetitle = get_the_title();
                $title = (strlen($thetitle) > 16) ? substr($thetitle,0,16).'...' : $thetitle;
                $alt = get_post_meta($post->ID, '_wp_attachment_image_alt', true);

                $output .= '<a class="pocket-carousel-item" href="'.get_permalink().'"><span class="pocket-carousel-item-caption">'.$title.'</span><img src="'.$imageurl.'" alt="'.$alt.'"></a>';

            endwhile;

            wp_reset_query();

        return $output .'</div><div class="pocket-carousel-pager center"><a href=# id=prev><i class="icon-angle-left"></i></a><a href=# id=next><i class="icon-angle-right"></i></a></div>';
    }

    function section_scripts() {

        global $post; global $pagelines_ID;
        $oset = array('post_id'=> $pagelines_ID);
        $pocketlayout = (get_post_meta($post->ID,'single_pocket_layout',$oset));

        // Load Single View Scripts
        if( $this->view == 'single' ) {

            wp_enqueue_script( 'pocket-cycle',  $this->base_url.'/js/jquery.cycle2.js', array('jquery'), self::version);
            wp_enqueue_script( 'pocket-molten',  $this->base_url.'/js/moltenleading.src.js', self::version);
            wp_enqueue_script( 'pocket-single',  $this->base_url.'/js/jquery.pockets-single.min.js', self::version);

        } elseif( $this->view == 'archive' ) {

            wp_enqueue_script( 'pocket-isotope',  $this->base_url.'/js/jquery.isotope.min.js',array('jquery'), self::version);
            wp_enqueue_script( 'pocket-archive',  $this->base_url.'/js/jquery.pockets-archive.min.js', self::version);

        }
    }

    function section_head($clone_id) {

        global $post; global $pagelines_ID;
        $oset = array('post_id'=> $pagelines_ID);
        $pocketlayout = (get_post_meta($post->ID,'single_pocket_layout',$oset));

        ?>
            <!-- Pockets -->
            <script>
                jQuery(document).ready(function() {

                    <?php if( $this->view == 'single') { ?>

                        // Add Body class with conditions
                        jQuery('body').addClass('<?php echo $pocketlayout;?>');

                    <?php } ?>
                });
            </script>
    <?php }

    function post_type_setup(){

        global $post; global $pagelines_ID;
        $oset = array('post_id' => $pagelines_ID);

        $plural = ploption('pocket_slug_plural',$oset) ? ploption('pocket_slug_plural',$oset) : 'pockets';
        $singular = ploption('pocket_slug_singular',$oset) ? ploption('pocket_slug_singular',$oset) : 'pocket';

        $args = array(
            'label'             => ucwords(strtolower($plural)).__('', 'pagelines'), // HAS TO MATCH FOLDER SLUG OR DIE!!!!!
            'singular_label'    => ucwords(strtolower($plural)),
            'description'       => 'For creating Pockets of info',
            'menu_icon'         => $this->icon,
            'public'            => true,
            'rewrite'           => array('slug' => $singular),
            'query_var'         => true,
            'supports'          => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
            'can_export'        => true,
            'has_archive'       => $plural,

        );
        $taxonomies = array(
            $this->taxID => array(
                "label" => ucwords(strtolower($singular)).' Categories',
                "singular_label" => ucwords(strtolower($singular)).__(' Category', 'pagelines'),
            )
        );

        $columns = array(
            "cb"                 => "<input type=\"checkbox\" />",
            "title"              => "Title",
            "description"        => "Text",
            "pocket-categories"  => "Categories",
        );

        $this->post_type = new PageLinesPostType( $this->ptID, $args, $taxonomies,$columns,array(&$this, 'column_display'));

        flush_rewrite_rules();
    }

    // Single Options
    function post_meta_setup(){

        global $post; global $pagelines_ID;
        $oset = array('post_id' => $pagelines_ID);

        $plural = ploption('pocket_slug_plural',$oset) ? ploption('pocket_slug_plural',$oset) : 'pockets';
        $singular = ploption('pocket_slug_singular',$oset) ? ploption('pocket_slug_singular',$oset) : 'pocket';

        $type_meta_array = array(

            'single_pocket_layout' => array(
                    'default'   => '',
                    'type'      => 'graphic_selector',
                    'showname'  => true,
                    'sprite'        => PL_EXTEND_URL.'/pockets/pocket-layout-sprite.png',
                    'height'        => '88px',
                    'width'         => '130px',
                    'layout'        => 'interface',
                    'selectvalues'  => array(
                        'pocket-layout-wide'  => array('name' => 'Wide', 'offset' => '0px -11px'),
                        'pocket-layout-general'  => array('name' => 'General', 'offset' => '0px -108px'),
                    ),
                   'title'        => ucwords(strtolower($singular)).' Layout',
                   'shortexp'    => __( 'Select a layout for this '.ucwords(strtolower($singular)), 'pagelines' ),
                   'exp'           => 'Here, you can choose a layout for your single post.'
            ),
            'single_pocket_gallery_options' => array(
                'type' => 'multi_option',
                'title' => 'Gallery Options',
                'shortexp' => 'Options for this '.ucwords(strtolower($plural)).' gallery',
                'selectvalues' => array(
                    'single_pocket_gallery_title' => array(
                        'type' => 'text',
                        'inputlabel' => 'Gallery Title'
                    ),
                    'single_pocket_gallery_transition_speed' => array(
                        'type' => 'text_small',
                        'inputlabel' => 'Transition Speed'
                    ),
                    'single_pocket_gallery_transition_delay' => array(
                        'type' => 'text_small',
                        'inputlabel' => 'Transition Delay'
                    ),
                    'single_pocket_gallery_float' => array(
                        'type' => 'select',
                        'inputlabel' => 'Gallery Position',
                        'selectvalues' => array(
                            'pocket-gallery-left' => array('name' => 'Left'),
                            'pocket-gallery-right' => array('name' => 'Right'),
                        ),
                    ),
                    'single_pocket_gallery_transition_type' => array(
                        'type' => 'select',
                        'inputlabel' => 'Transition Type',
                        'selectvalues' => array(
                            'fadeOut'     => array('name' => 'Fade'),
                            'fade'        => array('name' => 'Cross Fade'),
                            'scrollHorz'  => array('name' => 'Scroll Horizontal'),
                        ),
                    ),
                ),
                'exp' => 'These settings only apply when using a Gallery in the post. The title shows as a heading above the gallery, and can be changed to whatever you like. The remaining options, are optional gallery configurations.',
            ),
            'single_pocket_sbsc' => array(
                'type' => 'textarea',
                'title' => 'Additional Content',
                'shortexp' => 'Extra stuff to display',
                'exp'       => 'This will show up, under the "Shares" control, but at the end of your post content. You can use anything here from text, to shortcodes. For example, try putting in <code>[like_button]</code> and save settings to see the results. Enter button shortcodes that have URL options, without the quotes on the URL.'
            ),
            'single_pocket_label_options' => array(
                'type' => 'multi_option',
                'title' => 'Label',
                'shortexp' => 'Setup options for labels',
                'selectvalues' => array(
                    'single_pocket_label_name1' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Name 1'
                    ),
                    'single_pocket_label_att1' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Attribute 1',
                    ),
                    'single_pocket_label_name2' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Name 2'
                    ),
                    'single_pocket_label_att2' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Attribute 2',
                    ),
                    'single_pocket_label_name3' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Name 3'
                    ),
                    'single_pocket_label_att3' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Attribute 3',
                    ),
                    'single_pocket_label_name4' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Name 4'
                    ),
                    'single_pocket_label_att4' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Attribute 4',
                    ),
                     'single_pocket_label_name5' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Name 5'
                    ),
                    'single_pocket_label_att5' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Attribute 5',
                    ),
                    'single_pocket_label_name6' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Name 6'
                    ),
                    'single_pocket_label_att6' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Attribute 6',
                    ),
                    'single_pocket_label_name7' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Name 7'
                    ),
                    'single_pocket_label_att7' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Attribute 7',
                    ),
                    'single_pocket_label_name8' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Name 8'
                    ),
                    'single_pocket_label_att8' => array(
                        'type' => 'text',
                        'inputlabel' => 'Label Attribute 8',
                    ),
                ),
                'exp' => 'You can enter up to 8 label/attribute pairs. If you don\'t enter an option, no label/attribute will show.'
            ),
            'single_pocket_more_info'    => array(
                'type'          => '',
                'title'      => '<strong style="display:block;font-size:16px;color:#eaeaea;text-shadow:0 1px 0 black;padding:7px 7px 5px;background:#333;margin-top:5px;border-radius:3px;border:1px solid white;letter-spacing:0.1em;box-shadow:inset 0 0 3px black;">TIPS ON USE:</strong>',
                'shortexp'   => 'Create new post above. Fill in some text in the main edit window, give it a title, and apply a category. Upload a Featured Image, and give a few labels and attributes. Choose a layout, and publish. This is your single post.<br/><br />
                                <strong style="display:block;">Single Layout</strong> Each single view can have a different layout. Simply toggle the layout option while in the single view edit view. <br/><br />
                                <strong style="display:block;">Archive Layout</strong> This page is automagically created when you create your Slug. So if your slug was \'dogs\', then your archives would automagically be at <code>yoursite.com/dogs</code> <br/><br />
                                <strong style="display:block;">Excerpt</strong> Single Pockets have a lead text, that sits right under the title. This text, is the excerpt that can be filled in under the post-edit screen.<br/><br />
                                <strong style="display:block;">Labels</strong> Labels are used to describe bits and pieces of your post. On the demo, these labels are located on the left side of the Wide template, and in the collapsable drawer of the General template. These labels, and their attributes, can be whatever you like.<br/><br />
                                <strong style="display:block;">Featured Image</strong> The single view layouts both utilize the Featured Image option found on the right side of the single view edit screen. For the \'Wide\' view style view, make sure this image is square. <br/><br />
                                <strong style="display:block;">Image Gallery</strong> Create a new gallery, attached to this post. If using Wordpress 3.4, upload a gallery, and click "save changes." Do not insert the gallery. If using Wordpress 3.5: <br /><ul><ol><li>Click "Add Media" button.</li><li>Click "Create Gallery" on left.</li><li>Using the dropdown under Media Library, select "Uploaded to this post".</li><li>At this point you can click on each image and assign it a Title, Caption, and Alt tag, as the gallery utilizes these fields.</li><li>Click the "Create a new gallery" button on the bottom right.</li><li>DO NOT insert the gallery. Instead, simply click the "x" to close out the box.</li></ol></ul>
                                <strong style="margin-top:18px;display:block;">Post Categories</strong> The archive view, is run with Isotope filtering. This uses the categories for each Post. Try creating a new category or two, and assign each single pocket to a category. Then, visit your archive view to see how this plays. <br/><br />
                                <strong style="display:block;">Shortcodes</strong> Pockets comes equipped with 2 shortcodes that you can use to display an archive type view of your Pockets. These shortcodes can be used anywhere, even if you don\'t have the section active, on the page that you have placed the shortcodes are on. These are optional, as your archive page will live under the plural slug you created. This is just another way of showing your Pockets across your site.
                                <ul style="padding-left:10px;">
                                    <li><span style="text-decoration:underline;font-style:italic;">List Shortcode:</span> Output a styled, list-type multi-view of your Pockets archive. Available attributes include \'number\' and \'display\'. The "display" attribute accepts <strong>horizontal</strong>, <strong>vertical</strong>, and <strong>grid</strong>. The "number" attribute accepts a numeric value. This is the number of posts you want to show. <code>[pocket_list number="5" display="horizontal"]</code></li>
                                    <li style="margin-top:10px;"><span style="text-decoration:underline;font-style:italic;">Carousel Shortcode:</span> Output a carousel type view of your post archives. Available attributes include \'number\'. The "number" attribute accepts a numeric value. This is the number of posts you want to show. <code>[pocket_carousel number="5"]</code></li>
                                </ul>

                ',
            ),
        );

        $post_types = array($this->id);

        $type_metapanel_settings = array(
            'id'        => 'pocket-metapanel',
            'name'      => ucwords(strtolower($plural)),
            'posttype'  => $post_types,
        );

        global $p_meta_panel;

        $p_meta_panel =  new PageLinesMetaPanel( $type_metapanel_settings );

        $type_metatab_settings = array(
            'id'        => 'pocket-type-metatab',
            'name'      => 'Single ' .ucwords(strtolower($singular)). ' Options',
            'icon'      => $this->icon
        );

        $p_meta_panel->register_tab( $type_metatab_settings, $type_meta_array );

        flush_rewrite_rules();
    }

    function pockets_meta( $d ) {

        global $metapanel_options;
        global $pagelines_ID;
        $oset = array('post_id' => $pagelines_ID);

        $singular = ploption('pocket_slug_singular',$oset) ? ploption('pocket_slug_singular',$oset) : 'pocket';
        $plural = ploption('pocket_slug_plural',$oset) ? ploption('pocket_slug_plural',$oset) : 'pockets';

        $meta = array(
            strtolower($plural).'_archive' => array(
                'metapanel' => $metapanel_options->posts_metapanel( 'pockets_archive', strtolower($plural).'_archive' ),
                'icon'      => $this->base_url.'/icon.png'
            )
        );
        $d = array_merge($d, $meta);

        return $d;
    }

    // Add taxos to post_class for isotope filtering
    function pocket_taxo_post_class( $classes) {

        $custom_terms = get_the_terms(0, $this->taxID);

        if ($custom_terms) {
          foreach ($custom_terms as $custom_term) {
            $classes[] = $custom_term->slug;
          }
        }

        return $classes;

    }

    // Global Options
    function section_optionator( $settings ){


        global $post; global $pagelines_ID;
        $oset = array('post_id' => $pagelines_ID);

        $plural = ploption('pocket_slug_plural',$oset) ? ploption('pocket_slug_plural',$oset) : 'pockets';
        $singular = ploption('pocket_slug_singular',$oset) ? ploption('pocket_slug_singular',$oset) : 'pocket';

        $settings = wp_parse_args($settings, $this->optionator_default);

        if( strstr($_SERVER['REQUEST_URI'], 'wp-admin/admin.php?page=pagelines_special') ) {
            $metatab_array = array(
                'pocket_slug_setup' => array(
                    'type' => 'multi_option',
                    'title' => 'Naming Setup',
                    'shortexp' => 'Setup slugs for your Pocket',
                    'selectvalues' => array(
                        'pocket_slug_singular' => array(
                            'type' => 'text',
                            'inputlabel' => 'Singular Slug (ex: dog)'
                        ),
                        'pocket_slug_plural' => array(
                            'type' => 'text',
                            'inputlabel' => 'Plural Slug (ex: dogs)'
                        ),
                    ),
                    'exp' => 'Provide your own slugs. By default, the Singular Slug is <code>pocket</code> and the Plural Slug is <code>pockets</code>. You can change them to whatever you please and the section will automaticlaly rewrite the URL\'s to accomodate.  Enter in all lower case letters.'
                ),
                'pocket_more_info_master'    => array(
                    'type'          => '',
                    'title'      => '<strong style="display:block;font-size:16px;color:#eaeaea;text-shadow:0 1px 0 black;padding:7px 7px 5px;background:#333;margin-top:5px;border-radius:3px;border:1px solid white;letter-spacing:0.1em;box-shadow:inset 0 0 3px black;">TIPS ON USE:</strong>',
                    'shortexp'   => '<strong style="display:block;">Section Setup</strong> There are a few setup items to cover in order to get the most out of this section. 
                                        <ul style="padding-left:10px;">
                                            <li>1. Set your slugs above.</li>
                                            <li>2. Head to Drag & Drop, and locate Pockets and Pockets Archive. Switch out PostLoop, for Pockets in both instances.</li>
                                            <li>3. Create a pocket with the menu on the left, and choose a layout for the pocket. Continue following instructions on single Pocket Options panel on single Pocket page.</li>
                                            <li>4. Note: Pockets best served with Full Width mode (i.e., no sidebars). In other words, there is currently no sidebar support. The single layouts are just not built to have a sidebar, and the idea was to keep things as clean as possible. Feel free to utilize the Additional Content option to add shortcodes and more to the sidebar that is built into the section. Sidebars have not been disabled, in the event the user wants to make sidebars work on their own.</li>
                                        </ul>
                                    <strong style="display:block;margin-top:10px;">Single Layout</strong> Each single view can have a different layout. Simply toggle the layout option while in the single view edit view. <br/><br />
                                    <strong style="display:block;">Archive Layout</strong> This page is automagically created when you create your Slug. So if your slug was \'dogs\', then your archives would automagically be at <code>yoursite.com/dogs</code> <br/><br />
                                    <strong style="display:block;">Excerpt</strong> Single Pockets have a lead text, that sits right under the title. This text, is the excerpt that can be filled in under the post-edit screen.<br/><br />
                                    <strong style="display:block;">Labels</strong> Labels are used to describe bits and pieces of your post. On the demo, these labels are located on the left side of the Wide template, and in the collapsable drawer of the General template. These labels, and their attributes, can be whatever you like.<br/><br />
                                    <strong style="display:block;">Featured Image</strong> The single view layouts both utilize the Featured Image option found on the right side of the single view edit screen. For the \'Wide\' view style view, make sure this image is square. <br/><br />
                                    <strong style="display:block;">Image Gallery</strong> Create a new gallery, attached to this post. If using Wordpress 3.4, upload a gallery, and click "save changes." Do not insert the gallery. If using Wordpress 3.5: <br /><ul><ol><li>Click "Add Media" button.</li><li>Click "Create Gallery" on left.</li><li>Using the dropdown under Media Library, select "Uploaded to this post".</li><li>At this point you can click on each image and assign it a Title, Caption, and Alt tag, as the gallery utilizes these fields.</li><li>Click the "Create a new gallery" button on the bottom right.</li><li>DO NOT insert the gallery. Instead, simply click the "x" to close out the box.</li></ol></ul>
                                    <strong style="margin-top:18px;display:block;">Post Categories</strong> The archive view, is run with Isotope filtering. This uses the categories for each Post. Try creating a new category or two, and assign each single pocket to a category. Then, visit your archive view to see how this plays. <br/><br />
                                    <strong style="display:block;">Shortcodes</strong> Pockets comes equipped with 2 shortcodes that you can use to display an archive type view of your Pockets. These shortcodes can be used anywhere, even if you don\'t have the section active, on the page that you have placed the shortcodes are on. These are optional, as your archive page will live under the plural slug you created. This is just another way of showing your Pockets across your site.
                                    <ul style="padding-left:10px;">
                                        <li><span style="text-decoration:underline;font-style:italic;">List Shortcode:</span> Output a styled, list-type multi-view of your Pockets archive. Available attributes include \'number\' and \'display\'. The "display" attribute accepts <strong>horizontal</strong>, <strong>vertical</strong>, and <strong>grid</strong>. The "number" attribute accepts a numeric value. This is the number of posts you want to show. <code>[pocket_list number="5" display="horizontal"]</code></li>
                                        <li style="margin-top:10px;"><span style="text-decoration:underline;font-style:italic;">Carousel Shortcode:</span> Output a carousel type view of your post archives. Available attributes include \'number\'. The "number" attribute accepts a numeric value. This is the number of posts you want to show. <code>[pocket_carousel number="5"]</code></li>
                                    </ul>
                '),

            );
        } else {
             $metatab_array = array(
                'pocket_more_info'    => array(
                    'type'          => '',
                    'title'      => '<strong style="display:block;font-size:16px;color:#eaeaea;text-shadow:0 1px 0 black;padding:7px 7px 5px;background:#333;margin-top:5px;border-radius:3px;border:1px solid white;letter-spacing:0.1em;box-shadow:inset 0 0 3px black;">HOW TO USE:</strong>',
                    'shortexp'   => '<strong style="display:block;">Single '.ucwords(strtolower($singular)).' Setup</strong> You can find the options for this single '.ucwords(strtolower($singular)).', below. Instructions are also available at the end of the panel below. Options for the Archive view can be found under Page Options-->Pockets.',
                ),
            );
        }

        $metatab_settings = array(
            'id'        => 'pocket_meta',
            'name'      => ucwords(strtolower($plural)),
            'icon'      => $this->icon,
            'clone_id'  => $settings['clone_id'],
            'active'    => $settings['active']
        );

        register_metatab($metatab_settings, $metatab_array);
    }

   function section_template( $clone_id = null ) {

        global $post; global $pagelines_ID;
        $oset = array('post_id' => $pagelines_ID, 'clone_id' => $clone_id);

        if ($this->view == 'single') {

            $this->draw_single_pocket();

        } else {

            $this->draw_pocket_archive();

        }

    }

    // Draw Pocket Archive
    function draw_pocket_archive() {

        global $post; global $pagelines_ID;
        $oset = array('post_id' => $pagelines_ID);
        $custom_terms = get_terms($this->taxID);

        $width = (ploption('pocket_grid_archive_width',$oset)) ? (ploption('pocket_grid_archive_width',$oset)) : '280px';
        $margin = (ploption('pocket_grid_archive_margin',$oset)) ? (ploption('pocket_grid_archive_margin',$oset)) : '5px';


        ?>
        <nav class="pocket-filter-nav-wrap">
           <ul class="unstyled pocket-filter-options" data-option-key="filter">

                <li><a href="#" data-filter="*"><i class="icon-th"></i></a></li>

                <?php foreach ($custom_terms as $custom_term) {
                    printf('<li><a href="#filter" data-filter=".%s">%s</a></li>',$custom_term->slug,$custom_term->name);
                } ?>

            </ul>
            <div class="clear"></div>
        </nav>

        <section class="ba-pocket-wrap">
        <?php

            while ( have_posts() ) : the_post();

                $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );


                ?>
                <article <?php post_class(); ?> id="post-<?php the_ID();?>" style="width:<?php echo $width;?>;margin:<?php echo $margin;?>;background-image: url('<?php echo $image[0]; ?>');background-size:cover;background-position:50% 50%;">
                   <a class="pocket-article-trigger" href="<?php the_permalink();?>">
                        <div class="pocket-inner-article">

                            <?php

                                printf('<h2>%s</h2>',get_the_title() );

                                ?><div class="pocket-grid-article-entry"><?php echo apply_filters('the_content',(get_the_excerpt()));?></div>


                        </div>
                    </a>
                </article>
                <?php

            endwhile;

        ?>
        </section>
    <?php }

    // Draw Single Pocket
    function draw_single_pocket(){

        global $post; global $pagelines_ID;
        $oset = array('post_id' => $pagelines_ID);

        $pocketlayout = (get_post_meta($post->ID,'single_pocket_layout',$oset));

        while ( have_posts() ) : the_post();

            if($pocketlayout == 'pocket-layout-wide') {
                $this->do_single_pocket_wide();
            } elseif($pocketlayout == 'pocket-layout-general') {
                $this->do_single_pocket_general();
            } else {
                echo 'Choose layout';
            }

        endwhile;

    }

    // Draw General
    function do_single_pocket_general() {

        global $post; global $pagelines_ID;
        $oset = array('post_id' => $pagelines_ID);

        $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
        $excerpt = get_the_excerpt();
        $sbsc = (get_post_meta($post->ID,'single_pocket_sbsc',$oset));


        ?>
            <div class="ba-single-pocket-wrap pocket-general">

                <div class="ba-pocket-wrap">

                    <div class="row ba-single-pocket-excerpt">
                        <div class="span10 zmb lead">
                            <h1><?php the_title(); ?></h1>
                            <?php echo apply_filters('the_content',(stripslashes(do_shortcode($excerpt)))); ?>
                        </div>
                        <div class="span2 zmb">
                            <?php $this->get_pocket_postnav() ;?>
                        </div>
                    </div>

                    <?php  if(get_post_meta($post->ID,'single_pocket_label_name1',$oset)) { ?>

                        <div class="ba-single-pocket-info collapse">
                            <div class="row ba-single-pocket-info-pad">
                                <div class="span3 morepad zmb">
                                    <?php the_post_thumbnail('thumbnail',array('class' => 'single-pocket-thumb')); ?>
                                </div>
                                <div class="span9 zmb">
                                    <div class="ba-single-pocket-info-list">
                                        <ul class="row pocket-labels unstyled fix">
                                            <?php $this->get_pocket_labels(); ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>


                    </div>
                    <a class="ba-single-pocket-info-trigger" data-toggle="collapse" data-target=".ba-single-pocket-info" href="#"><span>Details</span></a>

                    <div class="row ba-single-pocket-content-share">
                        <div class="span10 zmb ba-single-pocket-entry-content">
                            <?php apply_filters('the_content',the_content());

                            if($sbsc)
                                printf('<div class="row ba-single-pocket-sbsc"><div class="span12 zmb morepad">%s</div></div>',stripslashes(do_shortcode($sbsc)));
                            ?>
                        </div>
                        <div class="span2 zmb ba-single-pocket-shares">
                            <?php $this->get_pocket_shares(); ?>
                        </div>
                    </div>

                    <?php $this->fetch_pocketpost_gallery(); ?>

                </div>

            </div>
        <?php
    }

    // Draw Wide
    function do_single_pocket_wide() {

        global $post; global $pagelines_ID;
        $oset = array('post_id' => $pagelines_ID);

        $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
        $excerpt = get_the_excerpt();

        $sbsc = (get_post_meta($post->ID,'single_pocket_sbsc',$oset));

        ?>
            <div class="ba-single-pocket-wrap pocket-wide">

                <div class="ba-single-pocket-featimg-wrap" style="background-image: url('<?php echo $image[0]; ?>');background-size:cover;background-position:50% 50%;">
                    <div class="ba-single-pocket-featimg-pad">
                        <h1><?php the_title(); ?></h1>
                        <?php the_post_thumbnail('thumbnail',array('class' => 'single-pocket-thumb')); ?>
                    </div>
                </div>

                <div class="ba-pocket-wrap">

                    <div class="row ba-single-pocket-excerpt-share">
                        <div class="span10 ba-single-pocket-excerpt lead">
                             <?php echo apply_filters('the_content',(stripslashes(do_shortcode($excerpt)))); ?>
                        </div>
                        <div class="span2">
                            <?php $this->get_pocket_postnav();?>
                        </div>
                    </div>

                    <div class="row ba-single-pocket-entry-content-wrap">

                        <aside class="ba-single-pocket-entry-sidebar span2 zmb">
                            <ul class="unstyled pocket-labels fix">
                                <?php $this->get_pocket_labels(); ?>
                            </ul>
                        </aside>

                        <div class="ba-single-pocket-entry-content span8 zmb">
                            <?php 

                            apply_filters('the_content',the_content());

                            if($sbsc)
                                printf('<div class="row ba-single-pocket-sbsc"><div class="span12 zmb morepad">%s</div></div>',stripslashes(do_shortcode($sbsc)));
                            ?>
                        </div>

                        <div class="span2 zmb ba-single-pocket-shares">
                            <?php $this->get_pocket_shares();?>
                        </div>
                    </div>

                    <?php $this->fetch_pocketpost_gallery(); ?>

                </div>

            </div>
        <?php
    }

    function get_pocket_postnav() {
        global $pagelines_ID;
        $oset = array('post_id' => $pagelines_ID);

        $plural = ploption('pocket_slug_plural',$oset) ? ploption('pocket_slug_plural',$oset) : 'pockets';
        $singular = ploption('pocket_slug_singular',$oset) ? ploption('pocket_slug_singular',$oset) : 'pocket';

        $prevone = sprintf('<i title="Previous %s" class="icon-angle-left"></i><span>Previous %s</span>',ucwords(strtolower($singular)),ucwords(strtolower($singular)));
        $nextone = sprintf('<span>Next %s</span><i title="Next %s" class="icon-angle-right"></i>',ucwords(strtolower($singular)),ucwords(strtolower($singular)));

        ?><ul class="unstyled pocket-post-nav">
            <li class="previous-post"><?php previous_post_link('%link',$prevone) ?></li>
            <li class="next-post"><?php next_post_link('%link',$nextone) ?></li>
        </ul>
    <?php }

    function get_pocket_shares() {
        global $pagelines_ID;
        $oset = array('post_id' => $pagelines_ID);

        $plural = ploption('pocket_slug_plural',$oset) ? ploption('pocket_slug_plural',$oset) : 'pockets';
        $singular = ploption('pocket_slug_singular',$oset) ? ploption('pocket_slug_singular',$oset) : 'pocket';

        printf('<a href="#" rel="tooltip" title="Share %s" class="ba-single-pocket-share-trigger"><i class="icon-share"></i> %s</a>',ucwords(strtolower($singular)),$this->get_total_shares());
        ?>
        <div class="ba-single-pocket-share-drawer">
            <ul class="unstyled">
                <li><a class="twitter" href="http://twitter.com/share?text=<?php the_permalink(); ?>&url=<?php the_permalink();?>" target="_blank"><i class="icon-twitter"></i></a></li>
                <li><a class="facebook" href="http://www.facebook.com/sharer.php?u=<?php the_permalink();?>&t=<?php the_title(); ?>" target="blank"><i class="icon-facebook"></i></a></li>
                <li><a class="pinterest" href="#"><i class="icon-pinterest"></i></a></li>
            </ul>
        </div><?php
    }

    function get_pocket_labels() {
        global $post; global $pagelines_ID;
        $oset = array('post_id' => $pagelines_ID);

        $labeltitle1 = (get_post_meta($post->ID,'single_pocket_label_name1',$oset));
        $labelatt1 = (get_post_meta($post->ID,'single_pocket_label_att1',$oset));

        $labeltitle2 = (get_post_meta($post->ID,'single_pocket_label_name2',$oset));
        $labelatt2 = (get_post_meta($post->ID,'single_pocket_label_att2',$oset));

        $labeltitle3 = (get_post_meta($post->ID,'single_pocket_label_name3',$oset));
        $labelatt3 = (get_post_meta($post->ID,'single_pocket_label_att3',$oset));

        $labeltitle4 = (get_post_meta($post->ID,'single_pocket_label_name4',$oset));
        $labelatt4 = (get_post_meta($post->ID,'single_pocket_label_att4',$oset));

        $labeltitle5 = (get_post_meta($post->ID,'single_pocket_label_name5',$oset));
        $labelatt5 = (get_post_meta($post->ID,'single_pocket_label_att5',$oset));

        $labeltitle6 = (get_post_meta($post->ID,'single_pocket_label_name6',$oset));
        $labelatt6 = (get_post_meta($post->ID,'single_pocket_label_att6',$oset));

        $labeltitle7 = (get_post_meta($post->ID,'single_pocket_label_name7',$oset));
        $labelatt7 = (get_post_meta($post->ID,'single_pocket_label_att7',$oset));

        $labeltitle8 = (get_post_meta($post->ID,'single_pocket_label_name8',$oset));
        $labelatt8 = (get_post_meta($post->ID,'single_pocket_label_att8',$oset));

        if($labeltitle1)
           printf('<li class="span3"><span class="ba-single-pocket-label">%s</span><span class="ba-single-pocket-label-att">%s</span></li>',do_shortcode($labeltitle1),do_shortcode($labelatt1) );

        if($labeltitle2)
             printf('<li class="span3"><span class="ba-single-pocket-label">%s</span><span class="ba-single-pocket-label-att">%s</span></li>',do_shortcode($labeltitle2),do_shortcode($labelatt2) );

        if($labeltitle3)
            printf('<li class="span3"><span class="ba-single-pocket-label">%s</span><span class="ba-single-pocket-label-att">%s</span></li>',do_shortcode($labeltitle3),do_shortcode($labelatt3) );

        if($labeltitle4)
            printf('<li class="span3"><span class="ba-single-pocket-label">%s</span><span class="ba-single-pocket-label-att">%s</span></li>',do_shortcode($labeltitle4),do_shortcode($labelatt4) );

        if($labeltitle5)
            printf('<li class="span3"><span class="ba-single-pocket-label">%s</span><span class="ba-single-pocket-label-att">%s</span></li>',do_shortcode($labeltitle5),do_shortcode($labelatt5) );

        if($labeltitle6)
             printf('<li class="span3"><span class="ba-single-pocket-label">%s</span><span class="ba-single-pocket-label-att">%s</span></li>',do_shortcode($labeltitle6),do_shortcode($labelatt6) );

        if($labeltitle7)
            printf('<li class="span3"><span class="ba-single-pocket-label">%s</span><span class="ba-single-pocket-label-att">%s</span></li>',do_shortcode($labeltitle7),do_shortcode($labelatt7) );

        if($labeltitle8)
            printf('<li class="span3"><span class="ba-single-pocket-label">%s</span><span class="ba-single-pocket-label-att">%s</span></li>',do_shortcode($labeltitle8),do_shortcode($labelatt8) );

    }

    function fetch_pocketpost_gallery() {

        $hash = rand();
        global $post; global $pagelines_ID;

        $oset = array('post_id' => $pagelines_ID);

        $pocketgallfloat = (get_post_meta($post->ID,'single_pocket_gallery_float',$oset)) ? (get_post_meta($post->ID,'single_pocket_gallery_float',$oset)) : 'pocket-gallery-left';

        $trantype = (get_post_meta($post->ID,'single_pocket_gallery_transition_type',$oset)) ? (get_post_meta($post->ID,'single_pocket_gallery_transition_type',$oset)) : 'fadeOut';
        $transpeed = (get_post_meta($post->ID,'single_pocket_gallery_transition_speed',$oset)) ? (get_post_meta($post->ID,'single_pocket_gallery_transition_speed',$oset)) : 200;
        $trandelay = (get_post_meta($post->ID,'single_pocket_gallery_transition_delay',$oset)) ? (get_post_meta($post->ID,'single_pocket_gallery_transition_delay',$oset)) : 5000;

        $galltitle = (get_post_meta($post->ID,'single_pocket_gallery_title',$oset)) ? (get_post_meta($post->ID,'single_pocket_gallery_title',$oset)) : 'Images';


        $featured_id = get_post_thumbnail_id( $post->ID );

        $attachments = get_children( array(
            'post_parent'    => get_the_ID(),
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'order'          => 'ASC',
            'exclude'        => $featured_id
            )
        );

        if($attachments) {

            printf('<div class="ba-single-pocket-gallery-title page-header"><h2 class="pocket-down-arrow">%s</h2></div>',$galltitle);
            ?>
            <div class="ba-single-pocket-gallery-wrap <?php echo $pocketgallfloat;?>">

                <div class="row ba-pocket-gallery-wrap">
                <div class="span9 zmb">
                <?php
                    printf('<div class="cycle-slideshow ba-single-pocket-gallery"
                                data-cycle-caption = ".ba-single-pocket-gallery-cap-pad"
                                data-cycle-caption-template="<h3 class=pocket-gall-title>{{cycleTitle}}</h3><p class=pocket-gall-cap>{{cycleCap}}</p>"
                                data-cycle-fx="%s"
                                data-cycle-swipe=true
                                data-cycle-pause-on-hover="true"
                                data-cycle-speed="%s"
                                data-cycle-timeout="%s"
                                data-cycle-prev=".prev"
                                data-cycle-next=".next"
                                >',$trantype,$transpeed,$trandelay);

                        foreach ( $attachments as $attachment_id => $attachment ) {

                            $image = wp_get_attachment_url($attachment->ID, 'full', false,'');
                            $alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
                            $title =  $attachment->post_title;
                            $caption = $attachment->post_excerpt;
                            $desc = $attachment->post_content;

                            echo '<img src="'. $image .'" alt="'. $alt .'" data-cycle-title="'. $title .'" data-cycle-cap="'. $caption .'" ">';
                        }
                    printf('</div>');
                ?>
                </div>
                <div class="span3 zmb ba-single-pocket-gallery-cap-wrap">

                    <div class="ba-single-pocket-gallery-cap"><div class="ba-single-pocket-gallery-cap-pad"></div></div>

                </div>
                <ul class="unstyled ba-single-pocket-gallery-nav">
                    <li><a class="prev" href="#"><i class="icon-chevron-left"></i></a></li>
                    <li><a class="resume" href="#" data-cycle-cmd="resume"><i class="icon-play"></i></a></li>
                    <li><a class="pause" href="#" data-cycle-cmd="pause"><i class="icon-pause"></i></a></li>
                    <li><a class="next" href="#"><i class="icon-chevron-right"></i></a></li>
                </ul>
            </div>
            </div>
            <?php
        }

    }

    // Get Total Shares
    function get_total_shares() {
        global $post;

        //$url = 'http://pagelines.com';
        $url = get_permalink();
        $link = rawurlencode($url);

        $apiurl = 'http://api.sharedcount.com/?url='.$link.'';

        $transientKey = "PocketShareCounts";

        $cached = get_transient($transientKey);

        if (false !== $cached) {
            return $cached;
        }

        $remote = wp_remote_retrieve_body(wp_remote_get($apiurl, array('sslverify'=>false)));

        if( is_wp_error( $remote ) ) {
            echo '<p>There was an error getting the data. Please try again later.</p>';
        } else {
            $count = json_decode( $remote,true);
        }

        $twitter     = $count['Twitter'];
        $fb_like    = $count['Facebook']['like_count'];


        $total = $fb_like + $twitter;
        $output = sprintf('%s',$total);

        set_transient($transientKey, $output, 600);

        return $output;
    }


    function column_display($column){
        global $post;

        switch ($column){
            case "description":
                the_excerpt();
                break;
            case "pocket-categories":
                $this->get_the_post_tags();
                break;
        }
    }

    // fetch the tags for the columns in admin
    function get_the_post_tags() {
        global $post;

        $terms = wp_get_object_terms($post->ID, $this->taxID);
        $terms = array_values($terms);

        for($term_count=0; $term_count<count($terms); $term_count++) {

            echo $terms[$term_count]->slug;

            if ($term_count<count($terms)-1){
                echo ', ';
            }
        }
    }

}
new baPockets;