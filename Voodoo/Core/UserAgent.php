<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/VoodooPHP/Voodoo
 * @package     VoodooPHP
 * 
 * @copyright   (c) 2012 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 * 
 */
/**
 * UserAgent
 *
 * Static class
 *
 * @since July 5 2009
 * @last update: Oct 8 2010, added: isIE6()
 */

namespace Voodoo\Core;

Class UserAgent{

	private static $agent		= NULL;

	private static $platforms	= array();
	private static $browsers	= array();
	private static $mobiles     = array();
	private static $robots		= array();

	public static $platform     = '';
	public static $browser      = '';
	public static $version      = '';
	public static $mobile		= '';
	public static $robot		= '';
        public static $ip           = '';
        public static $referrer     = '';
        public static $isReferral   = FALSE;
	public static $isBrowser	= FALSE;
	public static $isRobot      = FALSE;
	public static $isMobile     = FALSE;

        //------------------------------------------------------------------------------
        public static function init(){

            self::$agent = trim($_SERVER['HTTP_USER_AGENT']);

            self::_settings();

            self::ip();

            self::_set_platform();

            foreach (array('isBrowser', 'isRobot', 'isMobile','isReferral') as $function){
                if (self::$function() === TRUE) break;
            }
        }


	// --------------------------------------------------------------------

	/**
	 * Set the Platform
	 *
	 * @access	private
	 * @return	mixed
	 */
	private static function _set_platform()
	{
			foreach (self::$platforms as $key => $val)
			{
				if (preg_match("|".preg_quote($key)."|i", self::$agent))
				{
					self::$platform = $val;
					return TRUE;
				}
			}

		self::$platform = 'Unknown Platform';
	}

	// --------------------------------------------------------------------

        /**
         * Verify is the access is from ajax.
         * @return bool
         */
        public static function isAjax(){
           return (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=="xmlhttprequest") ? TRUE : FALSE;
        }


	/**
	 * Set the Browser
	 *
	 * @access	private
	 * @return	bool
	 */
	public static function isBrowser(){
	
			foreach (self::$browsers as $key => $val){
			
				if (preg_match("|".preg_quote($key).".*?([0-9\.]+)|i", self::$agent, $match)){
				
					self::$isBrowser = TRUE;
					self::$version = $match[1];
					self::$browser = $val;
					self::isMobile();
					return TRUE;
				}
			}
	
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Robot
	 *
	 * @access	private
	 * @return	bool
	 */
	public static function isRobot()
	{

			foreach (self::$robots as $key => $val)
			{
				if (preg_match("|".preg_quote($key)."|i", self::$agent))
				{
					self::$isRobot = TRUE;
					self::$robot = $val;
					return TRUE;
				}
			}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Mobile Device
	 *
	 * @access	private
	 * @return	bool
	 */
	public static function isMobile()
	{

			foreach (self::$mobiles as $key => $val)
			{
				if (FALSE !== (strpos(strtolower(self::$agent), $key)))
				{
					self::$isMobile = TRUE;
					self::$mobile = $val;
					return TRUE;
				}
			}
		return FALSE;
	}

    public static function isReferral(){
        // Referral
        if(! isset($_SERVER['HTTP_REFERER']) OR $_SERVER['HTTP_REFERER'] == ''){
           self::$isReferral = FALSE;
           return FALSE;
        }
        else{
            self::$isReferral = TRUE;
            self::$referrer = trim($_SERVER['HTTP_REFERER']);
            return TRUE;
        }
    }
//------------------------------------------------------------------------------
    
    /**
     * To manually set an IP
     * @param type $ip 
     */
    public static function setIp($ip){
        self::$ip = $ip;
    }
    
    
/**
 * Retrieve the IP address
 * @return type 
 */    
public static function ip(){
    
    if(self::$ip)
            return
                self::$ip;    
    
    
	/*
	This function checks if user is coming behind proxy server. Why is this important?
	If you have high traffic web site, it might happen that you receive lot of traffic
	from the same proxy server (like AOL). In that case, the script would count them all as 1 user.
	This function tryes to get real IP address.
	Note that getenv() function doesn't work when PHP is running as ISAPI module
	Added Feb 21 2005, from the class usersOnline()
	# Works good on the server
	*/

    
		if (getenv('HTTP_CLIENT_IP')) {
			self::$ip = getenv('HTTP_CLIENT_IP');
		}
		elseif (getenv('HTTP_X_FORWARDED_FOR')) {
			self::$ip = getenv('HTTP_X_FORWARDED_FOR');
		}
		elseif (getenv('HTTP_X_FORWARDED')) {
			self::$ip = getenv('HTTP_X_FORWARDED');
		}
		elseif (getenv('HTTP_FORWARDED_FOR')) {
			self::$ip = getenv('HTTP_FORWARDED_FOR');
		}
		elseif (getenv('HTTP_FORWARDED')) {
			self::$ip = getenv('HTTP_FORWARDED');
		}
		else {
			self::$ip = $_SERVER['REMOTE_ADDR'];
		}

    return self::$ip;
}



/**
 * Create a static function on the static variable
 * @param type $fn
 * @param type $a
 * @return type 
 */
public static function __callStatic($fn,$a){
    
  if(self::$$fn)
            return self::$$fn;
}

//------------------------------------------------------------------------------
public static function _settings(){

/*
| -------------------------------------------------------------------
| USER AGENT TYPES
| -------------------------------------------------------------------
| This file contains four arrays of user agent data.  It is used by the
| User Agent Class to help identify browser, platform, robot, and
| mobile device data.  The array keys are used to identify the device
| and the array values are used to set the actual name of the item.
|
*/

self::$platforms = array (
'windows nt 6.0'	=> 'Windows Longhorn',
'windows nt 5.2'	=> 'Windows 2003',
'windows nt 5.0'	=> 'Windows 2000',
'windows nt 5.1'	=> 'Windows XP',
'windows nt 4.0'	=> 'Windows NT 4.0',
'winnt4.0'			=> 'Windows NT 4.0',
'winnt 4.0'			=> 'Windows NT',
'winnt'				=> 'Windows NT',
'windows 98'                    => 'Windows 98',
'win98'				=> 'Windows 98',
'windows 95'                    => 'Windows 95',
'win95'				=> 'Windows 95',
'windows'			=> 'Unknown Windows OS',
'os x'				=> 'Mac OS X',
'ppc mac'			=> 'Power PC Mac',
'freebsd'			=> 'FreeBSD',
'ppc'				=> 'Macintosh',
'linux'				=> 'Linux',
'debian'			=> 'Debian',
'sunos'				=> 'Sun Solaris',
'beos'				=> 'BeOS',
'apachebench'                   => 'ApacheBench',
'aix'				=> 'AIX',
'irix'				=> 'Irix',
'osf'				=> 'DEC OSF',
'hp-ux'				=> 'HP-UX',
'netbsd'			=> 'NetBSD',
'bsdi'				=> 'BSDi',
'openbsd'			=> 'OpenBSD',
'gnu'				=> 'GNU/Linux',
'unix'				=> 'Unknown Unix OS'
);


// The order of this array should NOT be changed. Many browsers return
// multiple browser types so we want to identify the sub-type first.
self::$browsers = array(
                        'Opera'				=> 'Opera',
                        'MSIE'				=> 'Internet Explorer',
                        'Internet Explorer'	=> 'Internet Explorer',
                        'Shiira'			=> 'Shiira',
                        'Firefox'			=> 'Firefox',
                        'Chimera'			=> 'Chimera',
                        'Phoenix'			=> 'Phoenix',
                        'Firebird'			=> 'Firebird',
                        'Camino'			=> 'Camino',
                        'Netscape'			=> 'Netscape',
                        'OmniWeb'			=> 'OmniWeb',
                        'Safari'			=> 'Safari',
                        'Mozilla'			=> 'Mozilla',
                        'Konqueror'			=> 'Konqueror',
                        'icab'				=> 'iCab',
                        'Lynx'				=> 'Lynx',
                        'Links'				=> 'Links',
                        'hotjava'			=> 'HotJava',
                        'amaya'				=> 'Amaya',
                        'IBrowse'			=> 'IBrowse'
                );


self::$mobiles = array(
					// legacy array, old values commented out
					'mobileexplorer'	=> 'Mobile Explorer',
//					'openwave'			=> 'Open Wave',
//					'opera mini'		=> 'Opera Mini',
//					'operamini'			=> 'Opera Mini',
//					'elaine'			=> 'Palm',
					'palmsource'		=> 'Palm',
//					'digital paths'		=> 'Palm',
//					'avantgo'			=> 'Avantgo',
//					'xiino'				=> 'Xiino',
					'palmscape'			=> 'Palmscape',
//					'nokia'				=> 'Nokia',
//					'ericsson'			=> 'Ericsson',
//					'blackberry'		=> 'BlackBerry',
//					'motorola'			=> 'Motorola'

					// Phones and Manufacturers
					'motorola'			=> "Motorola",
					'nokia'				=> "Nokia",
					'palm'				=> "Palm",
					'iphone'			=> "Apple iPhone",
					'ipod'				=> "Apple iPod Touch",
					'sony'				=> "Sony Ericsson",
					'ericsson'			=> "Sony Ericsson",
					'blackberry'		=> "BlackBerry",
					'cocoon'			=> "O2 Cocoon",
					'blazer'			=> "Treo",
					'lg'				=> "LG",
					'amoi'				=> "Amoi",
					'xda'				=> "XDA",
					'mda'				=> "MDA",
					'vario'				=> "Vario",
					'htc'				=> "HTC",
					'samsung'			=> "Samsung",
					'sharp'				=> "Sharp",
					'sie-'				=> "Siemens",
					'alcatel'			=> "Alcatel",
					'benq'				=> "BenQ",
					'ipaq'				=> "HP iPaq",
					'mot-'				=> "Motorola",
					'playstation portable' 	=> "PlayStation Portable",
					'hiptop'			=> "Danger Hiptop",
					'nec-'				=> "NEC",
					'panasonic'			=> "Panasonic",
					'philips'			=> "Philips",
					'sagem'				=> "Sagem",
					'sanyo'				=> "Sanyo",
					'spv'				=> "SPV",
					'zte'				=> "ZTE",
					'sendo'				=> "Sendo",

					// Operating Systems
					'symbian'				=> "Symbian",
					'SymbianOS'				=> "SymbianOS",
					'elaine'				=> "Palm",
					'palm'					=> "Palm",
					'series60'				=> "Symbian S60",
					'windows ce'			=> "Windows CE",

					// Browsers
					'obigo'					=> "Obigo",
					'netfront'				=> "Netfront Browser",
					'openwave'				=> "Openwave Browser",
					'mobilexplorer'			=> "Mobile Explorer",
					'operamini'				=> "Opera Mini",
					'opera mini'			=> "Opera Mini",

					// Other
					'digital paths'			=> "Digital Paths",
					'avantgo'				=> "AvantGo",
					'xiino'					=> "Xiino",
					'novarra'				=> "Novarra Transcoder",
					'vodafone'				=> "Vodafone",
					'docomo'				=> "NTT DoCoMo",
					'o2'					=> "O2",

					// Fallback
					'mobile'				=> "Generic Mobile",
					'wireless' 				=> "Generic Mobile",
					'j2me'					=> "Generic Mobile",
					'midp'					=> "Generic Mobile",
					'cldc'					=> "Generic Mobile",
					'up.link'				=> "Generic Mobile",
					'up.browser'			=> "Generic Mobile",
					'smartphone'			=> "Generic Mobile",
					'cellphone'				=> "Generic Mobile"
				);


// There are hundreds of bots but these are the most common.
self::$robots = array(
					'googlebot'			=> 'Googlebot',
					'msnbot'			=> 'MSNBot',
					'slurp'				=> 'Inktomi Slurp',
					'yahoo'				=> 'Yahoo',
					'askjeeves'			=> 'AskJeeves',
					'fastcrawler'		=> 'FastCrawler',
					'infoseek'			=> 'InfoSeek Robot 1.0',
					'lycos'				=> 'Lycos'
				);



}

//------------------------------------------------------------------------------

    /**
     * Check if the user agent is ie6
     * @return bool
     */
    public static function isIE6(){
        return strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 6.") !== false;
    }


}

// Autoload 
UserAgent::init();
