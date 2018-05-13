{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="admin/config/config-main.tpl"}
{config_load file=$CONFIGFILE}
{block name=form}
    <form name="configauthform" method="post" action="editconfig.php">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="token" value="{$TOKEN}">
        <input type="hidden" name="section" value="admin">
        <dl>
            <dt><a name="logging">Logging Level</a></dt>
            <dd>The level of messages to be logged.
                <p>
                    <select name="logging" id="logging">
                        <option value="0" {if $LOGGING == "0"}selected="selected"{/if}>None</option>
                        <option value="1" {if $LOGGING == "1"}selected="selected"{/if}>Errors</option>
                        <option value="2" {if $LOGGING == "2"}selected="selected"{/if}>Warnings</option>
                        <option value="3" {if $LOGGING == "3"}selected="selected"{/if}>Information</option>
                        <option value="4" {if $LOGGING == "4"}selected="selected"{/if}>Debug</option>
                        <option value="5" {if $LOGGING == "5"}selected="selected"{/if}>Trace</option>
                    </select>
                </p>
                <hr>
            </dd>
        </dl>
        <dl>
            <dt><a name="logrotate">Log Rotate</a></dt>
            <dd>The interval between logfiles being rotated.
                <p>
                    <select name="logrotate" id="logrotate">
                        <option value="1" {if $LOGROTATE == "1"}selected="selected"{/if}>Daily</option>
                        <option value="2" {if $LOGROTATE == "2"}selected="selected"{/if}>Weekly</option>
                        <option value="3" {if $LOGROTATE == "3"}selected="selected"{/if}>Monthly</option>
                    </select>
                </p>
                <hr>
            </dd>
        </dl>
        <dl>
            <dt><a name="new_window">Open Links in New Window</a></dt>
            <dd>Open links in a new window or tab depending on browser settings.
                <p>
                    <select name="new_window" id="new_window">
                        <option value="yes" {if $NEW_WINDOW_ON == true}selected="selected"{/if}>Yes</option>
                        <option value="no" {if $NEW_WINDOW_ON == false}selected="selected"{/if}>No</option>
                    </select>
                </p>
                <hr>
            </dd>
        </dl>
        <dl>
            <dt><a name="max_records">Max Records</a></dt>
            <dd>Maximum number of membership records that can be returned in a single search.
                <p>
                    <select name="max_records" id="max_records">
                        <option value="1" {if $MAXRECORDS == "1"}selected="selected"{/if}>100</option>
                        <option value="2" {if $MAXRECORDS == "2"}selected="selected"{/if}>200</option>
                        <option value="3" {if $MAXRECORDS == "3"}selected="selected"{/if}>300</option>
                        <option value="4" {if $MAXRECORDS == "4"}selected="selected"{/if}>400</option>
                        <option value="5" {if $MAXRECORDS == "5"}selected="selected"{/if}>500</option>
                    </select>
                </p>
                <hr>
            </dd>
        </dl>
        <input type="submit" id="update" value="Submit Changes">
    </form>
{/block}
