<?php
/**
 * -----------------------------------------------------------------------------
 * VoodOrm
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/mardix/VoodOrm
 * @package     VoodooPHP (https://github.com/VoodooPHP/Voodoo/)
 *
 * @copyright   (c) 2012 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * About VoodOrm
 *
 * VoodOrm is a micro-ORM which functions as both a fluent select query API and a CRUD model class.
 * VoodOrm is built on top of PDO and is well fit for small to mid-sized projects, where the emphasis
 * is on simplicity and rapid development rather than infinite flexibility and features.
 * VoodOrm works easily with table relationship
 *
 * Learn more: https://github.com/mardix/VoodOrm
 *
 */

namespace Voodoo\Core\Model;

use ArrayIterator,
    IteratorAggregate,
    Closure,
    PDO;

class VoodOrm implements IteratorAggregate
{
    const NAME              = "VoodOrm";
    const VERSION           = "0.5";

    // RELATIONSHIP CONSTANT
    const REL_HASONE        =  1;       // OneToOne. Eager Load data
    const REL_LAZYONE       = -1;     // OneToOne. Lazy load data
    const REL_HASMANY       =  2;      // OneToMany. Eager load data
    const REL_LAZYMANY      = -2;    // OneToOne. Lazy load data
    const REL_HASMANYMANY   =  3;  // ManyToMany. Not implemented

    const WHERE_OPERATOR_AND = " AND ";
    const WHERE_OPERATOR_OR  = " OR ";
    
    public $pdo = null;

    public $pdoStmt = null;

    public $table_name = "";

    public $table_token = "";

    public $primary_key_name = "id";

    public $foreign_key_name = "";

    public $allRows = array();

    protected $table_alias = "";

    protected $is_single = false;

    private $select_fields = array();

    private $join_sources = array();

    private $limit = null;

    private $offset = null;

    private $order_by = array();

    private $group_by = array();

    private $where_parameters = array();

    private $where_conditions = array();
    
    private $where_operator = self::WHERE_OPERATOR_AND;
    
    private $wrap_open = false;
    
    private $last_wrap_position = 0;

    private $is_fluent_query = true;

    private $pdo_executed = false;

    private $_data = array();
    
    private $debug_sql_query = false;
    
    private $sql_query = "";
    
    private $sql_parameters = array();

    private $_dirty_fields = array();

    private static $references = array();
    
    private $query_profiler = array();

    // Table structure
    public $table_structure = array(
        "primaryKeyName"    => "id",
        "foreignKeyName"    => "%s_id",
        "tablePrefix"       => ""
    );

/*******************************************************************************/

    /**
     * Constructor & set the table structure
     *
     * @param PDO    $pdo            - The PDO connection
     * @param string $primaryKeyName - Structure: table primary. If its an array, it must be the structure
     * @param string $foreignKeyName - Structure: table foreignKeyName.
     *                  It can be like %s_id where %s is the table name
     * @param string $tablePrefix
     *
     */
    public function __construct(PDO $pdo, $primaryKeyName = "id", 
                                $foreignKeyName = "%s_id", $tablePrefix = "") {
        $this->pdo = $pdo;

        // Set the table structure
        if (is_array($primaryKeyName)) {
            $structure = $primaryKeyName;
        } else {
            $structure = array(
                "primaryKeyName"    => $primaryKeyName,
                "foreignKeyName"    => $foreignKeyName,
                "tablePrefix"       => $tablePrefix
            );
        }

        $this->table_structure = array_merge($this->table_structure, $structure);
    }

    /**
     * Define the working table and create a new instance
     *
     * @param  string   $tableName - Table name
     * @param  string   $alias     - The table alias name
     * @return Voodoo\VoodOrm
     */
    public function table($tableName, $alias = "")
    {
        $instance = clone($this);

        $instance->table_name = $this->table_structure["tablePrefix"].$tableName;

        $instance->table_token = $this->tokenize($this->table_name,":");

        $instance->setTableAlias($alias);

        $instance->primary_key_name = $this->formatTableKeyName($this->table_structure["primaryKeyName"], $tableName);

        $instance->foreign_key_name = $this->formatTableKeyName($this->table_structure["foreignKeyName"], $tableName);

        return $instance;
    }

