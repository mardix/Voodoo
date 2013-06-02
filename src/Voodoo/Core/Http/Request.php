<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/mardix/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2012 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * @name        Core\HTTP\Request
 * @desc        Access HTTP Request data
 */

namespace Voodoo\Core\Http;

class Request
{
    CONST GET = "GET";
    CONST POST = "POST";
    CONST DELETE = "DELETE";
    CONST PUT = "PUT";
    
    private static $params = [];
    private static $segments = null;
    /**
     * Return the $_GET
     * @return array
     */
    public static function getGetParams()
    {
        $params = [];
        $query = (parse_url($_SERVER["REQUEST_URI"],PHP_URL_QUERY));
        parse_str($query, $params);
        return $params;
    }

    /**
     * Return the $_POST
     * @return array
     */
    public static function getPostParams()
    {
        return $_POST;
    }

    /**
     * Return the params of both GET or POST
     * @param string $key
     * @param mixed $default
     */
    public static function getParam($key = null, $default = null)
    {
        if (! self::$params) {
            $params = array_merge(self::getGetParams(), self::getPostParams());
            self::$params = array_filter($params);
        }
        return (isset(self::$params[$key])) ? self::$params[$key] : $default;
    }
          

    /**
     * To check the request Method
     * @param string $method
     */
    public static function is($method="POST")
    {
        return (strtolower(self::getMethod()) === strtolower($method));
    }

    /**
     * Return the request method
     * @return string
     */
    public static function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
    * Verify is the access is from ajax.
    * @return bool
    */
    public static function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=="xmlhttprequest")
               ? true : false;
    }

    /**
     * Return segments of the URL
     * Segments are part of the URL separated by a slash /
     * @return array
     */
    public static function getUrlSegments()
    {
        return explode("/",$_SERVER["QUERY_STRING"]);
    }
    
    /**
     * Return the request ip
     * 
     * @return string
     */
    public static function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
          $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
          $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
          $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }   
    
    /**
     * Segements are part of the URL separated by /
     * ie: /gummy/bear/?q=hello 'gummy' and 'bear' are segments.
     * 
     * @param mixed (int | string) - if int, i will pick the index of the . If a string, it will return the k/v pair of the segemnt
     * @param int Where to start the segment
     * @param array $segmentsArr the array segments to use
     * @return mixed
     */
    public static function getSegment($key = null, $offset = 0, Array $segmentsArr = null)
    {
        if (! $segmentsArr) {
            if (self::$segments == null) {
                self::$segments = self::getUrlSegments();
            }
            $segmentsArr = self::$segments;
        } 
        
        if (is_numeric($key)) {
           return $segmentsArr[$key - 1];
        } else if (is_string($key)) {
            $segments = array_slice($segmentsArr, $offset);
            $i = 0;
            $lastval = '';
            $segs = array();

            foreach ($segments as $seg) {
                if ($i % 2) {
                    $segs[$lastval] = $seg;
                } else {
                    $segs[$seg] = FALSE;
                    $lastval = $seg;
                }
                $i++;
            }
            return ($key) ? $segs[$key] : $segs;
        } else {
            return $segmentsArr;
        }
    }    
}
