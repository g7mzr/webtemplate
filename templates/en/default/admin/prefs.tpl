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
<P>This lets you edit the default preferences values.</P>
<P>The Default Value displayed for each preference will apply to all users who do not choose their own value,
and to anyone who is not logged in.</P>
<P>The 'Enabled' checkbox controls whether or not this preference is available to users.<br />
If it is checked, users will see this preference on their User Preferences page, and will be allowed to choose
their own value if they desire.<br />
If it is not checked, this preference will not appear on the User Preference page, and the Default Value will
automatically apply to everyone.</P>
<hr>
<form name="adminsettingform" method="post" action="editsettings.php">
<table border="1" cellpadding="4">
    <tr>
        <th>Preference Text</th>
        <th>Default Value</th>
        <th>Enabled</th>
    </tr>
    <tr>
        <td align="right">{#application_name#} Default Theme</td>
        <td>
            <select name="theme" id="theme">
                {section name=themeloop loop=$THEME}
                    <option value="{$THEME[themeloop].name}" {if $THEME[themeloop].selected == true}selected="selected"{/if}>{$THEME[themeloop].name}</option>
                {/section}
            </select>
        </td>
        <td align="center"><input type="checkbox" name="theme-enabled"  id="theme-enabled" {if $THEME_ENABLED == true}checked="checked"{/if}></td>
    </tr>
    <tr>
        <td align="right">Zoom textareas large when in use (requires JavaScript) </td>
        <td>
            <select name="zoom_textareas" id="zoom_textareas">
                <option value="on" {if $ZOOM_TEXTAREAS_ON == true}selected="selected"{/if}>On</option>
                <option value="off" {if $ZOOM_TEXTAREAS_ON == false}selected="selected"{/if}>Off</option>
            </select>
        </td>
        <td align="center"> <input type="checkbox" name="zoom_textareas_enabled" id="zoom_textareas_enabled" {if $ZOOM_TEXTAREAS_ENABLED == true}checked="checked"{/if}></td>
    </tr>
    <tr>
        <td align="right">Search result lines to display</td>
        <td>
            <select name="display_rows" id="display_rows">
                <option value="1" {if $DISPLAY_ROWS == "1"}selected="selected"{/if}>10</option>
                <option value="2" {if $DISPLAY_ROWS == "2"}selected="selected"{/if}>20</option>
                <option value="3" {if $DISPLAY_ROWS == "3"}selected="selected"{/if}>30</option>
                <option value="4" {if $DISPLAY_ROWS == "4"}selected="selected"{/if}>40</option>
                <option value="5" {if $DISPLAY_ROWS == "5"}selected="selected"{/if}>50</option>
            </select>
        </td>
        <td align="center"> <input type="checkbox" name="display_rows_enabled" id="display_rows_enabled" {if $DISPLAY_ROWS_ENABLED == true}checked="checked"{/if}></td>
    </tr>


</table>
<input type="hidden" name="action" value="update">
<input type="hidden" name="token" value="{$TOKEN}">
<table>
<tr>
   <td width="150"></td>
   <td><input type="submit" id="update" value="Submit Changes"></td>
</tr>
</table>
</form>
{/block}

