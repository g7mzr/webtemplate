{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{block name=title}: {$PAGETITLE}{/block}
{block name=head}{/block}
{block name=body}
<table border="0" width="100%">
    <tr>
        <td width="15%">
            <table id="menu" border=1>
                <tr>
                    <td class="index">Index</td>
                </tr>
                {foreach $PAGELIST as $PAGE}
                    <tr>
                        {if $PAGE.selected == true}
                            <td class="selected_section">{$PAGE.description}</td>
                        {else}
                            <td><a href="{$PAGE.url}">{$PAGE.description}</a></td>
                        {/if}
                    </tr>
                {/foreach}

           </table>
        </td>

        <td>
            <div class="notice"><strong>Note:</strong> This is the main configuration pages for {#application_name#}.
                Please be careful when modifying any of the parameters as it may compromise your security or stop
                the application working all together.
            </div>
            <p> </p>
            {block name=form}{/block}
        </td>
    </tr>
</table>
{/block}
