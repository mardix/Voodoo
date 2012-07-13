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
 * @name        Voodooo\DB\Redis
 * @desc        The abstract class for models
 *              
 * @example     INCOMPLETE
 * 
 */

namespace Voodoo\DB\Mapper;

use Voodoo\Core;

abstract class Redis extends Connect{
 const DT_STRINGS = "Strings";
 const DT_HASHES = "Hashes";
 const DT_SETS = "Sets";
 const DT_SORTED_SETS = "SortedSets";
 const DT_LISTS = "Lists";
 
 protected $Model = null;
 
 protected $BucketName = null;
 
 protected $DataType = "Strings"; // Strings, Hashes, Lists, Sets, SortedSets 
 
 /*******************************************************************************/

    public function __construct(){

        $Connex = parent::connect();
        
        $this->Model = $Connex;
        
        if(!$this->BucketName)
            throw new Core\Exception("BucketName is null in ".__CLASS__);

    }  
  

    
    public function getModel(){
        
    }
    
/*******************************************************************************/    
    
    
    public function __call($fn,Array $args){

            $args = array_merge(array($this->BucketName),$args); 
            
            return
                call_user_func_array(array($this->getModel(),$fn),$args);

    }
    
    
    public function setBucket(){
        
    }
    
    public function set($key,$val){
        switch($this->DataTypes){
            
            case self::DT_STRINGS:
                
            break;
        }
        
    }

    
    public function get(){
        
    }
    
    
}