    /**
     * Set the table alias
     *
     * @param string $alias
     * @return Voodoo\VoodOrm
     */
    public function setTableAlias($alias)
    {
        $this->table_alias = $alias;
        return $this;
    }

/*******************************************************************************/
    /**
     * To execute a raw query
     *
     * @param string $query
     * @param Array  $parameters
     * @param bool   $is_fluent_query -
     *          FALSE to return a bool if the the pdoStmt was executed
     *          TRUE, return self and you can use $this->find() or $this->findOne() to retrieve entries
     * @return bool | Voodoo\VoodOrm 
     */
    public function query($query, Array $parameters = array(), $is_fluent_query = true)
    {
        $this->sql_parameters = $parameters;
        $this->sql_query = $query;
        
        if ($this->debug_sql_query) {
            return false;
        } else {
            $_stime = microtime(true);

            $this->pdoStmt = $this->pdo->prepare($query);

            $this->pdo_executed = $this->pdoStmt->execute($parameters);

            $_time = microtime(true) - $_stime;

            // query profiler
            if (!isset($this->$this->query_profiler["total_time"])){
                $this->query_profiler["total_time"] = 0;
            }

            $this->query_profiler[] = array(
                "query"         => $query,
                "params"        => $parameters,
                "affected_rows" => $this->rowCount(),
                "time"          => $_time
            );

            $this->query_profiler["total_time"] = $this->query_profiler["total_time"] + $_time;

            $this->is_fluent_query = $is_fluent_query;

            return $this->is_fluent_query ? $this : $this->pdo_executed;            
        }
    }

    /**
     * Return the number of affected row by the last statement
     *
     * @return int
     */
    public function rowCount()
    {
        return ($this->pdo_executed == true) ? $this->pdoStmt->rowCount() : 0;
    }


/*------------------------------------------------------------------------------
                                Querying
*-----------------------------------------------------------------------------*/
    /**
     * To find all rows and create their instances
     * Use the query builder to build the where clause or $this->query with select
     * If a callback function is provided, the 1st arg must accept the rows results
     *
     * $this->find(function($rows){
     *   // do more stuff here...
     * });
     *
     * @param  Closure        $callback - run a function on the returned rows
     * @return \ArrayIterator
     */
    public function find(Closure $callback = null)
    {
        $map = array();

        if($this->is_fluent_query){
            $this->query($this->getSelectQuery(), $this->getWhereParameters());
        }
        
        //Debug SQL Query
        if ($this->debug_sql_query) {
            $this->debugSqlQuery(false);
            return false;
        } else {
            if ($this->pdo_executed == true) {
                $this->reset();
                $this->allRows = $this->pdoStmt->fetchAll(PDO::FETCH_ASSOC);

                if (is_callable($callback)) {
                    return $callback($this->allRows);
                } else {
                    $that = $this;
                    $map = array_map(function($r) use ($that) {
                                return $that->_toRow($r);
                            }, $this->allRows);                    
                }
            }
            return new ArrayIterator($map);            
        }      
    }
    
    /**
     * Return one row
     *
     * @param  int      $id - use to fetch by primary key
     * @return Voodoo\VoodOrm 
     */
    public function findOne($id = null)
    {
        if ($id){
            $this->wherePK($id);
        }

        $this->limit(1);
        
        // Debug the SQL Query
        if ($this->debug_sql_query) {
            $this->find();
            return false;
        } else {
            $findAll = $this->find();
            return $findAll->valid() ? $findAll->offsetGet(0) : false;
        }
    }
    
    /**
     * This method allow the iteration inside of foreach()
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
      return ($this->is_single) ? new ArrayIterator($this->toArray()) : $this->find();
    }
    
    /**
     * Create an instance from the given row (an associative
     * array of data fetched from the database)
     *
     * @return Voodoo\VoodOrm 
     */
    public function _toRow(Array $data)
    {
        $row  = clone($this);
        $row->reset();
        $row->is_single = true;
        $row->_data = $data;
        return $row;
    }

/*------------------------------------------------------------------------------
                                Fluent Query Builder
*-----------------------------------------------------------------------------*/

    /**
     * Create the select clause
     *
     * @param  mixed    $expr  - the column to select. Can be string or array of fields
     * @param  string   $alias - an alias to the column
     * @return Voodoo\VoodOrm 
     */
    public function select($columns = "*", $alias = null)
    {
        $this->is_fluent_query = true;

        if ($alias && !is_array($columns)){
            $columns .= " AS {$alias} ";
        }

        if(is_array($columns)){
            $this->select_fields = array_merge($this->select_fields, $columns);
        } else {
            $this->select_fields[] = $columns;
        }

        return $this;
    }

