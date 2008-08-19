{pageTitle help="update"}{t _update_livecart}{/pageTitle}

{includeCss file="backend/Update.css"}

{include file="layout/backend/header.tpl"}

<table id="versionCompare">
	<tr>
		<td>{t _newest}:</td>
		<td class="version">{$newest}</td>
	</tr>
	<tr>
		<td>{t _current}:</td>
		<td class="version {if $needUpdate}outdated{else}upToDate{/if}">{$current}</td>
	</tr>
</table>

<p>
{if $needUpdate}
	{t _newer_available|branding}.
{else}
	{t _up_to_date|branding}
{/if}
</p>

{include file="layout/backend/footer.tpl"}