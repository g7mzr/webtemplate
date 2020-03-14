<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Users
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\users;

/**
 * User Groups Class
 **/
class Groups
{
    /**
     * Traits to be used by this Class
     */
    use \g7mzr\webtemplate\groups\TraitGroupFunctions;// {getGrouplist as protected;}

    /**
     * The userid of the user
     *
     * @var    integer
     * @access protected
     */
    protected $userId = 0;

    /**
     * Database Connection Object
     *
     * @var   \g7mzr\webtemplate\db\driver\InterfaceDatabaseDriver
     * @access protected
     */
    protected $db = null;

    /**
     * Last Message
     *
     * @var    string
     * @access protected
     */
    protected $lastMsg = '';

    /**
     * User Needs Access to Administration Pages
     *
     * @var    boolean
     * @access protected
     */
    protected $adminAccess = false;

    /**
     * List of groups users is a member off
     *
     * @var    array
     * @access protected
     */
    protected $groups = array();

    /**
     * List of groups User is a member off including description
     *
     * @var    array
     * @access protected
     */
    protected $groupDescription = array();

    /**
     * List of groups ids users is a member off
     *
     * @var    array
     * @access protected
     */
    protected $groupids = array();

    /**
    * User Class Constructor
    *
    * @param \g7mzr\db\interfaces\InterfaceDatabaseDriver $db     Database Connection Object.
    * @param integer                                      $userId Id of current user.
    *
    * @access public
    */
    public function __construct(
        \g7mzr\db\interfaces\InterfaceDatabaseDriver $db,
        int $userId = 0
    ) {
        $this->db     = $db ;
        $this->userId = $userId;

        if ($this->userId > 0) {
            $this->loadusersgroups($userId);
        }
    } // end constructor

    /**
     * Get the last message created by class
     *
     * @return string Last Message created by CLASS
     *
     * @access public
     */
    final public function getLastMsg()
    {
        return $this->lastMsg;
    }

    /**
     * Function to load the users groups separate from the class constructor
     *
     * @param integer $userId Id of current user.
     *
     * @return boolean True if groups loaded false otherwise.
     * @access public
     */
    public function loadusersgroups(int $userId)
    {
        $this->userId = $userId;

        // Get the users groups
        $result = $this->findGroups();
        if (\g7mzr\db\common\Common::isError($result)) {
            $this->lastMsg = gettext("Error gettings Users permissions");
            return false;
        } else {
            return true;
        }
    }

    /**
    * Get the admin status of the user
    *
    * @return boolean True if the user has admin right
    *
    * @access public
    */
    final public function getAdminAccess()
    {
        return $this->adminAccess;
    }

    /**
    * Get the userlist of groups
    *
    * @return array The list of groups a user is in
    *
    * @access public
    */
    final public function getGroups()
    {
        return $this->groups;
    }

    /**
    * Get the user list of groups including description
    *
    * @return array The list of groups a user is in
    *
    * @access public
    */
    final public function getGroupDescription()
    {
        return $this->groupDescription;
    }

    /**
    * Check if the current user is in a specific group
    *
    * @param string $groupName The name of the group to check.
    *
    * @return boolean true if user is in $group_name otherwise false
    *
    * @access public
    */
    final public function checkGroup(string $groupName)
    {

        if ((in_array($groupName, $this->groups))
            or (in_array('admin', $this->groups))
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to identify the groups the users is a member off
     *
     * @return mixed Boolean True if search completed ok generalError if failed
     *
     * @access private
     */
    private function findGroups()
    {
        $result = $this->usersDirectGroups();
        if (\g7mzr\db\common\Common::isError($result)) {
            if ($result->getCode() != DB_ERROR_NOT_FOUND) {
                return $result;
            }
        }

        $result = $this->autoGroups();
        if (\g7mzr\db\common\Common::isError($result)) {
            return $result;
        }

        $result = $this->convertGroupIdsToNames();
        if (\g7mzr\db\common\Common::isError($result)) {
            return $result;
        }
        return true;
    }

    /**
     * Function to identify the groups the users is direct member off
     *
     * @return mixed Boolean True if search completed ok generalError if failed
     *
     * @access private
     */
    private function usersDirectGroups()
    {
        // Get the groups the user is a member off
        $fields = array('group_id');
        $searchData = array("user_id" => $this->userId);
        $usersGroupsList = $this->db->dbselectMultiple(
            'user_group_map',
            $fields,
            $searchData
        );
        if (\g7mzr\db\common\Common::isError($usersGroupsList)) {
            return $usersGroupsList;
        }
        foreach ($usersGroupsList as $group) {
            $this->groupids[] = $group['group_id'];
        }
        //print_r($this->groupids);
        return true;
    }

    /**
     * Function to identify the groups the users is automatically a member off
     *
     * @return mixed Boolean True if serach completed ok generalError if failed
     *
     * @access private
     */
    private function autoGroups()
    {
        // Get the list of groups used by Webtemplate
        $fields = array('group_id');
        $search = array('group_autogroup' => 'Y');
        $groupList = $this->db->dbselectMultiple('groups', $fields, $search);
        if (\g7mzr\db\common\Common::isError($groupList)) {
            return $groupList;
        }

        // Walk through both arrays looking for a match
        foreach ($groupList as $group) {
            if (!in_array($group['group_id'], $this->groupids)) {
                $this->groupids[] = $group['group_id'];
            }
        }
        //print_r($this->groupids);

        return true;
    }



    /**
     * Function to convert groupids to names
     *
     * @return mixed Boolean True if conversion ok generalError if failed
     *
     * @access private
     */
    private function convertGroupIdsToNames()
    {
        // Set local variables
        $adminrights = false;

        // Get the list of groups used by Webtemplate
        $groupList = $this->getGroupList();
        if (\g7mzr\db\common\Common::isError($groupList)) {
            return $groupList;
        }

        // Walk through both arrays looking for a match
        foreach ($groupList as $group) {
            foreach ($this->groupids as $userGroup) {
                if ($group['groupid'] == $userGroup) {
                    $this->groups[] = $group['groupname'];
                    $this->groupDescription[] = array(
                        'groupname' => $group['groupname'],
                        'description' => $group['description'],
                        'autogroup' => $group['autogroup']
                    );
                    if (($group['admingroup'] == 'Y') and ($adminrights === false)) {
                        $this->adminAccess = true;
                        $adminrights = true;
                    }
                }
            }
        }
        //print_r($this->groupDescription);

        return true;
    }
}
