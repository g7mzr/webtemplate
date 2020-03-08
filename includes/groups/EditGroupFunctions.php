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
 * Global GroupFunctions
 */
class EditGroupFunctions
{
    /**
     * The listGroups method
     *
     * This method lists the database groups.
     *
     * @param \webtemplate\application\SmartyTemplate $tpl      Smarty Object.
     * @param EditGroups                              $groups   Edit Group Object.
     * @param string                                  $template Name of template.
     *
     * @return void
     *
     * @access public
     */
    public static function listGroups(
        \webtemplate\application\SmartyTemplate $tpl,
        EditGroups $groups,
        string &$template
    ) {
        // Get the list of current Groups
        $groupArray = $groups->getGroupList();

        // Check is the list has been returned ok
        if (!\webtemplate\general\General::isError($groupArray)) {
            // List returned ok.  Display
            $tpl->assign("RESULTS", $groupArray);
            $template = "groups/list.tpl";
        } else {
            // Database error getting group list
            // Clear the Template Msg box.
            $tpl->assign('MSG', '');

            // Display the error template.
            $template = "global/error.tpl";
            $msg =  gettext("Error encountered when retreiving groups.");
            $header = gettext("Database Error");
            $tpl->assign("ERRORMSG", $msg);
            $tpl->assign("HEADERMSG", $header);
        }
    }

    /**
     * The newGroup method
     *
     * This method displays an empty form to create a new group.
     *
     * @param \webtemplate\application\SmartyTemplate $tpl      Smarty Object.
     * @param string                                  $template Name of template.
     *
     * @return boolean
     *
     * @access public
     */
    public static function newGroup(
        \webtemplate\application\SmartyTemplate $tpl,
        string &$template
    ) {
        //Set default blank results
        $resultArray[] = array(
            "groupid" => '',
            "groupname" => '',
            "description" => '',
            "useforproduct" => '',
            "autogroup" => '',
            "editable" => 'Y');
        $tpl->assign("RESULTS", $resultArray);

        // Set the template and page name.
        $template = "groups/edit.tpl";
        $tpl->assign("PAGETITLE", gettext("New Group"));
        return true;
    }

    /**
     * The editGroup method
     *
     * This method displays a group for editing.
     *
     * @param \webtemplate\application\SmartyTemplate $tpl      Smarty Object.
     * @param EditGroups                              $groups   Edit Group Object.
     * @param string                                  $template Name of template.
     * @param string                                  $groupId  Group to be edited.
     *
     * @return boolean
     *
     * @access public
     */
    public static function editGroup(
        \webtemplate\application\SmartyTemplate $tpl,
        EditGroups $groups,
        string &$template,
        string $groupId
    ) {
        if (!EditGroupFunctions::getGroupData($tpl, $groups, $template, $groupId)) {
            return false;
        }
        $tpl->assign("PAGETITLE", gettext("Edit Group"));
        $template = "groups/edit.tpl";
        return true;
    }


    /**
     * The confirmDeleteGroup method
     *
     * This method displays the group to be deleted for confirmation
     *
     * @param \webtemplate\application\SmartyTemplate $tpl      Smarty Object.
     * @param EditGroups                              $groups   Edit Group Object.
     * @param string                                  $template Name of template.
     * @param string                                  $groupId  Group to be edited.
     *
     * @return boolean
     *
     * @access public
     */
    public static function confirmDeleteGroup(
        \webtemplate\application\SmartyTemplate $tpl,
        EditGroups $groups,
        string &$template,
        string $groupId
    ) {
        if (!EditGroupFunctions::getGroupData($tpl, $groups, $template, $groupId)) {
            return false;
        }
        $template = "groups/delete.tpl";
        $tpl->assign("PAGETITLE", gettext("Delete Group"));
        return true;
    }

