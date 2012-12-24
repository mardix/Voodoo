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
 * @name    autoload
 * @desc    Setup the voodoo autoload
 */

use Voodoo\Core;
CONST REQUIRE_PHP_VERSION = "5.4";

// Chek PHP Version
if (version_compare(PHP_VERSION, REQUIRE_PHP_VERSION, '<') ) {
    echo ("VoodooPHP requires PHP ".REQUIRE_PHP_VERSION." or greater");
    exit;
}

// Set the Voodoo Autoload
require_once __DIR__."/Core/Autoloader.php";
Core\Autoloader::register(dirname(__DIR__));
