{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{config_load file=$CONFIGFILE}
{block name=title}: About{/block}
{block name=head}{/block}
{block name=body}
<h2>About: {#application_name#}</h2>
    <table width="60%" border="0">
        <tr>
            <td width="20%" ></td>
            <th align="left" width="20%" >Version:</th>
            <td width="50%" >{#application_version#}</td>
        </tr>
        <tr>
            <td width="20%" ></td>
            <th align="left" width="20%" >Author:</th>
            <td width="50%" >{#Author#}</td>
        </tr>
        <tr>
            <td width="20%" ></td>
            <th align="left" width="20%" >PHP Version:</th>
            <td width="50%" >{$PHPVERSION}</td>
        </tr>
        <tr>
            <td width="20%" ></td>
            <th align="left" width="20%" >Smarty Version:</th>
            <td width="50%" >{$smarty.version}</td>
        </tr>
        <tr>
            <td width="20%" ></td>
            <th align="left" width="20%" >Server Name:</th>
            <td width="50%" >{$SERVERNAME}</td>
        </tr>
        <tr>
            <td width="20%" ></td>
            <th align="left" width="20%" >Server Software:</th>
            <td width="50%" >{$SERVERSOFTWARE}</td>
        </tr>
        <tr>
            <td width="20%" ></td>
            <th align="left" width="20%" >Server Admin:</th>
            <td width="50%" >{$SERVERADMIN}</td>
        </tr>
        <tr>
            <td width="20%" ></td>
            <th align="left" width="20%" >Database Version:</th>
            <td width="50%" >{$DATABASEVERSION}</td>
        </tr>
         <tr>
            <td width="20%" ></td>
            <th align="left" width="20%" >Log Dir Size:</th>
            <td width="50%" >{$LOGDIRSIZE} ({$PERCENTAGE}%)</td>
        </tr>
  </table>
{/block}
