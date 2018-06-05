{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{config_load file=$CONFIGFILE}
{block name=title}: Admin{/block}
{block name=head}{/block}
{block name=body}
<p id="center">
This page is only accessible to empowered users. You can access administrative pages from here
(based on your privileges), letting you configure different aspects of this installation.
</p>
<p>
<table id="adminmenu" border=1 width="70%">
    {foreach $PAGELIST as $page}
        {if (in_array($page.group, $GROUPLIST)  == true) or (in_array('admin', $GROUPLIST) == true)}
            <tr>
                <td width="10%"><a href="{$page.url}">{$page.name}</a></td>
                <td width="10%" id="center">
                    <a href="{$page.url}">
                        <img src="images/{$page.icon}" alt="{$page.alttext}" height="42" width="42">
                    </a>
                </td>
                <td width="80%">{$page.description}</td>
            </tr>
        {/if}
    {/foreach}
</table>
</p>
{/block}

