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
 * @name        Ini
 * @since       Aug 8, 2011
 * @desc        Access any INI file in the Application/Config directory
 * 
 */

namespace Voodoo\Core;

class INI{
    
    /**
     * Hold topics list that has been loaded
     * @var Array
     */
    protected static $INI = array();
    
    public $iniName;
    
    
   /**
    * Load via static
    * @param type $iniName
    * @return self 
    */
   public static function File($iniName){
      return 
        new self($iniName);
   }
    
   
   /**
    * Constructor
    * @param string $iniName - The filename without extension
    * @param bool $require - To make sure the ini file exists
    * @param mixed - true | false. But if RESET it will unset the saved data
    */
   public function __construct($iniName,$require = true,$absolutePath = false){
       
        $this->iniName = $iniName;

        /**
         * set absolute path oto RESET, will unset the saved data 
         */
        if($require == false && $absolutePath == "RESET" && isset(self::$INI[$this->iniName]))
            unset(self::$INI[$this->iniName]);
            
            
        if(!isset(self::$INI[$this->iniName])){
            
            if($absolutePath)
                $f = $iniName;
            
            else
                $f = APPLICATION_CONFIG_PATH."/{$this->iniName}.ini";
            
            if(file_exists($f))
                self::$INI[$this->iniName] = parse_ini_file($f,true);
            
            else{
                if($require)
                    throw new Exception("INI File '{$f}' doesn't exist");
                else
                   self::$INI[$this->iniName] = array(); 
            }
        
        }

   }
   
   
   /**
    * Return the INI array
    * @return Array 
    */
   public function toArray(){

      return 
            self::$INI[$this->iniName];
   }
   
   
    /**
     * Access value of an array with dot notation
     * @param type $dotNotation - the key. ie: Key.Field1
     * @param type $emptyValue - Use this value if empty
     * @return Mix
     * 
     * ie
     *  self::getValue("QA.UpVoteQuestion"); Will return the value of [QA][UpVoteQuestion]
     */
    public function get($dotNotation="",$emptyValue = null){
        
        return Helpers::getArrayDotNotationValue($this->toArray(),$dotNotation,$emptyValue);

    }
    
    /**
     * Set data in the loaded
     * @param array $cnf
     * @param type $keyName 
     */
    public function set(Array $cnf,$keyName=""){

        if($keyName)
            self::$INI[$this->iniName][$keyName] = Helpers::arrayExtend(self::$INI[$this->iniName][$keyName],$cnf);
        
        else
            self::$INI[$this->iniName] = Helpers::arrayExtend(self::$INI[$this->iniName],$cnf);
        
        return
            $this;
    }
    
    
    /**
     * Save the ini file. 
     * ATTENTION: It will remove all comments added. At this time, it's limited to 2 depth
     * @param type $fileName - The filename without the extension
     * @return $this
     */
    public function save($fileName=""){
        
        $fileName = $fileName ?: $this->iniName;
        
        $data = self::arrayToINI(self::$INI[$this->iniName]);
        
        file_put_contents(APPLICATION_CONFIG_PATH."/{$fileName}.ini",$data);
        
        return
            $this;
    }
    
    /**
     * Statically load any INI file. IE \Core\INI::Settings()->toArray()
     * @param type $name
     * @param type $args 
     * @return  INI
     * @since Jan 17 2012
     * Set the 1 agrs to false to not show error if class doesnt exist
     *  Core\INI::Settings(false)->toArray()
     */
    public static function __callStatic($name,$args){
        return
            new self($name,$args[0],$args[1]);
    }
    
    
    /**
     * To convert an array into a properly formatted INI file
     * @param array $iniArray
     * @param int $indent
     * @return String. A string to be saved as INI file
     */
    public static function arrayToINI(Array $iniArray, $indent = 0){

        foreach ($iniArray as $k => $v){
            if (is_array($v)){
                $ini .= str_repeat(" ", $indent * 5);
                $ini .= "[$k] \r\n";
                $ini .= self::arrayToINI($v, $indent + 1);
            }
            else{
                $ini .= str_repeat(" ", $indent * 5);
                $v = (is_string($v)) ? "\"$v\"" : $v;
                $ini .= "$k = $v \r\n";
            }
        }
        return
            $ini;
    }
 
}

