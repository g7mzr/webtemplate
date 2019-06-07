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
 * Webtemplate Group Traits
 *
 **/
trait TraitGroupFunctions
{
    /**
     * This function creates an array of current groups
     *
     * @return array Of groups or WEBTEMPLATE error type
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
