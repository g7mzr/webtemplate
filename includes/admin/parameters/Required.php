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

namespace g7mzr\webtemplate\admin\parameters;

/**
 * Parameters REQUIRED Interface Class
 **/
class Required extends ParametersAbstract
{

    /**
     * Application URL
     *
     * @var    string
     * @access protected
     */
    protected $urlbase = '';

    /**
     * Application e-mail Address
     *
     * @var    string
     * @access protected
     */
    protected $fromaddress = '';

    /**
     * Maintainers E-mail address
     *
     * @var    string
     * @access protected
     */
    protected $maintainer = '';

    /**
     * Path to documents
     *
     * @var    string
     * @access protected
     */
    protected $docbase = '';

    /**
     * Domain that cookies are valid for
     *
     * @var    string
     * @access protected
     */
    protected $cookiedomain = '';

    /**
     * Path that Cookies are valid for
     *
     * @var    string
     * @access protected
     */
    protected $cookiepath = '';

    /**
     * Constructor
     *
     * @param \g7mzr\webtemplate\config\Configure $config Configuration class.
     *
     * @access public
     */
    public function __construct(\g7mzr\webtemplate\config\Configure $config)
    {

        parent::__construct($config);

        // Preload the local variables with the current parameters
        // Required Section
        $this->urlbase = $this->config->read('param.urlbase');
        $this->maintainer = $this->config->read('param.maintainer');
        $this->docbase = $this->config->read('param.docbase');
        $this->cookiedomain = $this->config->read('param.cookiedomain');
        $this->cookiepath = $this->config->read('param.cookiepath');
    } // end constructor



    /**
     * Validate the Required set of parameters input by the user.  Last Msg will
     * contain a list of any parameters which failed validation.
     *
     * @param array $inputData Pointer to an array of User Input Data.
     *
     * @return boolean true if data Validated
     *
     * @access public
     */
    final public function validateParameters(array &$inputData)
    {

        // Set up Error Message string
        $this->lastMsg = '';

        // Set up the validation variables.
        $dataok = true;

        // Validate the Required parameters.
        // if a test fails set the flag it and the data to false

        // Validate the URL base
        if (!$this->validateURLBase($inputData)) {
            $dataok = false;
        }

        // Valiadate the maintainers e-mail address
        if (!$this->validateMaintainer($inputData)) {
            $dataok = false;
        }

        // Validate the document path
        if (!$this->validateDOCBase($inputData)) {
            $dataok = false;
        }

        // Valiadte the cookie domain
        if (!$this->validateCookieDomain($inputData)) {
            $dataok = false;
        }

        // validate the cookie path
        if (!$this->validateCookiePath($inputData)) {
            $dataok = false;
        }

        return $dataok;
    }

    /**
    * Check if any of the Required set of Parameters have changed.  The
    * locally stored parameters created as part of the validation process
    * are compared to the ones in the $parameters variable.  Last Msg will
    * contain a list of parameters which have changed.
    *
    * @param array $parameters Array of application Parameters.
    *
    * @return boolean true if data changed
    *
    * @access public
    */
    final public function checkParametersChanged(array $parameters)
    {
        // Set the data changed flags to false
        $datachanged = false;
        $msg = '';

        // Check if the URL Base has changed.
        if ($this->urlbase != $parameters['urlbase']) {
            $datachanged = true;
            $msg .= gettext("Base URL Changed") . "\n";
        }


        // Check if the maintainers address has changed
        if ($this->maintainer != $parameters['maintainer']) {
            $datachanged = true;
            $msg .= gettext("Maintainer's Address Changed") . "\n";
        }

        // Check if the document path has changed
        if ($this->docbase != $parameters['docbase']) {
            $datachanged = true;
            $msg .= gettext("Docs Base URL Changed") . "\n";
        }

        // Check if the cookie domain has changed
        if ($this->cookiedomain != $parameters['cookiedomain']) {
            $datachanged = true;
            $msg .= gettext("Cookie Domain Changed") . "\n";
        }

        // Check if the Cookie path has changed.
        if ($this->cookiepath != $parameters['cookiepath']) {
            $datachanged = true;
            $msg .= gettext("Cookie Path Changed") . "\n";
        }

        $this->lastMsg = $msg;
        return $datachanged;
    }

