{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{config_load file=$CONFIGFILE}
Hello {$EMAILUSERNAME},

You have requested a new password for {#application_name#}.

In order to reset your password please use the link below and
follow the instruction on the webpage.

{$PASSWODRESETLINK}


{$PASSWDFORMAT}


If you have not requested a new password please ignore this
e-mail and the link will expire in 1 hour.

You can also cancel the request by following the link above
and clicking on the "Cancel Password Request" button.

Thank You

Sysadmin
