{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="users/preferences/preferences-main.tpl"}
{config_load file=$CONFIGFILE}
{block name=form}
    <h2>General Preferences</H2>
    {if $ATLEASTONEPREFERENCE == true}
    Welcome to the general preferences page.  On this page you can configure how {#application_name#} works for you by
    chosing to set the preferences below to your requirements.<br />
    <form name="userprefsform" method="post" action="userprefs.php" autocomplete="off">
    <table border="0" cellpadding="8">
        <tr>
            <th>Preference Text</th>
            <th>Value</th>
        </tr>
        {if $THEME_ENABLED == true}
        <tr>
            <td align="right">{#application_name#}'s general appearance (theme)</td>
            <td>
                <select name="theme" id="theme">
                {section name=themeloop loop=$THEME}
                    <option value="{$THEME[themeloop].name}" {if $THEME[themeloop].selected == true}selected="selected"{/if}>{$THEME[themeloop].title}</option>
                {/section}
                </select>
            </td>
        </tr>
        {/if}
        {if $ZOOM_TEXTAREAS_ENABLED == true}
        <tr>
            <td align="right">Zoom textareas large when in use (requires JavaScript) </td>
            <td>
                <select name="zoom_textareas" id="zoom_textareas">
                {section name=zoomtextloop loop=$TEXTAREAS}
                    <option value="{$TEXTAREAS[zoomtextloop].name}" {if $TEXTAREAS[zoomtextloop].selected == true}selected="selected"{/if}>{$TEXTAREAS[zoomtextloop].title}</option>
                {/section}
                </select>
            </td>
        </tr>
        {/if}
        {if $DISPLAY_ROWS_ENABLED == true}
        <tr>
            <td align="right">Search result lines to display</td>
            <td>
                <select name="display_rows" id="display_rows">
                {section name=displayrowsloop loop=$DISPLAYROWS}
                    <option value="{$DISPLAYROWS[displayrowsloop].name}" {if $DISPLAYROWS[displayrowsloop].selected == true}selected="selected"{/if}>{$DISPLAYROWS[displayrowsloop].title}</option>
                {/section}
                </select>
            </td>
        </tr>
        {/if}

    </table>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="token" value="{$TOKEN}">
    <input type="hidden" name="tab" value="settings">
    <table>
        <tr>
            <td width="150"></td>
            <td><input type="submit" id="update" value="Submit Changes"></td>
        </tr>
    </table>
</form>
{else}
    The administrator has not enabled any site preferences for the user to change
{/if}
{/block}