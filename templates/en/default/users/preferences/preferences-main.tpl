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
<div class="tabbed">
  <table class="tabs" cellspacing="0" cellpadding="10" border="0" width="100%">
    <tr>
      <td class="spacer">&nbsp;</td>
            {foreach $PAGELIST as $page}
                {if $page.selected == true}
                    <td id="tab_settings" class="selected">{$page.description}</TD>
                {else}
                    <td id="tab_settings" class="clickable_area" onClick="document.location='{$page.url}';">
                        <a href="{$page.url}">{$page.description}</a>
                    </td>
                {/if}
            {/foreach}
      <td class="spacer">&nbsp;</td>
    </tr>
  </table>
  <div class="tabbody">
    {block name=form}{/block}
  </div>
</div>
{/block}

