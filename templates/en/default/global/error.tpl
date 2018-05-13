{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{config_load file=$CONFIGFILE}
{block name=title}: {$HEADERMSG}{/block}
{block name=head}{/block}
{block name=body}
<div id="error_msg" class="throw_error">
{$ERRORMSG}
</div>
<p />
<p>
    The system was unable to complete the task you requested.  The error has been
    logged.
</p>
<p>
    If the error persists please contact the System Adminsitrator and provide him
    with the details of the task you were trying to complete as well as the date
    and time.
</p>
<p>
    The System administrator can be contacted at
    <a href="mailto:{$SYSADMINEMAIL}">{$SYSADMINEMAIL}</a>
</p>
{/block}
