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
 * @name        Voodooist/setup
 * @desc        A setup file that create your Voodoo environment, include your 
 *              App directory, your classes etc.
 *              /your-path/App/_config/app.json will be created. It a JSON file
 *              that contains your MVC stucture. setup.php will read it and create the files
 *              and directory if not exist.
 *              
 * @run         To execute this file, run the sode below in your command line 
 *              cd /PATH/Voodoo/Voodooist 
 *              php -f ./setup.php
 *
 */

use Voodoo\Core,
    Voodoo\Voodooist;

require_once dirname(__DIR__)."/autoload.php";

date_default_timezone_set("America/New_York");

/**
 * Edit your directory below
 */

$baseDir = dirname(dirname(__DIR__));
$options = [
    // The root dir where index.php will reside
    "FrontController" => $baseDir,
    // The root dir where to place the Voodoo App directory
    "App" => $baseDir,
    // The base dir of your files
    "BaseDir" => $baseDir,
    // The path of your config files. By default it's under App/_config
    "Config" => $baseDir."/App/_config",
    // The root dir where the assets (/assets/js|css|images etc...) exists
    "PublicAssets" => $baseDir
];

    try {
        Voodooist\Voodooist::create($options, true);
    } catch (\Exception $e) {
        echo "EXCEPTION: ".$e->getMessage();
    }
