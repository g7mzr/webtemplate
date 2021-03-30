{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{config_load file=$CONFIGFILE}
{block name=title}: {if $LOGIN == "true"}Login{else}Home{/if}{/block}
{block name=head}{/block}
{block name=body}
<P>Welcome to Sandy's Web Skeleton Database.<br /></P>
<BR />
{if $LOGIN == "true"}
    <FORM method="POST" action="index.php" name="LOGIN_Form" {if $AUTOCOMPLETE == true}autocomplete="on"{else}autocomplete="off"{/if}>
        <TABLE>
            <TR>
                <TH Width=100 Align=left>User Name</TH>
                <TH Width=100><input type="TEXT" name="username" {if $AUTOCOMPLETE == true}autocomplete="on"{else}autocomplete="off"{/if}></TH>
            </TR>
            <TR>
                <TH Width=100 Align=left>Password</TH>
                <TH Width=100><input type="PASSWORD" name="password" id="password" {if $AUTOCOMPLETE == true}autocomplete="on"{else}autocomplete="off"{/if}></TH>
            </TR>
            <TR>
                <TH Align="left" colspan="2">Show Password <input type="checkbox" onclick="viewPassword();"></TH>
            </TR>
        </TABLE>
        <input type="Submit" value="Login" name="Login_Button" id="Login_Button">
    </FORM>
    <BR />
    {if $SELFREGISTER == true}
        <a href="/register.php">[New Account]</a>
    {/if}
    {if $NEWPASSWDENABLED == true}
        <a href="/resetpasswd.php">[Forgot Your Password?]</a>
    {/if}

    <script>
        function viewPassword() {
            var x = document.getElementById("password");
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        }
    </script>

{else}
    {include file="global/homeloggedin.tpl"}
    {if $LASTLOGEDIN <> ''}
        <P>You last accessed {#application_name#} on the {$LASTLOGEDIN}</P>
    {/if}
{/if}
<center>
    {if $DOCSAVAILABLE == true}
        <a href="showdocs.php?page=using" {if $NEWWINDOW == true}target="_blank"{/if}>Using {#application_name#}</a> |
    {/if}
    <a href="page.php?id=release-notes.html">Release Notes</a> |
    <a href="page.php?id=cookie-policy.html">Cookie Policy</a>

</center>

{/block}

