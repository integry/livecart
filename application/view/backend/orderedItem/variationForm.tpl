{form action="backend.orderedItem/saveVariations" method="POST" handle=form onsubmit="Backend.OrderedItem.saveOptions(event)"}

<div class="optionFormContainer" id="variationContainer_[[item.ID]]">
	<fieldset>

		<legend>{t _edit_variations}</legend>

		[[ partial('product/variations.tpl', ['variations': variations, 'container': "variationContainer_`item.ID`"]) ]]

		<input type="hidden" name="id" value="[[item.ID]]" />
		<input type="hidden" name="orderID" value="[[item.CustomerOrder.ID]]" />
		<input type="hidden" name="shipmentID" value="[[item.Shipment.ID]]" />

		<fieldset class="controls">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" class="submit" value="{t _save}" /> {t _or}
			<a href="[[ url("backend.orderedItem/item/" ~ item.ID) ]]" onclick="Backend.OrderedItem.reloadItem(event)" class="cancel">{t _cancel}</a>
		</fieldset>

	</fieldset>

</div>

{/form}