    /**
     * Add where condition, more calls appends with AND
     *
     * @param string condition possibly containing ? or :name
     * @param mixed array accepted by PDOStatement::execute or a scalar value
     * @param mixed ...
     * @return Voodoo\VoodOrm 
     */
    public function where($condition, $parameters = array())
    {
        $this->is_fluent_query = true;
        
        // By default the where_operator and wrap operator is AND, 
        if ($this->wrap_open || ! $this->where_operator) {
            $this->_and();
        } 

        // where(array("column1" => 1, "column2 > ?" => 2))
        if (is_array($condition)) {
            foreach ($condition as $key => $val) {
                $this->where($key, $val);
            }
            return $this;
        }

        $args = func_num_args();
        if ($args != 2 || strpbrk($condition, "?:")) { // where("column < ? OR column > ?", array(1, 2))
            if ($args != 2 || !is_array($parameters)) { // where("column < ? OR column > ?", 1, 2)
                $parameters = func_get_args();
                array_shift($parameters);
            }
        } else if (!is_array($parameters)) {//where(colum,value) => colum=value
            $condition .= " = ?";
            $parameters = array($parameters);
        } else if (is_array($parameters)) { // where("column", array(1, 2)) => column IN (?,?)
            $placeholders = $this->makePlaceholders(count($parameters));
            $condition = "({$condition} IN ({$placeholders}))";
        }

        $this->where_conditions[] = array(
            "STATEMENT"   => $condition,
            "PARAMS"      => $parameters,
            "OPERATOR"    => $this->where_operator
        );

        // Reset the where operator to AND. To use OR, you must call _or()
        $this->_and();
        
        return $this;
    }

    /**
     * Create an AND operator in the where clause
     * 
     * @return Voodoo\VoodOrm 
     */
    public function _and() 
    {
        if ($this->wrap_open) {
            $this->where_conditions[] = self::WHERE_OPERATOR_AND;
            $this->last_wrap_position = count($this->where_conditions);
            $this->wrap_open = false;
        } else {
            $this->where_operator = self::WHERE_OPERATOR_AND;
        }
        return $this;
    }

    
    /**
     * Create an OR operator in the where clause
     * 
     * @return Voodoo\VoodOrm 
     */    
    public function _or() 
    {
        if ($this->wrap_open) {
            $this->where_conditions[] = self::WHERE_OPERATOR_OR;
            $this->last_wrap_position = count($this->where_conditions);
            $this->wrap_open = false;
        } else {
            $this->where_operator = self::WHERE_OPERATOR_OR;
        }
        return $this;
    }
    
    /**
     * To group multiple where clauses together.  
     * 
     * @return Voodoo\VoodOrm 
     */
    public function wrap()
    {
        $this->wrap_open = true;
        
        $spliced = array_splice($this->where_conditions, $this->last_wrap_position, count($this->where_conditions), "(");
        $this->where_conditions = array_merge($this->where_conditions, $spliced);

        array_push($this->where_conditions,")");
        $this->last_wrap_position = count($this->where_conditions);

        return $this;
    }
    
    /**
     * Where Primary key
     *
     * @param  int  $id
     * @return type
     */
    public function wherePK($id)
    {
        return $this->where($this->primary_key_name, $id);
    }

    /**
     * WHERE $columName != $value
     *
     * @param  string   $columnName
     * @param  mixed    $value
     * @return Voodoo\VoodOrm 
     */
    public function whereNot($columnName, $value)
    {
        return $this->where("$columnName != ?", $value);
    }

    /**
     * WHERE $columName LIKE $value
     *
     * @param  string   $columnName
     * @param  mixed    $value
     * @return Voodoo\VoodOrm 
     */
    public function whereLike($columnName, $value)
    {
        return $this->where("$columnName LIKE ?", $value);
    }

    /**
     * WHERE $columName NOT LIKE $value
     *
     * @param  string   $columnName
     * @param  mixed    $value
     * @return Voodoo\VoodOrm 
     */
    public function whereNotLike($columnName, $value)
    {
        return $this->where("$columnName NOT LIKE ?", $value);
    }

