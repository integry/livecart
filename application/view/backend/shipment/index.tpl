<fieldset {denied role='order.update'}style="display: none"{/denied}>
	<ul class="menu" id="orderShipments_menu_[[orderID]]">
		<li class="order_addProduct" id="order[[orderID]]_addProduct_li">
		   <span {denied role='order.update'}style="display: none"{/denied}>
			   <a href="#newProduct" id="order[[orderID]]_addProduct">{t _add_new_product}</a>
		   </span>
		</li>
		<li class="order_addShipment"  id="order[[orderID]]_addShipment_li">
			<span id="orderShipments_new_[[orderID]]_indicator" class="progressIndicator" style="display: none"> </span>
			<a href="#new" id="orderShipments_new_[[orderID]]_show">{t _add_new_shipment}</a>
		</li>
		<li class="controls" id="orderShipments_new_[[orderID]]_controls" style="display:none; padding: 0; margin: 0;">
			<fieldset class="controls">
				{t _do_you_want_to_create_new_shipment}
				<input type="submit" value="{t _yes}" class="submit" id="orderShipments_new_[[orderID]]_submit">
				{t _or} <a href="#new" id="orderShipments_new_[[orderID]]_cancel">{t _no}</a>
			</fieldset>
		</li>
	</ul>
</fieldset>

<fieldset id="orderShipments_new_[[orderID]]_form" style="display: none;"> </fieldset>
<div id="orderShipment_[[orderID]]_controls_empty" style="display: none">[[ partial("backend/shipment/shipmentControls.tpl") ]]</div>
<div id="orderShipment_[[orderID]]_total_empty" style="display: none">[[ partial("backend/shipment/shipmentTotal.tpl") ]]</div>
<div id="orderShipmentItem_[[orderID]]_empty" style="display: none">[[ partial("backend/shipment/itemAmount.tpl") ]]</div>

<fieldset id="orderShipment_report_[[orderID]]" class="orderShipment_report">
	<legend>{t _report}</legend>
	<table class="orderShipment_report_values">
		<tr>
			<td class="orderShipment_report_description">{t _subtotal_price}</td>
			<td class="orderShipment_report_subtotal orderShipment_report_value">
				<span class="pricePrefix">[[order.Currency.pricePrefix]]</span>
				<span class="price">{$subtotalAmount|string_format:"%.2f"}</span>
				<span class="priceSuffix">[[order.Currency.priceSuffix]]</span>
			</td>
		</tr>
		<tr>
			<td class="orderShipment_report_description">{t _shipping_price}</td>
			<td class="orderShipment_report_shippingAmount orderShipment_report_value">
				<span class="pricePrefix">[[order.Currency.pricePrefix]]</span>
				<span class="price">{$shippingAmount|string_format:"%.2f"}</span>
				<span class="priceSuffix">[[order.Currency.priceSuffix]]</span>
			</td>
		</tr>

		{% if $order.discounts %}
			<tr>
				<td class="orderShipment_report_description">{t _discounts}</td>
				<td class="orderShipment_report_discounts orderShipment_report_value">
					<span class="pricePrefix">[[order.Currency.pricePrefix]]</span>
					<span class="price">{$order.discountAmount|string_format:"%.2f"}</span> <span class="priceSuffix">[[order.Currency.priceSuffix]]</span>
				</td>
			</tr>
		{% endif %}

		<tr>
			<td class="orderShipment_report_description">{t _taxes}</td>
			<td class="orderShipment_report_tax orderShipment_report_value">
				<span class="pricePrefix">[[order.Currency.pricePrefix]]</span>
				<span class="price">{$taxAmount|string_format:"%.2f"}</span> <span class="priceSuffix">[[order.Currency.priceSuffix]]</span>
			</td>
		</tr>

		<tr class="orderShipment_report_total">
			<td class="orderShipment_report_description">{t _total_price}</td>
			<td class="orderShipment_report_total orderShipment_report_value">
				<span class="pricePrefix">[[order.Currency.pricePrefix]]</span>
				<span class="price">{$order.totalAmount|string_format:"%.2f"}</span>
				<span class="priceSuffix">[[order.Currency.priceSuffix]]</span>
			</td>
		</tr>
	</table>
</fieldset>

