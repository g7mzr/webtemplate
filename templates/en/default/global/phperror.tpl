{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{config_load file=$CONFIGFILE}
{block name=title}: Error{/block}
{block name=head}{/block}
{block name=body}
    <H2>INTERNAL SERVER ERROR</H2>
    <P>
        Webtemplate has encountered an error and cannot complete the task
        you have requested.
    </P>
    <P>
        The error has been logged.  If it continues please report the
        error to the site administrator
        <a href="mailto:{$ADMIN}" target="_top">{$ADMIN}</a>.
    </P>
    {if #Production# == false}
        <BR>
        <HR>
        <H2>DEBUGGING INFORMATION - DEVELOPMENT SERVER ONLY</H2>
        <P>
            If you are seening this information on a  production server please read
            your installation instructions on how to suppress the error information.
        </P>

        <H3>Message</H3>
        <UL>
            <LI>{$MESSAGE}</LI>
        </UL>

        <H3>File</H3>
        <UL>
            <LI>{$FILE}</LI>
        </UL>

        <H3>Line</H3>
        <UL>
            <LI>{$LINE}</LI>
        </UL>

        <H3>Stack Trace</H3>
        <OL>
            {foreach $TRACE as $traceline}
                <LI>{$traceline}</LI>
            {/foreach}
        </OL>
    {/if}

{/block}

