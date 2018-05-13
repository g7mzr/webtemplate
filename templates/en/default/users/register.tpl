{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{config_load file=$CONFIGFILE}
{block name=title}: Register new user - {$PAGETITLE}{/block}
{block name=head}{/block}
{block name=body}
<H4>Create New Account</H4>

{if $ENTEREMAIL == true}
    <form name="registerform" method="post" action="register.php">
        <input type="hidden" name="action" value="saveemail">
        <input type="hidden" name="token" value ="{$TOKEN}">
        <p />
        <p>
            To create a {#application_name#} account please enter a
            legitimate email address. You will receive an email at this address to confirm
            who you are. <b>You will not be able to log in until you receive the email and
            create your account.</b> If it doesn't arrive within a reasonable amount of time,
            you may contact the maintainer of this {#application_name#} installation at
            <a href="mailto:{$SYSADMINEMAIL}">{$SYSADMINEMAIL}</a>.
        </p>
        <p>
            If you already have an account and want to change your email address, you can
            change it from the Preferences page after logging in.
        </p>
        <p>
            A user account is required to use {#application_name#}. <b>Note: Your e-mail
            address will never be seen by other users as your username will be used to
            identify changes you have made to the data.</b>
        </p>
        <table>
            <tr>
                <th align="right">Enter your email address:</th>
                <td><input size="35" maxlength="60" name="email1" {$READONLY} value="{$EMAIL1}"></td>
            </tr>
        </table>
        <input type="submit" id="update" value="Request Account">
        </form>
{/if}

{if $EMAILSENT == TRUE}
    <p />
    <p>
        A confirmation email has been sent containing a link to continue creating an account
    </p>
    <p>
        The link will expire in 3 days.
    </p>
{/if}

{if $ENTERDETAILS == TRUE}
    <form name="registerform" method="post" action="register.php">
        <input type="hidden" name="newacc" value ="{$NEWACCTOKEN}">
        <input type="hidden" name="token" value ="{$TOKEN}">
        <input type="hidden" name="action" value="savenewaccount">
        <table>
            <tr>
                <th align="right">Your email address:</th>
                <td>{$EMAIL}</td>
            </tr>
            <tr>
                <th align="right">Username:</th>
                <td><input type="text" size="25" maxlength="20" name="user_name" autocomplete="off" {$READONLY} value="{$USERNAME}"></td>
            </tr>
            <tr>
                <th align="right">Password:</th>
                <td><input type="password" size="25" maxlength="20" name="new_password1" autocomplete="off" {$READONLY} value="{$PASSWORD1}"></td>
            </tr>
            <tr>
                <th align="right">Confirm password:</th>
                <td><input type="password" size="25" maxlength="20" name="new_password2" autocomplete="off" {$READONLY} value="{$PASSWORD2}"></td>
            </tr>
            <tr>
                <th align="right">Your real name:</th>
                <td><input size="35" maxlength="60" name="realname" autocomplete="off" {$READONLY} value="{$REALNAME}"></td>
            </tr>
        </table>
        <input type="submit" id="update" value="Create">
    </form>
    <p>
    Your account will not be created if this form is not completed by {$EXPIREDATE}
    </p>
    <hr>
    <p>
        If you do not wish to create an account using this email address click the cancel
        button and your details will be forgotten.
    </p>
    <form name="registerform" method="post" action="register.php">
        <input type="hidden" name="token" value ="{$TOKEN}">
        <input type="hidden" name="newacc" value ="{$NEWACCTOKEN}">
        <input type="hidden" name="action" value="cancelnewaccount">
        <input type="submit" id="cancel" value="Cancel Account">
    </form>
{/if}

{if $DETAILSENTERED == TRUE}
    <p />
    <p>
        Your account has been created.  Please return to the home page to log in.
    </p>
{/if}

{if $ACCOUNTCANCELLED == TRUE}
    <p />
    <p>
        Your account request has been cancelled.
    </p>
    <p>
        Please return to the home page if you wish to submit a new request.
    </p>

{/if}

{/block}

