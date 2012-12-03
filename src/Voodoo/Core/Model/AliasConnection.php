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
 * @name        Model\AliasConnection
 * @desc        Create a single instance of a db connection based on DBAlias
 *
 */

namespace Voodoo\Core\Model;

use Voodoo\Core,
    PDO;

class AliasConnection
{
    /**
     * Holds all the connection
     * @var type
     */
    private static $dbConnections = [];

    /**
     * To establish the connection based on the DBAlias provided in the DB.ini
     * It will only connects to the db once, then
     *
     * @param  string               $dbAlias
     * @return \PDO|\Redisent\Redis
     * @throws Core\Exception
     */
    public static function connect($dbAlias)
    {
        if (!isset(self::$dbConnections[$dbAlias])) {

            $db = Core\Config::DB()->get($dbAlias);

            if(!is_array($db)){
                throw new Core\Exception("Database Alias: {$dbAlias} config doesn't exist.");
            }
            if (preg_match("/mysql|pgsql|sqlite|mongodb|redis/i",$db["dbms"])) {

                switch (strtolower($db["dbms"])) {

                    case "mysql":
                    case "pgsql":
                    case "sqlite":

                        $dbms = strtolower($db["dbms"]);
                        $port = (isset($db["port"]) && $db["port"]) ? ";port={$db["port"]}" : "";
                        if ($dbms == "sqlite"){
                            $PDO = new PDO("sqlite:".APPLICATION_DB_PATH."/{$db["dbName"]}.{$db["DBFileExt"]}");
                        } else if ($dbms == "mysql") {
                            $PDO = new PDO("mysql:host={$db["host"]};dbname={$db["dbName"]}{$port}",$db["user"],$db["password"]);
                        } else if ($dbms == "pgsql") {
                            $PDO = new PDO("pgsql:host={$db["host"]};dbname={$db["dbName"]}{$port}",$db["user"],$db["password"]);
                        }
                        
                        $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        self::$dbConnections[$dbAlias] = $PDO;
                    break;

                    // @FIXME: TO TEST WITH MONGO
                    case "mongodb":
                        $Options = array();
                        if($db["username"]){
                            $Options["username"] = $db["user"];
                        }
                        if($db["password"]){
                            $Options["password"] = $db["password"];
                        }
                        if($db["replicaset"]){
                            $Options["replicaset"] = true;
                        }
                        
                        $Mongo = new Mongo($db["host"],$Options);
                        if($db["slaveOk"]){
                            $Mongo->slaveOkay(true);
                        }

                        self::$dbConnections[$dbAlias] = $Mongo->selectDB($db["dbName"]);
                    break;

                    // @FIXME: TO TEST WITH REDISENT
                    case "redis":
                        $host = $db["host"].($db["Port"] ? ":{$db["Port"]}" : "");
                        $Redis = new Redis($host);
                        $Redis->setDB($db["DbNumber"]);
                        self::$dbConnections[$dbAlias] = $Redis;
                    break;
                }
            } else {
                throw new Core\Exception("Invalid dbms for Alias: '{$dbAlias}'. dbms: {$db["dbms"]} was provided. Must be MySQL, PostgreSQL, SQLite, MongoDB, Redis ");
            }
        }

        return self::$dbConnections[$dbAlias];
    }

}
