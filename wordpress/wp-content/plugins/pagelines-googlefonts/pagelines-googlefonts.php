<?php
/*
Plugin Name: PageLines Googlefonts
Plugin URI: http://www.pagelines.com
Description: Add all the Googlefonts to the font system.
Author: PageLines
PageLines: true
Version: 1.5
External: http://www.pagelines.com
Demo: http://www.google.com/webfonts
*/
class Google_Fonts {

	function __construct() {

		add_filter ( 'pagelines_foundry', array( &$this, 'google_fonts' ) );
		add_action( 'admin_init', array( &$this, 'admin_page' ) );
	}

	function google_fonts( $thefoundry ) {

		if ( ! defined( 'PAGELINES_SETTINGS' ) )
			return;

		$fonts = $this->get_fonts();
		return array_merge( $thefoundry, $fonts );

	}


	function admin_page() {
		if ( ! function_exists( 'ploption' ) )
			return;
		pl_add_options_page( array(
			'name'	=> 'googlefonts',
			'raw'	=> $this->instructions(),
		 	'title'	=> 'GoogleFonts Control Panel.'
		) );
	}


	function instructions() {

		$fcount = $this->get_fonts( true );
		return sprintf( '<p>There are currently %s fonts.%s</p><a class="button button-primary" href="%s">Rebuild Lists</a>',
			$fcount['number'],
			( false == $fcount['cached'] ) ? ' Using old style outdated list.' : ' Using the API to fetch all fonts :D',
			admin_url( 'admin.php?page=pagelines&gfont=rebuild')
			);
	}

	function get_fonts( $count = false ) {

		$fcount = array( 
			'cached'	=> false,
			'number'	=> 0
			);
		$url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyAc-H7le81NcghctcW8jXoCaDR73ZyMwZY';
		if( $fonts = get_option( 'pl_gfonts' ) ) {

			$fonts = json_decode( $fonts );
			$fcount['cached'] = true;
			$fcount['number'] = count( $fonts->items );
			if( $count ) 
				return $fcount;
			else
				return $this->format( $fonts->items );
		}

		$response = wp_remote_get( $url );
		if ( $response !== false ) {
			if( ! is_array( $response ) || ( is_array( $response ) && $response['response']['code'] != 200 ) ) {
				$fonts = $this->legacy();
			} else {

			$fonts = wp_remote_retrieve_body( $response );
			update_option( 'pl_gfonts', $fonts );
			$fcount['cached'] = true;
			$fonts = json_decode( $fonts );
			}
		}
		$fcount['number'] = count( $fonts->items );
		if( $count )
			return $fcount;
		else
			return $this->format( $fonts->items );
	}


	function format( $fonts ) {

		$fonts = ( array ) $fonts;

		$out = array();

		foreach ( $fonts as $font ) {

			$out[ str_replace( ' ', '_', $font->family ) ] = array(
				'name'		=> $font->family,
				'family'	=> sprintf( '"%s"', $font->family ),
				'web_safe'	=> true,
				'google' 	=> $font->variants,
				'monospace' => ( preg_match( '/\sMono/', $font->family ) ) ? 'true' : 'false',
				'free'		=> true
			);
		}
		return $out;
	}


	function legacy() {

		$fonts = pl_file_get_contents( dirname(__FILE__) . '/fonts.json' );

		return json_decode( $fonts );
	}
}

new Google_Fonts;