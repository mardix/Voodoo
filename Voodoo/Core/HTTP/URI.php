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
 * @name        Core\HTTP\URI
 * @since       ---
 * @desc        A URI Class, to access the uri and split them into segment
 * 
 */

namespace Voodoo\Core\HTTP;

use Voodoo\Core\Exception;

class URI {

    public static $uri_string;
    
    public static $segments = array();
    public static $segments_assoc = array();
    public static $registeredKeywords = array();
    public static $UrlQuery = array();

//------------------------------------------------------------------------------


        /**
         * Split the URI in multiple segment
         */
        public static function init(){

            // Fetch the complete URI string
            self::$uri_string = $_SERVER['QUERY_STRING'];

            // If the URI contains only a slash we'll kill it
            if (self::$uri_string == '/') self::$uri_string = '';

            $Xu = explode("/", preg_replace("|/*(.+?)/*$|", "\\1", self::$uri_string));

            //self::$segments[0] = self::$uri_string;
            $i = 0;
            foreach($Xu as $val) {

               self::$segments[++$i] = $val;

            }
            self::$segments_assoc = self::toAssoc(2);

            
            /**
             * Build query url. Everything after ? will also be saved and retrieved by k/v 
             */
            $query = (parse_url($_SERVER["REQUEST_URI"],PHP_URL_QUERY));
            $q1 = explode("&",$query);
            foreach($q1 as $v){
                $x = explode("=",$v,2);
                self::$UrlQuery[$x[0]] = $x[1];
            }             
            
         }

        /**
         * Return a segment of the URI
         * @param type $n (numeric or string)
         * @return type 
         */
	public static function get($n){
                return is_numeric($n)
                        ? ((self::$segments[$n]) ? self::$segments[$n] : false )
                        : (self::$segments_assoc[$n] ? self::$segments_assoc[$n] : false);
		
	}

        
        
        /**
         * Return all the segments
         * @return Array
         */
        public static function toArray(){
            return self::$segments;
        }
	// --------------------------------------------------------------------

	/**
	 * Generate a key value pair from the URI string
	 *
	 * This function generates and associative array of URI data starting
	 * at the supplied segment. For example, if this is your URI:
	 *
	 *	www.your-site.com/user/search/name/joe/location/UK/gender/male
	 *
	 * You can use this function to generate an array with this prototype:
	 *
	 * array (
	 *			name => joe
	 *			location => UK
	 *			gender => male
	 *		 )

	 */
	public static function toAssoc($n = 1){
		$segments = array_slice(self::$segments, ($n));

		$i = 0;
		$lastval = '';
		$retval  = array();
		foreach ($segments as $seg)	{
                    if ($i % 2)	{
                        $retval[$lastval] = $seg;
                    }
                    else {
                        $retval[$seg] = FALSE;
                        $lastval = $seg;
                    }

                    $i++;
		}

		// Cache the array for reuse
		return $retval;

	}


	// --------------------------------------------------------------------

	/**
	 * Fetch a URI Segment and add a trailing slash - helper function
	 */
	public static function slashSegment($n, $where = 'trailing'){

		if ($where == 'trailing'){
			$trailing	= '/';
			$leading	= '';
		}
		elseif ($where == 'leading'){
			$leading	= '/';
			$trailing	= '';
		}

		else{
			$leading	= '/';
			$trailing	= '/';
		}

		return ($leading.self::$segments[$n].$trailing);
	}

//------------------------------------------------------------------------------

    // Retrieve site url along with index
	public static function site_url($uri = ''){

		if (is_array($uri))	{
			$uri = implode('/', $uri);
		}

		if ($uri == '')	{
			return self::trailSlash('base_url').self::$_index_page;
		}

	}
//------------------------------------------------------------------------------
      // Add a trailing slash at the end
      public static function trailSlash($item)	{

                    if ($item != ''){

                      if (preg_match("/\/$/", $item) === FALSE)	$item .= '/';

                    }

                    return $item;
      }


