<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/mardix/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2012 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * @name        Ini
 * @since       Aug 8, 2011
 * @desc        Access any INI file in the App/Conf directory
 *
 */

namespace Voodoo\Core;

class Config
{
    const EXT = ".conf.php";
    
    /**
     * Hold topics list that has been loaded
     * @var Array
     */
    private static $Config = [];

    private $namespace = "";
   
    /**
     * Constructor
     * @param string $namespace - A unique name that will hold data for each 
     * set of config
     */
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
        
        if (!$this->namespaceExists()) {
            self::$Config[$this->namespace] = [
                "__called" => false,
                "data" => []
            ];
        }
    }
   
    /**
     * Check if a namespace exists
     * @return bool
     */
    public function namespaceExists()
    {
        return isset(self::$Config[$this->namespace]);
    }
    
    /**
     * Return the namespace data
     * @return Array
     */
    public function getNamespace()
    {
        return self::$Config[$this->namespace];
    }
    
    /**
     * To load an .ini file
     * 
     * @param type $file
     * @param type $keyname 
     */
    public function loadFile($file, $keyname = ""){
        if(! file_exists($file)) {
            throw new Exception("Config file '{$file}' doesn't exist or is not readable");
        } else {
            $cnf = parse_ini_file($file, true);
            $this->set($cnf, $keyname);  
            return $this;            
        }
    }  
    
    
    
   /**
    * Return the INI array
    * @return Array
    */
   public function toArray()
   {
      return $this->getNamespace()["data"];
   }

    /**
     * Access value of an array with dot notation
     * @param  type $dotNotation - the key. ie: Key.Field1
     * @param  type $emptyValue  - Use this value if empty
     * @return Mix
     *
     * ie
     *  self::get("QA.UpVoteQuestion"); Will return the value of [QA][UpVoteQuestion]
     */
    public function get($dotNotation="",$emptyValue = null)
    {
        return Helpers::getArrayDotNotationValue($this->toArray(), $dotNotation, $emptyValue);
    }

    /**
     * Set data in the loaded
     * @param array $data
     * @param type  $keyName
     */
    public function set(Array $data, $keyName="")
    {
        if($keyName){
            if (!isset($this->getNamespace()["data"][$keyName])){
                self::$Config[$this->namespace]["data"][$keyName] = [];
            }
            self::$Config[$this->namespace]["data"][$keyName] = Helpers::arrayExtend($this->getNamespace()["data"][$keyName], $data);
        } else {
            self::$Config[$this->namespace]["data"] = Helpers::arrayExtend($this->getNamespace()["data"], $data);
        }
        return $this;
    }

    /**
     * Save the ini file.
     * ATTENTION: It will remove all comments added. At this time, it's limited to 2 depth
     * @param type $fileName - The filename without the extension
     * @return $this
     */
    public function save($fileName="")
    {
        $fileName = $fileName ?: $this->namespace;
        $data = self::arrayToINI($this->toArray());
        file_put_contents(Env::getConfigDir()."/{$fileName}".self::EXT, $data);
        return $this;
    }

    /**
     * Statically load any INI file. IE \Core\INI::Settings()->toArray()
     * @param  type $name
     * @param  type $args
     * @return Config
     */
    public static function __callStatic($name, $args)
    {
        $ini = new self($name);
        
        if($ini->getNamespace()["__called"]) {
            return $ini;
        } else {
            $file = Env::getConfigDir()."/{$name}".self::EXT;
            if(file_exists($file)) {
                $ini->loadFile($file);
                self::$Config[$ini->namespace]["__called"] = true;
            } else if(!$ini->namespaceExists()) {
                throw new Exception("Config File '{$file}' doesn't exist");
            }
            return $ini;            
       }
    }

    /**
     * To convert an array into a properly formatted INI file
     * @param  array   $iniArray
     * @param  int     $indent
     * @return String. A string to be saved as INI file
     */
    public static function arrayToINI(Array $iniArray, $indent = 0)
    {
        foreach ($iniArray as $k => $v) {
            if (is_array($v)) {
                $ini .= str_repeat(" ", $indent * 5);
                $ini .= "[$k] \r\n";
                $ini .= self::arrayToINI($v, $indent + 1);
            } else {
                $ini .= str_repeat(" ", $indent * 5);
                $v = (is_string($v)) ? "\"$v\"" : $v;
                $ini .= "$k = $v \r\n";
            }
        }
        return $ini;
    }
}
