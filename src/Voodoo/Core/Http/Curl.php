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
 * @name        Curl
 * @desc        A simple curl class to make call to server
 *
 */

namespace Voodoo\Core\Http;

use Voodoo\Core;
/**
 * Make sure curl_init is enabled
 */
if (!function_exists('curl_init')) {
  throw new Core\Exception('Class '.__CLASS__.' requires the CURL PHP extension.');
}
//------------------------------------------------------------------------------

class Curl
{
   private $response = "";
   private $headers = "";

   /**
    * To make the curl request
    * @param string $url
    * @param mixed $params
    * @param string $method (GET | POST | DELETE | PUT)
    * @param array $curlOptions
    * @return Voodoo\Core\Curl
    * @throws Core\Exception
    */
    public function call($url, $params = [], $method = "GET", Array $curlOptions = [])
    {
        $ch = curl_init();
        $strParams = (is_array($params)) ? http_build_query($params) : $params;

        // Method
        switch (strtoupper($method)) {
            default:
            case "GET":
                $url .= (strpos($url,"?") === FALSE ? "?" : "&").$strParams;
            break;

            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            break;

            case "PUT":
            case "DELETE":
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST,strtoupper($method));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $strParams);
            break;
        }
        
        // Secure Url
        if (preg_match("!^https://!",$url)) {
           curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
           curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 1);
        
        if (count($curlOptions)) {
            curl_setopt_array($ch, $curlOptions);
        }
        
        $this->response = curl_exec($ch);
        $this->headers = curl_getinfo($ch);
        $error = curl_error($ch);
        $errorNo = curl_errno($ch);

        curl_close ($ch);

        if($this->response === FALSE) {
            throw new Core\Exception($error, $errorNo);
        }
           
       return $this;
    }

    /**
     * Return the response object
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Return the header or an element in the header
     * @param  string $key
     * 
     * @return mixed
     */
    public function getHeaders($key = "")
    {
        if ($key) {
            return isset($this->headers[$key]) ? $this->headers[$key] : "";
        } else {
            return $this->headers;
        }
    }

    /**
     * Return the HTTP code
     * @return type
     */
    public function getHTTPCode()
    {
        return $this->getHeaders("http_code");
    }

}
