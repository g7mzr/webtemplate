{***********************************************************************************

 This file is part of Webtemplate.

 (c) Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
{extends file="layout.tpl"}
{config_load file=$CONFIGFILE}
{block name=title}: 1.0 Release Notes{/block}
{block name=head}{/block}
{block name=body}
<h1>{#application_name#} 1.0 Release Notes</h1>

<ul class="toc">
  <li><a href="#v10_introduction">Introduction</a></li>
  <li><a href="#v10_point">Updates in this 1.0.x Release</a></li>
  <li><a href="#v10_bugs">Fixed Issues</a></li>
  <li><a href="#v10_feat">New Features and Improvements</a></li>
  <li><a href="#v10_removed">Removed Features</a></li>
  <li><a href="#v10_issues">Outstanding Issues</a></li>
</ul>

<h2 id="v10_introduction">Introduction</h2>

<p>text to be Added</p>

<h2 id="v10_point">Updates in this 1.0.x Release</h2>

<h3>1.0.0</h3>

<p>This Release contains....</p>

<h2 id="v10_bugs">Fixed Issues</h2>

<p>text to be Added</p>

<h2 id="v10_feat">New Features and Improvements</h2>

<p>text to be Added</p>

<h2 id="v10_removed">Removed Features</h2>

<p>text to be Added</p>

<h2 id="v10_issues">Outstanding Issues</h2>

<p>text to be Added</p>

{/block}

