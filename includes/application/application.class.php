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

namespace webtemplate\application;

use webtemplate\application\exceptions\AppException;
use webtemplate\general\General;
use webtemplate\application\WebTemplateCommon;

/**
 *  Webtemplate Application
 **/
class Application
{
    /**
     * Property: db
     * PHP PDO class
     *
     * @var    \g7mzr\db\interfaces\InterfaceDatabaseDriver
     * @access protected
     */
    protected $var_db = null;

    /**
     * Property: tpl
     * SMARTY Template class
     *
     * @var    \webtemplate\application\SmartyTemplate
     * @access protected
     */
    protected $var_tpl = null;

    /**
     * Property: language
     * Users language
     *
     * @var    string
     * @access protected
     */
    protected $var_language = '';

    /**
     * Property: appname
     * String containing application name
     *
     * @var    string
     * @access protected
     */
    protected $var_appname = '';

    /**
     * Property: appversion
     * String containing application version
     *
     * @var    string
     * @access protected
     */
    protected $var_appversion = '';

    /**
     * Property: apiversion
     * String containing application RESTful API Version
     *
     * @var    string
     * @access protected
     */
    protected $var_apiversion = '';

    /**
     * Property: production
     * Boolean true if application is in a production environment
     *
     * @var    boolean
     * @access protected
     */
    protected $var_production = true;

    /**
     * Property: config
     * Webtemplate config class
     *
     * @var    \webtemplate\config\Configure
     * @access protected
     */
    protected $var_config = null;

    /**
     * Property: log
     * Webtemplate Log Object
     *
     * @var    \webtemplate\general\Log
     * @access protected
     */
    protected $var_log = null;

    /**
     * Property: session
     * Webtemplate Session class
     *
     * @var    \webtemplate\application\Session
     * @access protected
     */
    protected $var_session = null;

    /**
     * Property: plugin
     * Webtemplate Plugin object
     *
     * @var \webtemplate\application\Plugin
     * @access protected
     */
    protected $var_plugin = null;

    /**
     * Property: user
     * Webtemplate User class
     *
     * @var    \webtemplate\users\User
     * @access protected
     */
    protected $var_user = null;

    /**
     * Property: usergroups
     * Webtemplate Usergroups class
     *
     * @var    \webtemplate\users\Groups
     * @access protected
     */
    protected $var_usergroups = null;

    /**
     * Property: editusers
     * Webtemplate edituser class
     *
     * @var    \webtemplate\users\Edituser
     * @access protected
     */
    protected $var_edituser = null;


    /**
     * Property: editusersgroups
     * Webtemplate editusers groups class
     *
     * @var    \webtemplate\groups\EditUsersGroups
     * @access protected
     */
    protected $var_editusersgroups;

    /**
     * Property: editgroups
     * Webtemplate edit groups class
     *
     * @var    \webtemplate\groups\EditGroups
     * @access protected
     */
    protected $var_editgroups;

    /**
     * Property: parameters
     * Webtemplate Parameters Object
     *
     * @var    \webtemplate\admin\Parameters
     * @access protected
     */
    protected $var_parameters;

    /**
     * Property: preferences
     * Webtemplate Preferences Object
     *
     * @var    \webtemplate\admin\Preferences
     * @access protected
     */
    protected $var_preferences;

    /**
     * Property: mail
     * Webtemplate Mailer Object
     *
     * @var    \webtemplate\general\Mail
     * @access protected
     */
    protected $var_mail;

    /**
     * Property: tokens
     * Webtemplate Tokens Object
     *
     * @var    \webtemplate\general\Tokens
     * @access protected
     */
    protected $var_tokens;

    /**
     * Property: edituserprefs
     * Webtemplate edituserprefs Object
     *
     * @var    \webtemplate\users\EditUserPref
     * @access protected
     */
    protected $var_edituserprefs;

