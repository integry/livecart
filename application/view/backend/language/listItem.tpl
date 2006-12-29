<li id="languageList_{$item.ID}" class="activeList_add_sort{if $item.isDefault} activeList_remove_delete{/if}">
	<div id="languageList_container_{$item.ID}">
		<div class="langListContainer">
			<span>
				<input type="checkbox" class="checkbox" id="languageList_enable_{$item.ID}" {if $item.isEnabled}checked{/if} {if $item.isDefault}disabled{/if} onclick="lng.setEnabled('{$item.ID}', 1 - {$item.isEnabled});" />
			</span>	
		
			<span>
				<img src="image/localeflag/{$item.ID}.png" />
			
				<span class="langTitle">{$item.name}</span> 
			
				{if !$item.isEnabled}
				<span>({t _inactive})</span>
				{/if}
			</span>
			
			<div class="langListMenu">
				<a href="{link controller=backend.language action=setDefault}/{$item.ID}" class="listLink">
					{t _set_as_default}
				</a>
				<span id="langDefault">{t _default_language}</span>
			</div>
			
		</div>
	</div>			
</li>