    /**
    * Validate the URL of the application.
    *
    * @param array $inputData Pointer to an array of User Input Data.
    *
    * @return boolean true if data Validated
    *
    * @access private
    */
    private function validateURLBase(array &$inputData)
    {
        // Setup the local data ok flag
        $dataok = true;

        // Get the URL Base from the webserver
        $this->urlbase = '';
        if (isset($inputData['url_base_id'])) {
            $this->urlbase = substr($inputData['url_base_id'], 0, 100);
        }

        if (!\g7mzr\webtemplate\general\LocalValidate::url($this->urlbase)) {
            $this->lastMsg .= gettext("Invalid URL Base") . "\n";
            $dataok = false;
        }

        return $dataok;
    }

    /**
    * Validate the maintainers e-mail address
    *
    * @param array $inputData Pointer to an array of User Input Data.
    *
    * @return boolean true if data Validated
    *
    * @access private
    */
    private function validateMaintainer(array &$inputData)
    {
        // Setup the local data ok flag
        $dataok = true;

        // Get the maintainers from e-mail address from the webserver
        $this->maintainer = '';
        if (isset($inputData['maintainer_id'])) {
            $this->maintainer = substr($inputData['maintainer_id'], 0, 60);
        }

        if (!\g7mzr\webtemplate\general\LocalValidate::email($this->maintainer)) {
            $this->lastMsg .= gettext("Invalid Maintainer's Email Address") . "\n";
            $dataok = false;
        }

        return $dataok;
    }

    /**
    * Validate the Document Base
    *
    * @param array $inputData Pointer to an array of User Input Data.
    *
    * @return boolean true if data Validated
    *
    * @access private
    */
    private function validateDOCBase(array &$inputData)
    {
        // Setup the local data ok flag
        $dataok = true;

        // Get the Documentation URL base from the webserver
        $this->docbase = '';
        if (isset($inputData['doc_baseurl_id'])) {
            $this->docbase = substr($inputData['doc_baseurl_id'], 0, 60);
        }

        if ($this->docbase != '') {
            if (!\g7mzr\webtemplate\general\LocalValidate::docPath($this->docbase)) {
                $this->lastMsg .= gettext("Invalid Document Path") . "\n";
                $dataok = false;
            }
        }

        return $dataok;
    }

    /**
    * Validate the Cookie Domain
    *
    * @param array $inputData Pointer to an array of User Input Data.
    *
    * @return boolean true if data Validated
    *
    * @access private
    */
    private function validateCookieDomain(array &$inputData)
    {
        // Setup the local data ok flag
        $dataok = true;

        // Get the Cookie domain from the webserver
        $this->cookiedomain = '';
        if (isset($inputData['cookie_domain_id'])) {
            $this->cookiedomain = substr($inputData['cookie_domain_id'], 0, 60);
        }

        if ($this->cookiedomain != '') {
            if (!\g7mzr\webtemplate\general\LocalValidate::domain($this->cookiedomain)) {
                $this->lastMsg .= gettext("Invalid Cookie Domain") . "\n";
                $dataok = false;
            }
        }

        return $dataok;
    }

    /**
    * Validate the Cookie Path
    *
    * @param array $inputData Pointer to an array of User Input Data.
    *
    * @return boolean true if data Validated
    *
    * @access private
    */
    private function validateCookiePath(array &$inputData)
    {
        // Setup the local data ok flag
        $dataok = true;

        // Get the Cookie path from the webserver
        $this->cookiepath = '';
        if (isset($inputData['cookie_path_id'])) {
            $this->cookiepath = substr($inputData['cookie_path_id'], 0, 60);
        }

        if (!\g7mzr\webtemplate\general\LocalValidate::path($this->cookiepath, false)) {
            $this->lastMsg .= gettext("Invalid Cookie Path") . "\n";
            $dataok = false;
        }

        return $dataok;
    }


    /**
     * This function transfers the parameters stored in this class to the
     * Configuration Class.
     *
     * @return boolean True if write is successful
     *
     * @access private
     */
    protected function savetoConfigurationClass()
    {
        $this->config->write('param.urlbase', $this->urlbase);
        $this->config->write('param.maintainer', $this->maintainer);
        $this->config->write('param.docbase', $this->docbase);
        $this->config->write('param.cookiedomain', $this->cookiedomain);
        $this->config->write('param.cookiepath', $this->cookiepath);
        return true;
    }
}
