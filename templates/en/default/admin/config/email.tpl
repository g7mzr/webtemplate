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
        <input type="hidden" name="section" value="email">
        <dl>
            <dt><a name="mail_delivery_method">Mail Delivery Method</a></dt>
            <dd>Defines how email is sent, or if it is sent at all.<br>
                <ul>
                    <li>
                        'Sendmail' and 'SMTP' are all MTAs.
                    </li>
                    <li>
                        'Test' is useful for debugging: all email is stored
                        in 'logs/mailer.testfile' instead of being sent.
                    </li>
                    <li>
                        'none' will completely disable email. {#application_name#} continues
                        to act as though it is sending mail, but nothing is sent or
                        stored.
                    </li>
                </ul>
                <p>
                    <select name="mail_delivery_method" id="mail_delivery_method">
                        <option value="none" {if $MAIL_DELIVERY_METHOD == "none"}selected="selected"{/if}>None</option>
                        <option value="smtp" {if $MAIL_DELIVERY_METHOD == "smtp"}selected="selected"{/if}>SMTP</option>
                        {*    <option value="sendmail" {if $MAIL_DELIVERY_METHOD == "sendmail"}selected="selected"{/if}>Sendmail</option> *}
                        <option value="test" {if $MAIL_DELIVERY_METHOD == "test"}selected="selected"{/if}>Test</option>
                    </select>
                </p>
                <hr>
            </dd>

            <dt><a name="email_address_id">Email Address</a></dt>
            <dd>The email address that is used by {#application_name#} as its From: address.
                <p><input type="text" size="80" name="email_address_id" id="email_address_id" value="{$EMAILADDRESS}"></p>
                <hr>
            </dd>

            <dt><a name="smtp_server_id">SMTP Server</a></dt>
            <dd>The SMTP server address (if using SMTP for mail delivery)
                <p><input type="text" size="80" name="smtp_server_id" id="smtp_server_id" value="{$SMTPSERVER}"></p>
                <hr>
            </dd>

            <dt><a name="smtp_user_name_id">SMTP Username</a></dt>
            <dd>The username to pass to the SMTP server for SMTP authentication. Leave this field empty if your SMTP server doesn't require authentication.
                <p><input type="text" size="80" name="smtp_user_name_id" id="smtp_user_name_id" value="{$SMTPUSERNAME}"></p>
                <hr>
            </dd>

            <dt><a name="smtp_passwd_id">SMTP Password</a></dt>
            <dd>The password to pass to the SMTP server for SMTP authentication. This field has no effect if the SMTP Username parameter is left empty.
                <p><input type="text" size="80" name="smtp_passwd_id" id="smtp_passwd_id" value="{$SMTPPASSWD}"></p>
                <hr>
            </dd>

            <dt><a name="smtp_debug">SMTP Debug</a></dt>
            <dd>If enabled, this will print detailed information to your web server's error log about the
                communication between Bugzilla and your SMTP server. You can use this to troubleshoot email problems.
                <p>
                    <select name="smtp_debug" id="smtp_debug">
                        <option value="yes" {if $SMTP_DEBUG_ON == true}selected="selected"{/if}>Yes</option>
                        <option value="no" {if $SMTP_DEBUG_ON == false}selected="selected"{/if}>No</option>
                    </select>
                </p>
                <hr>
            </dd>
        </dl>
        <input type="submit" id="update" value="Submit Changes">
    </form>
{/block}