    /**
     * WHERE $columName > $value
     *
     * @param  string   $columnName
     * @param  mixed    $value
     * @return Voodoo\VoodOrm 
     */
    public function whereGt($columnName, $value)
    {
        return $this->where("$columnName > ?", $value);
    }

    /**
     * WHERE $columName >= $value
     *
     * @param  string   $columnName
     * @param  mixed    $value
     * @return Voodoo\VoodOrm 
     */
    public function whereGte($columnName, $value)
    {
        return $this->where("$columnName >= ?", $value);
    }

    /**
     * WHERE $columName < $value
     *
     * @param  string   $columnName
     * @param  mixed    $value
     * @return Voodoo\VoodOrm 
     */
    public function whereLt($columnName, $value)
    {
        return $this->where("$columnName < ?", $value);
    }

    /**
     * WHERE $columName <= $value
     *
     * @param  string   $columnName
     * @param  mixed    $value
     * @return Voodoo\VoodOrm 
     */
    public function whereLte($columnName, $value)
    {
        return $this->where("$columnName <= ?", $value);
    }

    /**
     * WHERE $columName IN (?,?,?,...)
     *
     * @param  string   $columnName
     * @param  Array    $value
     * @return Voodoo\VoodOrm 
     */
    public function whereIn($columnName, Array $values)
    {
        return $this->where($columnName,$values);
    }
    
    /**
     * WHERE $columName NOT IN (?,?,?,...)
     *
     * @param  string   $columnName
     * @param  Array    $value
     * @return Voodoo\VoodOrm 
     */
    public function whereNotIn($columnName, Array $values)
    {
        $placeholders = $this->makePlaceholders(count($values));

        return $this->where("({$columnName} NOT IN ({$placeholders}))", $values);
    }

    /**
     * WHERE $columName IS NULL
     *
     * @param  string   $columnName
     * @return Voodoo\VoodOrm 
     */
    public function whereNull($columnName)
    {
        return $this->where("({$columnName} IS NULL)");
    }

    /**
     * WHERE $columName IS NOT NULL
     *
     * @param  string   $columnName
     * @return Voodoo\VoodOrm 
     */
    public function whereNotNull($columnName)
    {
        return $this->where("({$columnName} IS NOT NULL)");
    }

    /**
     * ORDER BY $columnName (ASC | DESC)
     *
     * @param  string   $columnName - The name of the colum or an expression
     * @param  string   $ordering   (DESC | ASC)
     * @return Voodoo\VoodOrm 
     */
    public function orderBy($columnName, $ordering = "")
    {
        $this->is_fluent_query = true;
        $this->order_by[] = "{$columnName} {$ordering}";
        return $this;
    }

    /**
     * GROUP BY $columnName
     *
     * @param  string   $columnName
     * @return Voodoo\VoodOrm 
     */
    public function groupBy($columnName)
    {
        $this->is_fluent_query = true;
        $this->group_by[] = $columnName;
        return $this;
    }


    /**
     * LIMIT $limit
     *
     * @param  int      $limit
     * @param  int      $offset
     * @return Voodoo\VoodOrm 
     */
    public function limit($limit, $offset = null)
    {
        $this->is_fluent_query = true;
        $this->limit = $limit;
        
        if($offset){
            $this->offset($offset);
        }

        return $this;
    }

    /**
     * OFFSET $offset
     *
     * @param  int      $offset
     * @return Voodoo\VoodOrm 
     */
    public function offset($offset)
    {
        $this->is_fluent_query = true;
        $this->offset = $offset;
        return $this;
    }


    /**
     * Build a join
     *
     * @param  type     $table         - The table name
     * @param  string   $constraint    -> id = profile.user_id
     * @param  string   $table_alias   - The alias of the table name
     * @param  string   $join_operator - LEFT | INNER | etc...
     * @return Voodoo\VoodOrm 
     */
    public function join($table, $constraint, $table_alias = "", $join_operator = "")
    {
        $this->is_fluent_query = true;

        if($table instanceof VoodOrm){
            $table = $table->table_name;
        }
        $join  = $join_operator ? "{$join_operator} " : "";
        $join .= "JOIN {$table} ";
        $join .= $table_alias ? "{$table_alias} " : "";
        $join .= "ON {$constraint}";
        $this->join_sources[] = $join;
        return $this;
    }