    /**
     * To add slash
     * @param <type> $str
     * @param <type> $where (leading|trailing|both)
     * @return <type> string
     */
    public static function addSlash($str,$where="trailing"){

       $s = preg_replace("#^(/)|(/)$#","",$str);

       switch($where){
           case "leading":
               return ("/{$s}");
           break;

           case "trailing":
               return ("{$s}/");
           break;

           case "both":
               return ("/{$s}/");
           break;
       }
    }

    /**
     * To remove slash
     * @param <type> $str
     * @param <type> $where (leading|trailing|both)
     * @return <type>
     */
    public static function removeSlash($str,$where="leading"){
       switch($where){
           case "leading":
               return preg_replace("#^(/)$#","",$str);
           break;

           case "trailing":
               return preg_replace("#(/)$#","",$str);
           break;

           case "both":
               return preg_replace("#^(/)|(/)$#","",$str);
           break;
       }
    }
//------------------------------------------------------------------------------
    // To create a path back url
    public static function trailBackUrl(){

      $count = count(self::$segments);

      if($count) {

            // If there is a trailing slash, will increase the count by 1, so the path_back can be ok
            if(preg_match('/\/$/',self::$uri_string)) $count++;

        // Add another slash, but will be removed
        $path=str_repeat("../",$count+1);

            // Remove the last slash
        $path=substr($path,0,-1);

            return $path;
      }

    }
//------------------------------------------------------------------------------

    /**
     *  Register the name of the segment so it can search through certain type of data. Save it i a lower case
     * @param type $segmentID
     * @param type $segmentName
     * @param type $segmentValue 
     */
    public static function registerKeyword($segmentID,$segmentName,$segmentValue){
        self::$registeredKeywords[$segmentID][$segmentName]=strtolower($segmentValue);
    }

    /**
     * Return the
     * @param type $segmentID
     * @return Array 
     */
    public static function getRegisteredKeyword($segmentID){

          if(is_array(self::$segments) && is_array(self::$registeredKeywords[$segmentID])){

             // lower case
             foreach(array_map("strtolower",self::$segments) as $value){

                        if(in_array($value,self::$registeredKeywords[$segmentID])) {

                          $v=array_keys(self::$registeredKeywords[$segmentID],$value);

                          return $v[0];
                        }

             }# end foreach

          }# end if

    }

    
    
    /**
     * Catch the first numeric value found
     * @return int
     */
    public static function catchNumeric(){

       if(is_array(self::$segments)){
         foreach(self::$segments as $value)
               if(is_numeric($value)) 
                   return $value;

       }
       
       return 
            false;
    }

    /**
     * Check if uri contains a segment
     * @param type $seg
     * @return type 
     */
    public static function has($seg){
        return in_array($seg,self::$segments);
    }
    
    
    /**
     * Get the URI string to string
     * @return string
     */
    public function __toString(){
        return self::$uri_string;
    }
    
    /**
     * Alias of toString()
     * @return type 
     */
    public static function to_s(){
        return self::$uri_string;
    }

    /**
     * Return the k/v of any fields after ?
     * @param type $key
     * @return type 
     */
    function getUrlQuery($key=null){
        return
           ($key == null) ? self::$UrlQuery : (self::$UrlQuery[$key] ? urldecode(self::$UrlQuery[$key]) : "" );
    }

    /**
     *  Check if the query contains a key. It can be blank
     * @param type $key
     * @return type 
     */
    function queryUrlHas($key){
        return
            isset(self::$UrlQuery[$key]) ? true : false;
    }    
    
    /**
     * To implode the segments with slashes
     * @return type 
     */
    public static function getPathSegments(){
        return 
            implode("/",self::$segments);
    }
//------------------------------------------------------------------------------

/**
 * To make friendly url;
 * @param string $O
 * @return type 
 */
public static function toFriendlyUrl($O) {

        // Clean up some words, concat 's
	$O = preg_replace("/\s+(a|an|the|and|or|of|for)\s+/i","-", str_replace("'s ","s ",$O) );
        
        // replace non words with - and remove excessive -
        $O = preg_replace("/\-{2,}/","-",preg_replace("/[^a-z0-9_\-]/i","-",$O));

        return 
            preg_replace("/^\-|\-$/","",$O);
}



//------------------------------------------------------------------------------
}

// init class
Uri::init();