    /**
     * Constructor
     *
     * @throws AppException If unable to connect to database.
     *
     * @access public
     */
    public function __construct()
    {
        $dsn = array(
            'dbtype'  => '',
            'hostspec' => '',
            'databasename' => '',
            'username' => '',
            'password' => '',
            'disable_iso_date' => 'disable'
        );

        // Set the base directory for the application
        $basedir = dirname(dirname(__DIR__));

        // Create the Smart Template class
        $this->var_tpl = new \webtemplate\application\SmartyTemplate();


        //Set up the correct language and associated templates
        $languageconfig = General::getconfigfile($this->var_tpl->getConfigDir());
        $this->var_tpl->assign('CONFIGFILE', $languageconfig);
        $this->var_tpl->configLoad($languageconfig);
        $this->var_language = $this->var_tpl->getConfigVars('Language');
        $templateArray = $this->var_tpl->getTemplateDir();
        $this->var_tpl
            ->setTemplateDir($templateArray[0] . '/' . $this->var_language);
        $this->var_tpl->setCompileId($this->var_language);

        // Get the application name and version information
        $this->var_appname = $this->var_tpl->getConfigVars('application_name');
        $this->var_appversion = $this->var_tpl->getConfigVars('application_version');
        $this->var_apiversion = $this->var_tpl->getConfigVars('api_version');

        // Get the application development mode
        $this->var_production = $this->var_tpl->getConfigVars('Production');


        // Get the Database login information
        WebTemplateCommon::loadDSN($this->var_tpl, $dsn);

        // Loginto the Database for all classes
        if (php_sapi_name() === 'cli') {
            $persistance = true;
        } else {
            $persistance = false;
        }
        $databaseconnection = true;
        try {
            $db = new \g7mzr\db\DBManager(
                $dsn,
                $dsn['username'],
                $dsn['password'],
                $persistance
            );
            $setresult = $db->setMode("datadriver");
            if (\g7mzr\db\common\Common::isError($setresult)) {
                $databaseconnection = false;
                //echo $setresult->getMessage();
            }
        } catch (Exception $ex) {
            $databaseconnection = false;
        }
        if ($databaseconnection === false) {
            throw new AppException('Unable to connect to the database', 1);
        }
        $this->var_db = $db->getDataDriver();


        //Create new config class
        $configdir = $this->var_tpl->getConfigDir(0);
        $this->var_config = new \webtemplate\config\Configure($configdir);

        //Create the logclass
        $this->var_log = new \webtemplate\general\Log(
            $this->var_config->read('param.admin.logging'),
            $this->var_config->read('param.admin.logrotate')
        );

        // Initilaise PHP Session handling from session.php
        $this->var_session = new \webtemplate\application\Session(
            $this->var_config->read('param.cookiepath'),
            $this->var_config->read('param.cookiedomain'),
            $this->var_config->read('param.users.autologout'),
            $this->var_tpl,
            $this->var_db
        );

        // Initialise the Plugin Class and all active plugins
        $plugindir = $basedir . "/plugins";
        $this->var_plugin = new \webtemplate\application\Plugin($plugindir);

        // Create a user class
        $this->var_user = new \webtemplate\users\User($this->var_db);

        // Initalise an Edit User class
        $this->var_edituser = new \webtemplate\users\EditUser($this->var_db);

        // Initalise an Usergrpups class
        $this->var_usergroups = new \webtemplate\users\Groups($this->var_db);


        // Initalise an edutusers groups class
        $this->var_editusersgroups = new \webtemplate\groups\EditUsersGroups(
            $this->var_db
        );

        // Initalise edit groups class
        $this->var_editgroups = new \webtemplate\groups\EditGroups($this->var_db);

        // Initalise the Parameters Object
        $this->var_parameters = new \webtemplate\admin\Parameters($this->var_config);

        // Inialise the Preferences Object
        $this->var_preferences = new \webtemplate\admin\Preferences(
            $this->var_config
        );

        // Initalise the Mail Object
        $this->var_mail = new \webtemplate\general\Mail(
            $this->var_config->read('param.email')
        );

        //Initalise the Tokens Object
        $this->var_tokens = new \webtemplate\general\Tokens(
            $this->var_tpl,
            $this->var_db
        );

        // Inialise the Edit Users Preferences Object
        $this->var_edituserprefs = new \webtemplate\users\EditUserPref(
            $this->var_db
        );
        // Check if user is logged in.  If they are register them.
        if ($this->var_session->getUserName() != '') {
            $result = $this->var_user->register(
                $this->var_session->getUserName(),
                $this->var_config->read('pref')
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->var_log->error(
                    basename(__FILE__) .
                    ": Failed To Register logged in user " .
                    $this->session->getUserName()
                );
                throw new AppException('Unable to register user', 1);
            }

            // Get the users groups
            $this->var_usergroups->loadusersgroups($this->var_user->getUserId());

            // Set the userid for edituserprefs
            $this->var_edituserprefs->setUserId($this->var_user->getUserId());
        }


        $this->var_log->debug('Application Class Initalised');
    }

