<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage RestFul Interface
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\rest\endpoints;

/**
 *  Webtemplate RestFul API users endpoint class
 *
 **/
class Users
{
    /**
     * Traits to be used by this Class
     */
    use TraitEndPointCommon;
    use TraitPermissions;

    /**
     * Property: inputdata
     *
     * @var    array
     * @access protected
     */
    protected $inputdata = array();

    /**
     * Property: accessgroup
     * This is the group a user must be a member of to access this resource.
     *
     * @var    string
     * @access protected
     */
    protected $accessgroup = "editusers";


    /**
     * Constructor
     *
     * @param \g7mzr\webtemplate\application\Application $webtemplate Webtemplate Application Class Object.
     *
     * @access public
     */
    public function __construct(\g7mzr\webtemplate\application\Application &$webtemplate)
    {
            $this->webtemplate = $webtemplate;
    }

    /**
     * This function implements GET command.
     *
     * @return array The result of the action undertaken by the API end point.
     *
     * @access protected
     */
    protected function get()
    {

        if (\count($this->args) > 2) {
            $errmsg = array('ErrorMsg' => 'users: Invalid number of arguments');
            return array('data' => $errmsg, 'code' => 400);
        }


        if (\count($this->args) == 0) {
            // Endpoint Only Return all users
            $data = $this->webtemplate->edituser()->search('username', '');
            if (!\g7mzr\webtemplate\general\General::isError($data)) {
                return array('data' => $data, 'code' => 200);
            } else {
                $errmsg = array('Errormsg' => $data->getMessage());
                return array('data' => $errmsg, 'code' => 404);
            }
        }
        if (\count($this->args) == 1) {
            // Endpoint has a single parameter.  Chek if is a valid userid or
            // username and return the relevant data
            if (\is_numeric($this->args[0])) {
                // Return data on user with userid held in args[0]
                $data = $this->webtemplate->edituser()->getuser($this->args[0]);
                if (!\g7mzr\webtemplate\general\General::isError($data)) {
                    // Return data on user
                    return array('data' => $data, 'code' => 200);
                } else {
                    // Return error message - user does not exist
                    $errmsg = array('Errormsg' => $data->getMessage());
                    return array('data' => $errmsg, 'code' => 404);
                }
            } else {
                // Check if args[0] is a valid user name
                $validname = \g7mzr\webtemplate\general\LocalValidate::username(
                    $this->args[0],
                    $this->webtemplate->config()->read('param.users.regexp')
                );
                if ($validname) {
                    // Search for user using username on args[0]
                    $data = $this->webtemplate->edituser()->search(
                        'username',
                        $this->args[0]
                    );
                    if (!\g7mzr\webtemplate\general\General::isError($data)) {
                        // Return data on user
                        return array('data' => $data, 'code' => 200);
                    } else {
                        // Return error message - user does not exist
                        $errmsg = array('Errormsg' => $data->getMessage());
                        return array('data' => $errmsg, 'code' => 404);
                    }
                } else {
                    // args[0] does not hold a username in a valid format
                    $errmsg = array('Errormsg' => 'Invalid User Name');
                    return array('data' => $errmsg, 'code' => 404);
                }
            }
        }

        if (\count($this->args) == 2) {
            if ($this->args[1] == 'groups') {
                // Return the list og groups that the args[0] userid or username
                // is a member of.

                $uid = '0';  // Set a default user uid of 0.  This is invalid
                if (!is_numeric($this->args[0])) {
                    // if args[1] is a username search out user to obtain userid
                    $validname = \g7mzr\webtemplate\general\LocalValidate::username(
                        $this->args[0],
                        $this->webtemplate->config()->read('param.users.regexp')
                    );
                    if ($validname) {
                        // User name is in a valid format
                        $data = $this->webtemplate->edituser()->search(
                            'username',
                            $this->args[0]
                        );
                        if (!\g7mzr\webtemplate\general\General::isError($data)) {
                            // Set uid to userid of requested user.
                            $uid = $data[0]['userid'];
                        } else {
                            // Username in args[0] does not exist.  return error
                            $errmsg = array('Errormsg' => $data->getMessage());
                            return array('data' => $errmsg, 'code' => 404);
                        }
                    } else {
                        // username in args[0] is in an invalid format
                        $errmsg = array('Errormsg' => 'Invalid User Name');
                        return array('data' => $errmsg, 'code' => 404);
                    }
                } else {
                    //  args[0] is a number set the uid to args[0]
                    $uid = $this->args[0];
                }

                // Get the groups the user is a member of
                $groups = $this->webtemplate->editusersgroups()->getGroupList();
                $result = $this->webtemplate->editusersgroups()->getUsersGroups(
                    $uid,
                    $groups
                );
                if (!\g7mzr\webtemplate\general\General::isError($result)) {
                    // No error so it is safe to get the groups and return them
                    return array('data' => $groups, 'code' => 200);
                } else {
                    // Error geeting the users groups
                    $errmsg = $result->getMessage();
                    return array('data' => $errmsg, 'code' => 404);
                }
            } else {
                $errmsg = array('Errormsg' => 'Invalid argument ' . $this->args[1]);
                return array('data' => $errmsg, 'code' => 400);
            }
        }
    }

