<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Install
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\install\commands;

use \GetOpt\Command;
use \GetOpt\GetOpt;
use \GetOpt\Operand;
use \GetOpt\Option;

/**
 * Setup is a test command for PharApp.
 *
 * @package  PharApp
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  https://github.com/g7mzr/phar-app/blob/master/LICENSE GNU GPL v3.0
 */
class PluginsCommand extends Command
{
    /**
     * Property: plugindir
     *
     * @var string
     * @access private
     */
    private $plugindir = "";

    /**
     * Property: configfile
     *
     * @var string
     * @access private
     */
    private $configfile = "";

    /**
     * Property: basedir
     *
     * @var string
     * @access private
     */
    private $basedir = "";

    /**
     * Constructor
     *
     * Constructor used for InstallCommand Class.  It is used to initialise the
     * command including setting up operands and help text.
     *
     * @param GetOpt $getOpt Pointer to the GetOpt structure for PharApp.
     * @param array  $config Webtemplate en.conf configuration array.
     *
     */
    public function __construct(GetOpt $getOpt, array $config)
    {
        parent::__construct('plugins', [$this, 'handle']);

        // Set the base DIR
         $this->basedir = dirname(dirname(dirname(__DIR__)));

        // Set up Operands fot Test Command

        // Setup description for Test Command
        $this->setDescription(
            'This command is used to manage webtemplate plugins individually '
            . 'without updating the database using the "update" command '
            . \PHP_EOL
        )->setShortDescription('Manage Webtemplate Plugins');

        $optionsarray = array();

        $optionsarray[] = Option::create('a', 'activate', GetOpt::REQUIRED_ARGUMENT)
            ->setDescription("Activate plugin <arg> and install/update its database schema")
            ->setValidation(function () use ($getOpt) {
                return !($getOpt->getOption('deactivate')
                    or $getOpt->getOption('status')
                    or $getOpt->getOption('new-plugin')
                );
            });

        $optionsarray[] = Option::create('c', 'config', GetOpt::REQUIRED_ARGUMENT)
            ->setDescription("Set Configuration file (config.php)")
            ->setDefaultValue($this->basedir . '/config.php');

        $optionsarray[] = Option::create('d', 'deactivate', GetOpt::REQUIRED_ARGUMENT)
            ->setDescription("Deactivate plugin <arg>.")
            ->setValidation(function () use ($getOpt) {
                return !($getOpt->getOption('activate')
                    or $getOpt->getOption('status')
                    or $getOpt->getOption('new-plugin')
                );
            });


        $optionsarray[] = Option::create('p', 'plugin-dir', GetOpt::REQUIRED_ARGUMENT)
            ->setDescription("Set location of plugin directory")
            ->setDefaultValue($this->basedir . '/plugins');

        $optionsarray[] = Option::create('s', 'status', GetOpt::NO_ARGUMENT)
            ->setDescription("Plugin Status - Installed, Active")
            ->setValidation(function () use ($getOpt) {
                return !($getOpt->getOption('deactivate')
                    or $getOpt->getOption('activate')
                    or $getOpt->getOption('new-plugin')
                );
            });

        if ($config['Production'] == false) {
            $optionsarray[] = Option::create('n', 'new-plugin', GetOpt::REQUIRED_ARGUMENT)
                ->setDescription("Create a new plugin template <arg>.")
                ->setValidation(function () use ($getOpt) {
                    return !($getOpt->getOption('deactivate')
                        or $getOpt->getOption('status')
                        or $getOpt->getOption('activate')
                    );
                });
        }

        $this->addOptions($optionsarray);
 /**           [



             ]
        ); */
    }

