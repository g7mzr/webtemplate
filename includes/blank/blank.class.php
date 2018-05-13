<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\blank;

/**
 * Blank Class
 *
 * @category Webtemplate
 * @package  Blank
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/


class Blank
{
     /**
     * Database MDB2 Database Connection Object
      *
     * @var    array
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
     * @param array $db MDB2 Database Connection Object
     *
     * @access public
     */
    public function __construct($db)
    {
        $this->db       = $db;
    } // end constructor


    /**
     * This function returns true
     *
     * @param string $searchdata The data to search for.
     *
     * @return mixed Array with the search results or WEBTEMPLATE error type
     * @access public
     */
    final public function search($searchdata)
    {
        return true;
    }


    /**
     * This function returns the details of a single XXX
     *
     * @param integer $Id The Id of the selected xxxx
     *
     * @return mixed Array with the search results or WEBTEMPLATE error type
     * @access public
     */
    final public function fetch($Id)
    {
        return array("fetcheddata");
    }

    /**
     * This function updates/creates the user in the database
     *
     * @param integer $Id         Pointer to the Id
     * @param string  $datatosave The datatosave
     *
     * @return mixed true if data saved okay or WEBTEMPLATE error type
     * @access public
     */
    final public function save(&$Id, $datatosave)
    {
        return true;
    }


     /**
     * This function creates a changestring for the users data
     *
     * @param integer $Id         The Id
     * @param string  $datatosave The data to check
     *
     * @return boolean true if change string created
     * @access public
     */
    final public function dataChanged($Id, $datatosave)
    {
        return true;
    }

     /**
     * This function returns the changestring for the user's data
     *
     * @return String Change String for the user's data
     * @access public
     */
    final public function getChangeString()
    {
        return $this->dataChanged;
    }

     /**
     * This function validated the user data.
     *
     * @param array $inputArray Pointer to an Array containing the user
     *                          data to be validated
     *
     * @return array Validated user data. msg element contains any error message
     * @access public
     */
    final public function validate(&$inputArray)
    {
        return true;
    }
}
