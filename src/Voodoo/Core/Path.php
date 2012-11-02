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
 * @name        Core\Path
 * @desc        Sets and holds your application's path
 *              Access path like Voodoo\Core\Path::VoodooApp() or Voodoo\Core\Path::Base()
 *
 */

namespace Voodoo\Core;

class Path
{
    private static $basePath = null;
    
    /**
     * Default paths
     * @var Array
     */
    private static $pathList = array(
        "Base"      => "",
        "Voodoo"    => "Voodoo",
        "Voodooist"  => "Voodoo/Voodooist",
        "VoodooApp" => "VoodooApp",
        "AppConfig" => "VoodooApp/Config",
        "Assets"    => "assets"
        
    );
    
    /**
     * Set the base path
     * @param string $basePath
     * @throws Exception
     */
    public static function setBase($basePath){
        
        if(self::$basePath == null){
            self::$basePath = realpath($basePath);
        } else {
            throw new Exception("Base Path is already set to: ".self::$basePath);
        }
    }
    
    /** 
     * To statically call path as method, ie Path::Voodoo(), Path::VoodooApp()
     * 
     * @param string $name
     * @param null $args
     * @return string
     */
    public static function __callStatic($name, $args) {
        if (isset(self::$pathList[$name])) {
            return self::$basePath."/".self::$pathList[$name];
        } else {
            return "";
        }
    }
}
