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
 * @name    init
 * @desc    include this file in the header to setup the environment
 */

/*******************************************************************************/

use Voodoo\Core;

ini_set('memory_limit', -1);

$BASE_PATH = dirname(__DIR__);

$REQUIRE_PHP_VERSION = "5.4";

/**
 *  Check PHP Version
 */
if (version_compare(PHP_VERSION, $REQUIRE_PHP_VERSION, '<') ) {
    echo ("VoodooPHP requires PHP ".$REQUIRE_PHP_VERSION." or greater");
    exit;
}

/*******************************************************************************/

/**
 * Autoloader
 * We'll set the autoloader at the base
 */
include(__DIR__."/Core/Autoloader.php");
Core\Autoloader::register($BASE_PATH);

// Set the base path of the application
Core\Path::setBase($BASE_PATH);

/*******************************************************************************/
/**
 * Load the basic config and assign it to the 'Application' namespace.
 * So it can be merged with VoodooApp\Config\Application.ini
 * and be used as Core\Config::Application()
 */
(new Core\Config("System"))->loadFile(__DIR__."/System.ini");

/*******************************************************************************/

// Set the system timezone
date_default_timezone_set(Core\Config::System()->get("timezone"));

// Error reporting
error_reporting(Core\Config::System()->get("errorReporting"));
