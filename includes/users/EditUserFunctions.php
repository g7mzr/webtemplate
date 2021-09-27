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
 * Global Edit User Functions
 */
class EditUserFunctions
{
    /**
     * ListUsers method
     *
     * This method lists the database users.
     *
     * This method searches the database using the search parameters input by
     * the user and outputs the result in a table containing username, realname
     * and e-mail address.  If no user is found an error page is displayed.
     *
     * @param \g7mzr\webtemplate\application\SmartyTemplate $tpl      Smarty Template class.
     * @param string                                        $template Template name.
     * @param EditUser                                      $editUser Edituser class.
     * @param string                                        $regexp   Regular expression for valid user names.
     * @param array                                         $data     The sanitised $_GET super array.
     *
     * @return boolean
     *
     * @access public
     */
    public static function listUsers(
        \g7mzr\webtemplate\application\SmartyTemplate $tpl,
        string &$template,
        EditUser $editUser,
        string $regexp,
        array &$data
    ) {
        // Get the type of search the user has chosen.  Valid values are:
        // username, email and realname.  Declare searchType as empty prior
        // to seing if it is set and a valid value has been selected.
        $searchType = '';
        if (isset($data['searchtype'])) {
            $regx = "/^(username)|(realname)|(email)$/";
            if (preg_match($regx, $data['searchtype']) == true) {
                $searchType = $data['searchtype'];
            }

            // Get the search data.  Declare searchData and empty prior to getting
            // and validating the data.  If searchType is not valid then don't
            // validate the search data.
            $searchData = '';
            if (isset($data['searchstr'])) {
                if ($searchType == "username") {
                    if (
                        \g7mzr\webtemplate\general\LocalValidate::username(
                            $data['searchstr'],
                            $regexp
                        )
                    ) {
                        $searchData = $data['searchstr'];
                    }
                } elseif ($searchType == "realname") {
                    if (
                        \g7mzr\webtemplate\general\LocalValidate::realname(
                            $data['searchstr']
                        )
                    ) {
                        $searchData = $data['searchstr'];
                    }
                } elseif ($searchType == "email") {
                    if (
                        \g7mzr\webtemplate\general\LocalValidate::email(
                            $data['searchstr']
                        )
                    ) {
                        $searchData = $data['searchstr'];
                    }
                }
            }
        }

        // If searchType is valid conduct the search
        if ($searchType != '') {
            // Conduct the search
            $resultArray = $editUser->search($searchType, $searchData);

            // Check if the search encounter errors
            if (!\g7mzr\webtemplate\general\General::isError($resultArray)) {
                // Populate the template with the results data.
                $usersFound = count($resultArray);
                $template = "users/list.tpl";
                $tpl->assign("RESULTS", $resultArray);
                $tpl->assign("USERSFOUND", $usersFound);
                return true;
            } else {
                // An error was encountered when running the search
                $template = "global/error.tpl";
                $tpl->assign("ERRORMSG", $resultArray->getMessage());
                $tpl->assign("HEADERMSG", gettext("Search Error"));
                return false;
            }
        } else {
            // The user sent an invalid search field.
            // This could be due to an attempted hack
            $tpl->assign("MSG", gettext("Invalid Search Field"));
            $template = "users/search.tpl";
            return false;
        }
    }


    /**
     * The newUser method
     *
     * This method displays and empty form to be completed for a new user.
     *
     * @param \g7mzr\webtemplate\application\SmartyTemplate $tpl      Smarty Template class.
     * @param string                                        $template Template name.
     *
     * @return boolean
     *
     * @access public
     */
    public static function newUser(
        \g7mzr\webtemplate\application\SmartyTemplate $tpl,
        string &$template
    ) {
        // Initialise global variables for this method.
        //global $tpl, $template;

        //Set dummy results variable
        $results[] = array("userid" => '',
                               "username" => '',
                               "realname" => '',
                               "useremail" => '',
                               "userenabled" => '',
                               "userdisablemail" => '',
                               "passwd" => '',
                               "lastseendate" => ''
            );

        $tpl->assign("RESULTS", $results);
        $tpl->assign("READONLY", false);

        // Select and configure the template
        $template = "users/edit.tpl";
        $tpl->assign("PAGETITLE", gettext("New User"));
        return true;
    }

