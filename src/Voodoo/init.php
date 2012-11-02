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
 * @desc    To initialize VoodooPHP
 */

/*******************************************************************************/

use Voodoo\Core;

ini_set('memory_limit', -1);

$BASE_PATH = dirname(__DIR__);

/*******************************************************************************/

/**
 * Voodoo Autoloader
 * Voodoo requires classes to be namespaced per PSR-0 (https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
 * And classes can be placed anywhere in your application as long there are properly namespaced per PHP 5.3
 * ie:
 *      \namespace\package\Class => ROOT_DIR/namespace/package/Class.php
 *      \namespace\package_name\Class_Name => ROOT_DIR/namespace/package_name/Class/Name.php
 *
 * It's best not to put your own or third party classes in the Voodoo directory, as if you update to the latest version it will squash your files
 * Instead you can place them in Application/Lib or AddOn/. Or the worst case, at the root.
 */
include(__DIR__."/Core/Autoloader.php");
Core\Autoloader::register($BASE_PATH);

Core\Path::setBase($BASE_PATH);

/*******************************************************************************/
/**
 * Load the basic config and assign it to the 'Application' namespace.
 * So it can be merged with VoodooApp\Config\Application.ini
 * and be used as Core\Config::Application()
 */
$Config = new Core\Config("Application");
$Config->loadFile(__DIR__."/Config.ini");

// Check HTACCESS existence. Can be overriden in the Application ini
// [env] htaccessEnabled = true
$htaccessEnabled = file_exists(dirname($_SERVER["SCRIPT_FILENAME"])."/.htaccess") ? true : false;
$Config->set(array("htaccessEnabled" => $htaccessEnabled), "env");

/*******************************************************************************/

// Set the system timezone
date_default_timezone_set(Core\Config::Application()->get("system.timezone"));

// Error reporting
error_reporting(Core\Config::Application()->get("system.errorReporting"));
