<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Blank
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace webtemplate\blank;

/**
 * Blank Class
 *
 * This is a blank class used for creating new classes.
 **/
class Blank
{
     /**
     * Database Connection Object
      *
     * @var    \g7mzr\db\interfaces\InterfaceDatabaseDriver
     * @access protected
     */

    protected $db = null;


    /**
     * String containing the list of field which have been changed
     *
     * @var    string
     * @access protected
     */

    protected $dataChanged = '';

    /**
     * Constructor for the edit user class.
     *
     * @param \g7mzr\db\interfaces\InterfaceDatabaseDriver $db Database Object.
     *
     * @access public
     */
    public function __construct(\g7mzr\db\interfaces\InterfaceDatabaseDriver $db)
    {
        $this->db       = $db;
    } // end constructor


    /**
     * This function returns true
     *
     * @param array $searchdata The data to search for.
     *
     * @return mixed Array with the search results or WEBTEMPLATE error type
     * @access public
     */
    final public function search(array $searchdata)
    {
        return true;
    }


    /**
     * This function returns the details of a single XXX
     *
     * @param integer $Id The Id of the selected xxxx.
     *
     * @return mixed Array with the search results or WEBTEMPLATE error type
     * @access public
     */
    final public function fetch(int $Id)
    {
        return array("fetcheddata");
    }

    /**
     * This function updates/creates the user in the database
     *
     * @param integer $Id         Id of record to save.
     * @param array   $datatosave The data to save.
     *
     * @return mixed true if data saved okay or WEBTEMPLATE error type
     * @access public
     */
    final public function save(int &$Id, array $datatosave)
    {
        return true;
    }


     /**
     * This function creates a change string for the users data
     *
     * @param integer $Id         The Id.
     * @param array   $datatosave The data to check.
     *
     * @return boolean true if change string created
     * @access public
     */
    final public function dataChanged(int $Id, array $datatosave)
    {
        return true;
    }

     /**
     * This function returns the change string for the user's data
     *
     * @return string Change String for the user's data
     * @access public
     */
    final public function getChangeString()
    {
        return $this->dataChanged;
    }

     /**
     * This function validated the user data.
     *
     * @param array $inputArray Array containing the user data to be validated.
     *
     * @return array Validated user data. msg element contains any error message
     * @access public
     */
    final public function validate(array &$inputArray)
    {
        return true;
    }
}