    /**
     * This function implements post command.
     *
     * The POST Command is used to add a new resource to the database in this case
     * a new user.  It is called using POST <host>/users with the new users data in
     * the body of the request.  They mandatory fields are username, passwd, passwd2,
     * realname, useremail, userdisablemail, userenabled and passwdchange
     *
     * @return array The result of the action undertaken by the API end point.
     *
     * @access protected
     */
    protected function post()
    {
        $this->requestdata['userid'] = 0;
        $fieldspresent = $this->userFieldsPresent();
        if ($fieldspresent !== true) {
            $data = array('ErrorMsg' => $fieldspresent);
            $code = 400;
            return array('data' => $data, 'code' => $code);
        }

        $resultArray = $this->webtemplate->edituser()->validateUserData(
            $this->inputdata,
            $this->webtemplate->config()->read('param.users')
        );

        if ($resultArray[0]['msg'] != '') {
            $data = array('ErrorMsg' => $resultArray[0]['msg']);
            return array('data' => $data, 'code' => 400);
        }

        // Check if the username already exists.  If it does report an error
        $username = $resultArray[0]['username'];
        if ($this->webtemplate->edituser()->checkUserExists($username)) {
            $data = array('ErrorMsg' => 'User ' . $username . ' already exists.');
            return array('data' => $data, 'code' => 409);
        }

        // Check if the emailaddress already exists.  If it does report an error
        $email = $resultArray[0]['useremail'];
        if ($this->webtemplate->edituser()->checkEmailExists($email)) {
            $data = array('ErrorMsg' => 'Email ' . $email . ' already exists.');
            return array('data' => $data, 'code' => 409);
        }


        $result = $this->webtemplate->edituser()->saveUser(
            $resultArray[0]['userid'],
            $resultArray[0]['username'],
            $resultArray[0]['realname'],
            $resultArray[0]['useremail'],
            $resultArray[0]['passwd'],
            $resultArray[0]['userdisablemail'],
            $resultArray[0]['userenabled'],
            $resultArray[0]['passwdchange']
        );
        if (\g7mzr\webtemplate\general\General::isError($result)) {
            $data = array('ErrorMsg' => $result->getMessage());
            return array('data' => $data, 'code' => 500);
        }

        $data = $this->webtemplate->edituser()->getuser($resultArray[0]['userid']);
        if (\g7mzr\webtemplate\general\General::isError($data)) {
            // Return error message
            $errmsg = array('Errormsg' => $data->getMessage());
            return array('data' => $errmsg, 'code' => 404);
        }


        return array('data' => $data, 'code' => 201);
    }

