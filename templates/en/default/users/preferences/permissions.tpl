{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="users/preferences/preferences-main.tpl"}
{config_load file=$CONFIGFILE}
{block name=form}
    <h2>Permissions</h2>
    <div id="permissions">
        You have the following permissions set on your account.
        <P></P>
        {if $GROUPRESULT != ''}
        <TABLE>
            {section name=grouploop loop=$GROUPRESULT}
                <TR>
                    {if $GROUPRESULT[grouploop].autogroup == 'N'}
                        <TH>&nbsp;</TH><TD><B>{$GROUPRESULT[grouploop].groupname}</B></TD><TD>{$GROUPRESULT[grouploop].description}</TD>
                    {/if}
                    {if $GROUPRESULT[grouploop].autogroup == 'Y'}
                        <TH>&nbsp;</TH><TD><B>{$GROUPRESULT[grouploop].groupname}</B></TD><TD>{$GROUPRESULT[grouploop].description} (*)</TD>
                    {/if}
                </TR>
            {/section}
        </TABLE>
        {/if}
    </div>
{/block}