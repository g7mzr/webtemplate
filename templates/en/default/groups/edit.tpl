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
<FORM  method="post" action="editgroups.php" name="save_group">
<input type="hidden" name="action" value="save" />
<input type="hidden" name="token" value ="{$TOKEN}">
<input type="hidden" name="groupid" value="{$RESULTS[0].groupid|default:0}" />
<TABLE class="editgroup">
    <TR>
        <TH>Group Name</TH>
        {if $RESULTS[0].editable == 'Y'}
            <TD><input size="64" maxlength="25" name="groupname" {$READONLY} value="{$RESULTS[0].groupname}"></TD>
        {else}
        <TD><input size="64" maxlength="25" name="groupname" READONLY value="{$RESULTS[0].groupname}"></TD>
        {/if}
    </TR>
    <TR>
        <TH>Group Description</TH>
        {if $RESULTS[0].editable == 'Y'}
            <TD><input size="64" maxlength="225" name="description" value="{$RESULTS[0].description}"></TD>
        {else}
        <TD><input size="64" maxlength="225" name="description" READONLY value="{$RESULTS[0].description}"></TD>
        {/if}
    </TR>
    <TR>
        <TH>Use for Product</TH><TD><input type="checkbox" name="useforproduct" {if $RESULTS[0].useforproduct == 'Y'} CHECKED {/if}></TD>
    </TR>
    <TR>
        <TH>Automatic Membership</TH><TD><input type="checkbox" name="autogroup" {if $RESULTS[0].autogroup == 'Y'} CHECKED {/if}></TD>
    </TR>
</TABLE>
<P></P>
<input type="Submit" value="Save" name="save">
</FORM>
{if $RESULTS[0].groupid != ''}
<P>If you do not wish to modify this group you can <a href="/editgroups.php">find another group</a> or <a href="/editgroups.php?action=new">add a new group</a>.</P>
{else}
<P> If you do not wish to create a new group you can <a href="/editgroups.php">find a group to edit</a> </P>
{/if}
{/block}

