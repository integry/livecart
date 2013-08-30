{% set extraColspanSize = 0 %}
{% if 'SHOW_SKU_CART'|config %}
	{assign var="extraColspanSize" value=1+$extraColspanSize}
{% endif %}

{form action="order/update" method="POST" enctype="multipart/form-data" handle=$form id="cartItems"}

{% if $cart.wishListItems %}
	<h2>{t _cart_items}</h2>
{% endif %}

<table id="cart" class="table table-striped">
	<thead>
		<tr>
			{section name="colspan" start=0 loop=$extraColspanSize+3}
				<th class="cartListTitle"></th>
			{/section}
			<th class="cartPrice">{t _price}</th>
			<th class="cartQuant">{t _quantity}</th>
		</tr>
	</thead>
	
	{% if !$hideNav %}
	<tfoot>
		[[ partial("order/block/navigation.tpl") ]]
	</tfoot>
	{% endif %}
	
	<tbody>
		[[ partial("order/block/items.tpl") ]]
		[[ partial("order/block/discounts.tpl") ]]
		[[ partial("order/block/shipping.tpl") ]]

		{% if !'HIDE_TAXES'|config %}
			[[ partial("order/block/taxes.tpl") ]]
		{% endif %}

		[[ partial("order/block/total.tpl") ]]
		[[ partial("order/block/customFields.tpl") ]]
		[[ partial("order/block/shippingEstimation.tpl") ]]
		[[ partial("order/block/coupons.tpl") ]]
	</tbody>
</table>
<input type="hidden" name="return" value="[[return]]" />

[[ partial("order/block/expressCheckout.tpl") ]]

<input type="hidden" value="" name="recurringBillingPlan" id="recurringBillingPlan">

{/form}
