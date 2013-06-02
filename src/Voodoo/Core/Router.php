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
 * @name        Router
 * @since       Apr 8 2012
 * @desc        A routing class to map uri to own path or controllers in MVC
 *
 * @version     1.1
 *
 * ABOUT
 *
 *      Router is a system for mapping URLs to application actions, and conversely to generate URLs.
 *      Router makes it easy to create pretty and concise URLs that are RESTful with little effort.
 *      Router was meant to be simple not tied to any MVC framework. It's really easy to use it with your own MVC or whatever framework
 *
 *
 * Use of Wildcards:
 *
 * Router use some wildcards to quickly match part of the url and replace it with the proper regex values
 *  (:any)      : Will match anything
 *  (:num)      : will match only numeric values
 *  (:alpha)    : will match only alphabetical values
 *  (:alphanum) : will match only alpha and numeric values
 *
 * Use of Regex
 * Router allow you to use your own regexp to do more complex stuff
 *
 * Example
 *

    $R = new Router;

    $R->setRoutes(array(

        // Use wildcard regexp (:any) or (:num) or (:alpha) or (:alphanum)
        "/news/(:any)/(:num)"=>"/articles/read/$2/$1/",
        "/blog/(:alphanum)/(:num)/(:any)"=>"/readblog/category/$1/post/$2/$3/",

        // use own Regexp
        "/music/([rap|techno|compas]+)/(:num)"=>"/select-music/genre/$1/song/$2",

        // With specific request method: POST | GET
        "POST /music/upload/(:num)"=>"/upload/music/songId/$1"

    ));

    $newRoute = $R->parse();
 *
 *
 ** Credit:
 * This class is:
 *  - based of CodeIgniter Routing class
 *  - Ported to Routes by https://github.com/simonhamp/routes/blob/master/routes.php
 */

namespace Voodoo\Core;

class Router
{
    const Name = "Router";

    const Version = "1.1.1";


    /**
     * Holds the route settings
     * @var type
     */
    protected $routes = [];

    /**
     * Holds extra routes info that were generated
     * @var type
     */
    protected $routesExtended = [];

    /**
     * Allow Query
     */
    protected $allowQuery = true;

    /**
     * The wildCards that will be used
     * @var type
     */
    private $wildCards = [
                            ":any"=>".*",
                            ":num"=>"[0-9]+",
                            ":alpha"=>"[A-Za-z_\-]+",
                            ":alphanum"=>"[A-Za-z0-9_\-]+",
                            ":hex"=>"[A-Fa-f0-9]+"
                        ];

    /**
     * The request method POST | GET
     * @var string
     */
    private $requestMethod = "";

    private $allowedMethods = "(POST|GET|PUT|DELETE)";
    
    /**
     * Statically instantiate the class
     * @param  array  $Routes
     * @return Router
     */
    public static function Create(Array $Routes)
    {
        return new self($Routes);
    }

    /**
     * The constructor
     * @param array $Routes - Set the routes
     */
    public function __construct(Array $Routes = [])
    {
        $this->setRoutes($Routes);
        $this->requestMethod = strtoupper($_SERVER["REQUEST_METHOD"]);
    }

    /**
     * To set more routes in the router
     * @param  array  $Routes
     * @return Router
     */
    public function setRoutes(Array $Routes)
    {
        $nR = [];
        $nR2 = [];
        foreach ($Routes as $k => $v) {
            $nR[$this->removeLeadingSlash($k)] = $this->removeLeadingSlash($v);

            // We'll also remove Index and Main from the key, and assign it to the extended routes
            if (preg_match("/(Main|Index)/",$k)) {
                $k = preg_replace("/(Main|Index)\//","",$k);
                $nR2[$this->removeLeadingSlash($k)] = $this->removeLeadingSlash($v);
            }
        }
        $this->routes = array_merge($this->routes,$nR);
        $this->routesExtended = array_merge($this->routesExtended,$this->routes,$nR2);

        return $this;
    }

    /**
     * Parse the route based on route params
     * @param  string $uri - the route url that will be returned
     * @return string
     */
    public function parse($uri)
    {
        $uri = $this->removeLeadingSlash($uri);
        $originalUri = $uri;
        $qs = "";

        if ($this->allowQuery && strpos( $uri, '?' ) !== false ) {
            // Break the query string off and attach later
            $qs = '?'.parse_url( $uri, PHP_URL_QUERY );
            $uri = str_replace( $qs, '', $uri );
        }

        // Is there a literal match?
        if (isset($this->routes[$uri])){
            return $this->routes[$uri].$qs;
        }

        $Routes = $this->getRoutes(true);

        /**
         * Check each route against the URI
         */
        foreach ($Routes as $route) {   
            $key = str_replace(array_keys($this->wildCards),array_values($this->wildCards),$route["From"]);
            $method = $route["RequestMethod"];
            $val = $route["To"];
            if (preg_match("#^{$key}$#i", $uri)) {
                // Do we have a back-reference?
                if(strpos($val,'$')!== false && strpos($key,"(")!== false){
                    $val = preg_replace("#^{$key}$#i",$val,$uri);
                }
                $route = $val.$qs;
                return ($method && $this->requestMethod != $method) ? $originalUri : $route;
            }
        }
        return $originalUri;
    }

    /**
     * To change the allow query value
     * @param  bool   $allow
     * @return Router
     */
    public function allowQuery($allow = true)
    {
        $this->allowQuery = $allow;
        return $this;
    }

    /**
     * Return routes to array.
     * @param  bool  $addExtendedRoutes - Some routes have been reformatted to match certain type of criteria, set to true to activate
     * @return Array
     */
    public function getRoutes($addExtendedRoutes = false)
    {
        $arr = [];
        foreach (($addExtendedRoutes ? $this->routesExtended : $this->routes) as $key => $val) {
            $method = "";
            if (preg_match("/^{$this->allowedMethods}\s+/i",$key,$m)) {
                $method = strtoupper($m[1]);
                $key = preg_replace("/^{$this->allowedMethods}\s+/i","",$key);
            }
            $arr[] = [
                "RequestMethod" => $method,
                "From" => $key,
                "To" => $val
            ];
        }
        return $arr;
    }

    /**
     * Will remove the leading slash
     * @param  type $str
     * @return string
     */
    private function removeLeadingSlash($str)
    {
        return preg_replace("/^({$this->allowedMethods}\s)?\/(.+)/","$2$3",$str);
    }
}
