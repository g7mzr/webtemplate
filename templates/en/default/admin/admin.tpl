{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{config_load file=$CONFIGFILE}
{block name=title}: Admin{/block}
{block name=head}{/block}
{block name=body}
This page is only accessible to empowered users. You can access administrative pages from here
(based on your privileges), letting you configure different aspects of this installation.
<table>
  <tr>
    <td class="admin_links">
        {if $EDITSETTINGS == true}
            <dl class="dottedline">
                <dt><a href="/editsettings.php">Site Preferences</a></dt>
                <dd>Set the default user preferences. These are the values which will be used by default for all users. Users will be able to edit their own preferences from the <a href="userprefs.php">Preferences</a>.</dd>
            </dl>
        {/if}
        {if $EDITCONFIG == true}
            <dl class="dottedline">
                <dt><a href="/editconfig.php">Setup</a></dt>
                <dd>Edit the application configuration, including mandatory settings.</dd>
            </dl>
        {/if}
        {if $ABOUT == true}
            <dl class="dottedline">
                <dt><a href="/about.php">About</a></dt>
                <dd>Information about the application and server it is running on.</dd>
            </dl>
        {/if}
    </td>

    <td class="admin_links">
        {if $EDITUSERS == true}
            <dl class="dottedline">
                <dt><a href="/editusers.php">Users</a></dt>
                <dd>Create new user accounts or edit existing ones. You can also add and remove users from groups.</dd>
            </dl>
        {/if}
        {if $EDITGROUPS == true}
            <dl class="dottedline">
                <dt><a href="/editgroups.php">Groups</a></dt>
                <dd>Define groups which will be used in the installation. They can either be used to define new user privileges.</dd>
            </dl>
        {/if}
   </td>

  </tr>
</table>
{/block}