    /**
     * This function implements put command.
     *
     * @return array The result of the action undertaken by the API end point.
     *
     * @access protected
     */
    protected function put()
    {

        if ((\count($this->args) == 0) or (\count($this->args) > 2)) {
            $errmsg = array('ErrorMsg' => 'users: Invalid number of arguments');
            return array('data' => $errmsg, 'code' => 400);
        }

        // GET THE USER ID OF THE ACCOUNT TO BE UPDATED
        if (\is_numeric($this->args[0])) {
            $userid = $this->args[0];
        } else {
            // Check if args[0] is a valid user name
            $validname = \g7mzr\webtemplate\general\LocalValidate::username(
                $this->args[0],
                $this->webtemplate->config()->read('param.users.regexp')
            );
            if ($validname) {
                // Search for user using username on args[0]
                $data = $this->webtemplate->edituser()->search(
                    'username',
                    $this->args[0]
                );
                if (!\g7mzr\webtemplate\general\General::isError($data)) {
                    // Return data on user
                    $userid = $data[0]['userid'];
                } else {
                    // Return error message - user does not exist
                    $errmsg = array('Errormsg' => $data->getMessage());
                    return array('data' => $errmsg, 'code' => 404);
                }
            } else {
                // args[0] does not hold a username in a valid format
                $errmsg = array('Errormsg' => 'Invalid User Name');
                return array('data' => $errmsg, 'code' => 404);
            }
        }

        //  We have the user ID now we need to carry out the UPDATE.
        // If count($this->args == 1 then we are updateing the user
        // If count($this->args == 2 and $arg[2] == 'groups then were are updating the
        // users groups other wise it is an error



        if (\count($this->args) == 1) {
            $this->requestdata['userid'] = $userid;

            // Check if the Manditory fields are Present
            $fieldspresent = $this->userFieldsPresent();
            if ($fieldspresent !== true) {
                $data = array('ErrorMsg' => $fieldspresent);
                $code = 400;
                return array('data' => $data, 'code' => $code);
            }

            // Validate the data
            $resultArray = $this->webtemplate->edituser()->validateUserData(
                $this->inputdata,
                $this->webtemplate->config()->read('param.users')
            );

            if ($resultArray[0]['msg'] != '') {
                $data = array('ErrorMsg' => $resultArray[0]['msg']);
                return array('data' => $data, 'code' => 400);
            }

            $result = $this->webtemplate->edituser()->saveUser(
                $resultArray[0]['userid'],
                $resultArray[0]['username'],
                $resultArray[0]['realname'],
                $resultArray[0]['useremail'],
                $resultArray[0]['passwd'],
                $resultArray[0]['userdisablemail'],
                $resultArray[0]['userenabled'],
                $resultArray[0]['passwdchange']
            );
            if (\g7mzr\webtemplate\general\General::isError($result)) {
                $data = array('ErrorMsg' => $result->getMessage());
                return array('data' => $data, 'code' => 500);
            }

            $data = $this->webtemplate
                ->edituser()->getuser($resultArray[0]['userid']);
            if (\g7mzr\webtemplate\general\General::isError($data)) {
                // Return error message
                $errmsg = array('Errormsg' => $data->getMessage());
                return array('data' => $errmsg, 'code' => 404);
            }
        } elseif ($this->args[1] == 'groups') {
            $groupArray = $this->webtemplate->editusersgroups()->getGroupList();

            $arrayCount = 0;
            while ($arrayCount < count($groupArray)) {
                $arraykey = $groupArray[$arrayCount]['groupname'];
                if (array_key_exists($arraykey, $this->requestdata)) {
                    $groupArray[$arrayCount]["addusertogroup"] = 'Y';
                }
                $arrayCount++;
            }


            $groupsChanged = $this->webtemplate->editusersgroups()->groupsChanged(
                $userid,
                $groupArray
            );

            if ($groupsChanged === true) {
                $result = $this->webtemplate->editusersgroups()->saveUsersGroups(
                    $userid,
                    $groupArray
                );
                if (!\g7mzr\webtemplate\general\General::isError($result)) {
                    $data = $this->webtemplate->editusersgroups()->getGroupList();
                    $this->webtemplate->editusersgroups()->getUsersGroups(
                        $userid,
                        $data
                    );
                } else {
                    $errmsg = array('Errormsg' => "Unable to update groups");
                    return array('data' => $errmsg, 'code' => 400);
                }
            } else {
                $errmsg = array('Errormsg' => "You have not made any changes");
                return array('data' => $errmsg, 'code' => 400);
            }
        } else {
            $errmsg = array('Errormsg' => 'Invalid argument ' . $this->args[1]);
            return array('data' =>  $errmsg, 'code' => 400);
        }

        return array('data' => $data, 'code' => 201);
    }

