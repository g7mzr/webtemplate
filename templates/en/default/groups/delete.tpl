{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{config_load file=$CONFIGFILE}
{block name=title}: Delete Group{/block}
{block name=head}{/block}
{block name=body}
<TABLE border="1" width="60%">
    <TR Class="tableheader">
        <TH width="5%">ID</TH>
        <TH width="15%">Name</TH>
        <TH width="45%">Description</TH>
    </TR>
    <TR>
        <TD width=5%><CENTER>{$RESULTS[0].groupid}</CENTER></TD>
        <TD width=15%>{$RESULTS[0].groupname}</TD>
        <TD width=45%>{$RESULTS[0].description} </TD>
    </TR>
</TABLE>
<form method="post" action="editgroups.php">

  <h2>Confirmation</h2>

  <p>Do you really want to delete this group?</p>

  <p>
    <input type="submit" id="delete" value="Yes, delete">
    <input type="hidden" name="action" value="del">
    <input type="hidden" name="groupid" value="{$RESULTS[0].groupid}">
    <input type="hidden" name="token" value="{$TOKEN}">

  </p>
</form>
<BR />
<a href="editgroups.php?action=list">List groups</a>
{/block}

