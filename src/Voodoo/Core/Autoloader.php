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
 * @name        Core\Autoloader
 * @desc        The autoloader
 *              Autoloader per PSR-0
 *              https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
 *
 */

namespace Voodoo\Core;

class Autoloader
{
    private $libPath;

    /**
     * Register a library where classes reside
     * @param type $libPath
     *
     */
    public static function register($libPath)
    {
        new self($libPath);
    }

//------------------------------------------------------------------------------

    /**
     * Intiate the autoload
     * @param type $libPath
     */
    public function __construct($libPath)
    {
        $this->libPath = $libPath;
        spl_autoload_register(array($this,"_register"));
    }

    /**
     * Register
     * @param type $className
     * PSR-0 compliant autoloader
     */
    private function _register($className)
    {
        $className = ltrim($className, '\\');
        $fileName  = '';

        $namespace = '';

        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);

            $className = substr($className, $lastNsPos + 1);

            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }

        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        $file = $this->libPath.DIRECTORY_SEPARATOR.$fileName;

        if (file_exists($file)) {
            require_once($file);
        }
    }

}
