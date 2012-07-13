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
 * @name        Mapper
 * @desc        An abstract class for data mapper, so all data storage can have the interface
 *               
 * 
 */


namespace Voodoo\DB;

use Voodoo\Core,
    ArrayIterator,
    PDO,
    Mongo;

abstract class Mapper implements \IteratorAggregate{

    // To insert data
    public abstract function insert(Array $Data);

    // To update & save data
    public abstract function update(Array $Data);

    // To delete
    public abstract function delete();
    
    // Find a single entry by primary key
    public abstract function findPK($key);

    // Return the primary key
    public abstract function getPK();
    
    // return the key's value
    public abstract function get($key);

    // Save the data
    public abstract function save();
    
    /**
     * @return Array 
     */
    protected abstract function getResults();
    

/*******************************************************************************/
  
   const ERROR_ROW_OBJ= "Object Row";
   
   const ERROR_NOT_ROW_OBJ = "Not Object Row";
  
    /**
    * Hold all db instances
    * @var type 
    */
    private static $Instances = array();
  
    /**
     * The Database alias
     * @var type 
     */
    protected $DBAlias = "";
    
    /**
    * The primary ke name
    * @var type 
    */
    protected $PrimaryKeyName = "%s_id";
  
    /**
    * The Foreign ke name
    * @var type 
    */
    protected $ForeignKeyName = "%s_id";
    
    /**
    * Hold the row results
    * @var Array 
    */    
    protected $Data = array();
    
    /**
    * When the entry is a row and not a set of results. 
    * @var type 
    */
    protected $isSingle = false;
    
    protected  $Model = null;
  
    
    /**
     * Create connection to DB based on it's alias
     * @return DataBase Connection Instance
     * @throws Exception 
     */
    
    protected function connect(){

        list($dbAlias,$tableCollName) = explode("\\",(str_replace("Application\\Model\\","",get_called_class())),2);

        $this->setDBAlias($dbAlias);
        
        $dbAlias = $this->DBAlias;
        
        if(!isset(self::$Instances[$dbAlias])){
            
            $db = Core\INI::DB()->get($dbAlias);
            
            if(!is_array($db))
                throw new Core\Exception("Database Alias: {$dbAlias} config doesn't exist for Model: ".get_called_class()."");
            
            if(preg_match("/mysql|sqlite|mongodb/i",$db["Type"])){

                switch(strtolower($db["Type"])){

                    /**
                     * MySQL & SQLite 
                     */
                    case "mysql":
                    case "sqlite":
                       
                       // SQLite
                       if(strtolower($db["Type"]) == "sqlite")
                           $PDO = new PDO("sqlite:".APPLICATION_DB_PATH."/{$db["DBName"]}.{$db["DBFileExt"]}");
                           
                       //MySQL    
                       else    
                           $PDO = new PDO("mysql:host={$db["Host"]};dbname={$db["DBName"]}",$db["Username"],$db["Password"]);
                       
                       $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                       self::$Instances[$dbAlias] = new NotORM\NotORM($PDO);
                        
     
                    break;

                
                    /**
                     * MongoDB 
                     */
                    case "mongodb":
                       
                        $Options = array();

                        if($db["Username"])
                            $Options["username"] = $db["Username"];
                        if($db["Password"])
                            $Options["password"] = $db["Password"];
                        if($db["ReplicaSet"])
                            $Options["replicaSet"] = true;

                        $Mongo = new Mongo($db["Host"],$Options);

                        if($db["SlaveOK"])
                            $Mongo->slaveOkay(true);

                        self::$Instances[$dbAlias] = $Mongo->selectDB($db["DBName"]);                        

                    break;
                    
                    
                    /**
                     * REDIS 
                     */
                    case "redis":
                        
                        $host = $db["Host"].($db["Port"] ? ":{$db["Port"]}" : "");
  
                        $Redis = new Redisent\Redis($host);
                       
                        $Redis->setDB($db["DBNumber"]);
                        
                        self::$Instances[$dbAlias] = $Redis;
                        
                    break;

                
                }

            }
            else
                throw new Core\Exception("Invalid Database type for Alias: '{$dbAlias}' in ".get_called_class().". Type: {$db["Type"]} was provided. Must be MySQL, SQLite or MongoDB ");
        }
        
        return
            self::$Instances[$dbAlias];
    }
    
    /**
     * Set the DB Alias
     * @param type $dbAlias
     * @return \Core\DataMapper\absMapper 
     */
    private function setDBAlias($dbAlias){
        $this->DBAlias = Core\Helpers::camelize($dbAlias,true);
        return
            $this;
    }
    
    
    /**
     * Create the object statically
     * @return static 
     */
    public static function Create(){
        return
            new static;
    }
    
    /**
     * This method allow the iteration
     * @return \ArrayIterator 
     */
    public function getIterator(){
       if($this->isSingle)
            throw new Core\Exception("This is a Row Object. Can't iterate ");
       
        $It = new ArrayIterator();
        
        foreach($this->getResults() as $rData)
            $It->append($this->map($rData));
        
        
        return
            $It;
    }

    
    /**
     * To clone the row results
     * @param Array $Row
     * @return type 
     */
    protected function map($Data){
        
        $this->Data = $Data;
        
        $this->isSingle = true;
        
        $row = clone $this;

        return
            $row;
            
    }

    
    public function __clone(){
        
    }

}
