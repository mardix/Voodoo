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
    private $includePath;

    /**
     * Statically load register the class
     * 
     * @param string $includePath
     *
     */
    public static function register($includePath)
    {
        new self($includePath);
    }

    /**
     * Intiate the autoload
     * 
     * @param string $includePath
     */
    public function __construct($includePath)
    {
        $this->includePath = $includePath;
        
        spl_autoload_register(function($className){
            $className = ltrim($className, '\\');
            $fileName  = '';
            $namespace = '';
            if ($lastNsPos = strripos($className, '\\')) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
            $file = $this->includePath.DIRECTORY_SEPARATOR.$fileName;
            if (file_exists($file)) {
                require_once($file);
            }            
        });
    }
}
