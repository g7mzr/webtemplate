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
            <ul class="links">
                <li><a href="./">Home</a></li>
                {if $LOGIN == 'false'}
                    <li><span class="separator">|</span> <a href="userprefs.php">Preferences</a></li>
                    {if $ADMINACCESS  == true}
                        <li><span class="separator">|</span> <a href="admin.php">Administration</a></li>
                    {/if}
                    <li><span class="separator">|</span> <a href="logout.php">Logout</a>&nbsp;{$USERNAME}</li>
                {/if}
            </ul>
</div>
<div id="main-body">
{if $MSG != ''}
<div id="msg-box">{$MSG|nl2br nofilter}</div>
{/if}

{block name=body}{/block}

</div>
<div id="footer_menu">
<ul id="useful-links">
    <li id="links-actions">
        <div class="label">Actions:&nbsp;</div>
            <ul class="links">
                <li><a href="/">Home</a></li>
                {if $LOGIN == 'false'}
                    <li><span class="separator">|</span> <a href="userprefs.php">Preferences</a></li>
                    {if $ADMINACCESS  == true}
                        <li><span class="separator">|</span> <a href="admin.php">Administration</a></li>
                    {/if}
                    <li><span class="separator">|</span> <a href="logout.php">Logout</a>&nbsp;{$USERNAME}</li>
                {/if}
            </ul>
    </li>
    {if $LOGIN == 'false'}
        <li id="links-saved">
            <div class="label">Searches: </div>
                <ul class="links">
                    <li><a href="/">Home</a></li>
                </ul>
        </li>
    {/if}
</ul>
</div>
<div id="copyright">Copyright {#Author#} {$YEAR} </div>
</body>
</html>