    /**
     * The editUser method
     *
     * This method displays the selected user for editing
     * If no user is found an error page is displayed.
     *
     * @param \g7mzr\webtemplate\application\SmartyTemplate $tpl      Smarty Template class.
     * @param string                                        $template Template name.
     * @param EditUser                                      $editUser Edituser class.
     * @param \g7mzr\webtemplate\groups\EditUsersGroups     $groups   Edit Users Group Class.
     * @param array                                         $data     The sanitised $_GET super array.
     *
     * @return void
     *
     * @access public
     */
    public static function editUser(
        \g7mzr\webtemplate\application\SmartyTemplate $tpl,
        string &$template,
        EditUser $editUser,
        \g7mzr\webtemplate\groups\EditUsersGroups $groups,
        array &$data
    ) {
        // Initialise global variables for this method.
        //global $tpl, $editUser, $template, $groups;
        // Check that the UserID is a valid number
        // The UserID does not have to exist at this point
        $validUserID = false;
        if (isset($data['userid'])) {
            if (\g7mzr\webtemplate\general\LocalValidate::dbid($data['userid'])) {
                $userId = $data['userid'];
                $validUserID = true;
            }
        }

        if ($validUserID == true) { //User id is valid
            // Get the users information from the database
            $resultArray = $editUser->getuser($userId);
            if (!\g7mzr\webtemplate\general\General::isError($resultArray)) {
                // Got the user's information
                $template = "users/edit.tpl";


                // Get the list of groups
                $groupArray = $groups->getGroupList();
                if (!\g7mzr\webtemplate\general\General::isError($groupArray)) {
                    // Select groups user is a member off.
                    $addUsersGroups = $groups->getUsersGroups($userId, $groupArray);
                    if (\g7mzr\webtemplate\general\General::isError($addUsersGroups)) {
                        // An database error was encountered
                        $tpl->assign("MSG", $addUsersGroups->getMessage());
                    }

                    // Assign the array to the template
                    $tpl->assign("GROUPRESULT", $groupArray);
                } else {
                    // An error was encountered getting the group list
                    $tpl->assign("GROUPRESULT", '');
                    $tpl->assign("MSG", $groupArray->getMessage());
                }

                // Set the edit user information
                $tpl->assign("RESULTS", $resultArray);

                //Set the username as readonly
                $tpl->assign("READONLY", true);

                // Set the page title
                $pageTitle = gettext("Edit User") . ": ";
                $pageTitle .= $resultArray[0]['username'];
                $pageTitle .= ' (' . $resultArray[0]['realname'] . ')';
                $tpl->assign("PAGETITLE", $pageTitle);
            } else {
                // No user information was returned
                $template = "users/search.tpl";
                $msg = $resultArray->getMessage() . ". ";
                $msg .= gettext("Please Try Again");
                $tpl->assign("MSG", $msg);
            }
        } else {
            $msg = gettext("You have chosen an invalid user.");
            $msg .= ' ' . gettext("Please try again");
            $tpl->assign("MSG", $msg);
            $template = "users/search.tpl";
        }
    }

