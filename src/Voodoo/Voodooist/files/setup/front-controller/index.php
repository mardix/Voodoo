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

require_once __DIR__."/App/bootstrap.php";

    /**
     * Set the application name to use. By default it's Www
     * @type string
     */
    $appName = "www";

    (new Voodoo\Core\Application(APP_ROOT_DIR, $appName))->doVoodoo();
