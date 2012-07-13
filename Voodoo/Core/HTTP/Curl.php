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
 * @name        Core\HTTP\Curl
 * @since       Apr 21, 2011
 * @desc        A curl class
 * 
 */

namespace Voodoo\Core\HTTP;

use Voodoo\Core\Exception;
//------------------------------------------------------------------------------

/**
 * Make sure curl_init is enabled
 */
if (!function_exists('curl_init')) 
  throw new Exception('Class Core\Curl requires the CURL PHP extension.');

//------------------------------------------------------------------------------


class Curl{
    
   protected $url;
   protected $params = array();
   
   
   /**
    * Init curl via static, Curl::init("http://api.yoursite.com")
    * @param string $url - The url
    * @return static 
    */
   public static function init($url){
       return new static($url);
   }
   
//------------------------------------------------------------------------------

   /**
    * init curl
    * @param string $url  - The url
    */
   public function __construct($url=""){
        if($url)
            $this->setUrl($url);
    }

    /**
     * To reset eveything
     * @return Curl 
     */
    public function reset(){
        $this->params = array();
        $this->url = "";
        return $this;
    }
    
    /**
     * To reset all params set
     * @return Curl 
     */
    public function resetParams(){
        
       $this->params = array();
       
       return $this;
    }
    
    /**
     * Set the URL
     * @param type $url
     * @return Curl 
     */
    public function setUrl($url){
       $this->url = $url;
       return $this;
    }
    
    /**
     * To add a segment in a URL
     * @param type $segment
     * @param type $separator
     * @return Curl 
     */
    public function addSegment($segment,$separator="/"){
        $this->url .= (preg_match("!{$separator}$!",$this->url) ? "" : $separator).$segment;
        return $this;
    }
    
    
    /**
     * Add params to be sent along
     * @param type $key
     * @param type $value
     * @return Curl 
     */
    public function addParam($key,$value=""){

        if(is_array($key))
            $this->params = array_merge($this->params,$key);
        else
            $this->params[$key] = $value;
        
        return $this;
        
    }
//------------------------------------------------------------------------------


    /**
     * Make a post request
     * @return CurlResponse 
     */
    public function post(){
       return $this->makeRequest("POST"); 
    }
    
    
    /**
     * make a GET request
     * @return CurlResponse
     */
    public function get(){
        return $this->makeRequest("GET");
    }
    
//------------------------------------------------------------------------------

    /**
     * Make the request and return the CURL RESPONSE
     * @param string $method  POST|GET (DELETE | PUT)
     * @return CurlResponse 
     */
    final protected function makeRequest($method = "GET"){

        $ch = curl_init();

        $url = $this->url;
        
        $strParams = http_build_query($this->params);
        
        /**
         * Methods
         */
        switch(strtoupper($method)){
        
            default:
            case "GET":
                $url .= (strpos($this->url,"?") === FALSE ? "?" : "&").$strParams;
            break;
        
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);                
            break;

        
            case "PUT":
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $strParams);
            break;
        
        
            case "DELETE":
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $strParams);
            break;        
        }

        /**
         * SECURE URL
         */
        if(preg_match("!^https://!",$url)){
           curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
           curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 1);
        
        $response = curl_exec($ch);
        
        if($response === FALSE)
            throw new Exception(curl_error($ch),curl_errno($ch));
        
        /**
         * Data to return to CurlResponse
         */
        $Data = array(
            "response"=>$response,
            "headers"=>curl_getinfo($ch)
        );

        curl_close ($ch);

        return 
            new CurlResponse($Data);

    }
    
}