    /**
     * The deleteGroup method
     *
     * This method deletes the selected group.
     *
     * @param \webtemplate\application\SmartyTemplate $tpl      Smarty Object.
     * @param EditGroups                              $groups   Edit Group Object.
     * @param string                                  $template Name of template.
     * @param string                                  $groupId  Group to be edited.
     *
     * @return boolean
     *
     * @access public
     */
    public static function deleteGroup(
        \webtemplate\application\SmartyTemplate $tpl,
        EditGroups $groups,
        string &$template,
        string $groupId
    ) {
        // Check the user has sent a valid group id.
        // For this check it does not matter if the group exists.
        if (\webtemplate\general\LocalValidate::dbid($groupId) === false) {
            $msg = gettext("Invalid Group");
            $tpl->assign("MSG", $msg);
            return false;
        }

        // Delete the group
        $deleteResult = $groups->deleteGroup($groupId);

        // Check for errors
        if (\webtemplate\general\General::isERROR($deleteResult)) {
            $template = "global/error.tpl";
            $msg =  gettext("Error encountered when deleting group.");
            $msg .= "\n\n";
            $msg .= $deleteResult->getMessage();
            $header = gettext("Database Error");
            $tpl->assign("ERRORMSG", $msg);
            $tpl->assign("HEADERMSG", $header);
            return false;
        }

        if ($deleteResult == 0) {
            $msg = gettext("Group Not Found");
            $tpl->assign("MSG", $msg);
            return false;
        }

        // Group Deleted ok.
        $msg = gettext("Group");
        $msg .=  ' (' . $groupId . ') ';
        $msg .= gettext("Deleted");
        $tpl->assign("MSG", $msg);

        return true;
    }



    /**
     * The saveGroup method
     *
     * This method saves the selected group.
     *
     * @param \webtemplate\application\SmartyTemplate $tpl      Smarty Object.
     * @param EditGroups                              $groups   Edit Group Object.
     * @param string                                  $template Name of template.
     * @param array                                   $data     Data to be saved.
     *
     * @return boolean
     *
     * @access public
     */
    public static function saveGroup(
        \webtemplate\application\SmartyTemplate $tpl,
        EditGroups $groups,
        string &$template,
        array $data
    ) {
        // Initalise the local variables
        $groupExists = false;
        $groupDataOk = true;
        $groupdatatosave = false;

        // Validate the User input
        $groupData = $groups->validateGroupData($data);

        // Check for Invalid Data
        if ($groupData[0]['msg'] != '') {
            $tpl->assign("RESULTS", $groupData);
            if ($groupData[0]['groupid'] != "0") {
                $tpl->assign("READONLY", "READONLY");
            }
            $tpl->assign("MSG", $groupData[0]['msg']);
            $template = "groups/edit.tpl";
            $tpl->assign("PAGETITLE", ' - ' . gettext("Edit Group"));
            return false;
        }


        if ($groupData[0]['groupid'] == '0') {
            $checkName = $groups->checkGroupExists($groupData[0]['groupname']);
            if (\webtemplate\general\General::isError($checkName)) {
                $template = "global/error.tpl";
                $msg = gettext("An error occured checking if the group exists.");
                $msg .= " " . gettext("Please try again.");
                $tpl->assign("ERRORMSG", $msg);
                $tpl->assign("HEADERMSG", gettext("Database Error"));
                return false;
            }
            if ($checkName == true) {
                $groupExists = true;
                $groupDataOk = false;
                $msg = $groupData[0]['groupname']
                    . gettext(" already exists in the database\n")
                    . $groupData[0]['msg'];
                $tpl->assign("MSG", $msg);
                $tpl->assign("RESULTS", $groupData);
                $template = "groups/edit.tpl";
                $tpl->assign("PAGETITLE", ' - ' . gettext("Edit Group"));
                return false;
            }
        }




        if ($groupData[0]['groupid'] == '0') {
            $newGroup = true;
        } else {
            $newGroup = false;
        }

        // Data is Valid.  Save it
        if (($groupDataOk == true) and ($newGroup === false)) {
            // Check Data has changed
            $groupdatatosave = $groups->groupDataChanged(
                $groupData[0]['groupid'],
                $groupData[0]['groupname'],
                $groupData[0]['description'],
                $groupData[0]['useforproduct'],
                $groupData[0]['autogroup']
            );
            if ($groupdatatosave === false) {
                $msg = \gettext("You have not made any changes");
                $tpl->assign("MSG", $msg);
                $tpl->assign("RESULTS", $groupData);
                $template = "groups/edit.tpl";
                $tpl->assign("PAGETITLE", ' - ' . gettext("Edit Group"));
                return false;
            }
        }

        if (($groupDataOk == true) and ($newGroup == true)) {
            $groupdatatosave = true;
        }

        // Save new group or Changed Data
        if ($groupdatatosave == true) {
            $saveResult = $groups->saveGroup(
                $groupData[0]['groupid'],
                $groupData[0]['groupname'],
                $groupData[0]['description'],
                $groupData[0]['useforproduct'],
                $groupData[0]['autogroup']
            );

            // Check for save error
            if (\webtemplate\general\General::isError($saveResult)) {
                $template = "global/error.tpl";
                $msg = \gettext("An error occured when saving the group details.");
                $msg .= " " . \gettext("Please try again.");
                $tpl->assign("ERRORMSG", $msg);
                $tpl->assign("HEADERMSG", gettext("Database Error"));
                return false;
            }

            // Reload the group data to show changes
            $groupData = $groups->getSingleGroup($groupData[0]['groupid']);
            if (\webtemplate\general\General::isError($groupData)) {
                $template = "global/error.tpl";
                $msg = \gettext("An error occured retrieving the group details.");
                $msg .= " " . \gettext("Please try again.");
                $tpl->assign("ERRORMSG", $msg);
                $tpl->assign("HEADERMSG", gettext("Database Error"));
                return false;
            }

            if ($newGroup == true) {
                $tpl->assign("PAGETITLE", gettext("New Group"));
                $msg = $groupData[0]['groupname']
                    . " "
                    . gettext("Created");
            } else {
                $tpl->assign("PAGETITLE", gettext("Edit Group"));
                $tempMsg = $groups->getChangeString();
                $msg = $groupData[0]['groupname'] . ' ';
                $msg .= gettext("Updated") . "\n\n";
                $msg .= $tempMsg;
            }
        }
        // Get the change string
        $tpl->assign("RESULTS", $groupData);
        if ($groupData[0]['groupid'] != "0") {
            $tpl->assign("READONLY", "READONLY");
        }
        $tpl->assign("MSG", $msg);
        $template = "groups/edit.tpl";
        return true;
    }