    /**
     * Create a left join
     *
     * @param  string   $table
     * @param  string   $constraint
     * @param  string   $table_alias
     * @return Voodoo\VoodOrm 
     */
    public function leftJoin($table, $constraint, $table_alias=null)
    {
        return $this->join($table, $constraint, $table_alias,"LEFT");
    }


    /**
     * Return the buit select query
     *
     * @return string
     */
    public function getSelectQuery()
    {
        $columns = (is_array($this->select_fields) && count($this->select_fields)) 
                    ? $this->select_fields : array("*");
        
        $result_columns = implode(", ", $columns);

        $query = "SELECT {$result_columns} FROM {$this->table_name}";

            $query .= ($this->table_alias) ? " AS {$this->table_alias}" : "";
            
        if(count($this->join_sources)){
            $query .= (" ").implode(" ",$this->join_sources);
        }
            $query .= $this->getWhereString();
        if (count($this->group_by)){
            $query .= " GROUP BY " . implode(", ", $this->group_by);
        }
        if (count($this->order_by)){
            $query .= " ORDER BY " . implode(", ", $this->order_by);
        }
        if ($this->limit){
            $query .= " LIMIT " . $this->limit;
        }
        if ($this->offset){
            $query .= " OFFSET " . $this->offset;
        }
        return $query;
    }

    
    /**
     * Build the WHERE clause(s)
     *
     * @return string
     */
    protected function getWhereString()
    {
        // If there are no WHERE clauses, return empty string
        if (!count($this->where_conditions)) {
            return " WHERE 1";
        } 

        $where_condition = "";
        $last_condition = "";

        foreach ($this->where_conditions as $condition) {
            if (is_array($condition)) {
                if ($where_condition && $last_condition != "(" && !preg_match("/\)\s+(OR|AND)\s+$/i", $where_condition)) {
                    $where_condition .= $condition["OPERATOR"];
                }
                $where_condition .= $condition["STATEMENT"];
                $this->where_parameters = array_merge($this->where_parameters, $condition["PARAMS"]);
            } else {
                $where_condition .= $condition;
            }
            $last_condition = $condition;
        }

        return " WHERE {$where_condition}" ;
    }

    /**
     * Return the values to be bound for where
     *
     * @return Array
     */
    protected function getWhereParameters()
    {
        return $this->where_parameters;
    }

    /**
      * Detect if its a single row instance and reset it to PK
      *
      * @return Voodoo\VoodOrm 
      */
    protected function setSingleWhere()
    {
        if ($this->is_single) {
            $this->resetWhere();
            $this->wherePK($this->getPK());
        }
        return $this;
    }

