<fieldset class="container" {denied role='order.update'}style="display: none"{/denied}>
	<ul class="menu" id="orderShipments_menu_{$orderID}">
		<li class="order_addProduct" id="order{$orderID}_addProduct_li">
		   <span {denied role='order.update'}style="display: none"{/denied}>
			   <a href="#newProduct" id="order{$orderID}_addProduct">{t _add_new_product}</a>
		   </span>
		</li>
		<li class="order_addShipment"  id="order{$orderID}_addShipment_li">
			<span id="orderShipments_new_{$orderID}_indicator" class="progressIndicator" style="display: none"> </span>
			<a href="#new" id="orderShipments_new_{$orderID}_show">{t _add_new_shipment}</a>
		</li>
		<li class="controls" id="orderShipments_new_{$orderID}_controls" style="display:none; padding: 0; margin: 0;">
			<fieldset class="controls">
				{t _do_you_want_to_create_new_shipment}
				<input type="submit" value="{t _yes}" class="submit" id="orderShipments_new_{$orderID}_submit">
				{t _or} <a href="#new" id="orderShipments_new_{$orderID}_cancel">{t _no}</a>
			</fieldset>
		</li>
	</ul>
</fieldset>

<fieldset id="orderShipments_new_{$orderID}_form" style="display: none;"> </fieldset>
<div id="orderShipment_{$orderID}_controls_empty" style="display: none">{include file="backend/shipment/shipmentControls.tpl"}</div>
<div id="orderShipment_{$orderID}_total_empty" style="display: none">{include file="backend/shipment/shipmentTotal.tpl"}</div>
<div id="orderShipmentItem_{$orderID}_empty" style="display: none">{include file="backend/shipment/itemAmount.tpl"}</div>

<fieldset id="orderShipment_report_{$orderID}" class="orderShipment_report">
	<legend>{t _report}</legend>
	<table class="orderShipment_report_values">
		<tr>
			<td class="orderShipment_report_description">{t _subtotal_price}</td>
			<td class="orderShipment_report_subtotal orderShipment_report_value">
				<span class="pricePrefix">{$order.Currency.pricePrefix}</span>
				<span class="price">{$subtotalAmount|string_format:"%.2f"}</span>
				<span class="priceSuffix">{$order.Currency.priceSuffix}</span>
			</td>
		</tr>
		<tr>
			<td class="orderShipment_report_description">{t _shipping_price}</td>
			<td class="orderShipment_report_shippingAmount orderShipment_report_value">
				<span class="pricePrefix">{$order.Currency.pricePrefix}</span>
				<span class="price">{$shippingAmount|string_format:"%.2f"}</span>
				<span class="priceSuffix">{$order.Currency.priceSuffix}</span>
			</td>
		</tr>

		{if $order.discounts}
			<tr>
				<td class="orderShipment_report_description">{t _discounts}</td>
				<td class="orderShipment_report_discounts orderShipment_report_value">
					<span class="pricePrefix">{$order.Currency.pricePrefix}</span>
					<span class="price">{$order.discountAmount|string_format:"%.2f"}</span> <span class="priceSuffix">{$order.Currency.priceSuffix}</span>
				</td>
			</tr>
		{/if}

		<tr>
			<td class="orderShipment_report_description">{t _taxes}</td>
			<td class="orderShipment_report_tax orderShipment_report_value">
				<span class="pricePrefix">{$order.Currency.pricePrefix}</span>
				<span class="price">{$taxAmount|string_format:"%.2f"}</span> <span class="priceSuffix">{$order.Currency.priceSuffix}</span>
			</td>
		</tr>

		<tr class="orderShipment_report_total">
			<td class="orderShipment_report_description">{t _total_price}</td>
			<td class="orderShipment_report_total orderShipment_report_value">
				<span class="pricePrefix">{$order.Currency.pricePrefix}</span>
				<span class="price">{$order.totalAmount|string_format:"%.2f"}</span>
				<span class="priceSuffix">{$order.Currency.priceSuffix}</span>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset class="discounts">
	<legend>{t _discounts}</legend>
	<table class="discounts">
		{foreach from=$order.discounts item=discount name=discounts}
			<tr class="{zebra loop="discounts"}">
				<td>{$discount.description}</td>
				<td class="amount">{$discount.formatted_amount}</td>
			</tr>
		{/foreach}
	</table>
