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
 * @name        App/voodooist.php
 * @desc        This file run Voodooist to setup file based on your app.json file
 *              
 * @run         To execute this file, run the code below in your command line 
 *              cd /App
 *              php -f ./voodooist.php
 *
 */

require_once(__DIR__."/bootstrap.php");

use Voodoo\Voodooist\Voodooist;

    try {
        Voodooist::create(APP_ROOT_DIR, APP_CONFIG_DIRNAME);
    } catch (\Exception $e) {
        echo "EXCEPTION: ".$e->getMessage();
    }