    /**
      * Reset the where
      *
      * @return Voodoo\VoodOrm 
      */
    protected function resetWhere()
    {
        $this->where_conditions = array();
        $this->where_parameters = array();
        return $this;
    }  
    
    
/*------------------------------------------------------------------------------
                                Insert
*-----------------------------------------------------------------------------*/    
    /**
     * Insert new rows
     * $data can be 2 dimensional to add a bulk insert
     * If a single row is inserted, it will return it's row instance
     *
     * @param  array    $data - data to populate
     * @return Voodoo\VoodOrm 
     */
    public function insert(Array $data)
    {
        $insert_values = array();
        $question_marks = array();

        // check if the data is multi dimention for bulk insert
        $multi = (count($data) != count($data,COUNT_RECURSIVE));

        $datafield = array_keys( $multi ? $data[0] : $data);

        if ($multi) {
            foreach ($data as $d) {
                $question_marks[] = '('  . $this->makePlaceholders(count($d)) . ')';
                $insert_values = array_merge($insert_values, array_values($d));
            }
        } else {
            $question_marks[] = '('  . $this->makePlaceholders(count($data)) . ')';
            $insert_values = array_values($data);
        }

        $sql = "INSERT INTO {$this->table_name} (" . implode(",", $datafield ) . ") ";
        $sql .= "VALUES " . implode(',', $question_marks);

        $this->query($sql,$insert_values,false);

        // Return the SQL Query
        if ($this->debug_sql_query) {
            $this->debugSqlQuery(false);
            return false;
        }
                
        $rowCount = $this->rowCount();

        if ($rowCount == 1) {
            return $this->findOne($this->pdo->lastInsertId($this->primary_key_name));
        }

        return $this->rowCount();
    }

/*------------------------------------------------------------------------------
                                Updating
*-----------------------------------------------------------------------------*/    
    /**
      * Update entries
      * Use the query builder to create the where clause
      *
      * @param Array the data to update
      * @return int - total affected rows
      */
    public function update(Array $data = null)
    {
        $this->setSingleWhere();

        if (! is_null($data)) {
            $this->set($data);
        }
        
        $values = array_values($this->_dirty_fields);
        $field_list = array();

        if (count($values) == 0){
            return false;
        }

        foreach (array_keys($this->_dirty_fields) as $key) {
            $field_list[] = "{$key} = ?";
        }

        $query  = "UPDATE {$this->table_name} SET ";
        $query .= implode(", ",$field_list);
        $query .= $this->getWhereString();

        $values = array_merge($values, $this->getWhereParameters());

        $this->query($query, $values, false);
        
        // Return the SQL Query
        if ($this->debug_sql_query) {
            $this->debugSqlQuery(false);
            return false;
        } else {
            $this->_dirty_fields = array();
            return false;            
        }
    }

/*------------------------------------------------------------------------------
                                Delete
*-----------------------------------------------------------------------------*/    
    /**
     * Delete rows
     * Use the query builder to create the where clause
     *
     * @return int - total affected rows
     */
    public function delete()
    {
        $this->setSingleWhere();

        $query  = "DELETE FROM {$this->table_name}";
        $query .= $this->getWhereString();

        $q = $this->query($query, $this->getWhereParameters(), false);
        
        // Return the SQL Query
        if ($this->debug_sql_query) {
            $this->debugSqlQuery(false);
            return false;
        } else {
           return $this->rowCount(); 
        }
    }
    
/*------------------------------------------------------------------------------
                                Set & Save
*-----------------------------------------------------------------------------*/
    /**
     * To set data for update or insert
     * $key can be an array for mass set
     *
     * @param  mixed    $key
     * @param  mixed    $value
     * @return Voodoo\VoodOrm 
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            $this->_data = array_merge($this->_data, $key);
            $this->_dirty_fields = array_merge($this->_dirty_fields, $key);
        } else {
            $this->_data[$key] = $value;
            $this->_dirty_fields[$key] = $value;
        }
        return $this;
    }

    /**
     * Save, a shortcut to update() or insert().
     * 
     * @return mixed 
     */
    public function save() 
    {
        if (! $this->where_conditions || ! $this->is_single) {
            return $this->insert($this->_data);
        } else {
            return $this->update();
        }
    }    


/*------------------------------------------------------------------------------
                                AGGREGATION
*-----------------------------------------------------------------------------*/
    
    /**
     * Return the aggregate count of column
     *
     * @param  string $column - the column name
     * @return double
     */
    public function count($column="*")
    {
        return $this->aggregate("COUNT({$column})");
    }

    /**
     * Return the aggregate max count of column
     *
     * @param  string $column - the column name
     * @return double
     */
    public function max($column)
    {
        return $this->aggregate("MAX({$column})");
    }


    /**
     * Return the aggregate min count of column
     *
     * @param  string $column - the column name
     * @return double
     */
    public function min($column)
    {
        return $this->aggregate("MIN({$column})");
    }

    /**
     * Return the aggregate sum count of column
     *
     * @param  string $column - the column name
     * @return double
     */
    public function sum($column)
    {
        return $this->aggregate("SUM({$column})");
    }

    /**
     * Return the aggregate average count of column
     *
     * @param  string $column - the column name
     * @return double
     */
    public function avg($column)
    {
        return $this->aggregate("AVG({$column})");
    }

    /**
     *
     * @param  string $fn - The function to use for the aggregation
     * @return double
     */
    public function aggregate($fn)
    {
        $this->select($fn, 'count');
        $result = $this->findOne();

        return ($result !== false && isset($result->count)) ? $result->count : 0;
    }

/*------------------------------------------------------------------------------
                                Access single entry data
*-----------------------------------------------------------------------------*/
    /**
     * Return the primary key
     *
     * @return int
     */
    public function getPK()
    {
        return $this->get($this->primary_key_name);
    }

    /**
     * Get the a key
     *
     * @param  string $key
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    /**
     * Return the raw data of this single instance
     *
     * @return Array
     */
    public function toArray()
    {
        return $this->_data;
    }

    
    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }    

