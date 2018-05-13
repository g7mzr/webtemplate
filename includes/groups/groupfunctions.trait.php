<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\groups;

/**
 * Webtemplate Group Traits
 *
 * @category Webtemplate
 * @package  Groups
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
trait TraitGroupFunctions
{
    /**
     * This function creates an array of current groups
     *
     * @return Array of groups or WEBTEMPLATE error type
     * @access public
     */
    public function getGroupList()
    {
        $gotdata = true;
        $fieldNames = array(
            "group_id",
            "group_name",
            "group_description",
            "group_useforproduct",
            "group_editable",
            "group_autogroup",
            "group_admingroup"
        );
        //$searchdata = array('group_id' => '%');

        $uaodb = $this->db->dbselectmultiple(
            'groups',
            $fieldNames,
            null,
            'group_id'
        );

        if (!\webtemplate\general\General::isError($uaodb)) {
            $resultarray = array();

            // Populate the output array with the records
            foreach ($uaodb as $uao) {
                $resultarray[]
                    = array("groupid" => $uao['group_id'],
                            "groupname" => $uao['group_name'],
                            "description" => $uao['group_description'],
                            "useforproduct" => $uao['group_useforproduct'],
                            "editable" => $uao['group_editable'],
                            "autogroup" => $uao["group_autogroup"],
                            "admingroup" => $uao['group_admingroup'],
                            "useringroup" => 'N',
                            "addusertogroup" => 'N'
                        );
            }
            return $resultarray; //$this->db->getGroupList();
        } else {
            $gotdata = false;
            return $uaodb;
        }
    }
}
