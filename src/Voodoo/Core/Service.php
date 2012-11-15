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
 * @name        Service
 * @desc        Allows you to connect to any service that have some type of API available
 *
 */

namespace Voodoo\Core;

class Service
{
    private $endpoint = "";
    private $params = array();
    private $method = null;
    
    private $headers = array();
    private $response = "";
    private $httpCode = "";

    /**
     * Constructor
     * @param string $endpoint
     */
    public function __construct($endpoint = "")
    {
        if ($endpoint) {
            $this->setEndpoint($endpoint);
        }
    }
    
    /**
     * Set the service endpoint
     * 
     * @param string $endpoint
     * @return \Voodoo\Core\Service
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }
    
    /**
     * Set the params 
     * @param mixed $key
     * @param string $value
     * @return \Voodoo\Core\Service
     */
    public function setParam($key, $value="")
    {
        if (is_array($key)) {
            foreach ($key as $k => $v){
                $this->setParam($k, $v);
            }
        } else {
            $this->params[$key] = $value;
        }
        return $this;
    }

    /**
     * Set options headers to be passed when making the call
     * @param array $headers
     */
    public function setHeaders(Array $headers)
    {
        $this->headers = $headers;
        return $this;
    }
    
    /**
     * Make a POST request
     * @return \Voodoo\Core\Service
     */
    public function post()
    {
        $this->method = "POST";
        $this->call();
        return $this;
    }
    
    /**
     * Make a GET request
     * @return \Voodoo\Core\Service
     */
    public function get()
    {
        $this->method = "GET";
        $this->call();
        return $this;        
    }

    /**
     * Return the response string
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }
    
    /**
     * Return the HTTP code response
     * 
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode();
    }
    
    /**
     * Return a json response as Array
     * @return Array
     * @throws Core\Exception
     */
    public function getJson()
    {
        if (!$this->method) {
            $this->get();
        }
        
       $data =  json_decode($this->getResponse(),true);
       
       $jsonError = Helpers::getJsonLastError();
       if ($jsonError) {
           throw new Exception($jsonError["message"], $jsonError["code"]);
       } else {
           return $data;
       }       
    }
    
    /**
     * Get the XML response and return it into SimpleXMLElement.
     * If there is an error, it will throw an exception
     * @return SimpleXMLElement
     */
    public function getXMLElement()
    {
        libxml_use_internal_errors(true);

        $SXML = simplexml_load_string($this->getResponse());

        if (!$SXML) {
            foreach (libxml_get_errors() as $E) {
                if ($E->message) {
                    libxml_clear_errors();
                    throw new Core\Exception($E->message, $E->code);
                }
            }
        } 
        return $SXML;
    }    
    
    /**
     * Make the curl call
     */
    protected function call()
    {
        $curl = new Http\Curl;
        $curl->call($this->endpoint, $this->params, $this->method, $this->headers);
        $this->response = $curl->getResponse();
        $this->httpCode = $curl->getHttpCode();
        $this->headers = $curl->getHeaders();
        return $this;
    }    
}

