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
 * @name        MongoDB
 * @desc        A simple MongoDB data mapper to map results to object
 */


namespace Voodoo\DB;

use Voodoo\Core,
    ArrayIterator,
    Mongo, 
    MongoId,
    MongoDate;


abstract class MongoDB extends Mapper{

    /**
     * Hold the where condition
     * @var Array
     */
    private $where = array();

    /**
     * Hold the query to find documents
     * @var type 
     */
    private $fQuery = array();

    /**
     * Hold the modifier to update document
     * @var Array 
     */
    private $fModifier = array();

    /**
     * Hold options that will be added ind find or update
     * @var Array
     */
    private $options = array();

    /**
     * Hold the fields to return
     * @var type 
     */
    private $fields = array();
  
  /**
   * The table name
   * @var type 
   */
  protected $CollectionName = null;
  
  /**
   * The primary ke name
   * @var type 
   */
  protected $PrimaryKeyName = "_id";

  
  /**
   * Holds the MongoDB
   * @var type 
   */
  private $MongoDB = null;

/*******************************************************************************/
    
    
    /**
     * Constructor
     * @throws Core\Exception 
     */
    public function __construct(){

        if(!$this->CollectionName)
            throw new Core\Exception("CollectionName is null in ".__CLASS__);

        if(!$this->PrimaryKeyName)
            throw new Core\Exception("PrimaryKeyName is null in ".__CLASS__);

        $this->MongoDB = parent::connect();

        $this->Model = $this->MongoDB->selectCollection($this->CollectionName);
    }  
    
    
    /**
     * Return the MongoDB instance
     * @return \MongoDB 
     */
    public function DB(){
        return $this->MongoDB;
    }
    
    /**
     * Return the table instance
     * @return type 
     */
    public function getModel(){
        return $this->Model;
    }  
    
    
    /**
     * return the table name
     * @return string 
     */
    public function getModelName(){
        return $this->CollectionName;
    }
         
    /**
     * return the primary key name
     * @return string
     */
    public function getPrimaryKeyName(){
        return $this->PrimaryKeyName; 
    }    
/*******************************************************************************/
/*******************************************************************************/

    
    /**
     * To insert new data
     * @param array $Data
     * @return type 
     */
    public function insert(Array $Data){
       $this->getModel()->insert($Data);
       return
            $this->map($Data);
    }
    
    /**
     * Do a batch insert to insert multiple entries at once
     * @param array $Data
     * @return \Voodoo\DB\MongoDB 
     */
    public function batchInsert(Array $Data){
        $this->getModel()->batchInsert($Data);
        return
            $this;
    }
    
    

    /**
     * Update documents. To do an upsert, $this->options(array("upsert"=>true))
     * @param array $Data
     * @return \Voodoo\DB\MongoDB
     * @throws Core\Exception 
     */
    public function update(Array $Data) {
        
        if(!count($this->where))
            throw new Core\Exception("Can't update documents. \$where criteria is not provided");
        
        $this->fModifier = $Data;
        
        $this->save();

        // Reset if not single
        if(!$this->isSingle)
            $this->reset();
        
        return
            $this;
    }
    
    
    /**
     * Delete documents
     * @return null
     * @throws Core\Exception 
     */
    public function delete(){
        
        if(!count($this->where))
            throw new Core\Exception("Can't delete documents. \$where criteria is not provided");        

        $this->getModel()->remove($this->where,$this->options);
        
        $this->reset();
        
        return
            null;
    }
    
    /**
     * Set a where clause to perform global CRUD
     * @param array $where
     * @return MongoDB
     */
    public function where(Array $where = array()){
       $this->where = $where;
       return
            $this;
    }
    
    /**
     * Set options
     * @param array $options
     * @return \Voodoo\DB\MongoDB 
     */
    public function options(Array $options = array()){
        $this->options = $options;
        return
            $this;
    }
    
    /**
     * 
     * @param array $fields
     * @return \Voodoo\DB\MongoDB 
     */
    public function fields(Array $fields= array()){
        $this->fields = $fields;
        return
            $this;
    }
    
    public function reset(){
        $this->where(array());
        $this->options(array());
        return
            $this;
    }
/*******************************************************************************/    
/*******************************************************************************/

    /**
     * Find a document by Primary Key
     * @param \MongoId $_id
     * @return type 
     */
    public function findPK($_id){
       if($this->isSingle)
            throw new Core\Exception("This is a Document Object. Can't call method: findPK() ");
       
        if($this->PrimaryKeyName == "_id")
            $_id = new MongoId($_id);
        
       return
            $this->findOne(array($this->PrimaryKeyName=>$_id));
        
    }
    
    /**
     * Find one document based on the criteria
     * @param array $Criteria
     * @param array $fields
     * @return type 
     */
    public function findOne(Array $where = array(),Array $fields = array()){
        
       if($this->isSingle)
            throw new Core\Exception("This is a Document Object. Can't call method: findOne() ");  
       
       $this->where($where)->fields($fields);
       
       if(!count($this->where))
            throw new Core\Exception("Can't findOne document. \$where criteria is not provided");  
       
       $R = $this->getModel()
                 ->findOne($this->where,$this->fields);

       $this->reset();
       
        return
            ($R) ? $this->map($R) : false;
    }
    

