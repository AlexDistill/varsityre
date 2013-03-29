<?php
/*
Plugin Name: Browser Specific CSS
Plugin URI: http://www.pagelines.com
Description: Add browser specific classes to the main body tags for all major browsers and devices.
Author: PageLines
PageLines: true
Version: 1.3
*/

class Browser_Specific_CSS {
	
	function __construct() {
		
		$this->useragent = ( isset($_SERVER['HTTP_USER_AGENT'] ) ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		add_filter( 'body_class', array( &$this, 'body_class' ) );
			
	}

	/***************************************************************
	* Function is_iphone
	* Detect the iPhone
	***************************************************************/

	function is_iphone() {
		
		return(preg_match('/iphone/i',$this->useragent));
	}

	/***************************************************************
	* Function is_ipad
	* Detect the iPad
	***************************************************************/

	function is_ipad() {

		return(preg_match('/ipad/i',$this->useragent));
	}

	/***************************************************************
	* Function is_ipod
	* Detect the iPod, most likely the iPod touch
	***************************************************************/

	function is_ipod() {
		
		return(preg_match('/ipod/i',$this->useragent));
	}

	/***************************************************************
	* Function is_android
	* Detect an android device. They *SHOULD* all behave the same
	***************************************************************/

	function is_android() {
		
		return(preg_match('/android/i',$this->useragent));
	}

	/***************************************************************
	* Function is_blackberry
	* Detect a blackberry device 
	***************************************************************/

	function is_blackberry() {
		
		return(preg_match('/blackberry/i',$this->useragent));
	}

	/***************************************************************
	* Function is_opera_mobile
	* Detect both Opera Mini and hopfully Opera Mobile as well
	***************************************************************/

	function is_opera_mobile() {
		
		return(preg_match('/opera\smini/i',$this->useragent));
	}

	/***************************************************************
	* Function is_palm
	* Detect a webOS device such as Pre and Pixi
	***************************************************************/

	function is_palm() {
		
		return(preg_match('/webOS/i', $this->useragent));
	}

	/***************************************************************
	* Function is_symbian
	* Detect a symbian device, most likely a nokia smartphone
	***************************************************************/

	function is_symbian() {
		
		return(preg_match('/Series60/i', $this->useragent) || preg_match('/Symbian/i', $this->useragent));
	}

	/***************************************************************
	* Function is_windows_mobile
	* Detect a windows smartphone
	***************************************************************/

	function is_windows_mobile() {
		
		return(preg_match('/WM5/i', $this->useragent) || preg_match('/WindowsMobile/i', $this->useragent));
	}

	/***************************************************************
	* Function is_lg
	* Detect an LG phone
	***************************************************************/

	function is_lg() {
		
		return(preg_match('/LG/i', $this->useragent));
	}

	/***************************************************************
	* Function is_motorola
	* Detect a Motorola phone
	***************************************************************/

	function is_motorola() {
		
		return(preg_match('/\ Droid/i', $this->useragent) || preg_match('/XT720/i', $this->useragent) || preg_match('/MOT-/i', $this->useragent) || preg_match('/MIB/i', $this->useragent));
	}

	/***************************************************************
	* Function is_nokia
	* Detect a Nokia phone
	***************************************************************/

	function is_nokia() {
		
		return(preg_match('/Series60/i', $this->useragent) || preg_match('/Symbian/i', $this->useragent) || preg_match('/Nokia/i', $this->useragent));
	}

	/***************************************************************
	* Function is_samsung
	* Detect a Samsung phone
	***************************************************************/

	function is_samsung() {
		
		return(preg_match('/Samsung/i', $this->useragent));
	}

	/***************************************************************
	* Function is_samsung_galaxy_tab
	* Detect the Galaxy tab
	***************************************************************/

	function is_samsung_galaxy_tab() {
		
		return(preg_match('/SPH-P100/i', $this->useragent));
	}

	/***************************************************************
	* Function is_sony_ericsson
	* Detect a Sony Ericsson
	***************************************************************/

	function is_sony_ericsson() {
		
		return(preg_match('/SonyEricsson/i', $this->useragent));
	}

	/***************************************************************
	* Function is_nintendo
	* Detect a Nintendo DS or DSi
	***************************************************************/

	function is_nintendo() {
		
		return(preg_match('/Nintendo DSi/i', $this->useragent) || preg_match('/Nintendo DS/i', $this->useragent));
	}

	/***************************************************************
	* Function is_handheld
	* Wrapper function for detecting ANY handheld device
	***************************************************************/

	function is_handheld() {
		return($this->is_iphone() || $this->is_ipad() || $this->is_ipod() || $this->is_android() || $this->is_blackberry() || $this->is_opera_mobile() || $this->is_palm() || $this->is_symbian() || $this->is_windows_mobile() || $this->is_lg() || $this->is_motorola() || $this->is_nokia() || $this->is_samsung() || $this->is_samsung_galaxy_tab() || $this->is_sony_ericsson() || $this->is_nintendo());
	}

	/***************************************************************
	* Function is_mobile
	* Wrapper function for detecting ANY mobile phone device
	***************************************************************/

	function is_mobile() {
		if ($this->is_tablet()) { return false; }  // this catches the problem where an Android device may also be a tablet device
		return($this->is_iphone() || $this->is_ipod() || $this->is_android() || $this->is_blackberry() || $this->is_opera_mobile() || $this->is_palm() || $this->is_symbian() || $this->is_windows_mobile() || $this->is_lg() || $this->is_motorola() || $this->is_nokia() || $this->is_samsung() || $this->is_sony_ericsson() || $this->is_nintendo());
	}

	/***************************************************************
	* Function is_ios
	* Wrapper function for detecting ANY iOS/Apple device
	***************************************************************/

	function is_ios() {
		return($this->is_iphone() || $this->is_ipad() || $this->is_ipod());

	}

	/***************************************************************
	* Function is_tablet
	* Wrapper function for detecting tablet devices
	***************************************************************/

	function is_tablet() {
		return($this->is_ipad() || $this->is_samsung_galaxy_tab());
	}
	
	
	function body_class($classes) 
	{

		global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_safari, $is_chrome;

		// top level
		if ($this->is_handheld()) { $classes[] = 'handheld'; };
		if ($this->is_mobile()) { $classes[] = 'mobile'; };
		if ($this->is_ios()) { $classes[] = 'ios'; };
		if ($this->is_tablet()) { $classes[] = 'tablet'; };

		// specific 
		if ($this->is_iphone()) { $classes[] = 'iphone'; };
		if ($this->is_ipad()) { $classes[] = 'ipad'; };
		if ($this->is_ipod()) { $classes[] = 'ipod'; };
		if ($this->is_android()) { $classes[] = 'android'; };
		if ($this->is_blackberry()) { $classes[] = 'blackberry'; };
		if ($this->is_opera_mobile()) { $classes[] = 'opera-mobile';}
		if ($this->is_palm()) { $classes[] = 'palm';}
		if ($this->is_symbian()) { $classes[] = 'symbian';}
		if ($this->is_windows_mobile()) { $classes[] = 'windows-mobile'; }
		if ($this->is_lg()) { $classes[] = 'lg'; }
		if ($this->is_motorola()) { $classes[] = 'motorola'; }
		if ($this->is_nokia()) { $classes[] = 'nokia'; }
		if ($this->is_samsung()) { $classes[] = 'samsung'; }
		if ($this->is_samsung_galaxy_tab()) { $classes[] = 'samsung-galaxy-tab'; }
		if ($this->is_sony_ericsson()) { $classes[] = 'sony-ericsson'; }
		if ($this->is_nintendo()) { $classes[] = 'nintendo'; }

		// bonus
		if (!$this->is_handheld()) { $classes[] = 'desktop'; }
		if ($is_lynx) { $classes[] = 'lynx'; }
		if ($is_gecko) { $classes[] = 'firefox'; }
		if ($is_opera) { $classes[] = 'opera'; }
		if ($is_safari) { $classes[] = 'safari'; }
		if ($is_chrome) { $classes[] = 'chrome'; }
		if ($is_IE) { 
			$ie_ver = pl_detect_ie();
			$classes[] = 'ie ie' . $ie_ver;
		 }

		return $classes;
	}
} // end class

new Browser_Specific_CSS;