    /**
     * This function checks to see that all the user database fields are present in
     * the input data from the client.
     *
     * The function checks if the mandatory fields are present.  They are
     * username, passwd, passwd2, realname, useremail, userdisablemail, userenabled
     * and passwdchange are check boxes in the web interface
     *
     * @return mixed TRUE if all fields are present.  Error Message otherwise.
     *
     * @access private
     */
    private function userFieldsPresent()
    {
        //Check if all the input fields have been included in the JSON Input
        $fieldspresent = true;
        $errMsg = 'The following mandatory fields are missing: ';

        // Check if the userid is present
        if (!array_key_exists('userid', $this->requestdata)) {
            $fieldspresent = false;
            $errMsg .= "userid ";
        }

        // Check if the username is present
        if (!array_key_exists('username', $this->requestdata)) {
             $fieldspresent = false;
            $errMsg .= "username ";
        }

        // Check is a password is needed and if it is it is present
        if (!array_key_exists('passwd', $this->requestdata)) {
            $fieldspresent = false;
            $errMsg .= 'passwd ';
        }

        // Check if the user's realname is present
        if (!array_key_exists('realname', $this->requestdata)) {
            $fieldspresent = false;
            $errMsg .= 'realname ';
        }

        // Check if the user's email is present
        if (!array_key_exists('useremail', $this->requestdata)) {
            $fieldspresent = false;
            $errMsg .= 'useremail ';
        }

        // Check if the user is enabled
        if (!array_key_exists('userenabled', $this->requestdata)) {
            $fieldspresent = false;
            $errMsg .= 'userenabled ';
        }

        // Check if the user's email is enabled
        if (!array_key_exists('userdisablemail', $this->requestdata)) {
            $fieldspresent = false;
            $errMsg .= 'userdisablemail ';
        }
        // Check if the user has to reset there password at the next login
        if (!array_key_exists('passwdchange', $this->requestdata)) {
            $fieldspresent = false;
            $errMsg .= 'passwdchange ';
        }

        // If at least one maditory field is missing return the error message
        if ($fieldspresent === false) {
            return $errMsg;
        }

        // Check if the input data is valid and transfer it to the working array
        $datafieldsvalid = true;
        $errMsg = "The following fields are invalid: ";

        // Chek the the userid is valid.
        $useridoptions = array(
            'options' => array(
                //'default' => 3, // value to return if the filter fails
                // other options here
                'min_range' => 0,
                'max_range' => 999999
            ),
            'flags' => FILTER_FLAG_ALLOW_OCTAL,
        );

        $userid = filter_var(
            $this->requestdata['userid'],
            FILTER_VALIDATE_INT,
            $useridoptions
        );
        if ($userid !== false) {
            $this->inputdata['userid'] = $userid;
        } else {
            $datafieldsvalid = false;
            $errMsg .= "userid ";
        }

        // Check the username is valid.
        $usernameoptions = array(
            'options' => array(
                'regexp' => $this->webtemplate->config()->read('param.users.regexp')
            )
        );
        $username = filter_var(
            $this->requestdata['username'],
            FILTER_VALIDATE_REGEXP,
            $usernameoptions
        );
        if ($username !== false) {
            $this->inputdata['username'] = $username;
        } else {
            $datafieldsvalid = false;
            $errMsg .= "username ";
        }

        // Check the password is valid only if the length is not zero.
        if (strlen($this->requestdata['passwd']) > 0) {
            $passwdoptions = array(
                'options' => array(
                    'regexp' => "/^([a-zA-Z\W\d]{8,20})$/"
                )
            );
            $passwd = filter_var(
                $this->requestdata['passwd'],
                FILTER_VALIDATE_REGEXP,
                $passwdoptions
            );
            if ($passwd !== false) {
                $this->inputdata['passwd'] = $passwd;
            } else {
                $datafieldsvalid = false;
                $errMsg .= "passwd ";
            }
        } else {
            $this->inputdata['passwd'] = "";
        }

        // Check the password is valid.
        $realnameoptions = array(
            'options' => array(
                'regexp' => "/^([a-zA-Z\s\']{3,60})$/"
            )
        );
        $realname = filter_var(
            $this->requestdata['realname'],
            FILTER_VALIDATE_REGEXP,
            $realnameoptions
        );
        if ($realname !== false) {
            $this->inputdata['realname'] = $realname;
        } else {
            $datafieldsvalid = false;
            $errMsg .= "realname ";
        }

        $email = filter_var(
            $this->requestdata['useremail'],
            FILTER_VALIDATE_EMAIL
        );
        if ($realname !== false) {
            $this->inputdata['useremail'] = $email;
        } else {
            $datafieldsvalid = false;
            $errMsg .= "useremail ";
        }

        // Options for all check boxes
        $checkboxoptions = array(
            'options' => array(
                'regexp' => "/^(Y)|(N)$/"
            )
        );

        // Check if user is to be enabled
        $userenabled = filter_var(
            $this->requestdata['userenabled'],
            FILTER_VALIDATE_REGEXP,
            $checkboxoptions
        );
        if ($userenabled !== false) {
            if ($userenabled == 'Y') {
                $this->inputdata['userenabled'] = 'Y';
            }
        } else {
            $datafieldsvalid = false;
            $errMsg .= "userenabled ";
        }

        // Check if the users email is to be disabled
        $userdisablemail = filter_var(
            $this->requestdata['userdisablemail'],
            FILTER_VALIDATE_REGEXP,
            $checkboxoptions
        );
        if ($userdisablemail !== false) {
            if ($userdisablemail == 'Y') {
                $this->inputdata['userdisablemail'] = 'Y';
            }
        } else {
            $datafieldsvalid = false;
            $errMsg .= "userdisablemail ";
        }

        // Check if the user is to change their password at the next login.
        // This is only valid if password aging is active
        $passwdchange = filter_var(
            $this->requestdata['passwdchange'],
            FILTER_VALIDATE_REGEXP,
            $checkboxoptions
        );
        if ($passwdchange !== false) {
            if (
                ($passwdchange == 'Y')
                and ($this->webtemplate->config()->read('params.users.passwdage'))
            ) {
                $this->inputdata['passwdchange'] = 'Y';
            }
        } else {
            $datafieldsvalid = false;
            $errMsg .= "passwdchange ";
        }

        if ($datafieldsvalid === false) {
            return $errMsg;
        }

        return true;
    }
}
