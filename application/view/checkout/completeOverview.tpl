<div class="completeOverview">
<h2>{t _order_overview}</h2>

[[ partial("checkout/orderOverview.tpl") ]]

{function name="address"}
	{% if !empty(address) %}
		<p>
			[[address.fullName]]
		</p>
		<p>
			[[address.companyName]]
		</p>
		<p>
			[[address.address1]]
		</p>
		<p>
			[[address.address2]]
		</p>
		<p>
			[[address.city]]
		</p>
		<p>
			{% if $address.stateName %}[[address.stateName]], {% endif %}[[address.postalCode]]
		</p>
		<p>
			[[address.countryName]]
		</p>
		<p>
			[[ partial('order/addressFieldValues.tpl', ['showLabels': true]) ]]
		</p>
	{% endif %}
{/function}

{% if empty(hideAddress) %}
<div id="overviewAddresses">
	{% if $order.ShippingAddress && !$order.isMultiAddress %}
		<div class="addressContainer">
			<h3>{t _will_ship_to}:</h3>


            {% if $order.isLocalPickup %}
                {foreach $order.shipments as $shipment}
                    <div class="ShippingServiceDescription">
                        {$shipment.ShippingService.description_lang|escape}
                    </div>
                {/foreach}
            {% else %}
                {address address=$order.ShippingAddress}
            {% endif %}
			{% if empty(nochanges) %}
				<a href="[[ url("checkout/selectAddress") ]]">{t _change}</a>
			{% endif %}
		</div>
	{% endif %}

	{% if $order.BillingAddress && !'REQUIRE_SAME_ADDRESS'|config && ($order.ShippingAddress.compact != $order.BillingAddress.compact) %}
	<div class="addressContainer">
		<h3>{t _will_bill_to}:</h3>
		{address address=$order.BillingAddress}
		{% if empty(nochanges) %}
			<a href="[[ url("checkout/selectAddress") ]]">{t _change}</a>
		{% endif %}
	</div>
	{% endif %}

	<div class="clear"></div>
</div>
{% endif %}

</div>