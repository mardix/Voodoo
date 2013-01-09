<?php
/**
 * -----------------------------------------------------------------------------
 *                      VoodooPHP Front Controller
 * The main entry point of your application
 * Every request is rerouted through here and it will do the magic... Abracadabra...
 * -----------------------------------------------------------------------------
 * @name        index.php
 */
/******************************************************************************/

require_once __DIR__."/Voodoo/autoload.php";

    /**
     * The root dir of your App directory
     * By default it is __DIR__ (NOT that __DIR__."/App")
     * 
     * @type string 
     */
    $rootAppDir = __DIR__;

    /**
     * Set the application name to use. By default it's Www
     * @type string
     */
    $appName = "www";

    /**
     * The URI
     * @type string
     */
    $uri = implode("/", Voodoo\Core\Http\Request::getUrlSegments());

    (new Voodoo\Core\Voodoo($rootAppDir, $appName, $uri))->doMagic();
    