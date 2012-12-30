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
     * The path of the App directory
     * @type string 
     */
    $baseAppDir = __DIR__."/App";

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

    (new Voodoo\Core\Voodoo($baseAppDir, $appName, $uri))->doMagic();
    