  /**
   * To query
   * @param type $fields
   * @return \Core\DataMapper\Collection 
   */
  public function find($fields=array()){
       if($this->isSingle)
            throw new Core\Exception("This is a Document Object. Can't call method: find() ");
            
      $this->reset()->fields($fields);

      return
        $this;
  }
  
  /**
   * Find all entries
   * @return \Voodoo\DB\MongoDB 
   */
  public function findAll($fields = array()){
      $this->reset()->where(array())->fields($fields);
      return
            $this;
  }
  
  /**
   * Return results found
   * @return type
   * @throws Core\Exception 
   */
  public function count(){
       if($this->isSingle)
            throw new Core\Exception("This is a Document Object. Can't call method: count() ");      
      return
            $this->getResults()->count();
  }
  
/*******************************************************************************/
// To build queries  
  /**
   * 
   * fields(), sort() or orderBy(), limit(), skip(), batchSize(), hint(), slaveOkay(), snapshot(), timeout(),
   * @param type $method
   * @param type $args
   * @return \Core\DataMapper\Collection
   * @throws Core\Exception 
   */
  final public function __call($method,$args){

         $method = strtolower($method);
         
         /**
          * Magic methods to update  
          */
         $modifierMethods = array(
             "inc", // increment +1
             "dec",// decrement -1
             "set",// set data
             "remove", // unset data
             "push", // To push data in array
             "pushall", // to push multiple data in array
             "pull",// to pull data from array
             "pullall", // Pull list of data from array
             "addtoset", // Add data once in array
             "pop", // Remove the last item in an array
             "shift", // remove the first item in array
             "rename" // to rename fieldA to fieldB
           );
                 
        /**
         * Magic methods to query 
         */
        $queryMethods = array(
            "sort",
            "orderBy",
            "limit",
            "skip",
            "batchSize",
            "hint",
            "slaveOkay",
            "snapshot",
            "timeout"
         );
        
        
           // Update
           if(in_array($method,$modifierMethods)){
                    $cmd = $method;
                    $data = $args[0];
                    if(is_string($args[0])){
                        $val = isset($args[1]) ? $args[1] : 1;
                        $data = array($args[0]=>$val) ;
                    }

                    switch($method){
                        default:
                            // Change some command into their counter part, or properly rename them to mongo
                            $cmd = str_replace(array("pullall","pushall","remove","addtoset"),array("pullAll","pushAll","unset","addToSet"),$cmd);
                        break;
                        case "inc":
                        case "dec":
                            $cmd = "inc";
                            $data = array($args[0]=>($method == "dec" ? -1 : 1) * (float)(isset($args[1])?$args[1]:1));
                        break;    
                        // To do a proper pull, we need to remove (unset) the data, then pull the field
                        case "pull":
                            $this->remove($args[0]);
                        break;
                        case "shift":
                            $cmd = "pop";
                            $data = array($args[0]=>-1);
                        break;  
                    }  
                    $this->fModifier = array_merge_recursive($this->fModifier,array('$'.$cmd=>$data));  

               
           }
           
           // Query
            else if(in_array($method,$queryMethods)){
                switch($method){
                    case "orderBy":
                        $method = "sort";
                    break;
                }            
                $this->fQuery[$method] = $args;            
            } 

      
      return
        $this;
  }
  

    /**
     * Build the cursor to find data
     * Create a cursor with the data of the query.
     * @return \MongoCursor A cursor with the data of the query.
     */
    protected function getResults(){
      
       if($this->isSingle)
            throw new Core\Exception("This is a Document Object. Can't call method getResults() ");
            
        $where = is_array($this->where) ? $this->where : array();

        $Cursor = $this->getModel()->find($where,$this->fields);

        foreach($this->fQuery as $fn=>$val){
            call_user_func_array(array($Cursor,$fn),$val);
        }

        return $Cursor;
    }  
  
    
/*******************************************************************************/
/*******************************************************************************/
/**
 * Methods for single document
 */
/*******************************************************************************/

    /**
     * Return the document primary key
     * @return type
     * @throws type 
     */
    public function getPK(){
        if(!$this->isSingle)
            throw Core\Exception("Not a Document Object. Can't getPK()"); 
        return
            $this->get($this->PrimaryKeyName);
    }
    
        

    public function get($key=""){
        if(!$this->isSingle)
            throw new Core\Exception(self::ERROR_NOT_ROW_OBJ." Can't call method: ".__METHOD__."({$key})");
        
        $key = (is_array($key)) ? key(Core\Helpers::toDotNotation($key)) : $key;
        
        $r = Core\Helpers::getArrayDotNotationValue($this->Data,$key,null);

        return
            ($r) ? $r : Core\Helpers::getArrayDotNotationValue($this->getModel()->findOne($this->where,$key)?:array(),$key,null);
    }    
    
