<br>
{foreach from=$curr item=item}
	{$item.ID} ( {$item.currName} )
	<br>
	{if $item.isDefault}
		<b>{translate text="_defaultCurrency"}</b>
	{else}
		<a href="{link controller="backend.currency" action="setDefault" id=$item.ID}">{translate text="_setAsDefaultCurrency"}</a>
	{/if}
	<br>
	<br>
{/foreach}
