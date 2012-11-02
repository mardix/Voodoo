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
 * @name        Env (Environment)
 * @desc        Class for your application's environment
 *
 */

namespace Voodoo\Core;

class Env {
    
    /**
     * Check if the SAPI is CLI or not
     * 
     * @return bool
     */
    public function isCLI()
    {
        return php_sapi_name()=== "cli";
    }
    
    /**
     * Get the domain url
     * 
     * @return string
     */
    public function getUrl()
    {
        $domain = "http";
        $domain .= self::isHttps() ? "s" : "";
        $domain .= "://".self::getHostName();
        
        return $domain;
    }
    
    /**
     * Return the server name
     * 
     * @return string
     */
    public function getServerName()
    {
        return $_SERVER["SERVER_NAME"];
    }
    
    /**
     * Get the server http host name
     * 
     * @return string
     */
    public function getHostName()
    {
        return $_SERVER["HTTP_HOST"];
    }

    /**
     * Check if the environment is under HTTPS
     * 
     * @return bool
     */
    public function isHttps()
    {
        return (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== 'off') 
                || ($_SERVER["SERVER_PORT"] == 443)  ;     
    }
    
    
    /**
     * Return the current PHP version
     * 
     * @return string
     */
    public function getPhpVersion()
    {
        return \PHP_VERSION;
    } 
    
    
    /**
     * Get the running application's base dir, usually the root where index.php is being run
     * 
     * @return string
     */
    public function getApplicationBaseDir()
    {
        return str_replace("\\","/",pathinfo($_SERVER["SCRIPT_NAME"],PATHINFO_DIRNAME));
    }
}

