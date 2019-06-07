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
 * DB_DRIVER_MOCK Class is the mock class for unit testing.  It implements
 * the DBDRIVERIF interface but returns errors for every function
 *
 * @category Webtemplate
 * @package  Database
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
**/

class DatabaseDrivermock implements InterfaceDatabaseDriver
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
     * An mdb2 Database Object.
     *
     * @var    object
     * @access protected
     */
    protected $mdb2;

    /**
     * Name of test being run
     *
     * @var    string
     * @access protected
     */
    protected $functions = array();

    /**
     * Data to be used in test
     *
     * @var    array
     * @access protected
     */
    protected $data = array();

    /**
     * PGSQL Driver Class Constructor
     *
     * Sets up the PGSQL Driver dsn from the calling function
     * and MDB2 Options specifig to the pgsql driver.
     *
     * @param array $dsn MDB2 Data Source Name
     *
     * @access public
     */
    public function __construct($dsn)
    {
        // Set the dsn options to suit the PGSQL MDB2 Driver
        /**$this->dsnOptions = array(
            'debug'       => 2,
            'portability' => MDB2_PORTABILITY_ALL,
            'idxname_format' => '%s'
        );*/
        $this->dsn  = $dsn ;
    } // end constructor

    /**
     * DB Driver Destructor
     *
     * Disconnect the MDB2 data object from the database
     */
    public function __destruct()
    {
        $this->dsn = '';
    }

    /****************************************************************************
     * This is a control function for the MOCK Interface only
     ****************************************************************************/
    /**
     * Function to control the MOCK Database Interface.
     *
     * This function is uesed to control wither the MOCK database interface return
     * sucessful values or failures.
     *
     * @param array $functions The name of the test being run
     * @param array $data      The data being sent to or returned by the database
     *
     * @return boolean Return true if control function sucessful
     *
     * @access public
     */
    public function control($functions, $data)
    {
        $this->functions = $functions;
        $this->data      = $data;
        return true;
    }

    /****************************************************************************
     * The functions in the section below are all used to create and modify the
     * database.  They are called from The Schemafunctions Class
     ****************************************************************************/
    /**
     * Function to create an index
     *
     * This is a stub function to create an index.  The actual implementation
     * is in the driver specific class.
     *
     * @param string $tableName The name of the table being indexed
     * @param string $indexname The Name of the Index.
     * @param array  $indexdata An array containing the index Data
     *
     * @return boolean true if index Created WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function createIndex($tableName, $indexname, $indexdata)
    {
        $indexcreated = false;
        $callers = debug_backtrace();
        $callingFunction = $callers[1]['function'];
        if (array_key_exists($callingFunction, $this->functions)) {
            if (array_key_exists('pass', $this->functions[$callingFunction])) {
                if (($this->data[0] === $tableName)
                    and ($this->data[1] === $indexname)
                    and ($this->data[2] === $indexdata['COLUMN'])
                ) {
                    $indexcreated = true;
                }
            }
        }
        if ($indexcreated === true) {
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
        $msg = gettext("Error Dropping Index ");
        $msg .= $tableName . ":" . $indexName . "\n";
        return \webtemplate\general\General::raiseError($msg, DB_ERROR);
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
        $msg = gettext("Error Creating Database User");
        return \webtemplate\general\General::raiseError($msg, DB_ERROR);
    }

     /**
     * Function to drop the database user for the application
     *
     * @param string $username The name of the database user
     *
     * @return boolean true if user dropped WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function dropUser($username)
    {
        $msg = gettext("Error Dropping Database User");
        return \webtemplate\general\General::raiseError($msg, DB_ERROR);
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
        $msg2 = gettext("Error checking the database exists") . "\n";
        return \webtemplate\general\General::raiseError($msg2, DB_ERROR);
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
        $msg2 = gettext("Error Creating the database") . "\n";
        return \webtemplate\general\General::raiseError($msg2, DB_ERROR);
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
        $msg = gettext("Error Dropping Database") . "\n";
        return \webtemplate\general\General::raiseError($msg, DB_ERROR);
    }

    /**
     * Function to create table SQL
     *
     * @param string $tableName The name of the table being created
     * @param string $tabledef  Array containing table details contained in
     *                          schema.php
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function createtable($tableName, $tabledef)
    {
        $tablecreated = false;
        $callers = debug_backtrace();
        $callingFunction = $callers[1]['function'];
        if (array_key_exists($callingFunction, $this->functions)) {
            if (array_key_exists('pass', $this->functions[$callingFunction])) {
                if ($this->data[$tableName] === $tabledef) {
                    $tablecreated =  true;
                }
            }
        }

        if ($tablecreated == true) {
            return true;
        } else {
            $errorMsg = "Error Creating the Table\n";
            return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
        }
    }

    /**
     * Function to create SQL to drop a table
     *
     * @param string $tableName The name of the table being dropped
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function dropTable($tableName)
    {
        $tabledropped = false;
        $callers = debug_backtrace();
        $callingFunction = $callers[1]['function'];
        if (array_key_exists($callingFunction, $this->functions)) {
            if (array_key_exists('pass', $this->functions[$callingFunction])) {
                if ($this->data[0] === $tableName) {
                    $tabledropped = true;
                }
            }
        }
        if ($tabledropped == true) {
            return true;
        } else {
            $errorMsg = "Error Dropping Table $tableName\n";
            return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
        }
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
        $columnadded = false;
        $callers = debug_backtrace();
        $callingFunction = $callers[1]['function'];
        if (array_key_exists($callingFunction, $this->functions)) {
            if (array_key_exists('pass', $this->functions[$callingFunction])) {
                if (($this->data[0] == $tableName)
                    and ($this->data[1] == $columnName)
                ) {
                    $columnadded = true;
                }
            }
        }
        if ($columnadded == true) {
            return true;
        } else {
            $errorMsg = "Error adding column to $tableName\n";
            return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
        }
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
        $columndropped = false;
        $callers = debug_backtrace();
        $callingFunction = $callers[1]['function'];
        if (array_key_exists($callingFunction, $this->functions)) {
            if (array_key_exists('pass', $this->functions[$callingFunction])) {
                if (($this->data[0] == $tableName)
                    and ($this->data[1] == $columnName)
                ) {
                    $columndropped = true;
                }
            }
        }
        if ($columndropped == true) {
            return true;
        } else {
            $errorMsg = "Error dropping column on $tableName\n";
            return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
        }
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
        $columnaltered = false;
        $callers = debug_backtrace();
        $callingFunction = $callers[1]['function'];
        if (array_key_exists($callingFunction, $this->functions)) {
            if (array_key_exists('pass', $this->functions[$callingFunction])) {
                if (($this->data[0] == $tableName)
                    and ($this->data[1] == $columnName)
                    and (array_key_exists($this->data[2], $changes))
                    and ($this->data[3] === $newColumn)
                    and ($this->data[4]  == $changes['OLDCONSTRAINTS'])
                ) {
                    if ($this->data[2] == 'PK') {
                        if ($changes['PK'] == $this->data[5]) {
                            $columnaltered = true;
                        }
                    } else {
                        $columnaltered = true;
                    }
                }
            }
        }


        if ($columnaltered === true) {
            return true;
        } else {
            $errorMsg = gettext("Error changing column Type on ");
            $errorMsg .= $tableName . ":" . $columnName . "\n";
            return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
        }
    }

    /**
     * Function to create the SQL to save the current schema
     *
     * @param integer $version The Version of the schema being saved
     * @param array   $schema  The array containing the schema
     * @param array   $groups  The array containing the group list
     *
     * @return mixed true if schema saved or WEBTEMPLATE error
     *
     * @access public
     */
    public function saveSchema($version, $schema, $groups)
    {
        $msg = gettext("Error Deleteting Previous Schema");
        return \webtemplate\general\General::raiseError($msg, DB_ERROR);
    }

    /**
     * Function to retrieve the current schema from the database.
     *
     * @return mixed array containing schema verson and data or WEBTEMPLATE error
     *
     * @access public
     */
    public function getSchema()
    {
        $msg = gettext('Schema Not Found');
        $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
        return $err;
    }

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
        return $columntype;
    }

     /**
     * Function to create a Forign Key
     *
     * @param array $keydata An array containing the Forign Key Data
     *
     * @return boolean true if Forign Key Created WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function createFK($keydata)
    {
        $msg = gettext("Error Creating Forign Keys.");
        return \webtemplate\general\General::raiseError($msg, DB_ERROR);
    }

     /**
     * Function to drop a Forign Key
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
        $errorMsg = "Error deleteting Foreign Key $keyName on $tableName\n";
        return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
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
        $errorMsg = "Error creating Unique Constraint $keydata[1] on $keydata[0]\n";
        return \webtemplate\general\General::raiseError($errorMsg, DB_ERROR);
    }

    /*****************************************************************************
     * End of the section which deals with Database Maintenance
     *****************************************************************************/

    /*****************************************************************************
     * This section contains all the functions used to manipulate the data held
     * within the database
     *****************************************************************************/
    /**
     * Function to get the database version
     *
     * This function gets the version of database currently being used.
     *
     * @return string database Version
     *
     * @access public
     */
    public function getDBVersion()
    {
        $callers = debug_backtrace();
        $callingFunction = $callers[1]['function'];
        $databaseversion = gettext("Error Getting Database Version");

        if (array_key_exists($callingFunction, $this->functions)) {
            if (array_key_exists('pass', $this->functions[$callingFunction])) {
                if ($this->functions[$callingFunction]['pass'] == true) {
                    $databaseversion = $this->data['version'];
                }
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
        $callers = debug_backtrace();
        $callingFunction = $callers[1]['function'];

        if (array_key_exists($callingFunction, $this->functions)) {
            if (array_key_exists(
                'starttransaction',
                $this->functions[$callingFunction]
            )
            ) {
                if ($this->functions[$callingFunction]['starttransaction'] == true) {
                    return true;
                }
            }
        }
        return false;
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
        $callers = debug_backtrace();
        $callingFunction = $callers[1]['function'];
        $result = false;

        if (array_key_exists($callingFunction, $this->functions)) {
            if (array_key_exists(
                'endtransaction',
                $this->functions[$callingFunction]
            )
            ) {
                if ($this->functions[$callingFunction]['endtransaction'] == true) {
                    $result = $commit;
                }
            }
        }
        return $result;
    }

    /**
     * This function inserts a new record to the database
     *
     * The data to be inserted in to $tableName is places in an array called
     * $field name.  The data is stored in the array in the following format
     * "columnname" => "data to be inserted".
     *
     * @param string $tableName  The name of the table data is to be inserted to
     * @param array  $insertData The name of the fields and data to be inserted
     *
     * @return boolean True if insert is ok or WEBTEMPLATE error type
     *
     * @access public
     */
    public function dbinsert($tableName, $insertData)
    {
        $callers = debug_backtrace();
        $callingFunction = $callers[1]['function'];

        if (array_key_exists($callingFunction, $this->functions)) {
            if (array_key_exists('pass', $this->functions[$callingFunction])) {
                if ($this->functions[$callingFunction]['pass'] == true) {
                    return true;
                }
            }
        }

        // The test should fail as
        $msg = gettext('SQL Query Error');
        $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
        return $err;
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
        $callers = debug_backtrace();
        $callingFunction = $callers[1]['function'];
        $result = false;

        if (array_key_exists($callingFunction, $this->functions)) {
            if (array_key_exists('id', $this->functions[$callingFunction])) {
                if ($this->functions[$callingFunction]['id'] == true) {
                    $result = true;
                }
            }
        }

        if ($result == true) {
            if (array_key_exists($srchdata, $this->data)) {
                return $this->data[$srchdata];
            } else {
                return 1;
            }
        } else {
            $msg = gettext('Error Getting record ID.');
            $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
            return $err;
        }
    }

    /**
     * This function updates an existing record to the database
     *
     * The data to be inserted in to $tableName is places in an array called
     * $field name.  The data is stored in the array in the following format
     * "columnname" => "data to be inserted".
     *
     * The data to be used for the where clause is again in an array in the same
     * format "columnname" => "search data".
     *
     * @param string $tableName  The name of the table data is to be inserted to
     * @param array  $insertData The name of the fields and data to be inserted
     * @param array  $searchdata The field and data to be used in the "WHERE" clause
     *
     * @return boolean True if insert is ok or WEBTEMPLATE error type
     *
     * @access public
     */
    public function dbupdate($tableName, $insertData, $searchdata)
    {
        $callers = debug_backtrace();
        $callingFunction = $callers[1]['function'];

        if (array_key_exists($callingFunction, $this->functions)) {
            if (array_key_exists('update', $this->functions[$callingFunction])) {
                if ($this->functions[$callingFunction]['update'] === true) {
                    return true;
                }
            }
        }

        // The test should fail as
        $msg = gettext('SQL Query Error');
        $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
        return $err;
    }

    /**
     * This function selects a single record from the database
     *
     * The columns to be returned from the search are in an array called  $fieldNames
     * This is an unindexed array, array=("Col1", "col2" etc).
     *
     * The data to be usedfor the where clause is in an array called $searchdata in
     * format"columnname" => "search data".
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
        return $this->dbselect($tableName, $fieldNames, $searchdata);
    }

    /**
     * This function returns a search from the database
     *
     * The columns to be returned from the search are in an array called  $fieldNames
     * This is an unindexed array, array=("Col1", "col2" etc).
     *
     * The data to be used for the where clause is in an array called $searchdata in
     * format "columnname" => "search data".
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
        return $this->dbselect($tableName, $fieldNames, $searchdata);
    }

    /**
     * This function deletes single from the database.
     *
     * The data to be used for the where clause is in an array called $searchdata
     * in format "columnname" => "search data".  It only deletes data which matches
     * exactly
     *
     * @param string $tableName  The name of the table data is to be deleted from
     * @param array  $searchdata The field and data to be used in the "WHERE" clause
     *
     * @return boolean true if search is ok or WEBTEMPLATE error type
     * @access public
     */
    public function dbdelete($tableName, $searchdata)
    {
        $callers = debug_backtrace();
        $callingFunction = $callers[1]['function'];

        if (array_key_exists($callingFunction, $this->functions)) {
            if (array_key_exists('delete', $this->functions[$callingFunction])) {
                if ($this->functions[$callingFunction]['delete'] == $tableName) {
                    return true;
                }
            }
        }

        $msg = gettext('SQL Query Error');
        $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
        return $err;
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
        $msg = gettext('SQL Query Error');
        $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
        return $err;
    }

    /**
     * This function implements the Select function in the mock driver.
     *
     * This function is the comon select function in the mock driver for both
     * dbselectsingle and dbselectmultiple
     *
     * The columns to be returned from the search are in an array called  $fieldNames
     * This is an unindexed array, array=("Col1", "col2" etc).
     *
     * The data to be usedfor the where clause is in an array called $searchdata in
     * format"columnname" => "search data".
     *
     * @param string $tableName  The name of the table data is to be selected from
     * @param array  $fieldNames The name of the fields to select from the database
     * @param array  $searchdata The field and data to be used in the "WHERE" clause
     *
     * @return array Search data if search is ok or WEBTEMPLATE error type
     * @access private
     */
    private function dbselect($tableName, $fieldNames, $searchdata)
    {
        $callers = debug_backtrace();
        $callingFunction = $callers[2]['function'];
        if (array_key_exists($callingFunction, $this->functions)) {
            if (array_key_exists('notfound', $this->functions[$callingFunction])) {
                if ($this->functions[$callingFunction]['notfound'] == true) {
                    $msg = gettext('Not Found');
                    $err = \webtemplate\general\General::raiseError(
                        $msg,
                        DB_ERROR_NOT_FOUND
                    );
                    return $err;
                }
            }
            if (array_key_exists('pass', $this->functions[$callingFunction])) {
                if ($this->functions[$callingFunction]['pass'] == true) {
                    return $this->data[$callingFunction];
                }
            }
        }

        // The test should fail as
        $msg = gettext('SQL Query Error');
        $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
        return $err;
    }
    /**
     * This function disconnects from the database
     *
     * @return boolean True
     */
    public function disconnect()
    {
        return true;
    }
}