/*******************************************************************************/

    /**
     * To dynamically call a table
     *
     * $VoodOrm = new VoodOrm($myPDO);
     * on table 'users'
     * $Users = $VoodOrm->users();
     *
     * Or to call a table relationship
     * on table 'photos' where users can have many photos
     * $allMyPhotos = $Users->findOne(1234)->photos();
     *
     * On relationship, it is faster to do eager load (VoodOrm::REL_HASONE | VoodOrm::REL_HASMANY)
     * All the data are loaded first than queried after. Eager load does one round to the table.
     * Lazy load will do multiple round to the table.
     *
     * @param  string $tableName
     * @param  string $arg
     * @return type
     */
    public function __call($tableName,$args)
    {
        /**
         * On single object we'll create a relationship to the table called
         * i.e:
         *  tablename(INT REL_TYPE, STRING foreign_key_name, ARRAY $whereArgs, Closure $callback)
         *
         * or
         *  tablename(foreign_key_name)
         *
         * or
         *  tablename(array("name"=>"hello"))
         *
         * or
         *  tablename(function(res){return $res});
         */
        if ($this->is_single) {

            $relationship = self::REL_HASMANY;
            $foreignKeyN = "";
            $whereCondition = null;
            $callback = null;

            /**
             * Assign vars. Any position should work, but it would be best
             * if you follow:
             * tablename(INT REL_TYPE, STRING foreign_key_name, ARRAY $whereArgs)
             */
            do {
                if (isset($args[0])) {
                    if (($args[0] === self::REL_HASONE) || ($args[0] === self::REL_LAZYONE) ||
                        ($args[0] === self::REL_HASMANY) || ($args[0] === self::REL_LAZYMANY)
                    ){
                        $relationship = $args[0];
                    } else if (is_string($args[0])) {
                        $foreignKeyN = $args[0];
                    } else if (is_array($args[0])){
                        $whereCondition = $args[0];
                    } else if ($args[0] instanceof Closure) {
                        $callback = $args[0];
                    }                    
                }

                 
                array_shift($args);

            } while (count($args));

            $foreignKeyN = $this->formatTableKeyName($foreignKeyN ?: $this->table_structure["foreignKeyName"],
                                                        $tableName);

            switch ($relationship) {
                /**
                 * By default OneToMany
                 * OneToMany : Eager Load.
                 * All data will be loaded. Only does one round to the db table
                 * Efficient and faster
                 */
                default:
                case self::REL_HASMANY:

                    $primaryKeyN = $this->primary_key_name;
                    $foreignKeyN = $this->foreign_key_name;

                    $token = $this->tokenize($tableName,$foreignKeyN.":".$relationship);

                    // Voodoo
                    if (!isset(self::$references[$token])) {

                        $newInstance = $this->table($tableName);

                        $primaryKeys = array_unique(array_map(function($r) use ($primaryKeyN) {
                            return $r[$primaryKeyN];
                        },$this->allRows));

                        $newInstance->where($this->foreign_key_name,$primaryKeys);
                        if(is_array($whereCondition)){
                            $newInstance->where($whereCondition);
                        }

                        self::$references[$token] = $newInstance->find(function($rows) use ($newInstance,$foreignKeyN,$callback) {
                            $results = array();
                            foreach ($rows as $row) {
                                if(!isset($results[$row[$foreignKeyN]])){
                                    $results[$row[$foreignKeyN]] = new ArrayIterator;
                                }

                                $results[$row[$foreignKeyN]]->append(is_callable($callback)
                                                                     ? $callback($row) : $newInstance->_toRow($row));
                            }

                            return $results;
                        });
                    }

                    return isset(self::$references[$token][$this->{$primaryKeyN}])
                                ? self::$references[$token][$this->{$primaryKeyN}] : false;

                break;

                /**
                 * OneToMany: Lazy Load
                 * Data loaded upon request. Will take multiple rounds the table
                 */
                case self::REL_LAZYMANY:
                    $newInstance = $this->table($tableName)
                                        ->where($this->foreign_key_name,$this->getPK());
                    if(is_array($whereCondition)){
                        $newInstance->where($whereCondition);
                    }

                    return is_callable($callback) ? $callback($newInstance) : $newInstance;
                break;

                /**
                 * OneToOne: Eager Load
                 * All data will be loaded. Only does one round to the db table
                 * Efficient and faster
                 */
                case self::REL_HASONE:

                    if (isset($this->{$foreignKeyN})) {

                        $token = $this->tokenize($tableName,$foreignKeyN.":".$relationship);

                        // Voodoo
                        if (!isset(self::$references[$token])) {

                            $newInstance = $this->table($tableName);

                            $newInstance->foreign_key_name = $foreignKeyN;

                            $foreignKeys = array_unique(array_map(function($r) use ($foreignKeyN) {
                                return $r[$foreignKeyN];
                            },$this->allRows));

                            $newInstance->where($newInstance->primary_key_name,$foreignKeys);
                            if(is_array($whereCondition)){
                                $newInstance->where($whereCondition);
                            }

                            self::$references[$token] = $newInstance->find(function($rows) use ($newInstance,$callback) {
                               $results = array();
                               foreach ($rows as $row) {
                                    $results[$row[$newInstance->primary_key_name]] =  is_callable($callback)
                                                                                ? $callback($row)
                                                                                : $newInstance->_toRow($row);
                               }

                               return $results;
                            });
                        }

                        return self::$references[$token][$this->{$foreignKeyN}];
                    } else {
                        return null;
                    }

                break;

                /**
                 * OneToOne: Lazy Load
                 * Data loaded upon request. Will take multiple rounds the table
                 */
                case self::REL_LAZYONE:
                    $newInstance = $this->table($tableName)
                                        ->wherePK($this->{$foreignKeyN});
                    if(is_array($whereCondition)){
                        $newInstance->where($whereCondition);
                    }

                    $one = $newInstance->findOne();

                    return is_callback($callback) ? $callback($one) : $one;
                break;
            }
        } else {
            return $this->table($tableName);
        }

    }

