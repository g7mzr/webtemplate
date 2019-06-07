<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Admin Parameters
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace webtemplate\groups;

/**
 * Webtemplate Edit Group Class
 *
 **/
class EditGroups
{
    /**
     * Traits to be used by this Class
     */
    use TraitGroupFunctions;

    /**
     * @var \webtemplate\db\DB
     *
     * @access protected
     */
    protected $db = null;

    /**
     * Data changed
     *
     * @var    string
     * @access protected
     */
    protected $dataChanged = '';

    /**
     * Error Message
     *
     * @var    string
     * @access protected
     */
    protected $message = '';
    /**
     * Admin Group ID
     *
     * @var   integer
     * @acces protected
     */
    protected $admingroupid = 1;

    /**
     * Constructor
     *
     * @param \webtemplate\db\driver\InterfaceDatabaseDriver $db Database Object.
     *
     * @access public
     */
    public function __construct(\webtemplate\db\driver\InterfaceDatabaseDriver $db)
    {
        $this->db = $db;

        // Get the Admin Group ID.  It will be needed if new groups are added
        $fields = array('group_id');
        $srchdata = array('group_name' => 'admin');
        $sqlresult = $this->db->dbselectsingle('groups', $fields, $srchdata);
        if (!\webtemplate\general\General::isError($sqlresult)) {
            $this->admingroupid = $sqlresult['group_id'];
        }
    } // end constructor

     /**
     * This function returns the contents of the ChangeString variable
     * It contains the list of fields which have been changed
     *
     * @return string List of fields which have changed
     * @access public
     */
    final public function getChangeString()
    {
        return $this->dataChanged;
    }

     /**
     * This function returns the last error message
     *
     * @return string Last error message
     * @access public
     */
    final public function getMessage()
    {
        return $this->message;
    }

     /**
     * This function returns information on a single group
     *
     * @param integer $groupId Integer containing Group ID No.
     *
     * @return array Group data if search is ok or webtemplate error type
     * @access public
     */
    final public function getSingleGroup(int $groupId)
    {

        // Initalise local variables
        $gotdataok = false;
        $gData = array();
        $fieldNames = array(
            "group_id",
            "group_name",
            "group_description",
            "group_useforproduct",
            "group_editable",
            "group_autogroup"
        );
        $searchdata = array('group_id' => $groupId);

        $gao = $this->db->dbselectsingle('groups', $fieldNames, $searchdata);

        if (!\webtemplate\general\General::isError($gao)) {
            $gData[] = array("groupid" => $gao['group_id'],
                             "groupname" => $gao['group_name'],
                             "description" => $gao['group_description'],
                             "useforproduct" => $gao['group_useforproduct'],
                             "autogroup" => $gao["group_autogroup"],
                             "editable" => $gao['group_editable']);
            $gotdataok = true;
        } else {
            $err = $gao;
        }
        if ($gotdataok == true) {
            return $gData;
        } else {
            return $err;
        }
    }

    /**
     * This function checks if a group is updated.  If it is it saves their
     * Change string in the class dataChanged variable and returns true.
     *
     * @param integer $gid      Integer containing Group ID No.
     * @param string  $gname    Name of Group.
     * @param string  $gdesc    Description od group.
     * @param string  $gproduct Group can be used Product access control.
     * @param string  $gauto    Automatic Group Membership Control.
     *
     * @return boolean true if search is ok or webtemplate error type
     * @access public
     */
    final public function groupDataChanged(
        int $gid,
        string $gname,
        string $gdesc,
        string $gproduct,
        string $gauto
    ) {

        // Initialise Local Variables
        $searchok = false;
        $groupdatachanged = false;
        $this->dataChanged = "";

        // Check if this is a new or existing group
        if ($gid <> '0') {
            // This is a existing group
            $fieldNames = array(
                "group_id",
                "group_name",
                "group_description",
                "group_useforproduct",
                "group_editable",
                "group_autogroup"
            );
            $searchdata = array('group_id' => $gid);
            $gao = $this->db->dbselectsingle('groups', $fieldNames, $searchdata);

            if (!\webtemplate\general\General::isError($gao)) {
                $searchok  = true;
                $groupdatachanged = $this->groupChangeString(
                    $gname,
                    $gdesc,
                    $gproduct,
                    $gauto,
                    $gao
                );
            } else {
                $err = $gao;
            }
        } else {
            // New Group need to return true
            $searchok = true;
            $this->dataChanged = gettext("New Group");
            $groupdatachanged = true;
        }

        if ($searchok == true) {
            return $groupdatachanged;
        } else {
            return $err;
        }
    }

