{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{config_load file=$CONFIGFILE}
{block name=title}: List Groups{/block}
{block name=head}{/block}
{block name=body}
<TABLE border="1" width="80%">
    <TR Class="tableheader">
        <TH width="15%">Name</TH>
        <TH width="45%">Description</TH>
        <TH width="10%">Use For Products</TH>
         <TH width="10%">Automatic Membership</TH>
       <TH width="10%">Type</TH>
        <TH width="10%">Action</TH>
    </TR>
    {section name=grouplist loop=$RESULTS}
    <TR>

		<TD width=15%><a href="editgroups.php?action=edit&groupid={$RESULTS[grouplist].groupid}">{$RESULTS[grouplist].groupname}</A> </TD>
		<TD width=45%>{$RESULTS[grouplist].description} </TD>
        <TD width=10% align="center" >{if $RESULTS[grouplist].useforproduct == 'Y'} * {else} &nbsp {/if} </TD>
         <TD width=10% align="center" >{if $RESULTS[grouplist].autogroup == 'Y'} * {else} &nbsp {/if} </TD>
       <TD width=10% align="center" >{if $RESULTS[grouplist].editable == 'Y'} USER {else} SYSTEM {/if} </TD>
        <TD width=10%>{if $RESULTS[grouplist].editable == 'Y'} <a href="editgroups.php?action=showdel&groupid={$RESULTS[grouplist].groupid}">Delete</a> {/if} </TD>
    </TR>
    {/section}
</TABLE>
<BR />
<a href="/editgroups.php?action=new">New group</a>
{/block}