    /**
     * Destructor
     *
     * @access public
     */
    public function __destruct()
    {
        $this->var_db->disconnect();
    }


    /**
     * This function returns the api version string
     *
     * @return string The Application RESTfull API version
     *
     * @access public
     */
    public function apiversion()
    {
        return $this->var_apiversion;
    }

    /**
     * This function returns the application name string
     *
     * @return string The application name
     *
     * @access public
     */
    public function appname()
    {
        return $this->var_appname;
    }

    /**
     * This function returns the application version string
     *
     * @return string The Application version
     *
     * @access public
     */
    public function appversion()
    {
        return $this->var_appversion;
    }

    /**
     * This function returns true if the application is set for a production
     * environment.
     *
     * @return boolean
     *
     * @access public
     */
    public function production()
    {
        return $this->var_production;
    }

    /**
     * This function returns the config class
     *
     * @return \webtemplate\config\Configure
     *
     * @access public
     */
    public function config()
    {
        return $this->var_config;
    }


    /**
     * This function returns the database class
     *
     * @return \webtemplate\db\DB
     *
     * @access public
     */
    public function db()
    {
        return $this->var_db;
    }

    /**
     * This function returns the edituser class
     *
     * @return \webtemplate\users\Edituser
     *
     * @access public
     */
    public function edituser()
    {
        return $this->var_edituser;
    }

    /**
     * This function returns the user class
     *
     * @return \webtemplate\users\EditUsersGroups
     *
     * @access public
     */
    public function editusersgroups()
    {
        return $this->var_editusersgroups;
    }

    /**
     * This function returns the language string
     *
     * @return string the Users language
     *
     * @access public
     */
    public function language()
    {
        return $this->var_language;
    }

    /**
     * This function returns the log class
     *
     * @return \webtemplate\general\Log
     *
     * @access public
     */
    public function log()
    {
        return $this->var_log;
    }

    /**
     * This function returns the session class
     *
     * @return \webtemplate\application\Session
     *
     * @access protected
     */
    public function session()
    {
        return $this->var_session;
    }

    /**
     * This function returns the Plugin Class
     *
     *  @return \webtemplate\application\Plugin
     *
     * @access protected
     */
    public function plugin ()
    {
        return $this->var_plugin;
    }
    
    /**
     * This function returns the tpl class
     *
     * @return \webtemplate\application\SmartyTemplate
     *
     * @access protected
     */
    public function tpl()
    {
        return $this->var_tpl;
    }


    /**
     * This function returns the user class
     *
     * @return \webtemplate\users\User
     *
     * @access public
     */
    public function user()
    {
        return $this->var_user;
    }

    /**
     * This function returns the users groups class
     *
     * @return \webtemplate\users\Groups
     *
     * @access protected
     */
    public function usergroups()
    {
        return $this->var_usergroups;
    }

    /**
     * This function returns the users groups class
     *
     * @return \webtemplate\groups\EditGroups
     *
     * @access protected
     */
    public function editgroups()
    {
        return $this->var_editgroups;
    }

    /**
     * This function returns the parameters class
     *
     * @return \webtemplate\admin\Parameters
     *
     * @access protected
     */
    public function parameters()
    {
        return $this->var_parameters;
    }


    /**
     * This function returns the preferences class
     *
     * @return \webtemplate\admin\Preferences
     *
     * @access protected
     */
    public function preferences()
    {
        return $this->var_preferences;
    }

    /**
     * This function returns the mail class
     *
     * @return \webtemplate\general\Mail
     *
     * @access protected
     */
    public function mail()
    {
        return $this->var_mail;
    }

    /**
     * This function returns the tokens class
     *
     * @return \webtemplate\general\tokens
     *
     * @access protected
     */
    public function tokens()
    {
        return $this->var_tokens;
    }

    /**
     * This function returns the edituserpref class
     *
     * @return \webtemplate\users\EditUserPref
     *
     * @access protected
     */
    public function edituserpref()
    {
        return $this->var_edituserprefs;
    }
}
