<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\db\driver;

/**
 * DBDRIVERIF defines the public interface of the Webtemplate database driver.  This
 * interface needs to be implemented for each of the RMDB systems to be accessed.
 *
 * @category Webtemplate
 * @package  Database
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

interface InterfaceDatabaseDriver
{

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
    public function createIndex($tableName, $indexname, $indexdata);

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
    public function dropIndex($tableName, $indexName);

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
    public function createUser($username, $password, $unittestdb);

     /**
     * Function to drop the database user for the application
     *
     * @param string $username The name of the database user
     *
     * @return boolean true if user dropped WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function dropUser($username);

    /**
     * Function to drop the database for the application
     *
     * @param string $database The name of the database
     *
     * @return boolean true if database exists WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function databaseExists($database);

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
    public function createDatabase($database, $username);

     /**
     * Function to drop the database for the application
     *
     * @param string $database The name of the database
     *
     * @return boolean true if database Created or exists WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function dropDatabase($database);

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
    public function createtable($tableName, $tabledef);

    /**
     * Function to create SQL to drop a table
     *
     * @param string $tableName The name of the table being dropped
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function dropTable($tableName);

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
    public function addColumn($tableName, $columnName, $columnData);

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
    public function dropColumn($tableName, $columnName);

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
    public function alterColumn($tableName, $columnName, $newColumn, $changes);

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
    public function saveSchema($version, $schema, $groups);

    /**
     * Function to retrieve the current schema from the database.
     *
     * @return mixed array containing schema verson and data or WEBTEMPLATE error
     *
     * @access public
     */
    public function getSchema();

    /**
     * Function to translate Column types from default schema
     *
     * @param string $columntype Type of database column to be translated
     *
     * @return string translated column type
     *
     * @access public
     */
    public function translateColumn($columntype);

     /**
     * Function to create a Forign Key
     *
     * @param array $keydata An array containing the Forign Key Data
     *
     * @return boolean true if Forign Key Created WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function createFK($keydata);

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
    public function dropFK($tableName, $keyName);

     /**
     * Function to create a Unique Constraint on a column
     *
     * @param array $keydata An array containing the column data
     *
     * @return boolean true if constraint Created WEBTEMPLATE Error otherwise
     *
     * @access public
     */
    public function createUniqueConstraint($keydata);

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
    public function getDBVersion();

     /**
     * Function to start a database transaction
     *
     * This function starts a Database Transaction
     *
     * @return boolean true if transaction is started
     *
     * @access public
     */
    public function startTransaction();

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
    public function endTransaction($commit);

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
    public function dbinsert($tableName, $insertData);

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
    public function dbinsertid($tableName, $idfield, $srchfield, $srchdata);

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
    public function dbupdate($tableName, $insertData, $searchdata);

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
    public function dbselectsingle($tableName, $fieldNames, $searchdata);

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
    );

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
    public function dbdelete($tableName, $searchdata);

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
    public function dbdeletemultiple($tableName, $searchdata);
}