    /**
     * This is the entry point for the command
     *
     * @param GetOpt $getOpt Pointer to the GetOpt structure for PharApp.
     *
     * @return boolean False in an error is encountered.  True otherwise.
     */
    public function handle(GetOpt $getOpt)
    {
        global $sessiontest;
        $sessiontest = array(true);

        // Setup default values for plugin options
        $status = false;
        $commandrun = false;

        // Process Options.
        if ($getOpt->getOption("status")) {
            $status = true;
        }
        $activate = $getOpt->getOption("activate");

        $deactivate = $getOpt->getOption("deactivate");
        $newplugin = $getOpt->getOption("new-plugin");
        $this->configfile = $getOpt->getOption("config");
        $this->plugindir = $getOpt->getOption("plugin-dir");
        if (!is_null($activate)) {
            $this->activate($activate);
            $commandrun = true;
        }
        if (!is_null($deactivate)) {
            $this->deactivate($deactivate);
            $commandrun = true;
        }
        if (!is_null($newplugin)) {
            $this->newPlugin($newplugin);
            $commandrun = true;
        }
        if ($status) {
            $this->status();
            $commandrun = true;
        }

        if ($commandrun === false) {
            $this->defaultCommand();
        }
        return true;
    }

    /**
     * Activate
     *
     * Activate the named plugin by removing the disable file and installing/updating
     * the database schema if it exists.  If the plugin is active just update the
     * schema if it exists.
     *
     * @param string $pluginname The name of the plugin.
     *
     * @return void
     *
     * @access private
     */
    private function activate(string $pluginname)
    {
        include $this->configfile;
        $app = new \g7mzr\webtemplate\application\Application();

        echo sprintf("Activating: %s\n", $pluginname);

        // Check if the plugin exists
        $plugindir = $this->basedir . "/plugins/" . $pluginname;
        if (!is_dir($plugindir)) {
            echo sprintf("Error: Plugin %s does not exist\n", $pluginname);
            exit(1);
        }

        $pluginclass = '\\g7mzr\\webtemplate\\plugins\\' . $pluginname . '\\Plugin';
        $activeplugin = new $pluginclass($app);
        $filelist[$pluginname] = array();
        $filelist[$pluginname]['schema'] = $activeplugin->getDBSchema();
        $filelist[$pluginname]['data'] = $activeplugin->getDBData();

        \g7mzr\webtemplate\install\PluginDataBase::createPluginSchema($installConfig, $filelist);


        // Remove the Disablefile to activate the plugin.
        $disablefile = $plugindir . "/disable";
        if (file_exists($disablefile)) {
            $unlinkresult = unlink($disablefile);
            if ($unlinkresult === false) {
                echo sprintf("Error: Unable to enable plugin %s\n", $pluginname);
                exit(1);
            }
        }
        echo sprintf("Plugin %s has been enabled\n", $pluginname);
    }

    /**
     * Deactivate
     *
     * Deactivate the named plugin by creating the disable file.
     *
     * @param string $pluginname The name of the plugin.
     *
     * @return void
     *
     * @access private
     */
    private function deactivate(string $pluginname)
    {
        echo sprintf("Deactivating: %s\n", $pluginname);
        $plugindir = $this->basedir . "/plugins/" . $pluginname;
        if (!is_dir($plugindir)) {
            echo sprintf("Error: Plugin %s does not exist\n", $pluginname);
            exit(1);
        }
        $disablefile = $plugindir . "/disable";
        if (!file_exists($disablefile)) {
            $touchresult = touch($disablefile);
            if ($touchresult === false) {
                echo sprintf("Error: Unable to disable plugin %s\n", $pluginname);
                exit(1);
            }
        }
        echo sprintf("Plugin %s has been disabled\n", $pluginname);
    }

