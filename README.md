# WEBTEMPLATE

## 1. Introduction

I originally created webtemplate as base for a number of web based applications I
was considering.  Over the years rather than being used to create applications
webtemplate became a tool I used to develop my PHP programming skills as I tried out
new ways of writing code such as abstract classes and additional capability such as
a RESTful API

I have uploaded the code to GitHub as I have spent a significant number of hours
developing it and I would hate to lose it due to a computer failure.

## 2. Project Status

This project is **not suitable for deploying in a live environment** as there is
likely to be significant security holes in the code as it has not been reviewed or
tested by anyone but me.

The documentation is not up to date

You are welcome to use the code at your own risk.

The code is licensed under GNU GENERAL PUBLIC LICENSE V3 29 June 2007

## 3. Installation Instructions

In order to use Webtemplate the minimum requirements are:

  * PHP7
  * Phing
  * Apache 2
  * PostgreSQL Database

To install Webtemplate carry out the following steps

  * Clone Webtemplate
  * Copy config.php.dist to config.php
  * Edit config.php with your system settings
  * run "phing prepare"
  * run "install.php"
  * Configure Apache

Webtemplate is now ready to run.

If you run phing without any targets is will run php tools such as phpmd, phpcpd, phpcs,
pdepend and phpunit.  It will then generate all the reports required for a Continuous
Integration system such as Jenkins.




