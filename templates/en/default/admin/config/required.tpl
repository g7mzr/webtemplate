{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="admin/config/config-main.tpl"}
{config_load file=$CONFIGFILE}
{block name=form}
    <form name="configrequiredform" method="post" action="editconfig.php">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="token" value="{$TOKEN}">
        <input type="hidden" name="section" value="required">
        <dl>
            <dt><a name="url_base_id">URL Base</a></dt>
            <dd>The URL that is the common initial leading part of all {#application_name#} URLs.
                <p><input type="text" size="80" name="url_base_id" id="url_base_id" value="{$URLBASE}"></p>
                <hr>
            </dd>
            <dt><a name="maintainer_id">Maintainer</a></dt>
            <dd>The {#application_name#} maintainer's email address.
                <p><input type="text" size="80" name="maintainer_id" id="maintainer_id" value="{$MAINTAINER}"></p>
                <hr>
            </dd>
            <dt><a name="doc_baseurl_id">Doc Base URL</a></dt>
            <dd>The URL that is the common initial leading part of all {#application_name#} documentation URLs. It is relative to urlbase above. Leave this empty to suppress links to the documentation.
    			'%lang%' will be replaced by user's preferred language (if documentation is available in that language).
                <p><input type="text" size="80" name="doc_baseurl_id" id="doc_baseurl_id" value="{$DOCBASEURL}"></p>
                <hr>
            </dd>
            <dt><a name="cookie_domain_id">Cookie Domain</a></dt>
            <dd>The domain for {#application_name#} cookies. Normally blank. If your website is at 'www.foo.com', setting this to '.foo.com' will also allow 'bar.foo.com' to access {#application_name#} cookies. This is useful if you have more than one hostname pointing at the same web server, and you want them to share the {#application_name#} cookie.
                <p><input type="text" size="80" name="cookie_domain_id" id="cookie_domain_id" value="{$COOKIEDOMAIN}"></p>
                <hr>
            </dd>
            <dt><a name="cookie_path_id">Cookie Path</a></dt>
            <dd>Path, relative to your web document root, to which to restrict {#application_name#} cookies. Normally this is the URI portion of your URL base. Begin with a / (single slash mark). For instance, if {#application_name#} serves from 'http://www.somedomain.com/{#application_name#}/', set this parameter to /{#application_name#}/. Setting it to / will allow all sites served by this web server or virtual host to read {#application_name#} cookies.
                <p><input type="text" size="80" name="cookie_path_id" id="cookie_path_id" value="{$COOKIEPATH}"></p>
                <hr>
            </dd>
        </dl>
        <input type="submit" id="update" value="Submit Changes">
    </form>
{/block}
