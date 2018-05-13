{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{config_load file=$CONFIGFILE}
{block name=title}: Search Users{/block}
{block name=head}{/block}
{block name=body}
<P></P>
    <FORM  method="get" action="editusers.php" name="user_search"><input type="hidden" name="action" value="list" />
        List users with
        <SELECT name="searchtype">
            <OPTION value="username">User Name</OPTION>
            <OPTION value="realname">Real Name</OPTION>
            <OPTION value="email">E-Mail</OPTION>
        </SELECT>
        matching <input type="TEXT" size="32" name="searchstr"> <input type="Submit" value="Search" id="search_button">
    </FORM>
<P>You can also <a href="editusers.php?action=new">add a new user</a></P>
{/block}
