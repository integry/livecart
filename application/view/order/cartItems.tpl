{assign var="extraColspanSize" value=0}
{if 'SHOW_SKU_CART'|config}
	{assign var="extraColspanSize" value=1+$extraColspanSize}
{/if}

{form action="controller=order action=update" method="POST" enctype="multipart/form-data" handle=$form id="cartItems"}

{if $cart.wishListItems}
	<h2>{t _cart_items}</h2>
{/if}

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
	
	{if !$hideNav}
	<tfoot>
		{include file="order/block/navigation.tpl"}
	</tfoot>
	{/if}
	
	<tbody>
		{include file="order/block/items.tpl"}
		{include file="order/block/discounts.tpl"}
		{include file="order/block/shipping.tpl"}

		{if !'HIDE_TAXES'|config}
			{include file="order/block/taxes.tpl"}
		{/if}

		{include file="order/block/total.tpl"}
		{include file="order/block/customFields.tpl"}
		{include file="order/block/shippingEstimation.tpl"}
		{include file="order/block/coupons.tpl"}
	</tbody>
</table>
<input type="hidden" name="return" value="{$return}" />

{include file="order/block/expressCheckout.tpl"}

<input type="hidden" value="" name="recurringBillingPlan" id="recurringBillingPlan">

{/form}
