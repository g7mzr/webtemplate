{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{config_load file=$CONFIGFILE}
{block name=title}: List Users{/block}
{block name=head}{/block}
{block name=body}
<P>{$USERSFOUND} users found.</P>
<TABLE border="1" width="70%">
    <TR>
        <TD width="15%" valign="top" align="center" > <B>User Name</B></TD>
        <TD width="35%" valign="top" align="center" > <B>Real Name</B></TD>
        <TD width="30%" valign="top" align="center" > <B>Email Address</B></TD>
        <TD width="10%" valign="top" align="center" > <B>Last Logged in</B></TD>
        <TD width="10%" valign="top" align="center" > <B>Passwd Changed</B></TD>
        <TD width="10%" valign="top" align="center" > <B>Locked</B></TD>
    </TR>
    {section name=useridloop loop=$RESULTS}
    <TR>
        <TD width="15%" valign="top" align="center" > <a href="editusers.php?action=edit&userid={$RESULTS[useridloop].userid}">{$RESULTS[useridloop].username}</a></TD>
        <TD width="35%" valign="top" align="center" > {$RESULTS[useridloop].realname}</TD>
        {if $RESULTS[useridloop].useremail != ''}
            <TD width="30%" valign="top" align="center" > <a href=mailto:{$RESULTS[useridloop].useremail}>{$RESULTS[useridloop].useremail}</a></TD>
        {else}
            <TD width="30%" valign="top" align="center" > &nbsp;</TD>
        {/if}
        <TD width="10%" valign="top" align="center" >{$RESULTS[useridloop].lastseendate}</TD>
        <TD width="10%" valign="top" align="center" >{$RESULTS[useridloop].passwdchanged}</TD>
        {if $RESULTS[useridloop].userenabled != 'Y'}
            <TD width="40%" valign="top" align="center" > Yes </TD>
        {else}
            <TD width="40%" valign="top" align="center" > &nbsp;</TD>
        {/if}
    </TR>
    {/section}
</TABLE>
<P>If you do not wish to modify a user account you can <a href="editusers.php">find another user</a> or <a href="editusers.php?action=new">add a new user</a>.</P>
{/block}

