{form action="controller=backend.orderedItem action=saveVariations" method="POST" handle=$form onsubmit="Backend.OrderedItem.saveOptions(event)"}

<div class="optionFormContainer" id="variationContainer_{$item.ID}">
	<fieldset>

		<legend>{t _edit_variations}</legend>

		{include file="product/variations.tpl" variations=$variations container="variationContainer_`$item.ID`"}

		<input type="hidden" name="id" value="{$item.ID}" />
		<input type="hidden" name="orderID" value="{$item.CustomerOrder.ID}" />
		<input type="hidden" name="shipmentID" value="{$item.Shipment.ID}" />

		<fieldset class="controls">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" class="submit" value="{tn _save}" /> {t _or}
			<a href="{link controller="backend.orderedItem" action=item id=$item.ID}" onclick="Backend.OrderedItem.reloadItem(event)" class="cancel">{t _cancel}</a>
		</fieldset>

	</fieldset>

</div>

{/form}