    /**
     * The saveUser method
     *
     * This method saves the edited user information in the database.
     *
     * @param \g7mzr\webtemplate\application\SmartyTemplate $tpl        Template class.
     * @param string                                        $template   Template name.
     * @param EditUser                                      $editUser   Edituser class.
     * @param \g7mzr\webtemplate\groups\EditUsersGroups     $groups     Edit Users Group Class.
     * @param array                                         $userparams Configuration vars.
     * @param array                                         $data       Sanitised $_POST.
     *
     * @return boolean
     *
     * @access public
     */
    public static function saveUser(
        \g7mzr\webtemplate\application\SmartyTemplate $tpl,
        string &$template,
        EditUser $editUser,
        \g7mzr\webtemplate\groups\EditUsersGroups $groups,
        array $userparams,
        array &$data
    ) {

        $userSaved = true;
        $userDataOk = true;
        $userdatatosave = false;
        $groupdatatosave = false;
        $msg = '';

        // Validate the USER Data received
        $resultArray = $editUser->validateUserData($data, $userparams);
        if ($resultArray[0]['msg'] != '') {
            $msg = $resultArray[0]['msg'];
            $userDataOk = false;
        }

        // Check if a new user exist in the database
        if (($userDataOk == true) and ($resultArray[0]['userid'] == '0')) {
            $checkname = $editUser->checkUserExists($resultArray[0]['username']);
            if (\g7mzr\webtemplate\general\General::isError($checkname)) {
                $template = "global/error.tpl";
                $msg = gettext("An error occured checking if the user exists.");
                $msg .= " " . gettext("Please try again.");
                $tpl->assign("ERRORMSG", $msg);
                $tpl->assign("HEADERMSG", gettext("Database Error"));
                return false;
            } else {
                if ($checkname == true) {
                    $msg = gettext("User") . " " . $resultArray[0]['username'];
                    $msg .= " " . gettext("exists in the database");
                    $userDataOk = false;
                }
            }
        }
        if ($resultArray[0]['userid'] == '0') {
            $newUser = true;
        } else {
            $newUser = false;
        }


        // Load the grouparray with data
        $groupArray = $groups->getGroupList();
        if (\g7mzr\webtemplate\general\General::isError($groupArray)) {
            $template = "global/error.tpl";
            $msg = \gettext("An error occured when retrieving the groups.");
            $msg .= " " . \gettext("Please try again.");
            $tpl->assign("ERRORMSG", $msg);
            $tpl->assign("HEADERMSG", gettext("Database Error"));
            return false;
        }
        $arrayCount = 0;
        while ($arrayCount < count($groupArray)) {
            if (isset($data[$groupArray[$arrayCount]['groupname']])) {
                $groupArray[$arrayCount]["addusertogroup"] = 'Y';
            }
            $arrayCount++;
        }

        // Check if the Userdata or Group Data has changed for existing user
        // providing the User Data is valid
        if (($userDataOk == true) and ($resultArray[0]['userid'] != '0')) {
            $userdatatosave = $editUser->dataChanged(
                $resultArray[0]['userid'],
                $resultArray[0]['username'],
                $resultArray[0]['realname'],
                $resultArray[0]['useremail'],
                $resultArray[0]['passwd'],
                $resultArray[0]['userdisablemail'],
                $resultArray[0]['userenabled']
            );

            $groupchangedresult = $groups->groupsChanged(
                $resultArray[0]['userid'],
                $groupArray
            );
            if (\g7mzr\webtemplate\general\General::isError($groupchangedresult)) {
                $groupdatatosave = false;
            } else {
                $groupdatatosave = $groupchangedresult;
            }

            if (($userdatatosave === false) and ($groupdatatosave === false)) {
                $msg = \gettext("You have not made any changes");
                $userDataOk = false;
            }
        }

        // Save a new user
        if (($userDataOk == true) and ($resultArray[0]['userid'] == '0')) {
            $userdatatosave = true;
        }


        // Save User
        if ($userdatatosave === true) {
            $result = $editUser->saveUser(
                $resultArray[0]['userid'],
                $resultArray[0]['username'],
                $resultArray[0]['realname'],
                $resultArray[0]['useremail'],
                $resultArray[0]['passwd'],
                $resultArray[0]['userdisablemail'],
                $resultArray[0]['userenabled'],
                $resultArray[0]['passwdchange']
            );
            if (\g7mzr\webtemplate\general\General::isError($result)) {
                $template = "global/error.tpl";
                $msg = \gettext("An error occured when saving the user's details.");
                $msg .= " " . \gettext("Please try again.");
                $tpl->assign("ERRORMSG", $msg);
                $tpl->assign("HEADERMSG", gettext("Database Error"));
                return false;
            }
            $resultArray = $editUser->getUser($resultArray[0]['userid']);
            if (\g7mzr\webtemplate\general\General::isError($resultArray)) {
                $template = "global/error.tpl";
                $msg = \gettext("An error occured retrieving the user's details.");
                $msg .= " " . \gettext("Please try again.");
                $tpl->assign("ERRORMSG", $msg);
                $tpl->assign("HEADERMSG", gettext("Database Error"));
                return false;
            }
        }


        // Save Groups
        if ($groupdatatosave === true) {
            $result = $groups->saveUsersGroups(
                $resultArray[0]['userid'],
                $groupArray
            );
            if (\g7mzr\webtemplate\general\General::isError($result)) {
                $template = "global/error.tpl";
                $msg = \gettext("An error occured when saving the user's groups.");
                $msg .= " " . \gettext("Please try again.");
                $tpl->assign("ERRORMSG", $msg);
                $tpl->assign("HEADERMSG", gettext("Database Error"));
                return false;
            }
            $groupArray = $groups->getGroupList();
            $addUsersGroups = $groups->getUsersGroups(
                $resultArray[0]['userid'],
                $groupArray
            );
            if (\g7mzr\webtemplate\general\General::isError($addUsersGroups)) {
                $template = "global/error.tpl";
                $msg = \gettext("An error occured retrieving the user's groups.");
                $msg .= " " . \gettext("Please try again.");
                $tpl->assign("ERRORMSG", $msg);
                $tpl->assign("HEADERMSG", gettext("Database Error"));
                return false;
            }
        }

        if ($userDataOk === true) {
            if ($newUser === true) {
                $msg = gettext("User Created");
                $msg .= " " . $resultArray[0]['username'];
                $pagetitle =  gettext("User");
                $pagetitle .= " " . $resultArray[0]['username'];
                $pagetitle .= " " . gettext("Created");
            } else {
                $msg = gettext("User changed");
                $msg .= " " . $resultArray[0]['username'] . "\n\n";
                $msg = $msg . $editUser->getChangeString();
                $msg = $msg . $groups->getChangeString();
                $pagetitle =  gettext("User");
                $pagetitle .= " " . $resultArray[0]['username'];
                $pagetitle .= " " . gettext("Updated");
            }
        } else {
            if ($resultArray[0]['userid'] <> '0') {
                $pagetitle = 'Edit User';
                $pagetitle .= ": " . $resultArray[0]['username'];
                $pagetitle .= ' (' . $resultArray[0]['realname'] . ')';
            } else {
                $pagetitle = 'New User';
            }
        }

        // Set up the template variables
        $template = "users/edit.tpl";
        $tpl->assign("RESULTS", $resultArray);
        $tpl->assign("GROUPRESULT", $groupArray);
        if ($resultArray[0]['userid'] <> '0') {
            $tpl->assign("READONLY", true);
        }
        $tpl->assign("PAGETITLE", $pagetitle);
        if ($msg != '') {
            $tpl->assign("MSG", $msg);
        }
        return true;
    }
}