{% if $order.discounts || $order.coupons %}
	<fieldset class="discounts">
		<legend>{t _discounts}</legend>

		{% if $order.coupons %}
			<div class="appliedCoupons">
				{t _coupons}:
				{foreach from=$order.coupons item=coupon name=coupons}
					<strong>[[coupon.couponCode]]</strong>{% if !$smarty.foreach.coupons.last %}, {% endif %}
				{/foreach}
			</div>
		{% endif %}

		<table class="discounts">
			{foreach from=$order.discounts item=discount name=discounts}
				<tr>
					<td>[[discount.description]]</td>
					<td class="amount">[[discount.formatted_amount]]</td>
				</tr>
			{/foreach}
		</table>
	</fieldset>
{% endif %}

<div id="order[[orderID]]_downloadableShipments" class="downloadableShipments shipmentCategoty" style="display: none">
	<h2 class="notShippedShipmentsTitle">{t _downloadable}</h2>
	<div id="orderShipments_list_[[orderID]]_downloadable" class="downloadableShipment"  {denied role='order.update'}style="display: none"{/denied}>
		<ul id="orderShipmentsItems_list_[[orderID]]_downloadable" class="activeList_add_delete orderShipmentsItem activeList singleShipment">
			<li id="orderShipments_list_[[orderID]]_[[downloadableShipment.ID]]" class="orderShipment" >
				[[ partial('backend/shipment/shipment.tpl', ['shipment': downloadableShipment, 'notShippable': true, 'downloadable': 1]) ]]
				{% if $downloadableShipment.items|@count > 0 %}
					<script type="text/javascript">
						Element.show("order[[orderID]]_downloadableShipments");
					</script>
				{% endif %}
			</li>
		</ul>
	</div>
</div>

{* Not Shipped Shipments *}
<div id="order[[orderID]]_shippableShipments" class="shippableShipments shipmentCategoty" style="display: none">
	<h2 class="notShippedShipmentsTitle">{t _not_shipped}</h2>
	{% set shippmentCount = 0 %}
	{foreach item="shipment" from=$shipments}
		{% if $shipment.status == 3 && $shipment.isShippable %}
			{assign var="shippmentCount" value=$shippmentCount+1}
		{% endif %}
	{/foreach}
	<ul id="orderShipments_list_[[orderID]]" class="orderShipments {% if $shippmentCount == 1 %}singleShipment{% endif %}">
		{foreach item="shipment" from=$shipments}
			{% if $shipment.status != 3 && $shipment.isShippable %}
				<li id="orderShipments_list_[[orderID]]_[[shipment.ID]]" class="orderShipment downloadableOrder">
					[[ partial("backend/shipment/shipment.tpl") ]]
					<script type="text/javascript">
						Element.show("order[[orderID]]_shippableShipments");
					</script>
				</li>
			{% endif %}
		{/foreach}
	</ul>
</div>


{* Shipped Shipments *}
<div id="order[[orderID]]_shippedShipments" class="shippedShipments shipmentCategoty" style="display: none">
	<h2 class="shippedShipmentsTitle">{t _shipped}</h2>
	{foreach item="shipment" from=$shipments}
		{% if $shipment.status == 3 && $shipment.isShippable %}
			{assign var="shippmentCount" value=$shippmentCount+1}
		{% endif %}
	{/foreach}
	<ul id="orderShipments_list_[[orderID]]_shipped" class="orderShippedShipments {% if $shippmentCount == 1 %}singleShipment{% endif %}">
		{foreach item="shipment" from=$shipments}
			{% if $shipment.status == 3 && $shipment.isShippable %}
				<li id="orderShipments_list_[[orderID]]_shipped_[[shipment.ID]]" class="orderShipment">
					[[ partial("backend/shipment/shipment.tpl") ]]
					<script type="text/javascript">Element.show("order[[orderID]]_shippedShipments");</script>
				</li>
			{% endif %}
		{/foreach}
	</ul>
</div>





