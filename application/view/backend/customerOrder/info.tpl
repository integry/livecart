<ul class="menu">
	<li>
		{form handle=$form class="orderStatus" action="backend.customerOrder/update" id="orderInfo_`$order.ID`_form" onsubmit="Backend.CustomerOrder.Editor.prototype.getInstance(`$order.ID`, false).submitForm(); return false;" method="post" role="order.update"}
			{hidden name="ID"}
			{hidden name="isCancelled"}
				<label for="order_[[order.ID]]_status" style="width: auto; float: none;">{t _status}: </label>
				{selectfield options=$statuses id="order_`$order.ID`_status" name="status" class="status"}
				<div class="errorText hidden"></div>
		{/form}
		<div class="order_acceptanceStatus" >
			{t _this_order_is}
			<span class="order_acceptanceStatusValue" id="order_acceptanceStatusValue_[[order.ID]]" style="color: {% if $order.isCancelled %}red{% else %}green{% endif %}">
				{% if $order.isCancelled %}{t _canceled}{% else %}{t _accepted}{% endif %}
			</span>
		</div>
	</li>
	{% if !$order.isFinalized %}
	<li {denied role='order.update'}style="display: none"{/denied} class="order_unfinalized">
		<span style="display: none;" id="order_[[order.ID]]_isFinalizedIndicator" class="progressIndicator"></span>
		<a id="order_[[order.ID]]_isFinalized" href="{link controller="backend.customerOrder" action="finalize" id=$order.ID}">
			{t _finalize}
		</a>
	</li>
	{% endif %}
	<li class="order_printInvoice">
		<a href="{link controller="backend.customerOrder" action=printInvoice id=$order.ID}" target="_blank">{t _print_invoice}</a>
	</li>

	<li class="order_printLabel">
		<a href="{link controller="backend.customerOrder" action=printLabels id=$order.ID}" target="_blank">{t _print_shipping_labels}</a>
	</li>

	<li {denied role='order.update'}style="display: none"{/denied}
		class="{% if $order.isCancelled %}order_accept{% else %}order_cancel{% endif %}">
		<span style="display: none;" id="order_[[order.ID]]_isCanceledIndicator" class="progressIndicator"></span>
		<a id="order_[[order.ID]]_isCanceled" href="{link controller="backend.customerOrder" action="setIsCanceled" id=$order.ID}">
			{% if $order.isCancelled %}{t _accept_order}{% else %}{t _cancel_order}{% endif %}
		</a>
	</li>

	<li {denied role='order.update'}style="display: none"{/denied} class="addCoupon">
		<span style="display: none;" id="order_[[order.ID]]_addCouponIndicator" class="progressIndicator"></span>
		<a id="order_[[order.ID]]_addCoupon" href="{link controller="backend.customerOrder" action="addCoupon" id=$order.ID}?coupon=_coupon_">{t _add_coupon}</a>
	</li>

	{% if $order.isFinalized %}
		<li {denied role='order.update'}style="display: none"{/denied} class="order_recalculateDiscounts">
			<a id="order_[[order.ID]]_recalculateDiscounts" href="{link controller="backend.customerOrder" action="recalculateDiscounts" id=$order.ID}">
				{t _recalculate_discounts}
			</a>
		</li>
	{% endif %}

</ul>
<div class="clear"></div>


<div class="addressContainer">
	{% if $formShippingAddress || !$formBillingAddress %}
		{form handle=$formShippingAddress action="backend.customerOrder/updateAddress" id="orderInfo_`$order.ID`_shippingAddress_form" onsubmit="Backend.CustomerOrder.Address.prototype.getInstance(this, false).submitForm(); return false;" method="post" role="order.update"}
			<fieldset class="order_shippingAddress">
				<legend>{t _shipping_address}</legend>
				[[ partial('backend/customerOrder/address.tpl', ['type': "order_`$order.ID`_shippingAddress", 'address': order.ShippingAddress, 'states': shippingStates, 'order': order]) ]]
			</fieldset>
		{/form}
	{% endif %}
	{% if $formBillingAddress || !$formShippingAddress %}
		{form handle=$formBillingAddress action="backend.customerOrder/updateAddress" id="orderInfo_`$order.ID`_billingAddress_form" onsubmit="Backend.CustomerOrder.Address.prototype.getInstance(this, false).submitForm(); return false;" method="post" role="order.update"}
			<fieldset class="order_billingAddress">
				<legend>{t _billing_address}</legend>
				[[ partial('backend/customerOrder/address.tpl', ['type': "order_`$order.ID`_billingAddress", 'address': order.BillingAddress, 'states': billingStates, 'order': order]) ]]
			</fieldset>
		{/form}
	{% endif %}
</div>