    /**
     * newPlugin
     *
     * newPlugin creates a template for a plugin.  The plugin is disabled
     *
     * @param string $pluginname The name of the plugin.
     *
     * @return void
     *
     * @access private
     */
    private function newPlugin(string $pluginname)
    {
        echo "Creating new plugin template: " . $pluginname . "\n";

        // Check if the plugin exists
        $plugindir = $this->basedir . "/plugins/" . $pluginname;
        if (is_dir($plugindir)) {
            echo sprintf("Error: Plugin %s already exist\n", $pluginname);
            exit(1);
        }

        // Set the filenames
        $disablefile = $plugindir . "/disable";
        $pluginfile = $plugindir . "/Plugin.php";

        // Set the new Directories
        $libdir = $plugindir . "/lib";
        $tpldir = $plugindir . "/templates";

        // Configure Smarty
        $tpl = new \g7mzr\webtemplate\application\SmartyTemplate();
        $templateArray = $tpl->getTemplateDir();
        $tpl->setTemplateDir($templateArray[0] . '/en');
        $tpl->setCompileId("en");

        // Setup the template and retreeive it
        $tpl->assign("PLUGINNAME", $pluginname);
        $templatefile = $tpl->fetch("develop/blankplugin.tpl");

        // Create the new directory structure
        $pluginmkdirresult = mkdir($plugindir, 0755);
        if ($pluginmkdirresult === false) {
            echo \sprintf("Error: Unable to make plugin directory %s\n" . $plugindir);
            exit(1);
        }

        $libmkdirresult = mkdir($libdir, 0755);
        if ($libmkdirresult == false) {
            echo \sprintf("Error: Unable to make plugin library directory %s\n" . $libdir);
            exit(1);
        }

        $tplmkdirresult = mkdir($tpldir, 0755);
        if ($tplmkdirresult === false) {
            echo \sprintf("Error: Unable to make plugin template directory %s\n" . $tpldir);
            exit(1);
        }

        // Create the disable file
        $touchresult = touch($disablefile);
        if ($touchresult === false) {
            echo \sprintf("Error: Unable to disable new plugin %s\n", $pluginname);
            exit(1);
        }

        // Save the Main Class file
        $saveresult = file_put_contents($pluginfile, $templatefile);
        if ($saveresult === false) {
            echo \sprintf("Error: Unable to save plugin class file %s\n", $pluginfile);
            exit(1);
        }
        //echo \sprintf("Bytes saved %d\n", $saveresult);

        echo sprintf("New plugin template: %s has been created\n", $pluginname);
    }

    /**
     * status
     *
     * Displays the status of the installed plugins
     *
     * @return void
     *
     * @access private
     */
    private function status()
    {
        $app = new \g7mzr\webtemplate\application\Application();
        $plugindetails = array();
        $scanned_directory = array_diff(scandir($this->plugindir), array('..', '.'));
        foreach ($scanned_directory as $filename) {
            $plugindetails[$filename] = array();
            $plugindir = $this->plugindir . '/' . $filename;
            if (!\is_dir($plugindir)) {
                continue;
            }
            $testDisabled =  $plugindir . "/disable";
            if (\file_exists($testDisabled)) {
                $plugindetails[$filename]['active'] = false;
                continue;
            }
            $testClassFileExists = $plugindir . "/Plugin.php";
            if (!\file_exists($testClassFileExists)) {
                $plugindetails[$filename]['active'] = false;
                continue;
            }
            $plugindetails[$filename]['active'] = true;
            $pluginclass = '\\g7mzr\\webtemplate\\plugins\\' . $filename . '\\Plugin';
            $activeplugin = new $pluginclass($app);
            $versiondata = $activeplugin ->getVersionInformation();
            $plugindetails[$filename]['version'] = $versiondata['version'];
        }
        $title1 = substr("Plugin Name                    ", 0, 20);
        $title2 = substr("Status        ", 0, 10);
        $title3 = "Version\n";
        echo "\n" . $title1 . $title2 . $title3;
        echo substr("--------------------------------------------------------------------------------", 0, 38) . "\n";
        foreach ($plugindetails as $name => $data) {
            if ($data['active'] === true) {
                $line1 = substr(sprintf("%s                           ", $name), 0, 20);
                $line2 = substr("Enabled       ", 0, 10);
                $line3 = sprintf("%s\n", $data['version']);
                echo $line1 . $line2 . $line3;
            } else {
                $line1 = substr(sprintf("%s                           ", $name), 0, 20);
                $line2 = substr("Disabled        ", 0, 10);
                $line3 = "\n";
                echo $line1 . $line2 . $line3;
            }
        }
        echo "\n";
    }

    /**
     * defaultCommand
     *
     * defaultCommand is the default action run by the plugin module.  It scans all
     * the enabled plugins and either creates or updates their database schema if
     * one exists
     *
     * @return void
     *
     * @access private
     */
    private function defaultCommand()
    {
        include $this->configfile;
        $filenames = \g7mzr\webtemplate\install\PluginDataBase::getPluginDBFiles($this->plugindir);
        \g7mzr\webtemplate\install\PluginDataBase::createPluginSchema($installConfig, $filenames);
    }
}
