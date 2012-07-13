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
 * @name        Core\Config
 * @since       Mar 26, 2011
 * @desc        Holds the application configuration
 *              
 * @example     Core\Config("Location.City")
 * 
 */


namespace Voodoo\Core;


class Config{
    
    private static $cnf = array();
    
    /**
     * To add data in the global configuration
     * @param mixed $cnf - By default it must be ARRAY. If its a string, it myst be a php INI file, and $isINIFile must be set to true
     * @param bool $isINIFile - if true $cnf must be the filepath to the INI file.
     * Example
     *      Core\Config::set(array(
     *                         "ActivePDO"=>array("user"=>"username",pass=>"password"),
     *                         "Mail"=>array("user"=>"username",pass=>"password")
     *                      ))
     */
    public static function set($cnf,$isINIFile=false,$keyName=""){
        
        if($isINIFile){
            if(!file_exists($cnf))
                throw new Exception("INI file doesn't '{$cnf}' doesn't exit in ".__METHOD__);
            else
                $cnf = parse_ini_file($cnf,true);
        }

       if(!is_array($cnf))
            throw new Exception("The data type for the config must be an array in ".__METHOD__);
        
        if($keyName)
            self::$cnf[$keyName] = Helpers::arrayExtend(self::$cnf[$keyName],$cnf);
        else
            self::$cnf = Helpers::arrayExtend(self::$cnf,$cnf);
        
        
        
    }

    
    /**
     * Retrieve a config set
     * @param type $dotNotation - The dot notation key of the data to retrieve
     * @param string $emptyValue - A value if the key is empty
     * @return mixed
     * 
     * Example
     *      Core\Config::get("ActivePDO.pass") // -> return the password
     *      Core\Config::get("Mail") // return the mail array with all data
     */
    public static function get($dotNotation="",$emptyValue = false){
        
        return Helpers::getArrayDotNotationValue(self::$cnf,$dotNotation,$emptyValue);

    }
 
}


