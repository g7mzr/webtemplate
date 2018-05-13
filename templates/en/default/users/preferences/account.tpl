{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="users/preferences/preferences-main.tpl"}
{config_load file=$CONFIGFILE}
{block name=form}
    <h2>Name and Password</h2>
    <form name="userprefsform" method="post" action="userprefs.php">
    <input type="hidden" name="tab" value="account">
    <input type="hidden" name="token" value ="{$TOKEN}">
    <table>
    <tr>
        <td colspan="3">Please enter your existing password to confirm account changes.</td>
    </tr>
    <tr>
        <th align="right">Password:</th>
        <td><input type="password" name="current_password"></td>
    </tr>
    <tr>
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <th align="right">New password:</th>
        <td><input type="password" name="passwd"></td>
    </tr>
    <tr>
        <th align="right">Confirm new password:</th>
        <td><input type="password" name="passwd2"></td>
    </tr>
    <tr>
        <th align="right">Your real name:</th>
        <td><input size="35" maxlength="60" id="realname" name="realname" value="{$REALNAME}"></td>
    </tr>
    <tr>
        <th align="right">Your email address:</th>
        <td><input size="35" maxlength="60" id="useremail" name="useremail" value="{$EMAIL}"></td>
     </tr>
    </table>
    <input type="hidden" name="action" value="save">
    <table>
    <tr>
        <td width="150">&nbsp;</td>
        <td><input type="submit" id="update" value="Submit Changes"></td>
    </tr>
    </table>
    </form>
{/block}