<fieldset class="order_info">
	<div class="clearfix invoiceNumber">
		<label class="param">{t _order_id}</label>
		<label class="value" id="invoiceNumber[[order.ID]]">{$order.invoiceNumber|default:$order.ID}</label>
	</div>

	{% if $order.User %}
	<div class="clearfix">
		<label class="param">{t _user}</label>
		<label class="value">
			<a href="{backendUserUrl user=$order.User}">
				[[order.User.fullName]]
			</a>
		</label>
	</div>
	{% endif %}

	<div class="clearfix orderAmount {% if !$order.isPaid %}unpaid{% endif %}">
		<label class="param">{t _amount}</label>
		<label class="value">
			[[order.Currency.pricePrefix]]<span class="order_totalAmount">{$order.totalAmount|default:0|string_format:"%.2f"}</span>[[order.Currency.priceSuffix]]
		</label>
		<span class="notPaid">
			<input type="checkbox" class="checkbox" id="{uniqid}" value="1" onchange="Backend.CustomerOrder.prototype.changePaidStatus(this, '{link controller="backend.payment" action=changeOrderPaidStatus id=$order.ID query='status=_stat_'}');">
			<label for="{uniqid last=true}" class="checkbox">{t _mark_as_paid}</label>
		</span>
	</div>

	{% if $order.dateCompleted %}
		<div class="clearfix">
			<label class="param" for="order_[[order.ID]]_dateCreated">{t _date_created}</label>
			<label id="dateCreatedLabel">
				<a class="menu order_editFields orderDate" href="#edit" id="editDateCompleted"></a>
				<span id="dateCreatedVisible">[[order.dateCompleted]]</span>
			</label>

			{form id="calendarform" handle=$dateForm class="hidden" action="backend.customerOrder/updateDate" method="POST"}
				{calendar name="dateCompleted" id="dateCompleted"}

				<span class="progressIndicator" id="indicatorDateCompleted" style="display: none;"></span>

				<span class="menu">
					<a href="#save" id="saveDateCompleted">{t _save}</a>
					<a href="#cancel" id="cancelDateCompleted">{t _cancel}</a>
				</span>
			{/form}
		</div>
	{% endif %}

	{% if 'ENABLE_MULTIADDRESS'|@config %}
	<div class="clearfix">
		<label class="param" for="order_[[order.ID]])_isMultiAddress">{t CustomerOrder.isMultiAddress}</label>
		<select style="width: auto; float: left;" onchange="Backend.CustomerOrder.prototype.setMultiAddress(this, '{link controller="backend.customerOrder" action=setMultiAddress id=$order.ID query='status=_stat_'}', [[order.ID]]);"><option value=0>{t _no}</option><option value=1{% if $order.isMultiAddress %} selected="selected"{% endif %}>{t _yes}</option></select>
		<span class="progressIndicator" style="display: none; float: left; padding-top: 0; padding-left: 0;"></span>
	</div>
	{% endif %}

	{% if $order.isRecurring %}
		<div class="clearfix">
			<label class="param">{t _recurring_status}:</label>
			<label class="value" id="recurringStatus[[order.ID]]">
				{% if $order.rebillsLeft > 0 %}
					{t _recurring_status_active}
				{% else %}
					{t _recurring_status_expired}
				{% endif %}
			</label>
		</div>

		<div class="clearfix">
			<label class="param">{t _remaining_rebills}:</label>
			<label class="value" id="remainingRebillsValue[[order.ID]]">
				{% if is_numeric($order.rebillsLeft) %}
					[[order.rebillsLeft]]
				{% else %}
					0
				{% endif %}
			</label>

			<span class="stopRebillsLinkContainer" style="{% if $order.rebillsLeft == 0 %}display:none;{% endif %}">
				<span class="progressIndicator" style="display:none;"></span>
				<a href="#" id="stopRebills[[order.ID]]">{t _cancel_subscription}</a>
				<input type="hidden" id="cancelSubscriptionURL[[order.ID]]" value="{link controller="backend.CustomerOrder" action=cancelSubscription id=$order.ID}" />
				<input type="hidden" id="stopRebillsURL[[order.ID]]" value="{link controller="backend.CustomerOrder" action=stopRebills id=$order.ID}" />
			</span>
		</div>
	{% endif %}
</fieldset>

<br class="clear" />

{% if !empty(specFieldList) %}
<div class="customFields">
	[[ partial("backend/customerOrder/saveFields.tpl") ]]
</div>
{% endif %}


{* count how many unshipped shipments *}
{% set shipmentCount = 0 %}
{foreach item="shipment" from=$shipments}
	{% if $shipment.status != 3 && $shipment.isShippable %}
		{assign var="shipmentCount" value=$shipmentCount+1}
	{% endif %}
{/foreach}

