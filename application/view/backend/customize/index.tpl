{pageTitle}{t _live_customization}{/pageTitle}
{includeCss file=backend/Customize.css}

{include file="layout/backend/header.tpl"}

<table id="customizeMenu">
	<tr>
		<td>
			<img src="image/backend/icon/translate.gif" style="vertical-align: absmiddle;">
		</td>
		<td>
			<a href="{link controller=backend.customize action=translationMode}">Live Translation Mode</a>
			{if $isTranslationModeEnabled}
				(on)
			{else}
				(off)
			{/if}
			Translate menus and captions to other languages directly from <s>user interface</s>.
		</td>
	</tr>

	<tr>
		<td>
			<img src="image/backend/icon/customize.gif" style="vertical-align: absmiddle;">
		</td>
		<td>
			<a href="">Customize Layout</a>
			Move page blocks around in drag & drop mode
		</td>
	</tr>
	
	<tr>
		<td>
			<img src="image/backend/icon/templates.gif" style="vertical-align: absmiddle;">
		</td>
		<td>
			<a href="">Edit Templates</a>
			Locate and modify individual page blocks
		</td>
</table>

{include file="layout/backend/footer.tpl"}