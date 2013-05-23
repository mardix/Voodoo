<?php
/**
 * App/bootstrap.php
 * To load the Voodoo environment and Voodoo App
 * Also load the config dir for the environment: ie: dev, production, staging
 * @since Jan 1 2013
 */

use Voodoo\Core\Env as Env,
    Voodoo\Core\Config as Config;

/*******************************************************************************/

ini_set('display_errors', '0');


/**
 * Autoload composer
 */
include_once(ROOT_DIR."/vendor/autoload.php");

// Autoload other the root for App && Lib
Voodoo\Core\Autoloader::register(ROOT_DIR);

// Set the ENV path
Env::setAppPath(ROOT_DIR);


// Set the system timezone
date_default_timezone_set(Config::System()->get("timezone"));

// Error Reporting
error_reporting(Config::System()->get("errorReporting"));
