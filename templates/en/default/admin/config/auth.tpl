{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="admin/config/config-main.tpl"}
{config_load file=$CONFIGFILE}
{block name=form}
    <form name="configauthform" method="post" action="editconfig.php">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="token" value="{$TOKEN}">
        <input type="hidden" name="section" value="auth">
        <dl>
            <dt><a name="create_account">Create Accounts</a></dt>
            <dd>Users can create new accounts.
                <p>
                    <select name="create_account" id="create_account">
                        <option value="yes" {if $CREATE_ACCOUNT_ON == true}selected="selected"{/if}>Yes</option>
                        <option value="no" {if $CREATE_ACCOUNT_ON == false}selected="selected"{/if}>No</option>
                    </select>
                </p>
                <hr>
            </dd>
            <dt><a name="new_password">New Password</a></dt>
            <dd>Users can request new passwords.
                <p>
                    <select name="new_password" id="new_password">
                        <option value="yes" {if $NEW_PASSWORD_ON == true}selected="selected"{/if}>Yes</option>
                        <option value="no" {if $NEW_PASSWORD_ON == false}selected="selected"{/if}>No</option>
                    </select>
                </p>
                <hr>
            </dd>

            <dt><a name="passwdstrength">Password Strength</a></dt>
            <dd> Set the complexity required for passwords. In all cases must the passwords be at least 8 characters long.
                <ul>
                    <li>no_constraints - No complexity required.</li>
                    <li>letters  - Lower and/or uppercase letters.</li>
                    <li>mixed_letters - Passwords must contain at least one UPPER and one lower case letter.</li>
                    <li>letters_numbers - Passwords must contain at least one UPPER and one lower case letter and a number.</li>
                    <li>letters_numbers_specialchars - Passwords must contain at least one UPPER or one lower case letter, a number and a special character.</li>
                </ul>
                <p>
                    <select name="passwdstrength" id="passwdstrength">
                        <option value="1" {if $PASSWDSTRENGTH == '1'}selected="selected"{/if}>No Constraints</option>
                        <option value="2" {if $PASSWDSTRENGTH == '2'}selected="selected"{/if}>Letters</option>
                        <option value="3" {if $PASSWDSTRENGTH == '3'}selected="selected"{/if}>Mixed Letters</option>
                        <option value="4" {if $PASSWDSTRENGTH == '4'}selected="selected"{/if}>Letters and Numbers</option>
                        <option value="5" {if $PASSWDSTRENGTH == '5'}selected="selected"{/if}>Letters, Numbers and Special Chars</option>
                    </select>
                </p>
            </dd>
            <dd>
                <dt><a name="regexp">User Regexp</a></dt>
                <dd>This defines the regular expression to use for legal user names. The maximum size of a user name is 20 characters.
                    <p><input type="text" size="60" name="regexp" id="regexp" value="{$REGEXP}"></p>
                    <hr>
            </dd>
            <dt><a name="regexpdesc">Description of User Name Regexp</a></st>
            <dd>This describes in English words what kinds of legal names are allowed by the <tt>regexp</tt> param.
                <p>
                    <textarea name="regexpdesc" id="regexpdesc" rows="10" cols="80" >{$REGEXPDESC}</textarea>
                </p>
                <hr>
            </dd>

            <dt><a name="passwdage">Password Ageing</a></dt>
            <dd> Set the User Password Aging Policy.
                <ul>
                    <li>None - Users are not prompted to change passwords</li>
                    <li>60 Days - Users must change their passwords every 60 days</li>
                    <li>90 Days - Users must change their passwords every 90 days</li>
                    <li>180 Days - Users must change their passwords every 180 days</li>
                </ul>
                <p>
                    <select name="passwdage" id="passwdage">
                        <option value="1" {if $PASSWDAGE == '1'}selected="selected"{/if}>None</option>
                        <option value="2" {if $PASSWDAGE == '2'}selected="selected"{/if}>60 Days</option>
                        <option value="3" {if $PASSWDAGE == '3'}selected="selected"{/if}>90 Days</option>
                        <option value="4" {if $PASSWDAGE == '4'}selected="selected"{/if}>180 Days</option>
                    </select>
                </p>
                <hr>
            </dd>
            <dt><a name="autocomplete">Save Login Details</a></dt>
            <dd>If enabled users can save their login details in their browser.
                <p>
                    <select name="autocomplete" id="autocomplete">
                        <option value="yes" {if $AUTOCOMPLETE == true}selected="selected"{/if}>Enabled</option>
                        <option value="no" {if $AUTOCOMPLETE == false}selected="selected"{/if}>Disabled</option>
                    </select>
                </p>
            </dd>
            <dt><a name="autologout">Automatically Log User Out</a></dt>
            <dd> Set the auto logout time out
                <ul>
                    <li>Session - Log the user out when the close the current browser session</li>
                    <li>10 Minutes - Log the user out after 10 minutes of inactivity</li>
                    <li>20 Minutes - Log the user out after 20 minutes of inactivity</li>
                    <li>30 Minutes - Log the user out after 30 minutes of inactivity</li>
                    <li>Never - Never log the user out</li>
                </ul>
                <p>
                    <select name="autologout" id="autologout">
                        <option value="1" {if $AUTOLOGOUT == "1"}selected="selected"{/if}>Session</option>
                        <option value="2" {if $AUTOLOGOUT == "2"}selected="selected"{/if}>10 Minutes</option>
                        <option value="3" {if $AUTOLOGOUT == "3"}selected="selected"{/if}>20 Minutes</option>
                        <option value="4" {if $AUTOLOGOUT == "4"}selected="selected"{/if}>30 Minutes</option>
                        <option value="5" {if $AUTOLOGOUT == "5"}selected="selected"{/if}>Never</option>
                    </select>
                </p>
            </dd>
        </dl>
        <input type="submit" id="update" value="Submit Changes">
    </form>
{/block}
