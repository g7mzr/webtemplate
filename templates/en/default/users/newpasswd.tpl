{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{config_load file=$CONFIGFILE}
{block name=title}: {$PAGETITLE}{/block}
{block name=head}{/block}
{block name=body}
{if $ENTERPASSWORD == true}
        <H4>Enter New Password</H4>
        <P>Please enter your new password in the both boxes below and and click on the
        change password button.</P>
        <form name="newpasswordform" method="post" action="newpasswd.php">
        <input type="hidden" name="token" value ="{$TOKEN}">
        <input type="hidden" name="userid" value ="{$USERID}">
        <table>
        <tr>
            <th align="right">New password:</th>
            <td><input type="password" name="passwd"></td>
        </tr>
        <tr>
            <th align="right">Confirm new password:</th>
            <td><input type="password" name="passwd2"></td>
        </tr>
        </table>
        <input type="hidden" name="action" value="save">
        <table>
        <tr>
            <td width="150">&nbsp;</td>
            <td><input type="submit" id="update" value="Change Password">&nbsp;<input type="submit" id="cancel" name="cancel" value="Cancel"></td>
        </tr>
        </table>
        </form>
{/if}
{if $CONFIRMPASSWORD == true}
    <H4>Password Updated</H4>
    <P>Your password has been updated.  Please click on continue to move to
    the login page.</P>
    <form name="newpasswdform" method="post" action="index.php">
        <table>
            <tr>
                <td width="150">&nbsp;</td>
                <td><input type="submit" id="continue" value="Continue"></td>
            </tr>
        </table>
    </form>
{/if}
{/block}

