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


// Loads the define file
include_once(__DIR__."/Config/define.php");


// Check PHP Version
if (version_compare(PHP_VERSION, VOODOO_PHP_VERSION, '<') )
   exit("Sorry, <b>".VOODOO_NAME."</b>  requires PHP ".VOODOO_PHP_VERSION." or greater!\n");

/*******************************************************************************/


/**
 * Voodoo Autoload
 * Voodoo requires classes to be namespaced per PSR-0 (https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
 * And classes can be placed anywhere in your application as long there are properly namespaced per PHP 5.3
 * ie: 
 *      \namespace\package\Class => ROOT_DIR/namespace/package/Class.php
 *      \namespace\package_name\Class_Name => ROOT_DIR/namespace/package_name/Class/Name.php
 * 
 * It's best not to put your own or third party classes in the Voodoo directory, as if you update to the latest version it will squash your files
 * Instead you can place them in Application/Lib or AddOn/. Or the worst case, at the root.
 */
include(VOODOO_PATH."/Core/Autoload.php");
    Core\Autoload::register(BASE_PATH);

/*******************************************************************************/
# Config

// Set global config
Core\Config::set(VOODOO_CONFIG_PATH."/Config.ini",true);

/**
 * Load the application config.ini file
 */
if(file_exists(APPLICATION_CONFIG_PATH."/Config.ini"))
    Core\Config::set(APPLICATION_CONFIG_PATH."/Config.ini",true);

/*******************************************************************************/


// Set the system timezone
date_default_timezone_set(Core\Config::get("System.Timezone"));


// Error reporting
error_reporting(Core\Config::get("System.ErrorReporting"));


/**
 * SETUP SOME VOODOO DIR & DOMAIN CONSTANT
 * Do not modify below. 
 * It creates relatives path for links from the root apps
 */

$domainName = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://").$_SERVER['HTTP_HOST'];

$VoodooRootDir = str_replace("\\","/",pathinfo($_SERVER["SCRIPT_NAME"],PATHINFO_DIRNAME));

    // The root dir, can be usedto access a file. 
    define("VOODOO_APP_ROOT_DIR",($VoodooRootDir == "/") ? "" : $VoodooRootDir);

    define("VOODOO_APP_HAS_HTACCESS",file_exists(BASE_PATH."/.htaccess") ? true : false);

    define("VOODOO_APP_SITE_DOMAIN",$domainName);
    
    // The site URL
    define("VOODOO_APP_SITE_URL",VOODOO_APP_SITE_DOMAIN.VOODOO_APP_ROOT_DIR);  
    
    // Url path, to prefix the url so they dont break in case it doesnt have htaccess
    define("VOODOO_APP_ROOT_URL",VOODOO_APP_SITE_URL.(VOODOO_APP_HAS_HTACCESS ? "" : "/?" ));
