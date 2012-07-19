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

use Voodoo\Core;


Class CurlResponse{
    
    protected $response = "";
    
    
    public function __construct($response){
        $this->response = $response;
    }

    
    /**
     * return the string value of the response
     * @return string 
     */
    public function __toString() {
        return $this->toString();
    }

    /**
     * Return to string 
     */
    public function toString(){
        return $this->response;
    }
    
    /**
     * Get the XML response and return it into SimpleXMLElement. 
     * If there is an error, it will throw an exception
     * @return SimpleXMLElement 
     */
    public function toXMLElement(){

        libxml_use_internal_errors(true);

        $SXML = simplexml_load_string($this->response());

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
    public function toArray(){
        
       $data =  json_decode($this->response,true);
       $msg = "";
       
         switch (json_last_error()) {
            case JSON_ERROR_NONE:
            break;
            case JSON_ERROR_DEPTH:
                $msg =  'Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                $msg =  'Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                $msg =  'Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                $msg =  'Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                $msg =  'Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
            default:
                $msg = 'Unknown error';
            break;
        }
        
        if($msg)
            throw new Core\Exception($msg);
        
        return
            $data;
    }
        
}




