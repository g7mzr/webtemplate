<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Configuration
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\config;

/**
 * Menu Class
 *
 **/
class Menus
{
    /**
     * Location of Configuration Directory
     *
     * @var    array
     * @access protected
     */
    protected $configDir = null;

    /**
     * Property Application Main Menu
     *
     * @var array
     * @access protected
     */
    protected $mainmenu;

    /**
     * Property Application Parameters Menu
     *
     * @var array
     * @access protected
     */
    protected $parametersmenu;

    /**
     * Property Application User Preferences Menu
     *
     * @var array
     * @access protected
     */
    protected $userprefmenu;

     /**
     * Property Application Admin  Menu
     *
     * @var array
     * @access protected
     */
    protected $adminmenu;

    /**
     * Constructor for the edit user class.
     *
     * @param string $configDir Location of the parameter file.
     *
     * @access public
     */
    public function __construct(string $configDir)
    {

        $this->configDir = $configDir;

        // Load the Main menu
        $mainmenufilename = $configDir . '/menus/mainmenu.json';
        $mainmenustr = file_get_contents($mainmenufilename);
        $this->mainmenu = json_decode($mainmenustr, true);

        // Load the Parameter Settings Menu
        $parammenufilename = $configDir . '/menus/parammenu.json';
        $parammenustr = file_get_contents($parammenufilename);
        $this->parametersmenu = json_decode($parammenustr, true);

        // Load the Users Preferences Menu
        $userprefmenufilename = $configDir . '/menus/userprefmenu.json';
        $userprefmenustr = file_get_contents($userprefmenufilename);
        $this->userprefmenu = json_decode($userprefmenustr, true);

        // Load the Main Admin Menu
        $adminmenufilename = $configDir . '/menus/adminmenu.json';
        $adminmenustr = file_get_contents($adminmenufilename);
        $this->adminmenu = json_decode($adminmenustr, true);
    }


    /**
     * This function returns the menu specified in menu
     *
     * @param string $menu The menu being requested.
     *
     * @return mixed The menu being requested or an empty array if it does not exist
     * @access public
     */
    final public function readMenu(string $menu)
    {
        // Set up the super array containing all menus
        $menulist = array();
        $menulist['mainmenu'] = $this->mainmenu;
        $menulist['parampagelist'] = $this->parametersmenu;
        $menulist['userprefpagelist'] = $this->userprefmenu;
        $menulist['adminpagelist'] = $this->adminmenu;

        // Check if the requested menu exists and return it.
        if (array_key_exists($menu, $menulist) == true) {
            return $menulist[$menu];
        } else {
            return array();
        }
    }
}
