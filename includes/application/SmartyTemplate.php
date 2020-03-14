<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Application
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\application;

/**
 * Extended SMARTY Class to set up local variables
 *
**/
class SmartyTemplate extends \Smarty
{
    /**
     * __construct()
     *
     * @access public
     */
    public function __construct() // smarty_template_class()
    {

        //Class Constructor
        //These automatically get set with each new instance
        parent::__construct();
        $systemdir = dirname(dirname(dirname(__FILE__)));

        // Set up the local SMARTY directories
        $this->setBaseTemplateDir($systemdir . '/templates');
        $this->setCompileDir($systemdir . '/templates_c');
        $this->setConfigDir($systemdir . '/configs');
        $this->setCacheDir($systemdir . '/cache');

        // Disable Caching.  It can cause problems with dynamic pages
        $this->setCaching(false);

        // Set HTML Escaping
        $this->escape_html = true;

        // Set the default stylesheet
        $stylesheetarray = array();
        $stylesheetarray[] = 'style/Dusk/main.css';
        $this->assign('STYLESHEET', $stylesheetarray);
        $this->assign('MSG', '');
    }

    /**
     * Displays a Smarty template
     *
     * @param mixed $template   The resource handle of the template file.
     * @param mixed $cache_id   Cache id to be used with this template.
     * @param mixed $compile_id Compile id to be used with this template.
     * @param mixed $parent     Next higher level of Smarty variables.
     *
     * @return void
     *
     * @access public
     */
    public function display(
        $template = null,
        $cache_id = null,
        $compile_id = null,
        $parent = null
    ) {
        parent::display($template, $cache_id, $compile_id, $parent);
    }


    /**
     * Fetches a Smarty template
     *
     * @param mixed $template   The resource handle of the template file.
     * @param mixed $cache_id   Cache id to be used with this template.
     * @param mixed $compile_id Compile id to be used with this template.
     * @param mixed $parent     Next higher level of Smarty variables.
     *
     * @return string rendered template output
     *
     * @access public
     */
    public function fetch(
        $template = null,
        $cache_id = null,
        $compile_id = null,
        $parent = null
    ) {
        return parent::fetch($template, $cache_id, $compile_id, $parent);
    }

    /**
     * Set template directory
     *
     * @param mixed $template_dir Directory(s) of template sources.
     * @param mixed $isConfig     True for configuration_dir.
     *
     * @return \Smarty current Smarty instance for chaining
     */
    public function setTemplateDir($template_dir, $isConfig = false)
    {
        $ds = DIRECTORY_SEPARATOR;

        if ($isConfig == true) {
            $dirarray = $template_dir;
        } else {
            $dirnamecustom = $template_dir . $ds . 'custom';
            $dirnamedefault = $template_dir . $ds . 'default';
            $dirarray = array(
                $dirnamecustom,
                $dirnamedefault
            );
        }

        return parent::setTemplateDir($dirarray, $isConfig);
    }

    /**
     * Set Base template directory
     *
     * This function sets the base template directory before any languages and
     * the default and custom directories are added
     *
     * @param mixed $template_dir Directory(s) of template sources.
     *
     * @return \Smarty current Smarty instance for chaining
     */
    public function setBaseTemplateDir($template_dir)
    {
        return parent::setTemplateDir($template_dir);
    }
}