    /**
     * This function covers common code in editGroup and confirm delete
     *
     * @param \webtemplate\application\SmartyTemplate $tpl      Smarty Object.
     * @param EditGroups                              $groups   Edit Group Object.
     * @param string                                  $template Name of template.
     * @param string                                  $groupId  Group to be edited.
     *
     * @return boolean
     *
     * @access private
     */
    private static function getGroupData(
        \webtemplate\application\SmartyTemplate $tpl,
        EditGroups $groups,
        string &$template,
        string $groupId
    ) {

            // Check the user has sent a valid group id.
        // For this check it does not matter if the group exists.
        if (\webtemplate\general\LocalValidate::dbid($groupId) === false) {
            $msg = gettext("Invalid Group");
            $tpl->assign("MSG", $msg);
            return false;
        }
        // Retrieve the group information from the database.
        $resultArray = $groups->getSingleGroup($groupId);
        if (\webtemplate\general\General::isError($resultArray)) {
            if ($resultArray->getCode() == DB_ERROR_NOT_FOUND) {
                $msg = gettext("Group Not Found");
                $tpl->assign("MSG", $msg);
                return false;
            } else {
                $template = "global/error.tpl";
                $msg =  gettext("Error encountered when retreiving group.");
                $header = gettext("Database Error");
                $tpl->assign("ERRORMSG", $msg);
                $tpl->assign("HEADERMSG", $header);
                return false;
            }
        }

        // Got the group okay.  Load the template.
        $tpl->assign("RESULTS", $resultArray);

        // Make the group name readonly
        $tpl->assign("READONLY", "READONLY");
        return true;
    }
}