    /**
     * Function to create the change string for an edited group
     *
     * @param string $gname    Name of Group.
     * @param string $gdesc    Description od group.
     * @param string $gproduct Group can be used Product access control.
     * @param string $gauto    Automatic Group Membership Control.
     * @param array  $gao      Existing group data.
     *
     * @return boolean true if group data hase changed false otherwise
     * @access private
     */
    private function groupChangeString(
        string $gname,
        string $gdesc,
        string $gproduct,
        string $gauto,
        array $gao
    ) {
        $groupdatachanged = false;

        // Check if the name has changed.
        // If is has update the change string
        if (strcmp(chop($gao['group_name']), chop($gname)) != 0) {
            $this->dataChanged .= gettext("Group Name Changed") . "\n";
            $groupdatachanged = true;
        }

        // Check if the description has changed.
        // If is has update the change string
        $grpdesc = chop($gao['group_description']);
        if (strcmp($grpdesc, chop($gdesc)) != 0) {
            $this->dataChanged .= gettext("Group Description Changed") . "\n";
            $groupdatachanged = true;
        }

        // Check if the useforproduct has changed.
        // If is has update the change string
        if ($gao['group_useforproduct'] != $gproduct) {
            $this->dataChanged .= gettext("Group Use For Product Changed") . "\n";
            $groupdatachanged = true;
        }

        // Check if the autogroup has changed.
        // If is has update the change string
        if ($gao['group_autogroup'] != $gauto) {
            $this->dataChanged .= gettext("Group Auto Membership Changed") . "\n";
            $groupdatachanged = true;
        }

        if ($groupdatachanged == false) {
            $this->dataChanged = gettext("You have not made any changes");
        }

        // Check if Group is a system Group
        if ($gao['group_editable'] == 'N') {
            $groupdatachanged = false;
            $this->dataChanged = gettext("You cannot edit System Groups");
        }

        return$groupdatachanged;
    }


