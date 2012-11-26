<?php
/**
 * -----------------------------------------------------------------------------
 *                      VoodooPHP Front Controller
 *
 * The main entry point of your application
 *
 * Every request is rerouted through here and we'll do the magic... Abracadabra...
 *
 * -----------------------------------------------------------------------------
 *
 * @name        index.php
 *
 */
/******************************************************************************/

/**
 * Voodoo/init.php is the bootstrap of the framework
 */
$VoodooPHP_Dir = __DIR__;
require($VoodooPHP_Dir."/Voodoo/init.php");

    /**
     * Set the application name to use. By default it's Www
     * @type string
     */
    $appName = "www";

    /**
     * The URI
     * @type string
     */
    $uri = implode("/",Voodoo\Core\Http\Request::getUrlSegments());

    /**
     * Let's do it!
     */
    (new Voodoo\Core\Voodoo($appName, $uri))->doMagic();
    