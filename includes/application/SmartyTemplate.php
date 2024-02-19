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
 * @copyright (c) 2020, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\application;

/**
 * Extended SMARTY Class to set up local variables and enable the ability to add
 * plugin template directories to the SMARTY variable $template_dir
 *
**/
class SmartyTemplate extends \Smarty
{
    /**
     * Property: Plugin Template Array
     *
     * An associative array where the key is a plugin template group and the value
     * is an array of fully qualified template file names.
     *
     * @var array
     * @access private
     */
    private $plugintemplates = array();

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
        $this->assign('UPDATEMSG', '');

        $this->debugging = false;
    }

    /**
     * assigns a Smarty variable
     *
     * @param array|string $tpl_var The template variable name(s).
     * @param mixed        $value   The value to assign.
     * @param mixed        $nocache If true any output of this variable will be not cached.
     *
     * @return Smarty_Internal_Data current Smarty_Internal_Data (or Smarty or Smarty_Internal_Template) instance for
     *                              chaining
     */
    public function assign($tpl_var, $value = null, $nocache = false)
    {
        return parent::assign($tpl_var, $value, $nocache);
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
        $this->assign("TEMPLATEGROUP", $this->plugintemplates);
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
        $this->assign("TEMPLATEGROUP", $this->plugintemplates);
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

    /**
     * Add Plugin template file
     *
     * This function allows a plugin to add a template to an array associated with a
     * include template option in an main template .  The array is associated with a
     * template variable in the Display and Fetch Functions
     *
     * @param string $templategroup The name of the Template hook.
     * @param string $templatename  The file name of the template to be added to the array.
     *
     * @return \Smarty current Smarty instance for chaining
     */
    public function addPluginTemplate(string $templategroup, string $templatename)
    {
       // initialize the template group array
        if (isset($this->plugintemplates[$templategroup]) === false) {
            $this->plugintemplates[$templategroup] = array();
        }
        array_push($this->plugintemplates[$templategroup], $templatename);
        return $this;
    }
}
