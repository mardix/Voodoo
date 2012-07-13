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
 * @name        Core\Sessions
 * @since       Apr 21, 2011
 * @desc        Curl class response
 * 
 */

namespace Voodoo\Core\HTTP;

use Voodoo\Core\Exception;

include_once(SOUP_INCLUDES_PATH."/encoding-functions.php");


Class CurlResponse{
    
    protected $ResponseData;
    
    
    public function __construct(Array $Response){
        $this->ResponseData = $Response;
    }

    /**
     * Return the response
     * @return type 
     */
    public function response(){
       return
            $this->ResponseData["response"];
    }
    
    /**
     * Return the http code. On success it should be 2XX
     * @return int 
     */
    public function httpCode(){
        return $this->ResponseData["headers"]["http_code"];
    }
    
    
    /**
     * return the string value of the response
     * @return string 
     */
    public function __toString() {
        return $this->response();
    }
//------------------------------------------------------------------------------
    
    /**
     * Get the XML response and return it into SimpleXMLElement. 
     * If there is an error, it will throw an exception
     * @return SimpleXMLElement 
     */
    public function toXMLElement(){

        libxml_use_internal_errors(true);

        $SXML = simplexml_load_string(utf8_encode($this->response()));

        if(!$SXML){
            foreach(libxml_get_errors() as $E){
                if($E->message){
                    libxml_clear_errors();
                    throw new Exception($E->message,$E->code);                     
                }
            }
        } 
        
        else
            return $SXML;
    }    
    
    
    
    /**
     * Return a json data to array
     * @return Array
     */
    public function jsonToArray(){
        return 
            json_decode($this->response(),true);
    }
        
}