    /**
     * Getter
     * @param type $key, * can be in dot notation
     * @return mixed
     */
    public function __get($key){
        return $this->get($key);
    }

    
    /**
     * Return total element in a set 
     * @param type $key
     * @return int
     */
    public function countSet($key){
        if(!$this->isSingle)
            throw Core\Exception("Not a Document Object. Can't countSet()");
        
        $set = $this->get($key);

        return (is_array($set)) ? count($set) : 0;        
    }
    
    /**
     * Check if a value exists in an array set
     * @param type $key
     * @param type $value 
     * @return bool
     */
    public function inSet($key,$value){
        if(!$this->isSingle)
            throw Core\Exception("Not a Document Object. Can't check if value is inSet()");
        
        $set = $this->get($key);
        
        return (!is_array($set)) ? false : in_array($value,$set);
    }
    

    /**
     * Return the document size in byte
     * @param string - th size format: b,k,m,g = byte,kilobyte,megabyte,gigabyte
     * @return type 
     */
    public function getDocumentSize(){
        
        if(!$this->isSingle)
            throw Core\Exception("Not a Document Object. Can't getDocumentSize");
        
        $collName = $this->getModelName();

        $jsonCriteria = json_encode($this->where());
        
       // $jsonCriteria = array();

        $code = "function(){
                   return Object.bsonsize(db.{$collName}.findOne({$jsonCriteria}))
                }";

        $resp = $this->DB()->execute($code);
        return $resp["retval"];        
        
    } 
    

    /**
     * To save the model that was created 
     */
    public function save(){
        
        if(count($this->fModifier) && count($this->where)){
            
            /**
             * Some operations need to be executed before some others 
             */
            $cmds = array('$unset','$pull','$pullAll','$push','$pushAll','$addToSet');
            foreach($cmds as $cmd){
                if(isset($this->fModifier[$cmd])){
                    $this->getModel()->update($this->where,array($cmd=>$this->fModifier[$cmd]),$this->options);
                    unset($this->fModifier[$cmd]);                    
                }
            }
            // Do the rest
            if(count($this->fModifier))
                $this->getModel()->update($this->where,$this->fModifier,$this->options);

            // reload data in the object for retrieval
            if($this->isSingle){
                $this->Data = $this->getModel()->findOne($this->where,$this->fields);
            }
            
        }
        
        return
            $this;
    }
    



    /**
     * To see 
     * @return type 
     */
    public function debug(){
        return 
            array(
                "WHERE"=>$this->where,
                "MODEL"=>$this->fModifier,
                "QUERY"=>$this->fQuery
            );
    }
    

    /**
     * To clone the row results
     * @param 
     * @return type 
     */
    protected function map(Array $Doc){
        
        if(isset($Doc[$this->PrimaryKeyName]))
           $this->where(array($this->PrimaryKeyName=>$Doc[$this->PrimaryKeyName]));

        else
            throw new Core\Exception("PrimaryKey was not provided!");
        
        $doc = parent::map($Doc);

        $this->reset();
        
        return
            $doc;
            
    }
    

    
    /**
     * Return the document id or the collection name
     * @return string 
     */
    public function __toString(){
        return
            ($this->isSingle) ? strval($this->getPK()) : $this->getModelName();
    }

    
    
    /**
     * Return a mongo timestamp
     * @param type $strtotime
     * @return \MongoDate 
     */          
    public static function TimeStamp($strtotime=null){
        
        $t = time();
        
        if($strtotime!=null){
            if(is_string($strtotime))
                $t = strtotime($strtotime);
            else if(is_int($strtotime))
                $t = $strtotime;
        }

         return
            new MongoDate($t);
    }
    
    
    /**
     * To insert a new entry by including a unique id: ID.
     * Also it will be included SAFE and FSYNC for durability
     * @param array $Data
     * @return $Data with _id and uid
     * @note: This method will not work properly with Sharding
     */
    public function insertWithUniqueID (Array $Data,$key='ID'){

        $this->getModel()->ensureIndex(array($key=>1),array("unique"=>true));
 
        while(true){
            $c = $this->getModel()->find(array(),array($key=>1))->sort(array($key=>-1))->limit(1);

                if($c->hasNext()){
                    $c->next();
                    $d = $c->current();
                    $i = $d[$key] + 1;
                }
                else
                    $i =1;

            $Data["ID"] = $i;
            
            // SAFE and SYNC
            $this->getModel()->insert($Data,array("safe"=>true,"fsync"=>true));

            $err = $this->DB()->lastError();
            if($err && $err["code"]){
                // dup key
                if($err["code"] == 11000)
                    continue;
                // other error
                else;

            }
            break;
        }

        return $Data;
    } 


    /**
     * To ensure index
     * @param bool $backgroundIndex - Of true, it will run a background index
     */
    public function ensureIndex($backgroundIndex = true){
        
        $this->getModel()->ensureIndex($this->indexes);
        
        if(is_array($C->indexes)){
		
            foreach($C->indexes as $I){
			
                if(is_array($I["fields"]) && count($I["fields"])){
				
                    $options = is_array($I["options"]) ? $I["options"] : array();
			
                    // Background index
                    if($backgroundIndex)
                        $options["background"] = true;

                    $this->getModel()->ensureIndex($I["fields"],$options);
                }		
            }    
            return
                true;
        } 
        
    }
    
}



