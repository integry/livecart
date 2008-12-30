<fieldset class="order_info">
	<legend>{t _overview}</legend>

	<ul class="menu">
		<li class="order_printInvoice">
			<a href="{link controller=backend.customerOrder action=printInvoice id=$order.ID}" target="_blank">{t _print_invoice}</a>
		</li>
	</ul>
	<div class="clear"></div>

	<p>
		<label>{t _order_id}</label>
		<label>{$order.ID}</label>
	</p>

	{if $order.User}
	<p>
		<label>{t _user}</label>
		<label>
			<a href="{backendUserUrl user=$order.User}">
				{$order.User.fullName}
			</a>
		</label>
	</p>
	{/if}

	<p>
		<label>{t _amount}</label>
		<label>
			{*
			{$order.Currency.pricePrefix}<span class="order_capturedAmount">{$order.capturedAmount|default:0}</span>{$order.Currency.priceSuffix}

			/
			*}

			{$order.Currency.pricePrefix}<span class="order_totalAmount">{$order.totalAmount|default:0|string_format:"%.2f"}</span>{$order.Currency.priceSuffix}
		</label>
	</p>

	{if $order.dateCompleted}
	<p>
		<label for="order_{$order.ID}_dateCreated">{t _date_created}</label>
		<label>{$order.dateCompleted}</label>
	</p>
	{/if}

	<p>
		<label for="order_{$order.ID})_isPaid">{t _is_paid}</label>
		<select style="width: auto; float: left;" onchange="Backend.CustomerOrder.prototype.changePaidStatus(this, '{link controller=backend.payment action=changeOrderPaidStatus id=$order.ID query='status=_stat_'}');"><option value=0>{t _no}</option><option value=1{if $order.isPaid} selected="selected"{/if}>{t _yes}</option></select>
		<span class="progressIndicator" style="display: none; float: left; padding-top: 0; padding-left: 0;"></span>
	</p>
</fieldset>

<fieldset class="order_status">
	<legend>{t _order_status}</legend>

	<ul class="menu orderMenu">
		<li {denied role='order.update'}style="display: none"{/denied}
			class="{if $order.isCancelled}order_accept{else}order_cancel{/if}">
			<span style="display: none;" id="order_{$order.ID}_isCanceledIndicator" class="progressIndicator"></span>
			<a id="order_{$order.ID}_isCanceled" href="{link controller="backend.customerOrder" action="setIsCanceled" id=$order.ID}">
				{if $order.isCancelled}{t _accept_order}{else}{t _cancel_order}{/if}
			</a>
		</li>
	</ul>
	<div class="clear"></div>

	{form handle=$form class="orderStatus" action="controller=backend.customerOrder action=update" id="orderInfo_`$order.ID`_form" onsubmit="Backend.CustomerOrder.Editor.prototype.getInstance(`$order.ID`, false).submitForm(); return false;" method="post" role="order.update"}
		{hidden name="ID"}
		{hidden name="isCancelled"}
		<fieldset class="error" style="text-align: center;">
			<label for="order_{$order.ID}_status" style="width: auto; float: none;">{t _status}: </label>
			{selectfield options=$statuses id="order_`$order.ID`_status" name="status" class="status"}
			{img src="image/indicator.gif" id="order_`$order.ID`_status_feedback" style="display: none;"}
			<div class="errorText hidden"></div>
		</fieldset>
	{/form}

	<div class="order_acceptanceStatus" >
		{t _this_order_is}
		<span class="order_acceptanceStatusValue" id="order_acceptanceStatusValue_{$order.ID}" style="color: {if $order.isCancelled}red{else}green{/if}">
			{if $order.isCancelled}{t _canceled}{else}{t _accepted}{/if}
		</span>
	</div>

</fieldset>

<br class="clear" />

{if $specFieldList}
<div class="customFields">
	{include file="backend/customerOrder/saveFields.tpl"}
</div>
{/if}

{if $formShippingAddress || !$formBillingAddress}
	{form handle=$formShippingAddress action="controller=backend.customerOrder action=updateAddress" id="orderInfo_`$order.ID`_shippingAddress_form" onsubmit="Backend.CustomerOrder.Address.prototype.getInstance(this, false).submitForm(); return false;" method="post" role="order.update"}
		<fieldset class="order_shippingAddress">
			<legend>{t _shipping_address}</legend>
			{include file=backend/customerOrder/address.tpl type="order_`$order.ID`_shippingAddress" address=$order.ShippingAddress states=$shippingStates order=$order}
		</fieldset>
	{/form}
{/if}


{if $formBillingAddress || !$formShippingAddress}
	{form handle=$formBillingAddress action="controller=backend.customerOrder action=updateAddress" id="orderInfo_`$order.ID`_billingAddress_form" onsubmit="Backend.CustomerOrder.Address.prototype.getInstance(this, false).submitForm(); return false;" method="post" role="order.update"}
		<fieldset class="order_billingAddress">
			<legend>{t _billing_address}</legend>
			{include file=backend/customerOrder/address.tpl type="order_`$order.ID`_billingAddress" address=$order.BillingAddress states=$billingStates order=$order}
		</fieldset>
	{/form}
{/if}



<script type="text/javascript">
	Backend.CustomerOrder.prototype.treeBrowser.selectItem({$type|default:0}, false);

	Backend.CustomerOrder.Editor.prototype.existingUserAddresses = {json array=$existingUserAddresses}
	{literal}
	var status = Backend.CustomerOrder.Editor.prototype.getInstance({/literal}{$order.ID}, true, {json array=$hideShipped}, {$order.isCancelled}, {$order.isFinalized}{literal});

	{/literal}{if $formShippingAddress}{literal}
		var shippingAddress = Backend.CustomerOrder.Address.prototype.getInstance($('{/literal}orderInfo_{$order.ID}_shippingAddress_form{literal}'), 'shippingAddress');
	{/literal}{/if}{literal}

	{/literal}{if $formBillingAddress}{literal}
		var billingAddress = Backend.CustomerOrder.Address.prototype.getInstance($('{/literal}orderInfo_{$order.ID}_billingAddress_form{literal}'), 'billingAddress');
	{/literal}{/if}

</script>