/*******************************************************************************/
// Utilities methods

    /**
     * Reset fields
     *
     * @return Voodoo\VoodOrm 
     */
    public function reset()
    {
        $this->where_parameters = array();
        $this->select_fields = array('*');
        $this->join_sources = array();
        $this->where_conditions = array();
        $this->limit = null;
        $this->offset = null;
        $this->order_by = array();
        $this->group_by = array();
        $this->_data = array();
        $this->_dirty_fields = array();
        $this->is_fluent_query = true;
        $this->where_operator = self::WHERE_OPERATOR_AND;
        $this->wrap_open = false;
        $this->last_wrap_position = 0;
        $this->debug_sql_query = false;
        return $this;
    }

    /**
     * Return the date in datetime format
     *
     * @return string YYYY-MM-DD HH:II:SS
     */
    public static function DateTime()
    {
        return date("Y-m-d H:i:s");
    }



/*******************************************************************************/
// Query Debugger
    
    /**
     * To debug the query. It will not execute it but instead using debugSqlQuery()
     * and getSqlParameters to get the data
     * 
     * @param bool $bool
     * @return Voodoo\VoodOrm 
     */
    public function debugSqlQuery($bool = true)
    {
        $this->debug_sql_query = $bool;
        return $this;
    }
    
    /**
     * Get the SQL Query with 
     * 
     * @return string 
     */
    public function getSqlQuery()
    {
        return $this->sql_query;
    }
    
    /**
     * Return the parameters of the SQL
     * 
     * @return array
     */
    public function getSqlParameters()
    {
        return $this->sql_parameters;
    }
    
    /**
     * To profile all queries that have been executed
     *
     * @return Array
     */
    public function getQueryProfiler()
    {
        return $this->query_profiler;
    }
/*******************************************************************************/
    /**
     * Return a string containing the given number of question marks,
     * separated by commas. Eg "?, ?, ?"
     *
     * @param int - total of placeholder to inser
     * @return string
     */
    protected function makePlaceholders($number_of_placeholders=1)
    {
        return implode(", ", array_fill(0, $number_of_placeholders, "?"));
    }

    /**
     * Format the table{Primary|Foreign}KeyName
     *
     * @param  string $pattern
     * @param  string $tableName
     * @return string
     */
    protected function formatTableKeyName($pattern, $tableName)
    {
       return sprintf($pattern,$tableName);
    }

    /**
     * To create a string that will be used as key for the relationship
     *
     * @param  type   $key
     * @param  type   $suffix
     * @return string
     */
    private function tokenize($key, $suffix = "")
    {
        return  $this->table_token.$key.$suffix;
    }

    public function __clone()
    {
    }
    
    public function __toString()
    {
        return $this->is_single ? $this->getPK() : $this->table_name;
    }    
}
