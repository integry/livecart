{if $showContainer}
<li id="languageList_{$item.ID}" class="activeList_add_sort">
{/if}

	<div id="languageList_container_{$item.ID}">
		<input type="checkbox" id="languageList_enable_{$item.ID}" {if $item.isEnabled}checked{/if} {if $item.isDefault}disabled{/if} onclick="lng.setEnabled('{$item.ID}', 1 - {$item.isEnabled});" />
	
		<img src="image/localeflag/{$item.ID}.png" />
	
		<span class="enabled_{$item.isEnabled}">{$item.name}</span> 
	
		{if !$item.isEnabled}
		({t _inactive})
		{/if}
			
		<br />
	
		<div class="progress">
			<div></div>
			<div>
				<small><a href="{link controller=backend.language action=edit id=$item.ID}" class="listLink">{t _edit_definitions}</a>
		
					{if $item.isEnabled}		
				 | 
					{if !$item.isDefault}
				<a href="{link controller=backend.language action=setDefault id=$item.ID}" class="listLink">{t _set_as_default}</a>
					{else}
						<strong>{t _default_language}</strong>
					{/if}
					{/if}
				</small>
			</div>
		</div>		
	</div>

{if $showContainer}
</li>
{/if}	