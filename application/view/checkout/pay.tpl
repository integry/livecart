<div class="checkoutPay">

{loadJs form=true}
{include file="checkout/layout.tpl"}

<div id="content" class="left right">

	<div class="checkoutHeader">
		<h1>{t _pay}</h1>

		{include file="checkout/checkoutProgress.tpl" progress="progressPayment"}
	</div>

	<div id="payTotal">
		<div>
			{t _order_total}: <span class="subTotal">{$order.formattedTotal.$currency}</span>
		</div>
	</div>

	{include file="checkout/paymentMethods.tpl"}

	{if 'OFFLINE_PAYMENT'|config}
		<h2>{t _pay_offline}</h2>

		{include file="checkout/offlinePaymentInfo.tpl"}

		<form action="{link controller=checkout action=payOffline}" method="POST">
			<input type="submit" value="{tn _offline_complete_payment}" />
		</form>
	{/if}

	<h2>{t _order_overview}</h2>

	{include file="checkout/orderOverview.tpl"}

	{defun name="address"}
		{if $address}
			<p>
				{$address.fullName}
			</p>
			<p>
				{$address.companyName}
			</p>
			<p>
				{$address.address1}
			</p>
			<p>
				{$address.address2}
			</p>
			<p>
				{$address.city}
			</p>
			<p>
				{if $address.stateName}{$address.stateName}, {/if}{$address.postalCode}
			</p>
			<p>
				{$address.countryName}
			</p>
		{/if}
	{/defun}

	<div id="overviewAddresses">

		{if $order.ShippingAddress}
		<div class="addressContainer">
			<h3>{t _will_ship_to}:</h3>
			{fun name="address" address=$order.ShippingAddress}
			<a href="{link controller=checkout action=selectAddress}">{t _change}</a>
		</div>
		{/if}

		<div class="addressContainer">
			<h3>{t _will_bill_to}:</h3>
			{fun name="address" address=$order.BillingAddress}
			<a href="{link controller=checkout action=selectAddress}">{t _change}</a>
		</div>

		<div class="clear"></div>

	</div>

</div>

{include file="layout/frontend/footer.tpl"}

</div>