<script type="text/javascript">
	Backend.OrderedItem.Links = {};
	Backend.OrderedItem.Links.remove = '{link controller="backend.orderedItem" action=delete}';
	Backend.OrderedItem.Links.changeShipment = '{link controller="backend.orderedItem" action=changeShipment}';
	Backend.OrderedItem.Links.addProduct = '{link controller="backend.orderedItem" action=selectProduct}/[[orderID]]';
	Backend.OrderedItem.Links.createNewItem = '{link controller="backend.orderedItem" action=create}';
	Backend.OrderedItem.Links.changeItemCount = '{link controller="backend.orderedItem" action=changeCount}';

	Backend.Shipment.Links = {};
	Backend.Shipment.Links.update = '{link controller="backend.shipment" action=update}';
	Backend.Shipment.Links.create = '{link controller="backend.shipment" action=create}';
	Backend.Shipment.Links.remove = '{link controller="backend.shipment" action=delete}';
	Backend.Shipment.Links.edit = '{link controller="backend.shipment" action=edit}';
	Backend.Shipment.Links.getAvailableServices = '{link controller="backend.shipment" action=getAvailableServices}';
	Backend.Shipment.Links.changeService = '{link controller="backend.shipment" action=changeService}';
	Backend.Shipment.Links.changeStatus = '{link controller="backend.shipment" action=changeStatus}';
	Backend.Shipment.Links.removeEmptyShipments = '{link controller="backend.customerOrder" action=removeEmptyShipments}';


	Backend.Shipment.Statuses = {json array=$statuses};

	Backend.Shipment.Messages = {};
	Backend.Shipment.Messages.areYouSureYouWantToDelete = '{t _are_you_sure_you_want_to_delete_group|addslashes}';
	Backend.Shipment.Messages.shippingServiceIsNotSelected = '{t _shipping_service_is_not_selected|addslashes}';
	Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToAwaiting = '{t _are_you_sure_you_want_to_change_shipment_status_to_awaiting|addslashes}';
	Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToPending = '{t _are_you_sure_you_want_to_change_shipment_status_to_pending|addslashes}';
	Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToNew = '{t _are_you_sure_you_want_to_change_shipment_status_to_new|addslashes}';
	Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToShipped = '{t _are_you_sure_you_want_to_change_shipment_status_to_shipped|addslashes}';
	Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToReturned = '{t _are_you_sure_you_want_to_change_shipment_status_to_returned|addslashes}';
	Backend.Shipment.Messages.youWontBeAableToRevertStatusFromShipped = '{t _you_wont_be_able_to_revert_status_from_shipped|addslashes}';
	Backend.Shipment.Messages.youWontBeAbleToUndelete = '{t _you_wont_be_able_to_undelete_this_shipment|addslashes}';
	Backend.Shipment.Messages.areYouSureYouWantToDeleteThisShipment = '{t _are_you_sure_you_want_to_delete_this_shipment|addslashes}';
	Backend.Shipment.Messages.emptyShipmentsWillBeRemoved = '{t _you_have_count_empty_shipments_do_you_want_to_proceed_to_the_next_page|addslashes}'
	Backend.Shipment.Messages.shipment = '{t _shipment}';

	Backend.OrderedItem.Messages = {};
	Backend.OrderedItem.Messages.areYouSureYouWantToDelete = '{t _are_you_sure_you_want_to_delete_this_item|addslashes}';
	Backend.OrderedItem.Messages.selectProductTitle = '{t _select_product|addslashes}';
	Backend.OrderedItem.Messages.areYouRealyWantToUpdateItemsCount = '{t _are_you_realy_want_to_update_items_count|addslashes}';

	Backend.Shipment.prototype.initializePage("[[orderID]]", "[[downloadableShipment.ID]]")

	ActiveList.prototype.getInstance("orderShipmentsItems_list_[[orderID]]_[[downloadableShipment.ID]]", Backend.OrderedItem.activeListCallbacks);
	var groupList = ActiveList.prototype.getInstance('orderShipments_list_[[orderID]]', Backend.Shipment.Callbacks);

	{foreach item="shipment" from=$shipments}
		{% if $shipment.isShippable %}
			var shipment = Backend.Shipment.prototype.getInstance('orderShipments_list_[[orderID]]{% if $shipment.isShipped %}_shipped{% endif %}_[[shipment.ID]]', {isShipped: {% if $shipment.isShipped %}true{% else %}false{% endif %}});
		{% else %}
			var shipment = Backend.Shipment.prototype.getInstance('orderShipments_list_[[orderID]]_[[shipment.ID]]');
		{% endif %}
	{/foreach}

	groupList.createSortable(true);
	</script>


