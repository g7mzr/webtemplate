{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"  "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Language" content="en-uk">
    {section name=id loop=$STYLESHEET}
        <link rel="stylesheet" type="text/css" href="{$STYLESHEET[id]}">
    {/section}
    <title>{#application_name#}{block name=title}{/block}</title>
    {block name=head}{/block}
</head>
<body>
{include file="global/banner.tpl"}
<div id="header">
    <div id="left">
        {#application_name#}{block name=title}{/block}
    </div>
    <div id="right">
    version {#application_version#}
    </div>
</div>
<div id="menu_bar">
    <table id="mainmenu" border=1>
        <tr>
            {foreach $MAINMENU as $menuitem}
                {if $menuitem.loggedin == false}
                    <td onClick="document.location='{$menuitem.url}';"><a href="{$menuitem.url}">{$menuitem.name}</a></td>
                {/if}
                {if ($menuitem.loggedin == true) and ($LOGIN == false)}
                    {if $menuitem.admin == false}
                        <td onClick="document.location='{$menuitem.url}';"><a href="{$menuitem.url}">{$menuitem.name}</a></td>
                    {elseif ($menuitem.admin == true) and ($ADMINACCESS == true)}
                        <td onClick="document.location='{$menuitem.url}';"><a href="{$menuitem.url}">{$menuitem.name}</a></td>
                    {/if}
                {/if}

            {/foreach}
        </tr>
    </table>
</div>
<div id="main-body">
{if $UPDATEMSG != ''}
    <div id="updatemsg-box">
        <B>Update Information</B><P />
        {$UPDATEMSG|nl2br nofilter}
    </div>
{/if}

{if $MSG != ''}
<div id="msg-box">{$MSG|nl2br nofilter}</div>
{/if}

{block name=body}{/block}

</div>
<div id="footer_menu">
</div>
<div id="copyright">Copyright {#Author#} {$YEAR} </div>
{include file="global/floatingfooter.tpl"}
</body>
</html>