</fieldset>

<div id="order{$orderID}_downloadableShipments" class="downloadableShipments shipmentCategoty" style="display: none">
	<h2 class="notShippedShipmentsTitle">{t _downloadable}</h2>
	<div id="orderShipments_list_{$orderID}_downloadable" class="downloadableShipment"  {denied role='order.update'}style="display: none"{/denied}>
		<ul id="orderShipmentsItems_list_{$orderID}_downloadable" class="activeList_add_delete orderShipmentsItem activeList">
			<li id="orderShipments_list_{$orderID}_{$downloadableShipment.ID}" class="orderShipment" >
				{include file="backend/shipment/shipment.tpl" shipment=$downloadableShipment notShippable=true downloadable=1}

				{if $downloadableShipment.items|@count > 0}
					<script type="text/javascript">
						Element.show("order{$orderID}_downloadableShipments");
					</script>
				{/if}
			</li>
		</ul>
	</div>
</div>


{* Not Shipped Shipments *}
<div id="order{$orderID}_shippableShipments" class="shippableShipments shipmentCategoty" style="display: none">
	<h2 class="notShippedShipmentsTitle">{t _not_shipped}</h2>
	<ul id="orderShipments_list_{$orderID}" class="orderShipments">
		{foreach item="shipment" from=$shipments}
			{if $shipment.status != 3 && $shipment.isShippable}
				<li id="orderShipments_list_{$orderID}_{$shipment.ID}" class="orderShipment downloadableOrder">
					{include file="backend/shipment/shipment.tpl"}
					<script type="text/javascript">
						Element.show("order{$orderID}_shippableShipments");
					</script>
				</li>
			{/if}
		{/foreach}
	</ul>
</div>


{* Shipped Shipments *}
<div id="order{$orderID}_shippedShipments" class="shippedShipments shipmentCategoty" style="display: none">
	<h2 class="shippedShipmentsTitle">{t _shipped}</h2>
	<ul id="orderShipments_list_{$orderID}_shipped" class="orderShippedShipments">
		{foreach item="shipment" from=$shipments}
			{if $shipment.status == 3 && $shipment.isShippable}
				<li id="orderShipments_list_{$orderID}_shipped_{$shipment.ID}" class="orderShipment">
					{include file="backend/shipment/shipment.tpl"}
					<script type="text/javascript">Element.show("order{$orderID}_shippedShipments");</script>
				</li>
			{/if}
		{/foreach}
	</ul>
</div>




