<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\db\schema;

/**
 * DB_Driver Class is the parent class for all database drivers.  It
 * contains functions which can be over ridden if necessary to provide
 * access to the database via MDB2
 *
 * @category Webtemplate
 * @package  Database
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

class SchemaFunctions
{

    /**
     * Database driver class
     *
     * @var webtemplate\db\driver\pgsql
     *
     * @access private
     */
    private $db;

    /**
     * Constructor
     *
     * Creates a SchemaFunctions class for processing the Schema description
     * arrays
     *
     * @param webtemplate\db\driver\pgsql $db Database Class
     *
     * @access public
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Function to create the database schema
     *
     * @param string $schema Array containing the schema details.
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function newSchema($schema)
    {
        $schemaupdated = true;
        foreach ($schema as $key => $table) {
            $result = $this->db->createtable($key, $table);
            if (\webtemplate\general\General::isError($result)) {
                $schemaupdated = false;
            }
        }

        if ($schemaupdated == true) {
            return true;
        } else {
            $msg = gettext("ERROR creating Database Schema");
            $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
            return $err;
        }
    }



    /**
     * Function to create the database schema
     *
     * @param string $newSchema     Array containing the new schema details.
     * @param string $currentSchema Array containing the current schema details.
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function updateSchema($newSchema, $currentSchema)
    {
        $schemaupdated = true;

        // Add new tables and update existing ones.
        foreach ($newSchema as $key => $table) {
            // Check if there are any new tables
            if (!array_key_exists($key, $currentSchema)) {
                // There is a new table.  Create it in the Database
                $this->schemaEcho("Adding Table $key");
                $result = $this->db->createtable($key, $table);
                if (\webtemplate\general\General::isError($result)) {
                    $schemaupdated = false;
                    $this->schemaEcho($result->getMessage());
                }
            } else {
                // The Table Exists.  Now need to check if it has changed.
                // Check if columns exist.  If they don't add them.
                // If they do update them if necessary
                foreach ($newSchema[$key]['COLUMNS'] as $name => $column) {
                    if (!array_key_exists($name, $currentSchema[$key]['COLUMNS'])) {
                        // Add the new Column
                        $this->schemaEcho("New Column $key:$name");
                        $result = $this->db->addColumn($key, $name, $column);
                        if (\webtemplate\general\General::isError($result)) {
                            $schemaupdated = false;
                            $this->schemaEcho($result->getMessage());
                        }
                    } else {
                        // Modify the existing Column
                        if ($newSchema[$key]['COLUMNS'][$name] !== $currentSchema[$key]['COLUMNS'][$name]) {
                            $checkArray = array();
                            $checkArray2 = array();
                            foreach ($newSchema[$key]['COLUMNS'][$name] as $tempkey => $temparray) {
                                if (!array_key_exists($tempkey, $currentSchema[$key]['COLUMNS'][$name])) {
                                    $checkArray[$tempkey] = true;
                                } elseif ($currentSchema[$key]['COLUMNS'][$name][$tempkey] != $newSchema[$key]['COLUMNS'][$name][$tempkey]) {
                                    $checkArray[$tempkey] = true;
                                }
                            }

                            foreach ($currentSchema[$key]['COLUMNS'][$name] as $tempkey => $temparray) {
                                if (!array_key_exists($tempkey, $newSchema[$key]['COLUMNS'][$name])) {
                                    $checkArray2[$tempkey] = true;
                                }
                            }

                            $mod_columns = array_merge($checkArray, $checkArray2);

                            if ((array_key_exists('CONSTRAINTS', $column)) and (array_key_exists('CONSTRAINTS', $currentSchema[$key]['COLUMNS'][$name]))) {
                                if (($column['CONSTRAINTS']['table'] != $currentSchema[$key]['COLUMNS'][$name]['CONSTRAINTS']['table']) or ($column['CONSTRAINTS']['column'] != $currentSchema[$key]['COLUMNS'][$name]['CONSTRAINTS']['column'])) {
                                    $mod_columns['PK'] = 'update';
                                }
                            }
                            if ((array_key_exists('CONSTRAINTS', $column)) and (!array_key_exists('CONSTRAINTS', $currentSchema[$key]['COLUMNS'][$name]))) {
                                $mod_columns['PK'] = 'add';
                            }
                            if ((!array_key_exists('CONSTRAINTS', $column)) and (array_key_exists('CONSTRAINTS', $currentSchema[$key]['COLUMNS'][$name]))) {
                                $mod_columns['PK'] = 'delete';
                            }

                            if (count($mod_columns) > 0) {
                                $this->schemaEcho("Column $key:$name changed");
                                if (array_key_exists('CONSTRAINTS', $currentSchema[$key]['COLUMNS'][$name])) {
                                    $mod_columns['OLDCONSTRAINTS'] = true;
                                } else {
                                    $mod_columns['OLDCONSTRAINTS'] = false;
                                }

                                $result = $this->db->alterColumn(
                                    $key,
                                    $name,
                                    $column,
                                    $mod_columns
                                );
                                if (\webtemplate\general\General::isError($result)) {
                                    $schemaupdated = false;
                                    $this->schemaEcho($result->getMessage());
                                }
                            }
                        }
                    }
                }

                // Drop Column if no longer Required
                foreach ($currentSchema[$key]['COLUMNS'] as $name => $column) {
                    if (!array_key_exists($name, $newSchema[$key]['COLUMNS'])) {
                        $this->schemaEcho("Drop Column $key:$name");
                        $result = $this->db->dropColumn($key, $name);
                        if (\webtemplate\general\General::isError($result)) {
                            $schemaupdated = false;
                            $this->schemaEcho($result->getMessage());
                        }
                    }
                }

                // Check if there are any indexes defined.
                //Create, replace or delete as required
                $result = $this->processIndex($key, $newSchema[$key], $currentSchema[$key]);
                if (\webtemplate\general\General::isError($result)) {
                    $schemaupdated = false;
                    $this->schemaEcho($result->getMessage());
                }
            }
        }

        // Drop existing tables
        foreach ($currentSchema as $key => $table) {
            // Check if there are any new tables
            if (!array_key_exists($key, $newSchema)) {
                // Drop existing table
                $this->schemaEcho("Dropping table $key");
                $result = $this->db->dropTable($key);
                if (\webtemplate\general\General::isError($result)) {
                    $schemaupdated = false;
                    $this->schemaEcho($result->getMessage());
                }
            }
        }

        if ($schemaupdated == true) {
            return true;
        } else {
            $msg = gettext("ERROR Updating Database Schema");
            $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
            return $err;
        }
    }


    /**
     * Function to create the default database groups
     *
     * @param string $groups      Array containing the group details.
     * @param array  $groupNumber Array to hold insalled group is
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function createGroups($groups, &$groupNumber)
    {

        $groupmap = array();
        $groupsCreated = true;

        // Add groups from the groups array in schema.php
        foreach ($groups as $name => $groupdata) {
            $this->schemaEcho("Creating Group: $name");
            $insertdata = array (
                "group_name"          => $name,
                "group_description"   => $groupdata['description'],
                "group_useforproduct" => $groupdata['useforproduct'],
                "group_autogroup"     => $groupdata['autogroup'],
                "group_editable"      => $groupdata['editable'],
                "group_admingroup"    => $groupdata['admingroup']
            );

            // Insert a new group to the database
            $result = $this->db->dbinsert('groups', $insertdata);


            // Always check that result is not an error
            if (!\webtemplate\general\General::isError($result)) {
                $groupId = $this->db->dbinsertid(
                    "groups",
                    "group_id",
                    "group_name",
                    $name
                );
                if (!\webtemplate\general\General::isError($groupId)) {
                    $groupNumber[$name] = $groupId;
                } else {
                    $groupsCreated = false;
                }
            } else {
                $groupsCreated = false;
            }
        }
        if ($groupsCreated == true) {
            return true;
        } else {
            $msg = gettext("ERROR Creating Database Groups") . "\n";
            $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
            return $err;
        }
    }



    /**
     * Function to update the default database groups
     *
     * @param array $newGroups Array containing the new group details.
     * @param array $oldGroups Array containing the old group details.
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function updateGroups($newGroups, $oldGroups)
    {
        // Set the default Variables
        $groupsUpdated = true;


        // Walk through the new groups adding or updateing as required
        foreach ($newGroups as $name => $groupdata) {
            // Check if this is a new group
            if (!array_key_exists($name, $oldGroups)) {
                // It is a new group add it.
                $this->schemaEcho("Creating Group $name");
                $groupdata = array (
                    "group_name"          => $name,
                    "group_description"   => $groupdata['description'],
                    "group_useforproduct" => $groupdata['useforproduct'],
                    "group_autogroup"     => $groupdata['autogroup'],
                    "group_editable"      => $groupdata['editable'],
                    "group_admingroup"    => $groupdata['admingroup']
                );

                // Insert a new group to the database
                $result = $this->db->dbinsert('groups', $groupdata);


                // Check the group was updated Okay
                if (\webtemplate\general\General::isError($result)) {
                    $groupsUpdated = false;
                }
            } else {
                // It is a new group check and update as necessary.
                if ($groupdata !== $oldGroups[$name]) {
                    // The Groups are different
                    $this->schemaEcho("Updating Group $name");

                    //Got the $gid from the database
                    $groupdata = array (
                        "group_name"          => $name,
                        "group_description"   => $groupdata['description'],
                        "group_useforproduct" => $groupdata['useforproduct'],
                        "group_autogroup"     => $groupdata['autogroup'],
                        "group_editable"      => $groupdata['editable'],
                        "group_admingroup"    => $groupdata['admingroup']
                    );

                    $searchdata = array(
                        "group_name" => $name
                    );
                    $result = $this->db->dbupdate(
                        "groups",
                        $groupdata,
                        $searchdata
                    );


                    if (\webtemplate\general\General::isError($result)) {
                        // error updating the group
                        $groupsUpdated = false;
                    }
                }
            }
        } // End of foreach walk through new groups

        // Now we need to delate Groups that do not exist.
        $result = $this->processDropGroups($oldGroups, $newGroups);
        if (\webtemplate\general\General::isError($result)) {
            $groupsUpdated = false;
        }


        // Return true if groups updated or a WEBTEMPLATE error if not
        if ($groupsUpdated == true) {
            return true;
        } else {
            $msg = gettext("ERROR Updating Database Groups");
            $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
            return $err;
        }
    }


    /**
     * Function to create the default database Users
     *
     * @param string $users       Array containing the group details.
     * @param array  $groupNumber Array containing the installed group ids
     *
     * @return mixed true if table created or WEBTEMPLATE error
     *
     * @access public
     */
    public function createUsers($users, $groupNumber)
    {
        $usersCreated = true;
        $userId = -1;

        // Add users from the uesr array in schema.php
        // Walk through the USERS Array
        foreach ($users as $name => $userdata) {
            // print the user being created.
            $this->schemaEcho("Creating user: $name");

            // Create the Data Array for inserting
            $encryptPasswd = \webtemplate\general\General::encryptPasswd(
                $userdata['passwd']
            );

            // If unable to create password abort save
            if ($encryptPasswd === false) {
                $errorMsg = gettext("ERROR Creating Database users. ");
                $errorMsg .= gettext("Unable to create Encrypted Password");
                return \webtemplate\general\General::raiseError(
                    $errorMsg,
                    DB_ERROR
                );
            }

            $insertdata = array (
                'user_name' => $name,
                'user_realname' => $userdata['realname'],
                'user_email' => $userdata['email'],
                'user_enabled' => $userdata['enabled'],
                'user_disable_mail' => 'N',
                'user_passwd' =>  $encryptPasswd,
                'passwd_changed' => $userdata['passwdchanged']
            );

            // Insert a new group to the database
            $result = $this->db->dbinsert('users', $insertdata);

            // Check if there was an error
            if (\webtemplate\general\General::isError($result)) {
                $errorMsg = gettext("ERROR Creating Database users. ");
                $errorMsg .= gettext("Unable to create user: ");
                $errorMsg .= $name;
                $errorMsg .= "\n";
                return \webtemplate\general\General::raiseError(
                    $errorMsg,
                    DB_ERROR
                );
            }


            // Get the Id of the iuser just inserted
            $userId = $this->db->dbinsertid(
                "users",
                "user_id",
                "user_name",
                $name
            );

            // Check for an error
            if (\webtemplate\general\General::isError($userId)) {
                $userId = -1;
                $errorMsg = gettext("ERROR Creating Database users. ");
                $errorMsg .= gettext("Unable get UserID for user: ");
                $errorMsg .= $name;
                $errorMsg .= "\n";
                return \webtemplate\general\General::raiseError(
                    $errorMsg,
                    DB_ERROR
                );
            }


            // Add the suer to any groups specified in the UserArray
            $result = $this->processUserGroups(
                $userId,
                $groupNumber,
                $userdata
            );
            if (\webtemplate\general\General::isError($result)) {
                $errorMsg = gettext("ERROR Creating Database users. ");
                $errorMsg .= gettext("Unable to create groups for user: ");
                $errorMsg .= $name;
                $errorMsg .= "\n";
                return \webtemplate\general\General::raiseError(
                    $errorMsg,
                    DB_ERROR
                );
            }

            // Add any user specific preferences.
            $result = $this->processUserPrefs(
                $userId,
                $userdata
            );

            if (\webtemplate\general\General::isError($result)) {
                $errorMsg = gettext("ERROR Creating Database users. ");
                $errorMsg .= gettext("Unable to create preferences for user: ");
                $errorMsg .= $name;
                $errorMsg .= "\n";
                return \webtemplate\general\General::raiseError(
                    $errorMsg,
                    DB_ERROR
                );
            }
        }

        return true;
    }

    /**
     * This function assigns new users created during the install process to groups
     *
     * @param integer $userId      The users id
     * @param array   $groupNumber The ids of each of the installed groups
     * @param array   $userdata    The array used to create the users
     *
     * @return mixed True is groups assigned pera error otherwise.
     *
     * @access private
     */
    private function processUserGroups($userId, $groupNumber, $userdata)
    {

        $usermapcreated = true;
        // Check and install user's groups if they exist
        if (array_key_exists('groups', $userdata)) {
            // Extract the group array from the main array
            $groups = $userdata['groups'];

            // Walk through the group array adding the details to the
            // user_group_map
            foreach ($groups as $groupId) {
                $mapdata = array (
                    'user_id'  => $userId,
                    'group_id' => $groupNumber[$groupId]
                );

                // Insert the group map
                $result = $this->db->dbinsert('user_group_map', $mapdata);

                // check if there is an error
                if (\webtemplate\general\General::isError($result)) {
                    $usermapcreated = false;
                }
            }
        }

        if ($usermapcreated == true) {
            return true;
        } else {
            // Else return a WEBTEMPLATE error
            $msg = gettext("ERROR Creating user group map");
            $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
            return $err;
        }
    }



    /**
     * This function creates user specific preferences. It is mainly used for
     * unit and Selenium Tests I
     *
     * @param integer $userId   The users id
     * @param array   $userdata The array used to create the users
     *
     * @return mixed True is groups assigned pera error otherwise.
     *
     * @access private
     */
    private function processUserPrefs($userId, $userdata)
    {
        $preferencesCreated = true;
        // check and install uers's preferences'
        if (array_key_exists('prefs', $userdata)) {
            // Extract the preferences array from the main user array
            $prefs = $userdata['prefs'];

            // Walk through the preferences array adding thye details
            // to the preferences table
            foreach ($prefs as $settingname => $settingvalue) {
                $insertData = array(
                    'user_id'      => $userId,
                    'settingname'  => $settingname,
                    'settingvalue' => $settingvalue
                );

                // Insert the preferences
                $result = $this->db->dbinsert('userprefs', $insertData);

                // Check if there was an error
                if (\webtemplate\general\General::isError($result)) {
                    $preferencesCreated = false;
                }
            }
        }
        if ($preferencesCreated == true) {
            return true;
        } else {
            // Else return a WEBTEMPLATE error
            $msg = gettext("ERROR Creating user preferences");
            $err = \webtemplate\general\General::raiseError($msg, DB_ERROR);
            return $err;
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
    private function schemaEcho($msg = "")
    {
        if (!isset($GLOBALS['unittest'])) {
            echo $msg . "\n";
        }
        return true;
    }

    /**
     * Up date an index to point to a new column
     *
     * @param string $key       The name of the table being amended
     * @param string $idxname   The bame of the index being amended
     * @param array  $oldColumn The old column the index applied to
     * @param array  $newColumn The nw column the index applies to
     *
     * @return mixed Boolean true if task completed.  WEBTEMPLATE error otherwise
     */
    private function updateIndex($key, $idxname, $oldColumn, $newColumn)
    {
        $schemaupdated = true;
        // Check if the INDEX Has changed.
        // If it has update it
        if ($oldColumn !== $newColumn) {
            $this->schemaEcho("Update INDEX");

            // Drop the OLD INDEX
            $result = $this->db->dropIndex($key, $idxname);
            if (\webtemplate\general\General::isError($result)) {
                $errorMsg = "Error ropping Indexes ";
                $errorMsg .= "on Table " . $key;
                $errorMsg . " during update\n";
                $schemaupdated = false;
            }

            // Create the new Index
            $result = $this->db->createIndex(
                $key,
                $idxname,
                $newColumn
            );
            if (\webtemplate\general\General::isError($result)) {
                $errorMsg = "Error Creating Indexes ";
                $errorMsg .= "on Table ";
                $errorMsg .= $key . " during update\n";
                $schemaupdated = false;
            }
        }

        if ($schemaupdated == true) {
            return true;
        } else {
            return \webtemplate\general\General::raiseError($errorMsg);
        }
    }



    /**
     * Update the indexes on a table in line with the new table definition in
     * $newTable
     *
     * @param string $key          The table being ammended
     * @param array  $newTable     The structure of the new table
     * @param array  $currentTable The structure of the old table
     *
     * @return mixed True if task complete WEBTEMPLATE error otherwise
     */
    private function processIndex($key, $newTable, $currentTable)
    {

        $schemaupdated = true;
        if (array_key_exists('INDEXES', $newTable)) {
            if (array_key_exists('INDEXES', $currentTable)) {
                foreach ($newTable['INDEXES'] as $idxname => $idxcolumn) {
                    if (!array_key_exists($idxname, $currentTable['INDEXES'])) {
                        // New Index Add it.
                        $result = $this->db->createIndex(
                            $key,
                            $idxname,
                            $idxcolumn
                        );
                        if (\webtemplate\general\General::isError($result)) {
                            $schemaupdated = false;
                            $this->schemaEcho($result->getMessage());
                            $errorMsg = $result->getMessage();
                        }
                    } else {
                        // Update indexes
                        $result = $this->updateIndex(
                            $key,
                            $idxname,
                            $currentTable['INDEXES'][$idxname]['COLUMN'],
                            $newTable['INDEXES'][$idxname]['COLUMN']
                        );
                        if (\webtemplate\general\General::isError($result)) {
                            $schemaupdated = false;
                            $this->schemaEcho($result->getMessage());
                            $errorMsg = $result->getMessage();
                        }
                    }
                }
            } else {
                // Table has never had indexes.  Add the new one.
                foreach ($newTable['INDEXES'] as $idxname => $idxcolumn) {
                    // New Index Add it.
                    $result = $this->db->createIndex(
                        $key,
                        $idxname,
                        $idxcolumn
                    );
                    if (\webtemplate\general\General::isError($result)) {
                        $schemaupdated = false;
                        $this->schemaEcho($result->getMessage());
                        $errorMsg = $result->getMessage();
                    }
                }
            }
        } else {
            // DROP the OLD Index
            if (array_key_exists('INDEXES', $currentTable)) {
                foreach ($currentTable['INDEXES'] as $idxname => $idxcolumn) {
                    $result = $this->db->dropIndex($key, $idxname);
                    if (\webtemplate\general\General::isError($result)) {
                        $errorMsg = "Error Dropping Indexes ";
                        $errorMsg .= "on Table " . $key . "\n";
                        $schemaupdated = false;
                    }
                }
            }
        }
        if ($schemaupdated == true) {
            return true;
        } else {
            return \webtemplate\general\General::raiseError($errorMsg);
        }
    }


    /**
     * Drop any groups that have been removed from the schemedata::$groups definition
     *
     * @param array $oldGroups The array containing the definition of the old groups
     * @param array $newGroups The array containing the definition of the new groups
     *
     * @return mixed True if task complete WEBTEMPLATE error otherwise
     */
    private function processDropGroups($oldGroups, $newGroups)
    {
        $groupsupdated = true;

        // Walk through the old array and drop groups that do not appear in the
        // new array
        foreach ($oldGroups as $name => $groupdata) {
            if (!array_key_exists($name, $newGroups)) {
                // The group does not exist in newGroups
                $this->schemaEcho("Deleting Group $name");

                $searchdata = array('group_name' => $name);
                $result = $this->db->dbdelete('groups', $searchdata);
                if (\webtemplate\general\General::isError($result)) {
                    // Error updating the group
                    $groupsupdated = false;
                    $errorMsg = gettext("Error dropping old groups");
                }
            }
        } // End of walk through for old groups

        if ($groupsupdated == true) {
            return true;
        } else {
            return \webtemplate\general\General::raiseError($errorMsg);
        }
    }
}