    /**
     * This function saves the new group data in the database.
     *
     * @param integer $gid       Group ID No.
     * @param string  $gname     Name of Group.
     * @param string  $gdesc     Description of group.
     * @param string  $gproduct  Group can be used Product access control.
     * @param string  $gauto     Automatic Group Membership Control.
     * @param string  $geditable User can edit the group. (N for a System Group).
     *
     * @return boolean true if search is ok or webtemplate error type
     * @access public
     */
    final public function saveGroup(
        int &$gid,
        string $gname,
        string $gdesc,
        string $gproduct,
        string $gauto,
        string $geditable = 'Y'
    ) {

        $saveok = true;
        if ($gid == '0') {
            // Insert a new group to the database
            $groupdata = array (
                "group_name"          => $gname,
                "group_description"   => $gdesc,
                "group_useforproduct" => $gproduct,
                "group_autogroup"     => $gauto,
                "group_editable"      => $geditable
            );

            $result = $this->db->dbinsert('groups', $groupdata);
            if (!\webtemplate\general\General::isError($result)) {
                $gid = $this->db->dbinsertid(
                    "groups",
                    "group_id",
                    "group_name",
                    $gname
                );
                if (\webtemplate\general\General::isError($gid)) {
                    $gid = -1;
                    $saveok = false;
                    $errorMsg = gettext("Error getting id for group ") . $gname;
                }
            } else {
                $saveok = false;
                $errorMsg = gettext("Errors Saving Group ") . $gname;
            }
        } else {
            // Update an existing group;
            $groupdata = array (
                "group_description"   => $gdesc,
                "group_useforproduct" => $gproduct,
                "group_autogroup"     => $gauto,
                "group_editable"      => $geditable
            );
            $searchdata = array(
                "group_id" => $gid
            );
            $result = $this->db->dbupdate("groups", $groupdata, $searchdata);
            if (\webtemplate\general\General::isError($result)) {
                $saveok = false;
                $errorMsg = gettext("Error updating group: ") . $gname;
            }
        }

        if ($saveok) {
            return true;
            ;
        } else {
            return \webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }

    /**
     * This function deletes group data from the database.
     *
     * @param integer $groupId Integer to Group ID No.
     * @param boolean $sysgrp  Set true to enable deletion of system group.
     *
     * @return boolean true if search is ok or webtemplate error type
     * @access public
     */
    final public function deleteGroup(int $groupId, bool $sysgrp = false)
    {

        // Initialise Local Variable
        $deleteok = true;
        if ($sysgrp == true) {
            $searchdata = array('group_id' => $groupId);
        } else {
            $searchdata = array('group_id' => $groupId, 'group_editable' => 'Y');
        }
        $result = $this->db->dbdelete('groups', $searchdata);
        if (\webtemplate\general\General::isError($result)) {
            $deleteok = false;
            $errorMsg = $result->getMessage();
        }
        // If delete okay return true.  If not return webtemplate error
        if ($deleteok) {
            return $this->db->rowCount();
        } else {
            return \webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }

    /**
     * This function checks if a group exits the database.
     * It returns TRUE if the group exits or false if the group does not exist.
     *
     * @param string $groupname String containing the group name.
     *
     * @return boolean true if group exists, false if not or webtemplate error type
     * @access public
     */
    final public function checkGroupExists(string $groupname)
    {

        // Initalise local variables
        $searchok = true;
        $groupExists = false;
        $fieldNames = array(
            "group_id",
            "group_name",
            "group_description",
            "group_useforproduct",
            "group_editable",
            "group_autogroup"
        );
        $searchdata = array('group_name' => $groupname);
        $gao = $this->db->dbselectsingle('groups', $fieldNames, $searchdata);

        if (!\webtemplate\general\General::isError($gao)) {
            $groupExists = true;
        } else {
            if ($gao->getCode() != \DB_ERROR_NOT_FOUND) {
                $err = $gao;
                $searchok = false;
            }
        }

        // Check Search reults.
        if ($searchok) {
            // True if group exits
            // False ig group does not exits
            return $groupExists;
        } else {
            // An error was encountered
            return $err;
        }
    }

     /**
     * This function validated the group data.
     *
     * @param array $inputArray Array containing the user data to be validated.
     *
     * @return array Validated user data.
     * @access public
     */
    final public function validateGroupData(array &$inputArray)
    {

        // Initalise Local Variables
        $groupDataOk = true;

        // Get the user input.  Transfer to local variables from $inputArray
        if (isset($inputArray['groupid'])) {
            $groupId = substr($inputArray['groupid'], 0, 5);
        } else {
            $groupId = '';
        }
        if (isset($inputArray['groupname'])) {
            $groupName = substr($inputArray['groupname'], 0, 25);
        } else {
            $groupName = '';
        }
        if (isset($inputArray['description'])) {
            $groupDescription = substr($inputArray['description'], 0, 225);
        } else {
            $groupDescription = '';
        }
        if (isset($inputArray['useforproduct'])) {
            $groupUseForProduct = 'Y';
        } else {
            $groupUseForProduct = 'N';
        }

        if (isset($inputArray['autogroup'])) {
            $groupAutoGroup = 'Y';
        } else {
            $groupAutoGroup = 'N';
        }

        // Validate user input

        $msg = '';
        if (!\webtemplate\general\LocalValidate::dbid($groupId)) {
            $msg = $msg . gettext("Invalid Group Id") . "\n";
            $groupDataOk = false;
        }
        if (!\webtemplate\general\LocalValidate::groupName($groupName)) {
            $msg = $msg . gettext("Invalid Group Name") . "\n";
            $groupDataOk = false;
        }

        $tempstr = $groupDescription;
        if (!\webtemplate\general\LocalValidate::groupDescription($tempstr)) {
            $msg = $msg . gettext("Invalid Group Description") . "\n";
            $groupDataOk = false;
        }

        $resultArray[] = array("groupid" => $groupId,
                               "groupname" => $groupName,
                               "description" => $groupDescription,
                               "useforproduct" => $groupUseForProduct,
                               "autogroup" => $groupAutoGroup,
                               "msg" => $msg);
        return $resultArray;
    }


    /**
     * This function gets the groupid from a name
     * It returns the groupid if the group exist or an error.
     *
     * @param string $groupname String containing the group name.
     *
     * @return integer The group ID or a webtemplate error type
     * @access public
     */
    final public function getGroupid(string $groupname)
    {

        // Initalise the search

        $fieldNames = array(
            "group_id",
            "group_name",
            "group_description",
            "group_useforproduct",
            "group_editable",
            "group_autogroup"
        );
        $searchdata = array('group_name' => $groupname);
        $gao = $this->db->dbselectsingle('groups', $fieldNames, $searchdata);

        if (!\webtemplate\general\General::isError($gao)) {
            return $gao['group_id'];
        } else {
            // An error was encountered
            return $gao;
        }
    }
}
