{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{config_load file=$CONFIGFILE}
{block name=title}: Reset Password - {$PAGETITLE}{/block}
{block name=head}{/block}
{block name=body}


{if $ENTERNAME == true}

    <H4>Request New Password</H4>
    <form name="newpasswdform" method="post" action="resetpasswd.php">
        <input type="hidden" name="token" value ="{$TOKEN}">
        <input type="hidden" name="action" value="new">
        <p />
        <p>
            If you have an account and have forgotton your password enter your
            username and submit a request to change your password
        </P>
        <p>
            The link you are sent will expire in one hour.
        </p>
        <table>
            <tr>
                <th align="right">Username:</th>
                <td><input type="text" name="user_name"></td>
            </tr>
        </table>
        <table>
            <tr>
                <td width="150">&nbsp;</td>
                <td><input type="submit" id="newpasswdreq" name="newpasswdreq" value="New Password"></td>
            </tr>
        </table>
    </form>
{/if}

{if $EMAILSENT == TRUE}
    <p />
    <p>
        A confirmation email has been sent containing a link to continue reseting
        your password.
    </p>
    <p>
        The link will expire in 1 hour.
    </p>
{/if}

{if $ENTERPASSWORD == true}
    <H4>Enter New Password</H4>
    <P>
        Please enter your new password in the both boxes below and and click on the
        change password button.
    </P>
    <form name="newpasswordform" method="post" action="resetpasswd.php">
        <input type="hidden" name="token" value ="{$TOKEN}">
        <input type="hidden" name="userid" value ="{$USERID}">
        <input type="hidden" name="passwdtoken" value ="{$PASSWDTOKEN}">
        <table>
            <tr>
                <th align="right">New password:</th>
                <td><input type="password" name="newpasswd"></td>
            </tr>
            <tr>
                <th align="right">Confirm new password:</th>
                <td><input type="password" name="newpasswd2"></td>
            </tr>
        </table>
        <input type="hidden" name="action" value="save">
        <table>
            <tr>
                <td width="150">&nbsp;</td>
                <td><input type="submit" id="update" value="Change Password"></td>
        </tr>
        </table>
    </form>
    <p>
    Your will not be able to reset your password if this form is not completed by {$EXPIREDATE}
    </p>
    <hr>
    <p>
        If you do not wish to reset your password click the cancel
        button and your details will be forgotten.
    </p>

    <form name="newpasswordform" method="post" action="resetpasswd.php">
        <input type="hidden" name="token" value ="{$TOKEN}">
        <input type="hidden" name="passwdreq" value ="{$PASSWDTOKEN}">
        <input type="hidden" name="action" value="cancelpasswdrequest">
        <input type="submit" id="cancel" value="Cancel Password Request">
    </form>

{/if}

{if $CONFIRMPASSWORD == true}
    <p />
    <P>
        Your password has been updated.  Please return to the home page to log in.
    </P>
    <P>
        The Password reset link you were sent has been deleted.  In order to reset
        your password again you will need to request a new link
    </P>
{/if}



{if $REQUESTCANCELLED == TRUE}
    <p />
    <p>
        Your password request has been cancelled.
    </p>
    <p>
        Please return to the home page if you wish to submit a new password request.
    </p>

{/if}

{/block}

