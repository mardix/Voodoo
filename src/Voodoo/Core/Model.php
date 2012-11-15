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
 * @name        Voodooo\Model
 * @desc        The abstract class for models
 *              This class is extended by VoodOrm. All the public VoodOrm methods
 *              can be accessed in this class
 *
 * Quick Tips
 * - Relationship
 * Sometimes you will need relationship in your model
 *
 * Let's say you have the tables: book and author
 *
 * We can create a method in Model/Book to get author from the Model/Author
 *
 * class Book extends Voodoo\Core\Model
  {
   // blah blah code here

    // For One to One. Will get only one entry
    public function getAuthor(){
        $table = Author::create()->getTableName();
         return $this->{$table}(self::REL_HASONE, function($res){
                    return Author::create($res);
                });

    }

    // For One to Many. Will get all the tags entry
    public function getTags(){
        $table = Tags::create()->getTableName();
         return $this->{$table}(self::REL_HASMANY, function($res){
                    return Tags::create($res);
                });

    }
 }

 * Now that's how you access them
   $book = new Model\Book::findOne(1234);
   $author = $book->getAuthor()->name;
   $tags = $book->getTags();

 *
 */

namespace Voodoo\Core;

use Closure;

abstract class Model extends Model\VoodOrm
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
   * The DB Alias to use. It is saved in Application/Config/DB.ini
   * @var string
   */
  protected $dbAlias = "";

 /*******************************************************************************/

  /**
   * Create a new instance
   *
   * @param mixed $obj
   * @return Model
   */
    public static function create($obj = null)
    {
        $Instance = new static;

        if ($obj) {
            // A single row will return an array
            if (is_array($obj)) {
                $Instance->_toRow($obj);
                $Instance->is_single = true;

            } else if ($obj instanceof Model\VoodOrm) {
            // TODO: implement
            }
        }

        return $Instance;
    }

    /**
     * Constructor
     *
     * @throws Core\Exception
     */
    public function __construct()
    {
        if(!$this->tableName){
            throw new Exception("TableName is null in ".get_called_class());
        } else if (!$this->primaryKeyName){
            throw new Exception("PrimaryKeyName is null in ".get_called_class());
        } else if (!$this->dbAlias){
            throw new Exception("DB Alias is missing in ".get_called_class());
        }

        $PDO = Model\Connect::alias($this->dbAlias);

        parent::__construct($PDO, $this->primaryKeyName, $this->foreignKeyName);

        $instance = parent::table($this->tableName);

        $this->table_name = $instance->table_name;

        $this->table_token = $instance->table_token;

        $this->table_alias = $instance->table_alias;

        $this->primary_key_name = $instance->primary_key_name;

        $this->foreign_key_name = $instance->foreign_key_name;
    }


    /**
     * Returns the table name
     *
     * @param ModelName
     * @return string
     */
    public function getTableName(Model\VoodOrm $model = null)
    {
        if ($model != null) {
            return $model->getTableName();
        } else {
            return $this->table_name;
        }
    }

    /**
     * Returns the primary key name
     *
     * @return string
     */
    public function getPrimaryKeyName()
    {
        return $this->primary_key_name;
    }

    /**
     * Returns the foreign key name
     *
     * @return string
     */
    public function getForeignKeyName()
    {
        return $this->foreign_key_name;
    }

    /**
     * To execute a code only if $this->is_single
     * @param Closure $fn
     * @return mixed
     * @throws Exception
     */
    public function ifSingle(\Closure $fn)
    {
        if ($this->is_single) {
            return $fn();
        } else {
            throw new Exception("Not a single Object");
        }
    }


}