<fieldset {denied role='order.update'}style="display: none"{/denied}>
	<ul class="menu" id="orderShipments_menu_[[orderID]]">
		<li class="order_addProduct" id="order[[orderID]]_addProduct_li">
			<span {denied role='order.update'}style="display: none"{/denied}>
				<a href="#newProduct" id="order[[orderID]]_openProductMiniform>{t _add_new_product}</a>
			</span>
		</li>
		<li class="order_addShipment" id="order[[orderID]]_addShipment_li">
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

<fieldset class="addProductsContainer" style="display:none;" id="order[[orderID]]_productMiniform>
	<legend>{t _add_new_product} <a class="cancel" href="#" id="order[[orderID]]_cancelProductMiniform>{t _cancel}</a></legend>
	<ul class="menu" id="orderShipments_menu_[[orderID]]">
		<li class="addProductAdvanced">
			<span {denied role='order.update'}style="display: none"{/denied}>
				<a href="#newProduct" id="order[[orderID]]_addProduct" class="cancel">{t _advanced_product_search}</a>
			</span>
		</li>
	</ul>

	<label for="ProductSearchQuery">{t _search_product}:</label>
	{include
		file="backend/quickSearch/form.tpl"
		formid="ProductSearch"
		classNames="Product"
		resultTemplates="Product:ProductAddToShippment"
	}
	<div class="controls" id="miniformControls[[orderID]]" style="display:none;">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" name="save" class="submit" value="{t _add_to_order}" id="order[[orderID]]_addSearchResultToOrder" />
		{t _or}
		<a class="cancel" href="#cancel" id="order[[orderID]]_cancelProductMiniform2">{t _cancel}</a>
	</div>
	<div class="clear" />
	<div class="tip">{t _search_product_tip1}<br />{t _search_product_tip2}</div>

	<div class="{% if $shipmentCount <= 1 %}singleShipment{% endif %}">
		<label>{t _add_to_shipment}:</label>
		<select id="order[[orderID]]_addToShipment" class="addToShipment">
			{foreach item="shipment" from=$shipments}
				{% if $shipment.status != 3 && $shipment.isShippable %}
					<option value="[[shipment.ID]]">{t _shipment} #[[shipment.ID]]</option>
				{% endif %}
			{/foreach}
		</select>
	</div>

	<div class="hidden" id="order[[orderID]]_cannotAddEmptyResult">{t _cannot_add_empty_result}</div>
	<div class="hidden" id="order[[orderID]]_addAllFoundProducts">{t _add_all_found_products}</div>
</fieldset>

<fieldset id="orderShipments_new_[[orderID]]_form style="display: none;"> </fieldset>
<div id="orderShipment_[[orderID]]_controls_empty" style="display: none">[[ partial('backend/shipment/itemAmount.tpl', ['shipment': null]) ]]</div>
<div id="orderShipment_[[orderID]]_total_empty" style="display: none">[[ partial('backend/shipment/itemAmount.tpl', ['shipment': null]) ]]</div>
<div id="orderShipmentItem_[[orderID]]_empty" style="display: none">[[ partial('backend/shipment/itemAmount.tpl', ['shipment': null]) ]]</div>


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

<div id="order[[orderID]]_downloadableShipments" class="downloadableShipments shipmentCategoty" style="display: none;">
	<h2 class="notShippedShipmentsTitle">{t _downloadable}</h2>
	<div id="orderShipments_list_[[orderID]]_downloadable" class="downloadableShipment"  {denied role='order.update'}style="display: none"{/denied}>
		<ul id="orderShipmentsItems_list_[[orderID]]_downloadable" class="activeList_add_delete orderShipmentsItem activeList">
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
	<ul id="orderShipments_list_[[orderID]]" class="orderShipments {% if $shipmentCount <= 1 %}singleShipment{% endif %}">
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
	{% set shipmentCount = 0 %}
	{foreach item="shipment" from=$shipments}
		{% if $shipment.status == 3 && $shipment.isShippable %}
			{assign var="shipmentCount" value=$shipmentCount+1}
		{% endif %}
	{/foreach}
	<ul id="orderShipments_list_[[orderID]]_shipped" class="orderShippedShipments {% if $shipmentCount <= 1 %}singleShipment{% endif %}">
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

<div class="hidden" style="display:none;" id="order[[orderID]]_tmpContainer"></div>