{literal}
<script type="text/javascript">
	Backend.OrderedItem.Links = {};
	Backend.OrderedItem.Links.remove = '{/literal}{link controller=backend.orderedItem action=delete}{literal}';
	Backend.OrderedItem.Links.changeShipment = '{/literal}{link controller=backend.orderedItem action=changeShipment}{literal}';
	Backend.OrderedItem.Links.addProduct = '{/literal}{link controller=backend.orderedItem action=selectProduct}/{$orderID}{literal}';
	Backend.OrderedItem.Links.createNewItem = '{/literal}{link controller=backend.orderedItem action=create}{literal}';
	Backend.OrderedItem.Links.changeItemCount = '{/literal}{link controller=backend.orderedItem action=changeCount}{literal}';

	Backend.Shipment.Links = {};
	Backend.Shipment.Links.update = '{/literal}{link controller=backend.shipment action=update}{literal}';
	Backend.Shipment.Links.create = '{/literal}{link controller=backend.shipment action=create}{literal}';
	Backend.Shipment.Links.remove = '{/literal}{link controller=backend.shipment action=delete}{literal}';
	Backend.Shipment.Links.edit = '{/literal}{link controller=backend.shipment action=edit}{literal}';
	Backend.Shipment.Links.getAvailableServices = '{/literal}{link controller=backend.shipment action=getAvailableServices}{literal}';
	Backend.Shipment.Links.changeService = '{/literal}{link controller=backend.shipment action=changeService}{literal}';
	Backend.Shipment.Links.changeStatus = '{/literal}{link controller=backend.shipment action=changeStatus}{literal}';
	Backend.Shipment.Links.removeEmptyShipments = '{/literal}{link controller=backend.customerOrder action=removeEmptyShipments}{literal}';


	Backend.Shipment.Statuses = {/literal}{json array=$statuses}{literal};

	Backend.Shipment.Messages = {};
	Backend.Shipment.Messages.areYouSureYouWantToDelete = '{/literal}{t _are_you_sure_you_want_to_delete_group|addslashes}{literal}';
	Backend.Shipment.Messages.shippingServiceIsNotSelected = '{/literal}{t _shipping_service_is_not_selected|addslashes}{literal}';
	Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToAwaiting = '{/literal}{t _are_you_sure_you_want_to_change_shipment_status_to_awaiting|addslashes}{literal}';
	Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToPending = '{/literal}{t _are_you_sure_you_want_to_change_shipment_status_to_pending|addslashes}{literal}';
	Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToNew = '{/literal}{t _are_you_sure_you_want_to_change_shipment_status_to_new|addslashes}{literal}';
	Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToShipped = '{/literal}{t _are_you_sure_you_want_to_change_shipment_status_to_shipped|addslashes}{literal}';
	Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToReturned = '{/literal}{t _are_you_sure_you_want_to_change_shipment_status_to_returned|addslashes}{literal}';
	Backend.Shipment.Messages.youWontBeAableToRevertStatusFromShipped = '{/literal}{t _you_wont_be_able_to_revert_status_from_shipped|addslashes}{literal}';
	Backend.Shipment.Messages.youWontBeAbleToUndelete = '{/literal}{t _you_wont_be_able_to_undelete_this_shipment|addslashes}{literal}';
	Backend.Shipment.Messages.areYouSureYouWantToDeleteThisShipment = '{/literal}{t _are_you_sure_you_want_to_delete_this_shipment|addslashes}{literal}';
	Backend.Shipment.Messages.emptyShipmentsWillBeRemoved = '{/literal}{t _you_have_count_empty_shipments_do_you_want_to_proceed_to_the_next_page|addslashes}{literal}'
	Backend.Shipment.Messages.shipment = '{/literal}{t _shipment}{literal}';

	Backend.OrderedItem.Messages = {};
	Backend.OrderedItem.Messages.areYouSureYouWantToDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_item|addslashes}{literal}';
	Backend.OrderedItem.Messages.selectProductTitle = '{/literal}{t _select_product|addslashes}{literal}';
	Backend.OrderedItem.Messages.areYouRealyWantToUpdateItemsCount = '{/literal}{t _are_you_realy_want_to_update_items_count|addslashes}{literal}';

	Backend.Shipment.prototype.initializePage("{/literal}{$orderID}{literal}", "{/literal}{$downloadableShipment.ID}{literal}")

	ActiveList.prototype.getInstance("{/literal}orderShipmentsItems_list_{$orderID}_{$downloadableShipment.ID}{literal}", Backend.OrderedItem.activeListCallbacks);
	var groupList = ActiveList.prototype.getInstance('{/literal}orderShipments_list_{$orderID}{literal}', Backend.Shipment.Callbacks);

	{/literal}{foreach item="shipment" from=$shipments}{literal}
		{/literal}{if $shipment.isShippable}{literal}
			var shipment = Backend.Shipment.prototype.getInstance('{/literal}orderShipments_list_{$orderID}{if $shipment.isShipped}_shipped{/if}_{$shipment.ID}{literal}', {isShipped: {/literal}{if $shipment.isShipped}true{else}false{/if}{literal}});
		{/literal}{else}{literal}
			var shipment = Backend.Shipment.prototype.getInstance('{/literal}orderShipments_list_{$orderID}_{$shipment.ID}{literal}');
		{/literal}{/if}{literal}
	{/literal}{/foreach}{literal}

	groupList.createSortable(true);

	</script>
	{/literal}

