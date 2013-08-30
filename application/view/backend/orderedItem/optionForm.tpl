{form action="backend.orderedItem/saveOptions" method="post" handle=$form onsubmit="Backend.OrderedItem.saveOptions(event)"}

<div class="optionFormContainer">
	<fieldset>

		<legend>{t _edit_options}</legend>

		{foreach from=$options[$item.ID] item=option}
			[[ partial('backend/orderedItem/optionItem.tpl', ['selectedChoice': $item.options[$option.ID]]) ]]
		{/foreach}

		<input type="hidden" name="id" value="[[item.ID]]" />
		<input type="hidden" name="orderID" value="[[item.CustomerOrder.ID]]" />
		<input type="hidden" name="shipmentID" value="[[item.Shipment.ID]]" />

		<fieldset class="controls">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" class="submit" value="{tn _save}" /> {t _or}
			<a href="{link controller="backend.orderedItem" action=item id=$item.ID}" onclick="Backend.OrderedItem.reloadItem(event)" class="cancel">{t _cancel}</a>
		</fieldset>

	</fieldset>

</div>

{/form}