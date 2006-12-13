{if $showContainer}
	<li id="currencyList_{$item.ID}" class="activeList_add_sort">
{/if}

<span id="currencyList_container_{$item.ID}">

	<span>
		<input type="checkbox" class="checkbox" id="currencyList_enable_{$item.ID}" {if $item.isEnabled}checked{/if} {if $item.isDefault}disabled{/if} onclick="lng.setEnabled('{$item.ID}', 1 - {$item.isEnabled});" />
	</span>	

	<span>	
		<span class="currTitle enabled_{$item.isEnabled}">{$item.ID} - {$item.name}</span> 	
		{if !$item.isEnabled}
		<span>({t _inactive})</span>
		{/if}
	</span>
	
	<div>
		<small>
			{if !$item.isDefault}
		<a href="{link controller=backend.currency action=setDefault id=$item.ID}" class="listLink">{t _set_as_default}</a>
			{else}
				<span id="currDefault">{t _default_currency}</span>
			{/if}
		</small>
	</div>
</span>	
		
{if $showContainer}
	</li>
{/if}