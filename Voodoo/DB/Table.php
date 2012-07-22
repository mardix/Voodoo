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
 * @name        Voodooo\DB\Table
 * @desc        The abstract class for models
 *              
 * @example     
 * 
 */

namespace Voodoo\DB;

use Voodoo\Core;

abstract class Table extends Mapper{
  

  /**
   * Hold the table's instance
   * @var type 
   */
  protected $Model = null;
  
  /**
   * Holds all the query with __call
   * @var type 
   */
  private $fQuery  = array();
  
  /**
   * The table name
   * @var type 
   */
  protected $TableName = null;
  
  /**
   * The primary ke name
   * @var type 
   */
  protected $PrimaryKeyName = "id";

 /*******************************************************************************/

    public function __construct(){

        if(!$this->TableName)
            throw new Core\Exception("TableName is null in ".__CLASS__);

        if(!$this->PrimaryKeyName)
            throw new Core\Exception("PrimaryKeyName is null in ".__CLASS__);

        $Connex = parent::connect();
        
        $Connex->structure->setPrimary($this->PrimaryKeyName);
        
        $this->Model = $Connex->{$this->TableName}();
            
    }  
  
    /**
     * Return the table instance
     * @return type 
     */
    public function getModel(){
        return
            $this->Model;
    }  
    
    
    /**
     * return the table name
     * @return string 
     */
    public function getModelName(){
        return
            $this->TableName;
    }
         
    /**
     * return the primary key name
     * @return string
     */
    public function getPrimaryKeyName(){
        return
            $this->PrimaryKeyName;
    }
    
    
/*******************************************************************************/
// CRUD  
    
    /**
     * To insert new data in the 
     * @param array $Data
     * @return type 
     */
    public function insert(Array $Data){
        if($this->isSingle)
            throw new Core\Exception(self::ERROR_ROW_OBJ." Can't call method: ".__METHOD__);
 
        return
            parent::map($this->getModel()->insert($Data));
    }
       
    
    /**
     * To update one or more rows in the table
     * If the Row is already active, it will update the current row, otherwise it will update all rows based on $where
     * @param array $Data
     * @return Table 
     */
    public function update(Array $Data){
        
        if($this->isSingle){
              $this->set($Data)->save();
        }
        
        else{
            if(isset($this->fQuery["where"])){
                call_user_func_array(array($this->getModel(),"where"),$this->fQuery["where"]);  
                unset($this->fQuery["where"]);
            }
            
            $this->getModel()->update($Data);
        }
        
        return
            $this;
    }
    
    
    
    /**
     * To delete one or more rows.
     * If Row is already active, it will delete the row, otherwise it will delete all rows based on $where
     * @param type $where
     * @return bool 
     */
    public function delete(){
        
        if($this->isSingle)
            return
                $this->Data->delete();

        else{
           
            if(isset($this->fQuery["where"])){
                call_user_func_array(array($this->getModel(),"where"),$this->fQuery["where"]);  
                unset($this->fQuery["where"]);
            }
            
          return
            $this->getModel()->delete();
        }
        
    }
       
   /**
    * To execute an SQL query and bind Data to it
    * @param type SELECT * FROM TABLE WHERE NAME=:Name
    * @param array array(:Name=>Soup)
    * @return Rows
    */
   public function query($query,$params){
        if($this->isSingle)
            throw new Core\Exception(self::ERROR_ROW_OBJ." Can't call method: ".__METHOD__);
        
       return
            $this->toRow($this->getModel()->executeQuery($query,$params));
   }    
    
    
    
    /**
     * Get the an entry by id
     * @param type $id
     * @param type $singleField - to return a single field value
     * @return Row
     */
    public function findPK($id){
        if($this->isSingle)
            throw new Core\Exception(self::ERROR_ROW_OBJ." Can't call method: ".__METHOD__);
        
        return
            $this->findOne(array($this->PrimaryKeyName=>$id));
    }

    
   /**
    * To return one row
    * @param Array $where. The where criteria for the single row
    * @return Row or false if not found
    */
   public function findOne(Array $where){  
        if($this->isSingle)
            throw new Core\Exception(self::ERROR_ROW_OBJ." Can't call method: ".__METHOD__);
        
       $R = $this->getModel()->reset()->where($where)->fetch();
       
       return 
            ($R) ? parent::map($R) : false;

   }
   
   /**
    * To find all entries.
    * @return Rows 
    */
   public function findAll(){
        if($this->isSingle)
            throw new Core\Exception(self::ERROR_ROW_OBJ." Can't call method: ".__METHOD__);
        
       return
            $this->find()->where(array());       
   }
  
   


