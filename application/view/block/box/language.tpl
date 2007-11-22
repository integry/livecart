<div id="language">
	{if 'LANG_SELECTION'|config == 'LANG_DROPDOWN'}
		<select onchange="window.location.href=this.value;">
			{foreach from=$allLanguages item="language"}
				<option value="{$language.url}"{if $language.ID == $current.ID} selected="selected"{/if}>{$language.originalName}</option>
			{/foreach}
		</select>
	{else}
		{foreach from=$languages item="language"}
			{if 'LANG_SELECTION'|config == 'LANG_NAMES' || !$language.image}
				<a href="{$language.url}">{$language.originalName}</a>
			{else}
				<a href="{$language.url}"><img src="{$language.image}" alt="{$language.originalName}" title="{$language.originalName}" /></a>
			{/if}
		{/foreach}
	{/if}
</div>