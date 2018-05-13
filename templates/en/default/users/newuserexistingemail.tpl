{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{config_load file=$CONFIGFILE}
Hello {$REALNAME},

{#application_name#} has received a request to create an account
using your email address ({$EMAILADDRESS}) which already exists
in the database.

If you created this request in error please ignore this email.

If you did not create this request and you are concerned please
log in to {#application_name#} and change your password.

If you have any further concerns please contact the admin user
at {$ADMINADDR}

This attempt to user your email address to create a new account
has been logged and will be investigated.

Thank You

Sysadmin
