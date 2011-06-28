<fieldset class="container completeOverview">
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
		<p>
			{include file="order/addressFieldValues.tpl" showLabels=true}
		</p>
	{/if}
{/defun}

<div id="overviewAddresses">
	{if $order.ShippingAddress && !$order.isMultiAddress}
		<div class="addressContainer">
			<h3>{t _will_ship_to}:</h3>


            {if $order.isLocalPickup}
                {foreach $order.shipments as $shipment}
                    <div class="ShippingServiceDescription">
                        {$shipment.ShippingService.description_lang|escape}
                    </div>
                {/foreach}
            {else}
                {fun name="address" address=$order.ShippingAddress}
            {/if}
			{if !$nochanges}
				<a href="{link controller=checkout action=selectAddress}">{t _change}</a>
			{/if}
		</div>
	{/if}

	{if $order.BillingAddress && !'REQUIRE_SAME_ADDRESS'|config && ($order.ShippingAddress.compact != $order.BillingAddress.compact)}
	<div class="addressContainer">
		<h3>{t _will_bill_to}:</h3>
		{fun name="address" address=$order.BillingAddress}
		{if !$nochanges}
			<a href="{link controller=checkout action=selectAddress}">{t _change}</a>
		{/if}
	</div>
	{/if}

	<div class="clear"></div>
</div>

</fieldset>