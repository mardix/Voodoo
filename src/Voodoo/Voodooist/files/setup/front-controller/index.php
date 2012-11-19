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
     * Set the default app to access. By default it's Www
     * @type string
     */
    $application = "www";

    /**
     * The URI
     * @type string
     */
    $segments = implode("/",Voodoo\Core\Http\Request::getUrlSegments());

    /**
     * Let's do it!
     */
    (new Voodoo\Core\Voodoo($application, $segments))->doMagic();
    