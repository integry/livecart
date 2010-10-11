{foreach from=$items item=item}
	<div id="html_{$item.ID}">
		{include file="backend/shipment/itemAmount.tpl" item=$item}
	</div>
{/foreach}