{literal}
<script type="text/javascript">
	Backend.OrderedItem.Links = {};
	Backend.OrderedItem.Links.remove = '{/literal}{link controller="backend.orderedItem" action=delete}{literal}';
	Backend.OrderedItem.Links.changeShipment = '{/literal}{link controller="backend.orderedItem" action=changeShipment}{literal}';
	Backend.OrderedItem.Links.addProduct = '{/literal}{link controller="backend.orderedItem" action=selectProduct}/[[orderID]]{literal}';
	Backend.OrderedItem.Links.createNewItem = '{/literal}{link controller="backend.orderedItem" action=create}{literal}';
	// Backend.OrderedItem.Links.createFromSearchQuery = '{/literal}{link controller="backend.orderedItem" action=createFromSearchQuery}{literal}';
	Backend.OrderedItem.Links.changeItemCount = '{/literal}{link controller="backend.orderedItem" action=changeCount}{literal}';

	Backend.Shipment.Links = {};
	Backend.Shipment.Links.update = '{/literal}{link controller="backend.shipment" action=update}{literal}';
	Backend.Shipment.Links.create = '{/literal}{link controller="backend.shipment" action=create}{literal}';
	Backend.Shipment.Links.remove = '{/literal}{link controller="backend.shipment" action=delete}{literal}';
	Backend.Shipment.Links.edit = '{/literal}{link controller="backend.shipment" action=edit}{literal}';
	Backend.Shipment.Links.getAvailableServices = '{/literal}{link controller="backend.shipment" action=getAvailableServices}{literal}';
	Backend.Shipment.Links.changeService = '{/literal}{link controller="backend.shipment" action=changeService}{literal}';
	Backend.Shipment.Links.changeStatus = '{/literal}{link controller="backend.shipment" action=changeStatus}{literal}';
	Backend.Shipment.Links.removeEmptyShipments = '{/literal}{link controller="backend.customerOrder" action=removeEmptyShipments}{literal}';


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
	Backend.Shipment.Messages.addCouponCode = '{/literal}{t _add_coupon_code}{literal}';

	Backend.OrderedItem.Messages = {};
	Backend.OrderedItem.Messages.areYouSureYouWantToDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_item|addslashes}{literal}';
	Backend.OrderedItem.Messages.selectProductTitle = '{/literal}{t _select_product|addslashes}{literal}';
	Backend.OrderedItem.Messages.areYouRealyWantToUpdateItemsCount = '{/literal}{t _are_you_realy_want_to_update_items_count|addslashes}{literal}';

	Backend.Shipment.prototype.initializePage("{/literal}[[orderID]]{literal}", "{/literal}[[downloadableShipment.ID]]{literal}")
	ActiveList.prototype.getInstance("{/literal}orderShipmentsItems_list_[[orderID]]_[[downloadableShipment.ID]]{literal}", Backend.OrderedItem.activeListCallbacks);
	var groupList = ActiveList.prototype.getInstance('{/literal}orderShipments_list_[[orderID]]{literal}', Backend.Shipment.Callbacks);

	{/literal}{foreach item="shipment" from=$shipments}{literal}
		{/literal}{% if $shipment.isShippable %}{literal}
			var shipment = Backend.Shipment.prototype.getInstance('{/literal}orderShipments_list_[[orderID]]{% if $shipment.isShipped %}_shipped{% endif %}_[[shipment.ID]]{literal}', {isShipped: {/literal}{% if $shipment.isShipped %}true{% else %}false{% endif %}{literal}});
		{/literal}{% else %}{literal}
			var shipment = Backend.Shipment.prototype.getInstance('{/literal}orderShipments_list_[[orderID]]_[[shipment.ID]]{literal}');
		{/literal}{% endif %}{literal}
	{/literal}{/foreach}{literal}

	groupList.createSortable(true);
	</script>
{/literal}

<script type="text/javascript">
	Backend.CustomerOrder.prototype.treeBrowser.selectItem({$type|default:0}, false);

	Backend.CustomerOrder.Editor.prototype.existingUserAddresses = {json array=$existingUserAddresses}
	{literal}
	var status = Backend.CustomerOrder.Editor.prototype.getInstance({/literal}[[order.ID]], true, {json array=$hideShipped}, [[order.isCancelled]], [[order.isFinalized]], {json array=$order.invoiceNumber}{literal});

	{/literal}{% if !empty(formShippingAddress) %}{literal}
		var shippingAddress = Backend.CustomerOrder.Address.prototype.getInstance($('{/literal}orderInfo_[[order.ID]]_shippingAddress_form{literal}'), 'shippingAddress');
	{/literal}{% endif %}{literal}

	{/literal}{% if !empty(formBillingAddress) %}{literal}
		var billingAddress = Backend.CustomerOrder.Address.prototype.getInstance($('{/literal}orderInfo_[[order.ID]]_billingAddress_form{literal}'), 'billingAddress');
	{/literal}{% endif %}

	{% if $order.dateCompleted %}
		var dateComplededEditor = new Backend.CustomerOrder.DateCompletedEditor();
	{% endif %}
	status.toggleInvoicesTab({% if $order.isRecurring %}1{% else %}0{% endif %});
</script>