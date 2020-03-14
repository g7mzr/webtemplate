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

namespace g7mzr\webtemplate\groups;

/**
 * Webtemplate Edit Users Group Class
 **/
class EditUsersGroups
{
    /**
     * Traits to be used by this Class
     */
    use TraitGroupFunctions;


    /**
     * Database Object
     *
     * @var \g7mzr\db\interfaces\InterfaceDatabaseDriver
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
     * Constructor
     *
     * @param \g7mzr\db\interfaces\InterfaceDatabaseDriver $db Database Object.
     *
     * @access public
     */
    public function __construct(\g7mzr\db\interfaces\InterfaceDatabaseDriver $db)
    {
        $this->db = $db;
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
     * This function returns the list of groups the user identified $userid
     * is a member off.  The array is returned in Parameter 2.
     *
     * @param integer $userid    Integer containing users ID No.
     * @param array   $grouplist Array containing list of users group.
     *
     * @return boolean true if search is ok or WEBTEMPLATE error type
     * @access public
     */
    final public function getUsersGroups(int $userid, array &$grouplist)
    {
        $searchok = true;

        // Add users to groups they are explicitly members off
        $result = $this->getUsersExplicitGroups($userid, $grouplist);
        if (\g7mzr\webtemplate\general\General::isError($result)) {
            $err = $result;
            $searchok = false;
        }

        $this->getUsersImplicitGroups($grouplist);

        if ($searchok == true) {
            return true;
        } else {
            return $err;
        }
    }

    /**
     * Function to get the list of groups a user is currently a member of
     *
     * @param integer $userid    Integer containing users ID No.
     * @param array   $grouplist Array containing list of users group.
     *
     * @return boolean true if search is ok or WEBTEMPLATE error type
     *
     * @access private
     */
    private function getUsersExplicitGroups(int $userid, array &$grouplist)
    {
         // Set default variable values
        $searchok = false;
        $fieldNames = array("group_id");
        $searchdata = array('user_id' => $userid);

        $uaodb = $this->db->dbselectmultiple(
            'user_group_map',
            $fieldNames,
            $searchdata,
            'group_id'
        );
        if (!\g7mzr\db\common\Common::isError($uaodb)) {
            $usersGroups = array();

            // Populate the output array with the records
            foreach ($uaodb as $uao) {
                $usersGroups[] = array('groupid' => chop($uao['group_id']));
            }
            $searchok = true;

            // Travis the results
            foreach ($usersGroups as $values) {
                $cnter = 0; // set counter for $groupList

                // Travis the groupList
                while ($cnter < count($grouplist)) {
                    // If the user is in the current group
                    // update the group list
                    if ($values['groupid'] == $grouplist[$cnter]['groupid']) {
                        $grouplist[$cnter]["useringroup"] = 'Y';
                    }
                    $cnter++;
                }
            }
        } else {
            if ($uaodb->getCode() == \DB_ERROR_NOT_FOUND) {
                $searchok = true;
            } else {
                $err =  new\g7mzr\webtemplate\application\Error($uaodb->getMessage(), $uaodb->getCode());
            }
        }


        if ($searchok == true) {
            return true;
        } else {
            return $err;
        }
    }

    /**
     * Function to get the list of groups a user is automatically a member of
     *
     * @param array $grouplist Array containing list of users group.
     *
     * @return boolean true if search is ok or WEBTEMPLATE error type
     *
     * @access private
     */
    private function getUsersImplicitGroups(array &$grouplist)
    {
        // Need to add user the groups they are automatically
        // members of if not explicitly
        $cnter = 0; // set counter for $groupList

        // Travis the groupList
        while ($cnter < count($grouplist)) {
            // If the group is an auto membership group and user is not
            // explicitly a member update the group list
            if ($grouplist[$cnter]["useringroup"] == 'N') {
                if ($grouplist[$cnter]["autogroup"] == 'Y') {
                    $grouplist[$cnter]["useringroup"] = 'I';
                }
            }
            $cnter++;
        }
        return true;
    }

    /**
     * This function creates the Change String to show which groups a user
     * has been added to or removed from.
     *
     * @param integer $userid     Integer containing users ID No.
     * @param array   $grouparray Array containing list of users group.
     *
     * @return boolean true if search is ok or\g7mzr\webtemplate error type
     * @access public
     */
    final public function groupsChanged(int $userid, array $grouparray)
    {

        // Set default variable data.
        $searchok = true;
        $groupdatachanged = false;
        $msg = '';

        $result = $this->getUsersExplicitGroups($userid, $grouparray);
        if (\g7mzr\webtemplate\general\General::isError($result)) {
            $err = $result;
            $searchok = false;
        }


        // Traverse the group array.
        foreach ($grouparray as $group) {
            if ($group['useringroup'] != $group['addusertogroup']) {
                // The user has been added to/removed from current group
                $groupdatachanged = true;
                if (($group['useringroup'] == 'N')
                    and  ($group['addusertogroup'] == 'Y')
                ) {
                    // The user has been added. Create message
                    $msg .= "Added to group: ";
                    $msg .= $group['groupname'] . "\n";
                } else {
                    // The user has been removed. Create message
                    $msg .= "Removed from group: ";
                    $msg .= $group['groupname'] . "\n";
                }
            } // end of if
        }  // End of foreach
        if ($searchok == true) {
            // All okay. Save the change string and return true
            $this->dataChanged = $msg;
            return $groupdatachanged;
        } else {
            // Problem.
            return $err;
        }
    }

    /**
     * This function updates the user_group_map with a list of
     * groups a user is in.
     *
     * @param integer $userid     Integer containing users ID No.
     * @param array   $grouparray Array containing list of users group.
     *
     * @return boolean true if search is ok or\g7mzr\webtemplate error type
     * @access public
     */
    final public function saveUsersGroups(int $userid, array $grouparray)
    {

        // Initialise variables
        $updateok = true;
        $datadeleted = false;

        $result = $this->db->startTransaction();
        if ($result == true) {
            // Delete the users existing entries
            $searchdata = array('user_id' => $userid);
            $result = $this->db->dbdelete('user_group_map', $searchdata);
            if (!\g7mzr\db\common\Common::isError($result)) {
                foreach ($grouparray as $group) {
                    if ($group['addusertogroup'] == 'Y') {
                        $grpid = $group['groupid'];

                        $data = array(
                            "user_id" => $userid,
                            "group_id" => $grpid
                        );
                        $result = $this->db->dbinsert('user_group_map', $data);

                        // $result = $this->db->saveUsersGroupIds($userid, $grpid);
                        if (\g7mzr\db\common\Common::isError($result)) {
                            $updateok = false;
                        }
                    }
                }
            } else {
                $updateok = false;
            }
            $result = $this->db->endTransaction($updateok);
            if ($updateok == true) {
                return true;
            } else {
                $msg = gettext("Unable to save groups");
                $err = \g7mzr\webtemplate\general\General::raiseError(
                    $msg,
                    \DB_ERROR
                );
                return $err;
            }
        } else {
            $msg = gettext("Unable to create database transaction");
            $err = \g7mzr\webtemplate\general\General::raiseError(
                $msg,
                \DB_ERROR_TRANSACTION
            );
            return $err;
        }
    }
}
