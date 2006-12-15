{if $showContainer}
	<li id="currencyList_{$item.ID}" class="activeList_add_sort{if $item.isDefault} activeList_remove_delete{/if}">
{/if}
	<div id="currencyList_container_{$item.ID}">
		<div class="currListContainer">
			<span>
				<input type="checkbox" class="checkbox" id="currencyList_enable_{$item.ID}" {if $item.isEnabled}checked{/if} {if $item.isDefault}disabled{/if} onclick="curr.setEnabled('{$item.ID}', 1 - {$item.isEnabled});" />
			</span>	
		
			<span>	
				<span class="currTitle{if !$item.isEnabled} disabled{/if}">{$item.ID} - {$item.name}</span> 	
				{if !$item.isEnabled}
				<span>({t _inactive})</span>
				{/if}
			</span>
			
			<div class="currListMenu">
				<small>
					{if !$item.isDefault}
				<a href="{link controller=backend.currency action=setDefault}?id={$item.ID}" class="listLink">{t _set_as_default}</a>
					{else}
						<span id="currDefault">{t _default_currency}</span>
					{/if}
				</small>
			</div>
		</div>
	</div>			
{if $showContainer}
	</li>
{/if}