{foreach from=$items item=item}
	<div id="html_[[item.ID]]">
		[[ partial('backend/shipment/itemAmount.tpl', ['item': item]) ]]
	</div>
{/foreach}