  /**
   * To query entries in the db. Using find() will reset all previous queries to start a fresh new query
   * @param type $columns
   * @return \Core\DataMapper\Table
   * @throws Core\Exception 
   */
  public function find($columns="*"){
        if($this->isSingle)
            throw new Core\Exception(self::ERROR_ROW_OBJ." Can't call method: ".__METHOD__);
       
      $this->getModel()->reset()->select($columns);
      
      return
        $this;
  }
  
  /**
   * Return the total entries found based on criteria
   * @param string $column, to quickly count on a column
   * @return type
   * @throws Core\Exception 
   */
  public function count($column = ""){
        if($this->isSingle)
            throw new Core\Exception(self::ERROR_ROW_OBJ." Can't call method: ".__METHOD__);  
       
       if(isset($this->fQuery["where"]))
           call_user_func_array(array($this->getModel(),"where"),$this->fQuery["where"]);

       return
           $this->getModel()->count($column); 
  }
  
  /**
   * 
   * where(), select(), orderBy(), order(), limit(), groupBy(), group(), sum(), max(), min()
   * @param type $method
   * @param type $args
   * @return \Core\DataMapper\Collection
   * @throws Core\Exception 
   */
  final public function __call($method,$args){
        if($this->isSingle)
            throw new Core\Exception(self::ERROR_ROW_OBJ." Can't call method: {$method}()");
     
      /**
       * Valid methods to build the query 
       */
      $validMethods = array("where","select","orderBy","order","limit","groupBy","group","sum","max","min");
      
      $method = str_replace(array("orderBy","groupBy"),array("order","group"),$method);

      if(in_array($method,$validMethods)){
         
         // Modify where to accept multiple call
         if($method == "where"){
             
            if(!isset($this->fQuery[$method]))
                $this->fQuery[$method] = array(); 

            $args = is_string($args[0]) ? array($args[0]=>$args[1]) : $args[0];

            $this->fQuery[$method] = array(Core\Helpers::arrayExtend($this->fQuery[$method],$args));             
         }

         else
           $this->fQuery[$method]  = $args;
      }
      
      else
          throw new Core\Exception("__call to non existent method: {$method}()");
  
      return
        $this;
  }
  

  /**
   * 
   * @return Array
   * @throws Core\Exception 
   */
  protected function getResults(){
        if($this->isSingle)
            throw new Core\Exception(self::ERROR_ROW_OBJ." Can't call method: ".__METHOD__);
            
        $O = $this->getModel();
        
        foreach($this->fQuery as $fn=>$val){
            call_user_func_array(array($O,$fn),$val);
        }

        return $O;      
  }
  

    
/*******************************************************************************/
// Method accessible with single row
   
    /**
     * Return the row Id
     * @return int
     * Id format $TableName_Id
     */
    public function getPK(){
        if(!$this->isSingle)
            throw new Core\Exception(self::ERROR_NOT_ROW_OBJ." Can't call method: ".__METHOD__);
        
        return
            $this->get($this->PrimaryKeyName);
    }
    
    final public function get($key){
        if(!$this->isSingle)
            throw new Core\Exception(self::ERROR_NOT_ROW_OBJ." Can't call method: ".__METHOD__);
                
        return
            $this->Data[$key];
    }
    
    
    /**
     * Getter
     * @param type $name
     * @return type 
     */
    final public function __get($name){
            return $this->get($name);
    }
    
    
    /**
     * To set data. But unlike update, it doesn't save.
     * @param string | array $key - if it's array, it will set array
     * @param type $value
     * @return AbstractRow 
     */
    final public function set($key,$value=""){
        if(!$this->isSingle)
            throw new Core\Exception(self::ERROR_NOT_ROW_OBJ." Can't call method: ".__METHOD__);
                
        if(is_array($key)){
            foreach($key as $k=>$v)
                $this->set($k,$v);
        }

        else
            $this->Data[$key] = $value;
        
        return
            $this;
    }   
    
    /**
     * To update saved data
     * @return AbstractRow 
     */
    public function save(){
        if(!$this->isSingle)
            throw new Core\Exception(self::ERROR_NOT_ROW_OBJ." Can't call method: ".__METHOD__);
        
        $this->Data->update();
        
        return 
            $this;
    }
    

    
    
    
    /**
     * Return the table name or the primary key if it's a single row
     * @return type 
     */
    public function __toString(){
        return
            ($this->isSingle) ? $this->getPK() : $this->getModelName();
    }
    
    
    /**
     * Return the date in MySQL datetime format
     * @return string YYYY-MM-DD HH:II:SS
     */
    public function DateTime(){
        return
            date("Y-m-d H:i:s");
    }
    
    
}
