<?php
$parameters['urlbase'] = 'http://www.example.com';
$parameters['maintainer'] = 'phpunit@example.com';
$parameters['docbase'] = 'docs/';
$parameters['cookiedomain'] = '';
$parameters['cookiepath'] = '/';
$parameters['admin']['logging'] = '1';
$parameters['admin']['logrotate'] = '2';
$parameters['admin']['newwindow'] = true;
$parameters['admin']['maxrecords'] = '2';
$parameters['users']['newaccount'] = false;
$parameters['users']['newpassword'] = true;
$parameters['users']['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
$parameters['users']['regexpdesc'] = 'Must contain upper and lower case letters and numbers only';
$parameters['users']['passwdstrength'] = '4';
$parameters['users']['passwdage'] = '1';
$parameters['users']['autocomplete'] = false;
$parameters['users']['autologout'] = '1';
$parameters['email']['smtpdeliverymethod'] = 'smtp';
$parameters['email']['emailaddress'] = 'phpunit@example.com';
$parameters['email']['smtpserver'] = 'smtp.example.com';
$parameters['email']['smtpusername'] = 'user';
$parameters['email']['smtppassword'] = 'password';
$parameters['email']['smtpdebug'] = false;
?>
