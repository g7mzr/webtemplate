<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\db;

use webtemplate\db\driver\InterfaceDatabaseDriver;

/**
 * DB_DRIVER_PGSQL Class is the class for the pgsql database drivers.  It implements
 * the DBDRIVERIF interface to provide access to the PGSQL database via PDO
 *
 * @category Webtemplate
 * @package  Database
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

class DatabaseDriverpgsql implements InterfaceDatabaseDriver
{
    /**
     * Database MDB2 Data Source Name
     *
     * @var    array
     * @access protected
     */
    protected $dsn  = array();

    /**
     * An associative array of MDB2 option names and their values.
     *
     * @var    array
     * @access protected
     */
    protected $dsnOptions = array();

    /**
     * A PDO Database Object.
     *
     * @var    object
     * @access protected
     */
    protected $pdo;

    /**
     * PDO Statement object.  USed when preparing SQL scripts
     *
     * @var    string
     * @access protected
     */
    protected $stmt;

    /**
     *  SQL text variable used when preparing SQL scripts
     *
     * @var    string
     * @access protected
     */
    protected $sql;

    /**
     * property: rowcount
     * @var integer
     * @access protected
     */
    protected $rowcount = 0;

    /**
     * PGSQL Driver Class Constructor
     *
     * Sets up the PGSQL Driver dsn from the calling function
     * and any PDO specific options.
     *
     * @param array $dsn an array containing the database connection details.
     *
     * @access public
     */
    public function __construct($dsn)
    {
        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s",
            $dsn["hostspec"],
            '5432',
            $dsn["database"]
        );

        // Create the PDO object and Connect to the database
        try {
            if (php_sapi_name() === 'cli') {
                $this->pdo = new \PDO(
                    $conStr,
                    $dsn["username"],
                    $dsn["password"],
                    array(\PDO::ATTR_PERSISTENT => true)
                );
            } else {
                $this->pdo = new \PDO(
                    $conStr,
                    $dsn["username"],
                    $dsn["password"]
                );
            }
        } catch (\Exception $e) {
            throw new \Exception('Unable to connect to the database');
        }
        //$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
    } // end constructor

    /**
     * DB Driver Destructor
     *
     * Disconnect the MDB2 data object from the database
     */
    public function __destruct()
    {
        $this->stmt = null;
        $this->pdo = null;
    }


    /**
     **************************************************************************
     **********  THIS SECTION OF THE FILE CONTAINS GENERAL FUNCTIONS  *********
     **************************************************************************
     */

    /**
     * Function to get the database version
     *
     * This function starts a Database Transaction
     *
     * @return string database Version
     *
     * @access public
     */
    public function getDBVersion()
    {
        $databaseversion = gettext("Error Getting Database Version");
        $this->sql = "SELECT version()";
        $this->stmt = $this->pdo->prepare($this->sql);
        $resultId = $this->stmt->execute();
        if ($resultId !== false) {
            // The version sql statement has run okay
            if ($this->stmt->rowCount() > 0) {
                $uao = $this->stmt->fetch(\PDO::FETCH_ASSOC, 0);
                $versiontext = chop($uao['version']);
                $versionarray = explode(" ", $versiontext);
                $databaseversion = $versionarray[0] . " " . $versionarray[1];
            }
        }

        return $databaseversion;
    }

    /**
     * Function to start a database transaction
     *
     * This function starts a Database Transaction
     *
     * @return boolean true if transaction is started
     *
     * @access public
     */
    public function startTransaction()
    {
        $this->stmt = $this->pdo->prepare("BEGIN TRANSACTION");
        $resultID = $this->stmt->execute();
        if ($resultID !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to end a database transaction
     *
     * This function ends a Database Transaction by eithe committing or rolling
     * back the transaction based on the value of $commit
     *
     * @param boolean $commit Commmit transiaction if true, rollback otherwise.
     *
     * @return boolean true if transaction is started
     *
     * @access public
     */
    public function endTransaction($commit)
    {
        if ($commit == true) {
            $this->stmt = $this->pdo->prepare("COMMIT");
            $resultID = $this->stmt->execute();
            return true;
        } else {
            $this->stmt = $this->pdo->prepare("ROLLBACK");
            $resultID = $this->stmt->execute();
            return false;
        }
    }

    /**
     * This function returns the last insert id for the selected table
     *
     * @param string $tableName The name of the table data was inserted to
     * @param string $idfield   The name of the id field the table
     * @param string $srchfield The name of the field where the sreach data is saved
     * @param string $srchdata  The unique name entered in to the field
     *
     * @return integer The id of the last record inserted or WEBTEMPLATE error type
     * @access public
     */
    public function dbinsertid($tableName, $idfield, $srchfield, $srchdata)
    {
        $result = -1;
        $this->sql = "SELECT $idfield FROM $tableName WHERE ";
        $this->sql .= "$srchfield = '$srchdata'";
        $this->stmt = $this->pdo->prepare($this->sql);
        $sqlresult = $this->stmt->execute();
        if ($sqlresult !== false) {
            $uao = $this->stmt->fetch(\PDO::FETCH_ASSOC, 0);
            $result = $uao[$idfield];
        } else {
            $msg = gettext("Error getting record ID.");
            return \webtemplate\general\General::raiseError($msg, DB_ERROR);
        }
        return $result;
    }

    /**
     * This function disconnects from the database
     *
     * @return boolean True
     */
    public function disconnect()
    {
        $this->stmt = null;
        $this->pdo = null;
        return true;
    }

    /**
     **************************************************************************
     ******************  END OF THE GENERAL FUNCTIONS SECTION   ***************
     **************************************************************************
     */

    /**
     **************************************************************************
     *****  THIS SECTION OF THE FILE CONTAINS DATABASE CREATION FUNCTIONS *****
     **************************************************************************
     */

    /**
     * Function to translate Column types from default schema
     *
     * @param string $columntype Type of database column to be translated
     *
     * @return string translated column type
     *
     * @access public
     */
    public function translateColumn($columntype)
    {
        switch ($columntype) {
            case "DATETIME":
                return "timestamp(0) without time zone";
                break;
            default:
                return $columntype;
        }
        return $columntype;
    }

    /**
     * Function to create a Foreign Key
     * keydata[0] = Table Name to be amended
     * keydata[1] = FK Name (fk_table_Column)
     * keydata[2] = column FK is to be added to
     * keydata[3] =  column data on target table (table(column))
     *
     * @param array $keydata An array containing the Foreign Key Data
     *
     * @return boolean true if Foreign Key Created WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function createFK($keydata)
    {
        $this->dbEcho("Creating Foreign Key " . $keydata[1]);
        $this->sql = "ALTER TABLE ONLY " . $keydata[0] . " ADD CONSTRAINT";
        $this->sql .=  " " . $keydata[1] . " FOREIGN KEY (" . $keydata[2] . ")";
        $this->sql .= " REFERENCES " . $keydata[3];
        $this->sql .= " ON DELETE CASCADE";
        $result = $this->pdo->exec($this->sql);

        // Always check that result is not an error
        if ($result === false) {
            $msg = gettext("Error Creating Foreign Keys.");
            return \webtemplate\general\General::raiseError($msg, DB_ERROR);
        }
        return true;
    }

     /**
     * Function to drop a Foreign Key
     *
     * @param string $tableName The name of the table being worked on
     * @param string $keyName   The name of the Foreign Key being dropped
     *
     * @return boolean true if Forigen Key dropped WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function dropFK($tableName, $keyName)
    {

        $this->dbEcho("Dropping Foreign Key $keyName");
        $this->sql = "ALTER TABLE ONLY $tableName DROP CONSTRAINT $keyName CASCADE";
        $affected = $this->pdo->exec($this->sql);

        if ($affected === false) {
            $errorMsg = "Error deleteting Foreign Key $keyName on $tableName\n";
            return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
        }
        return true;
    }

     /**
     * Function to create a Unique Constraint on a column
     *
     * @param array $keydata An array containing the column data
     *
     * @return boolean true if constraint Created WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function createUniqueConstraint($keydata)
    {
        $this->dbEcho(
            "Creating Unique " . $keydata[0] ."_" . $keydata[1] . "_key "
        );
        $constraintsaved = false;
        $this->sql = "ALTER TABLE " . $keydata[0];
        $this->sql .= " ADD CONSTRAINT " .$keydata[0] ."_" . $keydata[1] . "_key ";
        $this->sql .= "UNIQUE (" . $keydata[1] . ")";
        $result = $this->pdo->exec($this->sql);

        // Always check that result is not an error
        if ($result !== false) {
            $constraintsaved = true;
        }
        if ($constraintsaved == true) {
            return true;
        } else {
            $msg = gettext("Error Creating Unique Constraints.");
            return \webtemplate\general\General::raiseError($msg, DB_ERROR);
        }
    }

    /**
     * Function to create an index
     *
     * @param string $tablename The name of the table being indexed
     * @param string $indexname The Name of the Index.
     * @param array  $indexdata An array containing the index Data
     *
     * @return boolean true if index Created WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function createIndex($tablename, $indexname, $indexdata)
    {
        $this->dbEcho("Creating Index $indexname");
        $indexsaved = true;
        $this->sql = "CREATE";

        // Set  UNIQUE
        if (array_key_exists('UNIQUE', $indexdata)) {
            if ($indexdata['UNIQUE'] == '1') {
                $this->sql .= " UNIQUE";
            }
        }
        $this->sql .= " INDEX " . $indexname . " ON " . $tablename;
        $this->sql .= " USING btree (" . $indexdata['COLUMN'] . ")";

        $result = $this->pdo->exec($this->sql);

        // Always check that result is not an error
        if ($result === false) {
            $indexsaved = false;
        }
        if ($indexsaved == true) {
            return true;
        } else {
            $msg = gettext("Error Creating Index.");
            return \webtemplate\general\General::raiseError($msg, DB_ERROR);
        }
    }

     /**
     * Function to drop indexes for a table
     *
     * @param string $tableName The name of the table being changed
     * @param string $indexName The name of the index being dropped
     *
     * @return boolean true if index Created WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function dropIndex($tableName, $indexName)
    {
        $this->dbEcho("Dropping Index $indexName");

        $this->sql = "DROP INDEX IF EXISTS " . $indexName . " CASCADE";

        $result = $this->pdo->exec($this->sql);

        // Always check that result is not an error
        if ($result === false) {
            $msg = gettext("Error Dropping Index ");
            $msg .= $tableName .":" .$indexName . "\n";
            return \webtemplate\general\General::raiseError($msg, DB_ERROR);
        }
        return true;
    }

    /**
     * Function to create the database user for the application
     *
     * @param string $username   The name of the database user
     * @param string $password   The password for the database user
     * @param string $unittestdb True if this is a test system
     *
     * @return boolean true if user Created or exists WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function createUser($username, $password, $unittestdb)
    {
        $checkuserOk = false;
        $userExists = false;
        $this->sql = "select rolname from pg_roles where rolname = '$username'";
        $this->stmt = $this->pdo->prepare($this->sql);
        $sqlresult = $this->stmt->execute();
        if ($sqlresult !== false) {
            if ($this->stmt->rowCount() > 0) {
                $userExists = true;
                $checkuserOk = true;
            } else {
                $this->sql = " Create User $username with";
                if ($unittestdb == true) {
                    $this->sql .= " CREATEDB CREATEROLE";
                }
                $this->sql .= " encrypted password '$password'";
                $affected = $this->pdo->exec($this->sql);
                if ($affected !== false) {
                    $checkuserOk = true;
                }
            }
        }
        if ($checkuserOk == true) {
            return true;
        } else {
            $msg = gettext("Error Creating Database User");
            return \webtemplate\general\General::raiseError($msg, DB_ERROR);
        }
    }

     /**
     * Function to drop the database user for the application
     *
     * @param string $username The name of the database user
     *
     * @return boolean true if user does not exist or is dropped otherwise
     *                 WEBTEMPLATE Error
     *
     * @access public
     */
    public function dropUser($username)
    {
        $checkuserOk = false;
        $this->sql = "select rolname from pg_roles where rolname = '$username'";
        $this->stmt = $this->pdo->prepare($this->sql);
        $sqlresult = $this->stmt->execute();
        if ($sqlresult !== false) {
            if ($this->stmt->rowCount() > 0) {
                $this->sql = "Drop Role $username ";
                $affected = $this->pdo->exec($this->sql);
                if ($affected !== false) {
                    $checkuserOk = true;
                }
            }
        }
        if ($checkuserOk == true) {
            return true;
        } else {
            $msg = gettext("Error Dropping Database User");
            return \webtemplate\general\General::raiseError($msg, DB_ERROR);
        }
    }


    /**
     * Function to drop the database for the application
     *
     * @param string $database The name of the database
     *
     * @return boolean true if database exists WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function databaseExists($database)
    {
        $msg = '';
        $databaseExists = false;
        $this->sql = "select pg_database.datname from pg_database";
        $this->sql .= " where pg_database.datname = '$database'";

        $this->stmt = $this->pdo->prepare($this->sql);
        $result = $this->stmt->execute();
        if ($result === false) {
            $msg = gettext("Error checking the database exists") . "\n";
            return \webtemplate\general\General::raiseError($msg, DB_ERROR);
        } else {
            if ($this->stmt->rowCount() > 0) {
                $databaseExists = true;
            }
        }

        return $databaseExists;
    }


     /**
     * Function to create the database for the application
     *
     * @param string $database The name of the database
     * @param string $username The name of the database user
     *
     * @return boolean true if database Created or exists WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function createDatabase($database, $username)
    {

        $msg = '';
        $dbcheckok = true;
        $databaseCreated = false;

        $this->sql = "CREATE DATABASE " . $database;
        $this->sql .= " with owner = " .  $username;
        $this->stmt = $this->pdo->prepare($this->sql);
        $createDataBase = $this->stmt->execute();
        if ($createDataBase === false) {
            $dbcheckok = false;
            $msg .= gettext("Error Creating New Database") . "\n";
        } else {
            $databaseCreated = true;
        }
        if ($dbcheckok == true) {
            return $databaseCreated;
        } else {
            $msg2 = gettext("Error Creating the database") . "\n" . $msg;
            return \webtemplate\general\General::raiseError($msg2, DB_ERROR);
        }
    }

     /**
     * Function to drop the database for the application
     *
     * @param string $database The name of the database
     *
     * @return boolean true if database Created or exists WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function dropDatabase($database)
    {

        $msg = '';
        $dbcheckok = false;

        $this->sql = "select pg_database.datname from pg_database";
        $this->sql .= " where pg_database.datname = '$database'";
        $this->stmt = $this->pdo->prepare($this->sql);
        $result = $this->stmt->execute();
        if ($result !== false) {
            if ($this->stmt->rowCount() > 0) {
                $this->sql = "DROP DATABASE IF EXISTS $database";
                $this->stmt = $this->pdo->prepare($this->sql);
                $createtable = $this->stmt->execute();
                if ($createtable !== false) {
                    $dbcheckok = true;
                }
            }
        }
        if ($dbcheckok == true) {
            return true;
        } else {
            $msg .= gettext("Error Dropping Database") . "\n";
            return \webtemplate\general\General::raiseError($msg, DB_ERROR);
        }
    }

    /**
     * Function to create table SQL
     *
     * @param string $tableName The name of the table being created
     * @param string $tabledef  Array containing table details
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function createtable($tableName, $tabledef)
    {

        $this->dbEcho("Creating Table $tableName");
        $primaryKey = '';
        $foreignkeys = array();
        $uniquecolumns = array();
        //$gotconstraints = false;
        //$indexes = array();
        //$gotindexes = false;
        //$dataerror = false;
        $errorMsg = '';

        $this->sql = "CREATE TABLE " . $tableName . " (";

        $noOfColumns = count($tabledef['COLUMNS']);
        $counter = 1;
        foreach ($tabledef['COLUMNS'] as $key => $column) {
            $this->sql .= $this->processColumn(
                $tableName,
                $key,
                $column,
                $primaryKey,
                $foreignkeys,
                $uniquecolumns
            );


            if ($counter < $noOfColumns) {
                $this->sql .= ", ";
            }
            $counter++;
            //print_r($column);
        }

        if ($primaryKey != '') {
            $this->sql .= ", PRIMARY KEY ($primaryKey)";
        }

        $this->sql .= ")";

        //echo $this->sql . "\n\n"; $affected = true;
        $affected = $this->pdo->exec($this->sql);


        if ($affected  === false) {
            $errorMsg .= "Error Creating the Table\n";
            $err = \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
            return $err;
        }

        // Process the Table Constraints

        $result = $this-> processForeignKeys($foreignkeys);
        if (\webtemplate\general\General::isError($result)) {
            $errorMsg .= $result->getMessage();
            $err = \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
            return $err;
        }


        $result = $this->processUniqueColumn($uniquecolumns);
        if (\webtemplate\general\General::isError($result)) {
            $errorMsg .= $result->getMessage();
            $err = \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
            return $err;
        }


        if (array_key_exists("INDEXES", $tabledef)) {
            $result = $this->processIndexes($tableName, $tabledef['INDEXES']);
            if (\webtemplate\general\General::isError($result)) {
                $errorMsg .= $result->getMessage();
                $err = \webtemplate\general\General::raiseError(
                    $errorMsg,
                    DB_ERROR
                );
                return $err;
            }
        }

        return true;
    }



    /**
     * Function to create SQL to drop a table
     *
     * @param string $tableName The name of the table being droped
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function dropTable($tableName)
    {
        $this->dbEcho("Dropping Table $tableName");
        $this->sql = "DROP TABLE IF EXISTS $tableName CASCADE";
        $affected = $this->pdo->exec($this->sql);

        if ($affected === false) {
            $errorMsg = "Error Dropping Table $tableName\n";
            return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
        }
            return true;
    }



    /**
     * Function to create the SQL to add a column to a table
     *
     * @param string $tableName  The name of the table being altered
     * @param string $columnName The Name of the column being created
     * @param array  $columnData The column structure.
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function addColumn($tableName, $columnName, $columnData)
    {
        $this->dbEcho("Adding Column $tableName:$columnName");
        $dataerror = false;
        $errorMsg = '';
        $gotconstraints = false;
        $foreignkeys = array();
        $uniiquecolumns = array();
        $this->sql = "ALTER TABLE $tableName ADD COLUMN ";


        $this->sql .= $this->processColumn(
            $tableName,
            $columnName,
            $columnData,
            $primaryKey,
            $foreignkeys,
            $uniquecolumns
        );


        $affected = $this->pdo->exec($this->sql);

        if ($affected === false) {
            $errorMsg .= "Error adding column to $tableName\n";
            return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
        }



        $result = $this-> processForeignKeys($foreignkeys);
        if (\webtemplate\general\General::isError($result)) {
            $errorMsg .= $result->getMessage();
            $err = \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
            return $err;
        }


        return true;
    }

    /**
     * Function to create the SQL to drop a column from a table
     *
     * @param string $tableName  The name of the table being altered
     * @param string $columnName The Name of the column being dropped
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function dropColumn($tableName, $columnName)
    {
        $this->dbEcho("Dropping Column $tableName:$columnName");
        $dataerror = false;
        $this->sql = "ALTER TABLE $tableName DROP COLUMN $columnName CASCADE";

        $affected = $this->pdo->exec($this->sql);

        if ($affected === false) {
            $errorMsg = "Error dropping column on $tableName\n";
            return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
        }
        return true;
    }

    /**
     * Function to create the SQL to alter a column in a table
     *
     * @param string $tableName  The name of the table being altered
     * @param string $columnName The Name of the column being created
     * @param array  $newColumn  The new column structure.
     * @param array  $changes    The changes in the column structure.
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function alterColumn($tableName, $columnName, $newColumn, $changes)
    {
        $this->dbEcho("Altering Column $tableName:$columnName");
        $dataerror = false;
        $gotconstraints = false;

        // Drop any foreign keys associated with this column
        $result = $this->processOldForeignKeys($tableName, $columnName, $changes);
        if (\webtemplate\general\General::isError($result)) {
            $errorMsg = gettext("Error dropping Foreign Key") . " on ";
            $errorMsg .= $tableName .":" . $columnName ."\n";
            return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
        }


        $this->sql = "ALTER TABLE ONLY $tableName ALTER COLUMN $columnName";

        // Now Alter the table if the previous alteration worked
        // Alter Column Type
        $result = $this->processColumnType(
            $this->sql,
            $tableName,
            $columnName,
            $changes,
            $newColumn
        );
        if (\webtemplate\general\General::isError($result)) {
            $errorMsg = $result->getMessage();
            $err = \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
            return $err;
        }

        // Alter NOT NULL settingname
        $result = $this->processColumnNotNull(
            $this->sql,
            $tableName,
            $columnName,
            $changes,
            $newColumn
        );
        if (\webtemplate\general\General::isError($result)) {
            $errorMsg = $result->getMessage();
            $err = \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
            return $err;
        }


        // Alter DEFAULT settingname
        $result = $this->processColumnDefaultValue(
            $this->sql,
            $tableName,
            $columnName,
            $changes,
            $newColumn
        );
        if (\webtemplate\general\General::isError($result)) {
            $errorMsg = $result->getMessage();
            $err = \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
            return $err;
        }

        // Set the constraints if they exist
        $foreignkeys = array();
        if (array_key_exists('CONSTRAINTS', $newColumn)) {
            $coldatastr =  $newColumn['CONSTRAINTS']['table'];
            $coldatastr .= "(" . $newColumn['CONSTRAINTS']['column']  . ")";

            $foreignkeys[] = array(
                $tableName,
                "fk_" . $tableName . "_" . $columnName,
                $columnName,
                $coldatastr
            );
            $gotconstraints = true;
        }

        // Add any Foreign Keys to the column
        $result = $this-> processForeignKeys($foreignkeys);
        if (\webtemplate\general\General::isError($result)) {
            $errorMsg = $result->getMessage();
            $err = \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
            return $err;
        }


        return true;
    }


    /**
     * Function to create the SQL to save the current schema
     *
     * @param integer $version The Version of the schema being saved
     * @param array   $schema  The array containing the schema
     * @param array   $groups  The array containing the group list
     * @param string  $table   The schema table.  Default is schema
     *
     * @return mixed true if schema saved or WEBTEMPLATE error
     *
     * @access public
     */
    public function saveSchema($version, $schema, $groups, $table = "schema")
    {
        $schemaSaved = false;

        $this->sql = "DELETE FROM $table";
        $this->stmt = $this->pdo->prepare($this->sql);
        $resultId = $this->stmt->execute();
        if ($resultId !== false) {
            $serial_schema = serialize($schema);
            $serial_groups = serialize($groups);
            $this->sql = "INSERT INTO $table ";
            $this->sql .= "(version, schema, groups) values (?,?,?)";
            $this->stmt = $this->pdo->prepare(
                $this->sql,
                array('decimal', 'text', 'text')
            );
            if (!\webtemplate\general\General::isError($this->stmt)) {
                $data = array($version, $serial_schema, $serial_groups);
                $result = $this->stmt->execute($data);
                if ($result !== false) {
                    $schemaSaved = true;
                } else {
                    $msg = gettext("Error EXECUTING Schema INSERT");
                }
            } else {
                $msg = gettext("Error PREPARING Schema INSERT SQL");
            }
        } else {
            $msg = gettext("Error Deleteting Previous Schema. ");
            //$msg .= $resultId->getMessage();
        }
        if ($schemaSaved == true) {
            return true;
        } else {
            return \webtemplate\general\General::raiseError($msg, DB_ERROR);
        }
    }


    /**
     * Function to retrieve the current schema from the database.
     *
     * @param string $table The table the schema is stored in.  Default is schema
     *
     * @return mixed array containing schema version and data or WEBTEMPLATE error
     *
     * @access public
     */
    public function getSchema($table = 'schema')
    {

        // Set Local Variables
        $esultArray = array();
        // Set the SQL to get the Schema
        $this->sql = "SELECT version, schema, groups from " . $table;
        $this->stmt = $this->pdo->prepare($this->sql);
        $resultId = $this->stmt->execute();
        if ($resultId !== false) {
            if ($this->stmt->rowCount() > 0) {
                // Found a Schema Entry.  Get the data.
                $uao = $this->stmt->fetch(\PDO::FETCH_ASSOC, 0);
                $resultArray['version'] = $uao['version'];
                $resultArray['schema'] = unserialize($uao['schema']);
                $resultArray['groups'] = unserialize($uao['groups']);
            } else {
                $msg = gettext('Schema Not Found');
                $err = \webtemplate\general\General::raiseError(
                    $msg,
                    DB_ERROR_NOT_FOUND
                );
                return $err;
            }
        } else {
            $msg = gettext('SQL Query Error');
            $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
            return $err;
        }
        // Disconnect from the database
        return $resultArray;
    }


    /**
     **************************************************************************
     ******************  END OF THE DATABASE CREATION SECTION   ***************
     **************************************************************************
     */

    /**
     * ****************************************************************************
     * ** START OF THE SECTION OF THE FILE CONTAINING GENERIC DATABASE FUNCTIONS **
     * ****************************************************************************
     */


    /**
     * This function inserts a single record to the database
     *
     * @param string $tableName  The name of the table data is to be inserted to
     * @param array  $insertData The name of the fields and data to be inserted
     *
     * @return boolean True if insert is ok or WEBTEMPLATE error type
     * @access public
     */
    public function dbinsert($tableName, $insertData)
    {

        // Initialise the data array
        $data = array();
        //Build the SQL INSERT Statement
        $this->sql = "INSERT INTO " . $tableName . " (";

        // Add the field names

        $arr_length = count($insertData);
        $current_element = 1;
        foreach ($insertData as $key => $elementData) {
            $this->sql .= $key;
            $paramname = ":". $key;
            $data[$paramname] = $elementData;
            if ($current_element < $arr_length) {
                $this->sql .= ", ";
            }
            $current_element = $current_element + 1;
        }
        $this->sql .= ") VALUES (";

        $arr_length = count($data);
        $current_element = 1;
        foreach ($data as $paramname => $elementdata) {
            $this->sql .= $paramname;
            if ($current_element < $arr_length) {
                $this->sql .= ',';
            }
            $current_element = $current_element + 1;
        }
        $this->sql .= ")";

        $this->stmt = $this->pdo->prepare($this->sql);

        foreach ($data as $paramname => $value) {
            $this->bind($paramname, $value);
        }
        $saveok = true;

        // Run the Update Command
        if ($this->stmt !== false) {
            $affectedrows =  $this->stmt->execute();

            // Check the INSERT command run okay.
            if ($affectedrows === false) {
                $saveok = false;
                $msg = gettext("Error running the Database INSERT Statement");
            }
        } else {
            $saveok = false;
            $msg = gettext("Error preparing the Database INSERT Statement");
        }

        //If all went okay return true.  If not return a WEBTEMPLATE error
        if ($saveok) {
            return true;
            ;
        } else {
            return \webtemplate\general\General::raiseError($msg, 1);
        }
    }

    /**
     * This function updates a single record to the database
     *
     * @param string $tableName  The name of the table data is to be inserted to
     * @param array  $insertData The name of the fields and data to be inserted
     * @param array  $searchdata The field and data to be used in the "WHERE" clause
     *
     * @return boolean True if insert is okay or WEBTEMPLATE error type
     * @access public
     */
    public function dbupdate($tableName, $insertData, $searchdata)
    {

        // Initialise the data array
        $data = array();
        //Build the SQL INSERT Statement
        $this->sql = "UPDATE " . $tableName . " SET ";

        // Add the field names


        $arr_length = count($insertData);
        $current_element = 1;
        foreach ($insertData as $key => $elementData) {
            $paramname = ':' . $key;
            $this->sql .= $key . " = " . $paramname;
            $data[$paramname] = $elementData;
            if ($current_element < $arr_length) {
                $this->sql .= ", ";
            }
            $current_element = $current_element + 1;
        }

        // ADD the WHERE Statement
        $this->sql .= " WHERE ";
        $arr_length = count($searchdata);
        $current_element = 1;
        foreach ($searchdata as $key => $elementData) {
            $paramname = ':' . $key;
            $this->sql .= $key . " = " . $paramname ;
            $data[$paramname] = $elementData;
            if ($current_element < $arr_length) {
                $this->sql .= " AND ";
            }
            $current_element = $current_element + 1;
        }

        $this->stmt = $this->pdo->prepare($this->sql);

        foreach ($data as $paramname => $value) {
            $this->bind($paramname, $value);
        }

        $saveok = true;

        // Run the Update Command
        if ($this->stmt!== false) {
            $affectedrows = $this->stmt->execute();

            // Check the INSERT command run okay.
            if ($affectedrows === false) {
                $saveok = false;
                $msg = gettext("Error running the Database UPDATE Satement");
            } else {
                if ($this->stmt->rowCount() == 0) {
                    $saveok = false;
                    $msg = gettext("Record not found");
                }
            }
        } else {
            $saveok = false;
            $msg = gettext("Error preparing the Database UPDATE Statement");
        }

        //If all went okay return true.  If not return a WEBTEMPLATE error
        if ($saveok) {
            return true;
            ;
        } else {
            return \webtemplate\general\General::raiseError($msg, 1);
        }
    }


    /**
     * This function selects a single record from the database
     *
     * @param string $tableName  The name of the table data is to be selected from
     * @param array  $fieldNames The name of the fields to select from the database
     * @param array  $searchdata The field and data to be used in the "WHERE" clause
     *
     * @return array Search data if search is ok or WEBTEMPLATE error type
     * @access public
     */
    public function dbselectsingle($tableName, $fieldNames, $searchdata)
    {

        // Build SQL statement
        $this->sql = "SELECT ";
        $this->sql .= $this->processSearchFields($fieldNames);
        $this->sql .= " from " . $tableName;
        $this->sql .= $this->processSearchData($searchdata);

        // Run the statement and check for errors
        $this->stmt = $this->pdo->prepare($this->sql);
        $resultID = $this->stmt->execute();
        if ($resultID !== false) {
            // No errors
            if ($this->stmt->rowCount() > 0) {
                // Found at least one record.
                // retrieve the record and populate the result array
                $gao = $this->stmt->fetch(\PDO::FETCH_ASSOC, 0);
            } else {
                $msg = gettext('Not Found');
                $err = \webtemplate\general\General::raiseError(
                    $msg,
                    DB_ERROR_NOT_FOUND
                );
                return $err;
            }
        } else {
            $msg = gettext('SQL Query Error');
            $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
            return $err;
        }
        return $gao;
    }



    /**
     * This function returns a search from the database
     *
     * @param string $tableName  Name of the table data is to be selected from
     * @param array  $fieldNames Name of the fields to select from the database
     * @param array  $searchdata Field and data to be used in the "WHERE" clause
     * @param string $order      Field used to order the selected data
     * @param array  $join       Data used to join tables for the search
     *
     * @return array Search data if search is ok or WEBTEMPLATE error type
     * @access public
     */
    public function dbselectmultiple(
        $tableName,
        $fieldNames,
        $searchdata,
        $order = null,
        $join = null
    ) {

        // Build SQL statement
        $this->sql = "SELECT ";
        $this->sql .= $this->processSearchFields($fieldNames);
        $this->sql .= " from " . $tableName;
        $this->sql .= $this->processJoin($join);
        $this->sql .= $this->processSearchData($searchdata);
        $this->sql .= $this->processSearchOrder($order);

        // Run the statement and check for errors
        $this->stmt = $this->pdo->prepare($this->sql);
        $resultID = $this->stmt->execute();
        if ($resultID !== false) {
            // No errors
            if ($this->stmt->rowCount() > 0) {
                /*  The search has found at least one group.
                   Create the output array */
                $resultarray = array();

                /* Populate the output array with the records */
                while ($uao = $this->stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $resultarray[] = $uao;
                }
            } else {
                $msg = gettext('Not Found');
                $err = \webtemplate\general\General::raiseError(
                    $msg,
                    DB_ERROR_NOT_FOUND
                );
                return $err;
            }
        } else {
            $msg = gettext('SQL Query Error: ') . $this->sql;
            $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
            return $err;
        }
        return $resultarray;
    }




    /**
     * This function deletes single records from the database.
     *
     * The data to be used for the where clause is in an array called $searchdata
     * in format "columnname" => "search data".  It only deletes data which matches
     * exactly
     *
     * @param string $tableName  The name of the table data is to be deleted from
     * @param array  $searchdata The field and data to be used in the "WHERE" clause
     *
     * @return boolean if rows deleted or WEBTEMPLATE error type
     * @access public
     */
    public function dbdelete($tableName, $searchdata)
    {

        $this->sql = "DELETE FROM ". $tableName . " WHERE ";
        while ($data = current($searchdata)) {
            $this->sql .= key($searchdata) . " = '" . $data . "'";
            if (next($searchdata) !== false) {
                $this->sql .= " AND ";
            }
        }

        $this->stmt = $this->pdo->prepare($this->sql);
        $resultID = $this->stmt->execute();
        if ($this->stmt === false) {
            $msg = gettext('SQL Query Error');
            $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
            return $err;
        }
        $this->rowcount = $this->stmt->rowCount();
        return true;
    }


    /**
     * This function can delete multiple records from the database.
     *
     * The data to be used for the where clause is in an array called $searchdata
     * in format "columnname" => array("type" => "<,> or =", "data" => "search data")
     *
     * @param string $tableName  The name of the table data is to be deleted from
     * @param array  $searchdata The field and data to be used in the "WHERE" clause
     *
     * @return boolean true if search is ok or WEBTEMPLATE error type
     * @access public
     */
    public function dbdeletemultiple($tableName, $searchdata)
    {

        $this->sql = "DELETE FROM ". $tableName . " WHERE ";
        while ($data = current($searchdata)) {
            $this->sql .= key($searchdata) . " ";
            $this->sql .= $data['type'] . " '" . $data['data'] . "'";
            if (next($searchdata) !== false) {
                $this->sql .= " AND ";
            }
        }

        $this->stmt = $this->pdo->prepare($this->sql);
        $resultID = $this->stmt->execute();
        if ($resultID === false) {
            $msg = gettext('SQL Query Error');
            $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
            return $err;
        }
        $this->rowcount = $this->stmt->rowCount();
        return true;
    }

    /**
     * Get the rowcount of the last activity
     *
     * @return integer
     * @access public
     */
    public function rowCount()
    {
        return $this->rowcount;
    }

    /**
     * ****************************************************************************
     * **   END OF THE SECTION OF THE FILE CONTAINING GENERIC DATABASE FUNCTIONS **
     * ****************************************************************************
     */

    /**
     * ****************************************************************************
     * **                 START OF THE CLASS PRIVATE FUNCTIONS                   **
     * ****************************************************************************
     */

    /**
     * Function to process a column array into an SQL string and add any constraints
     * to arrays for latter processing
     *
     * @param string $tableName     The name of the table being processed
     * @param string $key           The name of the column being processed
     * @param array  $column        The column defintion
     * @param string $primaryKey    The Primary key for this table
     * @param array  $foreignkeys   The constraints array for the current table
     * @param array  $uniquecolumns Unique data array for the current table
     *
     * @return string The SQl description of the column
     *
     * @access private
     */
    private function processColumn(
        $tableName,
        $key,
        $column,
        &$primaryKey,
        &$foreignkeys,
        &$uniquecolumns
    ) {
        $sql = $key . ' ' . $this->translateColumn($column['TYPE']);

        //SET DEFAULT VALUE
        if (array_key_exists('DEFAULT', $column)) {
                $sql .= " DEFAULT '" . $column['DEFAULT'] . "'";
        }

        // Set  NOT NULL
        if (array_key_exists('NOTNULL', $column)) {
            if ($column['NOTNULL'] == '1') {
                $sql .= " NOT NULL";
            }
        }

        // Set The PRIMARY KEY IF ONE EXISTS
        if (array_key_exists('PRIMARY', $column)) {
            if ($column['PRIMARY'] == '1') {
                $primaryKey = $key;
            }
        }

        // Set the constraints if they exist
        if (array_key_exists('CONSTRAINTS', $column)) {
            $coldatastr = $column['CONSTRAINTS']['table'];
            $coldatastr .= "(" . $column['CONSTRAINTS']['column']  . ")";
            $foreignkeys[] = array(
                $tableName,
                "fk_" . $tableName . "_" . $key,
                $key,
                $coldatastr
            );
        }

        // Identify columns with unique data
        if (array_key_exists('UNIQUE', $column)) {
            if ($column['UNIQUE'] == 1) {
                $uniquecolumns[] = array($tableName, $key);
                $gotconstraints = true;
            }
        }

        return $sql;
    }




    /**
     * Function to process foreignkeys for a table
     *
     * @param array $foreignkeys The constraints array for the current table
     *
     * @return bolean True if processing complete WEBTEMPLATE error otherwise
     *
     * @access private
     */
    private function processForeignKeys($foreignkeys)
    {
        $dataerror = false;

        if (count($foreignkeys) > 0) {
            foreach ($foreignkeys as $value) {
                $result = $this->createFK($value);
                if (\webtemplate\general\General::isError($result)) {
                    $errorMsg = "Error Creating the FK on Table ";
                    $errorMsg .= $value[0] ."\n";
                    $dataerror = true;
                }
            }
        }

        if ($dataerror === false) {
            return true;
        } else {
            $err = \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
            return $err;
        }
    }


    /**
     * Function to process unique column constraints for a table
     *
     * @param array $uniquecolumns Unique data array for the current table
     *
     * @return bolean True if processing complete WEBTEMPLATE error otherwise
     *
     * @access private
     */
    private function processUniqueColumn(&$uniquecolumns)
    {
        if (count($uniquecolumns) > 0) {
            foreach ($uniquecolumns as $value) {
                $result = $this->createUniqueConstraint($value);
                if (\webtemplate\general\General::isError($result)) {
                    $errorMsg = "Error Creating unique column on Table ";
                    $errorMsg .= $value[0] ."\n";
                    $err = \webtemplate\general\General::raiseError(
                        $errorMsg,
                        DB_ERROR
                    );
                    return $err;
                }
            }
        }
        return true;
    }


    /**
     * Function to process Indexes for a table
     *
     * @param string $tableName The name of the table being processed
     * @param array  $indexes   The indexes for a tavle
     *
     * @return boolean True if processing complete WEBTEMPLATE error otherwise
     *
     * @access private
     */
    private function processIndexes($tableName, &$indexes)
    {
        $dataerror = false;

        foreach ($indexes as $indexname => $indexdata) {
            $result = $this->createIndex($tableName, $indexname, $indexdata);
            if (\webtemplate\general\General::isError($result)) {
                $errorMsg = gettext("Error Creating Indexes on Table ");
                $errorMsg .= $tableName ."\n";
                $dataerror = true;
            }
        }

        if ($dataerror == false) {
            return true;
        } else {
            $err = \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
            return $err;
        }
    }



    /**
     * This function processes the field to be returned into  a SQL string
     *
     * @param array $fieldNames The fields to be returned as part of the search
     *
     * @return string The combined join statements in SQL Format
     *
     * @access private
     */
    private function processSearchFields($fieldNames)
    {

        $sql = '';
        while ($field = current($fieldNames)) {
            $sql .= $field ;
            if (\next($fieldNames) !== false) {
                $sql .= ", ";
            }
        }
        return $sql;
    }


    /**
     * This function processes join statements into a SQL string for searching
     *
     * @param array $join The field to be returned as part of the search
     *
     * @return string The combined join statements in SQL Format
     *
     * @access private
     */
    private function processJoin($join)
    {

        $sql = '';
        if ($join != null) {
            $sql .= ' join ';
            while ($field = \current($join)) {
                $sql .= $field ;
                if (\next($join) !== false) {
                    $sql .= " join ";
                }
            }
        }
        return $sql;
    }



    /**
     * This function processes search data nto a SQL string for searching
     *
     * @param array $searchdata The field to be returned as part of the search
     *
     * @return string The  search data in SQL Format
     *
     * @access private
     */
    private function processSearchData($searchdata)
    {

        $sql = '';
        if ($searchdata != null) {
            $sql .= " WHERE ";
            while ($data = \current($searchdata)) {
                if (($data[0] =='%') or ($data[strlen($data)-1] == '%')) {
                    $sql .= \key($searchdata) . " like '" . $data . "'";
                } else {
                    $sql .= \key($searchdata) . " = '" . $data . "'";
                }
                if (\next($searchdata) !== false) {
                    $sql .= " AND ";
                }
            }
        }
        return $sql;
    }

    /**
     * This function processes search data nto a SQL string for searching
     *
     * @param string $order Thefield the search is to be ordered by
     *
     * @return string The  search data in SQL Format
     *
     * @access private
     */
    private function processSearchOrder($order)
    {

        $sql= '';
        if ($order != null) {
            $sql .= " ORDER BY " . $order;
        }

        return $sql;
    }


    /**
     * Function to update the column type
     *
     * @param string $sql        Initial part of Alter Column command
     * @param string $tableName  The name of the table being altered
     * @param string $columnName The name of the column being altered
     * @param array  $changes    List of Changes to the column
     * @param array  $newColumn  The structure of the new column
     *
     * @return boolean True if complete WEBTEMPLATE error otherwise
     *
     * @access private
     */
    private function processColumnType(
        $sql,
        $tableName,
        $columnName,
        $changes,
        $newColumn
    ) {
        if (array_key_exists('TYPE', $changes)) {
            $runsql = $sql . " TYPE " . $newColumn['TYPE'];
            $affected = $this->pdo->exec($runsql);
            if ($affected === false) {
                $errorMsg = gettext("Error changing column Type on ");
                $errorMsg .= $tableName .":" . $columnName . "\n";
                return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
            }
        }
        return true;
    }


    /**
     * Function to SET/CLEAR the NOTNULL flag
     *
     * @param string $sql        Initial part of Alter Column command
     * @param string $tableName  The name of the table being altered
     * @param string $columnName The name of the column being altered
     * @param array  $changes    List of Changes to the column
     * @param array  $newColumn  The structure of the new column
     *
     * @return boolean True if complete WEBTEMPLATE error otherwise
     *
     * @access private
     */
    private function processColumnNotNull(
        $sql,
        $tableName,
        $columnName,
        $changes,
        $newColumn
    ) {
        if (array_key_exists('NOTNULL', $changes)) {
            if (array_key_exists('NOTNULL', $newColumn)) {
                if ($newColumn['NOTNULL'] == '1') {
                    $runsql = $sql . " SET NOT NULL";
                } else {
                    $runsql = $sql . " DROP NOT NULL";
                }
            } else {
                $runsql = $sql . " DROP NOT NULL";
            }
            $affected = $this->pdo->exec($runsql);
            if ($affected === false) {
                $errorMsg = gettext("Error changing column notnull on ");
                $errorMsg .= $tableName .":" . $columnName . "\n";
                return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
            }
        }
        return true;
    }


    /**
     * Function to SET/CLEAR the column default value
     *
     * @param string $sql        Initial part of Alter Column command
     * @param string $tableName  The name of the table being altered
     * @param string $columnName The name of the column being altered
     * @param array  $changes    List of Changes to the column
     * @param array  $newColumn  The structure of the new column
     *
     * @return boolean True if complete WEBTEMPLATE error otherwise
     *
     * @access private
     */
    private function processColumnDefaultValue(
        $sql,
        $tableName,
        $columnName,
        $changes,
        $newColumn
    ) {
        if (array_key_exists('DEFAULT', $changes)) {
            if (array_key_exists('DEFAULT', $newColumn)) {
                $runsql = $sql . " SET DEFAULT " .$newColumn['DEFAULT'];
            } else {
                $runsql = $sql . " DROP DEFAULT";
            }
            $affected = $this->pdo->exec($runsql);
            if ($affected === false) {
                $errorMsg = gettext("Error changing column DEFAULT on ");
                $errorMsg .= $tableName . ":" .$columnName . "\n";
                return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
            }
        }
        return true;
    }





    /**
     * Function to process the deletion of foreign keys prior to altering a column
     *
     * @param string $tableName  The name of the table being worked on
     * @param string $columnName The name of the column being altered
     * @param array  $changes    The list of changes to be made to the column
     *
     * @return boolean True if the Foreign keys have been deleted.  Error othereise
     *
     * @access private
     */
    private function processOldForeignKeys($tableName, $columnName, $changes)
    {
        // Drop any foreign keys associated with this column
        if (array_key_exists("OLDCONSTRAINTS", $changes)) {
            if ($changes['OLDCONSTRAINTS'] == true) {
                $fk = 'fk_' . $tableName . "_" . $columnName;
                $result = $this->dropFK($tableName, $fk);
                if (\webtemplate\general\General::isError($result)) {
                    $errorMsg = gettext("Error dropping Foreign Key") . $fk . "\n";
                    return \webtemplate\general\General::raiseError(
                        $errorMsg,
                        DB_ERROR
                    );
                }
            }
        }
    }


    /**
     * Function to print out messages.  If class is under unit test output is aborted
     *
     * @param string $msg The message to be displayed
     *
     * @return boolean Always true
     *
     * @access private
     */
    private function dbEcho($msg = "")
    {
        if (!isset($GLOBALS['unittest'])) {
            echo $msg. "\n";
        }
        return true;
    }



    /**
     * Bind inputs to place holders
     *
     * This function binds the inputs to the place holders we put in place in the
     * sql statement prepared using the query function.
     *
     * @param string $param The name of the placeholder the variable is to be bound.
     * @param mixed  $value The value to be bound to the placeholder
     * @param int    $type  The type of variable defined using PDO Constants.
     *
     * @return boolean always true
     *
     * @access private
     */
    private function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = \PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = \PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = \PDO::PARAM_NULL;
                    break;
                default:
                    $type = \PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);

        return true;
    }

    /**
     * ****************************************************************************
     * **                   END OF THE CLASS PRIVATE FUNCTIONS                   **
     * ****************************************************************************
     */
}
