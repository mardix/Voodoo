<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/mardix/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2014 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * @name        Voodooo\Model
 * @desc        The abstract class for models
 *              This class is extended by VoodOrm. All the public VoodOrm methods
 *              can be accessed in this class
 *** Association
 * Association allow you to associate a model with another by using local and foreign key
 * Association is not a JOIN. 
 * Association only executes on demand by making a second query when the object is requested the first time
 * Association uses the foreignKey and localKey to query all the data in a set from the original query (eager load).
 * 
 * ---
 * Examples
 * 
 * namespace MyModel;
 * use Voodoo;
 * 
 * class Author extends Voodoo\Core\Model
 * {
 *      protected $tableName = "author";
 *      protected $primaryKeyName = "id";
 *      protected $foreignKeyName = "%s_id";
 *      protected $dbAlias = "MyDB";
 *
 *      /**
 *       * @association MANY
 *       * @model MyModel\Book
 *       * @foreignKey author_id
 *       * @localKey id
 *      ** /
 *      protected $books;    
 * }
 * 
 * class Book extends Voodoo\Core\Model
 * {
 *      protected $tableName = "book";
 *      protected $primaryKeyName = "id";
 *      protected $foreignKeyName = "%s_id";
 *      protected $dbAlias = "MyDB";
 *
 *      /**
 *       * @association ONE
 *       * @model MyModel\Author
 *       * @foreignKey id
 *       * @localKey author_id
 *      ** /
 *      protected $author;   
 * 
 *      /**
 *       * @association MANY
 *       * @model MyModel\Publisher
 *       * @foreignKey id
 *       * @localKey published_id
 *      ** /
 *      protected $publisher;    
 * }
 * 
 * class Publisher extends Voodoo\Core\Model
 * {
 *      protected $tableName = "publisher";
 *      protected $primaryKeyName = "id";
 *      protected $foreignKeyName = "%s_id";
 *      protected $dbAlias = "MyDB";
 * }
 * 
 * // Get all books, then retrieve each books author and publisher
 * $books = new MyModel\Book;
 * foreach ($books as $book) {
 *      echo $book->title . "\n";
 *      echo "By author: " . $book->author()->name . "\n";
 *      echo "Publisher: " . $book->publisher()->name . "\n";
 * }
 * 
 * // Get all authors, then retrieve all the books associated to that author
 * $authors = new MyModel\Author;
 * foreach ($authors as $author) {
 *      echo "Author: " . $author->name . "\n";
 *      echo "All Books \n";
 * 
 *      foreach($author->books() as $book) {
 *          echo "Title: " . $book->title . "\n";
 *          echo "Publisher: " . $book->publisher()->name . "\n";   
 *      }
 * }
 * 
 * // A where clause can be added to filter the association
 * $authors = new MyModel\Author;
 * foreach ($authors as $author) {
 *      echo "Author: " . $author->name . "\n";
 *      echo "All Books \n";
 * 
 *      foreach($author->books(["where" => ["published" => 1]]) as $book) {
 *          echo "Title: " . $book->title . "\n";
 *          echo "Publisher: " . $book->publisher()->name . "\n";   
 *      }
 * }
 */

namespace Voodoo\Core;

use Voodoo\VoodOrm,
    Closure,
    PDO,
    ReflectionClass;

abstract class Model extends VoodOrm
{
  /**
   * The table name
   * @var type
   */
  protected $tableName = null;

  /**
   * The primary ke name
   * @var string
   */
  protected $primaryKeyName = "id";

  /**
   * The foreign key name for one to many
   * @var string
   */
  protected $foreignKeyName = "%s_id";

  /**
   * The DB Alias to use. It is saved in App/Config/DB.ini
   * @var string
   */
  protected $dbAlias = "";

  /**
   * Hold the table prefix
   * @var string
   */
  protected $tablePrefix = "";
  
  /**
   * Holds the association definitions
   * @var Array
   */
  private static $associations = [];
  
 /*******************************************************************************/

  /**
   * Create a new instance
   *
   * @param mixed $obj
   * @return Model
   */
    public static function create($obj = null)
    {
        if(is_array($obj)) { // fill the object with new data
            return (new static)->fromArray($obj);
        } else {
            return new static;
        }
    }

    /**
     * The constructor
     *
     * @param PDO $pdo
     * @throws Exception
     */
    public function __construct(PDO $pdo = null)
    {
        if(! $this->tableName){
            throw new Exception\Model("TableName is null in ".get_called_class());
        }
        if (! $this->primaryKeyName){
            throw new Exception\Model("PrimaryKeyName is null in ".get_called_class());
        }

        if (! $pdo) {
            if (! $this->dbAlias){
                throw new Exception\Model("DB Alias is missing in ".get_called_class());
            }
            $pdo =  ConnectionManager::connect($this->dbAlias);
        }

        parent::__construct($pdo, $this->primaryKeyName, $this->foreignKeyName);

        $this->table_name = $this->tableName;
        $this->table_alias = $this->tableName;
        $this->table_token = $this->tableName;

        $this->buildAssociations();
        
        $this->setup();
    }

    /**
     * To setup logic upon initialization of the model
     */
    protected function setup()
    { }
    
    /**
     * Set the table prefix. Which will be removed when doing an alias
     * 
     * @param string $prefix
     * @return \Voodoo\Component\Model\Model
     */
    protected function setTablePrefix($prefix) 
    {
        $this->tablePrefix = $prefix;
        return $this;
    }
    
    /**
     * Reformat PK to remove prefix
     * @return string
     */
    public function getPrimaryKeyname() 
    {
        $pk = parent::getPrimaryKeyname();
        return preg_replace("/^{$this->tablePrefix}/","", $pk);
    }
    
    /**
     * Reformat FK to remove prefix
     * @return string
     */
    public function getForeignKeyname()
    {
        $fk = parent::getForeignKeyname();
        return preg_replace("/^{$this->tablePrefix}/","", $fk);
    } 
    
    /**
     * Return the table name without the prefix
     * @return string
     */
    public function tableName()
    {
        return preg_replace("/^{$this->tablePrefix}/", "", $this->getTablename());
    }   
    
    /**
     * Override the __call to call associations
     * 
     * @param type $association
     * @param type $args
     * @return Mixed
     */
    public function __call($association, $args) 
    {
        $cldCls = get_called_class();
        if (isset(self::$associations[$cldCls]) && isset(self::$associations[$cldCls][$association])) {
            $assoc = self::$associations[$cldCls][$association];
            if ($args[0]) {
                if (is_string($args[0])) {
                    $assoc["where"] = [$args[0]];
                } else if (is_array($args[0])) {
                    if(isset($args[0]["where"])) {
                        if (is_array($args[0]["where"])) {
                            $assoc["where"] = array_merge_recursive($assoc["where"], $args[0]["where"]);
                        } else {
                            $assoc["where"] = $args[0]["where"];
                        }
                    }
                    if (isset($args[0]["sort"])) {
                        $assoc["sort"] = $args[0]["sort"];
                    }
                    if (isset($args[0]["columns"])) {
                        $assoc["columns"] = $args[0]["columns"];
                    }                    
                }
            }
            return parent::__call($assoc["model"]->tableName(), $assoc);
        } else {
           return parent::__call($association, $args); 
        }
    }
    
    /**
     * Build association from the properties annotations
     */
    private function buildAssociations()
    {
        $cldCls = get_called_class();
        if (!isset(self::$associations[$cldCls])) {
            self::$associations[$cldCls] = [];
            $ref = new ReflectionClass($this);
            $relationships = array_map(function($prop){
                            return [$prop->name, $prop->getDocComment()];
                        },$ref->getProperties());

            foreach ($relationships as $rel) {
                list($name, $doc) = $rel;
                $anno = new AnnotationReader($doc);
                if ($anno->has("association")) {
                    $modelName = $anno->get("model");
                    $model = new $modelName;
                    if (! $model instanceof Model) {
                        throw new Exception\Model("Model '{$modelName}' must be an instance of :" . __CLASS__);
                    }
                    $where = [];
                    if (is_array($anno->get("where"))) {
                        $where = $anno->get("where");
                    }
                    self::$associations[$cldCls][$name] = [
                        "model" => $model,
                        "association" => $ref->getConstant("ASSO_" . $anno->get("association")) ?: self::ASSO_MANY,
                        "localKey" => $anno->get("localKey") ?: $this->getPrimaryKeyname(),
                        "foreignKey" => $anno->get("foreignKey") ?: $this->getForeignKeyname(),
                        "where" => $where,
                        "sort" => $anno->get("sort") ?: null,
                        "columns" => $anno->get("columns") ?: "*",
                        "backref" => $anno->get("backref") == 1 ? true : false,
                        "callback" => null,
                    ];  
                }
            }            
        }

    }
    
    /**
     * Return the columns of this table
     * 
     * @return Array
     */
    public function __getColumns()
    {
        $res = $this->query("DESCRIBE {$this->getTableName()}", [], true);
        if ($res->rowCount()) {
            return array_map(function($col){
                return $col["Field"];
            }, $res->fetchAll(PDO::FETCH_ASSOC));
        } else {
            return [];
        }      
    }

    /**
     * Check if the table exists
     * 
     * @return bool
     */
    public function __tableExists() 
    {
        $res = $this->query("SHOW TABLES LIKE '{$this->getTableName()}'");
        $res = ($res->rowCount() > 0) ? true : false;
        $this->reset();
        return $res;
    }
}
