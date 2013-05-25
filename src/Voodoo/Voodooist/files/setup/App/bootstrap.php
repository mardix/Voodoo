<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/VoodooPHP/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2013 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * @name        bootstrap
 * @desc        Setup the your Application and the Voodoo environment
 *
 */

use Voodoo\Core\Env as Env,
    Voodoo\Core\Config as Config;

ini_set('display_errors', '0');

// @var string - The root directory which contains /App
define ("APP_ROOT_DIR", dirname(__DIR__));

// @var bool - To indicate the bootstrap to load Voodoo with compose
define ("LOAD_VOODOO_WITH_COMPOSER", false);

//  @var string - The directory of the composer vendor
define ("COMPOSER_VENDOR_DIR", APP_ROOT_DIR."/vendor");

// @var string - The root directory which contains /Voodoo
define ("VOODOO_ROOT_DIR", APP_ROOT_DIR);

/**
 * @var string
 * Leave blank if your config files are at the based of /App/Config
 * If you create multiple environment, ie: /App/Config/production, /App/Config/stage, /App/Config/dev
 * Set the name of the subdirectory, ie: 'production'
 */
define ("APP_CONFIG_DIRNAME", "");


/**
 * To load Voodoo with composer or as self
 */
if (LOAD_VOODOO_WITH_COMPOSER) {
    include_once(COMPOSER_VENDOR_DIR."/autoload.php");    
} else {
    include_once(VOODOO_ROOT_DIR."/Voodoo/autoload.php");
}

// Autoload classes at the root
Voodoo\Core\Autoloader::register(APP_ROOT_DIR);

// Set the ENV path
Env::setAppRootDir(APP_ROOT_DIR);

// Set the config name. A sub directory name under /App/Conf/$subdirectory
Env::setConfigPath(APP_CONFIG_DIRNAME); 

// Set the system timezone
date_default_timezone_set(Config::System()->get("timezone"));

// Error Reporting
error_reporting(Config::System()->get("errorReporting"));
