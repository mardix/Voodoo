<?php

/**
 * Allow us to create a
 *
 * @author mardix
 * 
 * ie:
 * $schema = [
 *  [
 *      "name" => "id",
 *      "type" => "id"
 *  ],
 *  [
 *      "name" => "name",
 *      "type" => "string"
 *  ]
 * ];
 * 
 * $ts = new Voodoo\Core\SchemaBuilder($schema);
 * $sql = $ts->create("user");
 */
namespace Voodoo\Core;

class SchemaBuilder
{
    const VERSION = 0.1;
            
    private $typeNumerics = ["int", "tinyint", "smallint", "mediumint", "bigint", "float", "double", "decimal"];
    private $typeStrings = ["char", "varchar", "text", "tinytext", "mediumtext", "longtext"];
    protected $createColumformat = "`{name}` {type}{length} {unsigned} {allow_null} {default} {auto_increment} {extra}";
    
    private $fields = [
        "name" => null, 
        "type" => null, 
        "length" => 0, 
        "unsigned" => false, 
        "allow_null" => true, 
        "default" => null, 
        "auto_increment" => false, 
        "primary_key" => false, 
        "index" => false,
        "unique" => false,
        "extra" => null
    ];
    protected $alias = [
        "id" => [
            "type" => "int",
            "length" => 10,
            "unsigned" => true,
            "allow_null" => false,
            "auto_increment" => true,
            "primary_key" => true,
        ],
        "string" => [
            "type" => "varchar",
            "length" => 250,
            "default" => ""
        ],
        "text" => [
            "type" => "mediumtext",
            "default" => ""
        ],
        "number" => [
            "type" => "int",
            "length" => 10,
            "unsigned" => true,
            "allow_null" => true,
            "default" => 0
        ],
        "decimal_number" => [
            "type" => "decimal",
            "length" => "12,4",
            "unsigned" => false,
            "default" => "0"
        ],
        "bool" => [
            "type" => "tinyint",
            "length" => 1,
            "unsigned" => false,
            "allow_null" => false,
            "index" => true,
            "default" => "0"
        ],
         "dt" => [ // datetime
            "type" => "datetime",
        ],
         "ts" => [ // timestamp
            "type" => "datetime",
            "extra" => "ON UPDATE CURRENT_TIMESTAMP",
            "default" => "CURRENT_TIMESTAMP"
        ],
    ];
    
    private $schema = [];
    private $engine = "InnoDB";
    
    public function __construct(Array $schema, $engine = "InnoDB")
    {
        $this->schema = array_map([$this, "prepareProperties"], $schema);
        $this->engine = $engine;
    }
    
    
    private function isTypeString($type) 
    {
        return in_array(strtolower($type), $this->typeStrings);
    }
    
    private function isTypeNumeric($type) 
    {
        return in_array(strtolower($type), $this->typeNumerics);
    }
    
    private function prepareProperties(Array $properties)
    {
        if (in_array(strtolower($properties["type"]), array_keys($this->alias))) {
            $_prop = $this->alias[strtolower($properties["type"])];
            unset($properties["type"]);
            $properties = array_merge($this->fields, $_prop, $properties);    
        } else {
            $properties = array_merge($this->fields, $properties);
        }
        return $properties;
    }
    
    protected function addColumn(Array $properties)
    {
        if (! $properties["name"]) {
            throw new \Exception("A column name is required");
        } 
        if (! $properties["type"]) {
            throw new \Exception("A column type is required");
        }
        $type = $properties["type"];
        $allowNull = $properties["allow_null"];
        $default = $properties["default"];
        $properties["type"] = strtoupper($properties["type"]);
        $properties["length"] = $properties["length"] ? "({$properties["length"]})" : "";
        $properties["allow_null"] = $properties["allow_null"] ? "NULL" : "NOT NULL";
        $properties["default"] = $properties["default"] ? "DEFAULT '{$properties["default"]}'" : "";
        
        if ($this->isTypeString($type)) {
            if ($allowNull) {
                $properties["default"] = "DEFAULT '" . ($default ?: "") . "'";
            }
            $properties["allow_null"] = $allowNull ? "NULL" : "NOT NULL";
        }
        
        if ($this->isTypeNumeric($type)) {
            if ($allowNull) {
                $properties["default"] = "DEFAULT " . ($default ?: "NULL");
            }
            $properties["allow_null"] = $allowNull ? "NULL" : "NOT NULL";
        }
        
        $properties["unsigned"] = ($properties["unsigned"] && $this->isTypeNumeric($properties["type"])) 
                                    ? "UNSIGNED" : "";

        $properties["auto_increment"] = ($properties["auto_increment"] && $this->isTypeNumeric($properties["type"])) 
                                    ? "AUTO_INCREMENT" : ""; 
        $column = [];
        foreach($properties as $key => $value) {
            $column["{{$key}}"] = $value;
        }
        return str_replace(array_keys($column), array_values($column), $this->createColumformat);
    }
    
    protected function createConstraintKeys(Array $properties)
    {
        $keys = [];
        if ($properties["primary_key"]) {
            $keys[] = "PRIMARY KEY (`{$properties["name"]}`)";
        }
        if ($properties["unique"]) {
            $keys[] = "UNIQUE KEY `u__{$properties["name"]}` (`{$properties["name"]}`)";
        }    
        if ($properties["index"]) {
            $keys[] = "KEY `idx__{$properties["name"]}` (`{$properties["name"]}`)";
        }  
        return implode(",\n", $keys);
    }
    
    /**
     * Create  the SQL table query based on the schema
     * 
     * @param string $tableName
     * @return string
     */
    public function create($tableName) 
    {
        $cols = array_map([$this, "addColumn"], $this->schema);
        $keys = array_filter(array_map([$this, "createConstraintKeys"], $this->schema));
        $createTable = "CREATE TABLE IF NOT EXISTS `{$tableName}`\n";
        $createTable .= "(\n";
        $createTable .= implode(",\n", $cols);
        if (count($keys)) {
            $createTable .= ",\n";
            $createTable .= implode(",\n", $keys);
        }
        $createTable .= "\n) ENGINE={$this->engine} DEFAULT CHARSET=utf8;";
        return $createTable;
    }
    
    /**
     * 
     * @param \Voodoo\Core\Model $model
     */
    public function alter(Model $model)
    {}
}

