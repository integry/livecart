{if $showContainer}
<li id="languageList_{$item.ID}" class="activeList_add_sort">
{/if}
	<span id="languageList_container_{$item.ID}">
		<span>
			<input type="checkbox" class="checkbox" id="languageList_enable_{$item.ID}" {if $item.isEnabled}checked{/if} {if $item.isDefault}disabled{/if} onclick="lng.setEnabled('{$item.ID}', 1 - {$item.isEnabled});" />
		</span>	
	
		<span>
			<img src="image/localeflag/{$item.ID}.png" />
		
			<span class="langTitle enabled_{$item.isEnabled}">{$item.name}</span> 
		
			{if !$item.isEnabled}
			({t _inactive})
			{/if}
		</span>
		
		<div>
			{if $item.isEnabled}
				{if !$item.isDefault}
			<a href="{link controller=backend.language action=setDefault id=$item.ID}" class="listLink">{t _set_as_default}</a>
				{else}
					<span id="langDefault">{t _default_language}</span>
				{/if}
			{/if}
		</div>
	</span>			
{if $showContainer}</li>{/if}