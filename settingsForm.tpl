{**
 * plugins/gateways/rest/settingsForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * OJS REST gateway plugin settings
 *
 *}
{strip}
{assign var="pageTitle" value="plugins.gateways.rest.displayName"}
{include file="common/header.tpl"}
{/strip}

{url|assign:"directoryUrl" page="gateway" op="plugin" path="RestPlugin"}
<div id="restGatewaySettings">
<h3>{translate key="plugins.gateways.rest.settings"}</h3>

<form method="post" action="{plugin_url path="settings"}">
{include file="common/formErrors.tpl"}

<table width="100%" class="data">
	<tr valign="top">
		<td width="30%" class="label" align="right">{fieldLabel name="apiKey" key="plugins.gateways.rest.settings.apiKey"}</td>
		<td width="70%" class="value">
		<input type="text" name="apiKey" id="apiKey" value="{$apiKey}" size="50" maxlength="50" class="textField" /></td>
	</tr>
</table>

<br/>

<input type="submit" name="save" class="button defaultButton" value="{translate key="common.save"}"/>
<input type="button" class="button" value="{translate key="common.cancel"}" onclick="document.location.href='{url|escape:"quotes" page="manager" op="plugins" escape="false"}'"/>
</form>
</div>
{include file="common/footer.tpl"}
