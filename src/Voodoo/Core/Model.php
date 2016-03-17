<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/mardix/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2016 Mardix (http://github.com/mardix)
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
 **** Columns
 * To create column, just add the @column on the property
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
 *       * @column
 *       * @type id
 *      ** /
 *      protected $id;
 *
 *      /**
 *       * @column
 *       * @type string
 *      ** /
 *      protected $name;
 *
 *      /**
 *       * @column
 *       * @type number
 *      ** /
 *      protected $user_number;
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
 *
 *
 **** Table Properties
 * The model may contain the schema, timestampable names, and table engine
 * If the table doesn't exist and a schema is found, it will attempt to create it
 *
 *
 * protected $__table__ = [
 *      // The table engin
 *      self::TABLE_KEY_ENGINE => "InnoDB",
 *
 *      // Timestampable: To automatically update the time
 *      self::TABLE_KEY_TIMESTAMPABLE => [
 *          "onInsert" => ["updated_at", "created_at"],
 *          "onUpdate" => ["updated_at"],
 *      ],
 *
 *      // Define the table schema to be created when the table doesn't exist
 *      self::TABLE_KEY_SCHEMA => [
 *          "field_name" => [
 *              "type" => "id",
 *              "length => 3,
 *              ... more properties
 *          ],
 *          "another_field_name" => [
 *              "type" => "id",
 *              "length => 3,
 *              ... more properties
 *          ]
 *      ] ...
  * ];
 *
 *
 */

namespace Voodoo\Core;

use Voodoo\VoodOrm,
    Closure,
    PDO,
    PDOException,
    ReflectionClass;

abstract class Model extends VoodOrm
{
    /**
     * PDOException code for table that doesn't exist
     */
    const TABLE_DOESNT_EXIST_PDO_EX_CODE = "42S02";


    const TABLE_KEY_ENGINE = "ENGINE";

    const TABLE_KEY_TIMESTAMPABLE = "TIMESTAMPABLE";
    //--------------------------------------------------------------------------
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

    /**
    Holds all the columns
    **/
    private $__columns = [];

    /**
    * Hold the table properties
    * keys: SCHEMA, ENGINE, TIMESTAMPABLE
    * @var Array
    */
    protected $__table__ = [];

    protected $__tableEngine = "InnoDB";

    private $callbacks = [
        "onInsert" => null,
        "onUpdate" => null,
        "onDelete" => null
    ];

    /**
    * @column
    * @type id
    */
    public $id = "";

    /**
    * @column
    * @type dt
    */
    public $created_at = "";

    /**
    * @onUpdate 
    * @column
    * @type dt
    */
    public $updated_at = "";

    /**
    * @column
    * @type bool
    */
    public $is_deleted = "";

    /**
    * @column
    * @type dt
    */
    public $created_at = "";

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
        $this->__columns = array_filter(function($e){ return $e["name"]; }, $this->getSchemaColumns());
        $this->setup();
    }

    /**
     * To setup logic upon initialization of the model
     * Normally this is where you would set up onInsert, onUpdate, onDelete
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
     * Return the array representation of the single result for views.
     *
     * return Array
     */
    public function getViewModel()
    {
        if ($this->isSingleRow()) {
            return $this->toArray();
        } else {
            throw new \Exception("Can't get a valid ViewModel on non SingleRow");
        }
    }

//------------------------------------------------------------------------------
    /**
     * Setup a callback to execute on Insert
     * @param \Closure $callback
     * @return \Voodoo\Core\Model
     */
    protected function onInsert(Closure $callback = null)
    {
        $this->callbacks["onInsert"] = $callback;
        return $this;
    }

    /**
     * Setup a callback to execute on Update
     * @param \Closure $callback
     * @return \Voodoo\Core\Model
     */
    protected function onUpdate(Closure $callback = null)
    {
        $this->callbacks["onUpdate"] = $callback;
        return $this;
    }

    /**
     * Setup a callback to execute on Delete
     * @param \Closure $callback
     * @return \Voodoo\Core\Model
     */
    protected function onDelete(Closure $callback = null)
    {
        $this->callbacks["onDelete"] = $callback;
        return $this;
    }

    private function getTimestampableProperty($key)
    {
        if (isset($this->__table__[self::TABLE_KEY_TIMESTAMPABLE])
            && isset($this->__table__[self::TABLE_KEY_TIMESTAMPABLE][$key])) {

            return $this->__table__[self::TABLE_KEY_TIMESTAMPABLE][$key];
        }
        return null;
    }
    /**
     * To insert
     * @param array $data
     * @return Model
     */
    public function insert(array $data)
    {
        $ts = $this->getTimestampableProperty("onInsert");
        if (is_array($ts)) {
            $_data = [];
            foreach ($ts as $key) {
                $_data[$key] = $this->getDateTime();
            }
            if ($this->isArrayMultiDim($data)) {
                $nData = [];
                foreach($data as $dd) {
                    $nData[] = array_merge($_data, $dd);
                }
                $data = $nData;
            } else {
                $data = array_merge($_data, $data);
            }
        }
        return parent::insert($this->onCallable("onInsert", $data));
    }

    /**
     * To update
     * @param array $data
     * @return int
     */
    public function update(array $data = null)
    {
        $ts = $this->getTimestampableProperty("onUpdate");
        if (is_array($ts)) {
            foreach($ts as $key) {
                $this->set($key, $this->getDateTime());
            }
        }
        return parent::update($this->onCallable("onUpdate", $data));
    }

    /**
     * To delete
     * @param $deleteAll bool
     * @return int
     */
    public function delete($deleteAll = false)
    {
        $this->onCallable("onDelete");
        return parent::delete($deleteAll);
    }

    /**
     * To execute the callback
     *
     * @param string $fn - The callback key
     * @param mixed $data
     * @return mixed
     */
    private function onCallable($fn, $data = null)
    {
        if (is_callable($this->callbacks[$fn])) {
            return $this->callbacks[$fn]($data);
        } else {
            return $data;
        }
    }

//------------------------------------------------------------------------------

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
     * Build schema from the properties with @column
     */
    private function getSchemaColumns()
    {
        $schema = [];
		$_cols_key_props = ["name", "type", "length", "unsigned", "allow_null", "default",
		"auto_increment", "primary_key", "index", "unique", "extra"];
        $ref = new ReflectionClass($this);
        $properties = array_map(function($prop){
                        return [$prop->name, $prop->getDocComment()];
                    },$ref->getProperties());
        foreach ($properties as $prop) {
            list($name, $doc) = $prop;
            $anno = new AnnotationReader($doc);
            if ($anno->has("column")) {
                $_ = ["name" => $name];
                foreach ($_cols_key_props as $_k) {
                    if ($anno->has($_k)) {
                        $_[$_k] = $anno->get($_k);
                    }
                }
                $schema[] = $_;
            }
        }
        return $schema;
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

    /**
     * Create a schema based on the
     */
    public function __createTable()
    {
        $schema = $this->getSchemaColumns();
        if (is_array($this->__table__[self::TABLE_KEY_SCHEMA])) {

            if (! $this->__tableExists()) {
                $sql = (new SchemaBuilder($schema, $this->$__tableEngine))->create($this->getTableName());
                $this->query($sql);
                $this->reset();
            }
            return true;
        }
        return false;
    }

    /**
     * To execute a raw query.
     * The option to allow the creation of the table if it doesn't exist was added
     *
     * @param string    $query
     * @param Array     $parameters
     * @param bool      $return_as_pdo_stmt - true, it will return the PDOStatement
     *                                       false, it will return $this, which can be used for chaining
     *                                              or access the properties of the results
     * @return VoodOrm | PDOStatement
     */
    public function query($query, Array $parameters = array(), $return_as_pdo_stmt = false) {
        try {
            return parent::query($query, $parameters, $return_as_pdo_stmt);
        } catch (PDOException $pdoex) {
            // Table doesn't exist but schema is available
            if ($pdoex->getCode() === self::TABLE_DOESNT_EXIST_PDO_EX_CODE
                    && isset($this->__table__[self::TABLE_KEY_SCHEMA])
                    && is_array($this->__table__[self::TABLE_KEY_SCHEMA])) {
                $this->__createTable();
                return parent::query($query, $parameters, $return_as_pdo_stmt);
            } else {
                throw $pdoex;
            }
        }
    }


    /**
     * Create an instance from the given row (an associative
     * array of data fetched from the database)
     * In this instance we want to assign the object if it exists
     * @return Voodoo\VoodOrm
     */
    public function fromArray(Array $data)
    {
        $row  = clone($this);
        $row->reset();
        $row->is_single = true;
        $row->_data = $data;
        if (count($this->__columns)) {
            foreach($this->__columns as $c) {
                $row->{$c} = $row->_data[$c];
            }
        }
        return $row;
    }
}
