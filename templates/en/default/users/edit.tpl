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
<FORM  method="post" action="editusers.php" name="save_user">
<input type="hidden" name="action" value="save" />
<input type="hidden" name="token" value ="{$TOKEN}">
<input type="hidden" name="userid" value="{$RESULTS[0].userid|default:0}" />
<TABLE class="edituser">
    <TR>
        <TH>User Name</TH>
        <TD><input size="25" maxlength="20" name="username" {if $READONLY  == true} READONLY {/if} value="{$RESULTS[0].username}"></TD>
    <TR>
        <TH>Real Name</TH><TD><input size="64" maxlength="60" name="realname" value="{$RESULTS[0].realname}"></TD>
    </TR>
    <TR>
        <TH>Password</TH><TD><input size="25" maxlength="20" type="password" name="passwd" value="{$RESULTS[0].passwd}"><BR />
        {if (($RESULTS[0].userid == '') or ($RESULTS[0].userid  == '0'))} (Enter Password.) {else} (Enter new password to change.) {/if}</TD>
    </TR>
    {if ($PASSWDAGING == TRUE)}
    <TR>
        <TH>Force Password Change</TH><TD><input type="checkbox" name="passwdchange"></TD>
    </TR>
    {/if}
    <TR>
        <TH>Enable User</TH><TD><input type="checkbox" name="userenabled" {if $RESULTS[0].userenabled == 'Y'} CHECKED {/if}></TD>
    </TR>
    <TR>
        <TH>E-Mail</TH><TD><input size="64" maxlength="60" name="useremail" value="{$RESULTS[0].useremail}"></TD>
    </TR>
    <TR>
        <TH>Disable Mail</TH><TD><input type="checkbox" name="userdisablemail" {if $RESULTS[0].userdisablemail == 'Y'} CHECKED {/if}></TD>
    </TR>
</TABLE>
<P></P>
{if $GROUPRESULT != ''}
<TABLE class = "editusergroups">
    {section name=grouploop loop=$GROUPRESULT}
    <TR>
        <TH>&nbsp;</TH><TD><input type="checkbox" {if $GROUPRESULT[grouploop].useringroup == 'Y'}CHECKED{/if} name="{$GROUPRESULT[grouploop].groupname}"></TD><TD><B>{$GROUPRESULT[grouploop].groupname}: </B>{$GROUPRESULT[grouploop].description}</TD>
    </TR>
    {/section}
</TABLE>
{/if}
<P>Last Logged in: {$RESULTS[0].lastseendate}</P>
<input type="Submit" value="Save" name="save">
</FORM>
{if $RESULTS[0].userid != ''}
<P>If you do not wish to modify this user account you can <a href="editusers.php">find another user</a>{if $REFERER != ''}, <a href="{$REFERER}">go back to the list</a> or{/if} <a href="editusers.php?action=new">add a new user</a>.</P>
{else}
<P> If you do not wish to create a new user account you can <a href="editusers.php">find another user</a> </P>
{/if}
{/block}

