{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{config_load file=$CONFIGFILE}
Hello,

{#application_name#} has received a request to create an account
using your email address ({$EMAILADDRESS}).

To continue creating your account please follow the link below
by {$EXPIREDATE}.

{$NEWACCOUNTLINK}

If you have not requested to create an account or have requested to
create an account in error please ignore this e-mail.

Thank You

Sysadmin
